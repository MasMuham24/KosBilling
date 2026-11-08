<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'name',
        'whatsapp_number',
        'checkin_date',
        'due_date_day',
        'status',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'due_date_day' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }
}
