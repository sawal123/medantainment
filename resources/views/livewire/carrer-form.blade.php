<div>

    <!-- Custom Line Shape -->
    <div class="line-shape cus-z-1 first w-100 h-100 d-flex flex-wrap"></div>
    <!-- Custom Line Shape -->
    <main class="main position-relative overflow-hidden" id="mains" >
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
                                @if ($carrer && $carrer->status !== 'open')
                                <div class="alert alert-warning mb-4">
                                    ⚠️ Pendaftaran untuk lowongan <strong>{{ $title }}</strong> telah ditutup. Anda tidak dapat mengirim berkas lamaran baru.
                                </div>
                                @endif
                                <form wire:submit.prevent="save">
                                    @csrf
                                    {{-- ✅ [SECURITY FIX #3] Tampilkan pesan rate limit jika ada --}}
                                    @error('rate_limit')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    {{-- ✅ [SECURITY FIX #4] Hapus duplikasi value= , cukup wire:model saja --}}
                                    <input type="hidden" wire:model="carrer_id">
                                    <div class="mb-3">
                                        <label class="form-label">Posisi yang Dilamar</label>
                                        {{-- ✅ [SECURITY FIX #2] Readonly — cegah manipulasi data posisi oleh user --}}
                                        <input type="text" class="form-control bg-secondary text-light" wire:model="title" readonly>
                                        @error('carrer')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" wire:model="name" @if($carrer && $carrer->status !== 'open') disabled @endif>
                                        @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" wire:model="email" @if($carrer && $carrer->status !== 'open') disabled @endif>
                                        @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nomor Telepon</label>
                                        <input type="text" class="form-control" wire:model="phone" @if($carrer && $carrer->status !== 'open') disabled @endif>
                                        @error('phone')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Upload CV (PDF/DOC)</label>
                                        <input type="file" class="form-control" wire:model="resume" @if($carrer && $carrer->status !== 'open') disabled @endif>
                                        @error('resume')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Surat Lamaran (Opsional)</label>
                                        <textarea class="form-control" wire:model="cover_letter" rows="4" @if($carrer && $carrer->status !== 'open') disabled @endif></textarea>
                                        @error('cover_letter')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary" @if($carrer && $carrer->status !== 'open') disabled @endif>
                                        @if($carrer && $carrer->status !== 'open')
                                            Pendaftaran Ditutup
                                        @else
                                            Kirim Lamaran
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </section>
    </main>

</div>
