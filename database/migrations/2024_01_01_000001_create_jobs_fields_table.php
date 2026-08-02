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

        $now = now();
        $defaultFields = [
            ['label' => 'Username', 'type' => 'text', 'options' => null, 'is_required' => true, 'col_md' => 6, 'order' => 0],
            ['label' => 'Why should we choose you?', 'type' => 'textarea', 'options' => null, 'is_required' => true, 'col_md' => 12, 'order' => 1],
            ['label' => 'How many months have you been playing on the server?', 'type' => 'number', 'options' => null, 'is_required' => true, 'col_md' => 6, 'order' => 2],
            ['label' => 'Your availability', 'type' => 'select', 'options' => ['Always', '5 days a week', '3 days a week', 'A few hours per week'], 'is_required' => true, 'col_md' => 6, 'order' => 3],
            ['label' => 'Are you available immediately?', 'type' => 'checkbox', 'options' => null, 'is_required' => false, 'col_md' => 6, 'order' => 4],
        ];

        $positionIds = DB::table('jobs_apply_positions')
            ->whereIn('slug', ['configurator', 'moderator', 'animator'])
            ->pluck('id');

        foreach ($positionIds as $positionId) {
            foreach ($defaultFields as $field) {
                DB::table('jobs_apply_fields')->insert([
                    'position_id' => $positionId,
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'options' => $field['options'] !== null ? json_encode($field['options']) : null,
                    'is_required' => $field['is_required'],
                    'col_md' => $field['col_md'],
                    'order' => $field['order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_apply_fields');
    }
};
