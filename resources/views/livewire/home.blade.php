<div>
    <!-- Main start -->
    <main class="main position-relative overflow-hidden" id="mains">

        <!-- Custom Line Shape -->
        <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
        <!-- Custom Line Shape -->

        <!-- Hero Section Version0 -->
        <section class="hero-section-version1 bnbg position-relative">
            <div class="container">
                <div class="row g-5">
                    <div class="hero-v1-content position-relative">
                        <div class="text-center mb-xxl-16 mb-xl-14 mb-lg-12 mb-md-10 mb-sm-8 mb-5">
                            <style>
                                @media (max-width: 768px) {
                                    .designers {
                                        position: relative;
                                        bottom: -20px;
                                        /* atur sesuai kebutuhan */
                                    }
                                }
                            </style>
                            <h1 class="white-clr text-uppercase hero-title">
                                <span>
                                    <span class="designers" data-text="{{ $hero['hero1'] ?? '' }}" data-aos="zoom-in"
                                        data-aos-duration="2000">
                                        {{ $hero['hero1'] ?? '' }}
                                    </span>
                                    <br class="hero-br">
                                    <span class="text-normal">
                                        {{ $hero['hero2'] ?? '' }}
                                    </span>
                                </span>
                            </h1>

                            <h4 class=" mt-xxl-5 mt-lg-2 mt-sm-1" data-aos="zoom-in-left" data-aos-duration="1700">
                                {{ $hero['hero3'] ?? '' }}
                            </h4>
                            <a href="/about-us" class="btn btn-dark border mt-5" data-aos="zoom-in-right"
                                data-aos-duration="1800">About Us</a>
                        </div>

                        <div class="swiper mySwiper" wire:ignore>
                            <div class="swiper-wrapper">
                                @foreach ($slide as $item)
                                    <div class="swiper-slide">
                                        <a href="{{ $item->link }}">
                                            <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="img"
                                                class="w-100 rounded">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- Social -->
            @include('livewire.index.sosmed')

            <img src="{{ asset('assets/img/banner/soft-star.png') }}" alt="img" class="sfot-element1">
            <img src="{{ asset('assets/img/banner/soft-star.png') }}" alt="img" class="sfot-element2">

            <style>
                .partner-card {
                    background: white;
                    padding: 18px 28px;
                    border-radius: 14px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 80px;
                    box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
                    transition: 0.2s ease;
                }

                .partner-card img {
                    max-height: 50px;
                    max-width: 100%;
                    object-fit: contain;
                }

                .partner-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
                }

                .partners-wrapper {
                    /* background: #f5f7fb; */
                    padding: 30px 10px;
                    border-radius: 12px;
                }
            </style>
            <div class="container partners-wrapper mt-4">
                <h3 class="text-center my-4" data-aos="zoom-in-up" data-aos-duration="1500">
                    Kami akan senang bekerja dengan Anda <br>
                    <p>Berikut adalah beberapa klien kami dari nasional maupun lokal</p>
                </h3>

                <div class="row g-3 justify-content-center" data-aos="zoom-in-up" data-aos-duration="1600">
                    @foreach ($client as $item)
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="partner-card">
                                <img src="{{ asset('storage/' . $item->logo) }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- Tombol More hanya muncul jika belum menampilkan semua --}}
            @if (!$showAll)
                <div class="text-center mt-3" data-aos="zoom-in-up" data-aos-duration="1700">
                    <button wire:click.prevent="tes" type="button" class="btn btn-secondary">
                        {{ count($client) }}+ More
                    </button>
                </div>
            @endif


        </section>
        <!-- Hero Section Version0 -->

        <section class="container px-5 my-5  py-5 position-relative">
            <div class="row g-4 justify-content-center">
                <h3 class="text-center my-4">
                    Temukan Layanan Produksi Kreatif Kami<br>
                    <p>Berikut adalah kategori layanan video yang kami sediakan untuk mendukung kebutuhan visual Anda
                    </p>
                </h3>
                <style>
                    .card:hover {
                        transform: translateY(-5px);
                        transition: 0.2s ease-in-out;
                        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);
                    }
                </style>
                @foreach ($categoryFilm as $item)
                    <div class="col-12 col-md-6 col-lg-2">
                        <div class="card bg-dark shadow-sm border-0 rounded-3 overflow-hidden h-100">

                            {{-- Image 1:1 --}}
                            <div class="ratio ratio-1x1">
                                <img src="{{ asset('storage/' . $item->thumbnail) }}"
                                    class="object-fit-cover w-100 h-100" alt="{{ $item->name }}">
                            </div>

                            {{-- Content --}}
                            <div class="p-3 d-flex flex-column">

                                <h5 class="text-white mb-2">{{ $item->name }}</h5>

                                @if ($item->start)
                                    <small class="text-secondary mb-3 d-block">
                                        Start From: {{ 'Rp ' . number_format($item->start, 0, ',', '.') }}
                                    </small>
                                @endif

                                <div class="mt-auto">
                                    <a href="/project/{{ $item->slug }}" wire:navigate
                                        class="btn btn-sm btn-outline-light w-100">
                                        Detail
                                    </a>
                                </div>

                            </div>

                        </div>
                    </div>
                @endforeach




            </div>
        </section>

        <style>
            .masonry {
                column-count: 1;
                column-gap: 1rem;
            }

            .masonry-item {
                break-inside: avoid;
                margin-bottom: 1rem;
                border-radius: 12px;
                overflow: hidden;
            }

            .masonry-img {
                width: 100%;
                height: auto;
                display: block;
                border-radius: 12px;
                object-fit: cover;
            }

            /* Responsive */
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
        {{-- {{$photo}} --}}
        @if (empty($photo))
            <hr>
            <section class="container my-5 position-relative">
                <div class="text-center mb-5">
                    <h5>Beberapa Karya Terbaik yang Pernah Kami Kerjakan</h5>
                </div>
                <div class="masonry">
                    @foreach ($photo as $item)
                        <div class="masonry-item">
                            <img src="{{ asset('storage/' . $item->photo) }}" alt="" class="masonry-img">
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center mt-5">
                    <a href="/gallery" class="btn btn-secondary" wire:navigate>Load More</a>
                </div>
            </section>
            <hr>
        @endif


        <!-- Watch Version01 Start -->
        <div class="watch-version01 zindex1 position-relative">
            <div class="container">
                <div class="watch-content d-center">
                    <a href="{{ $hero['hero4'] ?? '' }}" class="video-popup position-relative">
                        <span class="icons themebg radius100 d-center">
                            <i class="fas fa-play"></i>
                        </span>
                        <img src="assets/img/element/watch-ciricle01.png" alt="img" class="circle">
                    </a>
                </div>
            </div>
        </div>

        <!-- Text SLider Start -->
        <div class="digital-solution blackbg testi-italic pt-space pb-lg-15 pb-10">
            <div class="mycustom-marque">
                <div class="scrolling-wrap">
                    <div class="comm">
                        <div class="cmn-textslide textitalick theme-clr">Client’s testimonial</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">Client’s testimonial</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide textitalick theme-clr">Client’s testimonial</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">Client’s testimonial</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                    </div>
                    <div class="comm">
                        <div class="cmn-textslide textitalick theme-clr">Client’s testimonial</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">Client’s testimonial</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide textitalick theme-clr">Client’s testimonial</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">Client’s testimonial</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Text Slider End -->

        <!-- Version01 Testimonial Start -->
        @include('livewire.index.testimoni')
        <!-- Version01 Testimonial End -->

        <!-- Version01 BLog One Start -->
        @include('livewire.index.blog')
        <!-- Version01 BLog One End -->

    </main>
    <!-- Main End -->
</div>
