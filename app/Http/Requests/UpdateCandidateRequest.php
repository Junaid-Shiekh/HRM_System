<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:candidates,email,' . $this->candidate->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'resume_path' => ['nullable', 'string'],
            'cover_letter' => ['nullable', 'string'],
        ];
    }
}
