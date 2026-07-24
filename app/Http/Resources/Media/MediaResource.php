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


        return [
            'id' => $this->id,
            'mediaUrl' => $this->getFullUrl(),
            'thumbnailImageUrl' => $this->hasGeneratedConversion('thumb') ? $this->getFullUrl('thumb') : null,
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
