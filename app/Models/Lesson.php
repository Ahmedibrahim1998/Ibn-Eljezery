<?php

namespace App\Models;

use App\Enum\Learning\ContentStatusEnum;
use App\Utilities\Traits\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A lesson inside a subject. It goes through the sharia-review lifecycle
 * (ContentStatusEnum) and only reaches the app once published.
 */
class Lesson extends Model
{
    use HasLocalizedAttributes;
    use SoftDeletes;

    protected $fillable = [
        'learning_subject_id',
        'title_ar',
        'title_en',
        'content_ar',
        'content_en',
        'audio_url',
        'video_url',
        'duration_minutes',
        'status',
        'author_id',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'published_at',
        'version',
        'sort_order',
    ];

    protected $casts = [
        'status' => ContentStatusEnum::class,
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'duration_minutes' => 'integer',
        'version' => 'integer',
        'sort_order' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(LearningSubject::class, 'learning_subject_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatusEnum::PUBLISHED->value);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
