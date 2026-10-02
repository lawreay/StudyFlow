<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_world_id')->constrained()->cascadeOnDelete();
            $table->string('certificate_number', 64)->unique();
            $table->string('learner_name');
            $table->string('program_name');
            $table->timestamp('issued_at');
            $table->timestamps();
            $table->unique(['user_id', 'game_world_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
