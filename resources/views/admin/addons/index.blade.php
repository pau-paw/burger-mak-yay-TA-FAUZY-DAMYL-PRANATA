@extends('layouts.app')
@section('title', 'Kelola Topping / Add-on')
@section('content')
<div class="section-heading page-heading">
    <div>
        <span class="eyebrow">KUSTOMISASI & TOPPING</span>
        <h1>Topping & Add-on menu.</h1>
    </div>
    <a class="button" href="{{ route('admin.addons.create') }}">+ Topping baru</a>
</div>

<div class="panel table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nama Topping</th>
                <th>Harga Tambahan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($addons as $addon)
            <tr>
                <td><strong>{{ $addon->name }}</strong></td>
                <td>Rp {{ number_format($addon->price, 0, ',', '.') }}</td>
                <td>
                    <span class="badge {{ $addon->is_active ? 'completed' : 'cancelled' }}">
                        {{ $addon->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td>
                    <div class="row">
                        <a class="text-link" href="{{ route('admin.addons.edit', $addon) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.addons.destroy', $addon) }}" data-confirm="Hapus topping ini?">
                            @csrf
                            @method('DELETE')
                            <button class="link-button danger" type="submit">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">Belum ada topping. Tambahkan topping pertama untuk menu burger.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('partials.pagination', ['paginator' => $addons])
@endsection
