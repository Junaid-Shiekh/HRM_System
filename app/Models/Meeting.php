<?php

namespace App\Models;

use App\Traits\BelongsToHR;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use BelongsToHR;

    protected $fillable = [
        'hr_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'meeting_link',
        'status',
        'meeting_type_id',
        'meeting_room_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $appends = ['date', 'start_time_fmt', 'end_time_fmt'];

    public function getDateAttribute(): string
    {
        return $this->start_time->format('M d, Y');
    }

    public function getStartTimeFmtAttribute(): string
    {
        return $this->start_time->format('H:i:s');
    }

    public function getEndTimeFmtAttribute(): string
    {
        return $this->end_time->format('H:i:s');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_meeting');
    }

    public function type()
    {
        return $this->belongsTo(MeetingType::class, 'meeting_type_id');
    }

    public function room()
    {
        return $this->belongsTo(MeetingRoom::class, 'meeting_room_id');
    }
}
