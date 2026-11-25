<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginEmailRequest;
use App\Http\Requests\Auth\LoginAuthenticateRequest;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FaceRecognitionService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected readonly FaceRecognitionService $faceService
    ) {
    }
    /**
     * Step 1: Display the email entry form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Step 1 POST: Detect study program from email domain.
     * On success, redirect to Step 2 with study program + user list in session.
     */
    public function detectStudyProgram(LoginEmailRequest $request): RedirectResponse
    {
        $email = strtolower(trim($request->input('email')));

        // Rate-limit Step 1 (Gmail + program password) by email + IP.
        $this->ensureIsNotRateLimitedByEmail($request);

        $program = StudyProgram::where('email', $email)
            ->where('is_active', true)
            ->first();

        if (! $program) {
            RateLimiter::hit($this->throttleKeyByEmail($request));
            throw ValidationException::withMessages([
                'email' => 'Gmail program studi tidak terdaftar.',
            ]);
        }

        // Verify the shared program password.
        if (! $program->password || ! Hash::check($request->input('password'), $program->password)) {
            RateLimiter::hit($this->throttleKeyByEmail($request));
            throw ValidationException::withMessages([
                'password' => 'Kata sandi program studi tidak cocok.',
            ]);
        }

        $hasUsers = User::where('study_program_id', $program->id)
            ->where('is_active', true)
            ->where('role', '!=', 'admin')
            ->exists();

        if (! $hasUsers) {
            throw ValidationException::withMessages([
                'email' => 'Belum ada akun aktif pada program studi ini. Hubungi admin.',
            ]);
        }

        RateLimiter::clear($this->throttleKeyByEmail($request));

        $request->session()->put('login.study_program_id', $program->id);
        $request->session()->put('login.email_attempted', $email);

        return redirect()->route('login.select');
    }

    /**
     * Step 2: Display the user dropdown + password form.
     */
    public function selectUser(Request $request): View|RedirectResponse
    {
        $programId = $request->session()->get('login.study_program_id');

        if (! $programId) {
            return redirect()->route('login');
        }

        $program = StudyProgram::find($programId);
        if (! $program) {
            $request->session()->forget(['login.study_program_id', 'login.email_attempted']);
            return redirect()->route('login');
        }

        $users = User::where('study_program_id', $programId)
            ->where('is_active', true)
            ->where('role', '!=', 'admin')
            ->orderByRaw("FIELD(role, 'lecturer', 'team')")
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'face_embedding']);

        return view('auth.select-user', compact('program', 'users'));
    }

    /**
     * AJAX endpoint: Verify face descriptor against the selected user account.
     */
    public function verifyFace(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'     => ['required', 'integer', 'exists:users,id'],
            'embedding'   => ['required', 'array'],
            'embedding.*' => ['numeric'],
        ], [
            'user_id.required'   => 'Pilih akun pengguna terlebih dahulu.',
            'embedding.required' => 'Data vektor wajah tidak boleh kosong.',
        ]);

        $programId = $request->session()->get('login.study_program_id');
        if (! $programId) {
            return response()->json([
                'success'  => false,
                'verified' => false,
                'message'  => 'Sesi login telah kedaluwarsa. Silakan ulangi langkah pertama.',
            ], 419);
        }

        $user = User::where('id', $request->input('user_id'))
            ->where('study_program_id', $programId)
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return response()->json([
                'success'  => false,
                'verified' => false,
                'message'  => 'Pengguna tidak valid atau tidak terdaftar pada program studi ini.',
            ], 404);
        }

        if (! $request->boolean('liveness_passed')) {
            AuditLogService::record('auth.face_liveness_missing', $user, [], [
                'action'  => 'Login wajah ditolak: tantangan kedipan mata (liveness) belum lulus',
                'user_id' => $user->id,
                'liveness_verified' => false,
            ]);

            return response()->json([
                'success'  => false,
                'verified' => false,
                'has_face' => $user->hasFaceRegistered(),
                'message'  => 'Verifikasi keaktifan (kedipan mata) belum lulus. Silakan berkedip di depan kamera.',
            ], 422);
        }

        if (! $user->hasFaceRegistered()) {
            AuditLogService::record('auth.face_missing', $user, [], [
                'action'  => 'Percobaan login wajah: Akun belum memiliki sampel wajah biometrik',
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success'  => false,
                'verified' => false,
                'has_face' => false,
                'message'  => 'Akun ' . $user->name . ' belum memiliki data sampel wajah. Silakan daftarkan wajah Anda terlebih dahulu.',
            ], 422);
        }

        $result = $this->faceService->verifyFace($user, $request->input('embedding'));

        if ($result['verified']) {
            // Backfill avatar for accounts whose photo is missing (face already verified as this user).
            if ($request->filled('photo_data') && ! $user->profile_photo_url) {
                $this->faceService->saveProfilePhoto($user, $request->input('photo_data'));
            }

            $request->session()->put('login.face_verified_user_id', $user->id);
            $request->session()->put('login.face_verified_at', now()->timestamp);

            AuditLogService::record('auth.face_matched', $user, [], [
                'action'     => 'Verifikasi wajah login cocok',
                'liveness_verified' => true,
                'similarity' => $result['similarity'],
                'distance'   => $result['distance'],
                'user_id'    => $user->id,
            ]);

            return response()->json([
                'success'    => true,
                'verified'   => true,
                'has_face'   => true,
                'similarity' => $result['similarity'],
                'distance'   => $result['distance'],
                'message'    => 'Wajah cocok! Mengarahkan masuk ke sistem...',
            ]);
        }

        AuditLogService::record('auth.face_mismatch', $user, [], [
            'action'     => 'Verifikasi wajah login gagal: Wajah tidak cocok',
            'similarity' => $result['similarity'],
            'distance'   => $result['distance'],
            'threshold'  => $result['threshold'],
            'user_id'    => $user->id,
        ]);

        return response()->json([
            'success'    => false,
            'verified'   => false,
            'has_face'   => true,
            'similarity' => $result['similarity'],
            'distance'   => $result['distance'],
            'message'    => 'Wajah tidak cocok dengan akun ' . $user->name . ' (Tingkat kemiripan: ' . ($result['similarity'] ?? 0) . '%). Silakan coba lagi.',
        ], 422);
    }

    /**
     * AJAX endpoint: First-time face enrollment for legacy accounts during login.
     */
    public function enrollFace(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'     => ['required', 'integer', 'exists:users,id'],
            'embedding'   => ['required', 'array'],
            'embedding.*' => ['numeric'],
        ], [
            'user_id.required'   => 'Pilih akun pengguna terlebih dahulu.',
            'embedding.required' => 'Perekaman sampel wajah wajib dilakukan.',
        ]);

        $programId = $request->session()->get('login.study_program_id');
        if (! $programId) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login telah kedaluwarsa. Silakan ulangi langkah pertama.',
            ], 419);
        }

        $user = User::where('id', $request->input('user_id'))
            ->where('study_program_id', $programId)
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak valid atau tidak terdaftar pada program studi ini.',
            ], 404);
        }

        if (! $request->boolean('liveness_passed')) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi keaktifan (kedipan mata) wajib dilakukan sebelum pendaftaran wajah.',
            ], 422);
        }

        $embedding = $request->input('embedding');
        if (count($embedding) < 64) {
            return response()->json([
                'success' => false,
                'message' => 'Data vektor wajah tidak lengkap (minimal 64 dimensi). Pastikan wajah terlihat jelas di kamera.',
            ], 422);
        }

        // Store face descriptor & face embedding
        $this->faceService->registerFace($user, $embedding);

        if ($request->filled('photo_data')) {
            $this->faceService->saveProfilePhoto($user, $request->input('photo_data'));
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        Auth::login($user, $request->boolean('remember'));

        AuditLogService::record('user.face_enrolled_at_login', $user, [], [
            'action'         => 'Pendaftaran wajah perdana akun lama saat proses login',
            'user_id'        => $user->id,
            'name'           => $user->name,
            'face_dimension' => count($embedding),
        ]);

        $request->session()->regenerate();
        $request->session()->forget([
            'login.study_program_id',
            'login.email_attempted',
            'login.face_verified_user_id',
            'login.face_verified_at',
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Wajah berhasil didaftarkan! Mengarahkan ke Dashboard...',
            'redirect' => $this->redirectPathByRole($user->role),
        ]);
    }

    /**
     * Step 2 POST: Authenticate the selected user with mandatory face verification.
     */
    public function authenticate(LoginAuthenticateRequest $request): RedirectResponse
    {
        $programId = $request->session()->get('login.study_program_id');

        if (! $programId) {
            return redirect()->route('login');
        }

        // The program password was already verified in Step 1; Step 2 confirms the
        // selected user belongs to that authenticated program, and verifies biometric face match.
        $user = User::where('id', $request->input('user_id'))
            ->where('study_program_id', $programId)
            ->where('is_active', true)
            ->where('role', '!=', 'admin')
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'user_id' => 'Pengguna yang dipilih tidak valid.',
            ]);
        }

        $sessionValid = ($request->session()->get('login.face_verified_user_id') === $user->id)
            && ((now()->timestamp - $request->session()->get('login.face_verified_at', 0)) <= 300);

        $payloadValid = false;
        $embeddingPayload = $request->input('face_embedding');
        // Payload path is only trusted when the client passed the blink-liveness challenge.
        if (! $request->boolean('is_face_verified') && ! $request->boolean('liveness_passed')) {
            $embeddingPayload = null;
        }

        // Check legacy account without face descriptor
        if (! $user->hasFaceRegistered()) {
            if ($embeddingPayload) {
                $embeddingArray = is_string($embeddingPayload) ? json_decode($embeddingPayload, true) : $embeddingPayload;
                if (is_array($embeddingArray) && count($embeddingArray) >= 64) {
                    $this->faceService->registerFace($user, $embeddingArray);
                    if ($request->filled('photo_data')) {
                        $this->faceService->saveProfilePhoto($user, $request->input('photo_data'));
                    }
                    AuditLogService::record('user.face_enrolled_at_login', $user, [], [
                        'action'  => 'Pendaftaran wajah perdana akun lama saat form submit login',
                        'user_id' => $user->id,
                    ]);
                    $payloadValid = true;
                }
            }

            if (! $payloadValid) {
                return redirect()->route('login.face-setup', ['user_id' => $user->id])
                    ->with('warning', 'Akun ' . $user->name . ' belum memiliki data sampel wajah. Silakan pindai wajah Anda sekarang untuk mengaktifkan login biometrik.');
            }
        } else {
            // Existing account with face registered -> verify
            if ($embeddingPayload) {
                $embeddingArray = is_string($embeddingPayload) ? json_decode($embeddingPayload, true) : $embeddingPayload;
                if (is_array($embeddingArray) && count($embeddingArray) >= 64) {
                    $verResult = $this->faceService->verifyFace($user, $embeddingArray);
                    $payloadValid = $verResult['verified'] ?? false;
                }
            }
        }

        if (! $sessionValid && ! $payloadValid) {
            AuditLogService::record('auth.face_login_rejected', $user, [], [
                'action'  => 'Login ditolak: Verifikasi wajah tidak valid atau belum dilakukan',
                'user_id' => $user->id,
            ]);

            throw ValidationException::withMessages([
                'face_verification' => 'Verifikasi wajah belum berhasil atau kedaluwarsa. Silakan lakukan pemindaian wajah di kamera untuk masuk.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $user->forceFill(['last_login_at' => now()])->save();

        AuditLogService::record('auth.face_login_success', $user, [], [
            'action'  => 'Login berhasil disahkan melalui verifikasi pengenalan wajah',
            'method'  => 'face_recognition',
            'user_id' => $user->id,
        ]);

        $request->session()->regenerate();
        $request->session()->forget([
            'login.study_program_id',
            'login.email_attempted',
            'login.face_verified_user_id',
            'login.face_verified_at',
        ]);

        return redirect()->intended($this->redirectPathByRole($user->role));
    }

    /**
     * Display face registration setup for legacy accounts that don't have face registered.
     */
    public function showFaceSetup(Request $request): View|RedirectResponse
    {
        $programId = $request->session()->get('login.study_program_id');
        if (! $programId) {
            return redirect()->route('login');
        }

        $program = StudyProgram::find($programId);
        if (! $program) {
            return redirect()->route('login');
        }

        $users = User::where('study_program_id', $programId)
            ->where('is_active', true)
            ->where('role', '!=', 'admin')
            ->orderBy('name')
            ->get();

        $selectedUserId = $request->input('user_id');

        return view('auth.face-setup', compact('program', 'users', 'selectedUserId'));
    }

    /**
     * Store face registration for a legacy account and authenticate the user.
     */
    public function storeFaceSetup(Request $request): RedirectResponse
    {
        $programId = $request->session()->get('login.study_program_id');
        if (! $programId) {
            return redirect()->route('login');
        }

        $request->validate([
            'user_id'        => ['required', 'integer', 'exists:users,id'],
            'face_embedding' => ['required'],
        ], [
            'user_id.required'        => 'Pilih akun Anda terlebih dahulu.',
            'face_embedding.required' => 'Perekaman wajah wajib dilakukan sebelum masuk.',
        ]);

        $user = User::where('id', $request->input('user_id'))
            ->where('study_program_id', $programId)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $request->boolean('liveness_passed')) {
            throw ValidationException::withMessages([
                'face_embedding' => 'Verifikasi keaktifan (kedipan mata) wajib dilakukan sebelum pendaftaran wajah.',
            ]);
        }

        $rawEmbedding = $request->input('face_embedding');
        $embedding = is_string($rawEmbedding) ? json_decode($rawEmbedding, true) : $rawEmbedding;

        if (! is_array($embedding) || count($embedding) < 64) {
            throw ValidationException::withMessages([
                'face_embedding' => 'Data vektor wajah tidak valid atau kurang lengkap. Pastikan wajah terlihat jelas di kamera.',
            ]);
        }

        $this->faceService->registerFace($user, $embedding);

        if ($request->filled('photo_data')) {
            $this->faceService->saveProfilePhoto($user, $request->input('photo_data'));
        }

        $user->forceFill(['face_registered_at' => now(), 'last_login_at' => now()])->save();

        AuditLogService::record('user.face_registered_legacy', $user, [], [
            'action'  => 'Pendaftaran wajah akun lama saat alur login',
            'user_id' => $user->id,
        ]);

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->forget(['login.study_program_id', 'login.email_attempted']);

        return redirect()->intended($this->redirectPathByRole($user->role))
            ->with('success', 'Wajah berhasil didaftarkan! Selamat datang di sistem, ' . $user->name . '.');
    }

    /**
     * Admin login POST: authenticate directly with email + password.
     */
    public function adminAuthenticate(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $this->ensureIsNotRateLimitedByEmail($request);

        $admin = User::where('email', $request->input('email'))
            ->where('role', 'admin')
            ->first();

        if (! $admin || ! Hash::check($request->input('password'), $admin->password)) {
            RateLimiter::hit($this->throttleKeyByEmail($request));

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak cocok.',
            ]);
        }

        if (! $admin->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun admin nonaktif.',
            ]);
        }

        RateLimiter::clear($this->throttleKeyByEmail($request));

        Auth::login($admin, $request->boolean('remember'));
        $admin->forceFill(['last_login_at' => now()])->save();
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectPathByRole(string $role): string
    {
        return $role === 'admin'
            ? route('admin.dashboard', absolute: false)
            : route('dashboard', absolute: false);
    }

    protected function ensureIsNotRateLimitedByEmail(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKeyByEmail($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKeyByEmail($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKeyByEmail(Request $request): string
    {
        $key = (string) $request->input('email', '');
        return Str::transliterate(Str::lower($key) . '|' . $request->ip());
    }
}
