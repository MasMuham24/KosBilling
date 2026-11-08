<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'room_id',
        'bill_month',
        'base_amount',
        'additional_charge',
        'total_amount',
        'status',
        'due_date',
        'snap_token',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'additional_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
