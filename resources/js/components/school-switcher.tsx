import { router, usePage } from '@inertiajs/react';
import { ChevronsUpDown, GraduationCap, Plus } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useIsMobile } from '@/hooks/use-mobile';
import { create as schoolsCreate } from '@/routes/schools';
import { switchMethod } from '@/routes/schools';

export function SchoolSwitcher() {
    const { auth } = usePage().props;
    const { state } = useSidebar();
    const isMobile = useIsMobile();

    const currentSchool = auth.schools.find(
        (s) => s.id === auth.currentSchoolId,
    );

    function handleSwitch(schoolId: number) {
        if (schoolId === auth.currentSchoolId) {
            return;
        }
        router.post(switchMethod.url(schoolId));
    }

    if (auth.schools.length === 0) {
        return null;
    }

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="lg"
                            className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        >
                            <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center overflow-hidden rounded-lg">
                                {currentSchool?.logo_path ? (
                                    <img
                                        src={`/storage/${currentSchool.logo_path}`}
                                        alt={currentSchool.name}
                                        className="size-8 object-cover"
                                    />
                                ) : (
                                    <GraduationCap className="size-4" />
                                )}
                            </div>
                            <div className="grid flex-1 text-left text-sm leading-tight">
                                <span className="truncate font-medium">
                                    {currentSchool?.name ?? 'Select School'}
                                </span>
                            </div>
                            <ChevronsUpDown className="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={
                            isMobile
                                ? 'bottom'
                                : state === 'collapsed'
                                  ? 'right'
                                  : 'bottom'
                        }
                        sideOffset={4}
                    >
                        <DropdownMenuLabel className="text-muted-foreground text-xs">
                            Schools
                        </DropdownMenuLabel>
                        {auth.schools.map((school) => (
                            <DropdownMenuItem
                                key={school.id}
                                onClick={() => handleSwitch(school.id)}
                                className="cursor-pointer gap-2 p-2"
                            >
                                <div className="flex size-6 items-center justify-center overflow-hidden rounded-sm border">
                                    {school.logo_path ? (
                                        <img
                                            src={`/storage/${school.logo_path}`}
                                            alt={school.name}
                                            className="size-6 object-cover"
                                        />
                                    ) : (
                                        <GraduationCap className="size-3.5 shrink-0" />
                                    )}
                                </div>
                                <span className="truncate">{school.name}</span>
                                {school.id === auth.currentSchoolId && (
                                    <span className="text-muted-foreground ml-auto text-xs">
                                        Active
                                    </span>
                                )}
                            </DropdownMenuItem>
                        ))}
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            className="cursor-pointer gap-2 p-2"
                            onClick={() => router.visit(schoolsCreate.url())}
                        >
                            <div className="bg-background flex size-6 items-center justify-center rounded-md border">
                                <Plus className="size-4" />
                            </div>
                            <span className="text-muted-foreground font-medium">
                                Add School
                            </span>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
