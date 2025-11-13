<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'requested_by',
        'approved_by',
        'executed_by',
        'reason',
        'method', // sale, donation, recycle, destroy
        'status', // pending, approved, rejected, executed
        'requested_date',
        'approved_date',
        'executed_date',
        'sale_amount',
        'notes',
        'documentation', // JSON for supporting documents
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'approved_date' => 'datetime',
            'executed_date' => 'datetime',
            'sale_amount' => 'decimal:2',
            'documentation' => 'array',
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

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function executedBy()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}