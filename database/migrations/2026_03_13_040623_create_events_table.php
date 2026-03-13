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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['service', 'rehearsal', 'concert', 'meeting', 'other'])->default('service');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'postponed'])->default('scheduled');
            $table->text('gcal_event_id')->nullable();
            $table->string('color')->default('#459315');
            $table->timestamps();
        });

        Schema::create('event_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', [
                'band_director',
                'vocalist',
                'choir',
                'musician',
                'technician'
            ])->default('vocalist');
            $table->string('notes', 255)->nullable();
            $table->unique(['event_id', 'user_id', 'role']);
            $table->timestamps();
        });

        Schema::create('event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['confirmed', 'pending', 'declined', 'attended', 'absent'])->default('pending');
            $table->string('notes', 255)->nullable();
            $table->unique(['event_id', 'user_id']);
            $table->index('event_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_attendees');
        Schema::dropIfExists('event_roles');
        Schema::dropIfExists('events');
    }
};
