import { Head, Link } from '@inertiajs/react';
import { useMemo } from 'react';
import { Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index, create, show } from '@/routes/exercises';
import type { Exercise, ExerciseCategoryOption } from '@/types';

type PageProps = {
    exercises: Exercise[];
    isShowingArchived: boolean;
    categories: ExerciseCategoryOption[];
};

export default function ExercisesIndex({
    exercises,
    isShowingArchived,
}: PageProps) {
    const groupedExercises = useMemo(() => {
        const groups: { category: string; exercises: Exercise[] }[] = [];
        let currentCategory: string | null = null;

        for (const exercise of exercises) {
            const label = exercise.category_label ?? exercise.category;
            if (label !== currentCategory) {
                currentCategory = label;
                groups.push({ category: label, exercises: [] });
            }
            groups[groups.length - 1].exercises.push(exercise);
        }

        return groups;
    }, [exercises]);

    return (
        <>
            <Head title="Exercises" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={
                            isShowingArchived
                                ? 'Archived Exercises'
                                : 'Exercises'
                        }
                        description="Manage your training exercises."
                    />

                    <div className="flex items-center gap-2">
                        {isShowingArchived ? (
                            <Button variant="outline" asChild>
                                <Link href={index.url()}>Active Exercises</Link>
                            </Button>
                        ) : (
                            <>
                                <Button variant="outline" asChild>
                                    <Link
                                        href={index.url({
                                            query: { archived: '1' },
                                        })}
                                    >
                                        View Archived
                                    </Link>
                                </Button>
                                <Button asChild>
                                    <Link href={create.url()}>
                                        <Plus className="mr-1 size-4" />
                                        Add Exercise
                                    </Link>
                                </Button>
                            </>
                        )}
                    </div>
                </div>

                {exercises.length === 0 ? (
                    <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center">
                        {isShowingArchived
                            ? 'No archived exercises.'
                            : 'No exercises yet. Add your first exercise to get started.'}
                    </div>
                ) : (
                    <div className="space-y-6">
                        {groupedExercises.map((group) => (
                            <div key={group.category}>
                                <h3 className="mb-3 text-lg font-semibold">
                                    {group.category}
                                </h3>
                                <div className="flex gap-3 overflow-x-auto pb-2">
                                    {group.exercises.map((exercise) => (
                                        <Link
                                            key={exercise.id}
                                            href={show.url(exercise.id)}
                                            className="hover:bg-accent w-48 shrink-0 overflow-hidden rounded-lg border transition-colors"
                                            prefetch
                                        >
                                            <img
                                                src={`/storage/${exercise.image_path}`}
                                                alt={exercise.name}
                                                className="h-28 w-full object-cover"
                                            />
                                            <div className="p-3">
                                                <div className="flex items-center justify-between">
                                                    <h4 className="truncate text-sm font-medium">
                                                        {exercise.name}
                                                    </h4>
                                                    {!exercise.is_active && (
                                                        <Badge
                                                            variant="secondary"
                                                            className="ml-1 shrink-0 text-xs"
                                                        >
                                                            Archived
                                                        </Badge>
                                                    )}
                                                </div>
                                                <p className="text-muted-foreground mt-1 text-xs">
                                                    Difficulty:{' '}
                                                    {exercise.difficulty}/5
                                                </p>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
