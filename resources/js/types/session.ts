import type { Pupil } from './pupil';

export type TrainingSession = {
    id: number;
    school_id: number;
    date: string;
    is_archived: boolean;
    notes: string | null;
    pupils?: Pupil[];
    created_at: string;
    updated_at: string;
};

export type Exercise = {
    id: number;
    name: string;
    category: string;
    category_label?: string;
    description: string | null;
    image_path: string;
    difficulty: number;
    default_max_score: number | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

export type SessionExercise = Exercise & {
    has_pupil_assignment: boolean;
};

export type ExerciseCategoryOption = {
    value: string;
    label: string;
};

export type ExerciseAssignment = {
    id: number;
    session_id: number;
    pupil_id: number;
    exercise_id: number;
    score: number | null;
    max_score: number | null;
    notes: string | null;
    is_completed: boolean;
    pupil?: Pupil;
    exercise?: Exercise;
};

export type PupilAssignment = {
    pupil: Pupil;
    assignments: {
        id: number;
        exercise: Exercise;
        score: number | null;
        max_score: number | null;
        notes: string | null;
        is_completed: boolean;
    }[];
};

export type AssignmentCell = {
    id: number;
    score: number | null;
    max_score: number | null;
};

export type PupilRow = {
    pupil: { id: number; name: string };
    assignments: Record<number, AssignmentCell>;
};

export type AvailablePupil = {
    id: number;
    name: string;
};
