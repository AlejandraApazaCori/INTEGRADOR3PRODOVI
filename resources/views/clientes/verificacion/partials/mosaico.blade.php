<div class="prodovi-mosaic">
    @for ($piece = 1; $piece <= 25; $piece++)
        <span class="mosaic-cell cell-{{ str_pad((string) $piece, 2, '0', STR_PAD_LEFT) }}"></span>
    @endfor
</div>
