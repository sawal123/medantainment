<div>

    <!-- Custom Line Shape -->
    <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
    <!-- Custom Line Shape -->
    <main class="main position-relative overflow-hidden" id="mains">
        <section class="hero-section-version1 bnbg position-relative">
            <div class="container">
                <div class="row">
                    @foreach ($carrer as $item)
                      
                    <div class="col-lg-3 col-md-12 mt-2">
                            <a href="/carrer/detail/{{ $item->slug }}" wire:navigate>
                                    <div class="card bg-dark" style="min-width: 18rem;">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $item->title }}</h5>
                                            <span class="badge bg-primary">{{ $item->time }}</span>
                                            <span class="badge bg-primary">{{ $item->status }}</span>
                                            <span class="badge bg-soft-secondary">Rp{{ $item->salary }}</span>
                                            <p class="card-text fs-6">
                                                {{ Str::limit(strip_tags($item->description), 100, '...') }}</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                    
                    @endforeach
                </div>
            </div>


        </section>
    </main>


</div>
