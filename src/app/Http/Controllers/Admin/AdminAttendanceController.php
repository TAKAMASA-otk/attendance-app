<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    // 詳細表示（FN037）
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        return view('admin.attendance.show', compact('attendance'));
    }

    // 更新処理（FN039・FN040）
    public function update(Request $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // 承認待ちは編集不可
        if ($attendance->status === 'pending') {
            return back()->with('error', '承認待ちのため修正はできません。');
        }

        // バリデーション
        $request->validate([
            'start_time' => ['required'],
            'end_time'   => ['required', 'after:start_time'],
            'note'       => ['required'],
        ], [
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'note.required'  => '備考を記入してください',
        ]);

        $date = $attendance->date;

        $start = Carbon::parse($date . ' ' . $request->start_time);
        $end   = Carbon::parse($date . ' ' . $request->end_time);

        // 勤怠更新
        $attendance->update([
            'start_time' => $start,
            'end_time'   => $end,
            'note'       => $request->note,
        ]);

        // 休憩処理
        $breakStarts = $request->break_start ?? [];
        $breakEnds   = $request->break_end ?? [];

        foreach ($breakStarts as $i => $bs) {

            if (!$bs && !$breakEnds[$i]) continue;

            $bsTime = Carbon::parse($date . ' ' . $bs);
            $beTime = isset($breakEnds[$i]) && $breakEnds[$i]
                ? Carbon::parse($date . ' ' . $breakEnds[$i])
                : null;

            // バリデーション
            if ($bsTime->lt($start) || $bsTime->gt($end)) {
                return back()->withErrors(['休憩時間が不適切な値です'])->withInput();
            }

            if ($beTime && $beTime->gt($end)) {
                return back()->withErrors(['休憩時間もしくは退勤時間が不適切な値です'])->withInput();
            }

            if ($beTime && $beTime->lt($bsTime)) {
                return back()->withErrors(['休憩時間が不適切な値です'])->withInput();
            }
        }

        // 保存
        foreach ($breakStarts as $i => $bs) {

            if (!$bs && !$breakEnds[$i]) continue;

            $bsTime = Carbon::parse($date . ' ' . $bs);
            $beTime = isset($breakEnds[$i]) && $breakEnds[$i]
                ? Carbon::parse($date . ' ' . $breakEnds[$i])
                : null;

            if (isset($attendance->breaks[$i])) {
                $attendance->breaks[$i]->update([
                    'break_start' => $bsTime,
                    'break_end'   => $beTime,
                ]);
            } else {
                $attendance->breaks()->create([
                    'break_start' => $bsTime,
                    'break_end'   => $beTime,
                ]);
            }
        }

        return redirect()
            ->route('admin.attendance.show', $attendance->id)
            ->with('success', '勤怠情報を修正しました');
    }
}