<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryProfile extends Model
{
    use HasFactory, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'employee_id',
        'base_salary',
        'salary_type',
        'payment_method',
        'bank_name',
        'account_name',
        'account_number',
        'iban',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
