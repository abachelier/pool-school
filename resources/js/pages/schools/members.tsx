import { Form, Head, router } from '@inertiajs/react';
import { ShieldCheck, ShieldOff, UserPlus } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import SchoolController from '@/actions/App/Http/Controllers/SchoolController';
import type { School } from '@/types';

type Member = {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'member';
    is_self: boolean;
};

type PageProps = {
    school: School;
    members: Member[];
};

export default function SchoolMembers({ school, members }: PageProps) {
    function handleToggleRole(memberId: number) {
        router.patch(
            SchoolController.toggleRole.url(school.id, memberId),
            {},
            { preserveScroll: true },
        );
    }

    return (
        <>
            <Head title="Members" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Members"
                    description={`Members of ${school.name}.`}
                />

                <div className="rounded-lg border p-4">
                    <h3 className="mb-3 text-sm font-medium">
                        Add a new member
                    </h3>
                    <Form
                        {...SchoolController.addMember.form(school.id)}
                        resetOnSuccess
                        className="grid gap-3 sm:grid-cols-4 sm:items-end"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="grid gap-1.5">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        required
                                        placeholder="Full name"
                                    />
                                    <InputError message={errors.name} />
                                </div>
                                <div className="grid gap-1.5">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        required
                                        placeholder="email@example.com"
                                    />
                                    <InputError message={errors.email} />
                                </div>
                                <div className="grid gap-1.5">
                                    <Label htmlFor="role">Role</Label>
                                    <Select name="role" defaultValue="member">
                                        <SelectTrigger id="role">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="member">
                                                Member
                                            </SelectItem>
                                            <SelectItem value="admin">
                                                Admin
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.role} />
                                </div>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="sm:w-auto"
                                >
                                    <UserPlus className="size-4" />
                                    Add
                                </Button>
                            </>
                        )}
                    </Form>
                </div>

                {members.length === 0 ? (
                    <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center">
                        No members yet.
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-lg border">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-muted/50 border-b">
                                    <th className="px-3 py-2 text-left font-medium">
                                        Name
                                    </th>
                                    <th className="px-3 py-2 text-left font-medium">
                                        Email
                                    </th>
                                    <th className="px-3 py-2 text-left font-medium">
                                        Role
                                    </th>
                                    <th className="w-10 px-2 py-2" />
                                </tr>
                            </thead>
                            <tbody>
                                {members.map((member) => (
                                    <tr
                                        key={member.id}
                                        className="border-b last:border-b-0"
                                    >
                                        <td className="px-3 py-2 font-medium whitespace-nowrap">
                                            {member.name}
                                        </td>
                                        <td className="text-muted-foreground px-3 py-2 whitespace-nowrap">
                                            {member.email}
                                        </td>
                                        <td className="px-3 py-2">
                                            <Badge
                                                variant={
                                                    member.role === 'admin'
                                                        ? 'default'
                                                        : 'secondary'
                                                }
                                            >
                                                {member.role === 'admin'
                                                    ? 'Admin'
                                                    : 'Member'}
                                            </Badge>
                                        </td>
                                        <td className="px-2 py-1">
                                            {!member.is_self && (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="size-7"
                                                    onClick={() =>
                                                        handleToggleRole(
                                                            member.id,
                                                        )
                                                    }
                                                    title={
                                                        member.role === 'admin'
                                                            ? 'Remove admin'
                                                            : 'Make admin'
                                                    }
                                                >
                                                    {member.role === 'admin' ? (
                                                        <ShieldOff className="size-4" />
                                                    ) : (
                                                        <ShieldCheck className="size-4" />
                                                    )}
                                                    <span className="sr-only">
                                                        {member.role === 'admin'
                                                            ? 'Remove admin'
                                                            : 'Make admin'}
                                                    </span>
                                                </Button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}
