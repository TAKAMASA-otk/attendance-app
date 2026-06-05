@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_staff.css') }}">
@endsection

@section('content')

<div class="staff-container">

    <h2 class="staff-title">スタッフ一覧</h2>

    <div class="staff-table-wrapper">
        <table class="staff-table">
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>

            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <a href="{{ route('admin.staff.attendance', $user->id) }}" class="detail-link">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>

</div>

@endsection