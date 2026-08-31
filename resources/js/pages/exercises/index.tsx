import { Head, Link, usePoll } from '@inertiajs/react';
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
    usePoll(15000);

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
                                <Link href={index.url()}>
                                    Active Exercises
                                </Link>
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
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {exercises.map((exercise) => (
                            <Link
                                key={exercise.id}
                                href={show.url(exercise.id)}
                                className="hover:bg-accent overflow-hidden rounded-lg border transition-colors"
                                prefetch
                            >
                                <img
                                    src={`/storage/${exercise.image_path}`}
                                    alt={exercise.name}
                                    className="h-36 w-full object-cover"
                                />
                                <div className="p-4">
                                    <div className="flex items-center justify-between">
                                        <h3 className="font-medium">
                                            {exercise.name}
                                        </h3>
                                        {!exercise.is_active && (
                                            <Badge variant="secondary">
                                                Archived
                                            </Badge>
                                        )}
                                    </div>
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
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
