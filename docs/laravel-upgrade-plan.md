# Panduan Rencana Upgrade Major: Laravel 10 ke Laravel 11 & 12 (Medantainment)

Dokumen ini menjelaskan strategi teknis dan langkah-langkah mitigasi untuk melakukan upgrade *major version* kerangka kerja **Laravel 10** ke **Laravel 11** (dan selanjutnya ke **Laravel 12**) tanpa mengabaikan kestabilan fungsionalitas Livewire 3 dan Filament 3 yang sedang digunakan.

---

## 1. Prasetel & Persyaratan Lingkungan (Environment Prerequisites)

| Komponen | Status Saat Ini (Laravel 10) | Persyaratan Laravel 11 / 12 | Tindakan Kebutuhan |
|---|---|---|---|
| **Version PHP** | `^8.1` / `^8.2` (Terkini: PHP 8.4) | Minimal **PHP 8.2+** (Laravel 11)<br>Minimal **PHP 8.3+** (Laravel 12) | Server lokal & production diwajibkan berjalan minimal di **PHP 8.3**. |
| **Database** | MySQL / SQLite | MySQL 8.0+ / MariaDB 10.3+ / SQLite 3.35+ | Pastikan struktur index MySQL mencukupi prasyarat engine InnoDB terbaru. |
| **Livewire** | Version 3.x | Livewire `^3.4+` | Tetap kompatibel dengan perbaruan minor di composer. |
| **Filament** | Version 3.x (`3.2.141`) | Filament `^3.2+` | Kompatibilitas sudah mendukung Laravel 11 & 12 (via pembaruan paket penunjang). |

---

## 2. Inventarisasi Tantangan & Breaking Changes Utama

Selama transisi menuju Laravel 11+, terdapat beberapa arsitektur baru Laravel yang mengubah standar kebiasaan dari versi 10:

1. **Perubahan Struktur Aplikasi (Slim Skeleton & Bootstrap / App Configs):**
   * Pada Laravel 11, folder `app/Http/Middleware` dan `app/Providers/AuthServiceProvider` (serta beberapa provider standar lain) tidak wajib ada dan disalin ke `bootstrap/app.php` secara terpusat.
   * *Mitigasi:* **Jangan merombak struktur aplikasi lama secara manual.** Laravel 11/12 sepenuhnya mendukung struktur file bergaya lama (Laravel 10). Biarkan `AuthServiceProvider` (yang baru kita perkuat) tetap bekerja di tempatnya.
2. **Perilaku Casts pada Model Eloquent:**
   * Di Laravel 11+, mutator casting disarankan menggunakan method `casts(): array` ketimbang properti protected `$casts = []`.
   * *Mitigasi:* Properti `$casts` tradisional tetap kompatibel (backward-compatible), namun untuk model baru dapat ditransisi berangsur-angsur.
3. **Ketergantungan Paket Pihak Ketiga (Third-party Dependencies):**
   * Perhatian utama terpusat pada package `openspout/openspout` dan `mews/purifier` di PHP 8.3 / 8.4.
   * *Mitigasi:* Jalankan pembaruan paket pendukung secara seiringan bersamaan dengan pengikatan constraint composer terbaru.

---

## 3. Tahapan Eksekusi Ekstensi (Upgrade Execution Plan)

### Langkah 1: Pengeringan Cache & Branching
Buat branch isolasi baru khusus pengetikan upgrade agar tidak mencemari branch utama atau branch security ini:
```bash
git checkout -b chore/upgrade-laravel-11
php artisan optimize:clear
```

### Langkah 2: Pembaruan Constraint di `composer.json`
Sesuaikan baris-baris dependensi di file `composer.json` Anda:
```diff
- "php": "^8.1",
+ "php": "^8.2|^8.3",
- "laravel/framework": "^10.10",
+ "laravel/framework": "^11.0",
- "laravel/sanctum": "^3.3",
+ "laravel/sanctum": "^4.0",
- "nunomaduro/collision": "^7.0",
+ "nunomaduro/collision": "^8.0",
- "spatie/laravel-ignition": "^2.0",
+ "spatie/laravel-ignition": "^2.4",
```

### Langkah 3: Pemutakhiran Paket Composer
Jalankan instalasi dependensi terbaru dengan parameter ramah konflik:
```bash
composer update -W --with-all-dependencies
```
*Catatan:* Jika terjadi error konflik perputaran constraint paket (seperti Filament atau Openspout), gunakan perintah diagnosis:
```bash
composer why-not laravel/framework 11.0.0
```
Dan perbaiki batasan versi paket terkait di `composer.json`.

### Langkah 4: Pemutakhiran Database & Konfigurasi Cache
Setelah composer berhasil menyelesaikan resolving dependensi tanpa error:
```bash
php artisan filament:upgrade
php artisan config:clear
php artisan migrate --force
```

---

## 4. Rencana Verifikasi Keamanan Pasca-Upgrade

Setelah Laravel di-upgrade ke 11/12, pengamanan (security hardening) yang telah kita jalankan harus dipertahankan. Lakukan verifikasi mendalam secara otomatis:

1. **Jalankan Automated Security Tests:**
   ```bash
   php artisan test --testsuite=Feature
   ```
   Pastikan seluruh test (`UserResourceAuthorizationTest`, `FileUploadSecurityTest`, `BlogXssTest`, `PrivateFileAccessTest`, `CareerFormTest`, `VisitorDeduplicationTest`) mengembalikan nilai **PASSED (Green)**.

2. **Pengecekan Manual Panel Filament & Request:**
   * Ujilah login sebagai akun role `author`: pastikan penolakan akses (HTTP 403 Forbidden) ketika mengintip menu manajemen pengguna di `/admin/users` tetap solid.
   * Verifikasi pengajuan lowongan pada `/career` dan pastikan rate limiter bekerja mematuhi pengeringan kuota cache SHA-256 yang dibagun.
   * Uji coba upload file non-PDF berkedok ekstensi `.pdf` pada form career pelamar terbukti tetap dicekik oleh validasi *magic bytes* MIME type.

---

## 5. Strategi Rollback Jaga-Jaga

Apabila penyesuaian dependensi Laravel 11 mematikan sebagian modul atau berakibat kebocoran memori (memory fault):
1. Jangan lakukan *force-push* ke branch produksi/main.
2. Balikkan kembali ke snapshot kompensasi branch ini:
   ```bash
   git checkout fix/security-hardening
   composer install
   php artisan optimize:clear
   ```
3. Lakukan proses isolatif *debugging* komponen yang memicu kesalahan kompilasi dan catat pada arsip *issues*.
