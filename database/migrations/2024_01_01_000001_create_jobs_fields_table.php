<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs_apply_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('jobs_apply_positions')->cascadeOnDelete();
            $table->string('label', 200);
            $table->enum('type', ['text', 'textarea', 'number', 'select', 'checkbox']);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedTinyInteger('col_md')->default(12);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_apply_fields');
    }
};
