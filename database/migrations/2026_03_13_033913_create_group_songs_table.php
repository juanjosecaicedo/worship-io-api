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
        Schema::create('group_songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('global_song_id')->nullable()->constrained('global_songs')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->comment("Custom title override for this group's version of the song");
            $table->string('author')->nullable()->comment('Original author or composer of the song');
            $table->string('custom_key', 10)->nullable()->comment('Custom key used by the group (e.g. G, Bb, F#)');
            $table->smallInteger('custom_tempo')->nullable()->comment('Custom tempo in BPM used by the group');
            $table->string('custom_time_signature', 5)->nullable()->comment('Custom time signature used by the group (e.g. 4/4, 3/4, 6/8)');
            $table->string('genre', 50)->nullable()->comment('Musical genre of the song (e.g. worship, gospel, contemporary)');
            $table->json('tags')->nullable()->comment('Group-specific tags stored as a JSON array');
            $table->string('youtube_url')->nullable()->comment('YouTube video URL for the song reference');
            $table->boolean('is_public')->default(false)->comment('Whether the song is visible to other groups (1 = public, 0 = private)');
            $table->json('sections_order')->nullable()->comment('Custom order of song sections as a JSON array of section IDs (e.g. [3,1,2,3,4])');
            $table->timestamps();
            $table->index('group_id');
        });

        Schema::create('group_song_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_song_id')->constrained('group_songs')->onDelete('cascade')->comment('Foreign key to the group song this section belongs to');
            $table->foreignId('global_section_id')->nullable()->constrained('global_song_sections')->onDelete('set null')->comment('Foreign key to the global section; null if this is a custom group-only section');
            $table->enum('type', ['intro', 'verse', 'pre_chorus', 'chorus', 'bridge', 'outro', 'instrumental', 'tag', 'vamp'])->comment('Type of song section');
            $table->string('label', 50)->nullable()->comment('Human-readable label for the section (e.g. Verse 1, Chorus, Bridge)');
            $table->text('lyrics')->nullable()->comment("Custom lyrics for this group's version of the section");
            $table->json('chords')->nullable()->comment("Chord chart in the group's key stored as JSON");
            $table->tinyInteger('order')->unsigned()->default(0)->comment('Display order of this section within the song');
            $table->timestamps();
            $table->index('group_song_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_song_sections');
        Schema::dropIfExists('group_songs');
    }
};
