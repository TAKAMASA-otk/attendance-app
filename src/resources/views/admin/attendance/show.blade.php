@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="attendance-container">
    <h2 class="title">｜勤怠詳細</h2>

    {{-- エラーメッセージ --}}
    @if($errors->any())
        <div style="color:red; margin-bottom:10px;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- 承認待ちメッセージ --}}
    @if($attendance->status === 'pending')
        <p style="color:red;">承認待ちのため修正はできません。</p>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
            @csrf

            {{-- 名前 --}}
            <div class="row">
                <div class="label">名前</div>
                <div class="value">{{ $attendance->user->name }}</div>
            </div>

            {{-- 日付 --}}
            <div class="row">
                <div class="label">日付</div>
                <div class="value">
                    {{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="value">
                    <input type="time" name="start_time"
                        value="{{ optional($attendance->start_time)->timezone('Asia/Tokyo')->format('H:i') }}"
                        {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="end_time"
                        value="{{ optional($attendance->end_time)->timezone('Asia/Tokyo')->format('H:i') }}"
                        {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                </div>
            </div>

            {{-- 休憩 --}}
            @forelse($attendance->breaks as $index => $break)
                <div class="row">
                    <div class="label">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </div>
                    <div class="value">
                        <input type="time" name="break_start[]"
                            value="{{ old('break_start.' . $index, $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}"
                            {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                        〜
                        <input type="time" name="break_end[]"
                            value="{{ old('break_end.' . $index, $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}"
                            {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                    </div>
                </div>
            @empty
                <div class="row">
                    <div class="label">休憩</div>
                    <div class="value">
                        <input type="time" name="break_start[]"
                            {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                        〜
                        <input type="time" name="break_end[]"
                            {{ $attendance->status === 'pending' ? 'disabled' : '' }}>
                    </div>
                </div>
            @endforelse

            @if($attendance->status !== 'pending' && $attendance->breaks->count() > 0)
                <div class="row">
                    <div class="label">休憩{{ $attendance->breaks->count() + 1 }}</div>
                    <div class="value">
                        <input type="time" name="break_start[]">
                        〜
                        <input type="time" name="break_end[]">
                    </div>
                </div>
            @endif

            {{-- 備考 --}}
            <div class="row">
                <div class="label">備考</div>
                <div class="value">
                    <textarea name="note" rows="3"
                        {{ $attendance->status === 'pending' ? 'disabled' : '' }}>{{ old('note', $attendance->note ?? '') }}</textarea>
                </div>
            </div>

            {{-- 修正ボタン --}}
            @if($attendance->status !== 'pending')
                <div class="button-area">
                    <button type="submit" class="btn">修正</button>
                </div>
            @endif

        </form>
    </div>
</div>

@endsection