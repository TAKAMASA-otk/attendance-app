@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')

<div class="login-container">
    <div class="login-card">

        <h2 class="login-title">管理者ログイン</h2>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="form-group">
                <label>メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}">

                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password">

                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <button class="login-button">管理者ログインする</button>

        </form>

    </div>
</div>

@endsection