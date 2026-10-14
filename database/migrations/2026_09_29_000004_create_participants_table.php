<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('troop_id')->constrained('troops')->cascadeOnDelete();
            $table->string('kta_number', 50)->nullable()->index();
            $table->string('attendance_code', 12)->nullable()->unique();
            $table->string('full_name');
            $table->date('birth_date');
            $table->char('gender', 1);
            $table->unsignedTinyInteger('group_number')->nullable()->index();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
