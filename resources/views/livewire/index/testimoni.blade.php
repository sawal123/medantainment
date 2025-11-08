  <section class="testimonial-version02-section blackbg position-relative" wire:ignore>
      <div class="container position-relative">
          <div class="row g-6">
              <div class="col-lg-12">
                  <div class="swiper testimonial-version01 position-relative">
                      <div class="swiper-wrapper">
                          @foreach ($testimoni as $item)
                              <div class="swiper-slide">
                                  <div class="testimonial-zero-oneitem">
                                      <div class="content ms-xl-15">
                                          <div class="d-flex align-items-center justify-content-between">
                                              <div class="ratting-area d-flex align-items-center gap-2">
                                                  <i class="fa-solid fa-star theme-clr"></i>
                                                  <i class="fa-solid fa-star theme-clr"></i>
                                                  <i class="fa-solid fa-star theme-clr"></i>
                                                  <i class="fa-solid fa-star theme-clr"></i>
                                                  <i class="fa-solid fa-star theme-clr"></i>
                                              </div>
                                              <div class="icon mb-xxl-6 mb-xl-5 mb-4">
                                                  <svg width="53" height="38" viewBox="0 0 53 38" fill="none"
                                                      xmlns="http://www.w3.org/2000/svg">
                                                      <path
                                                          d="M45.5 22.5V22H45H37.5C33.6394 22 30.5 18.8606 30.5 15V7.5C30.5 3.63942 33.6394 0.5 37.5 0.5H45C48.8606 0.5 52 3.63942 52 7.5V11.25V15V23.4375C52 30.9309 45.9309 37 38.4375 37H37.5C35.7019 37 34.25 35.5481 34.25 33.75C34.25 31.9519 35.7019 30.5 37.5 30.5H38.4375C42.3347 30.5 45.5 27.3347 45.5 23.4375V22.5ZM15.5 22.5V22H15H7.5C3.63942 22 0.5 18.8606 0.5 15V7.5C0.5 3.63942 3.63942 0.5 7.5 0.5H15C18.8606 0.5 22 3.63942 22 7.5V11.25V15V23.4375C22 30.9309 15.9309 37 8.4375 37H7.5C5.70192 37 4.25 35.5481 4.25 33.75C4.25 31.9519 5.70192 30.5 7.5 30.5H8.4375C12.3347 30.5 15.5 27.3347 15.5 23.4375V22.5Z"
                                                          stroke="#E3FF04" />
                                                  </svg>
                                              </div>
                                          </div>
                                          <p class="pra-clr mb-xxl-8 mb-xl-6 mb-lg-5 mb-4">
                                              {{$item->message}}
                                          </p>
                                          <div class="vector d-md-block d-none mb-xxl-5 mb-4 w-100">
                                              <img src="assets/img/element/testi-v2-element.png" alt="img"
                                                  class="w-100">
                                          </div>
                                          <div class="d-flex align-items-center gap-xxl-3 gap-2">
                                              <div class="savannah">
                                                  <img src="{{asset('storage/'.$item->photo)}}" alt="img">
                                              </div>
                                              <div class="desig">
                                                  <h6 class="white-clr mb-2">
                                                      {{$item->name}}
                                                  </h6>
                                                  <span class="theme-clr">
                                                      {{$item->position}}
                                                  </span>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          @endforeach

                      </div>
                  </div>
              </div>
          </div>
      </div>
  </section>
