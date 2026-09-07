<?php

namespace App\Models;

use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $exercise_category_id
 * @property string $name
 * @property string|null $description
 * @property string $image_path
 * @property int $difficulty
 * @property int|null $default_max_score
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ExerciseCategory $exerciseCategory
 */
#[Fillable(['exercise_category_id', 'description', 'image_path', 'difficulty', 'default_max_score', 'is_active'])]
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $appends = ['name', 'category_label'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'difficulty' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ExerciseCategory, $this>
     */
    public function exerciseCategory(): BelongsTo
    {
        return $this->belongsTo(ExerciseCategory::class);
    }

    /**
     * Dynamic name: {category-slug}-{difficulty}-{increment}.
     *
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(function (): string {
            $increment = static::where('exercise_category_id', $this->exercise_category_id)
                ->where('difficulty', $this->difficulty)
                ->where('id', '<=', $this->id)
                ->count();

            return $this->exerciseCategory->slug.'-'.$this->difficulty.'-'.$increment;
        });
    }

    /**
     * Human-readable category label.
     *
     * @return Attribute<string, never>
     */
    protected function categoryLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->exerciseCategory->name);
    }

    /**
     * Scope to only active exercises.
     *
     * @param  Builder<Exercise>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to only archived (inactive) exercises.
     *
     * @param  Builder<Exercise>  $query
     */
    public function scopeArchived(Builder $query): void
    {
        $query->where('is_active', false);
    }
}
