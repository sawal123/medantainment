<iframe width="200"
    height="100"
    src="{{ $getState() }}"
    style="border:0;"
    title="{{ $getRecord()->name ?? 'Video Preview' }}"
    loading="lazy"
    referrerpolicy="strict-origin-when-cross-origin"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
    sandbox="allow-scripts allow-same-origin allow-popups allow-presentation"
    allowfullscreen>
</iframe>
