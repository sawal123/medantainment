<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- == Meta Tags == -->
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Keywords -->
    <meta name="keywords" content="{{ $setting->seo_keywords }}">
    <!--  Description -->
    <meta name="description" content="{{ $setting->seo_description }}">
    <meta name="author" content="{{ $setting->seo_title }}">
    <!-- == Page title == -->
    <title>{{ $page }}</title>
    {{--
    <link rel="shortcut icon" href="{{ asset('storage/' . $setting->favicon) }}" type="image/x-icon"> --}}
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('storage/' . $setting->favicon) }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('storage/' . $setting->favicon) }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('storage/' . $setting->favicon) }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('/logo/favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('/logo/favicon.svg') }}">
    <!-- Bootstrap Min 5.2.3 Css-->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- Fontawsome Icons Css-->
    <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}">
    <!-- Magnifiq Popup Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
    <!-- Nice Select Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/nice-select.css') }}">
    <!-- Swiper Slider Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/swiper.min.css') }}">
    <!-- Aos Animation Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/aos.css') }}">
    <!-- Main css -->
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <style>
        .form-control,
        .form-select,
        textarea.form-control {
            background-color: #2b2f33 !important;
            /* warna gelap */
            border: 1px solid #3a3f44 !important;
            color: #f1f1f1 !important;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            background-color: #32363b !important;
            border-color: #5a5f66 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(120, 130, 140, 0.25) !important;
        }

        label {
            color: #e2e2e2 !important;
        }

        /* .card {
            background-color: #1e1f21 !important;
            border: 1px solid #2a2b2d !important;
        } */

        .card-header {
            background-color: #1e1f21 !important;
            border-bottom: 1px solid #292a2c !imp
        }

        .form-select {
            background-color: #2b2f33 !important;
            color: #f1f1f1 !important;
            border: 1px solid #3a3f44;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
    <style>
    </style>
    {{-- @vite(['']) --}}
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-MNHPFTB7');
    </script>
    <!-- End Google Tag Manager -->
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MNHPFTB7" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    @include('components.header')
    {{ $slot }}
    @include('components.footer')
    <div id="progress">
        <span id="valiu"><i class="fas fa-arrow-up"></i></span>
    </div>
    <!-- js Jquery start -->
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <!-- js Bootstrap start -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <!-- js Waypoints start -->
    <script src="{{ asset('assets/js/jquery.waypoints.js') }}"></script>
    <!-- js Magnific popup start -->
    <script src="{{ asset('assets/js/magnific-popup.js') }}"></script>
    <!-- js Nice Select start -->
    {{-- <script src="{{ asset('assets/js/jquery.nice-select.min.js') }}"></script> --}}
    <!-- js Swiper start -->
    <script src="{{ asset('assets/js/swiper.js') }}"></script>
    <!-- js Aos Counterup start -->
    <script src="{{ asset('assets/js/jquery.counterup.min.js') }}"></script>
    <!-- js Aos Animation start -->
    <script src="{{ asset('assets/js/aos.js') }}"></script>
    <!-- js Aos Tilt start -->
    <script src="{{ asset('assets/js/vanilla-tilt.min.js') }}"></script>
    <!-- js Mian start -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 2, // default untuk layar besar
            loop: true,

            spaceBetween: 30,
            freeMode: false,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },

            // Atur jumlah slide berdasarkan ukuran layar
            breakpoints: {
                0: { // untuk mobile
                    slidesPerView: 1
                },
                768: { // tablet ke atas
                    slidesPerView: 2
                }
            }
        });
    </script>

    <script>
        document.addEventListener("livewire:navigated", () => {
            const navLinks = document.querySelectorAll('.nav-link');

            // Hapus event listener sebelumnya untuk menghindari duplikasi
            navLinks.forEach(item => {
                item.removeEventListener('mouseover', handleHover);
            });

            // Tambahkan event listener baru
            navLinks.forEach(item => {
                item.addEventListener('mouseover', handleHover);
            });
        });

        function handleHover(event) {
            const targetPaneId = event.target.getAttribute('data-bs-target');
            const targetPane = document.querySelector(targetPaneId);

            // Hapus 'active' dari semua nav-link
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));

            // Hapus 'show' dan 'active' dari semua tab-pane
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));

            // Tambahkan 'active' ke nav-link yang di-hover
            event.target.classList.add('active');

            // Tambahkan 'show' dan 'active' ke tab-pane yang sesuai
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        }

    </script>
</body>

</html>
