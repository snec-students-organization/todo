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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['High', 'Medium', 'Low'])->default('Medium');
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Cancelled'])->default('Pending');
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            $table->enum('repeat_type', ['None', 'Daily', 'Weekly', 'Monthly', 'Yearly'])->default('None');
            $table->integer('estimated_minutes')->nullable();
            $table->timestamps();

            // Indexes for fast querying & filtering
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
