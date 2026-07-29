import { cva } from 'class-variance-authority';
import { useState } from 'react';
import Cta, { resolveCtaHref } from '@/components/editorial/atom/Cta';
import Eyelet from '@/components/editorial/atom/Eyelet';
import Subtitle from '@/components/editorial/atom/Subtitle';
import Title from '@/components/editorial/atom/Title';
import type { Block, CtaBlock } from '@/lib/types';
import { cn } from '@/lib/utils';
import ImageZoomModal from './ImageZoomModal';
import Picture from './Picture';

const sectionClasses = cva('block-masonry-gallery', {
    variants: {
        noPaddingBottom: {
            true: 'pt-16',
            false: 'py-16',
        },
    },
    defaultVariants: {
        noPaddingBottom: false,
    },
});

function MasonryItem({ block }: { block: Block }) {
    const [isZoomOpen, setIsZoomOpen] = useState(false);

    const imageData = block.images?.masonry_image?.default ?? null;

    /**
     * The `zoomed` crop only exists on items saved after it was added to
     * config/twill.php, so older content falls back to the `default` crop.
     */
    const zoomImageData = block.images?.masonry_image?.zoomed ?? imageData;

    const ctas = (
        (block.children?.filter(
            (child) => child.type === 'dynamic-repeater-ctas',
        ) ?? []) as CtaBlock[]
    ).filter((cta) => resolveCtaHref(cta.content));

    if (!imageData && ctas.length === 0) return null;

    const isZoomable = imageData !== null && ctas.length === 0;

    return (
        <div className="block-masonry-gallery__item mb-4 break-inside-avoid">
            {imageData &&
                (isZoomable ? (
                    <button
                        type="button"
                        onClick={() => setIsZoomOpen(true)}
                        aria-label={imageData.alt || 'Zoom image'}
                        className="block w-full cursor-zoom-in rounded-md focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-hidden"
                    >
                        <Picture
                            image={imageData}
                            className="block h-auto w-full rounded-md"
                        />
                    </button>
                ) : (
                    <Picture
                        image={imageData}
                        className="block h-auto w-full rounded-md"
                    />
                ))}

            {ctas.length > 0 && (
                <div className="flex flex-wrap gap-2 pt-3">
                    {ctas.map((cta) => (
                        <Cta
                            key={cta.id}
                            cta={cta.content!}
                        />
                    ))}
                </div>
            )}

            {isZoomable && zoomImageData && (
                <ImageZoomModal
                    image={zoomImageData}
                    open={isZoomOpen}
                    onOpenChange={setIsZoomOpen}
                />
            )}
        </div>
    );
}

export default function MasonryGallery({ block }: { block: Block }) {
    if (!block) return null;

    const items =
        block.children?.filter(
            (child) => child.type === 'dynamic-repeater-masonry_items',
        ) ?? [];

    const noPaddingBottom = block.content?.no_padding_bottom ?? false;
    const textColor = block.content?.text_color ?? 'block-text-dark';

    return (
        <section
            id={`block-${block.id}`}
            className={cn(
                sectionClasses({ noPaddingBottom }),
                'group',
                textColor,
            )}
            style={{ backgroundColor: block.content?.bg_color || undefined }}
        >
            <div className="block-masonry-gallery__container container mx-auto px-6">
                <div className="flex flex-col gap-2 pb-10">
                    <Eyelet
                        content={block.content.eyelet}
                        seoTag={block.content.eyelet_seo}
                    />
                    <Title
                        content={block.content.title}
                        seoTag={block.content.title_seo}
                    />
                    <Subtitle
                        content={block.content.subtitle}
                        seoTag={block.content.subtitle_seo}
                    />
                </div>

                {items.length > 0 && (
                    <div className="columns-1 gap-4 sm:columns-2 md:columns-3 lg:columns-4">
                        {items.map((item) => (
                            <MasonryItem
                                key={item.id}
                                block={item}
                            />
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
