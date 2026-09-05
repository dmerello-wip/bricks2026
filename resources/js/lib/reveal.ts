import type { CSSProperties } from 'react';

/**
 * Default stagger order of the editorial atoms inside a block.
 *
 * The value feeds the `--reveal-index` custom property, which
 * `resources/css/reveal.css` multiplies by `--reveal-stagger` to get the
 * transition delay. Call sites can override it per instance.
 */
export const REVEAL_INDEX = {
    eyelet: 0,
    title: 1,
    subtitle: 2,
    text: 3,
    cta: 4,
} as const;

type RevealAttributes = {
    style: CSSProperties;
};

/** Marks an element for the bottom-up fade reveal. */
export function revealProps(
    index = 0,
): RevealAttributes & { 'data-reveal': '' } {
    return {
        'data-reveal': '',
        style: { '--reveal-index': index } as CSSProperties,
    };
}

/** Marks an element for the zoom-out reveal (needs an overflow-hidden parent). */
export function revealZoomProps(
    index = 0,
): RevealAttributes & { 'data-reveal-zoom': '' } {
    return {
        'data-reveal-zoom': '',
        style: { '--reveal-index': index } as CSSProperties,
    };
}
