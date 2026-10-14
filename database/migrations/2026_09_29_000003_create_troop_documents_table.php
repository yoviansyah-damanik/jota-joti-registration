<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('troop_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('troop_id')->constrained('troops')->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->unique(['troop_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('troop_documents');
    }
};
