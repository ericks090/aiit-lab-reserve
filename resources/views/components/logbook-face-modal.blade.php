{{--
    Component: Logbook Face Verification Modal
    Provides the Face-API script, modal overlay, and Alpine.js helper for Face Recognition on Logbook submission.
--}}
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

        {{-- Dark Overlay Backdrop (z-[99990]) --}}
        <div class="face-modal-backdrop fixed inset-0 z-[99990] bg-black/60 backdrop-blur-xs"
             @click="closeFaceModal()"></div>

        {{-- Centering Container (pointer-events-none lets outside clicks hit backdrop) --}}
        <div class="face-modal-center-container fixed inset-0 z-[100000] flex items-center justify-center p-4 pointer-events-none">

            {{-- Modal Content Card (pointer-events-auto captures all clicks) --}}
            <div class="face-modal-card bg-white rounded-2xl shadow-2xl border border-rule max-w-md w-full overflow-hidden text-center p-6 relative z-[100001] pointer-events-auto"
                 @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                {{-- Header --}}
                <div class="flex items-center justify-between pb-3.5 border-b border-rule text-left">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-mark-500/20 text-ink-900 border border-mark-500/30 mb-1">
                            <span class="w-1.5 h-1.5 rounded-full" :class="isCameraLoading ? 'bg-amber-500 animate-ping' : 'bg-emerald-500 animate-pulse'"></span>
                            Verifikasi Kehadiran Lab
                        </div>
                        <h3 class="text-base font-bold text-ink-900">Pemindaian Wajah Pengguna</h3>
                    </div>
                    <button type="button" @click="closeFaceModal()" class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-300 transition-colors cursor-pointer" title="Tutup verifikasi">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Booking Code summary reminder --}}
                <template x-if="targetBookingCode">
                    <div class="mt-3 py-1.5 px-3 rounded-lg bg-ink-50 border border-rule text-xs text-ink-700/80 flex items-center justify-between">
                        <span class="font-mono font-semibold text-ink-900" x-text="targetBookingCode"></span>
                        <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded border border-emerald-200">Konfirmasi Logbook</span>
                    </div>
                </template>

                {{-- Camera Viewport --}}
                <div class="face-camera-viewport mt-3.5 relative rounded-xl overflow-hidden bg-ink-900 aspect-video flex items-center justify-center shadow-inner">
                    <video id="logbookFaceVideo" class="face-camera-video w-full h-full object-cover" style="transform: scaleX(-1);" playsinline autoplay muted></video>
                    <canvas id="logbookFaceCanvas" class="face-camera-canvas absolute inset-0 w-full h-full pointer-events-none" style="transform: scaleX(-1);"></canvas>
                    
                    {{-- Oval Reticle with scanning indicator --}}
                    <div x-show="!isCameraLoading && !modalError"
                         class="face-camera-reticle absolute w-32 h-44 border-2 rounded-full pointer-events-none transition-all duration-300"
                         :class="faceVerifiedSuccess ? 'border-emerald-400 shadow-[0_0_25px_rgba(16,185,129,0.7)]' : (modalFaceDetected ? 'border-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'border-amber-400/80 border-dashed')"></div>

                    {{-- Loading Spinner Overlay --}}
                    <div x-show="isCameraLoading" class="absolute inset-0 bg-ink-950/85 flex flex-col items-center justify-center text-white p-4 text-center z-10"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <svg class="animate-spin w-8 h-8 text-mark-500 mb-2.5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs font-semibold text-white/95" x-text="faceScanStatus"></span>
                        <span class="text-[11px] text-white/60 mt-1">Sedang menghubungkan ke webcam...</span>
                    </div>

                    {{-- Error Overlay inside Viewport --}}
                    <div x-show="modalError && !faceVerifiedSuccess" class="absolute inset-0 bg-ink-950/90 flex flex-col items-center justify-center text-white p-4 text-center z-10"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <div class="w-10 h-10 rounded-full bg-red-500/20 text-red-400 flex items-center justify-center mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="text-xs font-bold text-red-300">Kendala Kamera</div>
                        <div class="text-[11px] text-white/70 mt-1 max-w-xs leading-relaxed" x-text="modalErrorMessage || faceScanStatus"></div>
                        <button type="button" @click="retryFaceScan()" class="mt-3 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold shadow-md transition-colors cursor-pointer">
                            Coba Nyalakan Lagi
                        </button>
                    </div>

                    {{-- Success Overlay with checkmark --}}
                    <div x-show="faceVerifiedSuccess" class="face-camera-success-overlay absolute inset-0 bg-emerald-950/90 backdrop-blur-xs flex flex-col items-center justify-center text-white z-20"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-90"
                         x-transition:enter-end="opacity-100 scale-100">
                        <div class="w-14 h-14 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-2xl mb-2 shadow-lg">✓</div>
                        <div class="font-bold text-base">Wajah Terverifikasi!</div>
                        <div class="text-xs text-emerald-200 mt-1" x-text="'Tingkat Kemiripan: ' + faceMatchSimilarity + '%'"></div>
                        <div class="text-[11px] text-white/70 mt-1">Menyimpan catatan logbook...</div>
                    </div>
                </div>

                {{-- Status message --}}
                <div class="mt-3.5 p-2.5 rounded-xl text-xs font-semibold flex items-center justify-center gap-2"
                     :class="faceVerifiedSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : (modalError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-ink-50 text-ink-700 border border-rule')">
                    <template x-if="isCameraLoading">
                        <svg class="animate-spin w-3.5 h-3.5 text-mark-600 shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="faceScanStatus"></span>
                </div>

                {{-- Auto-capture progress indicator --}}
                <div class="mt-2 h-1.5 w-full rounded-full bg-slate-200 overflow-hidden" x-show="!isCameraLoading && !modalError && !faceVerifiedSuccess">
                    <div class="h-full bg-blue-600 transition-all duration-150" :style="'width:' + Math.round(faceProgress * 100) + '%'"></div>
                </div>

                <button type="button" x-show="livenessTimedOut" @click="livenessTimedOut = false; livenessRetry = true"
                        class="mt-2.5 w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-md transition-colors flex items-center justify-center gap-1.5 cursor-pointer pointer-events-auto">
                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Ulangi Tantangan / Coba Lagi</span>
                </button>
                {{-- Action Buttons (Never disabled during loading) --}}
                <div class="mt-4 pt-3 border-t border-slate-200 flex flex-col sm:flex-row items-center gap-2">
                    <button type="button" @click="closeFaceModal()"
                            class="w-full sm:w-auto flex-1 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali ke Form</span>
                    </button>
                    <button type="button" @click="continueWithoutFace()"
                            class="w-full sm:w-auto py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 text-xs font-semibold transition-colors cursor-pointer">
                        Simpan Tanpa Verifikasi
                    </button>
                    <button type="button" x-show="modalError" @click="retryFaceScan()"
                            class="w-full sm:w-auto py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer">
                        Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

