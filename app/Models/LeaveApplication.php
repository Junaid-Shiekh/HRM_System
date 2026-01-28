<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveApplication extends Model
{
    use HasFactory, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'reason',
        'attachment',
        'status',
        'approved_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
