<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_assignment_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('max_group_number');
            $table->boolean('is_reset')->default(false);
            $table->unsignedInteger('total_troops')->default(0);
            $table->unsignedInteger('total_participants')->default(0);
            $table->json('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_assignment_batches');
    }
};
