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
        //Historial de notificaciones enviadas
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 60);
            $table->string('title', 150)->comment('Title of the notification');
            $table->text('body')->comment('Body of the notification');
            $table->json('data')->nullable()->comment('Data of the notification');
            $table->enum('channel', ['push', 'email', 'sms', 'in_app', 'whatsapp'])->comment('Channel of the notification');
            $table->timestamp('read_at')->nullable()->comment('Read at');
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
