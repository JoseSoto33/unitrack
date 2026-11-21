<?php

namespace App\Models;

use App\Enums\SubjectModality;
use App\Enums\SubjectStatus;
use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $period_id
 * @property string $name
 * @property string|null $professor
 * @property int $credits
 * @property SubjectModality $modality
 * @property string $max_grade
 * @property string $min_passing_grade
 * @property string|null $final_grade
 * @property SubjectStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'period_id',
    'name',
    'professor',
    'credits',
    'modality',
    'max_grade',
    'min_passing_grade',
    'final_grade',
    'status',
])]
class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'modality' => SubjectModality::class,
            'status' => SubjectStatus::class,
            'max_grade' => 'decimal:2',
            'min_passing_grade' => 'decimal:2',
            'final_grade' => 'decimal:2',
        ];
    }

    /**
     * Suggest the status automatically when the final grade is recorded.
     */
    protected static function booted(): void
    {
        static::saving(function (Subject $subject): void {
            if ($subject->final_grade === null) {
                return;
            }

            if ($subject->isDirty('final_grade') || $subject->isDirty('min_passing_grade')) {
                $subject->status = (float) $subject->final_grade >= (float) $subject->min_passing_grade
                    ? SubjectStatus::Passed
                    : SubjectStatus::Failed;
            }
        });
    }

    /**
     * @return BelongsTo<Period, $this>
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * @return HasMany<Schedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
