<?php

namespace App\Models;

use App\Enums\SessionStatus;
use Database\Factories\TrainingSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $school_id
 * @property Carbon $date
 * @property SessionStatus $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['date', 'status', 'notes'])]
class TrainingSession extends Model
{
    /** @use HasFactory<TrainingSessionFactory> */
    use HasFactory;

    protected $table = 'training_sessions';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => SessionStatus::class,
        ];
    }

    /**
     * Get the school this session belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the pupils participating in this session.
     *
     * @return BelongsToMany<Pupil, $this>
     */
    public function pupils(): BelongsToMany
    {
        return $this->belongsToMany(Pupil::class, 'exercise_assignments', 'session_id')
            ->distinct();
    }

    /**
     * Get the exercises added to this session.
     *
     * @return BelongsToMany<Exercise, $this>
     */
    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'session_exercise', 'session_id')
            ->withTimestamps();
    }

    /**
     * Get the exercise assignments for this session.
     *
     * @return HasMany<ExerciseAssignment, $this>
     */
    public function exerciseAssignments(): HasMany
    {
        return $this->hasMany(ExerciseAssignment::class, 'session_id');
    }
}
