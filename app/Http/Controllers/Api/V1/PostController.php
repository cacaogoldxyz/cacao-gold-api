<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\PostRequest;
use App\Exceptions\InvalidQueryException;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $posts = Post::where('user_id', Auth::id())
            ->with(['comments.user']) 
            ->paginate($perPage);

        return AppResponse::success($posts, 'Posts retrieved successfully.', 200);
    }

    public function store(PostRequest $request)
    {
        info('Authenticated user ID:', ['id' => auth()->id()]);

        $validatedData = $request->validated();
        $validatedData['user_id'] = auth()->id();

        $post = Post::create($validatedData);

        return PostResource::make($post);
    }

    public function show($id)
    {
        $post = Post::with(['comments.user']) 
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$post) {
            return AppResponse::error('Post not found or unauthorized access.', 404);
        }

        return AppResponse::success($post, 'Post retrieved successfully.', 200);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $userName = $request->input('user_name');

        if (!$query && !$userName) {
            throw new InvalidQueryException('Query and user name cannot both be empty.');
        }

        $posts = Post::where('user_id', Auth::id())
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('body', 'LIKE', "%{$query}%");
            })
            ->when($userName, function ($queryBuilder) use ($userName) {
                return $queryBuilder->whereHas('user', function ($q) use ($userName) {
                    $q->where('name', 'LIKE', "%{$userName}%");
                });
            })
            ->with(['user', 'comments.user'])
            ->paginate(10);

        if ($posts->isEmpty()) {
            return AppResponse::error('No posts found matching the search criteria.', 404);
        }

        return AppResponse::success($posts, 'Search results retrieved successfully.', 200);
    }

    public function destroy($id)
    {
        $post = Post::with('comments')->where('id', $id)->where('user_id', Auth::id())->first();

        if (!$post) {
            return AppResponse::error('Post not found or unauthorized access.', 404);
        }

        $post->comments()->delete(); 
        $post->delete();

        return AppResponse::success('Post and related comments deleted successfully.', 200);
    }

    public function restore($id)
    {
        $post = Post::withTrashed()->with('comments')->where('id', $id)->where('user_id', Auth::id())->first();

        if (!$post || !$post->trashed()) {
            return AppResponse::error('Post not found or not deleted.', 404);
        }

        $post->restore();
        $post->comments()->onlyTrashed()->restore();

        return AppResponse::success('Post and associated comments restored successfully!', 200);
    }

    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $trashedPosts = Post::onlyTrashed()
            ->where('user_id', Auth::id())
            ->with(['comments.user'])
            ->paginate($perPage);

        if ($trashedPosts->isEmpty()) {
            return AppResponse::error('No trashed posts found.', 404);
        }

        return AppResponse::success($trashedPosts, 'Trashed posts retrieved successfully.', 200);
    }
}
