<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use App\Enums\SubjectModality;
use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $subject_id
 * @property SubjectModality|null $modality
 * @property DayOfWeek $day_of_week
 * @property string $starts_at
 * @property string $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'subject_id', 'modality', 'day_of_week', 'starts_at', 'ends_at'])]
class Schedule extends Model
{
    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'modality' => SubjectModality::class,
            'day_of_week' => DayOfWeek::class,
        ];
    }

    /**
     * A block without its own modality inherits the subject modality.
     *
     * @return Attribute<SubjectModality, never>
     */
    protected function effectiveModality(): Attribute
    {
        return Attribute::get(fn (): SubjectModality => $this->modality ?? $this->subject->modality);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
