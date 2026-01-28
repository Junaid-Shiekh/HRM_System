<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToHR
{
    protected static function bootBelongsToHR()
    {
        // Automatically set hr_id when creating
        static::creating(function ($model) {
            if (Auth::check() && !isset($model->hr_id)) {
                $user = Auth::user();
                // If it's an HR user, use their ID
                if ($user->user_type === 'hr') {
                    $model->hr_id = $user->id;
                } 
                // If it's an employee, use their linked HR's ID
                elseif ($user->user_type === 'employee') {
                    $employee = \App\Models\Employee::withoutGlobalScopes()->where('user_id', $user->id)->first();
                    if ($employee) {
                        $model->hr_id = $employee->hr_id;
                    }
                }
            }
        });

        // Global scope to filter by hr_id
        static::addGlobalScope('hr_scope', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                
                // Super Admin sees everything
                if ($user->hasRole('Super Admin')) {
                    return;
                }

                if ($user->user_type === 'hr') {
                    $builder->where('hr_id', $user->id);
                } elseif ($user->user_type === 'employee') {
                    $employee = \App\Models\Employee::withoutGlobalScopes()->where('user_id', $user->id)->first();
                    if ($employee) {
                        $builder->where('hr_id', $employee->hr_id);
                    }
                }
            }
        });
    }

    public function hr()
    {
        return $this->belongsTo(\App\Models\User::class, 'hr_id');
    }
}
