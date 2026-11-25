<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FaceRecognitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected readonly FaceRecognitionService $faceService
    ) {
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $studyPrograms = StudyProgram::where('is_active', true)->orderBy('name')->get();

        return view('auth.register', compact('studyPrograms'));
    }

    /**
     * Handle an incoming registration request with mandatory Face Biometric scanning.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'study_program_id' => ['required', 'integer', 'exists:study_programs,id'],
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
            'face_embedding'   => ['required'],
        ], [
            'name.required'             => 'Nama lengkap wajib diisi.',
            'email.required'            => 'Alamat email wajib diisi.',
            'email.unique'              => 'Email ini sudah terdaftar.',
            'study_program_id.required' => 'Pilih Program Studi Anda.',
            'study_program_id.exists'   => 'Program Studi yang dipilih tidak valid.',
            'password.required'         => 'Kata sandi wajib diisi.',
            'password.confirmed'        => 'Konfirmasi kata sandi tidak cocok.',
            'face_embedding.required'   => 'Perekaman wajah wajib dilakukan. Silakan lakukan pemindaian wajah di kamera sebelum mendaftar.',
        ]);

        if (! $request->boolean('liveness_passed')) {
            throw ValidationException::withMessages([
                'face_embedding' => 'Verifikasi keaktifan (kedipan mata) wajib dilakukan sebelum mendaftar.',
            ]);
        }

        $rawEmbedding = $request->input('face_embedding');
        $embedding = is_string($rawEmbedding) ? json_decode($rawEmbedding, true) : $rawEmbedding;

        if (! is_array($embedding) || count($embedding) < 64) {
            throw ValidationException::withMessages([
                'face_embedding' => 'Data vektor wajah tidak valid atau tidak lengkap. Pastikan wajah terlihat jelas dan tegak ke kamera saat dipindai.',
            ]);
        }

        $cleanedEmbedding = array_map('floatval', array_values($embedding));

        $user = User::create([
            'name'               => $request->name,
            'email'              => $request->email,
            'password'           => Hash::make($request->password),
            'study_program_id'   => $request->study_program_id,
            'role'               => 'lecturer',
            'is_active'          => true,
            'face_embedding'     => $cleanedEmbedding,
            'face_descriptor'    => $cleanedEmbedding,
            'face_registered_at' => now(),
        ]);

        if ($request->filled('photo_data')) {
            $this->faceService->saveProfilePhoto($user, $request->input('photo_data'));
        }

        AuditLogService::record('user.registered', $user, [], [
            'action'         => 'Pendaftaran akun baru dengan verifikasi biometrik wajah',
            'name'           => $user->name,
            'email'          => $user->email,
            'has_face'       => true,
            'liveness_verified' => true,
            'face_dimension' => count($cleanedEmbedding),
        ]);

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Selamat datang! Akun Anda berhasil didaftarkan bersama sampel wajah.');
    }
}
