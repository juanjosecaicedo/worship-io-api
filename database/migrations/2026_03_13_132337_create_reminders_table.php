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
        //Recordatorios programados para eventos
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->integer('minutes_before')->comment('Minutes before the event to send the reminder');
            $table->enum('channel', ['push', 'email', 'sms', 'in_app', 'whatsapp', 'both'])->comment('Channel of the notification');
            $table->boolean('is_sent')->default(false)->comment('Whether the reminder has been sent');
            $table->timestamp('sent_at')->nullable()->comment('Sent at');
            $table->timestamps();
            $table->index('event_id');
            $table->index(['is_sent', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
