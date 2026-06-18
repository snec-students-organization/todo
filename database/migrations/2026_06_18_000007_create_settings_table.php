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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('theme')->default('light');
            $table->integer('daily_streak')->default(0);
            $table->date('last_completed_date')->nullable();
            $table->integer('points')->default(0);
            $table->json('badges')->nullable();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('browser_notifications')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
