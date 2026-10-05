<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_challenge_id')->constrained()->cascadeOnDelete();
            $table->string('project_url', 2048);
            $table->text('notes')->nullable();
            $table->string('status', 32)->default('submitted');
            $table->text('feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'project_challenge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_submissions');
    }
};
