<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use App\Enums\JobType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    use HasFactory, SoftDeletes, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'job_category_id',
        'branch_id',
        'title',
        'slug',
        'job_type',
        'description',
        'requirements',
        'salary_min',
        'salary_max',
        'closing_date',
        'status',
    ];

    protected $casts = [
        'job_type' => JobType::class,
        'closing_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
