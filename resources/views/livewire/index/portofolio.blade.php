<div class="sponsor-section blackbg pb-space">
    <div class="sponsor-wrap ps-sm-10 ps-2 pe-2">
        <div class="swiper trusted-inner">
            <div class="swiper-wrapper">
                @foreach ($project as $item)
                    <div class="swiper-slide">
                        <div class="blog-widget-item">
                            <div class="thumb w-100 overflow-hidden mb-xxl-7 mb-xl-6 mb-4">
                                <iframe src="{{ $item->link }}" class="w-100 overflow-hidden"></iframe>
                            </div>
                            <div class="blog-cont">
                                <div class="d-flex align-items-center gap-xxl-5 gap-xl-4 gap-3 mb-xxl-5 mb-xl-4 mb-3">
                                    <a href="#"
                                        class="radius-btn cmn-border radius100 py-xxl-1 py-1 px-xxl-4 px-3 theme-clr style-2">
                                        {{ $item->client->name }}
                                        <span class="rot60 d-inline-block ml-10">
                                            <i class="fas fa-arrow-up theme-clr"></i>
                                        </span>
                                    </a>

                                </div>
                                <h5>
                                    {{ $item->name }}
                                </h5>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
