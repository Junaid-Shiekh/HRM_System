<?php

namespace App\Models;

use App\Traits\BelongsToHR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hr_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'cover_letter',
    ];

    protected $appends = ['name', 'created_at_fmt'];

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getCreatedAtFmtAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
