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
        Schema::create('setlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->constrained('events')
                ->onDelete('cascade');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('name')->comment('e.g., Setlist 1, Time of Ministry');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->comment('1=active setlist, 0=draft/alternative');
            $table->timestamps();
            $table->index('event_id');
        });

        Schema::create('setlist_songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setlist_id')
                ->constrained('setlists')
                ->onDelete('cascade');
            $table->foreignId('group_song_id')->constrained('group_songs')->onDelete('cascade');
            $table->tinyInteger('order')->unsigned()->default(0)->comment('Position in the setlist');
            $table->string('key_override', 5)->nullable()->comment('Key override for this song in this setlist');
            $table->unsignedInteger('duration_override')->nullable()->comment('Duration override for this song in this setlist');
            $table->string('notes')->nullable()->comment('Example: Repeat chorus 3 times, end on vamp');
            $table->timestamps();
        });

        Schema::create('setlist_song_vocalists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setlist_song_id')->constrained('setlist_songs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('vocal_role', ['lead', 'harmony', 'choir'])->default('lead');
            $table->string('key_override', 5)->nullable()->comment('Key override for this song in this setlist');
            $table->string('notes', 255)->nullable()->comment('For example: Only on the bridge, sing in falsetto');
            $table->timestamps();
            // Un vocalista no puede tener el mismo rol duplicado en la misma canción
            $table->unique(['setlist_song_id', 'user_id', 'vocal_role']);
            $table->index('setlist_song_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setlist_song_vocalists');
        Schema::dropIfExists('setlist_songs');
        Schema::dropIfExists('setlists');
    }
};
