import { Form, Head } from '@inertiajs/react';
import ExerciseController from '@/actions/App/Http/Controllers/ExerciseController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Exercise, ExerciseCategoryOption } from '@/types';

type PageProps = {
    exercise: Exercise;
    categories: ExerciseCategoryOption[];
};

export default function ExercisesEdit({ exercise, categories }: PageProps) {
    return (
        <>
            <Head title={`Edit ${exercise.name}`} />

            <div className="space-y-6 p-4">
                <Heading
                    title="Edit Exercise"
                    description={`Update ${exercise.name}'s details.`}
                />

                <Form
                    {...ExerciseController.update.form(exercise.id)}
                    encType="multipart/form-data"
                    options={{
                        preserveScroll: true,
                    }}
                    className="max-w-lg space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="category">Category</Label>
                                <select
                                    id="category"
                                    name="category"
                                    required
                                    defaultValue={exercise.category}
                                    className="border-input bg-background text-foreground focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">
                                        Select a category
                                    </option>
                                    {categories.map((cat) => (
                                        <option
                                            key={cat.value}
                                            value={cat.value}
                                        >
                                            {cat.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.category} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="image">
                                    Image{' '}
                                    <span className="text-muted-foreground">
                                        (leave empty to keep current)
                                    </span>
                                </Label>
                                {exercise.image_path && (
                                    <img
                                        src={`/storage/${exercise.image_path}`}
                                        alt={exercise.name}
                                        className="h-32 w-32 rounded-md object-cover"
                                    />
                                )}
                                <Input
                                    id="image"
                                    name="image"
                                    type="file"
                                    accept="image/*"
                                />
                                <InputError message={errors.image} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="difficulty">Difficulty</Label>
                                <Input
                                    id="difficulty"
                                    name="difficulty"
                                    type="number"
                                    min={1}
                                    max={5}
                                    required
                                    defaultValue={exercise.difficulty}
                                    placeholder="1-5"
                                />
                                <InputError message={errors.difficulty} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">
                                    Description{' '}
                                    <span className="text-muted-foreground">
                                        (optional)
                                    </span>
                                </Label>
                                <textarea
                                    id="description"
                                    name="description"
                                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    defaultValue={exercise.description ?? ''}
                                    placeholder="Describe the exercise..."
                                />
                                <InputError message={errors.description} />
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
                                    name="notes"
                                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    defaultValue={exercise.notes ?? ''}
                                    placeholder="Any additional notes..."
                                />
                                <InputError message={errors.notes} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    Save Changes
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
