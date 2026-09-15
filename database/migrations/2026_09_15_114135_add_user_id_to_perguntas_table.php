<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perguntas', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('evento_id')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('perguntas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};