<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $postId = $this->route('task')?->id;
        
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                })->ignore($postId),
            ],
            'body' => 'required|string|max:5000',
        ];
    }

    public function prepareForValidation() {
        info(auth()->id());
        if (auth()->id()) {
            $this->merge([
                'user_id' => auth()->id(),
            ]);
        } else {
            info('No user ID found');
        }
    }

    public function getValidatedData() 
    {
        $validated = $this->validated();
        $validated['user_id'] = auth()->id();
        return $validated;
    }
}
