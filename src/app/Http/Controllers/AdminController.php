<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'), 'Asia/Tokyo')
            : Carbon::today('Asia/Tokyo');

        $users = User::where('is_admin', 0)->get();

        $attendances = Attendance::with('breaks')
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        return view('admin.index', compact('users', 'attendances', 'date'));
    }

    public function staffList()
    {
    $users = User::where('is_admin', 0)->get();

    return view('admin.staff_list', compact('users'));
    }

    public function staffAttendance(User $user)
    {
        $month = request('month')
            ? Carbon::parse(request('month'))
            : Carbon::now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [
                $startOfMonth->format('Y-m-d'),
                $endOfMonth->format('Y-m-d')
            ])
            ->with('breaks')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->format('Y-m-d');
            });

        return view('admin.staff_attendance', compact(
            'user',
            'month',
            'dates',
            'attendances'
        ));
    }

    public function staffAttendanceCsv(User $user)
    {
        $month = request('month')
            ? Carbon::parse(request('month'))
            : Carbon::now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [
                $startOfMonth->format('Y-m-d'),
                $endOfMonth->format('Y-m-d')
            ])
            ->with('breaks')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->format('Y-m-d');
            });

        $fileName = $user->name . '_' . $month->format('Y_m') . '_attendance.csv';

        $response = new StreamedResponse(function () use ($dates, $attendances) {
            $handle = fopen('php://output', 'w');

            // Excel文字化け対策
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($dates as $date) {
                $key = $date->format('Y-m-d');
                $attendance = $attendances[$key] ?? null;

                $start = $attendance && $attendance->start_time
                    ? Carbon::parse($attendance->start_time)->format('H:i')
                    : '';

                $end = $attendance && $attendance->end_time
                    ? Carbon::parse($attendance->end_time)->format('H:i')
                    : '';

                $breakTotalMinutes = 0;

                if ($attendance) {
                    foreach ($attendance->breaks as $break) {
                        if ($break->break_start && $break->break_end) {
                        $breakTotalMinutes += Carbon::parse($break->break_start)
                            ->diffInMinutes(Carbon::parse($break->break_end));
                        }
                    }
                }

                $breakTime = $breakTotalMinutes > 0
                    ? floor($breakTotalMinutes / 60) . ':' . str_pad($breakTotalMinutes % 60, 2, '0', STR_PAD_LEFT)
                    : '';

                $totalTime = '';

                if ($attendance && $attendance->start_time && $attendance->end_time) {
                $workMinutes = Carbon::parse($attendance->start_time)
                    ->diffInMinutes(Carbon::parse($attendance->end_time));

                $actualMinutes = $workMinutes - $breakTotalMinutes;

                $totalTime = floor($actualMinutes / 60) . ':' . str_pad($actualMinutes % 60, 2, '0', STR_PAD_LEFT);
                }

                fputcsv($handle, [
                    $date->isoFormat('MM/DD(ddd)'),
                    $start,
                    $end,
                    $breakTime,
                    $totalTime,
                ]);
            }

            fclose($handle);
        });

    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

    return $response;
    }
}