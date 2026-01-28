<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryAdvance extends Model
{
    use HasFactory, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'employee_id',
        'amount',
        'reason',
        'status',
        'repayment_date',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'repayment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
