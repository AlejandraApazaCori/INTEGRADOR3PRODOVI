<nav class="flex flex-col sm:flex-row items-center justify-between gap-4" role="navigation" aria-label="Paginación de usuarios">
    <p class="text-sm text-gray-600">
        @if($paginator->total() > 0)
            Mostrando <strong>{{ $paginator->firstItem() }}</strong> a <strong>{{ $paginator->lastItem() }}</strong> de <strong>{{ $paginator->total() }}</strong> resultados
        @else
            No se encontraron resultados
        @endif
    </p>

    @if($paginator->hasPages())
        <div class="inline-flex items-center gap-1">
            @if($paginator->onFirstPage())
                <span class="px-3 py-2 rounded-lg text-sm text-gray-400 bg-gray-100">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-2 rounded-lg text-sm font-semibold" style="color:#638524;background:#edf4e4;">Anterior</a>
            @endif

            @foreach($elements as $element)
                @if(is_string($element))
                    <span class="px-2 py-2 text-gray-400">{{ $element }}</span>
                @endif
                @if(is_array($element))
                    @foreach($element as $page => $url)
                        @if($page == $paginator->currentPage())
                            <span class="w-9 h-9 inline-grid place-items-center rounded-lg text-sm font-bold text-white" style="background:#7da533;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="w-9 h-9 inline-grid place-items-center rounded-lg text-sm font-semibold text-gray-600 hover:bg-gray-100">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-2 rounded-lg text-sm font-semibold" style="color:#638524;background:#edf4e4;">Siguiente</a>
            @else
                <span class="px-3 py-2 rounded-lg text-sm text-gray-400 bg-gray-100">Siguiente</span>
            @endif
        </div>
    @endif
</nav>
