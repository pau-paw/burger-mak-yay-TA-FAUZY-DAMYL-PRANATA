@extends('layouts.app')
@section('title', $addon->exists ? 'Edit Topping' : 'Tambah Topping')
@section('content')
<a class="back" href="{{ route('admin.addons.index') }}">← Daftar topping</a>
<div class="page-heading">
    <h1>{{ $addon->exists ? 'Edit topping.' : 'Topping baru.' }}</h1>
</div>

<form class="panel stack form-width" method="POST" action="{{ $addon->exists ? route('admin.addons.update', $addon) : route('admin.addons.store') }}">
    @csrf
    @if($addon->exists)
        @method('PUT')
    @endif

    <div class="field">
        <label for="name">Nama topping / add-on</label>
        <input id="name" name="name" required maxlength="100" placeholder="Contoh: Extra Cheese Slice" value="{{ old('name', $addon->name) }}">
    </div>

    <div class="field">
        <label for="price">Harga tambahan (Rp)</label>
        <input id="price" name="price" type="number" min="0" max="1000000" step="500" required placeholder="5000" value="{{ old('price', $addon->price) }}">
        <p class="muted">Isi 0 jika gratis (misalnya: Pilihan Level Pedas).</p>
    </div>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $addon->is_active ?? true))>
        Aktifkan topping ini (bisa dipilih pelanggan saat memesan)
    </label>

    <button class="button" type="submit">{{ $addon->exists ? 'Simpan perubahan' : 'Tambah topping' }}</button>
</form>
@endsection
