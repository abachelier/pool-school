import { useCallback, useEffect, useRef, useState } from 'react';
import { store } from '@/actions/App/Http/Controllers/ExerciseCategoryController';
import type { ExerciseCategoryOption } from '@/types';

type CreatableCategorySelectProps = {
    name: string;
    categories: ExerciseCategoryOption[];
    defaultValue?: number;
    required?: boolean;
};

export default function CreatableCategorySelect({
    name,
    categories: initialCategories,
    defaultValue,
    required,
}: CreatableCategorySelectProps) {
    const [categories, setCategories] =
        useState<ExerciseCategoryOption[]>(initialCategories);
    const [isOpen, setIsOpen] = useState(false);
    const [query, setQuery] = useState(() => {
        if (defaultValue) {
            const match = initialCategories.find(
                (c) => c.value === defaultValue,
            );
            return match?.label ?? '';
        }
        return '';
    });
    const [selectedValue, setSelectedValue] = useState<number | null>(
        defaultValue ?? null,
    );
    const [isCreating, setIsCreating] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    const filteredCategories = categories.filter((c) =>
        c.label.toLowerCase().includes(query.toLowerCase()),
    );

    const isExactMatch = categories.some(
        (c) => c.label.toLowerCase() === query.trim().toLowerCase(),
    );

    const handleSelect = useCallback(
        (category: ExerciseCategoryOption) => {
            setSelectedValue(category.value);
            setQuery(category.label);
            setIsOpen(false);
        },
        [],
    );

    const handleCreate = useCallback(async () => {
        const trimmedName = query.trim();
        if (!trimmedName || isCreating) {
            return;
        }

        setIsCreating(true);

        try {
            const response = await fetch(store.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent(
                        document.cookie
                            .split('; ')
                            .find((row) => row.startsWith('XSRF-TOKEN='))
                            ?.split('=')[1] ?? '',
                    ),
                },
                body: JSON.stringify({ name: trimmedName }),
            });

            if (response.ok) {
                const newCategory =
                    (await response.json()) as ExerciseCategoryOption;
                setCategories((prev) =>
                    [...prev, newCategory].sort((a, b) =>
                        a.label.localeCompare(b.label),
                    ),
                );
                handleSelect(newCategory);
            }
        } finally {
            setIsCreating(false);
        }
    }, [query, isCreating, handleSelect]);

    useEffect(() => {
        function handleClickOutside(event: MouseEvent) {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target as Node)
            ) {
                setIsOpen(false);

                if (!selectedValue) {
                    setQuery('');
                } else {
                    const match = categories.find(
                        (c) => c.value === selectedValue,
                    );
                    setQuery(match?.label ?? '');
                }
            }
        }

        document.addEventListener('mousedown', handleClickOutside);
        return () =>
            document.removeEventListener('mousedown', handleClickOutside);
    }, [selectedValue, categories]);

    return (
        <div ref={containerRef} className="relative">
            <input type="hidden" name={name} value={selectedValue ?? ''} />
            <input
                ref={inputRef}
                type="text"
                value={query}
                onChange={(e) => {
                    setQuery(e.target.value);
                    setSelectedValue(null);
                    setIsOpen(true);
                }}
                onFocus={() => setIsOpen(true)}
                placeholder="Select or create a category..."
                required={required}
                autoComplete="off"
                className="border-input bg-background text-foreground placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
            />

            {isOpen && (
                <ul className="bg-popover text-popover-foreground absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border p-1 shadow-md">
                    {filteredCategories.map((category) => (
                        <li
                            key={category.value}
                            onMouseDown={(e) => {
                                e.preventDefault();
                                handleSelect(category);
                            }}
                            className="hover:bg-accent hover:text-accent-foreground cursor-pointer rounded-sm px-2 py-1.5 text-sm"
                        >
                            {category.label}
                        </li>
                    ))}

                    {query.trim() && !isExactMatch && (
                        <li
                            onMouseDown={(e) => {
                                e.preventDefault();
                                handleCreate();
                            }}
                            className="hover:bg-accent hover:text-accent-foreground cursor-pointer rounded-sm px-2 py-1.5 text-sm font-medium"
                        >
                            {isCreating
                                ? 'Creating...'
                                : `Create "${query.trim()}"`}
                        </li>
                    )}

                    {filteredCategories.length === 0 &&
                        !query.trim() && (
                            <li className="text-muted-foreground px-2 py-1.5 text-sm">
                                No categories found.
                            </li>
                        )}
                </ul>
            )}
        </div>
    );
}
