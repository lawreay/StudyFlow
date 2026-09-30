<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->unsignedInteger('points')->default(1)->after('explanation');
        });

        Schema::table('attempts', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('total_questions');
        });

        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->unsignedInteger('marks_earned')->default(0)->after('is_correct');
            $table->unique(['attempt_id', 'question_id']);
        });

        Schema::table('player_progress', function (Blueprint $table) {
            $table->decimal('accuracy', 5, 2)->default(0)->after('completed_questions');
        });

        Schema::create('attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('points');
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_questions');

        Schema::table('player_progress', function (Blueprint $table) {
            $table->dropColumn('accuracy');
        });

        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->dropUnique(['attempt_id', 'question_id']);
            $table->dropColumn('marks_earned');
        });

        Schema::table('attempts', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }
};