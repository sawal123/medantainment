<div>
    <!-- Custom Line Shape -->
    <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
    <!-- Custom Line Shape -->
    <main class="main position-relative overflow-hidden" id="mains">
        <section class="hero-section-version1 bnbg position-relative">
            <div class="container">
                <div class="my-2">
                    <button type="button" wire:click="Clickfilm('all')"
                        class="btn border {{ $selectedCategory == 'all' ? 'btn-primary' : 'btn-dark' }}">
                        All
                    </button>
                    {{-- {{$categoryFilm}} --}}
                    @foreach ($categoryFilm as $item)
                        <button type="button" wire:click="Clickfilm('{{ $item->slug }}')"
                            class="btn border {{ $selectedCategory == $item->slug ? 'btn-primary' : 'btn-dark' }} my-1">
                            {{ $item->name }}
                        </button>
                    @endforeach
                </div>

                <p class="mt-2">
                    {{ $firstCategory->deskripsi }}
                </p>

                <div class="row g-xxl-7 g-xl-6 g-4 mt-2">
                    @foreach ($this->films as $item)
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="blog-widget-item">
                                <div class="thumb w-100 overflow-hidden   rounded-md">
                                    <iframe src="{{ $item->link }}" class="w-100 overflow-hidden " height="250"
                                        style="border:1px solid #ccc;border-radius: 10px;" title="Contoh Iframe">
                                    </iframe>
                                </div>
                                <div class="px-2">
                                    {{-- <span class="text-sm fs-6">{{ $item->name }}</span> --}}
                                    <p class="text-sm " style="font-size: 12px; border-radius: 1px;">
                                        {{ $item->client->name }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if ($this->films->count() >= $filmLimit)
                        <button class="btn btn-warning mt-3" wire:click="loadMoreFilm">
                            Load More
                        </button>
                    @endif
                    {{-- @if (count($film) < count($cekFilm)) <div class="text-center mt-3">
                        <button wire:click="loadMoreFilm" class="btn btn-secondary w-auto" wire:loading.attr="disabled">
                            <span wire:loading.remove>Load More</span>
                            <span wire:loading>Loading...</span>
                        </button>
                </div>
                @endif --}}



                </div>



        </section>
    </main>
    <script>
        //           document.addEventListener("livewire:navigated", () => {
        //   });
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
