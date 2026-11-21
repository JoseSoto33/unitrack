<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // MySQL no permite soltar el índice compuesto antes que la columna (el FK de
            // user_id lo usa como prefijo, error 1553) y lo recompone solo al soltarla;
            // SQLite exige soltar el índice antes que la columna que forma parte de él.
            if (DB::getDriverName() !== 'mysql') {
                $table->dropIndex(['user_id', 'day_of_week']);
            }

            $table->dropColumn(['day_of_week', 'modality', 'starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->tinyInteger('day_of_week')->default(1);
            $table->enum('modality', ['in_person', 'online'])->nullable();
            $table->time('starts_at')->default('07:00');
            $table->time('ends_at')->default('08:00');
            $table->index(['user_id', 'day_of_week']);
        });
    }
};
