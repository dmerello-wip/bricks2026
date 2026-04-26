import React from 'react';
import { cn } from '@/lib/utils';

export default function Text({
    content,
    className = '',
}: {
    content: string;
    className?: string;
}) {
    if (!content) return null;

    const defaultClasses =
        'block-text text-lg group-[.block-text-dark]:text-black group-[.block-text-light]:text-white group-[.hero-text-under]:max-sm:!text-black';

    return (
        <div
            className={cn(defaultClasses, className)}
            dangerouslySetInnerHTML={{ __html: content }}
        />
    );
}
