@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mb-0">
            {{-- Previous Page Link --}}
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link rounded-circle d-flex align-items-center justify-content-center" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous" {{ $paginator->onFirstPage() ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link rounded-circle d-flex align-items-center justify-content-center">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link rounded-circle d-flex align-items-center justify-content-center" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link rounded-circle d-flex align-items-center justify-content-center" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next" {{ !$paginator->hasMorePages() ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>

    {{-- Hiển thị thông tin trang --}}
    <div class="text-center text-muted small mt-2">
        Hiển thị {{ $paginator->firstItem() }} đến {{ $paginator->lastItem() }} trong {{ $paginator->total() }} kết quả
    </div>
@endif