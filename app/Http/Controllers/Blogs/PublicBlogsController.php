<?php

namespace App\Http\Controllers\Blogs;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class PublicBlogsController extends Controller
{
    public function index(Request $request)
    {

        $query = Blog::query();
        $blogs = $query->where('status', Blog::DB_STATUS_PUBLISHED)
            ->where('moderation_status', Blog::DB_MODERATION_STATUS_APPROVED)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate(6);

        return view('blogs.public.index', [
            'blogs' => $blogs
        ]);
    }

    public function show($slugOrId){

        // todo: implement id in future
        $blog = Blog::findPublishedBySlug($slugOrId);

        if (empty($blog)) {
            abort(404);
        }

        return view('blogs.public.show', [
            'blog' => $blog,
        ]);
    }
}
