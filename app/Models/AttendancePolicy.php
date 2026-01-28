<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendancePolicy extends Model
{
    use HasFactory, SoftDeletes, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'name',
        'late_grace_period',
        'early_leave_grace_period',
        'overtime_rate',
        'status',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
