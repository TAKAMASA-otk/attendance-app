@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="detail-container">

    <h2 class="title">｜勤怠詳細</h2>

    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
    @csrf

    @if(session('error'))
        <p class="error">{{ session('error') }}</p>
    @endif

    @if ($errors->any())
        <div class="error-box">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">

        <!-- 名前 -->
        <div class="row">
            <div class="label">名前</div>
            <div class="value">{{ optional($attendance->user)->name }}</div>
        </div>

        <!-- 日付 -->
        <div class="row">
            <div class="label">日付</div>
            <div class="value">
                {{ \Carbon\Carbon::parse($attendance->date)->format('Y年 n月j日') }}
            </div>
        </div>

        <!-- 出勤・退勤 -->
        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="time">

                @if($isPending)
                    <span class="text">
                        {{ $latestRequest && $latestRequest->requested_clock_in
                            ? \Carbon\Carbon::parse($latestRequest->requested_clock_in)->format('H:i')
                            : '--:--'
                        }}
                    </span>
                    <span class="tilde">〜</span>
                    <span class="text">
                        {{ $latestRequest && $latestRequest->requested_clock_out
                            ? \Carbon\Carbon::parse($latestRequest->requested_clock_out)->format('H:i')
                            : '--:--'
                        }}
                    </span>
                @else
                    <input type="time" name="start_time"
                        value="{{ old('start_time', optional($attendance->start_time) ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                    <span class="tilde">〜</span>
                    <input type="time" name="end_time"
                        value="{{ old('end_time', optional($attendance->end_time) ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                @endif

            </div>
        </div>

        <!-- 休憩 -->
        @php
            $displayBreaks = $isPending ? $correctionBreaks : $attendance->breaks;
        @endphp

        @forelse($displayBreaks as $index => $break)
            <div class="row">
                <div class="label">
                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                </div>

                <div class="time">
                    @if($isPending)
                        <span class="text">
                            {{ $break && $break->break_start
                            ? \Carbon\Carbon::parse($break->break_start)->format('H:i')
                            : '--:--' }}
                        </span>

                        <span class="tilde">〜</span>

                        <span class="text">
                            {{ $break && $break->break_end
                            ? \Carbon\Carbon::parse($break->break_end)->format('H:i')
                            : '--:--' }}
                        </span>
                    @else
                        <input type="time" name="break_start[]"
                            value="{{ old('break_start.' . $index, $break && $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">

                        <span class="tilde">〜</span>

                        <input type="time" name="break_end[]"
                            value="{{ old('break_end.' . $index, $break && $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                    @endif
                </div>
            </div>
        @empty
            <div class="row">
                <div class="label">休憩</div>

                <div class="time">
                    @if($isPending)
                        <span class="text">--:--</span>
                        <span class="tilde">〜</span>
                        <span class="text">--:--</span>
                    @else
                        <input type="time" name="break_start[]">
                        <span class="tilde">〜</span>
                        <input type="time" name="break_end[]">
                    @endif
                </div>
            </div>
        @endforelse

    @if(!$isPending && $displayBreaks->count() > 0)
        <div class="row">
            <div class="label">休憩{{ $displayBreaks->count() + 1 }}</div>

            <div class="time">
                <input type="time" name="break_start[]">
                <span class="tilde">〜</span>
                <input type="time" name="break_end[]">
            </div>
        </div>
    @endif

        <!-- 備考 -->
        <div class="row note-row">
            <div class="label">備考</div>
            <div class="value note-value">
                @if($isPending)
                    <p>{{ $latestRequest->reason }}</p>
                @else
                    <textarea name="reason">{{ old('reason', $attendance->reason ?? '') }}</textarea>
                @endif
            </div>
        </div>
    </div>

    @if($isPending)
    <div class="pending-wrap">
        <p class="pending-text">
            ※承認待ちのため修正はできません。
        </p>
    </div>
    @endif

    @if(!$isPending)
    <div class="btn-area">
        <button type="submit" class="btn">修正</button>
    </div>
    @endif

    </form>

</div>

@endsection