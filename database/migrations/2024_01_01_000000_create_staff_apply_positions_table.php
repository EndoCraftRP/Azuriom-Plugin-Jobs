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

        $now = now();
        DB::table('jobs_apply_positions')->insert([
            ['name' => 'Configurator', 'slug' => 'configurator', 'description' => null, 'keywords' => json_encode([]), 'is_open' => true, 'published_at' => $now, 'closed_at' => null, 'max_pending' => null, 'show_applications_count' => false, 'order' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Moderator', 'slug' => 'moderator', 'description' => null, 'keywords' => json_encode([]), 'is_open' => true, 'published_at' => $now, 'closed_at' => null, 'max_pending' => null, 'show_applications_count' => false, 'order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Animator', 'slug' => 'animator', 'description' => null, 'keywords' => json_encode([]), 'is_open' => true, 'published_at' => $now, 'closed_at' => null, 'max_pending' => null, 'show_applications_count' => false, 'order' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_apply_positions');
    }
};
