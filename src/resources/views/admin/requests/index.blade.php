@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-request.css') }}">
@endsection

@section('content')

<div class="request-container">

    <h2 class="request-title">
        申請一覧
    </h2>

    <div class="request-tabs">

        <a
        href="{{ route('admin.requests.index',['status'=>'pending']) }}"
        class="{{ $status=='pending' ? 'active' : '' }}"
        >
            承認待ち
        </a>

        <a
        href="{{ route('admin.requests.index',['status'=>'approved']) }}"
        class="{{ $status=='approved' ? 'active' : '' }}"
        >
            承認済み
        </a>

    </div>

    <table class="request-table">

        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>

        @foreach($requests as $item)

        <tr>

            <td>
                {{ $item->status=='pending'
                    ? '承認待ち'
                    : '承認済み'
                }}
            </td>

            <td>
                {{ optional($item->user)->name }}
            </td>

            <td>
                {{ optional($item->attendance)->date
                    ? \Carbon\Carbon::parse(
                        $item->attendance->date
                    )->format('Y/m/d')
                    : ''
                }}
            </td>

            <td>
                {{ $item->reason }}
            </td>

            <td>
                {{ $item->created_at->format('Y/m/d') }}
            </td>

            <td>

                <a href="{{ route('admin.requests.show', $item->id) }}">詳細</a>

            </td>

        </tr>

        @endforeach

    </table>

</div>

@endsection