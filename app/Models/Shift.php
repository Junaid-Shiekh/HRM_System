<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'name',
        'start_time',
        'end_time',
        'break_duration',
        'grace_period',
        'type',
        'status',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
