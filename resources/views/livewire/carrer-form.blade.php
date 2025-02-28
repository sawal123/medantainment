<div>

    <!-- Custom Line Shape -->
    <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
    <!-- Custom Line Shape -->
    <main class="main position-relative overflow-hidden" id="mains">
        <section class="hero-section-version1 bnbg position-relative">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card bg-dark">
                            <div class="card-body">
                                @if (session()->has('message'))
                                    <div class="alert alert-success">
                                        {{ session('message') }}
                                    </div>
                                @endif
                                <form wire:submit.prevent="save">
                                    <div class="mb-3">
                                        <label class="form-label">Posisi yang Dilamar</label>
                                        <input type="text" class="form-control" wire:model="title">
                                        @error('carrer')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" wire:model="name">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" wire:model="email">
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nomor Telepon</label>
                                        <input type="text" class="form-control" wire:model="phone">
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Upload CV (PDF/DOC)</label>
                                        <input type="file" class="form-control" wire:model="resume">
                                        @error('resume')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Surat Lamaran (Opsional)</label>
                                        <textarea class="form-control" wire:model="cover_letter" rows="4"></textarea>
                                        @error('cover_letter')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary">Kirim Lamaran</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </section>
    </main>

</div>
