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
        Schema::create('song_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('group_song_id')->constrained('group_songs')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('group_song_sections')->onDelete('cascade');
            $table->enum('type', [
                'intro',
                'verse',
                'pre_chorus',
                'chorus',
                'bridge',
                'outro',
                'instrumental',
                'tag',
                'vamp'
            ])->comment('Section type');
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_notes');
    }
};
