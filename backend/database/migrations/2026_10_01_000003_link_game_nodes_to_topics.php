<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_nodes', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->after('world_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('required_score')->nullable()->after('reward_xp');
        });
    }

    public function down(): void
    {
        Schema::table('game_nodes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
            $table->dropColumn('required_score');
        });
    }
};