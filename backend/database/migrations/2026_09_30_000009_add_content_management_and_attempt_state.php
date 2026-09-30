<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->string('difficulty', 16)->default('Medium')->after('points');
        });

        Schema::table('attempts', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('topic_id');
            $table->unsignedInteger('current_question_index')->default(0)->after('total_questions');
            $table->unsignedInteger('duration_seconds')->default(0)->after('current_question_index');
        });

        DB::table('attempts')->whereNull('started_at')->update(['started_at' => DB::raw('created_at')]);

        Schema::table('attempt_questions', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('points');
        });
    }

    public function down(): void
    {
        Schema::table('attempt_questions', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('attempts', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'current_question_index', 'duration_seconds']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('difficulty');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};