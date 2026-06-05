@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')

<div class="login-container">

    <div class="login-box">
        <h2 class="login-title">ログイン</h2>

        <form method="POST" action="/login">
            @csrf

            <div class="form-group">
                <label>メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}">

                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password">

                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="login-button">
                ログインする
            </button>

            <div class="register-link">
                <a href="/register">会員登録はこちら</a>
            </div>

        </form>
    </div>

</div>

@endsection