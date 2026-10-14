<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->string('jid', 100);
            $table->string('jjid', 100);
            $table->string('jota_file_path');
            $table->string('joti_file_path');
            $table->text('notes')->nullable();

            $table->string('jota_status', 20)->default('pending');
            $table->text('jota_admin_note')->nullable();

            $table->string('joti_status', 20)->default('pending');
            $table->text('joti_admin_note')->nullable();

            $table->string('final_status', 20)->default('pending');
            $table->text('admin_notes')->nullable();

            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();

            $table->unique('participant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_submissions');
    }
};
