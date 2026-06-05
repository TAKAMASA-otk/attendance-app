@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')

<div class="register-container">

    <div class="register-title">会員登録</div>

    @if ($errors->any())
        <div class="error-box">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/register">
        @csrf

        <div class="form-group">
            <label>お名前</label>
            <input type="text" name="name">
        </div>

        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email">
        </div>

        <div class="form-group">
            <label>パスワード</label>
            <input type="password" name="password">
        </div>

        <div class="form-group">
            <label>確認用パスワード</label>
            <input type="password" name="password_confirmation">
        </div>

        <button type="submit" class="register-button">
            登録する
        </button>

        <div style="text-align:center; margin-top:10px;">
            <a href="/login">ログインはこちら</a>
        </div>
    </form>

</div>

@endsection