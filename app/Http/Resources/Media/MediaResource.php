<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Image\Image;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        //get file mime type
        $mime_type = $this->mime_type;

        if (strstr($mime_type, "video/")) {
            $type = 'video';
        } else if (strstr($mime_type, "image/")) {
            $type = 'image';
        } else if (strstr($mime_type, "audio/")) {
            $type = 'audio';
        } else {
            $type = 'file';
        }


        //Files on the local disk are not reachable at getFullUrl() (that points
        //at /storage on the public disk, and at the configured APP_URL host
        //rather than the request host). Serve them inline through the
        //request-host media.view route instead. S3 keeps its own working URL.
        $isS3 = $this->getDiskDriverName() === 's3';

        $mediaUrl = $isS3
            ? $this->getFullUrl()
            : route('media.view', ['uuid' => $this->uuid]);

        $thumbnailImageUrl = null;
        if ($this->hasGeneratedConversion('thumb')) {
            $thumbnailImageUrl = $isS3
                ? $this->getFullUrl('thumb')
                : route('media.view', ['uuid' => $this->uuid, 'conversion' => 'thumb']);
        }

        return [
            'id' => $this->id,
            'mediaUrl' => $mediaUrl,
            'thumbnailImageUrl' => $thumbnailImageUrl,
            'width' => $this->hasCustomProperty('width') ? $this->getCustomProperty('width') : null,
            'height' => $this->hasCustomProperty('height') ? $this->getCustomProperty('height') : null,
            'fileName' => $this->file_name,
            'uuid' => $this->uuid,
            'downloadUrl' => route('media.download', $this->uuid),
            'type' => $type,
            'mimeType' => $mime_type,
            'size' => $this->human_readable_size,
            'isVoiceNote' => $this->hasCustomProperty('isVoiceNote') && (bool)$this->getCustomProperty('isVoiceNote'),
        ];
    }
}
