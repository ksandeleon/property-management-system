<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Property Identification
        'property_number',      // Property # (e.g., 2021-06-086-164)
        'iar_number',           // IAR # (Inspection and Acceptance Report)
        'fund_cluster',         // Fund number (e.g., 164)
        'qr_code',             // Generated QR code

        // Item Details
        'description',         // Full item description
        'brand',              // Brand/Manufacturer (e.g., ACER)
        'model',              // Model number (e.g., VERITON M4665G)
        'serial_number',      // SN: Serial number
        'category_id',        // Category/Type

        // Acquisition Information
        'acquisition_cost',   // Acq. Cost (purchase price)
        'acquisition_date',   // Date Acquired
        'supplier',           // Where purchased from

        // Assignment/Location
        'accountable_person_id',  // Acc. Person: Foreign key to users
        'station_id',            // Station: Foreign key to locations (department/unit)
        'location_id',           // Physical location

        // Inventory Details
        'inventoried_date',   // Date Inventoried
        'estimated_useful_life', // In years
        'unit_of_measure',    // Unit (e.g., unit, set, piece)

        // Current Status
        'condition',          // excellent, good, fair, poor, damaged
        'status',            // available, assigned, maintenance, disposed
        'current_value',     // Depreciated value

        // Additional Information
        'specifications',    // JSON field for technical specs
        'remarks',          // Notes/remarks
        'image_path',       // Photo of the item
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'inventoried_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'current_value' => 'decimal:2',
            'estimated_useful_life' => 'integer',
            'specifications' => 'array',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function accountablePerson()
    {
        return $this->belongsTo(User::class, 'accountable_person_id');
    }

    public function station()
    {
        return $this->belongsTo(Location::class, 'station_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(Assignment::class)
            ->where('status', 'active')
            ->latest();
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function pendingMaintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class)
            ->where('status', 'pending');
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function disposalRecord()
    {
        return $this->hasOne(DisposalRecord::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    public function hasPendingMaintenance(): bool
    {
        return $this->maintenanceRequests()
            ->where('status', 'pending')
            ->exists();
    }

    public function isUnderMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    public function getMaintenanceCountAttribute(): int
    {
        return $this->maintenanceRecords()->count();
    }

    /**
     * Get the full property tag number
     */
    public function getFullPropertyNumberAttribute(): string
    {
        return $this->property_number ?? 'N/A';
    }

    /**
     * Get the depreciated value based on acquisition cost and useful life
     */
    public function calculateDepreciation(): float
    {
        if (!$this->acquisition_cost || !$this->acquisition_date || !$this->estimated_useful_life) {
            return $this->acquisition_cost ?? 0;
        }

        $yearsElapsed = now()->diffInYears($this->acquisition_date);
        $annualDepreciation = $this->acquisition_cost / $this->estimated_useful_life;
        $totalDepreciation = $annualDepreciation * $yearsElapsed;

        return max(0, $this->acquisition_cost - $totalDepreciation);
    }

    /**
     * Get years since acquisition
     */
    public function getAgeInYearsAttribute(): int
    {
        return $this->acquisition_date ? now()->diffInYears($this->acquisition_date) : 0;
    }

    /**
     * Check if item needs inventory update
     */
    public function needsInventoryUpdate(): bool
    {
        if (!$this->inventoried_date) {
            return true;
        }

        // Needs update if last inventoried more than 1 year ago
        return now()->diffInMonths($this->inventoried_date) > 12;
    }
}
