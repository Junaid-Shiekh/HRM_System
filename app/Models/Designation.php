<?php

namespace App\Models;

use App\Traits\BelongsToHR;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use HasFactory, SoftDeletes, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'department_id',
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
