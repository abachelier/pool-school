<?php

namespace App\Models;

use App\Enums\ExerciseCategory;
use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property ExerciseCategory $category
 * @property string|null $description
 * @property string $image_path
 * @property int $difficulty
 * @property int|null $default_max_score
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['category', 'description', 'image_path', 'difficulty', 'default_max_score', 'is_active'])]
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
            'category' => ExerciseCategory::class,
            'difficulty' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Dynamic name: {category-slug}-{difficulty}-{increment}.
     *
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(function (): string {
            $increment = static::where('category', $this->category)
                ->where('difficulty', $this->difficulty)
                ->where('id', '<=', $this->id)
                ->count();

            return $this->category->slug().'-'.$this->difficulty.'-'.$increment;
        });
    }

    /**
     * Human-readable category label.
     *
     * @return Attribute<string, never>
     */
    protected function categoryLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->category->label());
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
