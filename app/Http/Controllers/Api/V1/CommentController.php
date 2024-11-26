<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CommentRequest;
use App\Exceptions\InvalidQueryException;
use App\Models\Comment;
use App\Models\Post;
use App\Services\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = Comment::with(['post', 'user'])
            ->where('user_id', Auth::id())
            ->paginate(10);

        if ($comments->isEmpty()) {
            return AppResponse::error('No comments found.', 404);
        }

        return AppResponse::success($comments, 'Comments retrieved successfully.', 200);
    }

    public function store(CommentRequest $request, Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return AppResponse::error('Unauthorized to comment on this post.', 403);
        }

        $validated = $request->validated();

        $comment = $post->comments()->create([
            'body' => $validated['body'],
            'user_id' => Auth::id(),
        ]);

        return AppResponse::success($comment, 'Comment created successfully.', 201);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $userName = $request->input('user_name');

        if (!$query && !$userName) {
            throw new InvalidQueryException('Query and user name cannot both be empty.');
        }

        $comments = Comment::where('user_id', Auth::id())
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('body', 'LIKE', "%{$query}%");
            })
            ->when($userName, function ($queryBuilder) use ($userName) {
                return $queryBuilder->whereHas('user', function ($q) use ($userName) {
                    $q->where('name', 'LIKE', "%{$userName}%");
                });
            })
            ->with(['post', 'user'])
            ->paginate(10);

        if ($comments->isEmpty()) {
            return AppResponse::error('No comments found matching the provided criteria.', 404);
        }

        return AppResponse::success($comments, 'Comments retrieved successfully.', 200);
    }

    public function destroy($id)
    {
        $comment = Comment::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$comment) {
            return AppResponse::error('Comment not found or unauthorized access.', 404);
        }

        $comment->delete();

        return AppResponse::success('Comment soft deleted successfully.', 200);
    }

    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $trashedComments = Comment::onlyTrashed()
            ->where('user_id', Auth::id())
            ->with(['post', 'user'])
            ->paginate($perPage);

        if ($trashedComments->isEmpty()) {
            return AppResponse::error('No trashed comments found.', 404);
        }

        return AppResponse::success($trashedComments, 'Trashed comments retrieved successfully.', 200);
    }
}
