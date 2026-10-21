@extends('layouts.app')
@section('title', $category->exists ? 'Edit kategori' : 'Tambah kategori')
@section('content')
<a class="back" href="{{ route('admin.categories.index') }}">← Daftar kategori</a><div class="page-heading"><h1>{{ $category->exists ? 'Edit kategori.' : 'Kategori baru.' }}</h1></div>
<form class="panel stack form-width" method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">@csrf @if($category->exists) @method('PUT') @endif
<div class="field"><label for="name">Nama kategori</label><input id="name" name="name" required maxlength="100" value="{{ old('name', $category->name) }}"></div>
<div class="field"><label for="description">Deskripsi (opsional)</label><textarea id="description" name="description" rows="3" maxlength="500">{{ old('description', $category->description) }}</textarea></div><button class="button" type="submit">Simpan kategori</button></form>
@endsection
