import { useContext, type ReactNode } from 'react';
import { PreviewContext } from '@/lib/context/preview';
import { useInView, type UseInViewOptions } from '@/lib/useInView';
import { cn } from '@/lib/utils';

type RevealProps = UseInViewOptions & {
    children: ReactNode;
    className?: string;
};

/**
 * Scroll-reveal root for an editorial block.
 *
 * Owns the single IntersectionObserver of the block and exposes its state as
 * `data-in-view`. Descendants opt into the animation declaratively with
 * `data-reveal` (bottom-up fade) or `data-reveal-zoom` (zoom-out) — see
 * `resources/css/reveal.css`. No prop drilling, no per-element observer.
 *
 * Applied automatically to every top-level block by `BlockRenderer`.
 *
 * Inside the Twill preview iframe the block starts already revealed: the editor
 * needs to see the final state, not an animation.
 */
export default function Reveal({
    children,
    className,
    ...options
}: RevealProps) {
    const isPreview = useContext(PreviewContext);
    const { ref, inView } = useInView<HTMLDivElement>(options);

    return (
        <div
            ref={ref}
            data-reveal-root=""
            data-in-view={isPreview || inView}
            className={cn('block-reveal', className)}
        >
            {children}
        </div>
    );
}
