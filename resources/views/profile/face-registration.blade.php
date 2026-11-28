<x-app-layout>
    <x-slot:header>
        <x-page-header
            eyebrow="Akun Pengguna"
            title="Pendaftaran Pengenalan Wajah (Face Recognition)">
            <x-slot:actions>
                <a href="{{ route('dashboard') }}" class="btn-ghost btn-sm">← Kembali ke Dashboard</a>
            </x-slot:actions>
        </x-page-header>
    </x-slot:header>

    @push('styles')
    <style>
        .face-scanner-box {
            position: relative;
            width: 100%;
            max-width: 520px;
            aspect-ratio: 4 / 3;
            background: #0A1A47;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(10, 26, 71, 0.3);
            border: 2px solid rgba(245, 184, 0, 0.3);
        }
        .face-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Mirror camera for natural feel */
        }
        .face-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            transform: scaleX(-1);
        }
        .face-guide-oval {
            position: absolute;
            width: 200px;
            height: 250px;
            border: 2px dashed rgba(245, 184, 0, 0.7);
            border-radius: 50%;
            pointer-events: none;
            transition: all 0.3s ease;
            box-shadow: 0 0 0 9999px rgba(10, 26, 71, 0.45);
        }
        .face-guide-oval.detected {
            border-color: #10B981;
            border-style: solid;
            box-shadow: 0 0 0 9999px rgba(10, 26, 71, 0.25), 0 0 20px rgba(16, 185, 129, 0.4);
        }
        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #F5B800, transparent);
            animation: scanAnim 2.5s ease-in-out infinite alternate;
            pointer-events: none;
        }
        @keyframes scanAnim {
            0% { top: 15%; opacity: 0.3; }
            100% { top: 85%; opacity: 0.9; }
        }
    </style>
    @endpush

    <div class="max-w-4xl mx-auto space-y-6" x-data="faceRegistration({{ $hasFaceRegistered ? 'true' : 'false' }})" x-init="initComponent()">

        {{-- Hidden CSRF Form Helper --}}
        <form id="face-csrf-form" class="hidden">
            @csrf
        </form>

        {{-- Toast Notification Modal/Popup --}}
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 sm:scale-100"
             x-transition:leave-end="opacity-0 sm:scale-95"
             class="fixed top-6 right-6 z-50 max-w-sm w-full bg-white border shadow-2xl rounded-2xl p-4 flex items-start gap-3.5"
             :class="toast.type === 'success' ? 'border-emerald-300' : 'border-red-300'"
             style="display: none;">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                 :class="toast.type === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
                <template x-if="toast.type === 'success'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </template>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-bold text-ink-900" x-text="toast.title"></h4>
                <p class="text-xs text-ink-700/70 mt-0.5" x-text="toast.message"></p>
                <template x-if="toast.type === 'success'">
                    <div class="mt-2 text-[11px] text-emerald-700 font-semibold flex items-center gap-1.5">
                        <svg class="animate-spin h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                        <span>Mengalihkan ke dashboard...</span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Info Alert --}}
        <div class="bg-white border border-rule rounded-2xl p-6 shadow-card relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-mark-500/15 text-ink-900 text-xs font-bold uppercase tracking-wider border border-mark-500/30">
                        ✨ Fitur Persetujuan Instan
                    </div>
                    <h2 class="text-xl font-bold tracking-tight text-ink-900">Otomatisasi Reservasi Tanpa Menunggu Admin</h2>
                    <p class="text-slate-700 text-sm leading-relaxed">
                        Daftarkan sampel wajah Anda sekali saja. Saat membuat reservasi lab menggunakan opsi <strong class="text-ink-900">Verifikasi Wajah</strong>, reservasi Anda akan langsung disetujui (<strong class="text-emerald-700">Approved</strong>) secara instan tanpa perlu menunggu review manual dari Admin.
                    </p>
                </div>
                <div class="shrink-0 bg-slate-50 rounded-xl p-4 border border-slate-200 text-center min-w-[190px] shadow-xs">
                    <div class="text-xs uppercase tracking-wider text-slate-700 font-bold mb-2">Status Wajah Anda</div>
                    @if ($hasFaceRegistered)
                        @if ($user->profile_photo_url)
                            <div class="mb-3 flex justify-center">
                                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" onerror="this.remove()" class="w-16 h-16 rounded-full object-cover border-2 border-emerald-400 shadow-md">
                            </div>
                        @endif
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs border border-emerald-300">
                            <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Sudah Terdaftar
                        </span>
                        <p class="text-xs text-slate-700 font-semibold mt-2">Vektor 128‑D Aktif</p>
                        <button type="button" @click="showCamera = true; startCamera()" class="mt-3 w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-sm transition-colors cursor-pointer">Daftar Ulang Wajah</button>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-100 text-rose-800 font-bold text-xs border border-rose-300">
                            <svg class="w-4 h-4 text-rose-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Belum Terdaftar
                        </span>
                        <p class="text-xs text-slate-700 font-semibold mt-2">Silakan lakukan scan</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Camera Scanning Workspace (only when not registered or re‑register) --}}
        <div class="bg-white border border-rule rounded-2xl shadow-card p-6 md:p-8" x-show="showCamera">
            <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
                {{-- Left: Scanner Viewport & Manual Capture Button --}}
                <div class="flex flex-col items-center w-full md:w-auto">
                    <div class="face-scanner-box">
                        <video x-ref="video" class="face-video" playsinline autoplay muted></video>
                        <canvas x-ref="canvas" class="face-canvas"></canvas>
                        <div class="face-guide-oval" :class="{ 'detected': isFaceDetected }"></div>
                        <div class="scan-line" x-show="isCameraRunning && !isProcessing"></div>
                        <div x-show="!isCameraRunning" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center bg-ink-900/90 text-white z-10">
                            <div class="w-16 h-16 rounded-2xl bg-white/10 flex items-center justify-center mb-3 text-mark-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                            </div>
                            <h4 class="font-bold text-base">Kamera Sedang Tidak Aktif</h4>
                            <p class="text-xs text-white/60 mt-1 max-w-xs">Nyalakan kamera untuk mengaktifkan video scanner wajah.</p>
                            <button type="button" @click="startCamera()" :disabled="isLoadingModels" class="mt-4 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs uppercase tracking-wider transition-transform active:scale-95 shadow-md flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                Nyalakan Kamera
                            </button>
                        </div>
                    </div>
                    {{-- Status message badge below scanner --}}
                    <div class="mt-3 w-full flex items-center justify-between gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold"
                         :class="{ 'bg-emerald-50 text-emerald-800 border border-emerald-200': isFaceDetected, 'bg-amber-50 text-amber-800 border border-amber-200': isCameraRunning && !isFaceDetected, 'bg-ink-50 text-ink-700 border border-rule': !isCameraRunning }">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full"
                                  :class="{ 'bg-emerald-500 animate-pulse': isFaceDetected, 'bg-amber-500': isCameraRunning && !isFaceDetected, 'bg-ink-400': !isCameraRunning }"></span>
                            <span x-text="statusText"></span>
                        </div>
                        <span x-show="isFaceDetected" class="text-[11px] font-mono text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Skor: <span x-text="detectionConfidence + '%'"/></span>
                    </div>
                    {{-- Auto-capture progress --}}
                    <div class="mt-2 h-1.5 w-full rounded-full bg-slate-200 overflow-hidden" x-show="isCameraRunning && !isProcessing">
                        <div class="h-full bg-blue-600 transition-all duration-150" :style="'width:' + Math.round(autoProgress * 100) + '%'"></div>
                    </div>
                    <button type="button" x-show="livenessTimedOut" @click="livenessTimedOut = false; livenessRetry = true"
                            class="mt-2.5 w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-md transition-colors flex items-center justify-center gap-1.5 cursor-pointer pointer-events-auto">
                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Ulangi Tantangan / Coba Lagi</span>
                    </button>
                    {{-- Manual Capture Button (requires liveness) --}}
                    <div class="mt-3.5 w-full">
                        <button type="button" @click="takeManualSnapshot()" :disabled="!isCameraRunning || isProcessing" class="w-full py-3 px-5 rounded-xl font-semibold text-sm tracking-wide transition-all shadow-md flex items-center justify-center gap-2"
                                :class="isCameraRunning && !isProcessing ? 'bg-slate-900 hover:bg-slate-800 text-white cursor-pointer active:scale-[0.98]' : 'bg-slate-200 text-slate-600 border border-slate-300 cursor-not-allowed'">
                            <template x-if="isProcessing">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            </template>
                            <svg x-show="!isProcessing" class="w-5 h-5 shrink-0" :class="isCameraRunning && !isProcessing ? 'text-white' : 'text-slate-500'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                            <span :class="isCameraRunning && !isProcessing ? 'text-white' : 'text-slate-500'" x-text="isProcessing ? 'Memproses & Menyimpan...' : 'Ambil Foto Wajah'"></span>
                        </button>
                    </div>
                    {{-- Camera Toggle Control --}}
                    <div class="mt-2.5 w-full flex items-center justify-between text-xs text-ink-500">
                        <span>Kontrol Kamera:</span>
                        <button type="button" x-show="isCameraRunning" @click="stopCamera()" class="font-semibold text-ink-700 hover:text-ink-900 underline">Matikan Kamera</button>
                        <button type="button" x-show="!isCameraRunning" @click="startCamera()" class="font-semibold text-mark-600 hover:text-mark-700 underline">Nyalakan Kamera</button>
                    </div>
                </div>
                {{-- Right: Instructions & Actions --}}
                <div class="flex-1 w-full space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-ink-900">Petunjuk Pengambilan Sampel</h3>
                        <p class="text-xs text-ink-700/60 mt-1">Pastikan proses pendaftaran wajah memenuhi ketentuan berikut:</p>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-ink-50 border border-rule">
                            <div class="w-6 h-6 rounded-lg bg-mark-500/20 text-ink-900 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">1</div>
                            <div class="text-xs text-ink-700"><strong>Posisikan Wajah:</strong> Arahkan wajah menghadap lurus ke arah kamera di dalam bingkai lingkaran/oval.</div>
                        </div>
                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-ink-50 border border-rule">
                            <div class="w-6 h-6 rounded-lg bg-mark-500/20 text-ink-900 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">2</div>
                            <div class="text-xs text-ink-700"><strong>Klik Ambil Foto Wajah:</strong> Klik tombol manual <strong>\"Ambil Foto Wajah\"</strong>. Sistem akan langsung mengambil dan mengekstrak vektor wajah seketika tanpa perlu menunggu deteksi otomatis berulang.</div>
                        </div>
                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-ink-50 border border-rule">
                            <div class="w-6 h-6 rounded-lg bg-mark-500/20 text-ink-900 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">3</div>
                            <div class="text-xs text-ink-700"><strong>Pengalihan Otomatis:</strong> Setelah berhasil disimpan, Anda akan menerima konfirmasi dan otomatis dialihkan ke halaman reservasi.</div>
                        </div>
                    </div>
                    {{-- Secondary Action Button (Also Triggers Manual Snapshot) --}}
                    <div class="pt-4 border-t border-rule space-y-3">
                        <button type="button" @click="takeManualSnapshot()" :disabled="!isCameraRunning || isProcessing" class="w-full py-3.5 px-6 rounded-xl font-semibold text-sm tracking-wide transition-all shadow-sm flex items-center justify-center gap-2.5"
                                :class="isCameraRunning && !isProcessing ? 'bg-slate-900 hover:bg-slate-800 text-white cursor-pointer active:scale-[0.98]' : 'bg-slate-200 text-slate-600 border border-slate-300 cursor-not-allowed'">
                            <svg class="w-5 h-5 shrink-0" :class="isCameraRunning && !isProcessing ? 'text-white' : 'text-slate-500'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0221 12z"/></svg>
                            <span :class="isCameraRunning && !isProcessing ? 'text-white' : 'text-slate-500'" x-text="isProcessing ? 'Menyimpan...' : 'Ambil Foto Wajah & Simpan'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Vladmandic Face-API.js Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
    <x-face-autocapture />
    <script>
    function faceRegistration(isRegistered = false) {
        return {
            isCameraRunning: false,
            isLoadingModels: true,
            isFaceDetected: false,
            isProcessing: false,
            detectionConfidence: 0,
            statusText: 'Memuat model AI...',
            currentDescriptor: null,
            animFrameId: null,
            modelUrl: 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/',
            isRegistered: isRegistered,
            showCamera: false,
            autoProgress: 0,
            livenessTimedOut: false,
            livenessRetry: false,
            livenessPassed: false,
            autoCap: window.FaceAutoCapture(),

            toast: {
                show: false,
                title: '',
                message: '',
                type: 'success',
            },

            showToastNotification(title, message, type = 'success') {
                this.toast.title = title;
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;

                if (type !== 'success') {
                    setTimeout(() => { this.toast.show = false; }, 4000);
                }
            },

            async initComponent() {
                try {
                    this.statusText = 'Mengunduh model pendeteksi wajah...';
                    await window.FaceAutoCapture.loadModels(this.modelUrl);
                    this.isLoadingModels = false;
                    this.statusText = 'Model siap.';
                    if (!this.isRegistered) {
                        this.showCamera = true;
                        this.startCamera();
                    }
                } catch (err) {
                    console.error('Gagal memuat model face‑api:', err);
                    this.statusText = 'Gagal memuat model: ' + err.message;
                    this.isLoadingModels = false;
                }
            },

            async startCamera() {
                const video = this.$refs.video;
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Browser Anda tidak mendukung akses webcam.');
                    return;
                }
                try {
                    this.statusText = 'Membuka kamera...';
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
                        audio: false
                    });
                    video.srcObject = stream;
                    await video.play();
                    this.isCameraRunning = true;
                    this.statusText = 'Kamera aktif. Silakan arahkan wajah ke kamera.';
                    this.startDetectionLoop();
                } catch (err) {
                    console.error('Kamera error:', err);
                    this.statusText = 'Gagal mengakses kamera: ' + err.message;
                    this.isCameraRunning = false;
                }
            },

            stopCamera() {
                const video = this.$refs.video;
                if (video && video.srcObject) {
                    const tracks = video.srcObject.getTracks();
                    tracks.forEach(track => track.stop());
                    video.srcObject = null;
                }
                if (this.animFrameId) {
                    cancelAnimationFrame(this.animFrameId);
                    this.animFrameId = null;
                }
                this.isCameraRunning = false;
                this.isFaceDetected = false;
                this.statusText = 'Kamera dinonaktifkan.';
            },

            startDetectionLoop() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                const detect = async () => {
                    if (!this.isCameraRunning || video.paused || video.ended) {
                        return;
                    }
                    if (video.readyState === 4 && !this.isProcessing) {
                        const displaySize = { width: video.videoWidth, height: video.videoHeight };
                        faceapi.matchDimensions(canvas, displaySize);
                        // Fast loop: landmarks only (no descriptor) so blinks are not missed
                        const detection = await window.FaceAutoCapture.detectFast(video);
                        if (!this.isCameraRunning) return;
                        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                        if (this.livenessRetry) { this.autoCap.reset(); this.livenessRetry = false; this.livenessTimedOut = false; }
                        // Always evaluate (also with null) so brief drops during a blink are tolerated
                        const ac = this.autoCap.evaluate(detection, video);
                        this.livenessTimedOut = ac.state === 'timeout';
                        this.autoProgress = ac.progress;
                        this.statusText = ac.message;
                        this.isFaceDetected = ['stabilizing', 'liveness', 'ready'].includes(ac.state);
                        this.detectionConfidence = detection ? Math.round(detection.detection.score * 100) : 0;
                        if (detection) {
                            const resized = faceapi.resizeResults(detection, displaySize);
                            faceapi.draw.drawDetections(canvas, resized);
                        }
                        if (ac.ready && !this.isProcessing) {
                            this.livenessPassed = true;
                            this.autoCap.reset();
                            this.takeManualSnapshot().finally(() => { this.livenessPassed = false; });
                        }
                    }
                    this.animFrameId = requestAnimationFrame(detect);
                };
                detect();
            },

            async takeManualSnapshot() {
                if (!this.isCameraRunning) {
                    this.showToastNotification('Kamera Belum Aktif', 'Silakan klik tombol Nyalakan Kamera terlebih dahulu.', 'error');
                    return;
                }
                if (this.isProcessing) return;
                if (!this.livenessPassed) {
                    this.showToastNotification('Verifikasi Keaktifan', 'Berkedip perlahan 1 kali ke kamera terlebih dahulu.', 'error');
                    return;
                }
                this.isProcessing = true;
                this.statusText = 'Kedipan valid, memverifikasi wajah...';
                const video = this.$refs.video;
                let descriptor = null;
                if (video && video.readyState === 4) {
                    // Extract the 128-d descriptor ONCE, only after the blink passed
                    descriptor = await window.FaceAutoCapture.extractDescriptor(video);
                }
                if (!descriptor || descriptor.length < 64) {
                    this.isProcessing = false;
                    this.statusText = 'Wajah tidak terdeteksi. Silakan hadapkan wajah ke kamera lalu coba lagi.';
                    this.isProcessing = false;
                    this.showToastNotification('Wajah Tidak Terdeteksi', 'Pastikan wajah Anda terlihat jelas di dalam layar webcam lalu klik kembali.', 'error');
                    return;
                }
                await this.sendEmbeddingToBackend(descriptor);
            },

            async sendEmbeddingToBackend(descriptor) {
                this.statusText = 'Menyimpan data wajah dan foto profil ke database...';
                const csrfToken = document.querySelector('input[name="_token"]')?.value
                    || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || '{{ csrf_token() }}';

                // Tangkap foto snapshot dari elemen video kamera
                let photoData = null;
                const video = this.$refs.video;
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
                    } catch (e) {
                        console.warn('Snapshot frame error:', e);
                    }
                }

                try {
                    const response = await fetch('{{ route("profile.face-register") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _token: csrfToken,
                            embedding: descriptor,
                            photo_data: photoData,
                            liveness_passed: this.livenessPassed
                        })
                    });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.stopCamera();
                        this.statusText = 'Wajah Berhasil Didaftarkan!';
                        this.showToastNotification('Wajah Berhasil Didaftarkan!', 'Data wajah Anda telah tersimpan dengan aman.', 'success');
                        setTimeout(() => { window.location.href = data.redirect || '{{ route("dashboard") }}'; }, 1200);
                    } else {
                        this.isProcessing = false;
                        this.statusText = data.message || 'Gagal menyimpan data wajah.';
                        this.showToastNotification('Gagal Mendaftarkan Wajah', data.message || 'Silakan coba beberapa saat lagi.', 'error');
                    }
                } catch (err) {
                    this.isProcessing = false;
                    console.error('AJAX registration error:', err);
                    this.statusText = 'Kesalahan jaringan: ' + err.message;
                    this.showToastNotification('Kesalahan Jaringan', 'Gagal menghubungi server: ' + err.message, 'error');
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>