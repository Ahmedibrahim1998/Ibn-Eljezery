<?php

namespace App\Models;

use App\Enum\Lead\LeadSourceEnum;
use App\Enum\Lead\LeadStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'age_group',
        'level',
        'program',
        'course_id',
        'message',
        'source',
        'status',
    ];

    protected $casts = [
        'source' => LeadSourceEnum::class,
        'status' => LeadStatusEnum::class,
    ];

    /** The course the visitor picked (optional). */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
