@extends('layouts.app')
@section('title', $product->exists ? 'Edit menu' : 'Tambah menu')
@section('content')
<a class="back" href="{{ route('admin.products.index') }}">← Daftar menu</a><div class="page-heading"><h1>{{ $product->exists ? 'Edit menu.' : 'Racikan baru.' }}</h1><p class="muted">Harga dalam rupiah utuh. Stok berkurang saat checkout berhasil.</p></div>
@if($categories->isEmpty())<div class="alert error">Buat kategori terlebih dahulu. <a href="{{ route('admin.categories.create') }}">Tambah kategori →</a></div>@endif
<form class="panel stack form-width" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">@csrf @if($product->exists) @method('PUT') @endif
@if($product->exists)<input type="hidden" name="revision" value="{{ old('revision', $product->revision) }}">@endif
<div class="field"><label for="name">Nama menu</label><input id="name" name="name" required maxlength="150" value="{{ old('name', $product->name) }}"></div>
<div class="field"><label for="category_id">Kategori</label><select id="category_id" name="category_id" required><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
<div class="field"><label for="description">Deskripsi menu</label><textarea id="description" name="description" required rows="4" maxlength="3000">{{ old('description', $product->description) }}</textarea></div>
<div class="form-grid"><div class="field"><label for="price">Harga (Rp)</label><input id="price" type="number" name="price" required min="1000" max="10000000" step="1" value="{{ old('price', $product->price) }}"></div><div class="field"><label for="stock">Stok tersedia (total baru)</label><input id="stock" type="number" name="stock" required min="0" max="1000000" step="1" value="{{ old('stock', $product->stock) }}"></div></div>
<div class="field"><label for="image">Foto menu (opsional)</label>@if($product->image_path)<img class="edit-image" src="{{ $product->image_url }}" alt="Foto saat ini">@endif<input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" aria-describedby="image-help"><small id="image-help">JPG, PNG, atau WebP. Maksimal 2 MB dan 4096 × 4096 piksel. Kosongkan untuk mempertahankan foto. SVG tidak diterima.</small></div>
<div class="field"><label for="is_active">Tampilkan di katalog</label><select id="is_active" name="is_active" required><option value="1" @selected(old('is_active', $product->is_active) == 1)>Aktif</option><option value="0" @selected(old('is_active', $product->is_active) == 0)>Nonaktif</option></select></div>
<button class="button" type="submit" @disabled($categories->isEmpty())>Simpan menu</button></form>
@endsection
