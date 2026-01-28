<?php

namespace App\Models;

use App\Traits\BelongsToHR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_id',
        'payroll_run_id',
        'employee_id',
        'base_salary',
        'total_allowances',
        'total_deductions',
        'overtime_pay',
        'attendance_deduction',
        'bonus',
        'loan_deduction',
        'net_salary',
        'status',
        'paid_at',
        'calculation_snapshot'
    ];

    protected $casts = [
        'calculation_snapshot' => 'array',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
