import { Form, Head } from '@inertiajs/react';
import SchoolController from '@/actions/App/Http/Controllers/SchoolController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { School } from '@/types';

type PageProps = {
    school: School;
};

export default function SchoolsShow({ school }: PageProps) {
    return (
        <>
            <Head title="Settings" />

            <div className="space-y-6 p-4">
                <Heading title="Settings" />

                <Form
                    {...SchoolController.update.form(school.id)}
                    encType="multipart/form-data"
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
                                    defaultValue={school.name}
                                    placeholder="School name"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="logo">
                                    Logo{' '}
                                    <span className="text-muted-foreground">
                                        (optional)
                                    </span>
                                </Label>
                                {school.logo_path && (
                                    <img
                                        src={`/storage/${school.logo_path}`}
                                        alt={school.name}
                                        className="h-16 w-16 rounded-md object-cover"
                                    />
                                )}
                                <Input
                                    id="logo"
                                    name="logo"
                                    type="file"
                                    accept="image/*"
                                />
                                <InputError message={errors.logo} />
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
                                    defaultValue={school.description ?? ''}
                                    placeholder="A short description of the school..."
                                />
                                <InputError message={errors.description} />
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
