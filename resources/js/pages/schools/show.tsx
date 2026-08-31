import { Head, Link } from '@inertiajs/react';
import { Pencil, Users } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { edit as schoolsEdit } from '@/routes/schools';
import { index as pupilsIndex } from '@/routes/schools/pupils';
import type { School } from '@/types';

type PageProps = {
    school: School;
};

export default function SchoolsShow({ school }: PageProps) {
    return (
        <>
            <Head title={school.name} />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading title={school.name} />

                    <div className="flex items-center gap-2">
                        <Button variant="outline" asChild>
                            <Link href={schoolsEdit.url(school.id)}>
                                <Pencil className="mr-1 size-4" />
                                Edit
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={pupilsIndex.url(school.id)}>
                                <Users className="mr-1 size-4" />
                                Manage Pupils
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="max-w-lg space-y-4 rounded-lg border p-6">
                    <div>
                        <h3 className="text-muted-foreground text-sm font-medium">
                            Name
                        </h3>
                        <p className="mt-1">{school.name}</p>
                    </div>

                    {school.description && (
                        <div>
                            <h3 className="text-muted-foreground text-sm font-medium">
                                Description
                            </h3>
                            <p className="mt-1 whitespace-pre-wrap">
                                {school.description}
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
