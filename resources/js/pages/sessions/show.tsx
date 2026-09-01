import { Form, Head, Link, router, useForm } from '@inertiajs/react';
import { Archive, Dumbbell, Plus, RotateCcw, Trash2 } from 'lucide-react';
import { useState } from 'react';
import TrainingSessionController from '@/actions/App/Http/Controllers/TrainingSessionController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { exercises as sessionsExercises } from '@/routes/schools/sessions';
import { show as exercisesShow } from '@/routes/exercises';
import { detach as exercisesDetach } from '@/routes/schools/sessions/exercises';
import type {
    AvailablePupil,
    PupilRow,
    School,
    SessionExercise,
    TrainingSession,
} from '@/types';

type PageProps = {
    school: School;
    session: TrainingSession;
    pupilRows: PupilRow[];
    sessionExercises: SessionExercise[];
    availablePupils: AvailablePupil[];
};

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-GB', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function ResultCell({
    assignment,
    schoolId,
    sessionId,
    isArchived,
    defaultMaxScore,
}: {
    assignment: { id: number; score: number | null; max_score: number | null };
    schoolId: number;
    sessionId: number;
    isArchived: boolean;
    defaultMaxScore: number | null;
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [score, setScore] = useState(String(assignment.score ?? ''));
    const [maxScore, setMaxScore] = useState(
        String(assignment.max_score ?? ''),
    );

    function handleOpen() {
        if (isArchived) return;
        setScore(String(assignment.score ?? ''));
        setMaxScore(String(assignment.max_score ?? defaultMaxScore ?? ''));
        setIsOpen(true);
    }

    function handleSave() {
        setIsOpen(false);
        const newScore = score === '' ? null : Number(score);
        const newMaxScore = maxScore === '' ? null : Number(maxScore);

        if (
            newScore !== assignment.score ||
            newMaxScore !== assignment.max_score
        ) {
            router.patch(
                TrainingSessionController.updateAssignment.url({
                    school: schoolId,
                    session: sessionId,
                    assignment: assignment.id,
                }),
                { score: newScore, max_score: newMaxScore },
                { preserveScroll: true },
            );
        }
    }

    const displayValue =
        assignment.score !== null && assignment.max_score !== null
            ? `${assignment.score}/${assignment.max_score}`
            : assignment.score !== null
              ? String(assignment.score)
              : null;

    return (
        <>
            <button
                type="button"
                onClick={handleOpen}
                className={`inline-flex h-7 w-full min-w-[60px] items-center justify-center rounded-md text-sm ${
                    !isArchived
                        ? 'hover:bg-muted cursor-pointer'
                        : 'cursor-default'
                }`}
            >
                {displayValue || (
                    <span className="text-muted-foreground">—</span>
                )}
            </button>

            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                <DialogContent className="sm:max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Enter Result</DialogTitle>
                    </DialogHeader>
                    <div className="grid gap-4 py-2">
                        <div className="grid gap-2">
                            <Label htmlFor="score">Score</Label>
                            <Input
                                id="score"
                                type="number"
                                min={0}
                                value={score}
                                onChange={(e) => setScore(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        handleSave();
                                    }
                                }}
                                placeholder="0"
                                autoFocus
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="max_score">Max Score</Label>
                            <Input
                                id="max_score"
                                type="number"
                                min={1}
                                value={maxScore}
                                onChange={(e) => setMaxScore(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        handleSave();
                                    }
                                }}
                                placeholder="20"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setIsOpen(false)}
                        >
                            Cancel
                        </Button>
                        <Button onClick={handleSave}>Save</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

