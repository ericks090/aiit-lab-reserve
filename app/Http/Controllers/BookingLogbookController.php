<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\AuditLogService;
use App\Services\FaceRecognitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BookingLogbookController extends Controller
{
    public function __construct(
        protected readonly FaceRecognitionService $faceService
    ) {
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        abort_if(! $booking->isEditable(), 403, 'Logbook untuk reservasi ini belum dapat diubah.');

        $validated = $request->validate([
            'checkpoint_progress' => ['required', 'string', 'min:10', 'max:2000'],
            'supervisor_name'     => ['nullable', 'string', 'max:255'],
            'related_course'      => ['nullable', 'string', 'max:255'],
            'is_face_verified'    => ['nullable'],
            'face_embedding'      => ['nullable'],
        ], [
            'checkpoint_progress.required' => 'Deskripsi checkpoint wajib diisi.',
            'checkpoint_progress.min'      => 'Deskripsi minimal 10 karakter.',
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

        // Preserve existing required category — if creating fresh, default to "lainnya"
        $existing = $booking->logbook;
        $payload  = [
            'checkpoint_progress' => $validated['checkpoint_progress'],
            'supervisor_name'     => $validated['supervisor_name'] ?? null,
            'related_course'      => $validated['related_course'] ?? null,
            'category'            => $existing->category ?? 'lainnya',
        ];

        if (Schema::hasColumn('booking_logbooks', 'is_face_verified') && $isFaceVerified) {
            $payload['is_face_verified'] = true;
            $payload['face_verified_at'] = now();
        }

        $booking->logbook()->updateOrCreate(
            ['booking_id' => $booking->id],
            $payload
        );

        if ($isFaceVerified) {
            AuditLogService::record('logbook.face_verified', $booking, [], [
                'action'       => 'Verifikasi wajah pengisian logbook (detail reservasi)',
                'booking_code' => $booking->booking_code,
                'verified_at'  => now()->toIso8601String(),
            ]);
        }

        $msg = 'Logbook berhasil disimpan.' . ($isFaceVerified ? ' (Terverifikasi Wajah)' : '');

        return redirect()
            ->route('booking.show', $booking)
            ->with('success', $msg);
    }
}
