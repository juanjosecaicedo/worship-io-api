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
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')
                ->nullable()
                ->unique()
                ->after('email')
                ->comment('ID único de Google del usuario');
            $table->boolean('has_password')
                ->default(true)
                ->after('password')
                ->comment('false si solo se registró con Google');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'has_password']);
        });
    }
};
