<div class="sponsor-section blackbg pb-space">
    <div class="sponsor-wrap ps-sm-10 ps-2 pe-2">
        <div class="swiper trusted-port">
            <div class="swiper-wrapper">
                @foreach ($project as $item)
                    <div class="swiper-slide">
                        <div class="blog-widget-item">
                            <div class="thumb w-100 overflow-hidden mb-xxl-7 mb-xl-6 mb-4">
                                <iframe src="{{ $item->link }}" class="w-100 overflow-hidden"></iframe>
                            </div>
                            <div class="blog-cont">
                                <div class="d-flex align-items-center  mb-3">
                                   <span>{{ $item->client->name }}</span>

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
