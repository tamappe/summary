<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    //
    //hasMany設定
    public function entries()
    {
        return $this->hasMany('App\Models\Entry');
    }

    //
    /* ブログの存在確認 */
    public function entryExists($blog_title)
    {
        return $this->where('title', $blog_title)->exists();
    }
}
