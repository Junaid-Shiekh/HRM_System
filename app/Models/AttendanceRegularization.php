<?php

namespace App\Models;

use App\Traits\BelongsToHR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRegularization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hr_id',
        'employee_id',
        'attendance_record_id',
        'date',
        'original_clock_in',
        'original_clock_out',
        'requested_clock_in',
        'requested_clock_out',
        'reason',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
