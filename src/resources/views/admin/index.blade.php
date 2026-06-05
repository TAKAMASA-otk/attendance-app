@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')

<div class="admin-container">

    <h2 class="admin-title">
        {{ $date->format('Y年n月j日') }}の勤怠
    </h2>

    {{-- 日付ナビ --}}
    <div class="date-nav">
        <a href="{{ route('admin.index', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}">
            ← 前日
        </a>

        <div class="current-date">
            <span class="calendar-icon">▣</span>
            {{ $date->format('Y/m/d') }}
        </div>

        <a href="{{ route('admin.index', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}">
            翌日 →
        </a>
    </div>

    {{-- テーブル --}}
    <table class="admin-table">
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>

        @foreach($users as $user)

            @if($user->is_admin)
                @continue
            @endif

            @php
                $attendance = $attendances[$user->id] ?? null;
                $breakSeconds = 0;
            @endphp

            <tr>
                <td>{{ $user->name }}</td>

                <td>
                    {{ $attendance && $attendance->start_time
                        ? \Carbon\Carbon::parse($attendance->start_time)->timezone('Asia/Tokyo')->format('H:i')
                        : '' }}
                </td>

                <td>
                    {{ $attendance && $attendance->end_time
                        ? \Carbon\Carbon::parse($attendance->end_time)->timezone('Asia/Tokyo')->format('H:i')
                        : '' }}
                </td>

                <td>
                    @if($attendance)
                        @php
                            foreach ($attendance->breaks as $break) {
                                if ($break->break_start) {
                                    $start = \Carbon\Carbon::parse($break->break_start)->timezone('Asia/Tokyo');
                                    $end = $break->break_end
                                        ? \Carbon\Carbon::parse($break->break_end)->timezone('Asia/Tokyo')
                                        : now()->timezone('Asia/Tokyo');

                                    $breakSeconds += $end->diffInSeconds($start);
                                }
                            }

                            $breakTime = gmdate('H:i', $breakSeconds);
                        @endphp

                        {{ $breakSeconds > 0 ? $breakTime : '' }}
                    @endif
                </td>

                <td>
                    @if($attendance && $attendance->start_time && $attendance->end_time)
                        @php
                            $start = \Carbon\Carbon::parse($attendance->start_time)->timezone('Asia/Tokyo');
                            $end = \Carbon\Carbon::parse($attendance->end_time)->timezone('Asia/Tokyo');

                            $workSeconds = $end->diffInSeconds($start) - $breakSeconds;
                            $workTime = gmdate('H:i', $workSeconds);
                        @endphp

                        {{ $workTime }}
                    @endif
                </td>

                <td>
                    @if($attendance)
                        <a class="detail-link" href="{{ route('admin.attendance.show', $attendance->id) }}">
                            詳細
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach

    </table>

</div>

@endsection