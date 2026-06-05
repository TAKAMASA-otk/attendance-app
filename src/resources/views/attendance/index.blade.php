@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-center">

    {{-- ステータス --}}
    <div class="status
        @if($status === '勤務外') status-off
        @elseif($status === '出勤中') status-working
        @elseif($status === '休憩中') status-break
        @elseif($status === '退勤済') status-done
        @endif
    ">
        {{ $status }}
    </div>

    {{-- 日付 --}}
    @php
        $now = \Carbon\Carbon::now('Asia/Tokyo');
        $weeks = ['日','月','火','水','木','金','土'];
    @endphp

    {{-- 日付 --}}
    <div class="date">
        {{ $now->format('Y年n月j日') }}
        ({{ $weeks[$now->dayOfWeek] }})
    </div>

    {{-- 時刻 --}}
    <div class="clock" id="clock"></div>

    {{-- ボタン --}}
    <div class="button-group">

        {{-- 勤務外 --}}
        @if($status === '勤務外')
            <form method="POST" action="{{ route('attendance.start') }}">
                @csrf
                <button class="main-btn">出勤</button>
            </form>
        @endif

        {{-- 出勤中 --}}
        @if($status === '出勤中')
            <form method="POST" action="{{ route('attendance.end') }}">
                @csrf
                <button class="main-btn">退勤</button>
            </form>

            <form method="POST" action="{{ route('attendance.break.start') }}">
                @csrf
                <button class="sub-btn">休憩入</button>
            </form>
        @endif

        {{-- 休憩中 --}}
        @if($status === '休憩中')
            <form method="POST" action="{{ route('attendance.break.end') }}">
                @csrf
                <button class="sub-btn">休憩戻</button>
            </form>
        @endif
    </div>

    {{-- 退勤済 --}}
    @if($status === '退勤済')
        <p class="done-message">お疲れ様でした。</p>
    @endif

    {{-- メッセージ --}}
    @if(session('message'))
        <div class="message success">
            {{ session('message') }}
        </div>
    @endif

</div>

{{-- 時計 --}}
<script>
function updateClock() {
    const now = new Date();

    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');

    document.getElementById('clock').textContent = `${hh}:${mm}`;
}

updateClock();
setInterval(updateClock, 1000);
</script>

@endsection