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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('professor')->nullable();
            $table->tinyInteger('credits');
            $table->enum('modality', ['in_person', 'online', 'hybrid']);
            $table->decimal('max_grade', 5, 2);
            $table->decimal('min_passing_grade', 5, 2);
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->enum('status', ['pending', 'in_progress', 'passed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
