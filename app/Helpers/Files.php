<?php

namespace App\Helpers;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Auth;
use Illuminate\Support\Facades\File;
use getID3;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function Aws\filter;

class Files
{

    /*******************************************************************************************************
     * Upload files
     *******************************************************************************************************/

    /**
     * Upload file from request
     * @param Request $request
     * @param string $path
     * @param string $field
     * @param bool $analyse_file
     * @return array|null
     */
    public static function uploadRequestFile(Request $request, string $path = 'app/uploads', string $field = 'file', bool $analyse_file = true): ?array
    {
        //Validate
        $request->validate([
            $field => 'nullable|file', //|mimes:jpeg,png,jpg,gif,zip,rar,wax,mp3,mp4,mp5,pdf,doc,dox,ppt,txt
        ]);

        //Upload image if exists
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $name = strtolower(md5(time() . Str::random(10)) . '.' . $file->getClientOriginalExtension());
            $destinationPath = storage_path("app/{$path}");
            $fileFullPath = $destinationPath . '/' . $name;
            $filePath = "{$path}" . '/' . $name;

            //Upload image to s3
            Storage::disk('s3')->put($filePath, file_get_contents($file));

            return [
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'size_formatted' => self::formatBytes($file->getSize()),
                'path' => $filePath,
                'extension' => $file->getClientOriginalExtension(),
                'analysis' => ($analyse_file ? self::analysisFile($file) : null),
            ];
        }
        return null;
    }

    /**
     * Upload image from request
     * @param Request $request
     * @param string $path
     * @param string $field
     * @param bool $optimize
     * @param false $resize
     * @param int $sizeX
     * @param int $sizeY
     * @return string|null
     * @throws FileNotFoundException
     */
    public static function uploadRequestImage(Request $request, string $path = 'app/uploads', string $field = 'image', bool $optimize = true, bool $resize = false, int $sizeX = 640, int $sizeY = 640): ?string
    {
        //Validate
        $request->validate([
            $field => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        //Upload image if exists
        if ($request->hasFile($field)) {
            $image = $request->file($field);
            $name = strtolower(md5(time() . Str::random(10)) . '.' . $image->getClientOriginalExtension());
            $destinationPath = storage_path("app/{$path}");
            $imageFullPath = $destinationPath . '/' . $name;
            $imagePath = "{$path}" . '/' . $name;

            //Upload image locally to resize it
            $image->move($destinationPath, $name);

            //Optimize image
            if ($optimize && File::exists($image)) {
                //Optimize
                ImageOptimizer::optimize($image);
            }

            //Resize image
            if ($resize && File::exists($imageFullPath)) {
                // open an image file
                $img = Image::make($imageFullPath);

                // now you are able to resize the instance
                $img->resize($sizeX, $sizeY, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // and insert a watermark for example
                //$img->insert(public_path('img/logo.png'));

                // finally, we save the image as a new file
                $img->save($imageFullPath);
            }

            //Set uploaded image
            $image = Storage::disk('local')->get($imagePath);

            //Upload image to s3
            Storage::disk('s3')->put($imagePath, $image);

            //Delete local image
            Storage::disk('local')->delete($imagePath);

            return $imagePath;
        }
        return null;
    }

    /**
     * Upload image from base64
     * @param $image
     * @param string $path
     * @param string $field
     * @param bool $optimize
     * @param false $resize
     * @param int $sizeX
     * @param int $sizeY
     * @return string
     * @throws FileNotFoundException
     */
    public static function uploadBase64Image($image, string $path = 'app/uploads', string $field = 'image', bool $optimize = true, bool $resize = false, int $sizeX = 256, int $sizeY = 256): string
    {
        //Get extension
        preg_match("/^data:image\/(.*);base64/i", $image, $match);
        $extension = $match[1];

        //Create image
        $image = substr($image, strpos($image, ",") + 1);
        $image = base64_decode($image);

        //Set name
        $name = md5(time() . Str::random(10)) . '.' . $extension;

        //Set paths
        $destinationPath = storage_path("app/{$path}");
        $imageFullPath = $destinationPath . '/' . $name;
        $imagePath = "{$path}" . '/' . $name;

        //Store image
        Image::make($image)->save($imageFullPath);

        //Optimize image
        if ($optimize && File::exists($imageFullPath)) {
            //Optimize
            ImageOptimizer::optimize($imageFullPath);
        }


        //Resize image
        if ($resize && File::exists($imageFullPath)) {
            // open an image file
            $img = Image::make($imageFullPath);

            // now you are able to resize the instance
            $img->resize($sizeX, $sizeY, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // and insert a watermark for example
            //$img->insert(public_path('img/logo.png'));

            // finally we save the image as a new file
            $img->save($imageFullPath);
        }

        //Set uploaded image
        $image = Storage::disk('local')->get($imagePath);

        //Upload image to s3
        Storage::disk('s3')->put($imagePath, $image);

        //Delete local image
        Storage::disk('local')->delete($imagePath);

        return $imagePath;
    }

    /**
     * get s3 file path
     * @param $path
     * @return string|null
     */
    public static function getS3File($path): ?string
    {
        //Trim path
        $path = trim($path);

        //If empty
        if (empty($path)) {
            return null;
        }

        //check path if url
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        //Get the file
        try {
            $path = Storage::disk('s3')->url($path);
        } catch (Exception $e) {
            $path = null;
        }

        return $path;
    }

    /**
     * delete s3 file path
     * @param $path
     * @return mixed
     */
    public static function deleteS3File($path)
    {
        //check path if url
        if (is_null($path) || filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        //Delete the file
        try {
            $path = Storage::disk('s3')->delete($path);
        } catch (\Exception $e) {
            $path = null;
        }

        return $path;
    }

    /**
     * Get S3 uri
     * @param null $type
     * @return string
     */
    public static function getBaseS3URI($type = null): string
    {
        //Set base uri
        $base = Storage::disk('s3')->url('/') . '//';

        //Fix base
        $base = str_replace('////', '', $base);

        switch ($type) {
            case 'avatar':
                $uri = "{$base}/uploads/avatars";
                break;
            case 'advertisements':
                $uri = "{$base}/uploads/advertisements/avatars";
                break;
            case 'uploads':
                $uri = "{$base}/uploads";
                break;
            default:
                $uri = $base;
        }

        return $uri;
    }

    /**
     * Format Bytes
     * @param $size
     * @param int $precision
     * @return string
     */
    public static function formatBytes($size, int $precision = 2): string
    {
        $base = log($size) / log(1024);
        $suffix = array("", "k", "M", "G", "T")[floor($base)];
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffix;
    }

    /**
     *
     * @param $file
     * @return array
     */
    public static function analysisFile($file): array
    {
        $getID3 = new getID3();
        return $getID3->analyze($file);
    }

    /**
     * @param Media $media
     * @return false|int|BinaryFileResponse
     */
    /**
     * Stream a media file (or one of its conversions) INLINE, so it can be
     * shown directly in an <img>/video tag. Works for images and videos, reads
     * from wherever the media actually lives, and is served on the request host.
     *
     * @param Media|null $media
     * @param string|null $conversion
     */
    /**
     * Build a browser-usable URL for a media item.
     * Files on the local disk are not reachable at getFullUrl() (that points at
     * /storage on the public disk), so they are served inline through the
     * media.view route. S3 keeps its own working URL.
     *
     * @param Media|null $media
     * @param string|null $conversion
     * @return string|null
     */
    public static function mediaUrl($media, $conversion = null)
    {
        if (!$media) {
            return null;
        }

        $hasConversion = $conversion && $media->hasGeneratedConversion($conversion);

        if ($media->getDiskDriverName() === 's3') {
            return $hasConversion ? $media->getFullUrl($conversion) : $media->getFullUrl();
        }

        return $hasConversion
            ? route('media.view', ['uuid' => $media->uuid, 'conversion' => $conversion])
            : route('media.view', ['uuid' => $media->uuid]);
    }

    public static function streamMedia($media, $conversion = null)
    {
        if (!$media) {
            abort(404);
        }

        //remote disks already have a working absolute URL — redirect to it
        if ($media->getDiskDriverName() === 's3') {
            return redirect($conversion && $media->hasGeneratedConversion($conversion)
                ? $media->getFullUrl($conversion)
                : $media->getFullUrl());
        }

        $path = ($conversion && $media->hasGeneratedConversion($conversion))
            ? $media->getPath($conversion)
            : $media->getPath();

        if (!file_exists($path)) {
            abort(404);
        }

        //response()->file serves inline with the right content-type and honours
        //range requests, which video seeking needs
        return response()->file($path);
    }

    public static function downloadMedia(Media $media)
    {
        if ($media->getDiskDriverName() === 's3') {
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . basename($media->getUrl()));
            header("Content-Type: " . $media->mime_type);
            return readfile($media->getUrl());
        }
        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * Upload image from request
     * @param Request $request
     * @param string $path
     * @param string $field
     * @param bool $optimize
     * @param false $resize
     * @param int $sizeX
     * @param int $sizeY
     * @return string|null
     */
    public static function uploadTempImage(Request $request, string $path = 'app/uploads', string $field = 'image', bool $optimize = true, bool $resize = true, int $sizeX = 640, int $sizeY = 640): ?string
    {
        //Validate
        $request->validate([
            $field => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        //Upload image if exists
        if ($request->hasFile($field)) {
            $image = $request->file($field);
            $name = strtolower(md5(time() . Str::random(10)) . '.' . $image->getClientOriginalExtension());
            $destinationPath = storage_path("app/{$path}");
            $imageFullPath = $destinationPath . '/' . $name;
            $imagePath = "{$path}" . '/' . $name;

            //Upload image locally to resize it
            $image->move($destinationPath, $name);


            //Optimize image
            if ($optimize && File::exists($image)) {
                //Optimize
                ImageOptimizer::optimize($image);
            }

            //Resize image
            if ($resize && File::exists($imageFullPath)) {
                // open an image file
                $img = Image::make($imageFullPath);

                // now you are able to resize the instance
                $img->resize($sizeX, $sizeY, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // and insert a watermark for example
                //$img->insert(public_path('img/logo.png'));

                // finally, we save the image as a new file
                $img->save($imageFullPath);
            }
            return $imagePath;
        }
        return null;
    }
}
