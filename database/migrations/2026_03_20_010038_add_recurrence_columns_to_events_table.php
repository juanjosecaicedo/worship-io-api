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
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_template')
                ->default(false)
                ->after('color')
                ->comment('true = evento plantilla padre de una recurrencia');
            $table->foreignId('recurrence_id')
                ->nullable()
                ->after('is_template')
                ->constrained('event_recurrences', 'id', 'events_recurrence_id_fk')
                ->nullOnDelete()
                ->comment('FK a la regla de recurrencia si es instancia materializada');
            $table->date('original_date')
                ->nullable()
                ->after('recurrence_id')
                ->comment('Fecha original de la ocurrencia dentro de la recurrencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign('events_recurrence_id_fk');
            $table->dropColumn(['is_template', 'recurrence_id', 'original_date']);
        });
    }
};
