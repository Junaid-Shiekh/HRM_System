<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\JobType;
use Illuminate\Validation\Rules\Enum;

class StoreJobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'job_category_id' => ['required', 'exists:job_categories,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'job_type' => ['required', new Enum(JobType::class)],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'salary_min' => ['nullable', 'numeric'],
            'salary_max' => ['nullable', 'numeric'],
            'closing_date' => ['nullable', 'date'],
            'status' => ['required', 'string'],
        ];
    }
}
