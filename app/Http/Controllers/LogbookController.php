<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\AuditLogService;
use App\Services\FaceRecognitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LogbookController extends Controller
{
    public function __construct(
        protected readonly FaceRecognitionService $faceService
    ) {
    }

    /** List the user's approved/completed reservations, each with its logbook note. */
    public function index(): View
    {
        $bookings = auth()->user()->bookings()
            ->whereIn('status', ['approved', 'completed'])
            ->with('logbook')
            ->orderByDesc('date')->orderByDesc('start_time')
            ->paginate(10);

        $userHasFaceRegistered = auth()->user()?->hasFaceRegistered() ?? false;

        return view('logbook.index', compact('bookings', 'userHasFaceRegistered'));
    }

    /** Save the free-text logbook note for one booking (owner only). */
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        abort_if(! $booking->isEditable(), 403, 'Logbook reservasi ini belum dapat diubah.');

        $validated = $request->validate([
            'checkpoint_progress' => ['required', 'string', 'min:10', 'max:2000'],
            'needs_installation'  => ['nullable', 'boolean'],
            'special_software'    => ['nullable', 'string', 'max:2000', 'required_if:needs_installation,1'],
            'is_face_verified'    => ['nullable'],
            'face_embedding'      => ['nullable'],
        ], [
            'checkpoint_progress.required' => 'Catatan logbook wajib diisi.',
            'checkpoint_progress.min'      => 'Catatan minimal 10 karakter.',
            'checkpoint_progress.max'      => 'Catatan maksimal 2000 karakter.',
            'special_software.required_if' => 'Sebutkan perangkat lunak yang diunduh/diinstal.',
            'special_software.max'         => 'Daftar perangkat lunak maksimal 2000 karakter.',
        ]);

        $user = auth()->user();
        $isFaceVerified = false;

        // Face Verification logic (session verification or payload embedding)
        if ($request->boolean('is_face_verified') || $request->input('face_embedding')) {
            $sessionValid = (session('face_verified_user_id') === $user->id)
                && ((now()->timestamp - session('face_verified_at', 0)) <= 600);

            $payloadValid = false;
            $embeddingPayload = $request->input('face_embedding');
            if ($embeddingPayload && $user->hasFaceRegistered()) {
                $embeddingArray = is_string($embeddingPayload) ? json_decode($embeddingPayload, true) : $embeddingPayload;
                if (is_array($embeddingArray) && count($embeddingArray) >= 64) {
                    $verResult = $this->faceService->verifyFace($user, $embeddingArray);
                    $payloadValid = $verResult['verified'] ?? false;
                }
            }

            if ($sessionValid || $payloadValid) {
                $isFaceVerified = true;
                session()->forget(['face_verified_user_id', 'face_verified_at']);
            }
        }

        $needsInstall = $request->boolean('needs_installation');

        // Snapshot tracked fields BEFORE saving so we can audit what actually changed.
        $existing = $booking->logbook;
        $old = [
            'checkpoint_progress' => $existing->checkpoint_progress ?? null,
            'needs_installation'  => $existing ? (bool) $existing->needs_installation : null,
            'special_software'    => $existing->special_software ?? null,
            'is_face_verified'    => $existing ? (bool) ($existing->is_face_verified ?? false) : false,
        ];

        $logbookPayload = [
            'checkpoint_progress' => $validated['checkpoint_progress'],
            'needs_installation'  => $needsInstall,
            // When the box is off, don't keep a stale software list.
            'special_software'    => $needsInstall ? ($validated['special_software'] ?? null) : null,
            'category'            => $existing->category ?? 'lainnya',
        ];

        if (Schema::hasColumn('booking_logbooks', 'is_face_verified') && $isFaceVerified) {
            $logbookPayload['is_face_verified'] = true;
            $logbookPayload['face_verified_at'] = now();
        }

        $logbook = $booking->logbook()->updateOrCreate(
            ['booking_id' => $booking->id],
            $logbookPayload,
        );

        $new = [
            'checkpoint_progress' => $logbook->checkpoint_progress,
            'needs_installation'  => (bool) $logbook->needs_installation,
            'special_software'    => $logbook->special_software,
            'is_face_verified'    => (bool) ($logbook->is_face_verified ?? false),
        ];

        if ($isFaceVerified) {
            AuditLogService::record('logbook.face_verified', $booking, [], [
                'action'       => 'Verifikasi wajah saat pengisian logbook lab',
                'booking_code' => $booking->booking_code,
                'verified_at'  => now()->toIso8601String(),
            ]);
        }

        $this->recordLogbookAudit($booking, $old, $new);

        $msg = 'Catatan logbook ' . $booking->booking_code . ' tersimpan.' . ($isFaceVerified ? ' (Terverifikasi Wajah)' : '');

        return back()->with('success', $msg);
    }

    /**
     * Record a 'logbook.updated' audit entry, but only for the fields that
     * actually changed. The full before/after value of each changed field is
     * stored so the admin audit log can show the complete edit history.
     */
    private function recordLogbookAudit(Booking $booking, array $old, array $new): void
    {
        $changedOld = [];
        $changedNew = [];

        foreach ($new as $key => $newValue) {
            if (($old[$key] ?? null) === $newValue) {
                continue;
            }
            $changedOld[$key] = $old[$key] ?? null;
            $changedNew[$key] = $newValue;
        }

        if (empty($changedNew)) {
            return; // nothing changed → no audit noise
        }

        AuditLogService::record('logbook.updated', $booking, $changedOld, $changedNew);
    }
}
