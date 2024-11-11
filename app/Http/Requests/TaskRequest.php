<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Get the task ID from the route for update operations
        $taskId = $this->route('task')?->id; 

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks')->where(function ($query) {
                    return $query->where('user_id', auth()->id()); 
                })->ignore($taskId) 
            ],
            'task' => 'required|string',
            'status' => 'nullable|boolean',
            'user_id' => 'required|integer|exists:users,id'
        ];
    }

    public function prepareForValidation() {
        info(auth()->id());
        if(auth()->id()){
            $this->merge([
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function getValidatedData()
    {
        $validated = $this->validated();
        $validated['status'] = $this->boolean('status'); 
        $validated['user_id'] = auth()->id(); 
        return $validated;
    }
}
