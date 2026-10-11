@if ($paginator->hasPages())
    <nav class="mt-4 flex items-center justify-between gap-4 text-sm" aria-label="Navigasi halaman">
        <p class="text-redup">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ number_format($paginator->total(), 0, ',', '.') }}
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary pointer-events-none opacity-50" aria-disabled="true">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary">Sebelumnya</a>
            @endif

            <span class="px-1 text-redup">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary">Berikutnya</a>
            @else
                <span class="btn btn-secondary pointer-events-none opacity-50" aria-disabled="true">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
