import React from 'react';
import { REVEAL_INDEX, revealProps } from '@/lib/reveal';
import { cn } from '@/lib/utils';

interface TitleProps {
    content: string;
    seoTag?: keyof React.JSX.IntrinsicElements;
    className?: string;
    revealIndex?: number;
}

export default function Title({
    content,
    seoTag = 'div',
    className = '',
    revealIndex = REVEAL_INDEX.title,
}: TitleProps) {
    if (!content) return null;

    const defaultClasses =
        'block-title font-serif w-full text-4xl md:text-6xl font-bold text-primary uppercase';

    return React.createElement(
        seoTag,
        {
            className: cn(defaultClasses, className),
            ...revealProps(revealIndex),
        },
        content,
    );
}
