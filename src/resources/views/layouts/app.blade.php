<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', '勤怠アプリ')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
    <div class="header-logo">
        <a href="/">
            <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
        </a>
    </div>

    @if(Auth::check())
        <nav class="header-nav">
            @if(Auth::user()->is_admin == 1)
                <a href="{{ route('admin.index') }}">勤怠一覧</a>
                <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
                <a href="{{ route('admin.requests.index') }}">申請一覧</a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
            @else
                <a href="{{ route('attendance.index') }}">勤怠</a>
                <a href="{{ route('attendance.list') }}">勤怠一覧</a>
                <a href="{{ route('requests.index') }}">申請一覧</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
            @endif
        </nav>
    @endif
</header>

    <main>
        @yield('content')
    </main>
    @stack('scripts')
</body>

</html>