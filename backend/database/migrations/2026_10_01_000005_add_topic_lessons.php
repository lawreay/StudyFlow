<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->string('lesson_title')->nullable()->after('description');
            $table->text('lesson_summary')->nullable()->after('lesson_title');
            $table->json('lesson_content')->nullable()->after('lesson_summary');
        });

        Schema::create('topic_lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->timestamps();
            $table->unique(['user_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_lesson_completions');

        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn(['lesson_title', 'lesson_summary', 'lesson_content']);
        });
    }
};
