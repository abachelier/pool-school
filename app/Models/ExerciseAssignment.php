<?php

namespace App\Models;

use Database\Factories\ExerciseAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $session_id
 * @property int $pupil_id
 * @property int $exercise_id
 * @property int|null $score
 * @property int|null $max_score
 * @property string|null $notes
 * @property bool $is_completed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['session_id', 'pupil_id', 'exercise_id', 'score', 'max_score', 'notes', 'is_completed'])]
class ExerciseAssignment extends Model
{
    /** @use HasFactory<ExerciseAssignmentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }

    /**
     * Get the training session this assignment belongs to.
     */
    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'session_id');
    }

    /**
     * Get the pupil this assignment is for.
     */
    public function pupil(): BelongsTo
    {
        return $this->belongsTo(Pupil::class);
    }

    /**
     * Get the exercise assigned.
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
