<?php

namespace App\Models;

use App\Enums\ActivityModality;
use App\Enums\ActivityType;
use Database\Factories\ActivityFactory;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Saade\FilamentFullCalendar\Contracts\Eventable;
use Saade\FilamentFullCalendar\Data\EventData;

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
class Activity extends Model implements Eventable
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    public const TYPE_COLORS = [
        'exam' => '#dc2626',
        'midterm' => '#ea580c',
        'quiz' => '#ca8a04',
        'workshop' => '#16a34a',
        'presentation' => '#0d9488',
        'report' => '#2563eb',
        'essay' => '#7c3aed',
        'debate' => '#db2777',
        'discussion' => '#0891b2',
        'other' => '#6b7280',
    ];

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

    public function toCalendarEvent(): EventData
    {
        return EventData::make()
            ->id($this->getKey())
            ->title($this->title.' — '.$this->subject->name)
            ->start($this->due_at)
            ->end($this->due_at)
            ->color(Color::hex(self::TYPE_COLORS[$this->type->value]))
            ->extendedProps([
                'type' => $this->type->getLabel(),
                'subject' => $this->subject->name,
                'status' => $this->delivery_status,
                'percentage' => $this->percentage,
                'grade' => $this->grade,
            ]);
    }
}
