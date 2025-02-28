<div class="blog-right-bar mt-lg-0 mt-4">
    <div class="box mb-xxl-10 mb-xl-8 mb-7">
        <div class="wid-title" data-aos="fade-left" data-aos-duration="1600">
            <h6>Search</h6>
        </div>
        <div class="search-widget" data-aos="zoom-in" data-aos-duration="1400">
            <form wire:submit.prevent="search">
                <input type="text" placeholder="Search here..." wire:model="query">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
    </div>
    {{-- <div class="box mb-xxl-10 mb-xl-8 mb-7">
        <div class="wid-title" data-aos="fade-left" data-aos-duration="1600">
            <h6>Cagegories</h6>
        </div>
        <div class="category" data-aos="fade-down" data-aos-duration="1600">

            <ul class="d-grid gap-xxl-3 gap-3">
                @foreach ($category as $item)
                    <li>
                        <a href="blog-details.html">
                            {{ $item->name }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div> --}}
    <div class="box mb-xxl-10 mb-xl-8 mb-7">
        <div class="wid-title" data-aos="fade-left" data-aos-duration="1600">
            <h6>Recent Posts</h6>
        </div>
        <div class="recent-postwrap">
            @foreach ($recent as $item)
                <div class="recent-items d-flex align-items-center gap-xxl-5 gap-xl-4 gap-lg-3 gap-2"
                    data-aos="fade-down" data-aos-duration="1200">
                    <div class="recent-thumb">
                        <img src="{{asset('storage/'.$item->image)}}" style="width: 50px; height: 50px; object-fit: cover" alt="img">
                    </div>
                    <div class="recent-content">
                        
                        <a href="{{$item->slug}}" wire:navigate>
                            {{$item->title}}
                        </a>
                       
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
