<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use App\Models\StampCorrectionRequest;
use App\Models\CorrectionBreakTime;
use App\Http\Requests\UpdateAttendanceRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = Carbon::today('Asia/Tokyo');

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $today)
            ->first();

        $status = '勤務外';

        if ($attendance) {
            if ($attendance->end_time) {
                $status = '退勤済';
            } elseif ($attendance->breaks()->whereNull('break_end')->exists()) {
                $status = '休憩中';
            } elseif ($attendance->start_time) {
                $status = '出勤中';
            }
        }

        return view('attendance.index', compact('attendance', 'status'));
    }

    // 出勤
    public function start()
    {
        $today = Carbon::today('Asia/Tokyo');

        $exists = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $today)
            ->exists();

        if ($exists) {
            return back()->with('error', '本日はすでに出勤しています');
        }

        Attendance::create([
            'user_id' => Auth::id(),
            'date' => $today,
            'start_time' => Carbon::now('Asia/Tokyo'),
            'status' => 'working',
        ]);

        return redirect()->route('attendance.index');
    }

    // 休憩入
    public function breakStart()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance || $attendance->status !== 'working') {
            return back()->with('error', '出勤中のみ休憩できます');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now('Asia/Tokyo'),
        ]);

        $attendance->update([
            'status' => 'break',
        ]);

        return redirect()->route('attendance.index');
    }

    // 休憩戻
    public function breakEnd()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance || $attendance->status !== 'break') {
            return back()->with('error', '休憩中ではありません');
        }

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        if ($break) {
            $break->update([
                'break_end' => Carbon::now('Asia/Tokyo'),
            ]);
        }

        // 休憩戻りなので、退勤ではなく出勤中に戻す
        $attendance->update([
            'status' => 'working',
        ]);

        return redirect()->route('attendance.index');
    }

    // 退勤
    public function end()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance || $attendance->status !== 'working') {
            return back()->with('error', '出勤中のみ退勤できます');
        }

        $openBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        if ($openBreak) {
            $openBreak->update([
                'break_end' => Carbon::now('Asia/Tokyo'),
            ]);
        }

        $attendance->update([
            'end_time' => Carbon::now('Asia/Tokyo'),
            'status' => 'done',
        ]);

        return redirect()->route('attendance.index')
            ->with('message', 'お疲れ様でした。');
    }

    // 今日の勤怠取得
    private function getTodayAttendance()
    {
        $today = Carbon::today('Asia/Tokyo');

        return Attendance::where('user_id', Auth::id())
            ->whereDate('date', $today)
            ->first();
    }

    // 勤怠一覧
    public function list(Request $request)
    {
        $month = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now('Asia/Tokyo');

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->toDateString();
            });

        $dates = [];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $dates[] = $date->copy();
        }

        return view('attendance.list', compact('attendances', 'dates', 'month'));
    }

    // 勤怠詳細
    public function show($id)
    {
        $attendance = Attendance::with(['breaks', 'correctionRequests'])
        ->findOrFail($id);

        if ($attendance->user_id !== Auth::id()) {
        abort(403);
        }

        $latestRequest = $attendance->correctionRequests()
            ->where('status', 'pending')
            ->latest()
            ->first();

        $isPending = !!$latestRequest;

        $correctionBreaks = $latestRequest
            ? $latestRequest->correctionBreakTimes
            : collect();

        return view('attendance.show', compact(
            'attendance',
            'latestRequest',
            'isPending',
            'correctionBreaks'
        ));
    }

    // 修正申請
    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $exists = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('error', 'すでに申請中です');
        }

        $date = $attendance->date;

        $clockIn = Carbon::parse($date . ' ' . $request->start_time, 'Asia/Tokyo');
        $clockOut = Carbon::parse($date . ' ' . $request->end_time, 'Asia/Tokyo');

        $correction = StampCorrectionRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $clockIn,
            'requested_clock_out' => $clockOut,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        if ($request->has('break_start')) {
            foreach ($request->break_start as $index => $start) {
                $end = $request->break_end[$index] ?? null;

                if (!empty($start) || !empty($end)) {
                    CorrectionBreakTime::create([
                        'correction_id' => $correction->id,
                        'break_start' => !empty($start)
                            ? Carbon::parse($date . ' ' . $start, 'Asia/Tokyo')
                            : null,
                        'break_end' => !empty($end)
                            ? Carbon::parse($date . ' ' . $end, 'Asia/Tokyo')
                            : null,
                    ]);
                }
            }
        }

        return redirect()->route('attendance.show', $attendance->id)
            ->with('success', '修正申請を送信しました');
    }

    public function adminRequests()
    {
        $requests = StampCorrectionRequest::with('user', 'attendance')
            ->latest()
            ->get();

        return view('admin.requests', compact('requests'));
    }

    public function userRequests(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = StampCorrectionRequest::with('attendance')
            ->where('user_id', Auth::id())
            ->when($status === 'pending', function ($query) {
                $query->where('status', 'pending');
            })
            ->when($status === 'approved', function ($query) {
                $query->where('status', 'approved');
            })
            ->latest()
            ->get();

        return view('attendance.requests', compact('requests', 'status'));
    }
}