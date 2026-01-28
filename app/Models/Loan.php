<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory, BelongsToHR;

    protected $fillable = [
        'hr_id',
        'employee_id',
        'amount',
        'reason',
        'installments',
        'monthly_installment',
        'remaining_balance',
        'status',
        'rejection_reason',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
