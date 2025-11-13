<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'requested_by',
        'assigned_to',
        'type', // preventive, corrective, inspection
        'status', // pending, in_progress, completed, cancelled
        'priority', // low, medium, high, urgent
        'description',
        'scheduled_date',
        'completed_date',
        'cost',
        'notes',
        'outcome',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_date' => 'datetime',
            'cost' => 'decimal:2',
        ];
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
