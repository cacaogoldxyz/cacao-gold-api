<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use App\Models\Comment; 
use App\Services\AppResponse; 
use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommentController extends Controller
{
    public function getCommentsWithPosts()
    {
        $commentsWithPosts = DB::table('comments')
            ->join('posts', 'comments.post_id', '=', 'posts.id')
            ->select('comments.body as comment_body', 'posts.title as post_title', 'posts.body as post_body')
            ->get();

        return AppResponse::success($commentsWithPosts, 'Comments retrieved successfully.');
    }

    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'body' => 'required',
        ]);

        $comment = $post->comments()->create([
            'body' => $validated['body'],
        ]);

        return AppResponse::success($comment, 'Comment created successfully.', 201);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search');

        $comments = Comment::when($searchTerm, function ($query, $searchTerm) {
            return $query->where('body', 'like', "%{$searchTerm}%");
        })->withTrashed()->get(); 

        return AppResponse::success($comments, 'Comments retrieved successfully.');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete(); 

        return AppResponse::success(null, 'Comment soft deleted successfully.');
    }

    public function restore($id)
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);
        $comment->restore(); 

        return AppResponse::success($comment, 'Comment restored successfully.');
    }
}
