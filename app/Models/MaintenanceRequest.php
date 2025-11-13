<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * PURPOSE: Handle the REQUEST/APPROVAL workflow for maintenance
     *
     * FLOW:
     * 1. User/Staff notices item needs maintenance -> Creates MaintenanceRequest
     * 2. Manager reviews request -> Approves/Rejects
     * 3. If approved -> Creates MaintenanceRecord (actual work)
     */

    protected $fillable = [
        'item_id',
        'requested_by',
        'reviewed_by',
        'title',
        'description',
        'priority', // low, medium, high, urgent
        'type', // preventive, corrective, inspection, emergency
        'status', // pending, approved, rejected, in_progress, completed, cancelled
        'urgency_reason',
        'preferred_date',
        'estimated_cost',
        'approval_notes',
        'rejection_reason',
        'requested_at',
        'reviewed_at',
        'maintenance_record_id', // Links to actual maintenance work
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'preferred_date' => 'date',
            'estimated_cost' => 'decimal:2',
        ];
    }

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function maintenanceRecord()
    {
        return $this->belongsTo(MaintenanceRecord::class);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function reject(User $reviewer, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
