<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            Schema::getConnection()->statement('ALTER TABLE jobs_apply_fields DROP CONSTRAINT IF EXISTS jobs_apply_fields_type_check');
        } elseif ($driver === 'mysql') {
            try {
                Schema::getConnection()->statement('ALTER TABLE jobs_apply_fields DROP CHECK jobs_apply_fields_type_check');
            } catch (\Exception $e) {
                // Ignore if not present or unsupported
            }
        }

        Schema::table('jobs_apply_fields', function (Blueprint $table) {
            $table->string('type', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('jobs_apply_fields', function (Blueprint $table) {
            $table->enum('type', ['text', 'textarea', 'number', 'select', 'checkbox'])->change();
        });
    }
};
