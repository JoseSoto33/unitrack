<?php

namespace App\Models;

use Database\Factories\TimeRangeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $class_day_id
 * @property string $starts_at
 * @property string $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['class_day_id', 'starts_at', 'ends_at'])]
class TimeRange extends Model
{
    /** @use HasFactory<TimeRangeFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ClassDay, $this>
     */
    public function classDay(): BelongsTo
    {
        return $this->belongsTo(ClassDay::class);
    }
}
