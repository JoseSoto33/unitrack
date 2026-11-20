<?php

namespace App\Models;

use App\Enums\ActivityModality;
use App\Enums\ActivityType;
use Database\Factories\ActivityFactory;
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
 * @property string $title
 * @property ActivityType $type
 * @property ActivityModality $modality
 * @property Carbon $assigned_on
 * @property Carbon $due_at
 * @property Carbon|null $delivered_at
 * @property string $percentage
 * @property string $net_value
 * @property string|null $grade
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'subject_id',
    'title',
    'type',
    'modality',
    'assigned_on',
    'due_at',
    'delivered_at',
    'percentage',
    'grade',
])]
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'modality' => ActivityModality::class,
            'assigned_on' => 'date',
            'due_at' => 'datetime',
            'delivered_at' => 'datetime',
            'percentage' => 'decimal:2',
            'net_value' => 'decimal:2',
            'grade' => 'decimal:2',
        ];
    }

    /**
     * net_value is derived from the subject scale and the activity weight.
     */
    protected static function booted(): void
    {
        static::saving(function (Activity $activity): void {
            $activity->net_value = number_format(
                (float) $activity->subject->max_grade * (float) $activity->percentage / 100,
                2,
                '.',
                '',
            );
        });
    }

    /**
     * @return Attribute<string, never>
     */
    protected function deliveryStatus(): Attribute
    {
        return Attribute::get(fn (): string => match (true) {
            $this->delivered_at !== null => 'Entregada',
            $this->due_at->isFuture() => 'Pendiente',
            default => 'Vencida',
        });
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
