<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingLogbook extends Model
{
    protected $fillable = [
        'booking_id', 'category', 'checkpoint_progress',
        'related_course', 'supervisor_name', 'duration_sufficient',
        'special_software', 'needs_internet', 'needs_installation',
        'external_devices', 'priority_level',
        'is_face_verified', 'face_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_sufficient'  => 'boolean',
            'needs_internet'       => 'boolean',
            'needs_installation'   => 'boolean',
            'is_face_verified'     => 'boolean',
            'face_verified_at'     => 'datetime',
        ];
    }

    public function isFaceVerified(): bool
    {
        return (bool) $this->is_face_verified;
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
