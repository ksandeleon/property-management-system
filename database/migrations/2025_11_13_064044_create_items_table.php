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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Unique item code
            $table->string('qr_code')->unique()->nullable(); // Generated QR code
            $table->string('name');
            $table->text('description')->nullable();

            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('location_id')->constrained()->onDelete('restrict');

            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();

            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();

            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])->default('good');
            $table->enum('status', ['available', 'assigned', 'maintenance', 'disposed'])->default('available');

            $table->date('warranty_expiry')->nullable();
            $table->json('specifications')->nullable(); // JSON field for technical specs
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('qr_code');
            $table->index('status');
            $table->index(['category_id', 'status']);
            $table->index(['location_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
