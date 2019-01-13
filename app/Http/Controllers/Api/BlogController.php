<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Http\Resources\BlogResource;

class BlogController extends Controller
{
    public function index()
    {
        $datas = Blog::all();
        return BlogResource::collection($datas);
    }
}
