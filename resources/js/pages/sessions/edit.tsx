import { Head, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { update } from '@/routes/schools/sessions';
import type {
    Exercise,
    ExerciseAssignment,
    Pupil,
    School,
    TrainingSession,
} from '@/types';

type PageProps = {
    school: School;
    session: TrainingSession;
    pupils: Pupil[];
    exercises: Exercise[];
    existingAssignments: ExerciseAssignment[];
};

export default function SessionsEdit({
    school,
    session,
    pupils,
    exercises,
    existingAssignments,
}: PageProps) {
    const { data, setData, put, processing, errors } = useForm({
        date: session.date,
        notes: session.notes ?? '',
        pupil_ids: [
            ...new Set(existingAssignments.map((a) => a.pupil_id)),
        ] as number[],
        assignments: existingAssignments.map((a) => ({
            pupil_id: a.pupil_id,
            exercise_id: a.exercise_id,
            result_value: a.result_value ?? '',
            notes: a.notes ?? '',
            is_completed: a.is_completed,
        })),
    });

    function togglePupil(pupilId: number) {
        if (data.pupil_ids.includes(pupilId)) {
            setData({
                ...data,
                pupil_ids: data.pupil_ids.filter((id) => id !== pupilId),
                assignments: data.assignments.filter(
                    (a) => a.pupil_id !== pupilId,
                ),
            });
        } else {
            setData('pupil_ids', [...data.pupil_ids, pupilId]);
        }
    }

    function toggleAssignment(pupilId: number, exerciseId: number) {
        const exists = data.assignments.some(
            (a) => a.pupil_id === pupilId && a.exercise_id === exerciseId,
        );

        if (exists) {
            setData(
                'assignments',
                data.assignments.filter(
                    (a) =>
                        !(
                            a.pupil_id === pupilId &&
                            a.exercise_id === exerciseId
                        ),
                ),
            );
        } else {
            setData('assignments', [
                ...data.assignments,
                {
                    pupil_id: pupilId,
                    exercise_id: exerciseId,
                    result_value: '',
                    notes: '',
                    is_completed: false,
                },
            ]);
        }
    }

    function updateAssignment(
        pupilId: number,
        exerciseId: number,
        field: 'result_value' | 'notes' | 'is_completed',
        value: string | boolean,
    ) {
        setData(
            'assignments',
            data.assignments.map((a) =>
                a.pupil_id === pupilId && a.exercise_id === exerciseId
                    ? { ...a, [field]: value }
                    : a,
            ),
        );
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        put(update.url({ school: school.id, session: session.id }));
    }

    return (
        <>
            <Head title="Edit Session" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Edit Session"
                    description="Update session details and record results."
                />

                <form
                    onSubmit={submit}
                    className="max-w-lg space-y-6"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="date">Date</Label>
                        <Input
                            id="date"
                            type="date"
                            value={data.date}
                            onChange={(e) => setData('date', e.target.value)}
                            required
                        />
                        <InputError message={errors.date} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="notes">
                            Notes{' '}
                            <span className="text-muted-foreground">
                                (optional)
                            </span>
                        </Label>
                        <textarea
                            id="notes"
                            className="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            placeholder="Any notes about this session..."
                        />
                        <InputError message={errors.notes} />
                    </div>

                    <div className="grid gap-2">
                        <Label>Pupils</Label>
                        {pupils.length === 0 ? (
                            <p className="text-muted-foreground text-sm">
                                No pupils available.
                            </p>
                        ) : (
                            <div className="space-y-2">
                                {pupils.map((pupil) => (
                                    <label
                                        key={pupil.id}
                                        className="flex items-center gap-2"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={data.pupil_ids.includes(
                                                pupil.id,
                                            )}
                                            onChange={() =>
                                                togglePupil(pupil.id)
                                            }
                                        />
                                        <span className="text-sm">
                                            {pupil.name}
                                        </span>
                                    </label>
                                ))}
                            </div>
                        )}
                        <InputError message={errors.pupil_ids} />
                    </div>

                    {data.pupil_ids.length > 0 && exercises.length > 0 && (
                        <div className="grid gap-4">
                            <Label>Exercise Assignments</Label>
                            {data.pupil_ids.map((pupilId) => {
                                const pupil = pupils.find(
                                    (p) => p.id === pupilId,
                                );
                                if (!pupil) {
                                    return null;
                                }
                                return (
                                    <div
                                        key={pupilId}
                                        className="rounded-lg border p-4"
                                    >
                                        <h4 className="mb-2 font-medium">
                                            {pupil.name}
                                        </h4>
                                        <div className="space-y-3">
                                            {exercises.map((exercise) => {
                                                const assignment =
                                                    data.assignments.find(
                                                        (a) =>
                                                            a.pupil_id ===
                                                                pupilId &&
                                                            a.exercise_id ===
                                                                exercise.id,
                                                    );
                                                const isAssigned =
                                                    !!assignment;

                                                return (
                                                    <div
                                                        key={exercise.id}
                                                        className="space-y-2"
                                                    >
                                                        <label className="flex items-center gap-2">
                                                            <input
                                                                type="checkbox"
                                                                checked={
                                                                    isAssigned
                                                                }
                                                                onChange={() =>
                                                                    toggleAssignment(
                                                                        pupilId,
                                                                        exercise.id,
                                                                    )
                                                                }
                                                            />
                                                            <span className="text-sm font-medium">
                                                                {
                                                                    exercise.name
                                                                }
                                                            </span>
                                                        </label>

                                                        {isAssigned && (
                                                            <div className="ml-6 grid gap-2">
                                                                <Input
                                                                    placeholder="Result"
                                                                    value={
                                                                        assignment.result_value
                                                                    }
                                                                    onChange={(
                                                                        e,
                                                                    ) =>
                                                                        updateAssignment(
                                                                            pupilId,
                                                                            exercise.id,
                                                                            'result_value',
                                                                            e
                                                                                .target
                                                                                .value,
                                                                        )
                                                                    }
                                                                />
                                                                <textarea
                                                                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[60px] w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                                                    placeholder="Notes"
                                                                    value={
                                                                        assignment.notes
                                                                    }
                                                                    onChange={(
                                                                        e,
                                                                    ) =>
                                                                        updateAssignment(
                                                                            pupilId,
                                                                            exercise.id,
                                                                            'notes',
                                                                            e
                                                                                .target
                                                                                .value,
                                                                        )
                                                                    }
                                                                />
                                                                <label className="flex items-center gap-2">
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={
                                                                            assignment.is_completed
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            updateAssignment(
                                                                                pupilId,
                                                                                exercise.id,
                                                                                'is_completed',
                                                                                e
                                                                                    .target
                                                                                    .checked,
                                                                            )
                                                                        }
                                                                    />
                                                                    <span className="text-sm">
                                                                        Completed
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        )}
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                );
                            })}
                            <InputError message={errors.assignments} />
                        </div>
                    )}

                    <div className="flex items-center gap-4">
                        <Button disabled={processing}>Save Changes</Button>
                    </div>
                </form>
            </div>
        </>
    );
}
