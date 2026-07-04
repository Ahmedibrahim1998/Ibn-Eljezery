<?php

namespace App\Models;

use App\Enum\Lead\LeadSourceEnum;
use App\Enum\Lead\LeadStatusEnum;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'age_group',
        'level',
        'program',
        'message',
        'source',
        'status',
    ];

    protected $casts = [
        'source' => LeadSourceEnum::class,
        'status' => LeadStatusEnum::class,
    ];
}
