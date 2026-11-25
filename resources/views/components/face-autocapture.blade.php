{{--
    Shared Auto-Capture + Liveness (eye-blink) helper for all Face Recognition modules.
    Usage:  const ac = window.FaceAutoCapture();
            const r = ac.evaluate(detection, videoEl);  // detection must include landmarks
            // r = {state, progress, message, ready}
            // states: none | weak | offcenter | far | stabilizing | liveness | timeout | ready
            ac.reset();
    Flow: (1) face score >= 0.80, centered & stable ~1.2s  ->  (2) blink challenge
          (EAR < 0.21 then > 0.25, within 15s timeout)       ->  (3) ready (liveness passed).
--}}
<script>
window.FaceAutoCapture = function (options) {
    const cfg = Object.assign({
        minScore: 0.80,
        holdMs: 1200,
        minFrames: 3,
        centerTolerance: 0.25,
        minFaceRatio: 0.18,
        maxMove: 0.06,
        earClosed: 0.21,
        earOpen: 0.25,
        maxBlinkMs: 2000,
        livenessTimeoutMs: 15000,
    }, options || {});

    let frames = 0, startAt = 0, last = null;
    let phase = 'stabilize';          // stabilize | liveness | timeout | done
    let livenessStart = 0, closedAt = 0, sawClosed = false;

    const reset = () => {
        frames = 0; startAt = 0; last = null;
        phase = 'stabilize'; livenessStart = 0; closedAt = 0; sawClosed = false;
    };
    const result = (state, progress, message, ready = false) => ({ state, progress, message, ready });

    const dist = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
    const ear = (p) => (dist(p[1], p[5]) + dist(p[2], p[4])) / (2 * dist(p[0], p[3]));
    const computeEAR = (landmarks) => {
        const pts = landmarks.positions;
        const left = pts.slice(36, 42);
        const right = pts.slice(42, 48);
        return (ear(left) + ear(right)) / 2;
    };

    return {
        reset,
        computeEAR,
        evaluate(detection, video) {
            if (phase === 'timeout') {
                return result('timeout', 0, 'Waktu verifikasi keaktifan habis. Klik "Ulangi Tantangan".');
            }
            if (!detection) {
                reset();
                return result('none', 0, 'Posisikan wajah di tengah kamera...');
            }
            const det = detection.detection || detection;
            const box = det.box;
            const vw = video.videoWidth || 640;
            const vh = video.videoHeight || 480;

            if (det.score < cfg.minScore) {
                reset();
                return result('weak', 0, 'Wajah kurang jelas, perbaiki pencahayaan / hadap lurus ke kamera...');
            }
            const cx = (box.x + box.width / 2) / vw;
            const cy = (box.y + box.height / 2) / vh;
            if (Math.abs(cx - 0.5) > cfg.centerTolerance || Math.abs(cy - 0.5) > cfg.centerTolerance) {
                reset();
                return result('offcenter', 0, 'Geser wajah ke tengah kamera...');
            }
            if (box.width / vw < cfg.minFaceRatio) {
                reset();
                return result('far', 0, 'Dekatkan wajah ke kamera...');
            }

            // ---- Stage 2: blink challenge ----
            if (phase === 'liveness') {
                const now = performance.now();
                if (now - livenessStart > cfg.livenessTimeoutMs) {
                    phase = 'timeout';
                    return result('timeout', 0, 'Waktu verifikasi keaktifan habis. Klik "Ulangi Tantangan".');
                }
                const value = detection.landmarks ? computeEAR(detection.landmarks) : 1;
                if (value < cfg.earClosed) {
                    if (!sawClosed) { sawClosed = true; closedAt = now; }
                } else if (sawClosed && value > cfg.earOpen) {
                    if (now - closedAt <= cfg.maxBlinkMs) {
                        phase = 'done';
                        return result('ready', 1, 'Kedipan terdeteksi! Berhasil dipindai!', true);
                    }
                    sawClosed = false; // eyes were closed too long (not a natural blink)
                }
                return result('liveness', sawClosed ? 0.75 : 0.5, 'Silakan berkedip 1 kali untuk verifikasi keaktifan');
            }

            // ---- Stage 1: stable face ----
            if (last && Math.hypot(cx - last.cx, cy - last.cy) > cfg.maxMove) {
                frames = 0; startAt = 0;
            }
            last = { cx, cy };
            if (!startAt) startAt = performance.now();
            frames++;
            const elapsed = performance.now() - startAt;
            if (elapsed >= cfg.holdMs && frames >= cfg.minFrames) {
                phase = 'liveness';
                livenessStart = performance.now();
                sawClosed = false;
                return result('liveness', 0.5, 'Silakan berkedip 1 kali untuk verifikasi keaktifan');
            }
            const progress = Math.min(1, elapsed / cfg.holdMs, frames / cfg.minFrames) * 0.5;
            return result('stabilizing', progress, 'Wajah terdeteksi, tahan sebentar...');
        },
    };
};
</script>
