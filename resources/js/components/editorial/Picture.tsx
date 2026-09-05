import { revealZoomProps } from '@/lib/reveal';
import type { ImageData } from '@/lib/types';
import { cn } from '@/lib/utils';

export default function Picture({
    image,
    imageMobile,
    className,
    wrapperClassName,
    reveal = false,
    revealIndex = 0,
}: {
    image?: ImageData | null;
    imageMobile?: ImageData | null;
    /** Classes for the <img> itself. */
    className?: string;
    /** Classes for the <picture> box that clips the zoom. */
    wrapperClassName?: string;
    /** Zoom the image out to its final size when the block enters the viewport. */
    reveal?: boolean;
    revealIndex?: number;
}) {
    if (!image && !imageMobile) return null;

    return (
        <picture
            className={cn(reveal && 'block overflow-hidden', wrapperClassName)}
        >
            {imageMobile && (
                <source
                    media="(width < 640px)"
                    srcSet={imageMobile.src}
                    width={imageMobile.width ?? undefined}
                    height={imageMobile.height ?? undefined}
                />
            )}
            <source
                media="(width >= 1024px)"
                srcSet={image!.src}
                width={image!.width ?? undefined}
                height={image!.height ?? undefined}
            />
            <img
                className={className || ''}
                src={image!.src}
                alt={image!.alt || ''}
                width={image!.width ?? undefined}
                height={image!.height ?? undefined}
                {...(reveal ? revealZoomProps(revealIndex) : {})}
            />
        </picture>
    );
}
