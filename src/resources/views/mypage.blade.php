@extends('layouts.app')

@section('title', 'マイページ')

@section('content')

<div class="mypage-container">

    <h2>マイページ</h2>

    <div class="user-info">
        <p>名前：{{ Auth::user()->name }}</p>
        <p>メール：{{ Auth::user()->email }}</p>
    </div>

    <form method="POST" action="/logout">
        @csrf
        <button type="submit">ログアウト</button>
    </form>

</div>

@endsection