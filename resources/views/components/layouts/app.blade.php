<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- == Meta Tags == -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Keywords -->
    <meta name="keywords" content="{{$setting->seo_keywords}}">
    <!--  Description -->
    <meta name="description" content="{{$setting->seo_description}}">
    <meta name="author" content="{{$setting->seo_title}}">
    <!-- == Page title == -->


    <title>{{ $page }}</title>

    <link rel="shortcut icon" href="{{ asset('storage/'. $setting->favicon) }}" type="image/x-icon">
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
</head>

<body>

    @include('components.header')
    {{ $slot }}

    @include('components.footer')
    <!-- Scroll Top Start -->
    <div id="progress">
        <span id="valiu"><i class="fas fa-arrow-up"></i></span>
    </div>
    <!-- Scroll Top End -->



    <!-- js Jquery start -->
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <!-- js Bootstrap start -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <!-- js Waypoints start -->
    <script src="{{ asset('assets/js/jquery.waypoints.js') }}"></script>
    <!-- js Magnific popup start -->
    <script src="{{ asset('assets/js/magnific-popup.js') }}"></script>
    <!-- js Nice Select start -->
    <script src="{{ asset('assets/js/jquery.nice-select.min.js') }}"></script>
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

        // document.querySelectorAll('.nav-link').forEach(item => {
        //     item.addEventListener('mouseover', function() {
        //         const targetPaneId = this.getAttribute('data-bs-target');
        //         const targetPane = document.querySelector(targetPaneId);

        //         // Remove 'active' class from all nav links
        //         document.querySelectorAll('.nav-link').forEach(link => {
        //             link.classList.remove('active');
        //         });

        //         // Remove 'show' class from all tab panes
        //         document.querySelectorAll('.tab-pane').forEach(pane => {
        //             pane.classList.remove('show', 'active');
        //         });

        //         // Add 'active' class to the hovered nav link
        //         this.classList.add('active');

        //         // Add 'show' and 'active' classes to the corresponding tab pane
        //         if (targetPane) {
        //             targetPane.classList.add('show', 'active');
        //         }
        //     });
        // });
    </script>
</body>

</html>
