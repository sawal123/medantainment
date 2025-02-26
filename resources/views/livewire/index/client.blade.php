  <!-- Sponsor Start -->
  <div class="sponsor-section blackbg pb-space">
      <div class="sponsor-wrap ps-sm-10 ps-2 pe-2">
          <div class="swiper trusted-inner">
              <div class="swiper-wrapper">
                 @foreach ($client as $item)
                 <div class="swiper-slide">
                    <a href="javascript:void(0)" class="brand-image">
                        <img src="{{asset('storage/'.$item->logo)}}" style="height: 80px" alt="img">
                    </a>
                </div>
                 @endforeach
                 
              </div>
          </div>
      </div>
  </div>
  <!-- Sponsor End -->
