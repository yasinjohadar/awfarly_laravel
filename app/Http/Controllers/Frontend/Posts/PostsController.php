<?php

namespace App\Http\Controllers\Frontend\Posts;

use App\Http\Controllers\Controller;
use App\Models\Posts\Post;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function index($id)
    {
        Post::findOrFail($id);

        return view('frontend.pages.post.post', ['post_id' => $id]);
    }
}
