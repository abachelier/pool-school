import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    index as pupilsIndex,
    create as pupilsCreate,
    show as pupilsShow,
} from '@/routes/schools/pupils';
import type { Pupil, School } from '@/types';

type PageProps = {
    school: School;
    pupils: Pupil[];
    isShowingArchived: boolean;
};

export default function PupilsIndex({
    school,
    pupils,
    isShowingArchived,
}: PageProps) {
    return (
        <>
            <Head title="Pupils" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={isShowingArchived ? 'Archived Pupils' : 'Pupils'}
                        description="Manage the pupils you teach."
                    />

                    <div className="flex items-center gap-2">
                        {isShowingArchived ? (
                            <Button variant="outline" asChild>
                                <Link href={pupilsIndex.url(school.id)}>
                                    Active Pupils
                                </Link>
                            </Button>
                        ) : (
                            <>
                                <Button variant="outline" asChild>
                                    <Link
                                        href={pupilsIndex.url(school.id, {
                                            query: { archived: '1' },
                                        })}
                                    >
                                        View Archived
                                    </Link>
                                </Button>
                                <Button asChild>
                                    <Link href={pupilsCreate.url(school.id)}>
                                        <Plus className="mr-1 size-4" />
                                        Add Pupil
                                    </Link>
                                </Button>
                            </>
                        )}
                    </div>
                </div>

                {pupils.length === 0 ? (
                    <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center">
                        {isShowingArchived
                            ? 'No archived pupils.'
                            : 'No pupils yet. Add your first pupil to get started.'}
                    </div>
                ) : (
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {pupils.map((pupil) => (
                            <Link
                                key={pupil.id}
                                href={pupilsShow.url({
                                    school: school.id,
                                    pupil: pupil.id,
                                })}
                                className="hover:bg-accent rounded-lg border p-4 transition-colors"
                                prefetch
                            >
                                <div className="flex items-center justify-between">
                                    <h3 className="font-medium">
                                        {pupil.name}
                                    </h3>
                                    {!pupil.is_active && (
                                        <Badge variant="secondary">
                                            Archived
                                        </Badge>
                                    )}
                                </div>
                                {pupil.email && (
                                    <p className="text-muted-foreground mt-1 text-sm">
                                        {pupil.email}
                                    </p>
                                )}
                                {pupil.phone && (
                                    <p className="text-muted-foreground mt-0.5 text-sm">
                                        {pupil.phone}
                                    </p>
                                )}
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
