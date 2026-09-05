import React from 'react';
import { REVEAL_INDEX, revealProps } from '@/lib/reveal';
import { cn } from '@/lib/utils';

interface EyeletProps {
    content: string;
    seoTag?: keyof React.JSX.IntrinsicElements;
    className?: string;
    revealIndex?: number;
}

export default function Eyelet({
    content,
    seoTag = 'div',
    className = '',
    revealIndex = REVEAL_INDEX.eyelet,
}: EyeletProps) {
    if (!content) return null;

    const defaultClasses =
        'block-eyelet w-full font-serif text-md font-bold uppercase tracking-wider text-secondary';

    return React.createElement(
        seoTag,
        {
            className: cn(defaultClasses, className),
            ...revealProps(revealIndex),
        },
        content,
    );
}
