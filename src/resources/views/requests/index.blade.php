@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/requests.css') }}">
@endsection

@section('content')

<div class="request-container">

    <h2 class="page-title">申請一覧</h2>

    <!-- タブ -->
    <div class="tabs">
        <button class="tab active" data-tab="pending">承認待ち</button>
        <button class="tab" data-tab="approved">承認済み</button>
    </div>

    <!-- 承認待ち -->
    <div class="table-card tab-content active" id="pending">
        <table class="request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach($pendingRequests as $request)
                <tr>
                    <td class="status">承認待ち</td>
                    <td>{{ $request->user->name ?? '-' }}</td>
                    <td>
                    {{ optional($request->attendance)->date
                        ? \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d')
                        : '-'
                    }}
                    </td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td class="detail">
                        <a href="{{ route('attendance.show', $request->attendance->id) }}">
                        詳細
                    </a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- 承認済み -->
    <div class="table-card tab-content" id="approved">
        <table class="request-table">

            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($approvedRequests as $request)
                <tr>
                    <td class="status">承認済み</td>
                    <td>{{ $request->user->name ?? '-' }}</td>
                    <td>
                    {{ optional($request->attendance)->date
                        ? \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d')
                        : '-'
                    }}
                    </td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td class="detail">
                        <a href="{{ route('attendance.show', $request->attendance->id) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {

            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });

});
</script>
@endpush
