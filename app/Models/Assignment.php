<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'expected_return_date',
        'actual_return_date',
        'status', // active, returned, overdue, cancelled
        'purpose',
        'notes',
        'return_condition',
        'return_notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'expected_return_date' => 'date',
            'actual_return_date' => 'datetime',
        ];
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active'
            && $this->expected_return_date
            && $this->expected_return_date->isPast();
    }
}