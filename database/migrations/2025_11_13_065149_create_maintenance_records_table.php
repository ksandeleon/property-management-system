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
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('maintenance_request_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('requested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            $table->enum('type', ['preventive', 'corrective', 'inspection'])->default('corrective');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->text('description');
            $table->date('scheduled_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->decimal('labor_hours', 8, 2)->nullable();
            $table->json('parts_used')->nullable(); // [{part: 'name', quantity: 1, cost: 100}]

            $table->text('work_performed')->nullable();
            $table->text('technician_notes')->nullable();
            $table->enum('outcome', ['successful', 'partial', 'failed'])->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->json('attachments')->nullable(); // [file paths]

            $table->timestamps();

            $table->index('status');
            $table->index(['item_id', 'status']);
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
