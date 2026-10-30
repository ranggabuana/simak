<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dapodik_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('target', 30)->default('semua')->index(); // semua, sekolah, siswa, guru
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->index();
            $table->unsignedInteger('sekolah_synced')->default(0);
            $table->unsignedInteger('siswa_synced')->default(0);
            $table->unsignedInteger('guru_synced')->default(0);
            $table->unsignedInteger('total_records')->default(0);
            $table->string('semester_id', 10)->nullable();
            $table->text('message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dapodik_sync_logs');
    }
};
