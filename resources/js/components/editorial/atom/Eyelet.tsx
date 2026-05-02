import React from 'react';
import { cn } from '@/lib/utils';
interface EyeletProps {
    content: string;
    seoTag?: keyof React.JSX.IntrinsicElements;
    className?: string;
}

export default function Eyelet({
    content,
    seoTag = 'div',
    className = '',
}: EyeletProps) {
    if (!content) return null;

    const defaultClasses =
        'block-eyelet w-full font-serif text-md font-bold uppercase tracking-wider text-secondary';

    return React.createElement(
        seoTag,
        {
            className: cn(defaultClasses, className),
        },
        content,
    );
}
