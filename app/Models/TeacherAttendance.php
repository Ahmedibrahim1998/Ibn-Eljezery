<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One day's check-in/out record for a teacher (marked by the supervisor). */
class TeacherAttendance extends Model
{
    protected $fillable = [
        'teacher_id',
        'attended_on',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'attended_on' => 'date',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
