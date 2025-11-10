<div>
    <main class="main position-relative overflow-hidden" id="mains">

        <!-- Custom Line Shape -->
        <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
        <!-- Custom Line Shape -->

        <!-- Hero Section Version0 -->
        <section class="hero-section-version1 bnbg position-relative">
            <div class="container">
                <style>
                    .masonry {
                        column-count: 1;
                        column-gap: 1rem;
                    }

                    .masonry-item {
                        break-inside: avoid;
                        margin-bottom: 1rem;
                    }

                    .masonry-img {
                        width: 100%;
                        height: auto;
                        display: block;
                        border-radius: 12px;
                        object-fit: cover;
                    }

                    @media (min-width: 576px) {
                        .masonry {
                            column-count: 2;
                        }
                    }

                    @media (min-width: 768px) {
                        .masonry {
                            column-count: 3;
                        }
                    }

                    @media (min-width: 1200px) {
                        .masonry {
                            column-count: 4;
                        }
                    }
                </style>
                <section class="container my-5">
                    <div class="text-center mb-5">
                        <h5>Galeri Portofolio untuk Menunjukkan Kualitas Kami</h5>
                    </div>
                    @if ($photo)
                        <div class="masonry">
                            @foreach ($this->photos as $item)
                                <div class="masonry-item">
                                    <img src="{{ asset('storage/' . $item->photo) }}" alt="" class="masonry-img">
                                </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-center">
                            Gallery tidak tersedia
                        </p>
                    @endif

                    @if ($this->photos->count() < \App\Models\Photo::count())
                        <div class="text-center mt-4">
                            <button wire:click="loadMore" class="btn btn-dark px-4 py-2">
                                Load More
                            </button>
                        </div>
                    @endif
                </section>
            </div>
        </section>
    </main>
</div>
