import { Head, Link, usePoll } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    index as sessionsIndex,
    create as sessionsCreate,
    show as sessionsShow,
} from '@/routes/schools/sessions';
import type { School, TrainingSession } from '@/types';

type PageProps = {
    school: School;
    sessions: TrainingSession[];
};

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-GB', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function statusBadgeVariant(
    status: TrainingSession['status'],
): 'default' | 'outline' | 'secondary' {
    switch (status) {
        case 'planned':
            return 'default';
        case 'in_progress':
            return 'outline';
        case 'completed':
            return 'secondary';
    }
}

function statusLabel(status: TrainingSession['status']): string {
    switch (status) {
        case 'planned':
            return 'Planned';
        case 'in_progress':
            return 'In Progress';
        case 'completed':
            return 'Completed';
    }
}

export default function SessionsIndex({ school, sessions }: PageProps) {
    usePoll(15000);

    return (
        <>
            <Head title="Sessions" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Sessions"
                        description="Manage your training sessions."
                    />

                    <Button asChild>
                        <Link href={sessionsCreate.url(school.id)}>
                            <Plus className="mr-1 size-4" />
                            Add Session
                        </Link>
                    </Button>
                </div>

                {sessions.length === 0 ? (
                    <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center">
                        No sessions yet. Add your first session to get
                        started.
                    </div>
                ) : (
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {sessions.map((session) => (
                            <Link
                                key={session.id}
                                href={sessionsShow.url({
                                    school: school.id,
                                    session: session.id,
                                })}
                                className="hover:bg-accent rounded-lg border p-4 transition-colors"
                                prefetch
                            >
                                <div className="flex items-center justify-between">
                                    <h3 className="font-medium">
                                        {formatDate(session.date)}
                                    </h3>
                                    <Badge
                                        variant={statusBadgeVariant(
                                            session.status,
                                        )}
                                    >
                                        {statusLabel(session.status)}
                                    </Badge>
                                </div>
                                {session.pupils &&
                                    session.pupils.length > 0 && (
                                        <p className="text-muted-foreground mt-1 text-sm">
                                            {session.pupils.length}{' '}
                                            {session.pupils.length === 1
                                                ? 'pupil'
                                                : 'pupils'}
                                        </p>
                                    )}
                                {session.notes && (
                                    <p className="text-muted-foreground mt-0.5 truncate text-sm">
                                        {session.notes}
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
