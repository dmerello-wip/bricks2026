import React from 'react';
import { cn } from '@/lib/utils';

interface TitleProps {
    content: string;
    seoTag?: keyof React.JSX.IntrinsicElements;
    className?: string;
}

export default function Title({
    content,
    seoTag = 'div',
    className = '',
}: TitleProps) {
    if (!content) return null;

    const defaultClasses =
        'block-title font-serif w-full text-4xl md:text-5xl font-bold text-primary uppercase';

    return React.createElement(
        seoTag,
        {
            className: cn(defaultClasses, className),
        },
        content,
    );
}
