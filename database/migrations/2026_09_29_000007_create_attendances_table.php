<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->string('status', 20);
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
