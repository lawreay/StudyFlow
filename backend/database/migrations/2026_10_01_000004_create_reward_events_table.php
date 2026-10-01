<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->unsignedInteger('xp_amount');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(
                ['user_id', 'type', 'reference_type', 'reference_id'],
                'reward_events_once_per_reference',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_events');
    }
};