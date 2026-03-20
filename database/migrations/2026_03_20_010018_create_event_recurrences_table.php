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
        Schema::create('event_recurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete()
                ->comment('Evento plantilla/padre de esta recurrencia');
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])
                ->comment('Frecuencia base: diario, semanal o mensual');
            $table->tinyInteger('interval')
                ->unsigned()
                ->default(1)
                ->comment('Cada cuántos períodos se repite. 1=cada semana, 2=cada 2 semanas');
            $table->json('days_of_week')
                ->nullable()
                ->comment('Para weekly: días de la semana [0=Dom,1=Lun,...,6=Sáb]. Ej: [0,2,4]');
            $table->tinyInteger('day_of_month')
                ->unsigned()
                ->nullable()
                ->comment('Para monthly: día del mes. Ej: 15 = el día 15 de cada mes');
            $table->date('starts_at')
                ->comment('Fecha de inicio de la recurrencia');
            $table->date('ends_at')
                ->nullable()
                ->comment('Fecha de fin. NULL = sin fecha de fin');
            $table->smallInteger('occurrences_limit')
                ->unsigned()
                ->nullable()
                ->comment('Número máximo de ocurrencias. NULL = sin límite');
            $table->timestamps();
            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_recurrences');
    }
};
