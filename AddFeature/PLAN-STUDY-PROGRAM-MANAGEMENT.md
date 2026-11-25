# Rencana: Fitur Manajemen Program Studi

## Latar Belakang

Tabel `study_programs` sudah ada di database dengan kolom `id`, `name`, `email`, `password`, `is_active`, `created_at`, `updated_at`. Model `StudyProgram` juga sudah ada dengan relasi `users()` dan `teams()`. Saat ini, data program studi hanya dikelola langsung melalui database — belum ada halaman admin untuk mengelolanya.

### Data Saat Ini di Database

| ID | Nama | Email | Aktif |
|---|---|---|---|
| 1 | Teknik Informatika | ti.ukrida@gmail.com | ✅ |
| 2 | Sistem Informasi | si.ukrida@gmail.com | ✅ |
| 3 | Teknik Elektro | te.ukrida@gmail.com | ✅ |
| 4 | Teknik Industri | tk.ukrida@gmail.com | ✅ |

## Fitur yang Akan Dibuat

1. **Daftar Program Studi** — Tabel yang menampilkan semua program studi beserta nama, email, status aktif, jumlah dosen, dan jumlah tim
2. **Tambah Program Studi** — Form: nama, email, password
3. **Edit Program Studi** — Form: edit nama, ubah email, ubah password (opsional)

## File yang Akan Dibuat/Diubah

### Controller

#### [NEW] `app/Http/Controllers/Admin/AdminStudyProgramController.php`

CRUD controller mengikuti pola `AdminUserController`:

- `index()` — Daftar semua program studi, diurutkan berdasarkan nama. Eager-load `withCount(['users', 'teams'])` untuk menampilkan jumlah dosen dan tim di tabel.
- `create()` — Form tambah program studi baru.
- `store(StoreStudyProgramRequest)` — Simpan program studi baru. Hash password. Catat audit log `study_program.created`.
- `edit(StudyProgram)` — Form edit program studi.
- `update(UpdateStudyProgramRequest, StudyProgram)` — Update data. Password di-hash hanya jika diisi. Catat audit log `study_program.updated`.

---

### Form Requests (Validasi)

#### [NEW] `app/Http/Requests/Admin/StoreStudyProgramRequest.php`

```php
'name'     => ['required', 'string', 'max:255'],
'email'    => ['required', 'email', 'max:255', 'unique:study_programs,email'],
'password' => ['required', 'string', 'min:8', 'confirmed'],
```

#### [NEW] `app/Http/Requests/Admin/UpdateStudyProgramRequest.php`

```php
'name'     => ['required', 'string', 'max:255'],
'email'    => ['required', 'email', 'max:255', 'unique:study_programs,email,' . $this->study_program->id],
'password' => ['nullable', 'string', 'min:8', 'confirmed'],
```

---

### Routes

#### [MODIFY] `routes/web.php`

Tambah di dalam group admin, di bawah blok Teams:

```php
use App\Http\Controllers\Admin\AdminStudyProgramController;

// Study Programs
Route::get ('/study-programs',                    [AdminStudyProgramController::class, 'index'])->name('study-programs.index');
Route::get ('/study-programs/create',             [AdminStudyProgramController::class, 'create'])->name('study-programs.create');
Route::post('/study-programs',                    [AdminStudyProgramController::class, 'store'])->name('study-programs.store');
Route::get ('/study-programs/{study_program}/edit',[AdminStudyProgramController::class, 'edit'])->name('study-programs.edit');
Route::put ('/study-programs/{study_program}',    [AdminStudyProgramController::class, 'update'])->name('study-programs.update');
```

---

### Views (Blade Templates)

#### [NEW] `resources/views/admin/study-programs/index.blade.php`

Halaman daftar program studi:
- Header: "Program Studi" dengan tombol "+ Tambah Program Studi"
- Tabel: kolom Nama, Email, Dosen, Tim, Status, Aksi (Edit)
- Kolom Dosen: jumlah user dengan `role != 'admin'` di program studi tersebut
- Kolom Tim: jumlah tim di program studi tersebut
- Badge status: Aktif (hijau) / Nonaktif (abu-abu)
- Desain mengikuti pola `admin/users/index.blade.php`

#### [NEW] `resources/views/admin/study-programs/create.blade.php`

Form tambah:
- Input Nama (text, required)
- Input Email (email, required)
- Input Password (password, required)
- Input Konfirmasi Password (password, required)
- Tombol: Simpan + Batal (kembali ke index)
- Desain mengikuti pola `admin/users/create.blade.php`

#### [NEW] `resources/views/admin/study-programs/edit.blade.php`

Form edit:
- Input Nama (text, required, pre-filled)
- Input Email (email, required, pre-filled)
- Input Password Baru (password, opsional — kosong = tidak diubah)
- Input Konfirmasi Password Baru (password, opsional)
- Checkbox Status Aktif
- Tombol: Simpan Perubahan + Batal
- Desain mengikuti pola `admin/users/edit.blade.php`

---

### Sidebar

#### [MODIFY] `resources/views/components/app-sidebar.blade.php`

Tambah menu "Program Studi" di bawah "Pengguna & Tim" dalam section "Manajemen":

```html
<a href="{{ route('admin.study-programs.index') }}"
   class="nav-item {{ str_starts_with($current, 'admin.study-programs') ? 'active' : '' }}">
    <svg class="nav-item-icon" ...><!-- academic cap / book icon --></svg>
    <span>Program Studi</span>
</a>
```

---

## Tidak Perlu Diubah

| File | Alasan |
|---|---|
| `app/Models/StudyProgram.php` | Model sudah ada dan lengkap (`fillable`, `casts`, relasi) |
| Database / Migration | Tabel `study_programs` sudah ada dengan kolom yang dibutuhkan |
| `AuditLogService` | Service sudah generik — cukup panggil `record()` |

---

## Urutan Eksekusi

1. Buat form request: `StoreStudyProgramRequest.php` dan `UpdateStudyProgramRequest.php`
2. Buat controller: `AdminStudyProgramController.php`
3. Tambah routes di `web.php`
4. Buat views: `index.blade.php`, `create.blade.php`, `edit.blade.php`
5. Tambah menu sidebar di `app-sidebar.blade.php`
6. Verifikasi di browser

## Verifikasi

- [ ] Menu "Program Studi" muncul di sidebar admin
- [ ] Halaman daftar menampilkan 4 program studi yang sudah ada
- [ ] Kolom dosen dan tim menampilkan jumlah yang benar
- [ ] Tambah program studi baru berhasil (nama, email, password)
- [ ] Validasi: email unik, password minimal 8 karakter + konfirmasi
- [ ] Edit program studi: ubah nama, email berhasil
- [ ] Edit program studi: ubah password (opsional) berhasil
- [ ] Edit program studi: toggle status aktif/nonaktif berhasil
- [ ] Audit log mencatat `study_program.created` dan `study_program.updated`
- [ ] Flash message sukses muncul setelah simpan/edit
