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
                        <div
                            class="d-flex align-items-sm-center align-items-end justify-content-between mb-xxl-16 mb-xl-14 mb-lg-12 mb-md-10 mb-sm-8 mb-5">
                            <h1 class="white-clr text-uppercase">
                                <span>
                                    <span class="text-normal">
                                        {{$landing[0]}}
                                    </span>
                                    <span class="designers" data-text="{{$landing[1]}}" data-aos="zoom-in"
                                        data-aos-duration="2000">{{$landing[1]}}</span>
                                </span>
                                <span
                                    class="agency-title d-flex align-items-center gap-xxl-12 gap-xl-6 gap-lg-5 gap-md-3 gap-2 mt-xxl-5 mt-lg-2 mt-sm-1"
                                    data-aos="zoom-in-left" data-aos-duration="1800">
                                    <img src="assets/img/element/arrow-right-storke.png" alt="img"
                                        class="arrow-bnv1">
                                    {{$landing[2]}}
                                </span>
                            </h1>
                            <div class="hero-arrow">
                                <img src="assets/img/element/cmn-arrow.png" alt="img">
                            </div>
                        </div>
                        <div class="banner-quickly-thumb w-100" data-aos="zoom-in-up" data-aos-duration="1600">
                            <img src="{{ asset('img/1410X521.jpg') }}" alt="img" class="w-100">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Social -->
            @include('livewire.index.sosmed')
            <!-- Contact Info -->
            <!-- Element -->
            <img src="{{asset('assets/img/banner/soft-star.png')}}" alt="img" class="sfot-element1">
            <img src="{{asset('assets/img/banner/soft-star.png')}}" alt="img" class="sfot-element2">
        </section>
        <!-- Hero Section Version0 -->

        <!-- Cmn About Start -->
        <section class="pt-space pb-space section-bg">
            <div class="container">
                <div class="row g-6 justify-content-between">
                    <div class="col-lg-6 pe-lg-14">
                        <div class="d-flex gap-xxl-7 gap-xl-5 gap-4 position-relative z-index-1">
                            <div class="about-small-thumb w-100" data-aos="zoom-in" data-aos-duration="1500">
                                <img src="{{ asset('img/313X440.jpg') }}" alt="img" class="w-100">
                            </div>
                            <div class="about-small-thumb w-100 mt-xxl-10 mt-xl-7 mt-lg-5 mt-3" data-aos="zoom-in"
                                data-aos-duration="1800">
                                <img src="{{ asset('img/313X494.jpg') }}" alt="img" class="w-100">
                            </div>
                            <!--- About Arrow -->
                            <img src="assets/img/element/arrow-right-storke.png" alt="img"
                                class="about-tumb-arrow">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-samll-content">
                            <div class="pricing-title">
                                <div class="radius-btn text-uppercase cmn-border d-inline-flex radius100 py-xxl-2 py-2 px-xxl-4 px-4 theme-clr gap-xxl-4 gap-3 mb-xxl-8 mb-xl-6 mb-5"
                                    data-aos="zoom-in-left" data-aos-duration="1400">
                                    {{$landing[3]}}
                                </div>
                                <h2 class="stitle d-flex align-items-center mb-xxl-8 mb-xl-7 mb-lg-6 mb-5 gap-xxl-7 gap-xl-5 gap-3"
                                    data-aos="zoom-in-left" data-aos-duration="1700">
                                    <img src="assets/img/element/arrow-right-storke.png" alt="img"
                                        data-aos="zoom-in-up" data-aos-duration="2000" class="about-small-stork">
                                    {{$landing[4]}}
                                </h2>
                                <p class="white-clr mb-xxl-8 mb-xl-8 mb-5" data-aos="zoom-in-up"
                                    data-aos-duration="1400">
                                    {!!$landing[5]!!}
                                </p>
                                <div class="result-progress-wrap" data-aos="zoom-in-up" data-aos-duration="1800">
                                    <div class="progres-item mb-xxl-6 mb-xl-5 mb-4">
                                        <div class="d-flex align-items-center justify-content-between mb-xxl-4 mb-3">
                                            <span class="conssub">
                                                Branding Design
                                            </span>
                                            <span class="cons">88%</span>
                                        </div>
                                        <div class="progress-solving">
                                            <div class="progress-bar"></div>
                                        </div>
                                    </div>
                                    <div class="progres-item">
                                        <div class="d-flex align-items-center justify-content-between mb-xxl-4 mb-3">
                                            <span class="conssub">
                                                Business
                                            </span>
                                            <span class="cons">96%</span>
                                        </div>
                                        <div class="progress-solving">
                                            <div class="progress-bar"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Cmn About End -->

        <!-- Text SLider Start -->
        <div class="digital-solution testi-italic pb-lg-20 pb-15">
            <div class="mycustom-marque">
                <div class="scrolling-wrap">
                    <div class="comm">
                        <div class="cmn-textslide textitalick theme-clr">CREATIVE portfolio</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">CREATIVE portfolio</div>
                        <div class="cmn-textslide textitalick theme-clr">CREATIVE portfolio</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">CREATIVE portfolio</div>
                    </div>
                    <div class="comm">
                        <div class="cmn-textslide textitalick theme-clr">CREATIVE portfolio</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">CREATIVE portfolio</div>
                        <div class="cmn-textslide textitalick theme-clr">CREATIVE portfolio</div>
                        <div><img src="assets/img/client/text-slide.png" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">CREATIVE portfolio</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Text Slider End -->

        <!-- Case Studyv01 Start -->
        <section class="case-study-vsesion01 bg2-clr pb-space">
            @include('livewire.index.portofolio')
        </section>
        <!-- Case Studyv01 End -->

        <!-- Watch Version01 Start -->
        <div class="watch-version01 zindex1 position-relative">
            <div class="container">
                <div class="watch-content d-center">
                    <a href="{{$landing[6]}}" class="video-popup position-relative">
                        <span class="icons themebg radius100 d-center">
                            <i class="fas fa-play"></i>
                        </span>
                        <img src="assets/img/element/watch-ciricle01.png" alt="img" class="circle">
                    </a>
                </div>
            </div>
        </div>
        <!-- Watch Version01 End -->



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

        @include('livewire.index.client')

        <!-- Text SLider Start -->
        <div class="digital-solution blackbg home-onetext pb-20">
            <div class="mycustom-marque">
                <div class="scrolling-wrap">
                    <div class="comm">
                        <div class="cmn-textslide textitalick">MEDANTAINMENT</div>
                        <div><img src="{{asset('img/logo2.png')}}" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">Let’s talk!</div>
                        <div><img src="{{asset('img/logo2.png')}}" alt="img"></div>
                        <div class="cmn-textslide textitalick">MEDANTAINMENT</div>
                        <div><img src="{{asset('img/logo2.png')}}" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">Let’s talk!</div>
                        <div><img src="{{asset('img/logo2.png')}}" alt="img"></div>
                        <div class="cmn-textslide textitalick">Let’s talk!</div>
                    </div>
                    <div class="comm">
                        <div class="cmn-textslide textitalick">Let’s talk!</div>
                        <div><img src="{{asset('img/logo2.png')}}" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">MEDANTAINMENT</div>
                        <div><img src="{{asset('img/logo2.png')}}" alt="img"></div>
                        <div class="cmn-textslide textitalick">Let’s talk!</div>
                        <div><img src="{{asset('img/logo2.png')}}" alt="img"></div>
                        <div class="cmn-textslide text-custom-storke">MEDANTAINMENT</div>
                        <div><img src="{{asset('img/logo2.png')}}" alt="img"></div>
                        <div class="cmn-textslide textitalick">Let’s talk!</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Text Slider End -->

    </main>
    <!-- Main End -->
</div>
