<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs_apply_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 110)->unique();
            $table->text('description')->nullable();
            $table->json('keywords')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedInteger('max_pending')->nullable();
            $table->boolean('show_applications_count')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_apply_positions');
    }
};
