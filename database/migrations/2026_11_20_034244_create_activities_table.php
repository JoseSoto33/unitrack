<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['exam', 'midterm', 'quiz', 'workshop', 'presentation', 'report', 'essay', 'debate', 'discussion', 'other']);
            $table->enum('modality', ['individual', 'pair', 'group']);
            $table->date('assigned_on');
            $table->dateTime('due_at');
            $table->dateTime('delivered_at')->nullable();
            $table->decimal('percentage', 5, 2);
            $table->decimal('net_value', 5, 2);
            $table->decimal('grade', 5, 2)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
