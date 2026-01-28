<?php

namespace App\Models;

use App\Traits\BelongsToHR;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes, BelongsToHR;

    protected $fillable = ['hr_id', 'name', 'code', 'address', 'phone', 'email', 'status'];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
