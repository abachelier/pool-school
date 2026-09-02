import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import {
    Calendar,
    Dumbbell,
    LayoutGrid,
    Mail,
    Settings,
    Users,
    UsersRound,
} from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { SchoolSwitcher } from '@/components/school-switcher';
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
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { dashboard } from '@/routes';
import { index as exercisesIndex } from '@/routes/exercises';
import { show as schoolsShow } from '@/routes/schools';
import InvitationController from '@/actions/App/Http/Controllers/InvitationController';
import SchoolController from '@/actions/App/Http/Controllers/SchoolController';
import { index as pupilsIndex } from '@/routes/schools/pupils';
import { index as sessionsIndex } from '@/routes/schools/sessions';
import type { NavItem, PendingInvitation } from '@/types';

export function AppSidebar() {
    const { auth } = usePage().props;
    const { isCurrentUrl } = useCurrentUrl();
    const { setOpenMobile } = useSidebar();
    const [selectedInvitation, setSelectedInvitation] =
        useState<PendingInvitation | null>(null);

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    mainNavItems.push({
        title: 'Exercises',
        href: exercisesIndex.url(),
        icon: Dumbbell,
    });

    if (auth.currentSchoolId) {
        mainNavItems.push({
            title: 'Pupils',
            href: pupilsIndex.url(auth.currentSchoolId),
            icon: Users,
        });

        mainNavItems.push({
            title: 'Sessions',
            href: sessionsIndex.url(auth.currentSchoolId),
            icon: Calendar,
        });
    }

    const settingsHref =
        auth.currentSchoolId && auth.currentSchoolRole === 'admin'
            ? schoolsShow.url(auth.currentSchoolId)
            : null;

    return (
        <>
            <Sidebar collapsible="icon" variant="inset">
                <SidebarHeader>
                    <SchoolSwitcher />
                    {settingsHref && (
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    isActive={isCurrentUrl(settingsHref)}
                                    tooltip={{ children: 'Settings' }}
                                >
                                    <Link
                                        href={settingsHref}
                                        onClick={() => setOpenMobile(false)}
                                    >
                                        <Settings />
                                        <span>Settings</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    )}
                    {auth.currentSchoolId &&
                        auth.currentSchoolRole === 'admin' && (
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <SidebarMenuButton
                                        asChild
                                        isActive={isCurrentUrl(
                                            SchoolController.members.url(
                                                auth.currentSchoolId,
                                            ),
                                        )}
                                        tooltip={{ children: 'Members' }}
                                    >
                                        <Link
                                            href={SchoolController.members.url(
                                                auth.currentSchoolId,
                                            )}
                                            onClick={() => setOpenMobile(false)}
                                        >
                                            <UsersRound />
                                            <span>Members</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        )}
                </SidebarHeader>

                <SidebarSeparator />

                <SidebarContent>
                    <NavMain items={mainNavItems} />
                </SidebarContent>

                {auth.pendingInvitations.length > 0 && (
                    <>
                        <SidebarSeparator />
                        <div className="px-4 py-2">
                            {auth.pendingInvitations.map((invitation) => (
                                <button
                                    key={invitation.id}
                                    type="button"
                                    onClick={() => {
                                        setOpenMobile(false);
                                        setSelectedInvitation(invitation);
                                    }}
                                    className="text-muted-foreground hover:text-foreground flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors"
                                >
                                    <Mail className="size-4 shrink-0" />
                                    <span className="truncate">
                                        Invitation pending
                                    </span>
                                    <Badge
                                        variant="secondary"
                                        className="ml-auto shrink-0 text-xs"
                                    >
                                        {invitation.school_name}
                                    </Badge>
                                </button>
                            ))}
                        </div>
                    </>
                )}

                <SidebarFooter>
                    <NavUser />
                </SidebarFooter>
            </Sidebar>

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
        </>
    );
}
