<div>

    <div>
        <!-- Custom Line Shape -->
        <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
        <!-- Custom Line Shape -->
        <main class="main position-relative overflow-hidden" id="mains">
            <section class="hero-section-version1 bnbg position-relative">
                <div class="container">
                    <!-- Blog Details Start -->
                    <section class="blog-details pt-space pb-space">
                        <div class="container">
                            <div class="row g-5">
                                <div class="col-12 col-lg-8">
                                    <div class="blog-post-details mb-xxl-10 mb-xl-8 mb-lg-7 mb-6">
                                        @include('livewire.blog-detail.single-post')
                                    </div>



                                    @include('livewire.blog-detail.recomen')
                                </div>
                                <div class="col-12 col-lg-4">
                                    @include('livewire.blog-detail.sidebar')
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- Blog Details End -->
                </div>


            </section>
        </main>
    </div>



</div>
