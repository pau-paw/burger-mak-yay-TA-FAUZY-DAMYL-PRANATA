@extends('layouts.app')
@section('title', 'Kelola kategori')
@section('content')
<div class="section-heading page-heading"><div><span class="eyebrow">KELOMPOKKAN FAVORIT MEREKA</span><h1>Kategori menu.</h1></div><a class="button" href="{{ route('admin.categories.create') }}">+ Kategori</a></div>
<div class="panel table-wrap"><table><thead><tr><th>Nama</th><th>Deskripsi</th><th>Menu</th><th>Aksi</th></tr></thead><tbody>@forelse($categories as $category)<tr><td><strong>{{ $category->name }}</strong></td><td>{{ $category->description ?: '—' }}</td><td>{{ $category->products_count }}</td><td><div class="row"><a class="text-link" href="{{ route('admin.categories.edit', $category) }}">Edit</a><form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Hapus kategori ini? Kategori yang masih digunakan tidak bisa dihapus.">@csrf @method('DELETE')<button class="link-button danger" type="submit">Hapus</button></form></div></td></tr>@empty<tr><td colspan="4">Belum ada kategori. Tambahkan kategori pertama.</td></tr>@endforelse</tbody></table></div>
@include('partials.pagination', ['paginator' => $categories])
@endsection
