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
            'sekolah' => ['nullable', 'string', 'max:150'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'nis' => ['nullable', 'string', 'max:30'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string'],
            'alamat' => ['nullable', 'string'],
            'angkatan' => ['nullable', 'integer'],
            'status_karier' => ['nullable', 'string', 'in:bekerja,wirausaha,pendidikan,lainnya'],
        ];
    }
}
