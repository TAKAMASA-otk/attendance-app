<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminStampCorrectionRequestController extends Controller
{
    // 申請一覧
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = StampCorrectionRequest::with([
            'user',
            'attendance'
        ])
        ->where('status', $status)
        ->latest()
        ->get();

        return view(
            'admin.requests.index',
            compact('requests', 'status')
        );
    }

    // 詳細画面
    public function show($id)
    {
        $correction = StampCorrectionRequest::with([
            'user',
            'attendance',
            'correctionBreakTimes'
        ])->findOrFail($id);

        return view(
            'admin.requests.show',
            compact('correction')
        );
    }

    // 承認
    public function approve($id)
    {
        $correction = StampCorrectionRequest::with(
            'correctionBreakTimes'
        )->findOrFail($id);

        $attendance = Attendance::findOrFail(
            $correction->attendance_id
        );

        // 勤怠更新
        $attendance->update([
            'start_time' =>
                $correction->requested_clock_in,

            'end_time' =>
                $correction->requested_clock_out,
        ]);

        // 既存休憩削除
        BreakTime::where(
            'attendance_id',
            $attendance->id
        )->delete();

        // 修正休憩反映
        foreach (
            $correction->correctionBreakTimes
            as $break
        ) {

            BreakTime::create([
                'attendance_id' =>
                    $attendance->id,

                'break_start' =>
                    $break->break_start,

                'break_end' =>
                    $break->break_end,
            ]);
        }

        // 承認済み
        $correction->update([
            'status' => 'approved'
        ]);

        return redirect()
            ->route(
                'admin.requests.show',
                $correction->id
            )
            ->with(
                'success',
                '承認しました'
            );
    }
}