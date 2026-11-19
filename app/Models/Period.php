<?php

namespace App\Models;

use App\Enums\PeriodType;
use Database\Factories\PeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $career_id
 * @property PeriodType $type
 * @property int $number
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property bool $is_current
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'career_id', 'type', 'number', 'starts_on', 'ends_on', 'is_current'])]
class Period extends Model
{
    /** @use HasFactory<PeriodFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PeriodType::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /**
     * Keep a single current period per user and career.
     */
    protected static function booted(): void
    {
        static::saving(function (Period $period): void {
            if (! $period->is_current) {
                return;
            }

            static::query()
                ->where('user_id', $period->user_id)
                ->where('career_id', $period->career_id)
                ->when($period->exists, fn ($query) => $query->whereKeyNot($period->getKey()))
                ->update(['is_current' => false]);
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
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }
}