export default function SessionsShow({
    school,
    session,
    pupilRows,
    sessionExercises,
    availablePupils,
}: PageProps) {
    const addPupilForm = useForm({ pupil_id: '' });

    function handleRemoveExercise(exerciseId: number) {
        router.delete(
            exercisesDetach.url({
                school: school.id,
                session: session.id,
                exercise: exerciseId,
            }),
        );
    }

    function handleAddPupil(e: React.FormEvent) {
        e.preventDefault();
        if (!addPupilForm.data.pupil_id) return;
        addPupilForm.post(
            TrainingSessionController.addPupil.url({
                school: school.id,
                session: session.id,
            }),
            {
                preserveScroll: true,
                onSuccess: () => addPupilForm.reset(),
            },
        );
    }

    function handleRemovePupil(pupilId: number) {
        router.delete(
            TrainingSessionController.removePupil.url({
                school: school.id,
                session: session.id,
                pupil: pupilId,
            }),
            { preserveScroll: true },
        );
    }

    return (
        <>
            <Head title={formatDate(session.date)} />

            <div className="space-y-6 p-4">
                <Heading
                    title={formatDate(session.date)}
                    badge={
                        session.is_archived ? (
                            <Badge variant="secondary">Archived</Badge>
                        ) : undefined
                    }
                    actions={
                        !session.is_archived ? (
                            <Form
                                {...TrainingSessionController.archive.form({
                                    school: school.id,
                                    session: session.id,
                                })}
                                className="inline"
                            >
                                {({ processing }) => (
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        disabled={processing}
                                    >
                                        <Archive className="size-4" />
                                        <span className="sr-only">Archive</span>
                                    </Button>
                                )}
                            </Form>
                        ) : (
                            <Form
                                {...TrainingSessionController.restore.form({
                                    school: school.id,
                                    session: session.id,
                                })}
                                className="inline"
                            >
                                {({ processing }) => (
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        disabled={processing}
                                    >
                                        <RotateCcw className="size-4" />
                                        <span className="sr-only">Restore</span>
                                    </Button>
                                )}
                            </Form>
                        )
                    }
                />

                {session.notes && (
                    <div className="max-w-lg rounded-lg border p-6">
                        <h3 className="text-muted-foreground text-sm font-medium">
                            Notes
                        </h3>
                        <p className="mt-1 whitespace-pre-wrap">
                            {session.notes}
                        </p>
                    </div>
                )}

                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h3 className="text-lg font-medium">Exercises</h3>
                        {!session.is_archived && (
                            <Button variant="outline" size="sm" asChild>
                                <Link
                                    href={sessionsExercises.url({
                                        school: school.id,
                                        session: session.id,
                                    })}
                                >
                                    <Dumbbell className="mr-1 size-4" />
                                    Add Exercises
                                </Link>
                            </Button>
                        )}
                    </div>
                    {sessionExercises.length > 0 ? (
                        <div className="flex gap-3 overflow-x-auto pb-2 sm:grid sm:grid-cols-2 sm:overflow-x-visible lg:grid-cols-3">
                            {sessionExercises.map((exercise) => (
                                <div
                                    key={exercise.id}
                                    className="group relative w-36 shrink-0 overflow-hidden rounded-lg border sm:w-auto sm:shrink"
                                >
                                    <Link
                                        href={exercisesShow.url(exercise.id)}
                                        className="block"
                                    >
                                        {exercise.image_path && (
                                            <img
                                                src={`/storage/${exercise.image_path}`}
                                                alt={exercise.name}
                                                className="h-24 w-full object-cover sm:h-28"
                                            />
                                        )}
                                        <div className="p-2 sm:p-3">
                                            <h4 className="truncate text-xs font-medium group-hover:underline sm:text-sm">
                                                {exercise.name}
                                            </h4>
                                        </div>
                                    </Link>
                                    {!exercise.has_pupil_assignment &&
                                        !session.is_archived && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="absolute top-1 right-1 size-7 opacity-0 transition-opacity group-hover:opacity-100"
                                                onClick={() =>
                                                    handleRemoveExercise(
                                                        exercise.id,
                                                    )
                                                }
                                            >
                                                <Trash2 className="size-4" />
                                                <span className="sr-only">
                                                    Remove exercise
                                                </span>
                                            </Button>
                                        )}
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center">
                            No exercises yet. Add exercises to get started.
                        </div>
                    )}
                </div>

                <div className="space-y-4">
                    <h3 className="text-lg font-medium">Pupil Assignments</h3>

                    {sessionExercises.length === 0 ? (
                        <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center">
                            Add exercises first to start assigning pupils.
                        </div>
                    ) : pupilRows.length === 0 &&
                      availablePupils.length === 0 ? (
                        <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center">
                            No pupils available. Add active pupils to your
                            school first.
                        </div>
                    ) : (
                        <div className="overflow-x-auto rounded-lg border">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="bg-muted/50 border-b">
                                        <th className="px-3 py-2 text-left font-medium">
                                            Pupil
                                        </th>
                                        {sessionExercises.map((exercise) => (
                                            <th
                                                key={exercise.id}
                                                className="px-3 py-2 text-center font-medium"
                                            >
                                                {exercise.name}
                                            </th>
                                        ))}
                                        {!session.is_archived && (
                                            <th className="w-10 px-2 py-2" />
                                        )}
                                    </tr>
                                </thead>
                                <tbody>
                                    {pupilRows.map((row) => (
                                        <tr
                                            key={row.pupil.id}
                                            className="border-b last:border-b-0"
                                        >
                                            <td className="px-3 py-2 font-medium whitespace-nowrap">
                                                {row.pupil.name}
                                            </td>
                                            {sessionExercises.map(
                                                (exercise) => {
                                                    const assignment =
                                                        row.assignments[
                                                            exercise.id
                                                        ];
                                                    return (
                                                        <td
                                                            key={exercise.id}
                                                            className="px-2 py-1 text-center"
                                                        >
                                                            {assignment ? (
                                                                <ResultCell
                                                                    assignment={
                                                                        assignment
                                                                    }
                                                                    schoolId={
                                                                        school.id
                                                                    }
                                                                    sessionId={
                                                                        session.id
                                                                    }
                                                                    isArchived={
                                                                        session.is_archived
                                                                    }
                                                                    defaultMaxScore={
                                                                        exercise.default_max_score
                                                                    }
                                                                />
                                                            ) : (
                                                                <span className="text-muted-foreground">
                                                                    —
                                                                </span>
                                                            )}
                                                        </td>
                                                    );
                                                },
                                            )}
                                            {!session.is_archived && (
                                                <td className="px-2 py-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="size-7"
                                                        onClick={() =>
                                                            handleRemovePupil(
                                                                row.pupil.id,
                                                            )
                                                        }
                                                    >
                                                        <Trash2 className="size-4" />
                                                        <span className="sr-only">
                                                            Remove pupil
                                                        </span>
                                                    </Button>
                                                </td>
                                            )}
                                        </tr>
                                    ))}
                                    {!session.is_archived &&
                                        availablePupils.length > 0 && (
                                            <tr>
                                                <td
                                                    colSpan={
                                                        sessionExercises.length +
                                                        2
                                                    }
                                                    className="px-3 py-2"
                                                >
                                                    <form
                                                        onSubmit={
                                                            handleAddPupil
                                                        }
                                                        className="flex items-center gap-2"
                                                    >
                                                        <Select
                                                            value={
                                                                addPupilForm
                                                                    .data
                                                                    .pupil_id
                                                            }
                                                            onValueChange={(
                                                                val,
                                                            ) =>
                                                                addPupilForm.setData(
                                                                    'pupil_id',
                                                                    val,
                                                                )
                                                            }
                                                        >
                                                            <SelectTrigger size="sm">
                                                                <SelectValue placeholder="Select a pupil..." />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                {availablePupils.map(
                                                                    (pupil) => (
                                                                        <SelectItem
                                                                            key={
                                                                                pupil.id
                                                                            }
                                                                            value={String(
                                                                                pupil.id,
                                                                            )}
                                                                        >
                                                                            {
                                                                                pupil.name
                                                                            }
                                                                        </SelectItem>
                                                                    ),
                                                                )}
                                                            </SelectContent>
                                                        </Select>
                                                        <Button
                                                            type="submit"
                                                            variant="outline"
                                                            size="sm"
                                                            disabled={
                                                                !addPupilForm
                                                                    .data
                                                                    .pupil_id ||
                                                                addPupilForm.processing
                                                            }
                                                        >
                                                            <Plus className="mr-1 size-4" />
                                                            Add
                                                        </Button>
                                                    </form>
                                                </td>
                                            </tr>
                                        )}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
