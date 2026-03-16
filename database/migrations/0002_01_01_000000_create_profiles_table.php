<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_id_number', 50)->unique();
            $table->date('date_of_birth')->nullable();
            $table->string('parent_name')->nullable();
            $table->text('parent_phone')->nullable();
            $table->text('parent_email')->nullable();
            $table->text('address')->nullable();
            $table->integer('enrollment_year')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('teacher_id_number', 50)->unique();
            $table->string('specialization', 100)->nullable();
            $table->text('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
        Schema::dropIfExists('student_profiles');
    }
};
