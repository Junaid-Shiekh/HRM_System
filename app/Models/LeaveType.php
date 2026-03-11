<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveType extends Model
{
    use HasFactory, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'name',
        'description',
        'max_days',
        'is_paid',
        'gender',
        'allow_carry_forward',
        'color',
        'status',
    ];

    public function leavePolicies()
    {
        return $this->hasMany(LeavePolicy::class);
    }

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }
}
