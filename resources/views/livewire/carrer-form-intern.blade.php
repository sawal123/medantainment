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
                            <div class="card-header">
                                <h5 class="mb-0">Isi Formulir</h5>
                                <small class="text-muted">Isi Data Dengan Benar!</small>
                            </div>
                            <div class="card-body">

                                @if (session()->has('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                                @endif

                                <div class="">
                                    <form wire:submit.prevent="simpan" enctype="multipart/form-data">


                                        <input type="hidden" wire:model="carrer_id" value="{{$carrer_id}}">
                                        <div class="row">
                                            <div class="col-12 col-sm-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama</label>
                                                    <input type="text" class="form-control" wire:model="nama">
                                                    @error('nama') <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>

                                            </div>
                                            <!-- 2. TTL -->
                                            <div class="col-12 col-sm-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Tempat Tgl Lahir</label>
                                                    <input type="text" class="form-control" wire:model="ttl">
                                                    @error('ttl') <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>

                                        <!-- 3. Alamat -->
                                        <div class="mb-3">
                                            <label class="form-label">Alamat</label>
                                            <textarea class="form-control" rows="3" wire:model="alamat"></textarea>
                                            @error('alamat') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-12 col-sm-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Sekolah / Universitas</label>
                                                    <input type="text" class="form-control"
                                                        wire:model="sekolah_universitas">
                                                    @error('sekolah_universitas') <small class="text-danger">{{ $message
                                                        }}</small> @enderror
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <!-- 5. Jurusan -->
                                                <div class="mb-3">
                                                    <label class="form-label">Jurusan</label>
                                                    <input type="text" class="form-control" wire:model="jurusan">
                                                    @error('jurusan') <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>

                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <!-- 6. Periode Magang -->
                                                <div class="mb-3">
                                                    <label class="form-label">Periode Magang</label>
                                                    <input type="text" class="form-control" wire:model="periode_magang">
                                                    @error('periode_magang') <small class="text-danger">{{ $message
                                                        }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <!-- 4. Sekolah / Universitas -->




                                        <!-- 7. Keahlian -->
                                        <div class="mb-3">
                                            <label class="form-label">Keahlian</label>
                                            <textarea class="form-control" rows="3" wire:model="keahlian"></textarea>
                                            @error('keahlian') <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>


                                        <div class="card bg-dark">
                                            <div class="card-body">
                                                <!-- 8. Ketertarikan -->
                                                <div class="mb-3">
                                                    <label class="form-label">Ketertarikan</label><br>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="Director"
                                                            wire:model="ketertarikan">
                                                        <label class="form-check-label">Director</label>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="Producer"
                                                            wire:model="ketertarikan">
                                                        <label class="form-check-label">Producer</label>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            value="Scriptwriter" wire:model="ketertarikan">
                                                        <label class="form-check-label">Scriptwriter</label>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="DoP"
                                                            wire:model="ketertarikan">
                                                        <label class="form-check-label">DoP</label>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="Editor"
                                                            wire:model="ketertarikan">
                                                        <label class="form-check-label">Editor</label>
                                                    </div>

                                                    @error('ketertarikan') <small class="text-danger">{{ $message
                                                        }}</small>
                                                    @enderror
                                                </div>

                                                <!-- 9. Kalimat Ketertarikan -->
                                                <div class="mb-3">
                                                    <label class="form-label">1 kalimat tentang ketertarikan
                                                        tersebut</label>
                                                    <textarea class="form-control" rows="2"
                                                        wire:model="ketertarangan_singkat"></textarea>
                                                    @error('ketertarangan_singkat') <small class="text-danger">{{
                                                        $message
                                                        }}</small> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 10. Surat Izin -->
                                        <div class="card bg-dark mt-4">
                                            <div class="card-header">
                                                <h5 class="mb-0">Upload Berkas Kamu</h5>
                                                {{-- <small class="text-muted">Pilih nilai 1–5</small> --}}
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12 col-sm-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Surat Izin</label>
                                                            <input type="file" class="form-control"
                                                                wire:model="surat_izin">
                                                            @error('surat_izin') <small class="text-danger">{{ $message
                                                                }}</small>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <!-- 11. Surat Lamaran -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Surat Lamaran</label>
                                                            <input type="file" class="form-control"
                                                                wire:model="surat_lamaran">
                                                            @error('surat_lamaran') <small class="text-danger">{{
                                                                $message
                                                                }}</small>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6">

                                                        <!-- 12. CV & Portofolio -->
                                                        <div class="mb-3">
                                                            <label class="form-label">CV & Portofolio</label>
                                                            <input type="file" class="form-control"
                                                                wire:model="cv_portofolio">
                                                            @error('cv_portofolio') <small class="text-danger">{{
                                                                $message
                                                                }}</small>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6">

                                                        <!-- 13. Foto Diri -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Foto Diri</label>
                                                            <input type="file" class="form-control"
                                                                wire:model="foto_diri">
                                                            @error('foto_diri') <small class="text-danger">{{ $message
                                                                }}</small>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>





                                            </div>
                                        </div>

                                        <!-- 14. Rating -->
                                        <div class="card mt-4 bg-dark text-white">
                                            <div class="card-header">
                                                <h5 class="mb-0">Beri Rating Diri Kamu</h5>
                                                <small class="text-muted">Pilih nilai 1–5</small>
                                            </div>

                                            <div class="card-body">

                                                <div class="row g-4">

                                                    {{-- ITEM --}}
                                                    <div class="col-md-6">
                                                        <label class="fw-semibold mb-2">Kreatifitas</label>
                                                        <select class="form-control w-100"
                                                            wire:model="rating_kreatifitas">
                                                            <option value="">Pilih</option>
                                                            @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i
                                                                }}</option>
                                                                @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="fw-semibold mb-2">Kemampuan Berpikir
                                                            Analitis</label>
                                                        <select class="form-control w-100" wire:model="rating_analitis">
                                                            <option value="">Pilih</option>
                                                            @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i
                                                                }}</option>
                                                                @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="fw-semibold mb-2">Kemampuan Berkomunikasi</label>
                                                        <select class="form-control w-100"
                                                            wire:model="rating_komunikasi">
                                                            <option value="">Pilih</option>
                                                            @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i
                                                                }}</option>
                                                                @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="fw-semibold mb-2">Kemampuan Manajemen
                                                            Waktu</label>
                                                        <select class="form-control w-100"
                                                            wire:model="rating_manajemen_waktu">
                                                            <option value="">Pilih</option>
                                                            @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i
                                                                }}</option>
                                                                @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="fw-semibold mb-2">Kemampuan Beradaptasi</label>
                                                        <select class="form-control w-100" wire:model="rating_adaptasi">
                                                            <option value="">Pilih</option>
                                                            @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i
                                                                }}</option>
                                                                @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="fw-semibold mb-2">Kemampuan Kerja Tim</label>
                                                        <select class="form-control w-100" wire:model="rating_teamwork">
                                                            <option value="">Pilih</option>
                                                            @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i
                                                                }}</option>
                                                                @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="fw-semibold mb-2">Motivasi dan Inisiatif</label>
                                                        <select class="form-control w-100" wire:model="rating_motivasi">
                                                            <option value="">Pilih</option>
                                                            @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i
                                                                }}</option>
                                                                @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="fw-semibold mb-2">Kemampuan Mengatasi
                                                            Tekanan</label>
                                                        <select class="form-control w-100" wire:model="rating_tekanan">
                                                            <option value="">Pilih</option>
                                                            @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i
                                                                }}</option>
                                                                @endfor
                                                        </select>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>




                                        <!-- 15. Alasan Internship -->
                                        <div class="mb-3">
                                            <label class="form-label">Alasan Mengikuti Internship</label>
                                            <textarea class="form-control" rows="3"
                                                wire:model="alasan_internship"></textarea>
                                        </div>

                                        <!-- BUTTON -->
                                        <button type="submit" class="btn btn-primary">Kirim Intern</button>

                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </section>
    </main>
    <script>
        window.addEventListener('scroll-to-top', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    </script>

</div>
