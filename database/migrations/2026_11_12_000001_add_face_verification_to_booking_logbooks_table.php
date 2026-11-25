<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_logbooks', function (Blueprint $table) {
            $table->boolean('is_face_verified')->default(false)->after('special_software');
            $table->timestamp('face_verified_at')->nullable()->after('is_face_verified');
        });
    }

    public function down(): void
    {
        Schema::table('booking_logbooks', function (Blueprint $table) {
            $table->dropColumn(['is_face_verified', 'face_verified_at']);
        });
    }
};
