import { cva } from 'class-variance-authority';
import React from 'react';
import { cn } from '@/lib/utils';

interface LogoOrnamentProps {
    position?: 'top' | 'bottom';
    /** Tailwind spacing step for the diamond width. Height is derived as double the width. */
    size?: number;
    color?: 'primary' | 'secondary';
    className?: string;
}

const ornamentClasses = cva(
    'logo-hornament absolute left-1/2 flex h-16 w-64 -translate-x-1/2 items-center justify-center overflow-hidden',
    {
        variants: {
            position: {
                top: 'logo-hornament--top bottom-[calc(100%+1.2rem)]',
                bottom: 'logo-hornament--bottom top-[calc(100%+1.2rem)]',
            },
        },
    },
);

const diamondClasses = cva(
    'logo-hornament__diamond absolute left-1/2 h-24 w-12',
    {
        variants: {
            position: {
                top: 'top-full',
                bottom: 'top-0',
            },
        },
    },
);

const shapeClasses = cva('h-full w-full rotate-45 border-6', {
    variants: {
        color: {
            primary: 'border-primary',
            secondary: 'border-secondary',
        },
    },
});

export default function LogoOrnament({
    position = 'bottom',
    color = 'primary',
    className = '',
}: LogoOrnamentProps) {
    return (
        <div className={cn(ornamentClasses({ position }), className)}>
            <div className={diamondClasses({ position })}>
                <div className={shapeClasses({ color })} />
            </div>
        </div>
    );
}
