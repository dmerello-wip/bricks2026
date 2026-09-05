import type { VariantProps } from 'class-variance-authority';

import AppLink from '@/components/ui/AppLink';
import { Button, type buttonVariants } from '@/components/ui/Button';
import { REVEAL_INDEX, revealProps } from '@/lib/reveal';
import type { CtaContent } from '@/lib/types';
import { cn } from '@/lib/utils';

type CtaProps = {
    cta: CtaContent;
    className?: string;
    size?: VariantProps<typeof buttonVariants>['size'];
    revealIndex?: number;
};

/**
 * Resolves the href a CTA would actually render with, or null when the CTA is
 * incomplete. Exported so callers can tell an empty repeater item apart from a
 * real CTA without duplicating this guard.
 */
export function resolveCtaHref(cta?: CtaContent | null): string | null {
    if (!cta?.cta_label) return null;

    const href = cta.cta_type === 'download' ? cta.cta_dl_link : cta.cta_link;

    return href || null;
}

export default function Cta({
    cta,
    className,
    size = 'default',
    revealIndex = REVEAL_INDEX.cta,
}: CtaProps) {
    const ctaType = cta.cta_type;
    const target = cta.cta_target_blank ? '_blank' : '_self';
    const buttonStyle = cta.cta_style === 'secondary' ? 'secondary' : 'default';
    const label = cta.cta_label;
    const href = resolveCtaHref(cta);

    if (!href) return null;

    return (
        <Button
            className={cn('w-full sm:w-auto', className)}
            variant={buttonStyle}
            asChild
            size={size}
            {...revealProps(revealIndex)}
        >
            <AppLink
                href={href}
                type={ctaType}
                target={target}
                download={cta.cta_dl_filename ?? undefined}
            >
                {label}
            </AppLink>
        </Button>
    );
}
