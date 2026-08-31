import { usePage } from '@inertiajs/react';
import { Calendar, Dumbbell, LayoutGrid, Settings, Users } from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { SchoolSwitcher } from '@/components/school-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as exercisesIndex } from '@/routes/exercises';
import { show as schoolsShow } from '@/routes/schools';
import { index as pupilsIndex } from '@/routes/schools/pupils';
import { index as sessionsIndex } from '@/routes/schools/sessions';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const { auth } = usePage().props;

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

        if (auth.currentSchoolRole === 'admin') {
            mainNavItems.push({
                title: 'Settings',
                href: schoolsShow.url(auth.currentSchoolId),
                icon: Settings,
            });
        }
    }

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SchoolSwitcher />
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
