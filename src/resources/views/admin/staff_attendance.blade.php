@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance.css') }}">
@endsection

@section('content')

<div class="attendance-container">

    <h2 class="attendance-title">{{ $user->name }}さんの勤怠</h2>

    <div class="month-nav">
        <a href="{{ route('admin.staff.attendance', ['user' => $user->id, 'month' => $month->copy()->subMonth()->format('Y-m')]) }}">
            ← 前月
        </a>

        <div class="current-month">
            {{ $month->format('Y/m') }}
        </div>

        <a href="{{ route('admin.staff.attendance', ['user' => $user->id, 'month' => $month->copy()->addMonth()->format('Y-m')]) }}">
            翌月 →
        </a>
    </div>

    <table class="attendance-table">
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>

        @foreach($dates as $date)
            @php
                $key = $date->format('Y-m-d');
                $attendance = $attendances[$key] ?? null;

                $breakTotalMinutes = 0;

                if ($attendance) {
                    foreach ($attendance->breaks as $break) {
                        if ($break->break_start && $break->break_end) {
                            $breakTotalMinutes += \Carbon\Carbon::parse($break->break_start)
                                ->diffInMinutes(\Carbon\Carbon::parse($break->break_end));
                        }
                    }
                }

                $breakTime = $breakTotalMinutes > 0
                    ? floor($breakTotalMinutes / 60) . ':' . str_pad($breakTotalMinutes % 60, 2, '0', STR_PAD_LEFT)
                    : '';

                $totalTime = '';

                if ($attendance && $attendance->start_time && $attendance->end_time) {
                    $workMinutes = \Carbon\Carbon::parse($attendance->start_time)
                        ->diffInMinutes(\Carbon\Carbon::parse($attendance->end_time));

                    $actualMinutes = $workMinutes - $breakTotalMinutes;

                    $totalTime = floor($actualMinutes / 60) . ':' . str_pad($actualMinutes % 60, 2, '0', STR_PAD_LEFT);
                }
            @endphp

            <tr>
                <td>{{ $date->isoFormat('MM/DD(ddd)') }}</td>
                <td>
                    {{ $attendance && $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}
                </td>
                <td>
                    {{ $attendance && $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}
                </td>
                <td>{{ $breakTime }}</td>
                <td>{{ $totalTime }}</td>
                <td>
                    @if($attendance)
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-link">
                            詳細
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <div class="csv-area">
        <a href="{{ route('admin.staff.attendance.csv', ['user' => $user->id, 'month' => $month->format('Y-m')]) }}" class="csv-button">
            CSV出力
        </a>
    </div>

</div>

@endsection