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
        Schema::create('disposal_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('executed_by')->nullable()->constrained('users')->onDelete('set null');

            $table->text('reason');
            $table->enum('method', ['sale', 'donation', 'recycle', 'destroy'])->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'executed'])->default('pending');

            $table->date('requested_date');
            $table->timestamp('approved_date')->nullable();
            $table->timestamp('executed_date')->nullable();

            $table->decimal('sale_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->json('documentation')->nullable(); // JSON for supporting documents

            $table->timestamps();

            $table->index('status');
            $table->index(['item_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposal_records');
    }
};
