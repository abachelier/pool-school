import { Form, Head } from '@inertiajs/react';
import PupilController from '@/actions/App/Http/Controllers/PupilController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Pupil, School } from '@/types';

type PageProps = {
    school: School;
    pupil: Pupil;
};

export default function PupilsEdit({ school, pupil }: PageProps) {
    return (
        <>
            <Head title={`Edit ${pupil.name}`} />

            <div className="space-y-6 p-4">
                <Heading
                    title="Edit Pupil"
                    description={`Update ${pupil.name}'s information.`}
                />

                <Form
                    {...PupilController.update.form({
                        school: school.id,
                        pupil: pupil.id,
                    })}
                    options={{
                        preserveScroll: true,
                    }}
                    className="max-w-lg space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    defaultValue={pupil.name}
                                    placeholder="Pupil's full name"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    Email{' '}
                                    <span className="text-muted-foreground">
                                        (optional)
                                    </span>
                                </Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    defaultValue={pupil.email ?? ''}
                                    placeholder="pupil@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="phone">
                                    Phone{' '}
                                    <span className="text-muted-foreground">
                                        (optional)
                                    </span>
                                </Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    defaultValue={pupil.phone ?? ''}
                                    placeholder="Phone number"
                                />
                                <InputError message={errors.phone} />
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
                                    defaultValue={pupil.notes ?? ''}
                                    placeholder="Any additional notes about this pupil..."
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
