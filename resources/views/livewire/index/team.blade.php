<section class="team-section pt-space blackbg pb-space">
    <div class="container">
        <div class="row g-xxl-7 g-xl-5 g-lg-4 g-3">
            @foreach ($team as $item)
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" data-aos="zoom-in-left" data-aos-duration="1400">
                    <div class="team-common-item tilt">
                        <div class="thumb position-relative overflow-hidden w-100">
                            <img src="{{asset('storage/'. $item->gambar)}}" alt="img" style="height: 400px; object-fit: cover" class="overflow-hidden w-100">
                            <div class="namebox py-xxl-5 py-xl-4 py-sm-3 py-2 px-3 text-center">
                                <span class="text-uppercase white-clr d-block mb-2">
                                    {{$item->posisi}}
                                </span>
                                <h5>
                                    <a href="#" class="htheme white-clr">
                                        {{$item->nama}}
                                    </a>
                                </h5>
                            </div>
                          
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</section>
