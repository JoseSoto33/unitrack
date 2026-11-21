<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use App\Enums\SubjectModality;
use Database\Factories\ClassDayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $schedule_id
 * @property DayOfWeek $day_of_week
 * @property SubjectModality|null $modality
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['schedule_id', 'day_of_week', 'modality'])]
class ClassDay extends Model
{
    /** @use HasFactory<ClassDayFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => DayOfWeek::class,
            'modality' => SubjectModality::class,
        ];
    }

    /**
     * A day without its own modality inherits the subject modality.
     *
     * @return Attribute<SubjectModality, never>
     */
    protected function effectiveModality(): Attribute
    {
        return Attribute::get(fn (): SubjectModality => $this->modality ?? $this->schedule->subject->modality);
    }

    /**
     * @return BelongsTo<Schedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * @return HasMany<TimeRange, $this>
     */
    public function timeRanges(): HasMany
    {
        return $this->hasMany(TimeRange::class);
    }
}