@pushOnce('styles')
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

.face-camera-success-overlay {
    position: absolute !important;
    inset: 0 !important;
    z-index: 10 !important;
}
</style>
@endPushOnce

@pushOnce('scripts')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<x-face-autocapture />
<script>
function createLogbookFaceHandler(userHasFace) {
    const FACE_VERIFY_URL = @json(route('booking.verify-face'));
    const FACE_MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

    return {
        userHasFaceRegistered: userHasFace,
        showFaceModal: false,
        isCameraLoading: false,
        faceScanStatus: 'Mempersiapkan kamera...',
        modalFaceDetected: false,
        faceProgress: 0,
        livenessTimedOut: false,
        livenessRetry: false,
        faceVerifiedSuccess: false,
        modalError: false,
        modalErrorMessage: '',
        faceMatchSimilarity: 0,
        faceStream: null,
        faceAnimId: null,
        faceModelsLoaded: false,
        cameraSessionId: 0,
        targetForm: null,
        targetBookingCode: '',

        submitWithFaceVerification(event, bookingCode) {
            const form = event.target;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (!this.userHasFaceRegistered) {
                // If user has not registered face, proceed normally
                form.submit();
                return;
            }

            this.targetForm = form;
            this.targetBookingCode = bookingCode || '';
            this.openFaceModal();
        },

        openFaceModal() {
            this.showFaceModal = true;
            this.faceVerifiedSuccess = false;
            this.modalError = false;
            this.modalErrorMessage = '';
            this.faceScanStatus = 'Mempersiapkan kamera...';
            this.modalFaceDetected = false;
            const sessionId = ++this.cameraSessionId;

            this.$nextTick(() => {
                this.startFaceDetection(sessionId);
            });
        },

        getModalVideoElement() {
            return document.getElementById('logbookFaceVideo');
        },

        getModalCanvasElement() {
            return document.getElementById('logbookFaceCanvas');
        },

        stopFaceCamera() {
            if (this.faceStream) {
                try {
                    this.faceStream.getTracks().forEach(t => t.stop());
                } catch (e) {
                    console.warn('Error stopping camera stream:', e);
                }
                this.faceStream = null;
            }
            if (this.faceAnimId) {
                cancelAnimationFrame(this.faceAnimId);
                this.faceAnimId = null;
            }
            const video = this.getModalVideoElement();
            if (video) {
                video.srcObject = null;
            }
            const canvas = this.getModalCanvasElement();
            if (canvas) {
                const ctx = canvas.getContext('2d');
                if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        },

        closeFaceModal() {
            this.cameraSessionId++;
            this.isCameraLoading = false;
            this.stopFaceCamera();
            this.showFaceModal = false;
            this.targetForm = null;
            this.targetBookingCode = '';
        },

        continueWithoutFace() {
            this.cameraSessionId++;
            this.isCameraLoading = false;
            this.stopFaceCamera();
            this.showFaceModal = false;

            if (this.targetForm) {
                let verifiedInput = this.targetForm.querySelector('input[name="is_face_verified"]');
                if (!verifiedInput) {
                    verifiedInput = document.createElement('input');
                    verifiedInput.type = 'hidden';
                    verifiedInput.name = 'is_face_verified';
                    this.targetForm.appendChild(verifiedInput);
                }
                verifiedInput.value = '0';
                this.targetForm.submit();
            }
        },

        retryFaceScan() {
            this.modalError = false;
            this.modalErrorMessage = '';
            this.faceScanStatus = 'Mencoba memindai ulang...';
            const sessionId = ++this.cameraSessionId;
            this.startFaceDetection(sessionId);
        },

        async startFaceDetection(sessionId) {
            if (sessionId !== this.cameraSessionId || !this.showFaceModal) return;

            this.isCameraLoading = true;
            this.modalError = false;
            this.modalErrorMessage = '';

            try {
                // 1. Inisialisasi Model AI dengan timeout 12 detik
                if (!this.faceModelsLoaded) {
                    this.faceScanStatus = 'Memuat model AI deteksi wajah...';
                    const loadModelPromise = window.FaceAutoCapture.loadModels(FACE_MODEL_URL);

                    const timeoutModelPromise = new Promise((_, reject) => {
                        setTimeout(() => reject(new Error('Waktu pengunduhan model AI habis (timeout). Periksa koneksi internet Anda.')), 12000);
                    });

                    await Promise.race([loadModelPromise, timeoutModelPromise]);

                    if (sessionId !== this.cameraSessionId || !this.showFaceModal) return;
                    this.faceModelsLoaded = true;
                }

                // 2. Akses Webcam dengan timeout 8 detik
                this.faceScanStatus = 'Membuka kamera webcam...';
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Webcam tidak didukung pada browser ini atau koneksi tidak aman (gunakan HTTPS/localhost).');
                }

                const getUserMediaPromise = navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
                    audio: false
                });

                const timeoutCameraPromise = new Promise((_, reject) => {
                    setTimeout(() => reject(new Error('Waktu inisialisasi webcam habis (timeout). Pastikan kamera diizinkan dan tidak sedang digunakan oleh aplikasi lain.')), 8000);
                });

                const stream = await Promise.race([getUserMediaPromise, timeoutCameraPromise]);

                if (sessionId !== this.cameraSessionId || !this.showFaceModal) {
                    stream.getTracks().forEach(t => t.stop());
                    return;
                }

                this.faceStream = stream;
                const video = this.getModalVideoElement();
                if (!video) {
                    throw new Error('Elemen tampilan video kamera tidak ditemukan.');
                }

                video.srcObject = stream;
                video.muted = true;

                try {
                    await video.play();
                } catch (playErr) {
                    console.warn('Video play warning:', playErr);
                }

                if (sessionId !== this.cameraSessionId || !this.showFaceModal) return;

                this.isCameraLoading = false;
                this.faceScanStatus = 'Posisikan wajah Anda tegak di dalam oval';
                this.runFaceDetectionLoop(sessionId);

            } catch (err) {
                if (sessionId !== this.cameraSessionId) return;
                console.error('Kamera / Face-api error:', err);
                this.isCameraLoading = false;
                this.modalError = true;
                this.stopFaceCamera();

                let userMsg = err.message || 'Gagal mengakses webcam.';
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    userMsg = 'Izin kamera ditolak. Silakan izinkan akses kamera di ikon gembok pada browser Anda.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    userMsg = 'Kamera tidak ditemukan. Pastikan webcam terpasang dengan baik pada perangkat Anda.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    userMsg = 'Kamera sedang digunakan oleh aplikasi lain (seperti Zoom/Teams) atau sistem kamera sibuk.';
                } else if (err.name === 'OverconstrainedError') {
                    userMsg = 'Kamera tidak mendukung spesifikasi resolusi yang diminta.';
                }

                this.modalErrorMessage = userMsg;
                this.faceScanStatus = userMsg;
            }
        },

        runFaceDetectionLoop(sessionId) {
            const video = this.getModalVideoElement();
            const canvas = this.getModalCanvasElement();
            let verifyAttempted = false;
            let isDetecting = false;
            const autoCap = window.FaceAutoCapture();
            this.faceProgress = 0;

            const detect = async () => {
                if (sessionId !== this.cameraSessionId || !this.showFaceModal || this.faceVerifiedSuccess) {
                    return;
                }

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

                        // Fast loop: landmarks only (no descriptor) so blinks are not missed
                        const detection = await window.FaceAutoCapture.detectFast(video);

                        if (sessionId !== this.cameraSessionId || !this.showFaceModal || this.faceVerifiedSuccess) {
                            return;
                        }

                        if (canvas) {
                            const ctx = canvas.getContext('2d');
                            if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
                        }

                        if (this.livenessRetry) { autoCap.reset(); this.livenessRetry = false; this.livenessTimedOut = false; }
                        const ac = autoCap.evaluate(detection, video);
                        this.livenessTimedOut = ac.state === 'timeout';
                        this.faceProgress = ac.progress;
                        this.modalFaceDetected = ['stabilizing', 'liveness', 'ready'].includes(ac.state);

                        if (detection && canvas) {
                            const resized = faceapi.resizeResults(detection, displaySize);
                            faceapi.draw.drawFaceLandmarks(canvas, resized);
                        }

                        if (ac.ready) {
                            this.faceScanStatus = ac.message;
                            verifyAttempted = true;
                            // Extract the 128-d descriptor ONCE, only after the blink passed
                            const descriptor = await window.FaceAutoCapture.extractDescriptor(video);
                            if (sessionId !== this.cameraSessionId || !this.showFaceModal) return;
                            if (!descriptor) {
                                verifyAttempted = false;
                                autoCap.reset();
                                this.faceScanStatus = 'Wajah hilang dari kamera, ulangi kedipan...';
                            } else {
                                await this.verifyFaceWithServer(descriptor, sessionId);
                                return;
                            }
                        } else {
                            this.faceScanStatus = ac.message;
                        }
                    } catch (loopErr) {
                        console.warn('Frame detection error:', loopErr);
                    } finally {
                        isDetecting = false;
                    }
                }

                if (this.showFaceModal && !this.faceVerifiedSuccess && sessionId === this.cameraSessionId) {
                    this.faceAnimId = requestAnimationFrame(detect);
                }
            };

            this.faceAnimId = requestAnimationFrame(detect);
        },

        async verifyFaceWithServer(descriptor, sessionId) {
            if (sessionId !== this.cameraSessionId || !this.showFaceModal) return;

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
                        liveness_passed: true,
                        embedding: descriptor
                    })
                });

                const data = await response.json();

                if (sessionId !== this.cameraSessionId || !this.showFaceModal) return;

                if (response.ok && data.verified) {
                    this.faceVerifiedSuccess = true;
                    this.faceMatchSimilarity = data.similarity || 95;
                    this.faceScanStatus = '✓ Verifikasi berhasil! Menyimpan catatan logbook...';

                    setTimeout(() => {
                        if (sessionId === this.cameraSessionId && this.showFaceModal) {
                            this.stopFaceCamera();
                            this.showFaceModal = false;

                            if (this.targetForm) {
                                let verifiedInput = this.targetForm.querySelector('input[name="is_face_verified"]');
                                if (!verifiedInput) {
                                    verifiedInput = document.createElement('input');
                                    verifiedInput.type = 'hidden';
                                    verifiedInput.name = 'is_face_verified';
                                    this.targetForm.appendChild(verifiedInput);
                                }
                                verifiedInput.value = '1';

                                let embeddingInput = this.targetForm.querySelector('input[name="face_embedding"]');
                                if (!embeddingInput) {
                                    embeddingInput = document.createElement('input');
                                    embeddingInput.type = 'hidden';
                                    embeddingInput.name = 'face_embedding';
                                    this.targetForm.appendChild(embeddingInput);
                                }
                                embeddingInput.value = JSON.stringify(descriptor);

                                this.targetForm.submit();
                            }
                        }
                    }, 1200);
                } else {
                    this.modalError = true;
                    const errMsg = data.message || 'Wajah tidak cocok dengan akun terdaftar.';
                    this.modalErrorMessage = errMsg;
                    this.faceScanStatus = errMsg;
                }
            } catch (err) {
                if (sessionId !== this.cameraSessionId) return;
                console.error('Verifikasi wajah error:', err);
                this.modalError = true;
                const errMsg = 'Gagal menghubungi server verifikasi. Periksa koneksi Anda.';
                this.modalErrorMessage = errMsg;
                this.faceScanStatus = errMsg;
            }
        }
    };
}
</script>
@endPushOnce
