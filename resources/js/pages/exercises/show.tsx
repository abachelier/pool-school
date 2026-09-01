import { Form, Head, Link } from '@inertiajs/react';
import { Pencil, X } from 'lucide-react';
import { useState } from 'react';
import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { edit } from '@/routes/exercises';
import type { Exercise } from '@/types';

type PageProps = {
    exercise: Exercise;
};

export default function ExercisesShow({ exercise }: PageProps) {
    const [imageOpen, setImageOpen] = useState(false);

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
                            className="h-48 w-full cursor-pointer rounded-md object-cover"
                            onClick={() => setImageOpen(true)}
                        />
                    </div>

                    <Dialog open={imageOpen} onOpenChange={setImageOpen}>
                        <DialogContent className="flex max-h-[95vh] max-w-[95vw] items-center justify-center border-none bg-black/90 p-0">
                            <DialogTitle className="sr-only">
                                {exercise.name}
                            </DialogTitle>
                            <DialogClose className="absolute top-3 right-3 z-10 rounded-full bg-black/50 p-2 text-white hover:bg-black/70">
                                <X className="size-5" />
                                <span className="sr-only">Close</span>
                            </DialogClose>
                            <img
                                src={`/storage/${exercise.image_path}`}
                                alt={exercise.name}
                                className="max-h-[90vh] max-w-full object-contain"
                            />
                        </DialogContent>
                    </Dialog>

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

                    {exercise.default_max_score !== null && (
                        <div>
                            <h3 className="text-muted-foreground text-sm font-medium">
                                Default Max Score
                            </h3>
                            <p className="mt-1">{exercise.default_max_score}</p>
                        </div>
                    )}

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
                </div>
            </div>
        </>
    );
}
