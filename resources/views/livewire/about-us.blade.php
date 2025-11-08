<div>
    <main class="main position-relative overflow-hidden" id="mains">

        <!-- Custom Line Shape -->
        <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
        <!-- Custom Line Shape -->

        <!-- Hero Section Version0 -->
        <section class="hero-section-version1 bnbg position-relative">
            <div class="container py-5">

                {{-- TITLE --}}
                @if (!empty($data?->title))
                    <h2 class="text-center mb-3">{{ $data->title }}</h2>
                @endif

                {{-- SUBTITLE --}}
                @if (!empty($data?->subtitle))
                    <p class="text-center text-muted mb-4">{{ $data->subtitle }}</p>
                @endif

                {{-- IMAGE --}}
                @if (!empty($data?->image))
                    <div class="text-center mb-4">
                        <img src="{{ asset('storage/' . $data->image) }}" class="img-fluid rounded-3" alt="About Us">
                    </div>
                @endif

                {{-- DESCRIPTION --}}
                @if (!empty($data?->description))
                    <div class="mb-4">
                        {!! $data->description !!}
                    </div>
                @endif

                {{-- VISION --}}
                @if (!empty($data?->vision))
                    <h4 class="mt-4">Visi</h4>
                    <p>{{ $data->vision }}</p>
                @endif

                {{-- MISSION --}}
                @if (!empty($data?->mission))
                    <h4 class="mt-4">{{ $data->mission_title ?? 'Misi' }}</h4>
                    <div>{!! $data->mission !!}</div>
                @endif

                {{-- HIGHLIGHTS --}}
                @php
                    $validHighlights = collect($data?->highlights)
                        ->filter(fn($item) => !empty($item['text']))
                        ->values();
                @endphp

                @if ($validHighlights->isNotEmpty())
                    <div class="mt-4">
                        <h4>Keunggulan</h4>
                        <ul>
                            @foreach ($validHighlights as $item)
                                <li>{{ $item['text'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                {{-- VIDEO --}}
                @if (!empty($data?->video_url))
                    <div class="mt-4 text-center">
                        <iframe width="100%" height="380" src="{{ $data->video_url }}" class="rounded-3"
                            allowfullscreen>
                        </iframe>
                    </div>
                @endif

            </div>
        </section>

    </main>
</div>
