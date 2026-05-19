@if ($paginator->hasPages())
    <div class="table-pagination">
        <div class="flex items-center justify-between">
            <div class="pagination">
                @if (!$paginator->onFirstPage())
                    <li class="page-item">                 
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                            {{__('Previous')}}
                        </a>
                    </li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                    <li class="page-item">
                        <a class="page-link disabled" href="javascript:void(0);">{{ $element }}</a>
                    </li>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <a class="page-link"><span>{{ $page }}</span></a>
                            </li>
                            @else
                            <li class="page-item">                             
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}">
                            {{__('Next')}}
                        </a>
                    </li>
                @endif
            </div>
        </div>
    </div>
@endif 