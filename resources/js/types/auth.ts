export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type SchoolSummary = {
    id: number;
    name: string;
    logo_path: string | null;
};

export type Auth = {
    user: User;
    schools: SchoolSummary[];
    currentSchoolId: number | null;
    currentSchoolRole: 'admin' | 'member' | null;
};
