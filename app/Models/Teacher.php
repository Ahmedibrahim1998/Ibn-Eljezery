<?php

namespace App\Models;

use App\Utilities\Traits\HasListingScopes;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasListingScopes;
    use HasLocalizedAttributes;

    protected $fillable = [
        'user_id',
        'name_ar',
        'name_en',
        'certification_ar',
        'certification_en',
        'description_ar',
        'description_en',
        'badge_ar',
        'badge_en',
        'photo',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    /** Today's attendance row, if any. */
    public function todayAttendance(): ?TeacherAttendance
    {
        return $this->attendances()->whereDate('attended_on', today())->first();
    }

    /** Total days the teacher was present. */
    public function attendedDays(): int
    {
        return $this->attendances()->whereNotNull('checked_in_at')->count();
    }
}
