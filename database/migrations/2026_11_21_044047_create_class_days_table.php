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
        Schema::create('class_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->enum('modality', ['in_person', 'online'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_days');
    }
};
