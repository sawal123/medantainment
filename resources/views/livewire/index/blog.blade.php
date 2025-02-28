<section class="home-blog-version1 blackbg pb-space pt-space">
    <div class="container zindex position-relative">
        <div
            class="d-flex align-items-end justify-content-between flex-wrap gap-5 mb-xxl-15 mb-xl-12 mb-lg-10 mb-md-10 mb-sm-10 mb-9">
            <div class="pricing-title">
                <div
                    class="radius-btn text-uppercase cmn-border d-inline-flex radius100 py-xxl-3 py-2 px-xxl-5 px-4 theme-clr gap-xxl-4 gap-3 mb-xxl-8 mb-xl-6 mb-5">
                    BLOG & News
                    <span class="rot60 d-inline-block">
                        <i class="fas fa-arrow-up theme-clr"></i>
                    </span>
                </div>
                <h2 class="stitle">
                    Check Our Company <br> Inside Story
                </h2>
            </div>
            <a href="blog-grid.html"
                class="radius-btn details-btn d-inline-flex text-capitalize radius100 py-xxl-4 py-3 px-xxl-5 px-5 theme-border theme-clr gap-xxl-4 gap-3">
                view all Artcile
            </a>
        </div>
        <div class="row g-xxl-7 g-xl-6 g-4">
            @foreach ($blog as $item)
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="blog-widget-item">
                        <div class="thumb w-100 overflow-hidden mb-xxl-7 mb-xl-6 mb-4">
                            <a href="{{ url('/blog/detail/' . $item->slug) }}" wire:navigate>
                                <img src="{{ asset('storage/' . $item->image) }}" alt="img"
                                    class="w-100 overflow-hidden">
                            </a>
                        </div>
                        <div class="blog-cont">
                            <div class="d-flex align-items-center gap-xxl-5 gap-xl-4 gap-3 mb-xxl-5 mb-xl-4 mb-3">
                                <a href="#"
                                    class="radius-btn cmn-border radius100 py-xxl-1 py-1 px-xxl-4 px-3 theme-clr style-2">
                                    {{ $item->category->name }}
                                    <span class="rot60 d-inline-block ml-10">
                                        <i class="fas fa-arrow-up theme-clr"></i>
                                    </span>
                                </a>
                                <span class="bspan-clr">
                                    {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
                                </span>
                            </div>
                            <h5>
                                <a href="{{ url('/blog/detail/' . $item->slug) }}" wire:navigate class="white-clr">
                                    {{ $item->title }}
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</section>
