<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use HasFactory, BelongsToHR;

    protected $fillable = [
        'hr_id', 'name', 'type', 'amount_type', 'amount', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_salary_components')
                    ->withPivot('custom_amount')
                    ->withTimestamps();
    }
}
