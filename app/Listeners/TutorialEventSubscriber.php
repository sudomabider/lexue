<?php
/**
 *
 *
 *   ______                        _____           __
 *  /_  __/__  ____ _____ ___     / ___/__  ______/ /___
 *   / / / _ \/ __ `/ __ `__ \    \__ \/ / / / __  / __ \
 *  / / /  __/ /_/ / / / / / /   ___/ / /_/ / /_/ / /_/ /
 * /_/  \___/\__,_/_/ /_/ /_/   /____/\__,_/\__,_/\____/
 *
 *
 *
 * Filename->TutorialEventListener.php
 * Project->lexue
 * Description->The listener for tutorial events.
 *
 * Created by DM on 16/10/2 下午10:29.
 * Copyright 2016 Team Sudo. All rights reserved.
 *
 */
namespace App\Listeners;

use App\Models\Course\Order;
use App\Models\Course\Schedule;
use App\Events\TutorialPurchased;

class TutorialEventSubscriber
{
    public function onTutorialPurchased(TutorialPurchased $event)
    {
        $order = $event->order;
        $this->assignSchedules($order);
        $this->pushTutorialConfirmation($order);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\TutorialPurchased',
            'App\Listeners\TutorialEventSubscriber@onTutorialPurchased'
        );
    }

    /**
     * Assign schedules to the schedules table.
     *
     * @param Order $order
     */
    public function assignSchedules(Order $order)
    {
        $tutorials = $order->tutorials;

        foreach ($tutorials as $tutorial) {
            $schedule = new Schedule([
                'student_id' => $tutorial->student_id,
                'teacher_id' => $tutorial->teacher_id,
                'course_id' => $tutorial->id,
                'course_type' => 'tutorial',
                'date' => $tutorial->date,
                'start' => $tutorial->timeSlot->start,
                'end' => $tutorial->timeSlot->end
            ]);

            $schedule->save();
        }
    }

    /**
     * Handle the TutorialPurchased event.
     *
     * @param Order $order
     * @return bool
     */
    public function pushTutorialConfirmation(Order $order)
    {
        $tutorials = $order->tutorials;
        $count = count($tutorials);

        $sampleLecture = $tutorials->first();
        $student = $sampleLecture->student;
        $teacher = $sampleLecture->teacher;

        if (! $student->wechat_id) {
            \Log::error('student with id ' . $student->id . ' has no wechat_id.');
            return false;
        }

        $message = [
            'touser'      => $student->wechat_id,
            'template_id' => config('wechat.template.purchase_success'),
            'url'         => route('m.students::tutorials.index'),
            'topcolor'    => '#000000',
            'data'        => [
                "first"      => [
                    "value" => "亲爱的 " . $student->name . "，您已成功购买课程。\n",
                    "color" => "#000000"
                ],
                "keyword1"   => [
                    "value" => $teacher->name . " 老师的一对一微信课程 (共".$count."课时)",    // 课程名称
                    "color" => "#00beb7"
                ],
                "keyword2"   => [
                    "value" => number_format($order->total, 2) . "元",    // 支付金额
                    "color" => "#00beb7"
                ],
                "keyword3"   => [
                    "value" => $tutorials->pluck('human_date_time')->implode(', '),    // 课程时间
                    "color" => "#00beb7"
                ],
                "remark"   => [
                    "value" => "\n随后乐学云教学主管老师将第一时间与您取得联系，请您及时关注微信消息！",
                    "color" => "#000000"
                ]
            ]
        ];

        \WechatPusher::push($message);
    }
}