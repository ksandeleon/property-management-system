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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');

            $table->timestamp('assigned_at')->useCurrent();
            $table->date('expected_return_date')->nullable();
            $table->timestamp('actual_return_date')->nullable();

            $table->enum('status', ['active', 'returned', 'overdue', 'cancelled'])->default('active');
            $table->string('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->enum('return_condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])->nullable();
            $table->text('return_notes')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['item_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('expected_return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
