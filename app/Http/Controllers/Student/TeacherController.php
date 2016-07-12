<?php

namespace App\Http\Controllers\Student;

use App\Models\Course\Lecture;
use App\Models\User\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::all();

        return $this->frontendView('frontend.teachers.index', compact('teachers'));
    }

    public function show($id)
    {
        /** @var \App\Models\User\Teacher $teacher */
        $teacher = Teacher::find($id);

        $timetable = $teacher->getTimetable();

        return $this->frontendView('frontend.teachers.show', compact('teacher', 'timetable'));
    }

    /**
     * Accept a booking from the student
     *
     * @param Request $request
     * @param $teacherId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function book(Request $request, $teacherId)
    {
        $this->validate($request, [
            'time' => 'required|date'
        ],[
            'time.required' => '请选择课时',
            'time.date' => '课时格式有误'
        ]);

        /** @var \App\Models\User\Teacher $teacher */
        $teacher = Teacher::find($teacherId);

        $unavailabilities = $teacher->getUnavailabilities();

        $bookTime = $request->input('time');

        /*
         * sanitize and validate the time
         */
        $bookTime = Carbon::parse($bookTime);
        $bookTime->minute(0)->second(0);

        $request->session()->flash('test', 'Task was successful!');

        /* check if requested time is valid */
        if ($bookTime < Carbon::tomorrow()
            OR $bookTime > Carbon::today()->addDays(config('course.days_to_show'))
            OR $bookTime->hour < config('course.day_start')
            OR $bookTime->hour > config('course.day_end')) {
            flash()->error('选择的课时无效');
            return back();
        }

        /* check if requested time is available */
        if (in_array($bookTime, $unavailabilities)) {
            flash()->error('课时已被占用');
            return back();
        }

        /* create lecture */
        try {
            $lecture = new Lecture();
            $lecture->start_at = $bookTime;
            $lecture->student_id = authId();
            $lecture->teacher_id = $teacherId;
            $lecture->save();
        } catch (\Exception $e) {
            // TODO add notifications to backend to manually create lectures
            flash()->error('系统错误……我们将手动添加课程，请稍等片刻');
            return back();
        }

        flash()->success('课程添加成功');
        return redirect()->route('students::lectures.index');
    }
}