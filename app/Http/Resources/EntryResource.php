<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class EntryResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'blog_id' => $this->blog_id,
            'blog_title' => $this->blog->title,
            'title' => $this->title,
            'published' => $this->published,
            'link' => $this->link_url,
            'image' => $this->image_url
        ];
    }
}
