@if ($paginator->hasPages())
<nav class="attendance-pagination d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">

    {{-- معلومات السجلات --}}
    <div class="pagination-info text-muted small">
        عرض
        <strong>{{ $paginator->firstItem() }}</strong>
        إلى
        <strong>{{ $paginator->lastItem() }}</strong>
        من أصل
        <strong>{{ $paginator->total() }}</strong>
        سجل
    </div>

    {{-- أزرار التنقل --}}
    <ul class="pagination pagination-sm mb-0">

        {{-- الأول --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link"><i class="fas fa-angle-double-left"></i></span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url(1) }}"><i class="fas fa-angle-double-left"></i></a>
            </li>
        @endif

        {{-- السابق --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link"><i class="fas fa-angle-left"></i></span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}"><i class="fas fa-angle-left"></i></a>
            </li>
        @endif

        {{-- أرقام الصفحات --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- التالي --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}"><i class="fas fa-angle-right"></i></a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link"><i class="fas fa-angle-right"></i></span>
            </li>
        @endif

        {{-- الأخير --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}"><i class="fas fa-angle-double-right"></i></a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link"><i class="fas fa-angle-double-right"></i></span>
            </li>
        @endif

    </ul>
</nav>
@endif
