<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use App\Services\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('comments')->get();
        return AppResponse::success($posts, 'Posts retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $post = Post::create($validated);
        return AppResponse::success($post, 'Post created successfully.', 201);
    }

    public function show(Post $post)
    {
        $postWithComments = $post->load('comments');
        return AppResponse::success($postWithComments, 'Post retrieved successfully.');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
    
        if (!$query) {
            return AppResponse::error('No search query provided.', 400);
        }
    
        $posts = Post::where('title', 'LIKE', "%{$query}%")
                    ->orWhere('body', 'LIKE', "%{$query}%")
                    ->paginate(10);
    
        return AppResponse::success($posts, 'Search results retrieved successfully.');
    }

    public function destroy(Post $post)
    {
       
        if(!$post->exists) {
        return AppResponse::error('Post not found', 404);
        } 
    
        $post->delete(); 
    
        return AppResponse::success(null, 'Post deleted successfully.', 204);  
    }
    

    public function restore($id)
    {
        $post = Post::withTrashed()->find($id);

        if ($post && $post->trashed()) {
            $post->restore();
            return AppResponse::success(null, 'Post restored successfully!');
        }

        return AppResponse::error('Post not found or not deleted!', 404);
    }


    public function trashed()
    {
        $trashedPosts = Post::onlyTrashed()->get();
        return AppResponse::success($trashedPosts, 'Trashed posts retrieved successfully.');
    }
}
