<div>
    <div>
        <!-- Custom Line Shape -->
        <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
        <!-- Custom Line Shape -->
        <main class="main position-relative overflow-hidden" id="mains">
            <section class="hero-section-version1 bnbg position-relative">
                <div class="container">
                    <div class="row g-xxl-7 g-xl-6 g-4 mt-5">
                        @if ($search)
                            @forelse ($blogSearch as $item)
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="blog-widget-item">
                                        <div class="thumb w-100 overflow-hidden mb-xxl-7 mb-xl-6 mb-4">
                                            <a href="{{ url('/blog/detail/' . $item->slug) }}" wire:navigate>
                                                <img src="{{ asset('storage/' . $item->image) }}" alt="img"
                                                    class="w-100 overflow-hidden">
                                            </a>
                                        </div>
                                        <div class="blog-cont">
                                            <div
                                                class="d-flex align-items-center gap-xxl-5 gap-xl-4 gap-3 mb-xxl-5 mb-xl-4 mb-3">
                                                <a href="#"
                                                    class="radius-btn cmn-border radius100 py-xxl-1 py-1 px-xxl-4 px-3 theme-clr style-2">
                                                    {{ $item->category->name }}
                                                </a>
                                                <span class="bspan-clr">
                                                    {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
                                                </span>
                                            </div>
                                            <h5>
                                                <a href="{{ url('/blog/detail/' . $item->slug) }}" class="white-clr" wire:navigate>
                                                    {{ $item->title }}
                                                </a>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center">Tidak Ada Post</div>
                            @endforelse
                        @else
                            @forelse ($blog as $item)
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="blog-widget-item">
                                        <div class="thumb w-100 overflow-hidden mb-xxl-7 mb-xl-6 mb-4">
                                            <a href="{{ url('/blog/detail/' . $item->slug) }}" wire:navigate>
                                                <img src="{{ asset('storage/' . $item->image) }}" alt="img"
                                                    class="w-100 overflow-hidden">
                                            </a>
                                        </div>
                                        <div class="blog-cont">
                                            <div
                                                class="d-flex align-items-center gap-xxl-5 gap-xl-4 gap-3 mb-xxl-5 mb-xl-4 mb-3">
                                                <a href="#"
                                                    class="radius-btn cmn-border radius100 py-xxl-1 py-1 px-xxl-4 px-3 theme-clr style-2">
                                                    {{ $item->category->name }}
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
                            @empty
                                <div class="text-center">Tidak Ada Post</div>
                            @endforelse
                            @if (count($blog) < count($cekBlog))
                                <div class="text-center mt-3">
                                    <button wire:click="loadMoreBlog" class="btn btn-secondary w-auto"
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

</div>
