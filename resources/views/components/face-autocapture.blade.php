{{--
    Shared Auto-Capture + Liveness (eye-blink) helper for all Face Recognition modules.

    Usage:
        await window.FaceAutoCapture.loadModels();            // tinyFaceDetector + landmark68Tiny + landmark68 + recognition
        const ac  = window.FaceAutoCapture();
        const det = await window.FaceAutoCapture.detectFast(video);   // landmarks only, NO descriptor (fast loop)
        const r   = ac.evaluate(det, video);                  // {state, progress, message, ready, blink}
        if (r.ready) {
            const descriptor = await window.FaceAutoCapture.extractDescriptor(video); // 128-d, called ONCE
        }
        ac.reset();

    states: none | weak | offcenter | far | stabilizing | liveness | timeout | ready
    blink (sub-state while state === 'liveness'): calibrating | waiting | closed

    Flow:
      (1) face score >= 0.80, centered & stable ~0.7s
      (2) calibrate baseline EAR from the first 5 open-eye frames
      (3) blink: EAR < baseline*0.78 (or drop >= 0.045) for >= 1 frame, then EAR >= baseline*0.88
      (4) ready -> caller extracts the descriptor once.
--}}
<script>
(function () {
    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
    let modelsPromise = null;
    let useTinyLandmarks = true;

    const factory = function (options) {
        const cfg = Object.assign({
            minScore: 0.80,            // stage 1: confident face
            livenessMinScore: 0.50,    // stage 2: eyes closing can lower detector score
            holdMs: 700,
            minFrames: 3,
            centerTolerance: 0.25,
            minFaceRatio: 0.18,
            maxMove: 0.06,
            calibFrames: 5,
            closedRatio: 0.78,         // closed if EAR < baseline * 0.78
            closedDrop: 0.045,         //   ... or baseline - EAR >= 0.045
            openRatio: 0.88,           // blink complete when EAR >= baseline * 0.88
            minClosedFrames: 1,
            maxBlinkMs: 2000,
            lostGraceMs: 700,          // tolerate brief detection drops during the blink
            livenessTimeoutMs: 15000,
        }, options || {});

        let frames = 0, startAt = 0, last = null;
        let phase = 'stabilize';       // stabilize | liveness | timeout | done
        let livenessStart = 0, lostSince = 0;
        let calib = [], baseline = null;
        let closed = false, closedAt = 0, closedFrames = 0;
        let lastLiveness = null;

        const resetBlink = () => { calib = []; baseline = null; closed = false; closedAt = 0; closedFrames = 0; };
        const reset = () => {
            frames = 0; startAt = 0; last = null;
            phase = 'stabilize'; livenessStart = 0; lostSince = 0; lastLiveness = null;
            resetBlink();
        };
        const result = (state, progress, message, ready = false, blink = null) => ({ state, progress, message, ready, blink });

        const dist = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
        const ear = (p) => (dist(p[1], p[5]) + dist(p[2], p[4])) / (2 * dist(p[0], p[3]));
        const computeEAR = (landmarks) => {
            const pts = landmarks.positions;
            return (ear(pts.slice(36, 42)) + ear(pts.slice(42, 48))) / 2;
        };

        const MSG_TIMEOUT = 'Waktu verifikasi keaktifan habis. Klik "Ulangi Tantangan".';
        const MSG_BLINK = '👁️ Berkedip perlahan 1 kali ke kamera';

        const livenessResult = (blink, progress, message) => (lastLiveness = result('liveness', progress, message, false, blink));

        // During liveness a single missed/weak frame (often caused by the blink itself) must not reset the challenge.
        const softFail = (state, message) => {
            if (phase === 'liveness') {
                const now = performance.now();
                if (!lostSince) lostSince = now;
                if (now - lostSince <= cfg.lostGraceMs && lastLiveness) return lastLiveness;
            }
            reset();
            return result(state, 0, message);
        };

        return {
            reset,
            computeEAR,
            get baselineEAR() { return baseline; },
            evaluate(detection, video) {
                if (phase === 'timeout') return result('timeout', 0, MSG_TIMEOUT);
                if (phase === 'done') return result('ready', 1, '✅ Kedipan valid, memverifikasi wajah...', true);
                if (!detection) return softFail('none', 'Posisikan wajah di tengah kamera...');

                const det = detection.detection || detection;
                const box = det.box;
                const vw = video.videoWidth || 640;
                const vh = video.videoHeight || 480;
                const minScore = phase === 'liveness' ? cfg.livenessMinScore : cfg.minScore;

                if (det.score < minScore) return softFail('weak', 'Wajah kurang jelas, perbaiki pencahayaan / hadap lurus ke kamera...');
                const cx = (box.x + box.width / 2) / vw;
                const cy = (box.y + box.height / 2) / vh;
                if (Math.abs(cx - 0.5) > cfg.centerTolerance || Math.abs(cy - 0.5) > cfg.centerTolerance) {
                    return softFail('offcenter', 'Geser wajah ke tengah kamera...');
                }
                if (box.width / vw < cfg.minFaceRatio) return softFail('far', 'Dekatkan wajah ke kamera...');
                lostSince = 0;

                // ---- Stage 2: blink challenge with dynamic baseline EAR ----
                if (phase === 'liveness') {
                    const now = performance.now();
                    if (now - livenessStart > cfg.livenessTimeoutMs) {
                        phase = 'timeout';
                        return result('timeout', 0, MSG_TIMEOUT);
                    }
                    if (!detection.landmarks) return livenessResult('waiting', 0.5, MSG_BLINK);
                    const value = computeEAR(detection.landmarks);

                    if (baseline === null) {
                        calib.push(value);
                        if (calib.length < cfg.calibFrames) {
                            return livenessResult('calibrating', 0.5 + 0.1 * (calib.length / cfg.calibFrames), 'Kalibrasi mata... tetap buka mata sebentar');
                        }
                        // robust mean: drop the lowest sample (in case the user blinked during calibration)
                        const s = calib.slice().sort((a, b) => a - b).slice(1);
                        baseline = s.reduce((a, b) => a + b, 0) / s.length;
                        return livenessResult('waiting', 0.6, MSG_BLINK);
                    }

                    const isClosed = value < baseline * cfg.closedRatio || (baseline - value) >= cfg.closedDrop;
                    if (isClosed) {
                        if (!closed) { closed = true; closedAt = now; closedFrames = 0; }
                        closedFrames++;
                        if (now - closedAt > cfg.maxBlinkMs) {
                            // eyes closed too long -> not a natural blink; wait for eyes to reopen
                            return livenessResult('waiting', 0.6, 'Buka mata, lalu berkedip perlahan 1 kali');
                        }
                        return livenessResult('closed', 0.85, '😌 Mata tertutup terdeteksi...');
                    }

                    if (closed && value >= baseline * cfg.openRatio) {
                        const valid = closedFrames >= cfg.minClosedFrames && (now - closedAt) <= cfg.maxBlinkMs;
                        closed = false; closedFrames = 0;
                        if (valid) {
                            phase = 'done';
                            return result('ready', 1, '✅ Kedipan valid, memverifikasi wajah...', true);
                        }
                    } else if (!closed && value >= baseline * cfg.openRatio) {
                        // slowly follow lighting / pose drift while eyes are open
                        baseline = baseline * 0.95 + value * 0.05;
                    }
                    return livenessResult(closed ? 'closed' : 'waiting', closed ? 0.85 : 0.6,
                        closed ? '😌 Mata tertutup terdeteksi...' : MSG_BLINK);
                }

                // ---- Stage 1: stable face ----
                if (last && Math.hypot(cx - last.cx, cy - last.cy) > cfg.maxMove) { frames = 0; startAt = 0; }
                last = { cx, cy };
                if (!startAt) startAt = performance.now();
                frames++;
                const elapsed = performance.now() - startAt;
                if (elapsed >= cfg.holdMs && frames >= cfg.minFrames) {
                    phase = 'liveness';
                    livenessStart = performance.now();
                    resetBlink();
                    return livenessResult('calibrating', 0.5, 'Kalibrasi mata... tetap buka mata sebentar');
                }
                const progress = Math.min(1, elapsed / cfg.holdMs, frames / cfg.minFrames) * 0.5;
                return result('stabilizing', progress, 'Wajah terdeteksi, tahan sebentar...');
            },
        };
    };

    factory.MODEL_URL = MODEL_URL;

    // Load all nets once per page (cached promise). Tiny landmark net is optional: falls back to the full net.
    factory.loadModels = function (url) {
        if (modelsPromise) return modelsPromise;
        const base = url || MODEL_URL;
        modelsPromise = Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(base),
            faceapi.nets.faceLandmark68Net.loadFromUri(base),
            faceapi.nets.faceRecognitionNet.loadFromUri(base),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(base).catch((e) => {
                console.warn('faceLandmark68TinyNet gagal dimuat, memakai faceLandmark68Net:', e);
                useTinyLandmarks = false;
            }),
        ]).catch((e) => { modelsPromise = null; throw e; });
        return modelsPromise;
    };

    // Fast per-frame detection for the liveness loop: landmarks only, no 128-d descriptor.
    factory.detectFast = function (video) {
        const tiny = useTinyLandmarks && faceapi.nets.faceLandmark68TinyNet.isLoaded;
        return faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
            .withFaceLandmarks(tiny);
    };

    // One-shot descriptor extraction after liveness passes. Uses the full 68-point landmark net for
    // alignment so descriptors stay consistent with the ones already stored in the database.
    factory.extractDescriptor = async function (video, tries = 3) {
        for (let i = 0; i < tries; i++) {
            try {
                const det = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                if (det && det.descriptor) return Array.from(det.descriptor);
            } catch (e) {
                console.warn('extractDescriptor error:', e);
            }
            await new Promise((r) => requestAnimationFrame(r));
        }
        return null;
    };

    window.FaceAutoCapture = factory;
})();
</script>
