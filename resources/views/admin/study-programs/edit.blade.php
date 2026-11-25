<x-app-layout>

    <x-slot:header>
        <x-page-header
            eyebrow="Program Studi"
            :title="'Edit — ' . $program->name">
            <x-slot:actions>
                <a href="{{ route('admin.study-programs.index') }}" class="btn-ghost btn-sm">← Kembali</a>
            </x-slot:actions>
        </x-page-header>
    </x-slot:header>

    <div class="max-w-xl mx-auto">
        <form method="POST" action="{{ route('admin.study-programs.update', $program) }}">
            @csrf
            @method('PUT')

            <x-section label="Informasi Program Studi">
                <div class="space-y-5">

                    <div class="form-field">
                        <label class="form-label form-required">Nama Program Studi</label>
                        <input type="text" name="name" value="{{ old('name', $program->name) }}"
                               class="form-input @error('name') border-status-rejected @enderror"
                               placeholder="cth. Teknik Informatika" required>
                        @error('name') <p class="text-xs text-status-rejected mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-field">
                        <label class="form-label form-required">Email</label>
                        <input type="email" name="email" value="{{ old('email', $program->email) }}"
                               class="form-input @error('email') border-status-rejected @enderror"
                               placeholder="cth. ti.ukrida@gmail.com" required>
                        <p class="form-hint">Email Gmail program studi untuk autentikasi dosen.</p>
                        @error('email') <p class="text-xs text-status-rejected mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>
            </x-section>

            <x-section label="Ubah Password" class="mt-6">
                <div class="space-y-5">

                    <div class="form-field">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password"
                               class="form-input @error('password') border-status-rejected @enderror"
                               placeholder="Kosongkan jika tidak ingin mengubah">
                        <p class="form-hint">Minimal 8 karakter. Biarkan kosong jika tidak ingin mengubah password.</p>
                        @error('password') <p class="text-xs text-status-rejected mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-field">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation"
                               class="form-input"
                               placeholder="Ulangi password baru">
                    </div>

                </div>
            </x-section>

            <x-section label="Status" class="mt-6">
                <label class="flex items-center gap-3 p-3 rounded-lg border border-rule bg-white cursor-pointer hover:bg-ink-50/50 has-[:checked]:border-ink-700 has-[:checked]:bg-ink-50/40 transition-all">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $program->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 accent-ink-700 rounded">
                    <div>
                        <div class="text-sm font-medium text-ink-900">Program studi aktif</div>
                        <div class="text-xs text-ink-700/50">Program studi nonaktif tidak akan muncul sebagai pilihan saat mendaftarkan dosen atau tim baru</div>
                    </div>
                </label>
            </x-section>

            <div class="flex items-center justify-between pt-6 border-t border-rule mt-6">
                <a href="{{ route('admin.study-programs.index') }}" class="btn-ghost">Batal</a>
                <button type="submit" class="btn-mark">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

</x-app-layout>
