<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class FaceRecognitionService
{
    /**
     * Maximum Euclidean distance to consider two face descriptors a match.
     * Face-API.js descriptors are normalized 128-dimensional vectors.
     * Standard benchmark threshold is 0.45 (strict & secure).
     */
    public const DEFAULT_THRESHOLD = 0.45;

    /**
     * Calculate Euclidean distance between two face embedding vectors.
     *
     * @param  array<float>  $embedding1
     * @param  array<float>  $embedding2
     * @return float
     */
    public function calculateDistance(array $embedding1, array $embedding2): float
    {
        $len1 = count($embedding1);
        $len2 = count($embedding2);

        if ($len1 === 0 || $len2 === 0) {
            return 1.0;
        }

        $length = min($len1, $len2);
        $sumSquares = 0.0;

        for ($i = 0; $i < $length; $i++) {
            $diff = (float) $embedding1[$i] - (float) $embedding2[$i];
            $sumSquares += ($diff * $diff);
        }

        return (float) sqrt($sumSquares);
    }

    /**
     * Compute approximate similarity percentage based on Euclidean distance.
     * Distance 0.00 = 100% match.
     * Distance 0.45 ≈ 75% match (threshold).
     * Distance >= 1.0 = 0% match.
     */
    public function calculateSimilarityScore(float $distance): float
    {
        $similarity = (1.0 - ($distance / 1.0)) * 100;
        return round(max(0.0, min(100.0, $similarity)), 1);
    }

    /**
     * Verify a live scanned face embedding against the user's stored embedding.
     *
     * @param  User          $user
     * @param  array<float>  $liveEmbedding
     * @param  float|null    $threshold
     * @return array{verified: bool, distance: float|null, similarity: float|null, threshold: float, message: string}
     */
    public function verifyFace(User $user, array $liveEmbedding, ?float $threshold = null): array
    {
        $threshold ??= self::DEFAULT_THRESHOLD;

        $storedEmbedding = $user->getFaceVector();

        if (empty($storedEmbedding) || ! is_array($storedEmbedding)) {
            return [
                'verified'   => false,
                'distance'   => null,
                'similarity' => null,
                'threshold'  => $threshold,
                'message'    => 'Pengguna belum memiliki data sampel wajah di database.',
            ];
        }

        if (empty($liveEmbedding) || count($liveEmbedding) < 64) {
            return [
                'verified'   => false,
                'distance'   => null,
                'similarity' => null,
                'threshold'  => $threshold,
                'message'    => 'Data vektor wajah tidak valid atau tidak lengkap.',
            ];
        }

        $distance = $this->calculateDistance($storedEmbedding, $liveEmbedding);
        $similarity = $this->calculateSimilarityScore($distance);
        $isMatched = $distance <= $threshold;

        return [
            'verified'   => $isMatched,
            'distance'   => round($distance, 4),
            'similarity' => $similarity,
            'threshold'  => $threshold,
            'message'    => $isMatched
                ? "Wajah cocok! Verifikasi berhasil (Tingkat kemiripan: {$similarity}%)."
                : "Wajah tidak cocok dengan data terdaftar (Selisih jarak: " . round($distance, 4) . " > batas " . $threshold . ").",
        ];
    }

    /**
     * Store the extracted face descriptor for the given user.
     *
     * @param  User          $user
     * @param  array<float>  $embedding
     * @return bool
     */
    public function registerFace(User $user, array $embedding): bool
    {
        if (empty($embedding) || count($embedding) < 64) {
            throw new InvalidArgumentException('Vector embedding wajah tidak valid atau kurang dari 64 dimensi.');
        }

        // Clean and ensure floats
        $cleaned = array_map('floatval', array_values($embedding));

        $user->face_embedding = $cleaned;
        $user->face_descriptor = $cleaned;
        $user->face_registered_at = now();
        return $user->save();
    }

    /**
     * Save user avatar photo from base64 data string to public storage.
     *
     * @param  User         $user
     * @param  string|null  $base64Image
     * @return string|null Path to saved photo or null on failure/empty
     */
    public function saveProfilePhoto(User $user, ?string $base64Image): ?string
    {
        if (empty($base64Image)) {
            return null;
        }

        // Strip out base64 preamble if present: data:image/...;base64,
        if (str_contains($base64Image, ';base64,')) {
            [, $base64Image] = explode(';base64,', $base64Image, 2);
        }

        $decoded = base64_decode($base64Image, true);
        if ($decoded === false || strlen($decoded) < 100) {
            return null;
        }

        try {
            $disk = Storage::disk('public');
            if (! $disk->exists('avatars')) {
                $disk->makeDirectory('avatars');
            }

            // Delete old profile photo if it has a different name
            $filename = 'avatars/' . $user->id . '.jpg';
            if (! empty($user->profile_photo_path) && $user->profile_photo_path !== $filename && $disk->exists($user->profile_photo_path)) {
                $disk->delete($user->profile_photo_path);
            }

            // Fixed relative path (cache busting is handled via ?v=mtime in the URL accessor)
            $disk->put($filename, $decoded);

            $user->forceFill([
                'profile_photo_path' => $filename,
            ])->save();

            return $filename;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed saving avatar photo: ' . $e->getMessage());
            return null;
        }
    }
}
