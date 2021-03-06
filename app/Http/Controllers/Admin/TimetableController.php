<?php

namespace App\Http\Controllers\Admin;

use App\Models\User\Teacher;
use App\Models\Course\TimeSlot;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class TimetableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->backView('admins.timetables.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param $teacherId
     * @return \Illuminate\Http\Response
     */
    public function showTeacher($teacherId)
    {
        $teacher = Teacher::find($teacherId);

        $teacherTimeslots = $teacher->timeSlots->pluck('id');

        $timeslots = TimeSlot::orderByStart()->get()->groupBy('day_part');
        $timeslots = padArray($timeslots);

        $timetable = $teacher->getTimetable();

        if (!$timetable) {
            \Flash::warning('请先添加教师课时');
        }

        return $this->backView('backend.admins.timetables.show', compact('teacher', 'timetable', 'teacherTimeslots', 'timeslots'));
    }

    public function showSnippet($teacherId, $date, $timeSlotId)
    {
        $teacher = Teacher::find($teacherId);

        /* check for tutorials */
        $tutorial = $teacher->tutorials()->where([
            ['date', '=', $date],
            ['time_slot_id', '=', $timeSlotId]
        ])->first();

        if ($tutorial) {
            return view('backend.admins.timetables.snippets.tutorial', compact('tutorial'));
        }

        /* check for offtimes */
        // for some reason orWhere does not work properly here so queyring individually
        $offTime = $teacher->offTimes()->where([
            ['date', '=', $date],
            ['all_day', '=', 1]
        ])->first() ? : $teacher->offTimes()->where([
            ['date', '=', $date],
            ['time_slot_id', '=', $timeSlotId]
        ])->first();

        if ($offTime) {
            return view('backend.admins.timetables.snippets.offtime', compact('teacher','offTime','date','timeSlotId'));
        }

        return view('backend.admins.timetables.snippets.empty', compact('teacher', 'date', 'timeSlotId'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
