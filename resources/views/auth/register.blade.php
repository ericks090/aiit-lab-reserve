<x-auth-layout title="Daftar Akun">

    <div class="page-eyebrow">Pendaftaran</div>
    <h2 class="font-display text-3xl font-bold text-ink-900 tracking-tight">Buat akun baru.</h2>
    <p class="mt-1 text-xs text-ink-700/60">
        Lengkapi data diri dan rekam sampel wajah biometrik Anda untuk akses laboratorium.
    </p>

    @if ($errors->any())
        <div class="mt-6 rounded-md border border-status-rejected/30 bg-status-rejected/5 p-3">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-status-rejected">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div x-data="registerWithFaceScan()" x-init="init()" class="mt-8">
        <form method="POST" action="{{ route('register') }}" @submit.prevent="handleSubmit($event)" class="space-y-6">
            @csrf

            <input type="hidden" name="face_embedding" :value="faceEmbeddingJson">
            <input type="hidden" name="photo_data" :value="photoData">
            <input type="hidden" name="liveness_passed" :value="isCaptured ? 1 : 0">

            {{-- Form Fields --}}
            <div class="space-y-4">
                <div class="form-field">
                    <label for="name" class="form-label form-required">Nama Lengkap</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Dr. Budi Santoso"
                        required
                        autofocus
                        class="form-input"
                    />
                </div>

                <div class="form-field">
                    <label for="study_program_id" class="form-label form-required">Program Studi</label>
                    <select id="study_program_id" name="study_program_id" required class="form-select">
                        <option value="" disabled {{ old('study_program_id') ? '' : 'selected' }}>— Pilih Program Studi —</option>
                        @foreach ($studyPrograms as $program)
                            <option value="{{ $program->id }}" {{ old('study_program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="email" class="form-label form-required">Alamat Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="nama@ukrida.ac.id"
                        required
                        autocomplete="username"
                        class="form-input"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-field" x-data="{ show: false }">
                        <label for="password" class="form-label form-required">Kata Sandi</label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="show ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                class="form-input pr-10"
                                placeholder="Min. 8 karakter"
                            />
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-3 text-ink-700/50 hover:text-ink-700 transition-colors focus:outline-none">
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-field" x-data="{ show: false }">
                        <label for="password_confirmation" class="form-label form-required">Ulangi Sandi</label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="show ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                class="form-input pr-10"
                                placeholder="Ulangi sandi"
                            />
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-3 text-ink-700/50 hover:text-ink-700 transition-colors focus:outline-none">
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Biometric Face Scanning Section (Mandatory) --}}
            <div class="pt-4 border-t border-rule space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-mark-500/20 text-ink-900 border border-mark-500/30">
                            <span class="w-1.5 h-1.5 rounded-full" :class="isCaptured ? 'bg-emerald-500' : (isCameraLoading ? 'bg-amber-500 animate-ping' : 'bg-mark-500 animate-pulse')"></span>
                            Biometrik Wajah (Wajib)
                        </div>
                        <h4 class="text-xs font-bold text-ink-900 mt-1">Perekaman Sampel Wajah</h4>
                    </div>
                    <template x-if="isCaptured">
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-2 py-0.5">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            128-D Vektor Siap
                        </span>
                    </template>
                </div>

                {{-- Camera Viewport --}}
                <div class="face-camera-viewport relative rounded-xl overflow-hidden bg-ink-900 aspect-video flex items-center justify-center shadow-inner">
                    <video id="registerFaceVideo" class="face-camera-video w-full h-full object-cover" style="transform: scaleX(-1);" playsinline autoplay muted></video>
                    <canvas id="registerFaceCanvas" class="face-camera-canvas absolute inset-0 w-full h-full pointer-events-none" style="transform: scaleX(-1);"></canvas>

                    {{-- Oval Reticle --}}
                    <div x-show="!isCameraLoading && !isError && isCameraRunning"
                         class="face-camera-reticle absolute w-28 h-36 border-2 rounded-full pointer-events-none transition-all duration-300"
                         :class="isCaptured ? 'border-emerald-400 shadow-[0_0_25px_rgba(16,185,129,0.7)]' : (isFaceDetected ? 'border-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'border-amber-400/80 border-dashed')"></div>

                    {{-- Camera Off Initial State --}}
                    <div x-show="!isCameraRunning && !isCaptured" class="absolute inset-0 bg-ink-950/80 flex flex-col items-center justify-center text-white p-4 text-center z-10">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center mb-2 text-mark-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="text-xs font-bold text-white">Nyalakan Kamera untuk Perekaman Wajah</div>
                        <p class="text-[11px] text-white/60 mt-1 max-w-xs">Perekaman wajah digunakan untuk autentikasi login dan reservasi instan lab.</p>
                        <button type="button" @click="startCamera()" class="mt-3 px-4 py-2 rounded-lg bg-mark-500 text-ink-900 text-xs font-bold hover:bg-mark-400 transition-colors cursor-pointer inline-flex items-center gap-1.5 shadow-md">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Buka Kamera Webcam</span>
                        </button>
                    </div>

                    {{-- Loading Spinner Overlay --}}
                    <div x-show="isCameraLoading" class="absolute inset-0 bg-ink-950/85 flex flex-col items-center justify-center text-white p-4 text-center z-10"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <svg class="animate-spin w-7 h-7 text-mark-500 mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs font-semibold text-white/95" x-text="statusText"></span>
                    </div>

                    {{-- Error Overlay inside Viewport --}}
                    <div x-show="isError" class="absolute inset-0 bg-ink-950/90 flex flex-col items-center justify-center text-white p-4 text-center z-10"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <div class="w-9 h-9 rounded-full bg-red-500/20 text-red-400 flex items-center justify-center mb-1.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="text-xs font-bold text-red-300">Kendala Kamera</div>
                        <div class="text-[11px] text-white/70 mt-1 max-w-xs leading-relaxed" x-text="errorMessage"></div>
                        <button type="button" @click="startCamera()" class="mt-2.5 px-3 py-1.5 rounded-lg bg-mark-500 text-ink-900 text-xs font-bold hover:bg-mark-400 transition-colors cursor-pointer">
                            Coba Nyalakan Lagi
                        </button>
                    </div>

                    {{-- Success Captured Overlay --}}
                    <div x-show="isCaptured" class="absolute inset-0 bg-emerald-950/85 backdrop-blur-xs flex flex-col items-center justify-center text-white z-20"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-90"
                         x-transition:enter-end="opacity-100 scale-100">
                        <template x-if="photoData">
                            <div class="relative mb-2">
                                <img :src="photoData" alt="Foto Profil" class="w-16 h-16 rounded-full object-cover border-2 border-emerald-400 shadow-lg">
                                <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shadow">✓</div>
                            </div>
                        </template>
                        <template x-if="!photoData">
                            <div class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xl mb-1.5 shadow-lg">✓</div>
                        </template>
                        <div class="font-bold text-sm">Wajah Berhasil Dipindai!</div>
                        <div class="text-[11px] text-emerald-200 mt-0.5">Vektor biometrik & foto profil siap disimpan.</div>
                        <button type="button" @click="retake()" class="mt-2.5 px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md transition-colors cursor-pointer pointer-events-auto">
                            Foto Ulang
                        </button>
                    </div>
                </div>

                {{-- Auto-capture status & progress --}}
                <div class="space-y-1.5 pt-1" x-show="isCameraRunning && !isCaptured && !isCameraLoading">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium" :class="isFaceDetected ? 'text-emerald-700' : 'text-ink-700/70'" x-text="statusText"></span>
                        <span class="text-[11px] font-semibold text-blue-700" x-text="Math.round(autoProgress * 100) + '%'"></span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-slate-200 overflow-hidden">
                        <div class="h-full bg-blue-600 transition-all duration-150" :style="'width:' + Math.round(autoProgress * 100) + '%'"></div>
                    </div>
                </div>
                <button type="button" x-show="livenessTimedOut" @click="livenessRetry = true" class="mt-2 w-full py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md cursor-pointer pointer-events-auto">Ulangi Tantangan</button>
                <div class="flex items-center justify-between text-xs pt-1" x-show="!isCameraRunning || isCaptured || isCameraLoading">
                    <span class="text-ink-700/70" x-text="statusText"></span>
                    <button type="button" x-show="isCaptured" @click="retake()"
                            class="px-3.5 py-2 rounded-lg text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-md cursor-pointer inline-flex items-center gap-1.5">
                        Foto Ulang
                    </button>
                </div>
            </div>

            <button type="submit" :disabled="!isCaptured"
                    class="btn-mark btn-lg w-full flex items-center justify-center gap-2 cursor-pointer shadow-md">
                <span>Daftar Akun</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-rule text-center">
            <p class="text-xs text-ink-700/60">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-mark-600 hover:text-mark-700 hover:underline">
                    Masuk ke Sistem
                </a>
            </p>
        </div>
    </div>

@push('styles')
<style>
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
    width: 7.5rem !important;
    height: 10rem !important;
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
function registerWithFaceScan() {
    const FACE_MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

    return {
        isCameraRunning: false,
        isCameraLoading: false,
        isFaceDetected: false,
        isCaptured: false,
        isError: false,
        errorMessage: '',
        statusText: 'Klik "Buka Kamera Webcam" untuk memulai perekaman wajah.',
        faceEmbeddingJson: '',
        photoData: '',
        faceStream: null,
        faceAnimId: null,
        faceModelsLoaded: false,
        sessionId: 0,
        currentDescriptor: null,
        autoProgress: 0,
        livenessTimedOut: false,
        livenessRetry: false,
        autoCap: window.FaceAutoCapture(),

        init() {
            // Prepared
        },

        getVideo() {
            return document.getElementById('registerFaceVideo');
        },

        getCanvas() {
            return document.getElementById('registerFaceCanvas');
        },

        async startCamera() {
            this.isError = false;
            this.errorMessage = '';
            this.isCaptured = false;
            this.faceEmbeddingJson = '';
            this.currentDescriptor = null;
            this.isCameraLoading = true;
            this.statusText = 'Menghubungkan ke webcam...';
            const curSession = ++this.sessionId;
            this.autoCap.reset();
            this.autoProgress = 0;

            try {
                // 1. Load models with 12s timeout
                if (!this.faceModelsLoaded) {
                    this.statusText = 'Memuat model AI deteksi wajah...';
                    const loadModelPromise = Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(FACE_MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(FACE_MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(FACE_MODEL_URL),
                    ]);

                    const timeoutModelPromise = new Promise((_, reject) => {
                        setTimeout(() => reject(new Error('Waktu pemuatan model AI habis (timeout). Periksa koneksi internet.')), 12000);
                    });

                    await Promise.race([loadModelPromise, timeoutModelPromise]);
                    if (curSession !== this.sessionId) return;
                    this.faceModelsLoaded = true;
                }

                // 2. Open camera with 8s timeout
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
                if (curSession !== this.sessionId) {
                    stream.getTracks().forEach(t => t.stop());
                    return;
                }

                this.faceStream = stream;
                const video = this.getVideo();
                if (!video) throw new Error('Elemen video kamera tidak ditemukan.');

                video.srcObject = stream;
                video.muted = true;
                try {
                    await video.play();
                } catch (pErr) {
                    console.warn('Video play:', pErr);
                }

                if (curSession !== this.sessionId) return;
                this.isCameraRunning = true;
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
                    userMsg = 'Izin kamera ditolak. Silakan izinkan akses kamera di ikon gembok pada browser Anda.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    userMsg = 'Kamera tidak ditemukan. Pastikan webcam terpasang pada perangkat Anda.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    userMsg = 'Kamera sedang digunakan aplikasi lain (Zoom/Teams) atau sibuk.';
                }

                this.errorMessage = userMsg;
                this.statusText = userMsg;
            }
        },

        startDetectionLoop(curSession) {
            const video = this.getVideo();
            const canvas = this.getCanvas();
            let isDetecting = false;

            const detect = async () => {
                if (curSession !== this.sessionId || !this.isCameraRunning || this.isCaptured) return;

                if (!video || video.paused || video.ended) {
                    this.faceAnimId = requestAnimationFrame(detect);
                    return;
                }

                if (video.readyState === 4 && !isDetecting) {
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

                        if (curSession !== this.sessionId || this.isCaptured) return;

                        if (canvas) {
                            const ctx = canvas.getContext('2d');
                            if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
                        }

                        if (detection) {
                            if (this.livenessRetry) { this.autoCap.reset(); this.livenessRetry = false; this.livenessTimedOut = false; }
                            const ac = this.autoCap.evaluate(detection, video);
                            this.livenessTimedOut = ac.state === 'timeout';
                            this.autoProgress = ac.progress;
                            this.isFaceDetected = ['stabilizing', 'liveness', 'ready'].includes(ac.state);
                            this.currentDescriptor = Array.from(detection.descriptor);
                            this.statusText = ac.message;

                            if (canvas) {
                                const resized = faceapi.resizeResults(detection, displaySize);
                                faceapi.draw.drawFaceLandmarks(canvas, resized);
                            }

                            if (ac.ready) {
                                this.captureFace();
                                return;
                            }
                        } else {
                            this.autoCap.reset();
                            this.autoProgress = 0;
                            this.isFaceDetected = false;
                            this.currentDescriptor = null;
                            this.statusText = 'Posisikan wajah di tengah kamera...';
                        }
                    } catch (dErr) {
                        console.warn('Frame detection:', dErr);
                    } finally {
                        isDetecting = false;
                    }
                }

                if (this.isCameraRunning && !this.isCaptured && curSession === this.sessionId) {
                    this.faceAnimId = requestAnimationFrame(detect);
                }
            };

            this.faceAnimId = requestAnimationFrame(detect);
        },

        captureFace() {
            if (!this.currentDescriptor || !this.isFaceDetected) return;

            // Capture camera frame for profile photo
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
                    this.photoData = snapCanvas.toDataURL('image/jpeg', 0.85);
                } catch (e) {
                    console.warn('Frame snapshot error:', e);
                }
            }

            this.faceEmbeddingJson = JSON.stringify(this.currentDescriptor);
            this.isCaptured = true;
            this.statusText = '✓ Wajah berhasil direkam! Lanjutkan pengisian formulir.';
            this.stopCamera();
        },

        retake() {
            this.isCaptured = false;
            this.faceEmbeddingJson = '';
            this.photoData = '';
            this.currentDescriptor = null;
            this.startCamera();
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
            this.isCameraRunning = false;
        },

        handleSubmit(e) {
            const form = e.target;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (!this.isCaptured || !this.faceEmbeddingJson) {
                this.statusText = '⚠️ Anda wajib memindai wajah sebelum mendaftar!';
                if (!this.isCameraRunning) {
                    this.startCamera();
                }
                return;
            }

            form.submit();
        }
    };
}
</script>
@endpush

</x-auth-layout>
