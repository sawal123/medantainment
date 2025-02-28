 <!-- Custom Header Start -->
 <header class="header-section cmn-fixed py-lg-0 py-6">
    <div class="container">
        <div class="main-navbar">
            <nav class="navbar-custom">
                <div class="d-lg-flex flex-xl-nowrap flex-wrap align-items-center justify-content-lg-between">
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="/" class="brand-logo">
                            <img class="w-100" style="max-height: 90px;" src="{{asset('img/logo.png')}}" alt="logo">
                        </a>
                        <div class="d-flex align-items-center gap-xxl-5 gap-5">
                            <a href="javascript:void(0)" class="search-trigger search-icon d-lg-none d-block">
                                <i class="fal fa-search"></i>
                            </a>
                            <button class="navbar-toggle-btn d-block d-lg-none" type="button">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </button>
                        </div>
                    </div>
                    <div class="navbar-toggle-item">
                        <ul
                            class="custom-nav d-lg-flex d-grid gap-xxl-10 gap-xl-8 gap-lg-5 gap-md-2 gap-2 pt-lg-0 pt-5">
                            
                            <li class="menu-item position-relative">
                                <a href="/" wire:navigate class="fw_500">
                                    Home
                                </a>
                            </li>
                            <li class="menu-item position-relative">
                                <a href="/project"  wire:navigate class="fw_500">
                                    Project
                                </a>
                            </li>
                            <li class="menu-item position-relative">
                                <a href="/blog" wire:navigate class="fw_500">
                                    Blog
                                </a>
                            </li>
                            <li class="menu-item position-relative">
                                <a href="/carrer" wire:navigate class="fw_500">
                                    Carrer
                                </a>
                            </li>
                            <li class="menu-item position-relative">
                                <a href="/contact" wire:navigate class="fw_500">
                                    Contact
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                    <div
                        class="d-lg-flex d-none d-grid justify-content-center ph-clickwrap align-items-center gap-xxl-7 gap-xl-6 gap-lg-5 gap-3">
                        
                        <a href="contact.html" class="d-flex align-items-center gap-sm-3 gap-2 touch-btn cmn-btn">
                            <span class="rot60">
                                <i class="fas fa-arrow-up"></i>
                            </span>
                            Hubungi Kami
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</header>
<!-- Custom Header End -->