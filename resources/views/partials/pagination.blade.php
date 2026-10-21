@if($paginator->hasPages())
<nav class="pagination" aria-label="Halaman daftar">
    @if($paginator->onFirstPage())<span>Sebelumnya</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Sebelumnya</a>@endif
    <span>Halaman {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
    @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Selanjutnya →</a>@else<span>Selanjutnya</span>@endif
</nav>
@endif
