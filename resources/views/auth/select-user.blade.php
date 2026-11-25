<x-auth-layout title="Pilih Akun & Verifikasi Wajah">

    {{-- Step indicator --}}
    <div class="flex items-center gap-3 mb-8">
        <div class="step-dot-complete">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <span class="text-[0.7rem] uppercase tracking-label font-semibold text-ink-700/50">Gmail Program Studi</span>
        <div class="step-connector-complete"></div>
        <div class="step-dot-active">2</div>
        <span class="text-[0.7rem] uppercase tracking-label font-semibold text-ink-900">Pilih Akun & Wajah</span>
    </div>

    <div class="page-eyebrow">{{ $program->name }}</div>
    <h2 class="font-display text-3xl font-bold text-ink-900 tracking-tight">Pilih akun Anda.</h2>
    <p class="mt-1 text-xs text-ink-700/60">
        Pilih nama Anda dan verifikasi wajah dengan kamera untuk mengakses laboratorium.
    </p>

    @if ($errors->any())
        <div class="mt-6 rounded-md border border-status-rejected/30 bg-status-rejected/5 p-3">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-status-rejected">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div x-data="selectUserLogin()" x-init="init()" class="mt-8 space-y-6">
        <form id="loginAuthForm" method="POST" action="{{ route('login.authenticate') }}" @submit.prevent="handleFormSubmit($event)" class="space-y-6">
            @csrf

            <input type="hidden" name="face_embedding" x-model="faceEmbeddingJson">
            <input type="hidden" name="is_face_verified" x-model="isFaceVerified">

            <div class="form-field">
                <label for="user_id" class="form-label form-required">Nama Pengguna</label>
                <select id="user_id" name="user_id" x-model.number="selectedUserId" @change="onUserChange($event)" required class="form-select">
                    <option value="" disabled selected>— Pilih nama Anda —</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}"
                                data-has-face="{{ $u->hasFaceRegistered() ? '1' : '0' }}"
                                data-name="{{ $u->name }}">
                            {{ $u->name }}
                            @if ($u->role === 'team') · Tim
                            @elseif ($u->role === 'admin') · Admin
                            @else · Dosen
                            @endif
                            @if (! $u->hasFaceRegistered()) (Belum Ada Wajah) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Notice if selected user has NO face registered yet --}}
            <div x-show="selectedUserId && !selectedUserHasFace" x-cloak
                 class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-2.5 transition-all">
                <div class="font-bold flex items-center gap-1.5 text-amber-800">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Pendaftaran Wajah Pertama Kali Diperlukan</span>
                </div>
                <p class="text-amber-700/90 leading-relaxed text-[11.5px]">
                    Akun ini belum memiliki data sampel wajah biometrik terdaftar. Klik tombol di bawah untuk memindai wajah Anda sekarang dan langsung masuk ke sistem.
                </p>
                <button type="button" @click="openModal('enroll')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold transition-colors cursor-pointer text-xs shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Pindai Wajah Sekarang & Masuk</span>
                </button>
            </div>

            {{-- Submit button when face exists --}}
            <template x-if="selectedUserHasFace">
                <button type="submit" class="btn-mark btn-lg w-full flex items-center justify-center gap-2 cursor-pointer shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Verifikasi Wajah & Masuk</span>
                </button>
            </template>

            <template x-if="!selectedUserId">
                <button type="button" @click="promptSelectUser()" class="btn-mark btn-lg w-full opacity-60 cursor-not-allowed">
                    <span>Pilih Akun Terlebih Dahulu</span>
                </button>
            </template>

            <a href="{{ route('login') }}"
               class="block text-center text-xs uppercase tracking-label font-semibold text-ink-700/50 hover:text-ink-700 transition-colors">
                ← Gunakan gmail prodi lain
            </a>
        </form>

        {{-- ── Face Verification / Enrollment Modal (Teleported to Body) ── --}}
        <template x-teleport="body">
            <div x-show="showFaceModal"
                 x-cloak
                 class="face-modal-wrapper fixed inset-0 z-[99999]"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="display: none;">

                {{-- Dark Backdrop (z-[99990]) --}}
                <div class="face-modal-backdrop fixed inset-0 z-[99990] bg-black/60 backdrop-blur-xs"
                     @click="closeModal()"></div>

                {{-- Centering Container (pointer-events-none) --}}
                <div class="face-modal-center-container fixed inset-0 z-[100000] flex items-center justify-center p-4 pointer-events-none">

                    {{-- Modal Card (pointer-events-auto) --}}
                    <div class="face-modal-card bg-white rounded-2xl shadow-2xl border border-rule max-w-md w-full overflow-hidden text-center p-6 relative z-[100001] pointer-events-auto"
                         @click.stop
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100">

                        {{-- Header --}}
                        <div class="flex items-center justify-between pb-3.5 border-b border-rule text-left">
                            <div>
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider mb-1"
                                     :class="modalMode === 'enroll' ? 'bg-amber-500/20 text-amber-950 border border-amber-500/30' : 'bg-mark-500/20 text-ink-900 border border-mark-500/30'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="isCameraLoading ? 'bg-amber-500 animate-ping' : (modalMode === 'enroll' ? 'bg-amber-600 animate-pulse' : 'bg-emerald-500 animate-pulse')"></span>
                                    <span x-text="modalMode === 'enroll' ? 'Pendaftaran Biometrik Perdana' : 'Autentikasi Biometrik'"></span>
                                </div>
                                <h3 class="text-base font-bold text-ink-900" x-text="modalMode === 'enroll' ? 'Pendaftaran Wajah Pertama Kali' : 'Verifikasi Wajah Pengguna'"></h3>
                            </div>
                            <button type="button" @click="closeModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-ink-400 hover:text-ink-900 hover:bg-ink-50 transition-colors cursor-pointer" title="Tutup">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Selected User Info Bar & Instructions --}}
                        <div class="mt-3 py-2 px-3 rounded-lg bg-ink-50 border border-rule text-xs text-ink-700/80 text-left">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-ink-900 truncate" x-text="selectedUserName"></span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded"
                                      :class="modalMode === 'enroll' ? 'text-amber-800 bg-amber-100 border border-amber-200' : 'text-mark-700 bg-mark-100 border border-mark-200'"
                                      x-text="modalMode === 'enroll' ? 'Pendaftaran Perdana' : 'Login Otentik'"></span>
                            </div>
                            <template x-if="modalMode === 'enroll'">
                                <p class="text-[11px] text-amber-800 font-medium mt-1.5 leading-snug">
                                    Akun Anda belum memiliki data wajah. Silakan pindai wajah Anda sekarang untuk mengaktifkan login biometrik.
                                </p>
                            </template>
                        </div>

                        {{-- Viewport Kamera --}}
                        <div class="face-camera-viewport mt-3.5 relative rounded-xl overflow-hidden bg-ink-900 aspect-video flex items-center justify-center shadow-inner">
                            <video id="loginFaceVideo" class="face-camera-video w-full h-full object-cover" style="transform: scaleX(-1);" playsinline autoplay muted></video>
                            <canvas id="loginFaceCanvas" class="face-camera-canvas absolute inset-0 w-full h-full pointer-events-none" style="transform: scaleX(-1);"></canvas>

                            {{-- Oval Reticle --}}
                            <div x-show="!isCameraLoading && !isError"
                                 class="face-camera-reticle absolute w-32 h-44 border-2 rounded-full pointer-events-none transition-all duration-300"
                                 :class="isSuccess ? 'border-emerald-400 shadow-[0_0_25px_rgba(16,185,129,0.7)]' : (isFaceDetected ? 'border-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'border-amber-400/80 border-dashed')"></div>

                            {{-- Loading Spinner Overlay --}}
                            <div x-show="isCameraLoading" class="absolute inset-0 bg-ink-950/85 flex flex-col items-center justify-center text-white p-4 text-center z-10"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100">
                                <svg class="animate-spin w-8 h-8 text-mark-500 mb-2.5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-xs font-semibold text-white/95" x-text="statusText"></span>
                                <span class="text-[11px] text-white/60 mt-1">Sedang menghubungkan ke webcam...</span>
                            </div>

                            {{-- Error Overlay inside Viewport --}}
                            <div x-show="isError && !isSuccess" class="absolute inset-0 bg-ink-950/90 flex flex-col items-center justify-center text-white p-4 text-center z-10"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100">
                                <div class="w-10 h-10 rounded-full bg-red-500/20 text-red-400 flex items-center justify-center mb-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                </div>
                                <div class="text-xs font-bold text-red-300" x-text="modalMode === 'enroll' ? 'Kendala Pendaftaran Wajah' : 'Verifikasi Tidak Cocok'"></div>
                                <div class="text-[11px] text-white/70 mt-1 max-w-xs leading-relaxed" x-text="errorMessage || statusText"></div>
                                <button type="button" @click="retryFaceScan()" class="mt-3 px-3.5 py-1.5 rounded-lg bg-mark-500 text-ink-900 text-xs font-bold hover:bg-mark-400 transition-colors cursor-pointer">
                                    Coba Pindai Lagi
                                </button>
                            </div>

                            {{-- Success Overlay with checkmark --}}
                            <div x-show="isSuccess" class="absolute inset-0 bg-emerald-950/90 backdrop-blur-xs flex flex-col items-center justify-center text-white z-20"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-90"
                                 x-transition:enter-end="opacity-100 scale-100">
                                <template x-if="capturedPhotoData">
                                    <div class="relative mb-2">
                                        <img :src="capturedPhotoData" alt="Foto Profil" class="w-16 h-16 rounded-full object-cover border-2 border-emerald-400 shadow-lg">
                                        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shadow">✓</div>
                                    </div>
                                </template>
                                <template x-if="!capturedPhotoData">
                                    <div class="w-14 h-14 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-2xl mb-2 shadow-lg">✓</div>
                                </template>
                                <div class="font-bold text-base" x-text="modalMode === 'enroll' ? 'Wajah Berhasil Didaftarkan!' : 'Wajah Terverifikasi!'"></div>
                                <div class="text-xs text-emerald-200 mt-1" x-text="modalMode === 'enroll' ? 'Sampel biometrik & foto profil tersimpan.' : ('Tingkat Kemiripan: ' + similarityScore + '%')"></div>
                                <div class="text-[11px] text-white/70 mt-1">Membuka sesi sistem laboratorium...</div>
                            </div>
                        </div>

                        {{-- Status message --}}
                        <div class="mt-3.5 p-2.5 rounded-xl text-xs font-semibold flex items-center justify-center gap-2"
                             :class="isSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : (isError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-ink-50 text-ink-700 border border-rule')">
                            <template x-if="isCameraLoading">
                                <svg class="animate-spin w-3.5 h-3.5 text-mark-600 shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="statusText"></span>
                        </div>

                        {{-- Auto-capture progress indicator --}}
                        <div class="mt-2 h-1.5 w-full rounded-full bg-slate-200 overflow-hidden" x-show="!isCameraLoading && !isSuccess && !isError">
                            <div class="h-full bg-blue-600 transition-all duration-150" :style="'width:' + Math.round(autoProgress * 100) + '%'"></div>
                        </div>

                        <button type="button" x-show="livenessTimedOut" @click="livenessRetry = true" class="mt-2 w-full py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md cursor-pointer pointer-events-auto">Ulangi Tantangan</button>
                        {{-- Multi-step Actions (Never disabled during loading) --}}
                        <div class="mt-4 pt-3 border-t border-rule flex items-center justify-between gap-2">
                            <button type="button" @click="closeModal()"
                                    class="flex-1 py-2.5 px-3 rounded-xl border border-rule text-xs font-semibold text-ink-700 hover:bg-ink-50 transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-ink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                <span>Kembali</span>
                            </button>
                            <button type="button" x-show="isError" @click="retryFaceScan()"
                                    class="py-2.5 px-4 rounded-xl bg-ink-900 text-white text-xs font-bold hover:bg-ink-800 transition-colors cursor-pointer">
                                Coba Lagi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

