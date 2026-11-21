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
        Schema::create('time_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_day_id')->constrained()->cascadeOnDelete();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_ranges');
    }
};
