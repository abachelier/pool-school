import type { ReactNode } from 'react';

export default function Heading({
    title,
    description,
    badge,
    variant = 'default',
}: {
    title: string;
    description?: string;
    badge?: ReactNode;
    variant?: 'default' | 'small';
}) {
    return (
        <header className={variant === 'small' ? '' : 'mb-8 space-y-0.5'}>
            <div className="flex items-center gap-3">
                <h2
                    className={
                        variant === 'small'
                            ? 'mb-0.5 text-base font-medium'
                            : 'text-xl font-semibold tracking-tight'
                    }
                >
                    {title}
                </h2>
                {badge}
            </div>
            {description && (
                <p className="text-muted-foreground text-sm">{description}</p>
            )}
        </header>
    );
}
