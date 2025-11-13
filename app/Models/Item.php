<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', // Unique item code
        'qr_code', // Generated QR code
        'name',
        'description',
        'category_id',
        'location_id',
        'serial_number',
        'model',
        'manufacturer',
        'purchase_date',
        'purchase_cost',
        'current_value',
        'condition', // excellent, good, fair, poor, damaged
        'status', // available, assigned, maintenance, disposed
        'warranty_expiry',
        'specifications', // JSON field
        'notes',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
            'purchase_cost' => 'decimal:2',
            'current_value' => 'decimal:2',
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
}
