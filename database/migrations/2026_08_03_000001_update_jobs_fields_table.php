<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs_apply_fields', function (Blueprint $table) {
            $table->enum('type', ['text', 'textarea', 'number', 'select', 'checkbox', 'radio', 'date', 'date_range', 'html'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('jobs_apply_fields', function (Blueprint $table) {
            $table->enum('type', ['text', 'textarea', 'number', 'select', 'checkbox'])->change();
        });
    }
};
