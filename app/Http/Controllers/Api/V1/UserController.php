<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\UserRequest;
use Illuminate\Routing\Controller;
use App\Services\AppResponse; 
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function userDetails(UserRequest $request)
    {
        $user = $request->user();

        return AppResponse::success(
            new UserResource($user), 
            'User detail retrieved successfully.',
            200
        );
    }
}
