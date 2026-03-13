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
        Schema::create('user_song_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('group_song_id')->constrained('group_songs')->onDelete('cascade');
            $table->string('preferred_key')->comment('e.g., C, G, Am, Bb');
            $table->integer('capo')->default(0)->comment('Capo position');
            $table->string('notes')->nullable()->comment('For example: A half a tone down looks better on me.');
            $table->unique(['user_id', 'group_song_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_song_keys');
    }
};
