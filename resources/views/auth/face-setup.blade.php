<x-auth-layout title="Pendaftaran Wajah Akun">

    <div class="page-eyebrow">{{ $program->name }}</div>
    <h2 class="font-display text-3xl font-bold text-ink-900 tracking-tight">Pendaftaran Wajah Akun.</h2>
    <p class="mt-1 text-xs text-ink-700/60">
        Daftarkan sampel wajah biometrik Anda sekali ini untuk mengaktifkan autentikasi login aman dan reservasi instan lab.
    </p>

    @if ($errors->any())
        <div class="mt-6 rounded-md border border-status-rejected/30 bg-status-rejected/5 p-3">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-status-rejected">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div x-data="legacyFaceSetup({{ $selectedUserId ? (int)$selectedUserId : 'null' }})" x-init="init()" class="mt-8">
        <form method="POST" action="{{ route('login.face-setup.store') }}" @submit.prevent="handleSubmit($event)" class="space-y-6">
            @csrf

            <input type="hidden" name="face_embedding" :value="faceEmbeddingJson">
            <input type="hidden" name="photo_data" :value="photoData">
            <input type="hidden" name="liveness_passed" :value="isCaptured ? 1 : 0">

            <div class="form-field">
                <label for="user_id" class="form-label form-required">Pilih Akun Anda</label>
                <select id="user_id" name="user_id" x-model.number="userId" required class="form-select">
                    <option value="" disabled>— Pilih nama Anda —</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">
                            {{ $u->name }}
                            @if ($u->role === 'team') · Tim
                            @elseif ($u->role === 'admin') · Admin
                            @else · Dosen
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Camera Viewport Section --}}
            <div class="pt-2 border-t border-rule space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-mark-500/20 text-ink-900 border border-mark-500/30">
                            <span class="w-1.5 h-1.5 rounded-full" :class="isCaptured ? 'bg-emerald-500' : (isCameraLoading ? 'bg-amber-500 animate-ping' : 'bg-mark-500 animate-pulse')"></span>
                            Biometrik Wajah
                        </div>
                        <h4 class="text-xs font-bold text-ink-900 mt-1">Pemindaian Kamera Wajah</h4>
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
                    <video id="setupFaceVideo" class="face-camera-video w-full h-full object-cover" style="transform: scaleX(-1);" playsinline autoplay muted></video>
                    <canvas id="setupFaceCanvas" class="face-camera-canvas absolute inset-0 w-full h-full pointer-events-none" style="transform: scaleX(-1);"></canvas>

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
                        <p class="text-[11px] text-white/60 mt-1 max-w-xs">Pastikan wajah Anda berada di tempat dengan pencahayaan yang cukup.</p>
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
                <span>Daftarkan Wajah & Masuk</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </button>

            <a href="{{ route('login.select') }}"
               class="block text-center text-xs uppercase tracking-label font-semibold text-ink-700/50 hover:text-ink-700 transition-colors">
                ← Kembali ke Pilihan Akun
            </a>
        </form>
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
function legacyFaceSetup(initialUserId) {
    const FACE_MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

    return {
        userId: initialUserId || '',
        isCameraRunning: false,
        isCameraLoading: false,
        isFaceDetected: false,
        isCaptured: false,
        isError: false,
        errorMessage: '',
        statusText: 'Klik "Buka Kamera Webcam" untuk memindai wajah.',
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
            if (this.userId) {
                this.startCamera();
            }
        },

        getVideo() {
            return document.getElementById('setupFaceVideo');
        },

        getCanvas() {
            return document.getElementById('setupFaceCanvas');
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
                if (!this.faceModelsLoaded) {
                    this.statusText = 'Memuat model AI deteksi wajah...';
                    const loadModelPromise = Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(FACE_MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(FACE_MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(FACE_MODEL_URL),
                    ]);

                    const timeoutModelPromise = new Promise((_, reject) => {
                        setTimeout(() => reject(new Error('Waktu pemuatan model AI habis (timeout).')), 12000);
                    });

                    await Promise.race([loadModelPromise, timeoutModelPromise]);
                    if (curSession !== this.sessionId) return;
                    this.faceModelsLoaded = true;
                }

                this.statusText = 'Membuka kamera webcam...';
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Webcam tidak didukung atau koneksi tidak aman.');
                }

                const getUserMediaPromise = navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
                    audio: false
                });

                const timeoutCameraPromise = new Promise((_, reject) => {
                    setTimeout(() => reject(new Error('Waktu inisialisasi webcam habis (timeout).')), 8000);
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
                    console.warn(pErr);
                }

                if (curSession !== this.sessionId) return;
                this.isCameraRunning = true;
                this.isCameraLoading = false;
                this.statusText = 'Posisikan wajah Anda tegak di dalam oval';
                this.startDetectionLoop(curSession);

            } catch (err) {
                if (curSession !== this.sessionId) return;
                console.error(err);
                this.isCameraLoading = false;
                this.isError = true;
                this.stopCamera();

                let userMsg = err.message || 'Gagal mengakses webcam.';
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    userMsg = 'Izin kamera ditolak. Silakan izinkan akses kamera di ikon gembok browser Anda.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    userMsg = 'Kamera tidak ditemukan. Pastikan webcam terpasang.';
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
                        console.warn(dErr);
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
            this.statusText = '✓ Wajah berhasil direkam! Klik tombol di bawah untuk masuk.';
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
            if (!this.userId) {
                alert('Pilih akun Anda terlebih dahulu.');
                return;
            }
            if (!this.isCaptured || !this.faceEmbeddingJson) {
                this.statusText = '⚠️ Anda wajib memindai wajah sebelum masuk!';
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
