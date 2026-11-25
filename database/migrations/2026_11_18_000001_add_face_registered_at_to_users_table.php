<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'face_embedding')) {
                $table->longText('face_embedding')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'face_registered_at')) {
                $table->timestamp('face_registered_at')->nullable()->after('face_embedding');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'face_registered_at')) {
                $table->dropColumn('face_registered_at');
            }
        });
    }
};
