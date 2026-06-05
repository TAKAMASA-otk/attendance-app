@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="request-container">

    <h2 class="title">申請一覧</h2>

    {{-- タブ --}}
    <div class="tabs">
        <a href="?status=pending" class="{{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="?status=approved" class="{{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    {{-- テーブル --}}
    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $req)
            <tr>
                <td>{{ $req->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                <td>{{ $req->user->name ?? '' }}</td>
                <td>{{ \Carbon\Carbon::parse($req->attendance->date)->format('Y/m/d') }}</td>
                <td>{{ $req->reason }}</td>
                <td>{{ $req->created_at->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('attendance.show', $req->attendance->id) }}">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection