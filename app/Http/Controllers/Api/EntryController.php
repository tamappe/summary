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
        $datas = Entry::orderBy('published', 'desc')->paginate(100);
        return EntryResource::collection($datas);
    }
}
