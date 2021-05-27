<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
//        $this->middleware('is_admin');
    }

    public function  index() {
        return view('admin.posts');
    }
}
