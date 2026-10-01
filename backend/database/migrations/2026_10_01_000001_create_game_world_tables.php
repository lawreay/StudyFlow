<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_worlds', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('game_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained('game_worlds')->cascadeOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('position');
            $table->unsignedInteger('unlock_xp')->default(0);
            $table->boolean('is_start_node')->default(false);
            $table->timestamps();
            $table->unique(['world_id', 'slug']);
            $table->unique(['world_id', 'position']);
        });

        Schema::create('player_node_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_node_id')->constrained('game_nodes')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'game_node_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_node_progress');
        Schema::dropIfExists('game_nodes');
        Schema::dropIfExists('game_worlds');
    }
};