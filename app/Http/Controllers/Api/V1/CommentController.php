<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InvalidQueryException;
use App\Models\Comment;
use App\Models\Post;
use App\Services\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = Comment::with(['post', 'user'])->paginate(10);

        if ($comments->isEmpty()) {
            return AppResponse::error('No comments found.', 404);
        }

        return AppResponse::success($comments, 'Comments retrieved successfully.');
    }

    public function getCommentsWithPosts()
    {
        $commentsWithPosts = Comment::with(['post', 'user'])->get();
        if ($commentsWithPosts->isEmpty()) {
            return AppResponse::error('No comments with Posts found.', 404);
        }

        return AppResponse::success($commentsWithPosts, 'Comments with posts retrieved successfully.');
    }

    public function store(Request $request, Post $post)
    {
        // $user = $request->user();
        // if (!$user) {
        //     return AppResponse::error('Unauthorized. Please log in.', 401);
        // }

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $comment = $post->comments()->create([
            'body' => $validated['body'],
            // 'user_id' => $user->id,
        ]);

        return AppResponse::success($comment, 'Comment created successfully.', 201);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $userName = $request->input('user_name');

        if (!$query && !$userName) {
            return AppResponse::error('Please provide a search term or a user name.', 400); // Changed to 400
        }

        $comments = Comment::query()
            ->with('user')
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('body', 'LIKE', "%{$query}%");
            })
            ->when($userName, function ($queryBuilder) use ($userName) {
                return $queryBuilder->whereHas('user', function ($q) use ($userName) {
                    $q->where('name', 'LIKE', "%{$userName}%");
                });
            })
            ->withTrashed()
            ->paginate(10);

        if ($comments->isEmpty()) {
            return AppResponse::error('No comments found matching the provided criteria.', 404);
        }

        return AppResponse::success($comments, 'Comments retrieved successfully.', 200);
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return AppResponse::error('Comment not found.', 404);
        }

        $comment->delete();
        return AppResponse::success('Comment soft deleted successfully.', 200);
    }

    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $trashedComments = Comment::onlyTrashed()->paginate($perPage);

        if ($trashedComments->isEmpty()) {
            return AppResponse::error('No trashed comments found.', 404);
        }

        return AppResponse::success($trashedComments, 'Trashed comments retrieved successfully.', 200);
    }
}
