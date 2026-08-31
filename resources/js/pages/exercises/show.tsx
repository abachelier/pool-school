import { Form, Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { edit } from '@/routes/exercises';
import type { Exercise } from '@/types';

type PageProps = {
    exercise: Exercise;
};

export default function ExercisesShow({ exercise }: PageProps) {
    return (
        <>
            <Head title={exercise.name} />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Heading title={exercise.name} />
                        {!exercise.is_active && (
                            <Badge variant="secondary">Archived</Badge>
                        )}
                    </div>

                    <div className="flex items-center gap-2">
                        {exercise.is_active ? (
                            <>
                                <Button variant="outline" asChild>
                                    <Link
                                        href={edit.url(exercise.id)}
                                    >
                                        <Pencil className="mr-1 size-4" />
                                        Edit
                                    </Link>
                                </Button>
                                <Form
                                    {...ExerciseController.archive.form(
                                        exercise.id,
                                    )}
                                    className="inline"
                                >
                                    {({ processing }) => (
                                        <Button
                                            variant="outline"
                                            disabled={processing}
                                        >
                                            Archive
                                        </Button>
                                    )}
                                </Form>
                            </>
                        ) : (
                            <Form
                                {...ExerciseController.restore.form(
                                    exercise.id,
                                )}
                                className="inline"
                            >
                                {({ processing }) => (
                                    <Button
                                        variant="outline"
                                        disabled={processing}
                                    >
                                        Restore
                                    </Button>
                                )}
                            </Form>
                        )}
                    </div>
                </div>

                <div className="max-w-lg space-y-4 rounded-lg border p-6">
                    <div>
                        <img
                            src={`/storage/${exercise.image_path}`}
                            alt={exercise.name}
                            className="h-48 w-full rounded-md object-cover"
                        />
                    </div>

                    {exercise.category_label && (
                        <div>
                            <h3 className="text-muted-foreground text-sm font-medium">
                                Category
                            </h3>
                            <p className="mt-1">{exercise.category_label}</p>
                        </div>
                    )}

                    <div>
                        <h3 className="text-muted-foreground text-sm font-medium">
                            Difficulty
                        </h3>
                        <p className="mt-1">{exercise.difficulty}/5</p>
                    </div>

                    {exercise.description && (
                        <div>
                            <h3 className="text-muted-foreground text-sm font-medium">
                                Description
                            </h3>
                            <p className="mt-1 whitespace-pre-wrap">
                                {exercise.description}
                            </p>
                        </div>
                    )}

                    {exercise.notes && (
                        <div>
                            <h3 className="text-muted-foreground text-sm font-medium">
                                Notes
                            </h3>
                            <p className="mt-1 whitespace-pre-wrap">
                                {exercise.notes}
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