@push('styles')
<style>
.face-modal-wrapper {
    position: fixed !important;
    inset: 0 !important;
    z-index: 99999 !important;
    pointer-events: auto !important;
}
.face-modal-backdrop {
    position: fixed !important;
    inset: 0 !important;
    z-index: 99990 !important;
    background-color: rgba(10, 26, 71, 0.65) !important;
    backdrop-filter: blur(4px) !important;
    -webkit-backdrop-filter: blur(4px) !important;
    pointer-events: auto !important;
}
.face-modal-center-container {
    position: fixed !important;
    inset: 0 !important;
    z-index: 100000 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 1rem !important;
    pointer-events: none !important;
    overflow-y: auto !important;
}
.face-modal-card {
    position: relative !important;
    z-index: 100001 !important;
    pointer-events: auto !important;
    max-width: 28rem !important;
    width: 100% !important;
    border-radius: 1rem !important;
    background-color: #ffffff !important;
    box-shadow: 0 25px 50px -12px rgba(10, 26, 71, 0.35) !important;
    overflow: hidden !important;
    isolation: isolate !important;
}
.face-camera-viewport {
    position: relative !important;
    overflow: hidden !important;
    border-radius: 0.75rem !important;
    background-color: #0A1A47 !important;
    aspect-ratio: 16 / 9 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    isolation: isolate !important;
    z-index: 1 !important;
}
.face-camera-video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transform: scaleX(-1) !important;
    position: relative !important;
    z-index: 1 !important;
}
.face-camera-canvas {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
    pointer-events: none !important;
    transform: scaleX(-1) !important;
    z-index: 2 !important;
}
.face-camera-reticle {
    position: absolute !important;
    width: 8rem !important;
    height: 11rem !important;
    border-width: 2px !important;
    border-radius: 9999px !important;
    pointer-events: none !important;
    transition: all 0.3s ease !important;
    z-index: 3 !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<x-face-autocapture />
<script>
function selectUserLogin() {
    const FACE_VERIFY_URL = @json(route('login.verify-face'));
    const FACE_ENROLL_URL = @json(route('login.enroll-face'));
    const FACE_MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

    return {
        selectedUserId: '',
        selectedUserName: '',
        selectedUserHasFace: false,
        modalMode: 'verify', // 'verify' or 'enroll'
        showFaceModal: false,
        capturedPhotoData: '',
        isCameraLoading: false,
        isFaceDetected: false,
        isProcessing: false,
        autoProgress: 0,
        livenessTimedOut: false,
        livenessRetry: false,
        currentDescriptor: null,
        isSuccess: false,
        isError: false,
        errorMessage: '',
        statusText: 'Mempersiapkan kamera...',
        similarityScore: 0,
        faceEmbeddingJson: '',
        isFaceVerified: 0,
        faceStream: null,
        faceAnimId: null,
        faceModelsLoaded: false,
        sessionId: 0,

        init() {
            const selectEl = document.getElementById('user_id');
            if (selectEl && selectEl.value) {
                this.selectedUserId = parseInt(selectEl.value);
                const opt = selectEl.options[selectEl.selectedIndex];
                this.selectedUserHasFace = opt.dataset.hasFace === '1';
                this.selectedUserName = opt.dataset.name || '';
            }
        },

        onUserChange(e) {
            const select = e.target;
            const opt = select.options[select.selectedIndex];
            if (opt && opt.value) {
                this.selectedUserId = parseInt(opt.value);
                this.selectedUserHasFace = opt.dataset.hasFace === '1';
                this.selectedUserName = opt.dataset.name || '';
            } else {
                this.selectedUserId = '';
                this.selectedUserHasFace = false;
                this.selectedUserName = '';
            }
        },

        promptSelectUser() {
            alert('Silakan pilih nama akun Anda pada menu dropdown terlebih dahulu.');
        },

        handleFormSubmit(e) {
            if (!this.selectedUserId) {
                this.promptSelectUser();
                return;
            }

            if (!this.selectedUserHasFace) {
                this.openModal('enroll');
                return;
            }

            this.openModal('verify');
        },

        openModal(mode = 'verify') {
            this.modalMode = mode;
            this.showFaceModal = true;
            this.capturedPhotoData = '';
            this.isSuccess = false;
            this.isError = false;
            this.errorMessage = '';
            this.isFaceDetected = false;
            this.statusText = mode === 'enroll'
                ? 'Mempersiapkan kamera untuk pendaftaran wajah...'
                : 'Mempersiapkan kamera...';
            const curSession = ++this.sessionId;

            this.$nextTick(() => {
                this.startCamera(curSession);
            });
        },

        getVideo() {
            return document.getElementById('loginFaceVideo');
        },

        getCanvas() {
            return document.getElementById('loginFaceCanvas');
        },

        async startCamera(curSession) {
            if (curSession !== this.sessionId || !this.showFaceModal) return;

            this.isCameraLoading = true;
            this.isError = false;
            this.errorMessage = '';

            try {
                // 1. Inisialisasi Model AI dengan timeout 12 detik
                if (!this.faceModelsLoaded) {
                    this.statusText = 'Memuat model AI deteksi wajah...';
                    const loadModelPromise = Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(FACE_MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(FACE_MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(FACE_MODEL_URL),
                    ]);

                    const timeoutModelPromise = new Promise((_, reject) => {
                        setTimeout(() => reject(new Error('Waktu pengunduhan model AI habis (timeout).')), 12000);
                    });

                    await Promise.race([loadModelPromise, timeoutModelPromise]);
                    if (curSession !== this.sessionId || !this.showFaceModal) return;
                    this.faceModelsLoaded = true;
                }

                // 2. Akses Webcam dengan timeout 8 detik
                this.statusText = 'Membuka kamera webcam...';
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Webcam tidak didukung atau koneksi tidak aman (gunakan HTTPS/localhost).');
                }

                const getUserMediaPromise = navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
                    audio: false
                });

                const timeoutCameraPromise = new Promise((_, reject) => {
                    setTimeout(() => reject(new Error('Waktu inisialisasi webcam habis (timeout). Pastikan kamera diizinkan.')), 8000);
                });

                const stream = await Promise.race([getUserMediaPromise, timeoutCameraPromise]);
                if (curSession !== this.sessionId || !this.showFaceModal) {
                    stream.getTracks().forEach(t => t.stop());
                    return;
                }

                this.faceStream = stream;
                const video = this.getVideo();
                if (!video) throw new Error('Elemen tampilan video tidak ditemukan.');

                video.srcObject = stream;
                video.muted = true;
                try {
                    await video.play();
                } catch (playErr) {
                    console.warn(playErr);
                }

                if (curSession !== this.sessionId || !this.showFaceModal) return;

                this.isCameraLoading = false;
                this.statusText = 'Posisikan wajah Anda tegak di dalam oval';
                this.startDetectionLoop(curSession);

            } catch (err) {
                if (curSession !== this.sessionId) return;
                console.error('Kamera / Face-api error:', err);
                this.isCameraLoading = false;
                this.isError = true;
                this.stopCamera();

                let userMsg = err.message || 'Gagal mengakses webcam.';
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    userMsg = 'Izin kamera ditolak. Silakan izinkan akses kamera pada browser.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    userMsg = 'Kamera tidak ditemukan. Pastikan webcam terpasang.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    userMsg = 'Kamera sedang digunakan oleh aplikasi lain atau sibuk.';
                }

                this.errorMessage = userMsg;
                this.statusText = userMsg;
            }
        },

        startDetectionLoop(curSession) {
            const video = this.getVideo();
            const canvas = this.getCanvas();
            let isDetecting = false;
            let verifyAttempted = false;
            const autoCap = window.FaceAutoCapture();
            this.autoProgress = 0;

            const detect = async () => {
                if (curSession !== this.sessionId || !this.showFaceModal || this.isSuccess) return;

                if (!video || video.paused || video.ended) {
                    this.faceAnimId = requestAnimationFrame(detect);
                    return;
                }

                if (video.readyState === 4 && !verifyAttempted && !isDetecting) {
                    isDetecting = true;
                    try {
                        const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
                        if (canvas) {
                            faceapi.matchDimensions(canvas, displaySize);
                        }

                        const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.50 });
                        const detection = await faceapi.detectSingleFace(video, options)
                            .withFaceLandmarks()
                            .withFaceDescriptor();

                        if (curSession !== this.sessionId || !this.showFaceModal || this.isSuccess) return;

                        if (canvas) {
                            const ctx = canvas.getContext('2d');
                            if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
                        }

                        if (detection) {
                            if (this.livenessRetry) { autoCap.reset(); this.livenessRetry = false; this.livenessTimedOut = false; }
                        const ac = autoCap.evaluate(detection, video);
                        this.livenessTimedOut = ac.state === 'timeout';
                            this.autoProgress = ac.progress;
                            this.isFaceDetected = ['stabilizing', 'liveness', 'ready'].includes(ac.state);
                            this.currentDescriptor = Array.from(detection.descriptor);

                            if (canvas) {
                                const resized = faceapi.resizeResults(detection, displaySize);
                                faceapi.draw.drawFaceLandmarks(canvas, resized);
                            }

                            if (ac.ready) {
                                this.statusText = this.modalMode === 'enroll'
                                    ? 'Berhasil dipindai! Menyimpan sampel biometrik...'
                                    : 'Berhasil dipindai! Memverifikasi ke akun Anda...';
                                verifyAttempted = true;
                                if (this.modalMode === 'enroll') {
                                    await this.enrollWithServer(this.currentDescriptor, curSession);
                                } else {
                                    await this.verifyWithServer(this.currentDescriptor, curSession);
                                }
                                return;
                            }
                            this.statusText = ac.message;
                        } else {
                            autoCap.reset();
                            this.autoProgress = 0;
                            this.isFaceDetected = false;
                            this.currentDescriptor = null;
                            this.statusText = 'Posisikan wajah di tengah kamera...';
                        }
                    } catch (dErr) {
                        console.warn('Loop error:', dErr);
                    } finally {
                        isDetecting = false;
                    }
                }

                if (this.showFaceModal && !this.isSuccess && curSession === this.sessionId) {
                    this.faceAnimId = requestAnimationFrame(detect);
                }
            };

            this.faceAnimId = requestAnimationFrame(detect);
        },

        async triggerManualCapture() {
            if (!this.isFaceDetected || this.isProcessing) return;
            const descriptor = this.currentDescriptor;
            if (!descriptor) return;
            this.isProcessing = true;
            this.statusText = this.modalMode === 'enroll'
                ? 'Menyimpan sampel wajah & foto profil...'
                : 'Memverifikasi wajah Anda...';
            try {
                if (this.modalMode === 'enroll') {
                    await this.enrollWithServer(descriptor, this.sessionId);
                } else {
                    await this.verifyWithServer(descriptor, this.sessionId);
                }
            } finally {
                this.isProcessing = false;
            }
        },

        snapshotFrame() {
            const video = this.getVideo();
            if (!video || video.readyState !== 4) return null;
            try {
                const c = document.createElement('canvas');
                c.width = video.videoWidth || 640;
                c.height = video.videoHeight || 480;
                const ctx = c.getContext('2d');
                ctx.translate(c.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, c.width, c.height);
                return c.toDataURL('image/jpeg', 0.85);
            } catch (e) {
                return null;
            }
        },

        async verifyWithServer(descriptor, curSession) {
            if (curSession !== this.sessionId || !this.showFaceModal) return;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

                const response = await fetch(FACE_VERIFY_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: this.selectedUserId,
                        embedding: descriptor,
                        liveness_passed: true,
                        photo_data: this.snapshotFrame()
                    })
                });

                const data = await response.json();
                if (curSession !== this.sessionId || !this.showFaceModal) return;

                if (response.ok && data.verified) {
                    this.isSuccess = true;
                    this.similarityScore = data.similarity || 95;
                    this.statusText = '✓ Wajah cocok! Membuka sesi Anda...';
                    this.faceEmbeddingJson = JSON.stringify(descriptor);
                    this.isFaceVerified = 1;

                    setTimeout(() => {
                        if (curSession === this.sessionId && this.showFaceModal) {
                            this.stopCamera();
                            this.showFaceModal = false;
                            document.getElementById('loginAuthForm').submit();
                        }
                    }, 1200);
                } else {
                    this.isError = true;
                    this.errorMessage = data.message || 'Wajah tidak cocok dengan akun terdaftar.';
                    this.statusText = this.errorMessage;
                }
            } catch (err) {
                if (curSession !== this.sessionId) return;
                console.error(err);
                this.isError = true;
                this.errorMessage = 'Terjadi kendala saat verifikasi ke server.';
                this.statusText = this.errorMessage;
            }
        },

        async enrollWithServer(descriptor, curSession) {
            if (curSession !== this.sessionId || !this.showFaceModal) return;

            // Capture snapshot frame from video for profile photo
            let photoData = null;
            const video = this.getVideo();
            if (video && video.readyState === 4) {
                try {
                    const snapCanvas = document.createElement('canvas');
                    snapCanvas.width = video.videoWidth || 640;
                    snapCanvas.height = video.videoHeight || 480;
                    const snapCtx = snapCanvas.getContext('2d');
                    snapCtx.translate(snapCanvas.width, 0);
                    snapCtx.scale(-1, 1);
                    snapCtx.drawImage(video, 0, 0, snapCanvas.width, snapCanvas.height);
                    photoData = snapCanvas.toDataURL('image/jpeg', 0.85);
                    this.capturedPhotoData = photoData;
                } catch (e) {
                    console.warn('Enroll frame snapshot error:', e);
                }
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

                const response = await fetch(FACE_ENROLL_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: this.selectedUserId,
                        embedding: descriptor,
                        liveness_passed: true,
                        photo_data: photoData
                    })
                });

                const data = await response.json();
                if (curSession !== this.sessionId || !this.showFaceModal) return;

                if (response.ok && data.success) {
                    this.isSuccess = true;
                    this.statusText = '✓ Wajah berhasil didaftarkan! Mengarahkan ke Dashboard...';

                    setTimeout(() => {
                        if (curSession === this.sessionId && this.showFaceModal) {
                            this.stopCamera();
                            this.showFaceModal = false;
                            window.location.href = data.redirect || "{{ route('dashboard') }}";
                        }
                    }, 1200);
                } else {
                    this.isError = true;
                    this.errorMessage = data.message || 'Gagal menyimpan sampel wajah.';
                    this.statusText = this.errorMessage;
                }
            } catch (err) {
                if (curSession !== this.sessionId) return;
                console.error(err);
                this.isError = true;
                this.errorMessage = 'Terjadi kendala saat menghubungi server pendaftaran wajah.';
                this.statusText = this.errorMessage;
            }
        },

        retryFaceScan() {
            this.isError = false;
            this.errorMessage = '';
            this.statusText = 'Mencoba memindai ulang...';
            const curSession = ++this.sessionId;
            this.startCamera(curSession);
        },

        closeModal() {
            this.sessionId++;
            this.isCameraLoading = false;
            this.stopCamera();
            this.showFaceModal = false;
        },

        stopCamera() {
            if (this.faceStream) {
                try {
                    this.faceStream.getTracks().forEach(t => t.stop());
                } catch (e) {
                    console.warn(e);
                }
                this.faceStream = null;
            }
            if (this.faceAnimId) {
                cancelAnimationFrame(this.faceAnimId);
                this.faceAnimId = null;
            }
            const video = this.getVideo();
            if (video) video.srcObject = null;
            const canvas = this.getCanvas();
            if (canvas) {
                const ctx = canvas.getContext('2d');
                if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }
    };
}
</script>
@endpush

</x-auth-layout>
