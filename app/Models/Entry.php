<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use DateTimeZone;

class Entry extends Model
{
    //
    //belongsTo設定
    public function blog()
    {
        return $this->belongsTo('App\Models\Blog');
    }

    /* ブログ記事の存在確認 */
    public function entryExists($entry_title)
    {
        return $this->where('title', $entry_title)->exists();
    }

    /* 登校日の日付をカスタムフォーマットで返す */
    public function getPublishedDate()
    {
        $published = new DateTime($this->published);
        $published->setTimezone(new DateTimeZone('Asia/Tokyo'));
        // 日付フォーマット defalut: 'Y-m-d H:i:s'
        return $published->format('H:i:s');
    }

    public function getSiteTitleFromEntry()
    {
        return $this->blog->title;
    }
}
