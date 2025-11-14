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

            // Property Identification
            $table->string('property_number')->unique(); // Property # (e.g., 2021-06-086-164)
            $table->string('iar_number')->nullable(); // IAR # (Inspection and Acceptance Report)
            $table->string('fund_cluster')->nullable(); // Fund number (e.g., 164)
            $table->string('qr_code')->unique()->nullable(); // Generated QR code

            // Item Details
            $table->text('description'); // Full item description
            $table->string('brand')->nullable(); // Brand/Manufacturer (e.g., ACER)
            $table->string('model')->nullable(); // Model number (e.g., VERITON M4665G)
            $table->string('serial_number')->nullable(); // SN: Serial number
            $table->foreignId('category_id')->constrained()->onDelete('restrict');

            // Acquisition Information
            $table->decimal('acquisition_cost', 12, 2); // Acq. Cost (purchase price)
            $table->date('acquisition_date'); // Date Acquired
            $table->string('supplier')->nullable(); // Where purchased from

            // Assignment/Location
            $table->foreignId('accountable_person_id')->nullable()->constrained('users')->onDelete('set null'); // Acc. Person
            $table->foreignId('station_id')->nullable()->constrained('locations')->onDelete('set null'); // Station (department/unit)
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null'); // Physical location

            // Inventory Details
            $table->date('inventoried_date')->nullable(); // Date Inventoried
            $table->integer('estimated_useful_life')->nullable(); // In years
            $table->string('unit_of_measure')->default('unit'); // Unit (e.g., unit, set, piece)

            // Current Status
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])->default('good');
            $table->enum('status', ['available', 'assigned', 'maintenance', 'disposed'])->default('available');
            $table->decimal('current_value', 12, 2)->nullable(); // Depreciated value

            // Additional Information
            $table->json('specifications')->nullable(); // JSON field for technical specs
            $table->text('remarks')->nullable(); // Notes/remarks
            $table->string('image_path')->nullable(); // Photo of the item

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('property_number');
            $table->index('iar_number');
            $table->index('qr_code');
            $table->index('serial_number');
            $table->index('status');
            $table->index('condition');
            $table->index(['category_id', 'status']);
            $table->index(['accountable_person_id', 'status']);
            $table->index(['station_id', 'status']);
            $table->index('acquisition_date');
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
