<?php

namespace App\Http\Requests\Admin\Author;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'preview_image' => 'image',
            'id' => 'required|exists:articles,id',
            'full_name' => 'required',
            'biography' => 'required'
        ];
    }
}