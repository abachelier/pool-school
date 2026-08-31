import { Form, Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import PupilController from '@/actions/App/Http/Controllers/PupilController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { edit as pupilsEdit } from '@/routes/schools/pupils';
import type { Pupil, School } from '@/types';

type PageProps = {
    school: School;
    pupil: Pupil;
};

export default function PupilsShow({ school, pupil }: PageProps) {
    return (
        <>
            <Head title={pupil.name} />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Heading title={pupil.name} />
                        {!pupil.is_active && (
                            <Badge variant="secondary">Archived</Badge>
                        )}
                    </div>

                    <div className="flex items-center gap-2">
                        {pupil.is_active ? (
                            <>
                                <Button variant="outline" asChild>
                                    <Link
                                        href={pupilsEdit.url({
                                            school: school.id,
                                            pupil: pupil.id,
                                        })}
                                    >
                                        <Pencil className="mr-1 size-4" />
                                        Edit
                                    </Link>
                                </Button>
                                <Form
                                    {...PupilController.archive.form({
                                        school: school.id,
                                        pupil: pupil.id,
                                    })}
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
                                {...PupilController.restore.form({
                                    school: school.id,
                                    pupil: pupil.id,
                                })}
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
                        <h3 className="text-muted-foreground text-sm font-medium">
                            Name
                        </h3>
                        <p className="mt-1">{pupil.name}</p>
                    </div>

                    {pupil.email && (
                        <div>
                            <h3 className="text-muted-foreground text-sm font-medium">
                                Email
                            </h3>
                            <p className="mt-1">
                                <a
                                    href={`mailto:${pupil.email}`}
                                    className="text-primary hover:underline"
                                >
                                    {pupil.email}
                                </a>
                            </p>
                        </div>
                    )}

                    {pupil.phone && (
                        <div>
                            <h3 className="text-muted-foreground text-sm font-medium">
                                Phone
                            </h3>
                            <p className="mt-1">
                                <a
                                    href={`tel:${pupil.phone}`}
                                    className="text-primary hover:underline"
                                >
                                    {pupil.phone}
                                </a>
                            </p>
                        </div>
                    )}

                    {pupil.notes && (
                        <div>
                            <h3 className="text-muted-foreground text-sm font-medium">
                                Notes
                            </h3>
                            <p className="mt-1 whitespace-pre-wrap">
                                {pupil.notes}
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
