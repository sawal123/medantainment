<div class="row g-xl-7 g-4 mb-xxl-15 mb-xl-10 mb-8">
    <h5>Blog Yang Mungkin Kamu Cari</h5>
    @foreach ($serupa as $item)
        <div class="col-lg-6" data-aos="zoom-in" data-aos-duration="1400">
            <div
                class="recent-items bg1-clr d-flex align-items-center gap-xxl-5 gap-xl-4 gap-lg-3 gap-2 py-xxl-6 py-xl-4 py-3 px-xxl-7 px-xl-5 px-lg-4 px-3">
                <div class="recent-thumb">
                    <img src="{{asset('storage/'. $item->image)}}" style="width: 80px; height: 80px; object-fit: cover" alt="img">
                </div>
                <div class="recent-content">
                    <span class="pra-clr d-block mb-1 fs14">
                        {{date('d-m-Y', strtotime($item->created_at))}}
                    </span>
                    <a href="{{$item->slug}}" wire:navigate class="htheme">
                        {{$item->title}}
                    </a>
                </div>
            </div>
        </div>
    @endforeach

</div>
