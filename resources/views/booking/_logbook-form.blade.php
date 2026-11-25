{{--
    Logbook form partial — included in booking/show.blade.php
    Only rendered when booking status is 'approved' or 'completed'
    Required vars: $booking
--}}
@php
    $lb = $booking->logbook;
    $hasFace = auth()->user()?->hasFaceRegistered() ?? false;
@endphp
<div x-data="createLogbookFaceHandler({{ $hasFace ? 'true' : 'false' }})">
    <x-logbook-face-modal />

    <form method="POST" action="{{ route('booking.logbook.update', $booking) }}"
          class="bg-white border border-rule rounded-xl shadow-card p-6 space-y-5"
          @submit.prevent="submitWithFaceVerification($event, '{{ $booking->booking_code }}')">
        @csrf
        @method('PUT')

        <input type="hidden" name="is_face_verified" value="{{ $lb?->is_face_verified ? '1' : '0' }}">
        <input type="hidden" name="face_embedding" value="">

        <div class="form-field">
            <label class="form-label form-required">Checkpoint / Progress Kegiatan</label>
            <textarea name="checkpoint_progress" class="form-textarea" rows="4"
                      placeholder="Jelaskan tahap kegiatan yang sudah diselesaikan dalam sesi ini…"
                      required>{{ old('checkpoint_progress', $lb->checkpoint_progress ?? '') }}</textarea>
            <p class="form-hint">Deskripsikan secara singkat apa yang berhasil diselesaikan (min. 10 karakter).</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="form-field">
                <label class="form-label">Nama Pembimbing</label>
                <input type="text" name="supervisor_name" class="form-input"
                       placeholder="cth. Dr. Budi Santoso"
                       value="{{ old('supervisor_name', $lb->supervisor_name ?? '') }}">
            </div>
            <div class="form-field">
                <label class="form-label">Mata Kuliah Terkait</label>
                <input type="text" name="related_course" class="form-input"
                       placeholder="cth. Kecerdasan Buatan"
                       value="{{ old('related_course', $lb->related_course ?? '') }}">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-rule">
            <button type="submit" class="btn-mark inline-flex items-center gap-1.5 cursor-pointer">
                @if ($hasFace)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Verifikasi Wajah & Simpan Logbook</span>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Logbook</span>
                @endif
            </button>
        </div>
    </form>
</div>
