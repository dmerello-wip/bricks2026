import React from 'react';
import { REVEAL_INDEX, revealProps } from '@/lib/reveal';
import { cn } from '@/lib/utils';

interface SubtitleProps {
    content: string;
    seoTag?: keyof React.JSX.IntrinsicElements;
    className?: string;
    revealIndex?: number;
}

export default function Subtitle({
    content,
    seoTag = 'div',
    className,
    revealIndex = REVEAL_INDEX.subtitle,
}: SubtitleProps) {
    if (!content) return null;

    const defaultClasses =
        'block-subtitle w-full text-2xl text-secondary font-serif font-bold uppercase group-[.block-text-light]:text-muted-foreground group-[.hero-text-under]:max-sm:!text-muted-foreground';

    return React.createElement(
        seoTag,
        {
            className: cn(defaultClasses, className),
            ...revealProps(revealIndex),
        },
        content,
    );
}
