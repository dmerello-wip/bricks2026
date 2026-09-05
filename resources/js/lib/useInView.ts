import { useCallback, useState, type RefCallback } from 'react';

export interface UseInViewOptions {
    /** Fraction of the element that must be visible before it counts as in view. */
    threshold?: number;
    /** Margin around the viewport, e.g. '0px 0px -10% 0px' to trigger slightly late. */
    rootMargin?: string;
    /** Stop observing after the first intersection and stay in view. */
    once?: boolean;
}

export interface UseInViewResult<T extends HTMLElement> {
    ref: RefCallback<T>;
    inView: boolean;
}

/**
 * Tracks whether the referenced element is inside the viewport.
 *
 * The observer is attached from a ref callback, so it starts exactly when the
 * node mounts and is disconnected by React when it unmounts.
 *
 * SSR-safe: `inView` is `false` on the server and on the first client render,
 * so the markup is identical across hydration. Environments without
 * IntersectionObserver fall back to "always in view" rather than hiding content.
 *
 * Usage:
 *   const { ref, inView } = useInView<HTMLDivElement>();
 *   <div ref={ref} data-in-view={inView} />
 */
export function useInView<T extends HTMLElement = HTMLElement>({
    threshold = 0.15,
    rootMargin = '0px 0px -10% 0px',
    once = true,
}: UseInViewOptions = {}): UseInViewResult<T> {
    const [inView, setInView] = useState(false);

    const ref = useCallback<RefCallback<T>>(
        (element) => {
            if (!element) return;

            if (typeof IntersectionObserver === 'undefined') {
                setInView(true);
                return;
            }

            const observer = new IntersectionObserver(
                ([entry]) => {
                    if (entry.isIntersecting) {
                        setInView(true);

                        if (once) {
                            observer.disconnect();
                        }

                        return;
                    }

                    if (!once) {
                        setInView(false);
                    }
                },
                { threshold, rootMargin },
            );

            observer.observe(element);

            return () => observer.disconnect();
        },
        [threshold, rootMargin, once],
    );

    return { ref, inView };
}
