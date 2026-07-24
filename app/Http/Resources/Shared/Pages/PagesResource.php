<?php

namespace App\Http\Resources\Shared\Pages;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class PagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        //get language column to show
        $title_column = App::currentLocale() === 'ar' ? 'title_ar' : 'title_en';
        $content_column = App::currentLocale() === 'ar' ? 'contents_ar' : 'contents_en';

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->{$title_column},
            'content' => $this->{$content_column},
        ];
    }
}
