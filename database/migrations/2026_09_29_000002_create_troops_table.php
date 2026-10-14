<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('troops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('troop_number', 6)->index();
            $table->string('troop_number_pair', 6)->index();
            $table->string('troop_name');
            $table->string('unit', 20)->index();
            $table->unsignedInteger('fee_per_participant')->default(0);
            $table->string('pic_name');
            $table->string('pic_phone', 30);
            $table->string('pic_email')->nullable();
            $table->string('registration_code', 20)->unique();
            $table->string('status', 20)->default('submitted')->index();
            $table->text('admin_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('troops');
    }
};
