<?php

namespace App\Models\Course;

use App\Models\User\Student;
use App\Models\User\Teacher;
use Carbon;
use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    protected $appends = [
        'human_date_time',
        'human_time',
    ];

    protected $with = ['timeSlot'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getHumanDateTimeAttribute()
    {
        $timeSlot = $this->timeSlot;
        return humanDate($this->date, true) . $timeSlot->day_part . ' ' . $timeSlot->range;
    }

    public function getHumanTimeAttribute()
    {
        $timeSlot = $this->timeSlot;
        return humanDayOfWeek(Carbon::parse($this->date)->dayOfWeek).$timeSlot->day_part . ' ' . $timeSlot->range;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeOrderByLatest($query)
    {
        return $query->orderBy('date', 'desc')->orderBy('start', 'desc');
    }

    public function scopeFollowingWeek($query)
    {
        return $this->scopeFollowingDays($query, 7);
    }

    public function scopeFollowingDays($query, $days)
    {
        return $query->where([
            ['date', '>=' , Carbon::now()->tomorrow()->toDateString()],
            ['date', '<', Carbon::now()->tomorrow()->addDays($days)->toDateString()]
        ]);
    }
}