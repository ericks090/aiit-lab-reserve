<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('verification_method', ['manual', 'face_recognition'])
                  ->default('manual')
                  ->after('status');
            $table->timestamp('verified_at')
                  ->nullable()
                  ->after('verification_method');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['verification_method', 'verified_at']);
        });
    }
};
