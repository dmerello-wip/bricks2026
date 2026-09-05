import React from 'react';
import { REVEAL_INDEX, revealProps } from '@/lib/reveal';
import { cn } from '@/lib/utils';

export default function Text({
    content,
    className = '',
    revealIndex = REVEAL_INDEX.text,
}: {
    content: string;
    className?: string;
    revealIndex?: number;
}) {
    if (!content) return null;

    const defaultClasses =
        'block-text text-lg group-[.block-text-dark]:text-foreground group-[.block-text-light]:text-background group-[.hero-text-under]:muted-foreground';

    return (
        <div
            className={cn(defaultClasses, className)}
            {...revealProps(revealIndex)}
            dangerouslySetInnerHTML={{ __html: content }}
        />
    );
}
