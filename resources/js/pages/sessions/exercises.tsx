import { Head, Link, router } from '@inertiajs/react';
import { Check, ChevronLeft } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { show as sessionsShow } from '@/routes/schools/sessions';
import { syncExercises } from '@/actions/App/Http/Controllers/TrainingSessionController';
import type { Exercise, School, TrainingSession } from '@/types';

type PageProps = {
    school: School;
    session: TrainingSession;
    exercises: Exercise[];
    selectedExerciseIds: number[];
};

export default function SessionExercises({
    school,
    session,
    exercises,
    selectedExerciseIds,
}: PageProps) {
    const [selected, setSelected] = useState<number[]>(selectedExerciseIds);
    const [processing, setProcessing] = useState(false);

    function toggleExercise(exerciseId: number) {
        setSelected((prev) =>
            prev.includes(exerciseId)
                ? prev.filter((id) => id !== exerciseId)
                : [...prev, exerciseId],
        );
    }

    function handleSave() {
        setProcessing(true);
        router.post(
            syncExercises.url({ school: school.id, session: session.id }),
            { exercise_ids: selected },
            {
                onFinish: () => setProcessing(false),
            },
        );
    }

    const hasChanges =
        JSON.stringify([...selected].sort()) !==
        JSON.stringify([...selectedExerciseIds].sort());

    return (
        <>
            <Head title="Choose Exercises" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Choose Exercises"
                        description="Select exercises for this session."
                    />

                    <div className="flex items-center gap-2">
                        <Button variant="outline" asChild>
                            <Link
                                href={sessionsShow.url({
                                    school: school.id,
                                    session: session.id,
                                })}
                            >
                                <ChevronLeft className="mr-1 size-4" />
                                Back
                            </Link>
                        </Button>
                        <Button
                            onClick={handleSave}
                            disabled={processing || !hasChanges}
                        >
                            {processing ? 'Saving...' : 'Save Selection'}
                        </Button>
                    </div>
                </div>

                {exercises.length === 0 ? (
                    <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center">
                        No exercises available. Create some exercises first.
                    </div>
                ) : (
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {exercises.map((exercise) => {
                            const isSelected = selected.includes(exercise.id);

                            return (
                                <button
                                    key={exercise.id}
                                    type="button"
                                    onClick={() => toggleExercise(exercise.id)}
                                    className={`relative overflow-hidden rounded-lg border text-left transition-colors ${
                                        isSelected
                                            ? 'border-primary ring-primary ring-2'
                                            : 'hover:bg-accent'
                                    }`}
                                >
                                    {isSelected && (
                                        <div className="bg-primary text-primary-foreground absolute top-2 right-2 z-10 flex size-6 items-center justify-center rounded-full">
                                            <Check className="size-4" />
                                        </div>
                                    )}
                                    {exercise.image_path && (
                                        <img
                                            src={`/storage/${exercise.image_path}`}
                                            alt={exercise.name}
                                            className="h-36 w-full object-cover"
                                        />
                                    )}
                                    <div className="p-4">
                                        <h3 className="font-medium">
                                            {exercise.name}
                                        </h3>
                                        {exercise.category_label && (
                                            <Badge
                                                variant="outline"
                                                className="mt-1"
                                            >
                                                {exercise.category_label}
                                            </Badge>
                                        )}
                                        <p className="text-muted-foreground mt-1 text-sm">
                                            Difficulty: {exercise.difficulty}/5
                                        </p>
                                        {exercise.description && (
                                            <p className="text-muted-foreground mt-1 truncate text-sm">
                                                {exercise.description}
                                            </p>
                                        )}
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                )}
            </div>
        </>
    );
}
