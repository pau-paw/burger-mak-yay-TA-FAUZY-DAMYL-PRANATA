@extends('layouts.app')
@section('title', 'Daftar pelanggan')
@section('content')
<section class="auth-card"><h1>Mulai dari sini.</h1><p class="muted">Buat akun untuk pesanan pertamamu.</p>
<form method="POST" action="{{ route('register.store') }}" class="stack">@csrf
    <div class="field"><label for="name">Nama lengkap</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"></div>
    <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"></div>
    <div class="field"><label for="password">Kata sandi</label><input id="password" type="password" name="password" required minlength="10" maxlength="128" autocomplete="new-password" aria-describedby="password-help"><small id="password-help">Minimal 10 karakter, mengandung huruf dan angka.</small></div>
    <div class="field"><label for="password_confirmation">Ulangi kata sandi</label><input id="password_confirmation" type="password" name="password_confirmation" required minlength="10" maxlength="128" autocomplete="new-password"></div>
    <button class="button full" type="submit">Buat akun pelanggan →</button>
</form><p class="auth-foot">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p></section>
@endsection
