<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs_apply_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('jobs_apply_positions')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->json('answers');
            $table->enum('status', ['pending', 'reviewing', 'accepted', 'refused'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['position_id', 'status']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_apply_applications');
    }
};
