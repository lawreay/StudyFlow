<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_nodes', function (Blueprint $table) {
            $table->unsignedInteger('reward_xp')->default(0)->after('unlock_xp');
        });
    }

    public function down(): void
    {
        Schema::table('game_nodes', function (Blueprint $table) {
            $table->dropColumn('reward_xp');
        });
    }
};