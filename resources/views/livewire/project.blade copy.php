<div>
    <!-- Custom Line Shape -->
    <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
    <!-- Custom Line Shape -->
    <main class="main position-relative overflow-hidden" id="mains">
        <section class="hero-section-version1 bnbg position-relative">
            <div class="container">
                <button type="button" wire:click='Clickfilm' class="btn btn-primary">
                    <i class="bi bi-film"></i> Film
                </button>
                <button type="button" wire:click='Clickphoto' class="btn btn-primary">
                    <i class="bi bi-image"></i> Photo
                </button>

                <div class="row g-xxl-7 g-xl-6 g-4 mt-5">
                    @if ($showFilm)
                        @foreach ($film as $item)
                            <div class="col-lg-4 col-md-6 col-sm-6">
                                <div class="blog-widget-item">
                                    <div class="thumb w-100 overflow-hidden mb-xxl-7 mb-xl-6 mb-4 rounded-md">

                                        <iframe src="{{ $item->link }}" class="w-100 overflow-hidden " height="250"
                                            style="border:1px solid #ccc;" title="Contoh Iframe">
                                        </iframe>
                                    </div>
                                    <div class="">
                                        <div
                                            class="d-flex align-items-center gap-xxl-5 gap-xl-4 gap-3 mb-xxl-5 mb-xl-4 mb-3">
                                            <a href="#"
                                                class="radius-btn cmn-border radius100 py-xxl-1 py-1 px-xxl-4 px-3 theme-clr style-2">
                                                {{ $item->client->name }}
                                            </a>
                                        </div>

                                        <span class="text-sm">{{ $item->name }}</span>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if (count($film) < count($cekFilm))
                            <div class="text-center mt-3">
                                <button wire:click="loadMoreFilm" class="btn btn-secondary w-auto"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove>Load More</span>
                                    <span wire:loading>Loading...</span>
                                </button>
                            </div>
                        @endif
                    @endif
                    @if ($showImage)
                        @foreach ($image as $item)
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <div class="blog-widget-item">
                                    <div class="thumb w-100 overflow-hidden mb-xxl-7 mb-xl-6 mb-4">
                                        <img src="{{ asset('storage/' . $item->photo) }}" alt="img"
                                            class="w-100 overflow-hidden">
                                    </div>
                                    <div class="blog-cont">
                                        <div
                                            class="d-flex align-items-center gap-xxl-5 gap-xl-4 gap-3 mb-xxl-5 mb-xl-4 mb-3">
                                            <a href="#"
                                                class="radius-btn cmn-border radius100 py-xxl-1 py-1 px-xxl-4 px-3 theme-clr style-2">
                                                {{ $item->client->name }}

                                            </a>

                                        </div>
                                        <span class="text-sm">{{ $item->name }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if (count($image) < count($cekImage))
                            <div class="text-center mt-3">
                                <button wire:click="loadMoreImage" class="btn btn-secondary w-auto"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove>Load More</span>
                                    <span wire:loading>Loading...</span>
                                </button>
                            </div>
                        @endif
                    @endif


                </div>
            </div>


        </section>
    </main>
</div>
