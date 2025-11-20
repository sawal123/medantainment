<div>
    <style>
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            border: 1px solid white;
            transform: scale(1.02);
            /* Sedikit membesar */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            /* Bayangan lebih tajam */
        }

        .card-body h5 {
            transition: color 0.3s ease;
        }

        .card:hover .card-body h5 {
            color: #007bff;
            /* Warna berubah saat hover */
        }
    </style>
    <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
    <!-- Custom Line Shape -->
    <main class="main position-relative overflow-hidden" id="mains">
        <section class="hero-section-version1 bnbg position-relative">
            <div class="container">
                <div class="row">
                    <!-- Sidebar: col-4 pada lg, pindah ke bawah pada md -->
                    <div class="col-lg-4 col-md-12 order-last order-md-first ">
                        @foreach ($carrers as $item)
                            <a href="/carrer/detail/{{ $item->slug }}" wire:navigate>
                                <div class="card bg-dark mt-2" style="min-width: 18rem;">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $item->title }}</h5>
                                        <span class="badge bg-primary">{{ $item->time }}</span>
                                        <span class="badge bg-primary">{{ $item->status }}</span>
                                        {{-- <span class="badge bg-soft-secondary">Rp{{ $item->salary }}</span> --}}
                                        <p class="card-text fs-6">
                                            {{ Str::limit(strip_tags($item->description), 100, '...') }}
                                        </p>

                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Konten utama: col-8 pada lg, pindah ke atas pada md -->
                    <div class="col-lg-8 col-md-12 ">
                        <h4>{{ $carrer->title }}</h4>
                        <span class="badge bg-primary">{{ $carrer->time }}</span>
                        <span class="badge bg-primary">{{ $carrer->status }}</span>
                        {{-- <span class="badge bg-soft-secondary">Rp{{ $carrer->salary }}</span> --}}
                        <p class="card-text fs-6 mt-5">
                            {!! nl2br($carrer->description) !!}
                        </p>
                        <a href="{{$carrer->apply_link}}" wire:navigate class="btn btn-primary">Kirim Lamaran</a>
                    </div>
                </div>

            </div>


        </section>
    </main>

</div>
