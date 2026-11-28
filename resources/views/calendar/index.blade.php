<x-app-layout>

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* ── Calendar shell ─────────────────────────────────────────── */
.wcal-wrap { --wcal-gutter: 58px; --wcal-hour-h: 56px; }
.wcal-card { background:#fff; border:1px solid rgba(15,36,96,.10); border-radius:14px; box-shadow:0 1px 2px rgba(16,18,27,.04),0 8px 24px -12px rgba(16,18,27,.14); overflow:hidden; position:relative; z-index:1; }

/* Toolbar */
.wcal-tb { display:flex; align-items:center; gap:14px; padding:12px 16px; border-bottom:1px solid rgba(15,36,96,.08); flex-wrap:wrap; }
.wcal-today { height:32px; padding:0 14px; border:1px solid rgba(15,36,96,.12); background:#fff; border-radius:8px; font-size:13px; font-weight:600; color:#0A1A47; transition:background .12s,border-color .12s; }
.wcal-today:hover { background:#FAFAF7; border-color:rgba(15,36,96,.25); }
.wcal-iconbtn { width:32px; height:32px; display:grid; place-items:center; border:1px solid rgba(15,36,96,.12); background:#fff; border-radius:8px; color:rgba(10,26,71,.55); transition:background .12s,color .12s,border-color .12s; }
.wcal-iconbtn:hover { background:#FAFAF7; color:#0A1A47; border-color:rgba(15,36,96,.25); }
.wcal-iconbtn:disabled { opacity:.35; cursor:not-allowed; }
.wcal-iconbtn svg { stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
.wcal-period { font-size:17px; font-weight:700; letter-spacing:-.015em; color:#0A1A47; min-width:150px; }
.wcal-period .wcal-sub { color:rgba(10,26,71,.4); font-weight:500; font-size:12.5px; margin-left:7px; }
.wcal-seg { display:flex; background:#FAFAF7; border:1px solid rgba(15,36,96,.10); border-radius:9px; padding:3px; gap:2px; }
.wcal-seg button { border:0; background:transparent; padding:5px 13px; font-size:13px; font-weight:600; color:rgba(10,26,71,.45); border-radius:6px; transition:background .12s,color .12s; }
.wcal-seg button:hover { color:#0A1A47; }
.wcal-seg button.active { background:#fff; color:#0A1A47; box-shadow:0 1px 2px rgba(16,18,27,.10); }
.wcal-newbtn { height:34px; padding:0 15px; display:flex; align-items:center; gap:7px; background:#F5B800; color:#0A1A47; border:0; border-radius:9px; font-size:13px; font-weight:700; white-space:nowrap; box-shadow:0 1px 2px rgba(16,18,27,.10); transition:filter .12s,transform .04s; }
.wcal-newbtn:hover { filter:brightness(1.04); }
.wcal-newbtn:active { transform:translateY(1px); }
.wcal-newbtn svg { stroke:currentColor; fill:none; stroke-width:2.4; stroke-linecap:round; }
.wcal-cancelbtn { height:34px; padding:0 15px; display:flex; align-items:center; gap:7px; background:#fff; color:#0A1A47; border:1px solid rgba(15,36,96,.18); border-radius:9px; font-size:13px; font-weight:600; white-space:nowrap; transition:background .12s,border-color .12s; }
.wcal-cancelbtn:hover { background:#FAFAF7; border-color:rgba(15,36,96,.3); }

/* ── Booking mode — events become bare, click-through colored blocks so the drag
      surface is clear (cards no longer intercept the mousedown). ─────────────── */
.wcal-booking-mode .wcal-ev { pointer-events:none; cursor:default; opacity:.92; }
.wcal-booking-mode .wcal-ev > div { display:none; }            /* hide all card text */
.wcal-booking-mode .wcal-ev.is-selected { box-shadow:none; }
.wcal-booking-mode .wcal-ev.is-pending::after { display:none; } /* drop the stripe for a clean block */
.wcal-booking-mode .wcal-col.is-bookable { cursor:crosshair; }

/* Legend */
.wcal-legend { display:flex; align-items:center; gap:16px; padding:8px 16px; border-bottom:1px solid rgba(15,36,96,.08); flex-wrap:wrap; }
.wcal-legend-item { display:flex; align-items:center; gap:6px; font-size:11px; font-weight:600; color:rgba(10,26,71,.5); }
.wcal-legend-sw { width:11px; height:11px; border-radius:3px; }

/* Scroll + grid */
.wcal-scroll { overflow:auto; max-height:calc(100vh - 250px); position:relative; z-index:1; }
.wcal-head, .wcal-canvas { display:grid; grid-template-columns:var(--wcal-gutter) repeat(var(--wcal-n), minmax(0,1fr)); }
.wcal-head, .fc-header-toolbar, .fc-col-header, .fc-scrollgrid-section-header { position:sticky; top:0; z-index:10 !important; background:#fff; border-bottom:1px solid rgba(15,36,96,.10); }
#calendar, 
.fc, 
.fc-view-harness,
.fc-header-toolbar,
.fc-scrollgrid,
.fc-scrollgrid-section,
.fc-scrollgrid-section-header,
.fc-col-header,
.fc-col-header-cell,
.wcal-scroll,
.wcal-card {
    z-index: 1;
}
.wcal-corner { border-right:1px solid rgba(15,36,96,.08); display:flex; align-items:flex-end; justify-content:center; padding-bottom:6px; font-size:10px; color:rgba(10,26,71,.4); font-weight:600; }
.wcal-dh { padding:9px 6px 8px; border-right:1px solid rgba(15,36,96,.06); display:flex; flex-direction:column; align-items:center; gap:3px; }
.wcal-dh:last-child { border-right:0; }
.wcal-dh-name { font-size:11px; font-weight:700; color:rgba(10,26,71,.42); text-transform:uppercase; letter-spacing:.05em; }
.wcal-dh-num { font-size:18px; font-weight:700; letter-spacing:-.02em; width:34px; height:34px; border-radius:9px; display:grid; place-items:center; color:#0A1A47; }
.wcal-dh.is-today .wcal-dh-name { color:#4f46e5; }
.wcal-dh.is-today .wcal-dh-num { background:#4f46e5; color:#fff; }
.wcal-dh.is-closed .wcal-dh-num { color:rgba(10,26,71,.3); }

.wcal-canvas { position:relative; }
.wcal-gutter { border-right:1px solid rgba(15,36,96,.08); position:relative; background:#fff; }
.wcal-hour { position:absolute; right:9px; transform:translateY(-50%); font-size:11px; color:rgba(10,26,71,.4); font-weight:500; font-variant-numeric:tabular-nums; white-space:nowrap; }
.wcal-col { position:relative; border-right:1px solid rgba(15,36,96,.06); background-image:repeating-linear-gradient(to bottom, rgba(15,36,96,.06) 0, rgba(15,36,96,.06) 1px, transparent 1px, transparent var(--wcal-hour-h)); }
.wcal-col:last-child { border-right:0; }
.wcal-col.is-closed { background-color:#FAFAF7; cursor:not-allowed; }
.wcal-col.is-past { background-color:rgba(15,36,96,.015); }
.wcal-col.is-bookable { cursor:pointer; }

/* drag-to-block selection */
.wcal-block { position:absolute; left:4px; right:5px; z-index:6; border-radius:7px; background:rgba(79,70,229,.14); border:1.5px dashed #4f46e5; pointer-events:none; overflow:hidden; }
.wcal-block-label { position:absolute; top:3px; left:7px; font-size:11px; font-weight:700; color:#4f46e5; font-variant-numeric:tabular-nums; white-space:nowrap; }

/* event card */
.wcal-ev { position:absolute; border-radius:7px; padding:5px 8px; overflow:hidden; cursor:pointer; font-size:12px; border:1px solid transparent; border-left:3px solid var(--ev); background:var(--ev-bg); color:var(--ev-fg); box-shadow:0 1px 1px rgba(16,18,27,.05); transition:box-shadow .12s,transform .04s; user-select:none; z-index:4; }
.wcal-ev:hover { box-shadow:0 2px 8px -1px rgba(16,18,27,.20); z-index:8; }
.wcal-ev.is-selected { box-shadow:0 0 0 2px #fff,0 0 0 4px var(--ev); z-index:9; }
.wcal-ev.is-mine { box-shadow:inset 0 0 0 1.5px var(--ev); }
.wcal-ev.is-mine.is-selected { box-shadow:0 0 0 2px #fff,0 0 0 4px var(--ev); }
.wcal-ev.is-pending { border-left-style:dashed; }
.wcal-ev.is-pending::after { content:''; position:absolute; inset:0; background:repeating-linear-gradient(45deg, transparent, transparent 6px, rgba(255,255,255,.35) 6px, rgba(255,255,255,.35) 8px); pointer-events:none; }
.wcal-ev.wcal-narrow { padding:4px 5px; }
.wcal-ev.wcal-narrow .wcal-ev-badge { display:none; }
.wcal-ev-row { display:flex; align-items:center; gap:5px; min-width:0; position:relative; z-index:1; }
.wcal-ev-badge { flex:none; font-size:9px; font-weight:700; letter-spacing:.02em; color:#fff; padding:1px 5px; border-radius:4px; text-transform:uppercase; line-height:1.4; }
.wcal-ev-title { font-weight:700; letter-spacing:-.005em; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.wcal-ev-sub { font-size:11px; opacity:.8; margin-top:1px; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; position:relative; z-index:1; }
.wcal-ev-time { font-size:10.5px; opacity:.72; margin-top:1px; font-variant-numeric:tabular-nums; position:relative; z-index:1; }
.wcal-ev.wcal-compact { padding:3px 8px; }
.wcal-ev.wcal-compact .wcal-ev-time, .wcal-ev.wcal-compact .wcal-ev-sub, .wcal-ev.wcal-compact .wcal-ev-badge { display:none; }

/* rollup tile */
.wcal-ev.wcal-rollup { background:#fff; border:1px dashed rgba(15,36,96,.18); border-left:3px solid rgba(10,26,71,.4); color:rgba(10,26,71,.55); display:flex; flex-direction:column; justify-content:center; }
.wcal-ev.wcal-rollup::after { display:none; }
.wcal-ev.wcal-rollup:hover { border-color:rgba(10,26,71,.4); background:#FAFAF7; }
.wcal-rollup-count { font-size:14px; font-weight:800; color:#0A1A47; letter-spacing:-.02em; line-height:1; }
.wcal-rollup .wcal-ev-title { color:#0A1A47; font-size:11px; margin-top:2px; }
.wcal-rollup.wcal-narrow { align-items:center; text-align:center; padding:4px 2px; }

/* now line */
.wcal-now { position:absolute; left:var(--wcal-gutter); right:0; height:0; z-index:15; pointer-events:none; border-top:2px solid #ef4444; }
.wcal-now::before { content:''; position:absolute; left:-4px; top:-5px; width:9px; height:9px; border-radius:50%; background:#ef4444; }
.wcal-now-flag { position:absolute; right:calc(100% - var(--wcal-gutter) + 3px); top:-8px; font-size:10px; font-weight:700; color:#ef4444; font-variant-numeric:tabular-nums; white-space:nowrap; }

/* ── Popovers ───────────────────────────────────────────────── */
.wcal-overlay { position:fixed; inset:0; z-index:9990; }
.wcal-pop { position:fixed; z-index:9999; width:300px; max-height:calc(100vh - 24px); overflow-y:auto; overscroll-behavior:contain; background:#fff; border:1px solid rgba(15,36,96,.10); border-radius:14px; box-shadow:0 4px 12px -2px rgba(16,18,27,.10),0 16px 40px -8px rgba(16,18,27,.28); padding:16px; pointer-events:auto; }
.wcal-pop.wide { width:330px; }
.wcal-pop h3 { margin:0 0 12px; font-size:14px; font-weight:700; color:#0A1A47; }
.wcal-pop label.wcal-lbl { display:block; font-size:10.5px; font-weight:700; color:rgba(10,26,71,.5); margin:0 0 5px; text-transform:uppercase; letter-spacing:.04em; }
.wcal-datefield { display:flex; align-items:center; gap:8px; padding:9px 11px; margin-bottom:12px; border-radius:8px; border:1px solid rgba(15,36,96,.10); background:#FAFAF7; font-size:13px; font-weight:600; color:#0A1A47; }
.wcal-datefield svg { color:rgba(10,26,71,.4); flex:none; stroke:currentColor; fill:none; stroke-width:2; }
.wcal-row { display:flex; gap:10px; }
.wcal-row > div { flex:1; }
.wcal-select { width:100%; padding:8px 10px; font-size:13px; font-family:inherit; border:1px solid rgba(15,36,96,.10); border-radius:8px; background:#FAFAF7; color:#0A1A47; margin-bottom:12px; outline:none; }
.wcal-select:focus { border-color:#4f46e5; background:#fff; }
.wcal-typelist { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
.wcal-typeopt { display:flex; align-items:center; gap:9px; padding:8px 10px; border-radius:9px; border:1px solid rgba(15,36,96,.10); background:#FAFAF7; text-align:left; transition:border-color .12s,background .12s; }
.wcal-typeopt:hover:not(:disabled) { border-color:rgba(10,26,71,.3); }
.wcal-typeopt:disabled { opacity:.4; cursor:not-allowed; }
.wcal-typeopt.on { border-width:1.5px; }
.wcal-typesw { width:11px; height:11px; border-radius:3px; flex:none; }
.wcal-typename { font-size:13px; font-weight:600; color:#0A1A47; flex:1; }
.wcal-typebadge { font-size:9px; font-weight:700; color:#fff; padding:2px 6px; border-radius:4px; text-transform:uppercase; letter-spacing:.02em; }
.wcal-submode { display:flex; gap:6px; margin:-4px 0 14px; }
.wcal-submode button { flex:1; height:32px; border-radius:8px; border:1px solid rgba(15,36,96,.10); background:#FAFAF7; font-size:13px; font-weight:600; color:rgba(10,26,71,.5); transition:border-color .12s,color .12s,background .12s; }
.wcal-submode button:hover:not(:disabled) { color:#0A1A47; }
.wcal-submode button:disabled { opacity:.4; cursor:not-allowed; }
.wcal-submode button.on { border-color:#4f46e5; color:#4f46e5; background:rgba(79,70,229,.07); }
.wcal-input { width:100%; padding:8px 10px; font-size:13px; font-family:inherit; border:1px solid rgba(15,36,96,.10); border-radius:8px; background:#FAFAF7; color:#0A1A47; margin-bottom:12px; outline:none; }
.wcal-input:focus { border-color:#4f46e5; background:#fff; }
.wcal-pc-note { font-size:12px; color:rgba(10,26,71,.5); padding:8px 0 12px; }
.wcal-pc-note.err { color:#B91C1C; }
.wcal-pcgrid { display:grid; grid-template-columns:repeat(3, 1fr); gap:6px; margin-bottom:12px; }
.wcal-pcbox { display:flex; flex-direction:column; align-items:center; gap:2px; padding:8px 4px; border-radius:8px; border:1.5px solid rgba(15,36,96,.10); background:#FAFAF7; cursor:pointer; transition:border-color .12s,background .12s; }
.wcal-pcbox:hover:not(:disabled) { border-color:#4f46e5; }
.wcal-pcbox.on { border-color:#7c3aed; background:rgba(124,58,237,.08); }
.wcal-pcbox.off { opacity:.4; cursor:not-allowed; }
.wcal-pcbox-label { font-size:12px; font-weight:700; color:#0A1A47; font-family:'JetBrains Mono', ui-monospace, monospace; }
.wcal-pcbox-status { font-size:8.5px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:rgba(10,26,71,.45); }
.wcal-hidden-form { display:none; }
.wcal-banner { display:flex; gap:8px; align-items:flex-start; padding:9px 11px; border-radius:8px; margin-bottom:14px; font-size:11.5px; line-height:1.45; }
.wcal-banner svg { flex:none; margin-top:1px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
.wcal-banner.red { background:#FEF2F2; border:1px solid #FCA5A5; color:#B91C1C; }
.wcal-banner.teal { background:#F0FDFA; border:1px solid #99F6E4; color:#0F766E; }
.wcal-banner.amber { background:#FFFBEB; border:1px solid #FDE68A; color:#92400E; }
.wcal-actions { display:flex; gap:8px; justify-content:flex-end; align-items:center; }
.wcal-btn { height:33px; padding:0 14px; border-radius:8px; font-size:13px; font-weight:600; border:1px solid rgba(15,36,96,.12); background:#fff; color:#0A1A47; transition:background .12s; }
.wcal-btn:hover { background:#FAFAF7; }
.wcal-btn.primary { background:#F5B800; color:#0A1A47; border-color:transparent; font-weight:700; }
.wcal-btn.primary:hover { filter:brightness(1.04); background:#F5B800; }
.wcal-btn.primary:disabled { opacity:.4; cursor:not-allowed; filter:none; }
.wcal-btn.danger { color:#e1483b; border-color:transparent; background:transparent; margin-right:auto; padding:0 8px; }
.wcal-btn.danger:hover { background:rgba(225,72,59,.1); }

/* details */
.wcal-det-head { display:flex; align-items:flex-start; gap:10px; margin-bottom:12px; }
.wcal-det-sw { width:12px; height:12px; border-radius:3px; margin-top:4px; flex:none; }
.wcal-det-title { font-size:15px; font-weight:700; letter-spacing:-.01em; line-height:1.25; color:#0A1A47; }
.wcal-det-tag { font-size:11.5px; font-weight:600; padding:2px 9px; border-radius:999px; display:inline-block; margin-top:5px; }
.wcal-det-row { display:flex; align-items:center; gap:10px; font-size:13px; color:rgba(10,26,71,.6); margin-bottom:8px; }
.wcal-det-row svg { flex:none; color:rgba(10,26,71,.4); stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }

/* group list */
.wcal-grp-sub { font-size:12px; color:rgba(10,26,71,.4); font-variant-numeric:tabular-nums; margin-bottom:12px; }
.wcal-grp-list { display:flex; flex-direction:column; gap:4px; max-height:300px; overflow:auto; margin:0 -4px; padding:0 4px; }
.wcal-grp-row { display:flex; align-items:flex-start; gap:9px; padding:8px; border-radius:9px; cursor:pointer; transition:background .12s; }
.wcal-grp-row:hover { background:#FAFAF7; }
.wcal-grp-main { flex:1; min-width:0; }
.wcal-grp-title { font-size:13px; font-weight:700; letter-spacing:-.005em; color:#0A1A47; }
.wcal-grp-meta { font-size:11.5px; color:rgba(10,26,71,.55); margin-top:1px; font-variant-numeric:tabular-nums; }

@media (max-width:640px) {
    .wcal-period { min-width:auto; font-size:15px; }
    .wcal-scroll { max-height:calc(100vh - 300px); }
}

/* ── Face Verification Modal & Stacking ─────────────────────── */
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

/* Area preview kamera: overflow hidden & isolated layer */
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
@endpush

    <x-slot:header>
        <div class="pb-5 mb-0 border-b border-rule">
            <div class="text-[0.7rem] font-semibold uppercase tracking-label text-mark-600 mb-1">Jadwal</div>
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-ink-900 tracking-tight">Kalender Lab</h2>
                    <p class="mt-1 text-xs sm:text-sm text-ink-700/60">
                        Klik atau seret pada slot waktu untuk membuat reservasi baru.
                    </p>
                </div>
            </div>
        </div>
    </x-slot:header>

    <div class="wcal-wrap" x-data="weekCal()" x-init="init()"
         :style="{ '--wcal-n': ndays, '--wcal-hour-h': hourH + 'px' }">

        <div class="wcal-card">

            {{-- ── Toolbar ── --}}
            <div class="wcal-tb">
                <button class="wcal-today" @click="goToday()">Hari Ini</button>
                <div class="flex items-center gap-1.5">
                    <button class="wcal-iconbtn" @click="go(-1)" :disabled="!canGo(-1)" aria-label="Sebelumnya">
                        <svg width="16" height="16" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button class="wcal-iconbtn" @click="go(1)" :disabled="!canGo(1)" aria-label="Berikutnya">
                        <svg width="16" height="16" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
                <div class="wcal-period">
                    <span x-text="periodLabel"></span><span class="wcal-sub" x-text="periodSub" x-show="periodSub"></span>
                </div>
                <div class="flex-1"></div>
                <div class="wcal-seg">
                    <button :class="{ active: view==='week' }" @click="setView('week')">Minggu</button>
                    <button :class="{ active: view==='day' }" @click="setView('day')">Hari</button>
                </div>
                {{-- View mode → enter booking (drag) mode --}}
                <button class="wcal-newbtn" x-show="!bookingMode" @click="enterBookingMode()">
                    <svg width="15" height="15" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Buat Reservasi
                </button>
                {{-- Booking mode → cancel back to view --}}
                <button class="wcal-cancelbtn" x-show="bookingMode" x-cloak @click="exitBookingMode()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Batal
                </button>
            </div>

            {{-- ── Legend ── --}}
            <div class="wcal-legend">
                <div class="wcal-legend-item"><span class="wcal-legend-sw" style="background:#4f46e5"></span>Komputer</div>
                <div class="wcal-legend-item"><span class="wcal-legend-sw" style="background:#7c3aed"></span>Ruang + Komputer</div>
                <div class="wcal-legend-item"><span class="wcal-legend-sw" style="background:#0d9488"></span>Ruang Eksklusif</div>
                <div class="wcal-legend-item"><span class="wcal-legend-sw" style="background:#d97706"></span>Ruang Berbagi</div>
                <div class="wcal-legend-item" style="margin-left:auto"><span class="wcal-legend-sw" style="background:repeating-linear-gradient(45deg,#cbd5e1,#cbd5e1 3px,#fff 3px,#fff 5px);border:1px solid #cbd5e1"></span>Menunggu persetujuan</div>
            </div>

            {{-- ── Calendar scroll ── --}}
            <div class="wcal-scroll">

                {{-- Sticky day header --}}
                <div class="wcal-head">
                    <div class="wcal-corner">24j</div>
                    <template x-for="day in days" :key="day.key">
                        <div class="wcal-dh" :class="{ 'is-today': day.isToday, 'is-closed': day.closed }">
                            <span class="wcal-dh-name" x-text="day.name"></span>
                            <span class="wcal-dh-num" x-text="day.dateNum"></span>
                        </div>
                    </template>
                </div>

                {{-- Canvas --}}
                <div class="wcal-canvas" :class="{ 'wcal-booking-mode': bookingMode }" :style="{ height: canvasH + 'px' }">

                    {{-- Hour gutter --}}
                    <div class="wcal-gutter">
                        <template x-for="h in hours" :key="h">
                            <div class="wcal-hour" :style="{ top: ((h - rangeStart) * hourH) + 'px' }"
                                 x-show="h !== rangeStart" x-text="fmtHour(h)"></div>
                        </template>
                    </div>

                    {{-- Day columns --}}
                    <template x-for="day in days" :key="day.key">
                        <div class="wcal-col"
                             :class="{ 'is-closed': day.closed, 'is-past': day.past && !day.closed, 'is-bookable': day.bookable }"
                             @mousedown="onColMouseDown($event, day)">

                            {{-- live drag block --}}
                            <template x-if="block && block.key === day.key">
                                <div class="wcal-block" :style="blockStyle()">
                                    <span class="wcal-block-label" x-text="blockLabel()"></span>
                                </div>
                            </template>

                            {{-- events + rollups --}}
                            <template x-for="item in layoutDay(day.key)" :key="item.k">
                                <div class="wcal-ev" :class="evClasses(item)" :style="evStyle(item)"
                                     @mousedown.stop @click.stop="onItemClick(item, $event)">
                                    <template x-if="item.kind === 'event'">
                                        <div>
                                            <div class="wcal-ev-row">
                                                <span class="wcal-ev-badge" :style="{ background: typeOf(item.ev).fg }" x-text="typeOf(item.ev).badge"></span>
                                                <span class="wcal-ev-title" x-text="item.ev.label"></span>
                                            </div>
                                            <div class="wcal-ev-sub" x-text="item.ev.who"></div>
                                            <div class="wcal-ev-time" x-text="timeRange(item.ev.start, item.ev.start + item.ev.dur)"></div>
                                        </div>
                                    </template>
                                    <template x-if="item.kind === 'rollup'">
                                        <div>
                                            <div class="wcal-rollup-count" x-text="'+' + item.items.length"></div>
                                            <div class="wcal-ev-title" x-text="rollupLabel(item)"></div>
                                            <div class="wcal-ev-time" x-text="timeRange(item.start, item.start + item.dur)"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- now line --}}
                    <template x-if="nowVisible">
                        <div class="wcal-now" :style="{ top: nowTop + 'px' }">
                            <span class="wcal-now-flag" x-text="fmtMin(nowMin)"></span>
                        </div>
                    </template>

                </div>
            </div>
        </div>

        {{-- ── Create popover (Step 1) ── --}}
        <template x-if="creating">
            <div>
                {{-- Step 1: Popover Form is hidden while showFaceModal is true --}}
                <div x-show="!showFaceModal">
                    <div class="wcal-overlay" @mousedown="closeCreating()"></div>
                    <div class="wcal-pop wide" :style="popStyle(creating.pos)">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                            <h3 style="margin:0;">Reservasi Baru</h3>
                            <button type="button" @click="closeCreating()" style="border:0; background:transparent; cursor:pointer; color:rgba(10,26,71,0.4); padding:2px;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                    <label class="wcal-lbl">Tanggal</label>
                    <div class="wcal-datefield">
                        <svg width="15" height="15" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                        <span x-text="creating.dateLabel"></span>
                    </div>

                    <div class="wcal-row">
                        <div>
                            <label class="wcal-lbl">Waktu Mulai</label>
                            <select class="wcal-select" x-ref="startSel" x-model.number="creating.start" @change="onStartChange()">
                                <template x-for="m in startOptions" :key="m">
                                    <option :value="m" x-text="fmtMin(m)"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="wcal-lbl">Waktu Selesai</label>
                            <select class="wcal-select" x-ref="endSel" x-model.number="creating.end">
                                <template x-for="m in endOptions" :key="m">
                                    <option :value="m" x-text="fmtMin(m)"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- restriction banners --}}
                    <template x-if="creating.hardBlocked">
                        <div class="wcal-banner red">
                            <svg width="14" height="14" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span>Slot ini sudah penuh (ruangan dipesan penuh / eksklusif). Pilih waktu lain.</span>
                        </div>
                    </template>
                    <template x-if="!creating.hardBlocked && creating.sharedRoom">
                        <div class="wcal-banner teal">
                            <svg width="14" height="14" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <span>Ruangan sedang dipakai berbagi. Hanya <b>Komputer</b> yang dapat dipesan.</span>
                        </div>
                    </template>
                    <template x-if="!creating.hardBlocked && !creating.sharedRoom && creating.computerBooked">
                        <div class="wcal-banner amber">
                            <svg width="14" height="14" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span>Sebagian komputer sudah dipesan. <b>Ruang + Komputer</b> dan mode <b>Eksklusif</b> tidak tersedia.</span>
                        </div>
                    </template>

                    <label class="wcal-lbl">Tipe Reservasi</label>
                    <div class="wcal-typelist">
                        <template x-for="o in loanOpts" :key="o.key">
                            <button class="wcal-typeopt" type="button"
                                    :class="{ on: creating.loanType === o.key }"
                                    :style="creating.loanType === o.key ? { borderColor: o.fg, background: o.soft } : {}"
                                    :disabled="typeDisabled(o.key)"
                                    @click="selectLoan(o.key)">
                                <span class="wcal-typesw" :style="{ background: o.fg }"></span>
                                <span class="wcal-typename" x-text="o.name"></span>
                                <span class="wcal-typebadge" :style="{ background: o.fg }" x-text="o.badge"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Room only → sharing mode --}}
                    <template x-if="creating.loanType === 'room_only'">
                        <div>
                            <label class="wcal-lbl">Penggunaan Ruang</label>
                            <div class="wcal-submode">
                                <button type="button" :class="{ on: creating.roomMode === 'shared' }" @click="creating.roomMode = 'shared'">Berbagi</button>
                                <button type="button" :class="{ on: creating.roomMode === 'exclusive' }" :disabled="roomModeDisabled('exclusive')" @click="!roomModeDisabled('exclusive') && (creating.roomMode = 'exclusive')">Eksklusif</button>
                            </div>
                        </div>
                    </template>

                    {{-- Computer only → single unit dropdown --}}
                    <template x-if="creating.loanType === 'computer'">
                        <div>
                            <label class="wcal-lbl">Unit Komputer</label>
                            <template x-if="pcLoading"><div class="wcal-pc-note">Memuat ketersediaan…</div></template>
                            <template x-if="pcError"><div class="wcal-pc-note err">Gagal memuat ketersediaan. Ubah waktu untuk mencoba lagi.</div></template>
                            <template x-if="!pcLoading && !pcError">
                                <select class="wcal-select" x-model.number="selectedPc">
                                    <option value="">Pilih unit…</option>
                                    <template x-for="pc in pcList" :key="pc.id">
                                        <option :value="pc.id" :disabled="!pc.available"
                                                x-text="pc.label + (pc.available ? (pc.pending ? ' · menunggu' : '') : (pc.status !== 'online' ? ' · perawatan' : ' · terpakai'))"></option>
                                    </template>
                                </select>
                            </template>
                        </div>
                    </template>

                    {{-- Room + Computer → multi-unit checkboxes --}}
                    <template x-if="creating.loanType === 'room_computer'">
                        <div>
                            <label class="wcal-lbl">Unit Komputer (boleh lebih dari satu)</label>
                            <template x-if="pcLoading"><div class="wcal-pc-note">Memuat ketersediaan…</div></template>
                            <template x-if="pcError"><div class="wcal-pc-note err">Gagal memuat ketersediaan. Ubah waktu untuk mencoba lagi.</div></template>
                            <template x-if="!pcLoading && !pcError">
                                <div class="wcal-pcgrid">
                                    <template x-for="pc in pcList" :key="pc.id">
                                        <button type="button" class="wcal-pcbox"
                                                :class="{ on: selectedPcs.includes(pc.id), off: !pc.available }"
                                                :disabled="!pc.available"
                                                @click="togglePc(pc.id, pc.available)">
                                            <span class="wcal-pcbox-label" x-text="pc.label"></span>
                                            <span class="wcal-pcbox-status"
                                                  x-text="pc.available ? (pc.pending ? 'menunggu' : 'tersedia') : (pc.status !== 'online' ? 'perawatan' : 'terpakai')"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <label class="wcal-lbl">Kategori</label>
                    <select class="wcal-select" x-model="category">
                        <option value="penelitian">Penelitian</option>
                        <option value="tugas_akhir">Tugas Akhir</option>
                        <option value="project_akademik">Project Akademik</option>
                        <option value="praktikum">Praktikum</option>
                        <option value="lainnya" selected>Lainnya</option>
                    </select>

                    <label class="wcal-lbl">Alasan / Tujuan</label>
                    <input type="text" class="wcal-input" x-model="reason" maxlength="1000"
                           placeholder="Tujuan peminjaman…" @keydown.enter.prevent="submitBooking()">

                    <label class="wcal-lbl">Kebutuhan Tambahan</label>
                    <div style="display:flex; flex-direction:column; gap:7px; margin-bottom:14px;">
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#0A1A47; cursor:pointer;">
                            <input type="checkbox" x-model="needsInternet" style="accent-color:#4f46e5; width:15px; height:15px;">
                            <span>Akses Internet</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#0A1A47; cursor:pointer;">
                            <input type="checkbox" x-model="needsInstallation" style="accent-color:#4f46e5; width:15px; height:15px;">
                            <span>Instalasi Software</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#0A1A47; cursor:pointer;">
                            <input type="checkbox" x-model="hasExternalDevices" style="accent-color:#4f46e5; width:15px; height:15px;">
                            <span>Perangkat Eksternal</span>
                        </label>
                    </div>

                    {{-- Face Recognition / Approval Method Selection --}}
                    <label class="wcal-lbl">Metode Persetujuan</label>
                    <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:14px;">
                        <label style="display:flex; align-items:flex-start; gap:10px; padding:9px 12px; border-radius:10px; border:1.5px solid; cursor:pointer; transition:all .15s;"
                               :style="reservationMode === 'manual' ? 'border-color:#4f46e5; background:rgba(79,70,229,0.05);' : 'border-color:rgba(15,36,96,0.12); background:#fff;'">
                            <input type="radio" name="_res_mode" value="manual" x-model="reservationMode" style="accent-color:#4f46e5; margin-top:2px;">
                            <div style="flex:1;">
                                <div style="font-size:12.5px; font-weight:700; color:#0A1A47;">Reservasi Biasa (Pending Admin)</div>
                                <div style="font-size:11px; color:rgba(10,26,71,0.55); margin-top:1px;">Membutuhkan tinjauan dan persetujuan manual oleh admin lab.</div>
                            </div>
                        </label>

                        <label style="display:flex; align-items:flex-start; gap:10px; padding:9px 12px; border-radius:10px; border:1.5px solid; cursor:pointer; transition:all .15s;"
                               :style="reservationMode === 'instant_face' ? 'border-color:#10b981; background:rgba(16,185,129,0.06);' : 'border-color:rgba(15,36,96,0.12); background:#fff;'">
                            <input type="radio" name="_res_mode" value="instant_face" x-model="reservationMode" style="accent-color:#10b981; margin-top:2px;">
                            <div style="flex:1;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <span style="font-size:12.5px; font-weight:700; color:#0A1A47;">Reservasi Instan (Verifikasi Wajah)</span>
                                    <span style="font-size:9.5px; font-weight:800; background:#10b981; color:#fff; padding:1px 5px; border-radius:4px; text-transform:uppercase;">Auto-Approved</span>
                                </div>
                                <div style="font-size:11px; color:rgba(10,26,71,0.55); margin-top:1px;">Pindai wajah via webcam untuk persetujuan otomatis seketika.</div>

                                <template x-if="!userHasFaceRegistered">
                                    <div style="margin-top:6px; font-size:11px; color:#d97706; background:#fffbeb; padding:5px 8px; border-radius:6px; border:1px solid #fde68a;">
                                        ⚠️ Anda belum mendaftarkan sampel wajah. <a href="{{ route('profile.face-registration') }}" target="_blank" style="font-weight:700; text-decoration:underline; color:#b45309;">Daftarkan Wajah</a>
                                    </div>
                                </template>
                            </div>
                        </label>
                    </div>

                    {{-- Hidden form — submitted natively on "Konfirmasi". Values mirror the popover state. --}}
                    <form x-ref="calForm" method="POST" action="{{ route('calendar.booking.store') }}" class="wcal-hidden-form">
                        @csrf
                        <input type="hidden" name="booking_type" :value="backendType">
                        <input type="hidden" name="room_sharing" :value="creating.loanType === 'room_only' ? creating.roomMode : ''">
                        <input type="hidden" name="date" :value="creating.dateKey">
                        <input type="hidden" name="start_time" :value="fmtMin(creating.start)">
                        <input type="hidden" name="end_time" :value="fmtMin(creating.end)">
                        <input type="hidden" name="reason" :value="reason">
                        <input type="hidden" name="category" :value="category">
                        <input type="hidden" name="needs_internet" :value="needsInternet ? '1' : '0'">
                        <input type="hidden" name="needs_installation" :value="needsInstallation ? '1' : '0'">
                        <input type="hidden" name="external_devices" :value="hasExternalDevices ? '1' : '0'">
                        <input type="hidden" name="reservation_mode" :value="reservationMode">
                        <input type="hidden" name="is_face_verified" :value="isFaceVerified ? '1' : '0'">
                        <input type="hidden" name="face_embedding" :value="verifiedEmbedding ? JSON.stringify(verifiedEmbedding) : ''">
                        <template x-for="id in computersPayload" :key="id">
                            <input type="hidden" name="computers[]" :value="id">
                        </template>
                    </form>

                    <div class="wcal-actions">
                        <button class="wcal-btn" type="button" @click="closeCreating()">Batal</button>
                        <button class="wcal-btn primary" type="button" :disabled="!canConfirm" @click="submitBooking()">
                            <span x-text="reservationMode === 'instant_face' && !isFaceVerified ? 'Lanjut: Verifikasi Wajah →' : 'Konfirmasi Reservasi'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ── Step 2: Face Verification Modal (Multi-step flow) ── --}}
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

            {{-- Dark Overlay Backdrop --}}
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

                    {{-- Step Header --}}
                    <div class="flex items-center justify-between pb-3.5 border-b border-rule text-left">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-mark-500/20 text-ink-900 border border-mark-500/30 mb-1">
                                <span class="w-1.5 h-1.5 rounded-full" :class="isCameraLoading ? 'bg-amber-500 animate-ping' : 'bg-emerald-500 animate-pulse'"></span>
                                Langkah 2 dari 2: Verifikasi
                            </div>
                            <h3 class="text-base font-bold text-ink-900">Pemindaian Wajah Pemesan</h3>
                        </div>
                        <button type="button" @click="closeFaceModal()" class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-300 transition-colors cursor-pointer" title="Tutup verifikasi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Reservation summary reminder --}}
                    <template x-if="creating">
                        <div class="mt-3 py-1.5 px-3 rounded-lg bg-ink-50 border border-rule text-xs text-ink-700/80 flex items-center justify-between">
                            <span class="font-medium truncate" x-text="creating.dateLabel + ' · ' + fmtMin(creating.start) + '–' + fmtMin(creating.end)"></span>
                            <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded border border-emerald-200">Auto-Approved</span>
                        </div>
                    </template>

                    {{-- Camera Viewport --}}
                    <div class="face-camera-viewport mt-3.5 relative rounded-xl overflow-hidden bg-ink-900 aspect-video flex items-center justify-center shadow-inner">
                        <video id="modalFaceVideo" x-ref="modalVideo" class="face-camera-video w-full h-full object-cover" style="transform: scaleX(-1);" playsinline autoplay muted></video>
                        <canvas id="modalFaceCanvas" x-ref="modalCanvas" class="face-camera-canvas absolute inset-0 w-full h-full pointer-events-none" style="transform: scaleX(-1);"></canvas>
                        
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
                            <span class="text-[11px] text-white/60 mt-1">Sedang menghubungkan ke perangkat webcam...</span>
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
                            <div class="text-[11px] text-white/70 mt-1">Menyetujui reservasi secara otomatis...</div>
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
                    {{-- Multi-step Actions (Never disabled during loading) --}}
                    <div class="mt-4 pt-3 border-t border-slate-200 flex flex-col sm:flex-row items-center gap-2">
                        <button type="button" @click="closeFaceModal()"
                                class="w-full sm:w-auto flex-1 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Kembali ke Formulir</span>
                        </button>
                        <button type="button" @click="continueWithoutFace()"
                                class="w-full sm:w-auto py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 text-xs font-semibold transition-colors cursor-pointer">
                            Reservasi Biasa Saja
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

        {{-- ── Details popover ── --}}
        <template x-if="details">
            <div>
                <div class="wcal-overlay" @mousedown="closeDetails()"></div>
                <div class="wcal-pop" :style="popStyle(details.pos)">
                    <div class="wcal-det-head">
                        <span class="wcal-det-sw" :style="{ background: typeOf(details.ev).fg }"></span>
                        <div>
                            <div class="wcal-det-title" x-text="details.ev.label"></div>
                            <span class="wcal-det-tag" :style="{ background: typeOf(details.ev).bg, color: typeOf(details.ev).fg }"
                                  x-text="typeOf(details.ev).label + ' · ' + statusLabel(details.ev.status)"></span>
                        </div>
                    </div>
                    <div class="wcal-det-row">
                        <svg width="15" height="15" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span x-text="details.ev.who + (details.ev.is_mine ? ' (Anda)' : '')"></span>
                    </div>
                    <div class="wcal-det-row">
                        <svg width="15" height="15" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
                        <span x-text="timeRange(details.ev.start, details.ev.start + details.ev.dur)"></span>
                    </div>
                    <div class="wcal-det-row">
                        <svg width="15" height="15" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                        <span x-text="details.dateLabel"></span>
                    </div>
                    <div class="wcal-det-row">
                        <svg width="15" height="15" viewBox="0 0 24 24"><path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z"/><circle cx="7" cy="7" r="1.2" fill="currentColor"/></svg>
                        <span class="font-mono" x-text="details.ev.booking_code"></span>
                    </div>

                    <div class="wcal-actions" style="margin-top:14px">
                        <template x-if="details.ev.is_mine">
                            <form :action="cancelUrl(details.ev.id)" method="POST"
                                  onsubmit="return confirm('Batalkan reservasi ini?')">
                                @csrf
                                <button class="wcal-btn danger" type="submit">Batalkan</button>
                            </form>
                        </template>
                        <button class="wcal-btn" type="button" @click="closeDetails()">Tutup</button>
                        <template x-if="details.ev.is_mine">
                            <a class="wcal-btn primary" :href="showUrl(details.ev.id)">Detail</a>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- ── Group (rollup) popover ── --}}
        <template x-if="groupPop">
            <div>
                <div class="wcal-overlay" @mousedown="groupPop = null"></div>
                <div class="wcal-pop" :style="popStyle(groupPop.pos)">
                    <h3 style="margin-bottom:4px" x-text="groupPop.items.length + ' reservasi pada slot ini'"></h3>
                    <div class="wcal-grp-sub" x-text="timeRange(groupPop.start, groupPop.start + groupPop.dur)"></div>
                    <div class="wcal-grp-list">
                        <template x-for="ev in groupPop.sorted" :key="ev.id">
                            <div class="wcal-grp-row" @click="openDetailsFromGroup(ev, $event)">
                                <span class="wcal-det-sw" :style="{ background: typeOf(ev).fg, marginTop: '3px' }"></span>
                                <div class="wcal-grp-main">
                                    <div class="wcal-grp-title" x-text="ev.label"></div>
                                    <div class="wcal-grp-meta" x-text="ev.who + ' · ' + timeRange(ev.start, ev.start + ev.dur)"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="wcal-actions" style="margin-top:12px">
                        <button class="wcal-btn" type="button" @click="groupPop = null">Tutup</button>
                    </div>
                </div>
            </div>
        </template>

    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<x-face-autocapture />
<script>
const CAL_EVENTS   = @json($calendarEvents);
const TODAY_ISO    = @json($todayIso);
const LOAD_START   = @json($loadStartIso);
const LOAD_END     = @json($loadEndIso);
const PC_AVAIL_URL = @json(route('api.availability.computers'));
const BOOKING_BASE = @json(url('/booking'));
const USER_HAS_FACE_REGISTERED = @json($userHasFaceRegistered);
const FACE_VERIFY_URL = @json(route('booking.verify-face'));
const FACE_MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

const MAX_COLS = 4;

const BOOKING_TYPES = {
    computer:       { label:'Komputer',         badge:'Komputer',  fg:'#4f46e5', bg:'#eef0fe' },
    room_computer:  { label:'Ruang + Komputer', badge:'Ruang+PC',  fg:'#7c3aed', bg:'#f1ebfe' },
    room_exclusive: { label:'Ruang Eksklusif',  badge:'Eksklusif', fg:'#0d9488', bg:'#e3f4f1' },
    room_sharing:   { label:'Ruang Berbagi',    badge:'Berbagi',   fg:'#d97706', bg:'#fcf0dd' },
};
const LOAN_OPTS = [
    { key:'computer',      name:'Komputer Saja',    badge:'Komputer',  fg:'#4f46e5', soft:'rgba(79,70,229,.08)' },
    { key:'room_computer', name:'Ruang + Komputer', badge:'Ruang+PC',  fg:'#7c3aed', soft:'rgba(124,58,237,.08)' },
    { key:'room_only',     name:'Ruang Saja',       badge:'Ruang',     fg:'#0d9488', soft:'rgba(13,148,136,.08)' },
];
const STATUS_ID = { submitted:'Menunggu', under_review:'Ditinjau', approved:'Disetujui', completed:'Selesai', rejected:'Ditolak', cancelled:'Dibatalkan' };

const DAY_SHORT = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
const DAY_FULL  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
const MONTHS    = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const MONTHS_SH = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

// ── date helpers ──
function startOfDay(d) { const x = new Date(d); x.setHours(0,0,0,0); return x; }
function addDays(d, n) { const x = new Date(d); x.setDate(x.getDate() + n); return x; }
function dateKey(d) { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
function mondayOf(d) { const x = startOfDay(d); const off = (x.getDay() + 6) % 7; return addDays(x, -off); }
function snap(mins, step) { return Math.round(mins / step) * step; }
function fmtMin(mins) { return `${String(Math.floor(mins/60)).padStart(2,'0')}:${String(mins%60).padStart(2,'0')}`; }

function weekCal() {
    return {
        events: CAL_EVENTS,
        loanOpts: LOAN_OPTS,
        rangeStart: 8,
        rangeEnd: 22,
        hourH: 56,

        anchor: startOfDay(new Date(TODAY_ISO + 'T00:00:00')),
        view: 'week',
        now: new Date(),

        creating: null,
        details: null,
        groupPop: null,
        block: null,
        selectedEvId: null,
        bookingMode: false,   // false = view (detail cards) · true = drag-to-book (bare blocks)

        // ── inline booking form state (popover) ──
        reason: '',
        category: 'lainnya',
        needsInternet: false,
        needsInstallation: false,
        hasExternalDevices: false,
        pcList: [],
        pcLoading: false,
        pcError: false,
        selectedPc: null,    // single PC id (Computer only)
        selectedPcs: [],     // multiple PC ids (Room + Computer)

        // ── Face recognition state ──
        userHasFaceRegistered: USER_HAS_FACE_REGISTERED,
        reservationMode: USER_HAS_FACE_REGISTERED ? 'instant_face' : 'manual',
        isFaceVerified: false,
        verifiedEmbedding: null,
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

        init() {
            this._tick = setInterval(() => { this.now = new Date(); }, 30000);
        },

        // ── computed ──
        get days() {
            const out = [];
            if (this.view === 'day') {
                out.push(this.mkDay(this.anchor));
            } else {
                const mon = mondayOf(this.anchor);
                for (let i = 0; i < 6; i++) out.push(this.mkDay(addDays(mon, i))); // Mon–Sat
            }
            return out;
        },
        get ndays() { return this.days.length; },
        get canvasH() { return (this.rangeEnd - this.rangeStart) * this.hourH; },
        get hours() { const a = []; for (let h = this.rangeStart; h <= this.rangeEnd; h++) a.push(h); return a; },
        get nowMin() { return this.now.getHours() * 60 + this.now.getMinutes(); },
        get nowVisible() {
            return this.days.some(d => d.key === TODAY_ISO) &&
                   this.nowMin >= this.rangeStart * 60 && this.nowMin <= this.rangeEnd * 60;
        },
        get nowTop() { return ((this.nowMin - this.rangeStart * 60) / 60) * this.hourH; },
        get periodLabel() {
            const ds = this.days, f = ds[0].dateObj, l = ds[ds.length - 1].dateObj;
            if (this.view === 'day') return `${DAY_FULL[f.getDay()]}, ${f.getDate()} ${MONTHS[f.getMonth()]} ${f.getFullYear()}`;
            if (f.getMonth() === l.getMonth()) return `${MONTHS[f.getMonth()]} ${f.getFullYear()}`;
            return `${MONTHS_SH[f.getMonth()]} – ${MONTHS_SH[l.getMonth()]} ${f.getFullYear()}`;
        },
        get periodSub() {
            if (this.view === 'day') return '';
            const ds = this.days, f = ds[0].dateObj, l = ds[ds.length - 1].dateObj;
            return `${f.getDate()}–${l.getDate()}`;
        },
        get startOptions() { const a = []; for (let m = this.rangeStart*60; m < this.rangeEnd*60; m += 30) a.push(m); return a; },
        get endOptions() {
            const a = []; const from = this.creating ? this.creating.start : this.rangeStart*60;
            for (let m = from + 30; m <= this.rangeEnd*60; m += 30) a.push(m); return a;
        },

        mkDay(d) {
            const key = dateKey(d);
            const closed = d.getDay() === 0; // Sunday
            const past = key < TODAY_ISO;
            return {
                key, name: DAY_SHORT[d.getDay()], dateNum: d.getDate(),
                isToday: key === TODAY_ISO, closed, past,
                bookable: !closed && !past,
                dateObj: new Date(d),
            };
        },

        // ── concurrent layout ──
        layoutDay(dayKey) {
            const evs = this.events.filter(e => e.date === dayKey);
            const sorted = [...evs].sort((a, b) => a.start - b.start || b.dur - a.dur || a.id - b.id);
            const clusters = []; let cur = [], curEnd = -1;
            for (const e of sorted) {
                if (cur.length && e.start >= curEnd) { clusters.push(cur); cur = []; curEnd = -1; }
                cur.push(e); curEnd = Math.max(curEnd, e.start + e.dur);
            }
            if (cur.length) clusters.push(cur);

            const items = [];
            for (const cluster of clusters) {
                // Greedy column assignment — tracked locally (never mutate the reactive event objects).
                const placed = [], colEnds = [];
                for (const e of cluster) {
                    let col = -1;
                    for (let i = 0; i < colEnds.length; i++) {
                        if (colEnds[i] <= e.start) { colEnds[i] = e.start + e.dur; col = i; break; }
                    }
                    if (col === -1) { col = colEnds.length; colEnds.push(e.start + e.dur); }
                    placed.push({ ev: e, col });
                }
                const ncols = colEnds.length;
                if (ncols <= MAX_COLS) {
                    const w = 1 / ncols;
                    for (const p of placed) items.push({ kind:'event', k:'e'+p.ev.id, ev:p.ev, start:p.ev.start, dur:p.ev.dur, left:p.col*w, width:w });
                } else {
                    const keep = MAX_COLS - 1, w = 1 / MAX_COLS, bucket = [];
                    for (const p of placed) {
                        if (p.col < keep) items.push({ kind:'event', k:'e'+p.ev.id, ev:p.ev, start:p.ev.start, dur:p.ev.dur, left:p.col*w, width:w });
                        else bucket.push(p.ev);
                    }
                    if (bucket.length) {
                        const start = Math.min(...bucket.map(e => e.start));
                        const end = Math.max(...bucket.map(e => e.start + e.dur));
                        items.push({ kind:'rollup', k:'r'+dayKey+'-'+start, items:bucket, start, dur:end-start, left:keep*w, width:w });
                    }
                }
            }
            return items;
        },

        // ── geometry ──
        evStyle(item) {
            const top = ((item.start - this.rangeStart * 60) / 60) * this.hourH;
            const height = Math.max((item.dur / 60) * this.hourH - 2, 16);
            const style = {
                top: top + 'px', height: height + 'px',
                left: `calc(${(item.left * 100).toFixed(3)}% + 3px)`,
                width: `calc(${(item.width * 100).toFixed(3)}% - 4px)`,
            };
            if (item.kind === 'event') {
                const t = this.typeOf(item.ev);
                style['--ev'] = t.fg; style['--ev-bg'] = t.bg; style['--ev-fg'] = t.fg;
            }
            return style;
        },
        evClasses(item) {
            const h = (item.dur / 60) * this.hourH;
            if (item.kind === 'rollup') return { 'wcal-rollup': true, 'wcal-compact': h < 42, 'wcal-narrow': item.width < 0.34 };
            const ev = item.ev;
            return {
                'is-selected': this.selectedEvId === ev.id,
                'is-mine': ev.is_mine,
                'is-pending': ev.status !== 'approved',
                'wcal-compact': h < 42,
                'wcal-narrow': item.width < 0.34,
            };
        },
        blockStyle() {
            const b = this.block;
            return {
                top: ((b.start - this.rangeStart * 60) / 60) * this.hourH + 'px',
                height: Math.max(((b.end - b.start) / 60) * this.hourH, 2) + 'px',
            };
        },
        blockLabel() { return `${fmtMin(this.block.start)} – ${fmtMin(this.block.end)}`; },

        // ── formatting ──
        typeOf(ev) { return BOOKING_TYPES[ev.type] || BOOKING_TYPES.computer; },
        timeRange(s, e) { return `${fmtMin(s)} – ${fmtMin(e)}`; },
        fmtMin(m) { return fmtMin(m); },
        fmtHour(h) { return fmtMin(h * 60); },
        statusLabel(s) { return STATUS_ID[s] || s; },
        rollupLabel(item) {
            if (item.width < 0.34) return 'lainnya';
            const allPc = item.items.every(e => e.type === 'computer');
            return '+' + item.items.length + (allPc ? ' komputer' : ' reservasi');
        },
        dateLabelOf(dateKeyStr) {
            const d = new Date(dateKeyStr + 'T00:00:00');
            return `${DAY_FULL[d.getDay()]}, ${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
        },

        // ── navigation ──
        canGo(dir) {
            const step = this.view === 'day' ? 1 : 7;
            const next = addDays(this.anchor, dir * step);
            return next >= new Date(LOAD_START + 'T00:00:00') && next <= new Date(LOAD_END + 'T00:00:00');
        },
        go(dir) {
            if (!this.canGo(dir)) return;
            const step = this.view === 'day' ? 1 : 7;
            this.anchor = addDays(this.anchor, dir * step);
            this.closeAll();
        },
        goToday() { this.anchor = startOfDay(new Date(TODAY_ISO + 'T00:00:00')); this.closeAll(); },
        setView(v) { this.view = v; this.closeAll(); },

        // ── drag to create ──
        onColMouseDown(e, day) {
            if (e.button !== 0 || !day.bookable) return;
            const r = e.currentTarget.getBoundingClientRect();
            const calc = (cy) => {
                let m = snap(this.rangeStart * 60 + ((cy - r.top) / this.hourH) * 60, 30);
                return Math.max(this.rangeStart * 60, Math.min(m, this.rangeEnd * 60));
            };
            const anchorMin = calc(e.clientY);
            this.closeAll();
            this.block = { key: day.key, start: anchorMin, end: Math.min(anchorMin + 30, this.rangeEnd * 60), moved: false };

            const onMove = (ev) => {
                const m = calc(ev.clientY);
                let s = Math.min(anchorMin, m), en = Math.max(anchorMin, m);
                if (en <= s) en = Math.min(s + 30, this.rangeEnd * 60);
                this.block = { key: day.key, start: s, end: en, moved: true };
            };
            const onUp = (ev) => {
                window.removeEventListener('mousemove', onMove);
                window.removeEventListener('mouseup', onUp);
                const b = this.block; this.block = null;
                if (!b) return;
                let s = b.start, en = b.end;
                if (!b.moved) { en = Math.min(s + 60, this.rangeEnd * 60); if (en <= s) s = Math.max(this.rangeStart * 60, en - 60); }
                this.openCreate(day, s, en, { x: ev.clientX, y: ev.clientY });
            };
            window.addEventListener('mousemove', onMove);
            window.addEventListener('mouseup', onUp);
        },

        enterBookingMode() {
            this.closeAll();         // clear any open detail/group/create popover + selection + block
            this.bookingMode = true;
        },
        exitBookingMode() {
            this.closeAll();
            this.bookingMode = false;
        },

        // ── slot restrictions (mirrors EC-C/EC-H) ──
        slotRestrictions(dateKey, start, end) {
            const ov = this.events.filter(e => e.date === dateKey && e.start < end && (e.start + e.dur) > start);
            let hard = false, shared = false, comp = false;
            for (const e of ov) {
                if (e.type === 'room_computer' || e.type === 'room_exclusive') hard = true;
                if (e.type === 'room_sharing') shared = true;
                if (e.type === 'computer') comp = true;
            }
            return { hardBlocked: hard, sharedRoom: shared && !hard, computerBooked: comp && !hard };
        },

        // ── popovers ──
        popPos(anchorPt, w, h) {
            const pad = 12;
            let left = anchorPt.x + 12;
            if (left + w > window.innerWidth - pad) left = anchorPt.x - w - 12;
            if (left < pad) left = pad;
            let top = anchorPt.y;
            if (top + h > window.innerHeight - pad) top = window.innerHeight - h - pad;
            if (top < pad) top = pad;
            return { left, top };
        },
        // Position + cap the card height to the space left below its top, so a tall form
        // never runs past the viewport bottom — it scrolls internally and the action
        // buttons stay reachable without zooming.
        popStyle(pos) {
            return {
                left: pos.left + 'px',
                top: pos.top + 'px',
                maxHeight: 'calc(100vh - ' + (pos.top + 12) + 'px)',
            };
        },
        rectAnchor(el) { const r = el.getBoundingClientRect(); return { x: r.right, y: r.top }; },

        openCreate(day, start, end, anchorPt) {
            const r = this.slotRestrictions(day.key, start, end);
            // shared-room slots only allow Computer; computer-booked slots forbid Room+Computer.
            const loanType = 'computer';
            this.details = null; this.groupPop = null;
            this.reason = '';
            this.category = 'lainnya';
            this.needsInternet = false;
            this.needsInstallation = false;
            this.hasExternalDevices = false;
            this.selectedPc = null;
            this.selectedPcs = [];
            this.pcList = [];
            this.pcError = false;
            this.creating = {
                dateKey: day.key, dateLabel: this.dateLabelOf(day.key),
                start, end,
                hardBlocked: r.hardBlocked, sharedRoom: r.sharedRoom, computerBooked: r.computerBooked,
                loanType, roomMode: r.computerBooked ? 'shared' : 'exclusive',
                pos: this.popPos(anchorPt, 330, 540),
            };
            this.fetchPcAvail();
            this.syncTimeSelects();
        },
        onStartChange() {
            if (this.creating.end <= this.creating.start) {
                this.creating.end = Math.min(this.creating.start + 30, this.rangeEnd * 60);
            }
            this.fetchPcAvail();
            this.syncTimeSelects();
        },
        // Force the <select>s to reflect creating.start/end after their <option>s render.
        // x-model applies its value during the select's own init — which, for a select whose
        // options come from a nested x-for, happens BEFORE the options exist, so the value
        // fails to stick and the box shows the first option. Re-assigning on the next tick
        // (options now present) keeps the dropdown in sync with the dragged slot.
        syncTimeSelects() {
            this.$nextTick(() => {
                if (!this.creating) return;
                if (this.$refs.startSel) this.$refs.startSel.value = String(this.creating.start);
                if (this.$refs.endSel)   this.$refs.endSel.value   = String(this.creating.end);
            });
        },

        get backendType() {
            if (!this.creating) return '';
            return { computer:'computers_only', room_computer:'full_room', room_only:'room_only' }[this.creating.loanType] || '';
        },
        get computersPayload() {
            const c = this.creating; if (!c) return [];
            if (c.loanType === 'computer') return this.selectedPc ? [this.selectedPc] : [];
            if (c.loanType === 'room_computer') return this.selectedPcs;
            return [];
        },
        get canConfirm() {
            const c = this.creating; if (!c || c.hardBlocked) return false;
            if (c.end <= c.start) return false;
            if (!this.reason || this.reason.trim().length < 3) return false;
            if (c.loanType === 'computer' && !this.selectedPc) return false;
            if (c.loanType === 'room_computer' && this.selectedPcs.length < 1) return false;
            if (c.loanType === 'room_only' && !c.roomMode) return false;
            return true;
        },
        togglePc(id, available) {
            if (!available) return;
            const i = this.selectedPcs.indexOf(id);
            if (i === -1) this.selectedPcs.push(id);
            else this.selectedPcs.splice(i, 1);
        },

        async fetchPcAvail() {
            const c = this.creating; if (!c) return;
            if (!c.dateKey || c.end <= c.start) return;
            this.pcLoading = true; this.pcError = false;
            const p = new URLSearchParams({ date: c.dateKey, start_time: fmtMin(c.start), end_time: fmtMin(c.end) });
            try {
                const res = await fetch(PC_AVAIL_URL + '?' + p.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('http ' + res.status);
                const data = await res.json();
                this.pcList = data.computers || [];
                // prune selections that are no longer available
                if (this.selectedPc) {
                    const pc = this.pcList.find(x => x.id === this.selectedPc);
                    if (!pc || !pc.available) this.selectedPc = null;
                }
                this.selectedPcs = this.selectedPcs.filter(id => {
                    const pc = this.pcList.find(x => x.id === id);
                    return pc && pc.available;
                });
            } catch (e) {
                this.pcError = true; this.pcList = [];
            } finally {
                this.pcLoading = false;
            }
        },

        submitBooking() {
            if (!this.canConfirm) return;
            if (this.reservationMode === 'instant_face' && !this.isFaceVerified) {
                if (!this.userHasFaceRegistered) {
                    alert('Anda belum mendaftarkan sampel wajah. Silakan daftarkan wajah terlebih dahulu melalui menu Pendaftaran Wajah.');
                    return;
                }
                this.openFaceModal();
                return;
            }
            this.$refs.calForm.submit();
        },

        getModalVideoElement() {
            return this.$refs.modalVideo || document.getElementById('modalFaceVideo') || document.querySelector('.face-camera-video');
        },

        getModalCanvasElement() {
            return this.$refs.modalCanvas || document.getElementById('modalFaceCanvas') || document.querySelector('.face-camera-canvas');
        },

        openFaceModal() {
            this.showFaceModal = true;
            this.isCameraLoading = true;
            this.modalError = false;
            this.modalErrorMessage = '';
            this.faceVerifiedSuccess = false;
            this.modalFaceDetected = false;
            this.faceScanStatus = 'Mempersiapkan kamera...';

            const sessionId = ++this.cameraSessionId;
            this.$nextTick(() => {
                this.startFaceDetection(sessionId);
            });
        },

        closeFaceModal() {
            this.cameraSessionId++;
            this.isCameraLoading = false;
            this.stopFaceCamera();
            this.showFaceModal = false;
        },

        stopFaceCamera() {
            if (this.faceAnimId) {
                cancelAnimationFrame(this.faceAnimId);
                this.faceAnimId = null;
            }
            if (this.faceStream) {
                try {
                    this.faceStream.getTracks().forEach(track => track.stop());
                } catch (e) {
                    console.warn('Error stopping tracks:', e);
                }
                this.faceStream = null;
            }
            const video = this.getModalVideoElement();
            if (video) {
                try {
                    video.srcObject = null;
                    video.pause();
                } catch (e) {}
            }
            const canvas = this.getModalCanvasElement();
            if (canvas) {
                try {
                    const ctx = canvas.getContext('2d');
                    if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
                } catch (e) {}
            }
        },

        continueWithoutFace() {
            this.cameraSessionId++;
            this.isCameraLoading = false;
            this.stopFaceCamera();
            this.showFaceModal = false;
            this.reservationMode = 'manual';
            this.$nextTick(() => {
                if (this.$refs.calForm) {
                    this.$refs.calForm.submit();
                }
            });
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
                // 1. Inisialisasi Model AI dengan batas waktu (timeout 12 detik)
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

                // 2. Akses Webcam dengan batas waktu (timeout 8 detik)
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
                    this.isFaceVerified = true;
                    this.verifiedEmbedding = descriptor;
                    this.faceMatchSimilarity = data.similarity || 95;
                    this.faceScanStatus = '✓ Verifikasi berhasil! Reservasi Disetujui Otomatis.';

                    setTimeout(() => {
                        if (sessionId === this.cameraSessionId && this.showFaceModal) {
                            this.stopFaceCamera();
                            if (this.$refs.calForm) {
                                this.$refs.calForm.submit();
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
                const netMsg = 'Kesalahan koneksi saat verifikasi: ' + err.message;
                this.modalErrorMessage = netMsg;
                this.faceScanStatus = netMsg;
            }
        },
        typeDisabled(key) {
            const c = this.creating; if (!c) return true;
            if (c.hardBlocked) return true;
            if (key === 'room_computer') return c.sharedRoom || c.computerBooked;
            if (key === 'room_only') return c.sharedRoom;
            return false;
        },
        roomModeDisabled(mode) {
            const c = this.creating; if (!c) return true;
            return mode === 'exclusive' && c.computerBooked;
        },
        selectLoan(key) {
            if (this.typeDisabled(key)) return;
            this.creating.loanType = key;
            if (key === 'room_only' && this.roomModeDisabled(this.creating.roomMode)) {
                this.creating.roomMode = 'shared';
            }
        },
        onItemClick(item, e) {
            if (item.kind === 'event') this.openDetails(item.ev, e);
            else this.openGroup(item, e);
        },
        openDetails(ev, e) {
            this.creating = null; this.groupPop = null;
            this.selectedEvId = ev.id;
            this.details = {
                ev, dateLabel: this.dateLabelOf(ev.date),
                pos: this.popPos(this.rectAnchor(e.currentTarget), 300, 260),
            };
        },
        closeDetails() { this.details = null; this.selectedEvId = null; },
        openGroup(item, e) {
            this.creating = null; this.details = null;
            const sorted = [...item.items].sort((a, b) => a.start - b.start || a.id - b.id);
            this.groupPop = {
                items: item.items, sorted, start: item.start, dur: item.dur,
                pos: this.popPos(this.rectAnchor(e.currentTarget), 300, 360),
            };
        },
        openDetailsFromGroup(ev, e) {
            this.groupPop = null;
            this.openDetails(ev, e);
        },
        closeCreating() {
            this.cameraSessionId++;
            this.isCameraLoading = false;
            this.stopFaceCamera();
            this.showFaceModal = false;
            this.creating = null;
            this.block = null;
        },
        closeAll() {
            this.cameraSessionId++;
            this.isCameraLoading = false;
            this.stopFaceCamera();
            this.showFaceModal = false;
            this.creating = null;
            this.details = null;
            this.groupPop = null;
            this.block = null;
            this.selectedEvId = null;
        },

        // ── booking action urls ──
        showUrl(id) { return `${BOOKING_BASE}/${id}`; },
        cancelUrl(id) { return `${BOOKING_BASE}/${id}/cancel`; },
    };
}
</script>
@endpush

</x-app-layout>
