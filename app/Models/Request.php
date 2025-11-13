<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // item_assignment, item_return, disposal, maintenance
        'status', // pending, approved, rejected, cancelled
        'item_id',
        'requested_date',
        'approved_by',
        'approved_date',
        'reason',
        'notes',
        'response_notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'datetime',
            'approved_date' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
