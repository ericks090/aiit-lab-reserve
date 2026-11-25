<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'study_program_id', 'name', 'email', 'password',
        'role', 'is_active', 'last_login_at', 'face_embedding',
        'face_descriptor', 'face_registered_at', 'profile_photo_path',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'           => 'hashed',
            'is_active'          => 'boolean',
            'last_login_at'      => 'datetime',
            'face_embedding'     => 'array',
            'face_descriptor'    => 'array',
            'face_registered_at' => 'datetime',
        ];
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function teamAccount()
    {
        return $this->hasOne(Team::class, 'user_id');
    }

    public function picTeams()
    {
        return $this->hasMany(Team::class, 'pic_lecturer_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isLecturer(): bool
    {
        return $this->role === 'lecturer';
    }

    public function isTeam(): bool
    {
        return $this->role === 'team';
    }

    public function hasFaceRegistered(): bool
    {
        return !empty($this->face_descriptor) || !empty($this->face_embedding);
    }

    public function getFaceVector(): ?array
    {
        return $this->face_descriptor ?? $this->face_embedding ?? null;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        $path = ltrim((string) $this->profile_photo_path, '/');

        // Normalise legacy values such as "public/avatars/1.jpg" or "storage/avatars/1.jpg".
        $path = preg_replace('#^(public|storage)/#', '', $path);

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $version = Storage::disk('public')->lastModified($path);

        // Prefer the public/storage symlink; fall back to a streaming route when it is missing.
        if (file_exists(public_path('storage'))) {
            return asset('storage/' . $path) . '?v=' . $version;
        }

        return route('profile.avatar', ['user' => $this->id, 'v' => $version]);
    }
}
