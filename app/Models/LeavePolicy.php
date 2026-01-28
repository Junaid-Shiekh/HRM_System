<?php

namespace App\Models;

use App\Traits\BelongsToHR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeavePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_id',
        'name',
        'description',
        'leave_type_id',
        'accrual_type',
        'accrual_rate',
        'carry_forward_limit',
        'min_days',
        'max_days',
        'requires_approval',
        'status',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
