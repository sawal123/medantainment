@if (!empty($hero['hero4']))
    @php
        $embedUrl = \App\Rules\SafeVideoEmbedUrl::toEmbedUrl($hero['hero4']);
    @endphp

    @if (!empty($embedUrl))
        <section class="container my-md-5 my-3 mt-5 video-section" data-aos="zoom-in-up"
            data-aos-duration="900">
            <div class="text-center mb-4 mt-md-5 mt-3">
            </div>
            <div class="ratio ratio-16x9" style="border-radius:10px; overflow:hidden;">
                <iframe src="{{ $embedUrl }}" title="hero-video" frameborder="0"
                    sandbox="allow-scripts allow-same-origin allow-presentation"
                    loading="lazy"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen></iframe>
            </div>
        </section>
    @endif
@endif
