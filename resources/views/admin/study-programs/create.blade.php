<x-app-layout>

    <x-slot:header>
        <x-page-header
            eyebrow="Program Studi"
            title="Tambah Program Studi">
            <x-slot:actions>
                <a href="{{ route('admin.study-programs.index') }}" class="btn-ghost btn-sm">← Kembali</a>
            </x-slot:actions>
        </x-page-header>
    </x-slot:header>

    <div class="max-w-xl mx-auto">
        <form method="POST" action="{{ route('admin.study-programs.store') }}">
            @csrf

            <x-section label="Informasi Program Studi">
                <div class="space-y-5">

                    <div class="form-field">
                        <label class="form-label form-required">Nama Program Studi</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-input @error('name') border-status-rejected @enderror"
                               placeholder="cth. Teknik Informatika" required>
                        @error('name') <p class="text-xs text-status-rejected mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-field">
                        <label class="form-label form-required">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-input @error('email') border-status-rejected @enderror"
                               placeholder="cth. ti.ukrida@gmail.com" required>
                        <p class="form-hint">Email Gmail program studi untuk autentikasi dosen.</p>
                        @error('email') <p class="text-xs text-status-rejected mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-field">
                        <label class="form-label form-required">Password</label>
                        <input type="password" name="password"
                               class="form-input @error('password') border-status-rejected @enderror"
                               placeholder="Minimal 8 karakter" required>
                        @error('password') <p class="text-xs text-status-rejected mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-field">
                        <label class="form-label form-required">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                               class="form-input"
                               placeholder="Ulangi password" required>
                    </div>

                </div>
            </x-section>

            <div class="flex items-center justify-between pt-6 border-t border-rule">
                <a href="{{ route('admin.study-programs.index') }}" class="btn-ghost">Batal</a>
                <button type="submit" class="btn-mark">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Program Studi
                </button>
            </div>

        </form>
    </div>

</x-app-layout>
