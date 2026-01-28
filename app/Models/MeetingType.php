<?php

namespace App\Models;

use App\Traits\BelongsToHR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_id',
        'name',
        'description',
        'color',
        'default_duration',
        'status',
    ];

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }
}
