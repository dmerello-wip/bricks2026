import { cva } from 'class-variance-authority';
import React from 'react';
import { cn } from '@/lib/utils';

interface LogoOrnamentProps {
    position?: 'top' | 'bottom';
    /** Tailwind spacing step for the diamond width. Height is derived as double the width. */
    size?: number;
    color?: 'primary' | 'secondary';
    /** 'left' pins the ornament to the content's left edge instead of centering it. */
    align?: 'center' | 'left';
    className?: string;
}

const ornamentClasses = cva(
    'logo-hornament absolute flex h-16 w-64 items-center justify-center overflow-hidden',
    {
        variants: {
            align: {
                center: 'left-1/2 -translate-x-1/2',
                left: 'left-0',
            },
            position: {
                top: 'logo-hornament--top bottom-[calc(100%+1.2rem)]',
                bottom: 'logo-hornament--bottom top-[calc(100%+1.2rem)]',
            },
        },
    },
);

const diamondClasses = cva('logo-hornament__diamond absolute h-24 w-12', {
    variants: {
        align: {
            center: 'left-1/2',
            // The rotated shape reaches ~3.2rem left of its center: this puts its edge on the text edge
            left: 'left-13',
        },
        position: {
            top: 'top-full',
            bottom: 'top-0',
        },
    },
});

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
    align = 'center',
    className = '',
}: LogoOrnamentProps) {
    return (
        <div className={cn(ornamentClasses({ position, align }), className)}>
            <div className={diamondClasses({ position, align })}>
                <div className={shapeClasses({ color })} />
            </div>
        </div>
    );
}
