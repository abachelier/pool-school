import { Head, Link, router } from '@inertiajs/react';
import { Dumbbell, Pencil } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    index as sessionsIndex,
    edit as sessionsEdit,
    show as sessionsShow,
    exercises as sessionsExercises,
    complete,
    start,
} from '@/routes/schools/sessions';
import type { Exercise, PupilAssignment, School, TrainingSession } from '@/types';

type PageProps = {
    school: School;
    session: TrainingSession;
    pupilAssignments: PupilAssignment[];
    sessionExercises: Exercise[];
};

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-GB', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function statusBadgeVariant(
    status: TrainingSession['status'],
): 'default' | 'outline' | 'secondary' {
    switch (status) {
        case 'planned':
            return 'default';
        case 'in_progress':
            return 'outline';
        case 'completed':
            return 'secondary';
    }
}

function statusLabel(status: TrainingSession['status']): string {
    switch (status) {
        case 'planned':
            return 'Planned';
        case 'in_progress':
            return 'In Progress';
        case 'completed':
            return 'Completed';
    }
}

export default function SessionsShow({
    school,
    session,
    pupilAssignments,
    sessionExercises,
}: PageProps) {
    function handleStart() {
        router.patch(
            start.url({ school: school.id, session: session.id }),
        );
    }

    function handleComplete() {
        router.patch(
            complete.url({ school: school.id, session: session.id }),
        );
    }

    return (
        <>
            <Head title={formatDate(session.date)} />

            <div className="space-y-6 p-4">
                <Heading
                    title={formatDate(session.date)}
                    badge={
                        <Badge
                            variant={statusBadgeVariant(session.status)}
                        >
                            {statusLabel(session.status)}
                        </Badge>
                    }
                />

                <div className="flex items-center gap-2">
                    {session.status === 'planned' && (
                        <Button
                            variant="outline"
                            onClick={handleStart}
                        >
                            Start Session
                        </Button>
                    )}
                    {session.status === 'in_progress' && (
                        <Button
                            variant="outline"
                            onClick={handleComplete}
                        >
                            Complete Session
                        </Button>
                    )}
                    <Button variant="outline" asChild>
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
                    <Button variant="outline" asChild>
                        <Link
                            href={sessionsEdit.url({
                                school: school.id,
                                session: session.id,
                            })}
                        >
                            <Pencil className="mr-1 size-4" />
                            Edit
                        </Link>
                    </Button>
                </div>

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

                {sessionExercises.length > 0 && (
                    <div className="space-y-4">
                        <h3 className="text-lg font-medium">Exercises</h3>
                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            {sessionExercises.map((exercise) => (
                                <div
                                    key={exercise.id}
                                    className="overflow-hidden rounded-lg border"
                                >
                                    {exercise.image_path && (
                                        <img
                                            src={`/storage/${exercise.image_path}`}
                                            alt={exercise.name}
                                            className="h-28 w-full object-cover"
                                        />
                                    )}
                                    <div className="p-3">
                                        <h4 className="text-sm font-medium">
                                            {exercise.name}
                                        </h4>
                                        {exercise.category_label && (
                                            <Badge
                                                variant="outline"
                                                className="mt-1"
                                            >
                                                {exercise.category_label}
                                            </Badge>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                <div className="space-y-4">
                    <h3 className="text-lg font-medium">
                        Pupil Assignments
                    </h3>

                    {pupilAssignments.length === 0 ? (
                        <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center">
                            No pupils assigned to this session.
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {pupilAssignments.map((group) => (
                                <div
                                    key={group.pupil.id}
                                    className="rounded-lg border p-4"
                                >
                                    <h4 className="mb-3 font-medium">
                                        {group.pupil.name}
                                    </h4>

                                    {group.assignments.length === 0 ? (
                                        <p className="text-muted-foreground text-sm">
                                            No exercises assigned.
                                        </p>
                                    ) : (
                                        <div className="space-y-2">
                                            {group.assignments.map(
                                                (assignment) => (
                                                    <div
                                                        key={assignment.id}
                                                        className="flex items-center justify-between rounded border p-3 text-sm"
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            {assignment.is_completed ? (
                                                                <Badge variant="secondary">
                                                                    Done
                                                                </Badge>
                                                            ) : (
                                                                <Badge variant="outline">
                                                                    Pending
                                                                </Badge>
                                                            )}
                                                            <span className="font-medium">
                                                                {
                                                                    assignment
                                                                        .exercise
                                                                        .name
                                                                }
                                                            </span>
                                                        </div>
                                                        <div className="text-muted-foreground flex items-center gap-4">
                                                            {assignment.result_value && (
                                                                <span>
                                                                    Result:{' '}
                                                                    {
                                                                        assignment.result_value
                                                                    }
                                                                </span>
                                                            )}
                                                            {assignment.notes && (
                                                                <span>
                                                                    {
                                                                        assignment.notes
                                                                    }
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
