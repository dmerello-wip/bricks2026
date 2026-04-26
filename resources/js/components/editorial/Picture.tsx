import type { ImageData } from '@/lib/types';

export default function Picture({
    image,
    imageMobile,
    className,
}: {
    image?: ImageData | null;
    imageMobile?: ImageData | null;
    className?: string;
}) {
    if (!image && !imageMobile) return null;

    return (
        <picture>
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
            />
        </picture>
    );
}
