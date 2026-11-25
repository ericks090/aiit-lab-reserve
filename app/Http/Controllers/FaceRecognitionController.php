<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Services\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaceRecognitionController extends Controller
{
    public function __construct(
        protected readonly FaceRecognitionService $faceService
    ) {
    }

    /**
     * Show the Face Registration / Profile Face Settings view.
     */
    public function showRegistration(): View
    {
        $user = auth()->user();

        return view('profile.face-registration', [
            'user'              => $user,
            'hasFaceRegistered' => $user->hasFaceRegistered(),
            'faceDimensions'    => $user->hasFaceRegistered() ? count((array) $user->face_embedding) : 0,
        ]);
    }

    /**
     * Save the user's face embedding vector from webcam registration.
     */
    public function registerFace(Request $request): JsonResponse
    {
        $request->validate([
            'embedding'   => ['required', 'array'],
            'embedding.*' => ['numeric'],
        ], [
            'embedding.required' => 'Data vektor wajah tidak boleh kosong.',
            'embedding.array'    => 'Data vektor wajah harus berupa array angka float.',
        ]);

        $user = $request->user();
        $embedding = $request->input('embedding');

        if (! $request->boolean('liveness_passed')) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi keaktifan (kedipan mata) wajib dilakukan sebelum mendaftarkan wajah.',
            ], 422);
        }
        if (count($embedding) < 64) {
            return response()->json([
                'success' => false,
                'message' => 'Vektor wajah tidak lengkap (minimal 64 dimensi). Pastikan wajah terlihat jelas dan tegak ke kamera.',
            ], 422);
        }

        $this->faceService->registerFace($user, $embedding);

        if ($request->filled('photo_data')) {
            $this->faceService->saveProfilePhoto($user, $request->input('photo_data'));
        }

        session()->flash('success', 'Wajah Berhasil Didaftarkan!');

        AuditLogService::record(
            'user.face_registered',
            $user,
            [],
            [
                'action'     => 'Pendaftaran/pembaruan sampel wajah pengguna',
                'dimensions' => count($embedding),
                'liveness_verified' => true,
            ]
        );

        return response()->json([
            'success'  => true,
            'message'  => 'Wajah Berhasil Didaftarkan!',
            'redirect' => route('dashboard'),
        ]);
    }

    /**
     * Stream a user's avatar straight from the public disk (works without the public/storage symlink).
     */
    public function avatar(\App\Models\User $user)
    {
        $path = preg_replace('#^(public|storage)/#', '', ltrim((string) $user->profile_photo_path, '/'));
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        abort_if($path === '' || ! $disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    /**
     * Verify live face embedding against user's stored face embedding.
     */
    public function verifyFace(Request $request): JsonResponse
    {
        $request->validate([
            'embedding'   => ['required', 'array'],
            'embedding.*' => ['numeric'],
        ], [
            'embedding.required' => 'Data pemindaian wajah tidak boleh kosong.',
            'embedding.array'    => 'Format data pemindaian wajah tidak valid.',
        ]);

        $user = $request->user();

        if (! $request->boolean('liveness_passed')) {
            AuditLogService::record('booking.face_liveness_missing', $user, [], [
                'action' => 'Verifikasi wajah ditolak: tantangan kedipan mata (liveness) belum lulus',
                'liveness_verified' => false,
            ]);

            return response()->json([
                'success'  => false,
                'verified' => false,
                'message'  => 'Verifikasi keaktifan (kedipan mata) belum lulus. Silakan berkedip di depan kamera.',
            ], 422);
        }

        if (! $user->hasFaceRegistered()) {
            return response()->json([
                'success'  => false,
                'verified' => false,
                'message'  => 'Anda belum mendaftarkan sampel wajah. Silakan daftarkan wajah terlebih dahulu di menu Profil.',
            ], 422);
        }

        $result = $this->faceService->verifyFace($user, $request->input('embedding'));

        if ($result['verified']) {
            AuditLogService::record('booking.face_verified', $user, [], [
                'action'     => 'Verifikasi wajah (reservasi/logbook) berhasil dengan liveness kedipan mata',
                'similarity' => $result['similarity'],
                'distance'   => $result['distance'],
                'liveness_verified' => true,
            ]);

            // Set temporary session mark valid for 10 minutes
            session([
                'face_verified_user_id' => $user->id,
                'face_verified_at'      => now()->timestamp,
            ]);

            return response()->json([
                'success'    => true,
                'verified'   => true,
                'distance'   => $result['distance'],
                'similarity' => $result['similarity'],
                'threshold'  => $result['threshold'],
                'message'    => $result['message'],
            ]);
        }

        return response()->json([
            'success'    => false,
            'verified'   => false,
            'distance'   => $result['distance'],
            'similarity' => $result['similarity'],
            'threshold'  => $result['threshold'],
            'message'    => $result['message'],
        ], 422);
    }
}
