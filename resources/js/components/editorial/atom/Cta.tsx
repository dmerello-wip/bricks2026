import AppLink from '@/components/ui/AppLink';
import { Button } from '@/components/ui/Button';
import type { CtaContent } from '@/lib/types';
import { cn } from '@/lib/utils';

type CtaProps = {
    cta: CtaContent;
    className?: string;
};

export default function Cta({ cta, className }: CtaProps) {
    const ctaType = cta.cta_type;
    const target = cta.cta_target_blank ? '_blank' : '_self';
    const buttonStyle = cta.cta_style === 'secondary' ? 'secondary' : 'default';
    const label = cta.cta_label;
    const href = ctaType === 'download' ? cta.cta_dl_link : cta.cta_link;

    if (!href || !label) return null;

    return (
        <Button
            className={cn('w-full sm:w-auto', className)}
            variant={buttonStyle}
            asChild
            size="lg"
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
