<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'booking_id',
        'attended_on',
        'classes_count',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'attended_on' => 'date',
        'classes_count' => 'integer',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
