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
        Schema::create('event_recurrence_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_recurrence_id')
                ->constrained('event_recurrences', 'id', 'event_recurrence_id')
                ->onDelete("cascade")
                ->comment('Regla de recurrencia a la que aplica esta excepción');
            $table->date('original_date')
                ->comment('Fecha original de la ocurrencia que se modifica o cancela');
            $table->enum('type', ['cancelled', 'modified'])
                ->comment('cancelled = esa ocurrencia no existe, modified = fue reemplazada por otro evento');
            $table->foreignId('event_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->comment('Si type=modified: el evento real que reemplaza esta ocurrencia');
            $table->timestamps();
            $table->unique(['event_recurrence_id', 'original_date'], 'ere_recurrence_id_original_date_unique');
            $table->index('event_recurrence_id', 'ere_event_recurrence_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_recurrence_exceptions');
    }
};
