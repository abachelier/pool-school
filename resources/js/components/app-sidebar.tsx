import { Link, usePage } from '@inertiajs/react';
import {
    Calendar,
    Dumbbell,
    LayoutGrid,
    Settings,
    Users,
    UsersRound,
} from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { SchoolSwitcher } from '@/components/school-switcher';
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
import SchoolController from '@/actions/App/Http/Controllers/SchoolController';
import { index as pupilsIndex } from '@/routes/schools/pupils';
import { index as sessionsIndex } from '@/routes/schools/sessions';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const { auth } = usePage().props;
    const { isCurrentUrl } = useCurrentUrl();
    const { setOpenMobile } = useSidebar();

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
                {auth.currentSchoolId && auth.currentSchoolRole === 'admin' && (
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

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
