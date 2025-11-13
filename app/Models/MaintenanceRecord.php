<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model
{
    use HasFactory;

    /**
     * PURPOSE: Record ACTUAL maintenance work performed
     * Created AFTER a MaintenanceRequest is approved
     */

    protected $fillable = [
        'item_id',
        'maintenance_request_id', // Optional - links to request if came from request
        'requested_by',
        'assigned_to',
        'type', // preventive, corrective, inspection
        'status', // scheduled, in_progress, completed, cancelled
        'priority', // low, medium, high, urgent
        'description',
        'scheduled_date',
        'started_at',
        'completed_at',
        'actual_cost',
        'labor_hours',
        'parts_used', // JSON: [{part: 'name', quantity: 1, cost: 100}]
        'work_performed',
        'technician_notes',
        'outcome', // successful, partial, failed
        'next_maintenance_date',
        'attachments', // JSON: [file paths]
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'actual_cost' => 'decimal:2',
            'labor_hours' => 'decimal:2',
            'parts_used' => 'array',
            'attachments' => 'array',
            'next_maintenance_date' => 'date',
        ];
    }

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Helper Methods
    public function complete(string $workPerformed, string $outcome, array $partsUsed = []): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'work_performed' => $workPerformed,
            'outcome' => $outcome,
            'parts_used' => $partsUsed,
            'actual_cost' => $this->calculateTotalCost($partsUsed),
        ]);
    }

    private function calculateTotalCost(array $partsUsed): float
    {
        return collect($partsUsed)->sum('cost') ?? 0;
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
