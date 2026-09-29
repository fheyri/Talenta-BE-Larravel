<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'sekolah' => ['required', 'string', 'max:150'],
            'jurusan' => ['required', 'string', 'max:100'],
        ];
    }
}
