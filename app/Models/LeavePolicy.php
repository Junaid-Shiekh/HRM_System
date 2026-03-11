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
        'max_days_per_request',
        'max_consecutive_days',
        'notice_period_days',
        'allow_carry_forward',
        'probation_restriction_days',
        'allow_half_day',
        'allow_encashment',
        'applicability_type',
        'applicability_ids',
        'requires_approval',
        'status',
    ];

    protected $casts = [
        'applicability_ids' => 'array',
        'allow_carry_forward' => 'boolean',
        'allow_half_day' => 'boolean',
        'allow_encashment' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
