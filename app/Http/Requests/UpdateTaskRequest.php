<?php

namespace App\Http\Requests;
use App\Http\Requests\StoreTaskRequest;


class UpdateTaskRequest extends StoreTaskRequest
{
    public function rules (): array {
        return [
            'name' => 'required|string|max:255|min:2'
        ];
    }
}
