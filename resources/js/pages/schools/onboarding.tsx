import { Form, Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Mail } from 'lucide-react';
import SchoolController from '@/actions/App/Http/Controllers/SchoolController';
import InvitationController from '@/actions/App/Http/Controllers/InvitationController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PendingInvitation } from '@/types';

export default function SchoolsOnboarding() {
    const { auth } = usePage().props;
    const [selectedInvitation, setSelectedInvitation] =
        useState<PendingInvitation | null>(null);

    return (
        <div className="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <Head title="Create Your School" />

            <div className="w-full max-w-md">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-2 text-center">
                        <h1 className="text-2xl font-bold tracking-tight">
                            Pool School
                        </h1>
                        <p className="text-muted-foreground text-sm">
                            Create your first school to get started.
                        </p>
                    </div>

                    {auth.pendingInvitations.length > 0 && (
                        <div className="space-y-3">
                            <h2 className="text-center text-sm font-medium">
                                Pending invitations
                            </h2>
                            {auth.pendingInvitations.map((invitation) => (
                                <button
                                    key={invitation.id}
                                    type="button"
                                    onClick={() =>
                                        setSelectedInvitation(invitation)
                                    }
                                    className="hover:bg-muted flex w-full items-center gap-3 rounded-lg border p-3 text-left transition-colors"
                                >
                                    <Mail className="text-muted-foreground size-5 shrink-0" />
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-sm font-medium">
                                            {invitation.school_name}
                                        </p>
                                        <p className="text-muted-foreground text-xs">
                                            Invited as {invitation.role}
                                        </p>
                                    </div>
                                    <Badge variant="secondary">Pending</Badge>
                                </button>
                            ))}
                            <div className="relative py-2">
                                <div className="absolute inset-0 flex items-center">
                                    <span className="w-full border-t" />
                                </div>
                                <div className="relative flex justify-center text-xs uppercase">
                                    <span className="bg-background text-muted-foreground px-2">
                                        or create a school
                                    </span>
                                </div>
                            </div>
                        </div>
                    )}

                    <Form
                        {...SchoolController.store.form()}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">School Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        required
                                        autoFocus
                                        placeholder="e.g. Aqua Swimming Academy"
                                    />
                                    <InputError message={errors.name} />
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
                                        placeholder="A short description of the school..."
                                    />
                                    <InputError message={errors.description} />
                                </div>

                                <Button
                                    className="w-full"
                                    disabled={processing}
                                >
                                    Create School
                                </Button>
                            </>
                        )}
                    </Form>
                </div>
            </div>

            <Dialog
                open={selectedInvitation !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setSelectedInvitation(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>School invitation</DialogTitle>
                        <DialogDescription>
                            You have been invited to join{' '}
                            <strong>{selectedInvitation?.school_name}</strong>{' '}
                            as a <strong>{selectedInvitation?.role}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="gap-2 sm:gap-0">
                        <Button
                            variant="outline"
                            onClick={() => {
                                if (selectedInvitation) {
                                    router.post(
                                        InvitationController.decline.url(
                                            selectedInvitation.id,
                                        ),
                                        {},
                                        {
                                            onFinish: () =>
                                                setSelectedInvitation(null),
                                        },
                                    );
                                }
                            }}
                        >
                            Decline
                        </Button>
                        <Button
                            onClick={() => {
                                if (selectedInvitation) {
                                    router.post(
                                        InvitationController.accept.url(
                                            selectedInvitation.id,
                                        ),
                                        {},
                                        {
                                            onFinish: () =>
                                                setSelectedInvitation(null),
                                        },
                                    );
                                }
                            }}
                        >
                            Accept
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
