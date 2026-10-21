@extends('layouts.app')
@section('title', 'Kelola pesanan')
@section('content')
<div class="page-heading"><span class="eyebrow">DARI PESAN KE SIAP MAKAN</span><h1>Pesanan masuk.</h1></div>
<form class="filter-bar" method="GET" action="{{ route('admin.orders.index') }}"><div class="field grow"><label for="status">Filter status</label><select id="status" name="status"><option value="">Semua status</option>@foreach(\App\Models\Order::STATUSES as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div><button class="button" type="submit">Tampilkan</button></form>
<section class="panel">@include('admin.orders.table')</section>@include('partials.pagination', ['paginator' => $orders])
@endsection
