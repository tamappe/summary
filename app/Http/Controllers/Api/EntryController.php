<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Entry;
use App\Http\Resources\EntryResource;

class EntryController extends Controller
{
    //
    public function index()
    {
        // ref: https://qiita.com/zdjjs/items/1c2437fcdd35c6754bcf
        $datas = Entry::orderBy('published', 'desc')->paginate(100);
        return EntryResource::collection($datas);
    }

    public function blog(Request $request, $id) {
        $datas = Entry::orderBy('published', 'desc')->where('blog_id', $id)->paginate(100);
        return EntryResource::collection($datas);
    }
}
