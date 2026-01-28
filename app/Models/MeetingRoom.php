<?php

namespace App\Models;

use App\Traits\BelongsToHR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'hr_id',
        'name',
        'location',
        'capacity',
        'equipment',
        'status',
    ];

    /**
     * Get the meetings for the room.
     */
    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }
}
