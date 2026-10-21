@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<section class="auth-card"><span class="eyebrow"></span><h1>Sudah lapar?</h1>
<form method="POST" action="{{ route('login.store') }}" class="stack">@csrf
    <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" maxlength="255"></div>
    <div class="field"><label for="password">Kata sandi</label><input id="password" type="password" name="password" required autocomplete="current-password" maxlength="128"></div>
    <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini</label>
    <button class="button full" type="submit">Masuk →</button>
</form><p class="auth-foot">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p></section>
@endsection
