import type { Pupil } from './pupil';

export type TrainingSession = {
    id: number;
    school_id: number;
    date: string;
    status: 'planned' | 'in_progress' | 'completed';
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
    notes: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
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
    result_value: string | null;
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
        result_value: string | null;
        notes: string | null;
        is_completed: boolean;
    }[];
};
