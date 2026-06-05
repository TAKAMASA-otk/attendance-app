@extends('layouts.app')

@section('title', 'メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')

<div class="verify-container">

    <div class="verify-box">

        <p class="verify-text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <!-- 認証ページへ（メールリンク用の説明） -->
        <div class="verify-button-wrapper">
            <a href="#" class="verify-button">
                認証はこちらから
            </a>
        </div>

        <!-- 再送 -->
        <div class="resend-wrapper">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="resend-link">
                    認証メールを再送する
                </button>
            </form>
        </div>

    </div>

</div>

@endsection