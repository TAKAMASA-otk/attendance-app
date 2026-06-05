@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<h2>勤怠一覧</h2>

<div class="attendance-container">

    <div class="attendance-header">
        <a href="{{ route('attendance.list', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}">
            ← 前月
        </a>

        <span class="attendance-month">{{ $month->format('Y/m') }}</span>

        <a href="{{ route('attendance.list', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}">
            翌月 →
        </a>
    </div>

    <div class="table-wrapper">
        <table class="attendance-table">
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>

            @foreach ($dates as $date)
            @php
                $attendance = $attendances[$date->format('Y-m-d')] ?? null;

                // =========================
                // 休憩時間
                // =========================
                $breakSeconds = 0;

                if ($attendance) {
                    foreach ($attendance->breaks as $break) {

                        if ($break->break_start) {
                            $start = \Carbon\Carbon::parse($break->break_start);
                            $end = $break->break_end
                                ? \Carbon\Carbon::parse($break->break_end)
                                : now();

                            $breakSeconds += $end->diffInSeconds($start);
                        }
                    }
                }

                $breakTime = $breakSeconds > 0 ? gmdate('H:i', $breakSeconds) : '';

                // =========================
                // 合計勤務時間
                // =========================
                $workTime = '';

                if ($attendance && $attendance->start_time && $attendance->end_time) {

                    $start = \Carbon\Carbon::parse($attendance->start_time);
                    $end = \Carbon\Carbon::parse($attendance->end_time);

                    $workSeconds = $end->diffInSeconds($start) - $breakSeconds;

                    // マイナス防止
                    if ($workSeconds < 0) {
                        $workSeconds = 0;
                    }

                    $workTime = gmdate('H:i', $workSeconds);
                }
            @endphp

            <tr>
                <td>{{ $date->format('m/d') }}</td>

                <td>
                    {{ $attendance && $attendance->start_time
                        ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i')
                        : '' }}
                </td>

                <td>
                    {{ $attendance && $attendance->end_time
                        ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i')
                        : '' }}
                </td>

                <td>{{ $breakTime }}</td>

                <td>{{ $workTime }}</td>

                <td>
                    @if ($attendance)
                        <a href="{{ route('attendance.show', $attendance->id) }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach

        </table>
    </div>
</div>

@endsection