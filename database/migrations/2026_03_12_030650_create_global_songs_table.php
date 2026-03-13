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
        Schema::create('global_songs', function (Blueprint $table) {
            $table->id()->comment('Unique identifier for the song');
            $table->string('title')->comment('e.g., Oceans, Reckless Love');
            $table->string('author')->comment('e.g., Hillsong United, Bethel Music');
            $table->string('original_key')->comment('e.g., C, G, Am, Bb');
            $table->unsignedInteger('tempo')->comment('BPM. e.g., 72, 120');
            $table->string('time_signature')->comment('e.g., 4/4, 3/4, 6/8');
            $table->unsignedInteger('duration_seconds')->comment('Duration in seconds');
            $table->string('genre')->comment('e.g., Contemporary, Hymn, Gospel');
            $table->json('tags')->comment('JSON: ["worship", "slow", "communion"]');
            $table->string('youtube_url')->nullable()->comment('YouTube URL');
            $table->string('spotify_url')->nullable()->comment('Spotify URL');
            $table->boolean('is_active')->default(true)->comment('Active status of the song');
            $table->foreignId('created_by')->nullable()->comment('FK users (NULL = system)')->constrained('users');
            $table->timestamps();
        });

        Schema::create('global_song_sections', function (Blueprint $table) {
            $table->id()->comment('Unique identifier for the section');
            $table->foreignId('global_song_id')->comment('FK global_songs')->constrained('global_songs')->onDelete('cascade');
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
            $table->string('label')->comment('e.g., Verse 1, Chorus, Bridge');
            $table->text('lyrics')->comment('Section lyrics');
            $table->json('chords')->comment('JSON: [{"beat":1,"chord":"C"},{"beat":3,"chord":"Am"}]');
            $table->unsignedInteger('order')->default(0)->comment('Position in the song');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_song_sections');
        Schema::dropIfExists('global_songs');
    }
};
