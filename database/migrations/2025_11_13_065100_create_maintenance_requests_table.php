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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('maintenance_record_id')->nullable(); // No FK constraint to avoid circular dependency

            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('type', ['preventive', 'corrective', 'inspection', 'emergency'])->default('corrective');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed', 'cancelled'])->default('pending');

            $table->text('urgency_reason')->nullable();
            $table->date('preferred_date')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
            $table->index(['item_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
