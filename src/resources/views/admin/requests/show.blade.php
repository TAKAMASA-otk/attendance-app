@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-container">

    <h2 class="title">｜勤怠詳細</h2>

    <div class="card">

        {{-- 名前 --}}
        <div class="row">
            <div class="label">名前</div>
            <div class="value">
                {{ $correction->user->name }}
            </div>
        </div>

        {{-- 日付 --}}
        <div class="row">
            <div class="label">日付</div>

            <div class="value">
                {{ \Carbon\Carbon::parse($correction->attendance->date)->format('Y年') }}

                &nbsp;&nbsp;&nbsp;&nbsp;

                {{ \Carbon\Carbon::parse($correction->attendance->date)->format('n月j日') }}
            </div>
        </div>

        {{-- 出勤退勤 --}}
        <div class="row">
            <div class="label">出勤・退勤</div>

            <div class="value">

                <input type="time"
                    value="{{ $correction->requested_clock_in ? \Carbon\Carbon::parse($correction->requested_clock_in)->format('H:i') : '' }}"
                    disabled>

                〜

                <input type="time"
                    value="{{ $correction->requested_clock_out ? \Carbon\Carbon::parse($correction->requested_clock_out)->format('H:i') : '' }}"
                    disabled>

            </div>
        </div>

        {{-- 休憩 --}}
        @forelse($correction->correctionBreakTimes as $index => $break)
            <div class="row">
                <div class="label">
                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                </div>

                <div class="value">
                    <input type="time"
                        value="{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}"
                        disabled>

                    〜

                    <input type="time"
                        value="{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}"
                        disabled>
                </div>
            </div>
        @empty
            <div class="row">
                <div class="label">休憩</div>

                <div class="value">
                    <input type="time" disabled>
                    〜
                    <input type="time" disabled>
                </div>
            </div>
        @endforelse

        {{-- 備考 --}}
        <div class="row">
            <div class="label">備考</div>

            <div class="value">
                <textarea rows="3" disabled>{{ $correction->reason }}</textarea>
            </div>
        </div>

    <div class="button-area">

        @if($correction->status === 'pending')

            <form method="POST"
                action="{{ route('admin.requests.approve',$correction->id) }}">

                @csrf

                <button class="btn">
                    承認
                </button>

            </form>

        @else

            <button class="btn" disabled>
                承認済み
            </button>

        @endif

    </div>

</div>

@endsection