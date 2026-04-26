import React from 'react';
import { cn } from '@/lib/utils';

interface SubtitleProps {
    content: string;
    seoTag?: keyof React.JSX.IntrinsicElements;
    className?: string;
}

export default function Subtitle({
    content,
    seoTag = 'div',
    className,
}: SubtitleProps) {
    if (!content) return null;

    const defaultClasses =
        'block-subtitle w-full text-2xl text-secondary group-[.block-text-light]:text-white group-[.hero-text-under]:max-sm:!text-secondary';

    return React.createElement(
        seoTag,
        {
            className: cn(defaultClasses, className),
        },
        content,
    );
}
