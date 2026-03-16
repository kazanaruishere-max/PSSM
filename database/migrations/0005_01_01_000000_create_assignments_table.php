<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamp('deadline');
            $table->integer('max_score')->default(100);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('deadline');
            $table->index(['class_id', 'subject_id']);
            $table->index('is_published');
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->integer('score')->nullable();
            $table->text('feedback')->nullable();
            $table->json('ai_feedback')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_late')->default(false);
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['assignment_id', 'student_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignments');
    }
};
