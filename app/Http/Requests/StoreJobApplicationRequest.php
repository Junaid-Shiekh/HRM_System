<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ApplicationStatus;
use Illuminate\Validation\Rules\Enum;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_posting_id' => ['required', 'exists:job_postings,id'],
            'candidate_id' => ['required', 'exists:candidates,id'],
            'status' => ['required', new Enum(ApplicationStatus::class)],
        ];
    }
}
