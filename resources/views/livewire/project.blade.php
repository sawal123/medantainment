<div>
    @php
        $selectedSeries = $selectedSeries ?? null;
        $selectedCategory = $selectedCategory ?? 'all';
        $categoryFilm = $categoryFilm ?? collect();
        $firstCategory = $firstCategory ?? null;
        $films = $films ?? collect();
        $mixedItems = $mixedItems ?? collect(
            $films->map(fn ($film) => [
                'content_type' => 'standalone_project',
                'id' => $film->id,
                'urutan' => $film->urutan,
                'model' => $film,
            ]),
        );
    @endphp

    <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
    <main class="main position-relative overflow-hidden" id="mains">
        <section class="hero-section-version1 bnbg position-relative">
            <div class="container">
                <div class="my-2">
                    <button type="button" wire:click="Clickfilm('all')"
                        class="btn border {{ $selectedCategory == 'all' ? 'btn-primary' : 'btn-dark' }}">
                        All
                    </button>
                    @foreach ($categoryFilm as $item)
                        <button type="button" wire:click="Clickfilm('{{ $item->slug }}')"
                            class="btn border {{ $selectedCategory == $item->slug ? 'btn-primary' : 'btn-dark' }} my-1">
                            {{ $item->name }}
                        </button>
                    @endforeach
                </div>

                @if (! empty($selectedSeries))
                    <div class="mt-2">
                        <h2 class="h4">{{ $selectedSeries->name }}</h2>
                        <p>{{ $selectedSeries->description }}</p>
                    </div>
                @elseif (! empty($firstCategory))
                    <p class="mt-2">
                        {{ $firstCategory->deskripsi }}
                    </p>
                @endif

                @if (! empty($selectedSeries))
                    <div class="row g-xxl-7 g-xl-6 g-4 mt-2">
                        @foreach ($films as $item)
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="blog-widget-item">
                                    <div class="thumb w-100 overflow-hidden rounded-md">
                                        <iframe src="{{ $item->link }}" class="w-100 overflow-hidden" height="250"
                                            style="border:1px solid #ccc;border-radius: 10px;"
                                            title="{{ $item->name ?? 'Video Project' }}" loading="lazy"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            sandbox="allow-scripts allow-same-origin allow-popups allow-presentation"
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                    <div class="px-2">
                                        <h4 class="h6">{{ $item->name }}</h4>
                                        <p class="text-sm " style="font-size: 12px; border-radius: 1px;">
                                            {{ $item->client->name ?? 'No Client' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="row g-xxl-7 g-xl-6 g-4 mt-2">
                        @foreach ($mixedItems as $mixedItem)
                            @php
                                $item = $mixedItem['model'];
                            @endphp

                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="blog-widget-item">
                                    @if ($mixedItem['content_type'] === 'project_series')
                                        <div class="thumb w-100 overflow-hidden rounded-md" style="min-height: 180px;">
                                            <img src="{{ $item->thumbnail ? asset('storage/' . $item->thumbnail) : asset('/logo/favicon.svg') }}"
                                                class="w-100 h-100 object-cover" alt="{{ $item->name }}">
                                        </div>
                                        <div class="px-2 mt-2">
                                            <p class="text-sm text-muted" style="font-size: 12px;">
                                                Series / Playlist
                                            </p>
                                            <h4 class="h6">{{ $item->name }}</h4>
                                            <p class="text-sm text-muted" style="font-size: 12px;">
                                                {{ $item->episodes_count }} episode
                                            </p>
                                            <a href="{{ route('project.series.show', $item->slug) }}"
                                                class="btn btn-sm btn-primary">
                                                Lihat Series
                                            </a>
                                        </div>
                                    @else
                                        <div class="thumb w-100 overflow-hidden rounded-md">
                                            <iframe src="{{ $item->link }}" class="w-100 overflow-hidden" height="250"
                                                style="border:1px solid #ccc;border-radius: 10px;"
                                                title="{{ $item->name ?? 'Video Project' }}" loading="lazy"
                                                referrerpolicy="strict-origin-when-cross-origin"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                sandbox="allow-scripts allow-same-origin allow-popups allow-presentation"
                                                allowfullscreen>
                                            </iframe>
                                        </div>
                                        <div class="px-2">
                                            <h4 class="h6">{{ $item->name }}</h4>
                                            <p class="text-sm " style="font-size: 12px; border-radius: 1px;">
                                                {{ $item->client->name ?? 'No Client' }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($filmLimit < $totalFilms)
                    <button class="btn btn-warning mt-3" wire:click="loadMoreFilm">
                        Load More
                    </button>
                @endif
            </div>
        </section>
    </main>
    <script>
        window.addEventListener('change-url', event => {
            const slug = event.detail.slug;

            if (slug === 'all') {
                history.pushState({}, '', '/project');
            } else {
                history.pushState({}, '', `/project/${slug}`);
            }
        });
    </script>
</div>
