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
            'user_id' => 'required|integer|exists:users,id', 
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(), 
            'name' => trim($this->name), 
            'task' => trim($this->task), 
        ]);
    }

    public function getValidatedData()
    {
        $validated = $this->validated();
        $validated['status'] = $this->boolean('status'); 
        return $validated; 
    }
}
