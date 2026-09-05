import { Splide, SplideSlide } from '@splidejs/react-splide';
import { cva } from 'class-variance-authority';
import '@splidejs/react-splide/css';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import { useRef } from 'react';
import VideoEmbed from '@/components/VideoPlayer';
import type { Block } from '@/lib/types';
import { cn } from '@/lib/utils';
import Picture from './Picture';

type GalleryLayout = 'peek' | 'centered';

const sectionClasses = cva('block-gallery', {
    variants: {
        noPaddingBottom: {
            true: 'pt-16',
            false: 'py-16',
        },
        layout: {
            peek: '',
            centered: 'overflow-hidden',
        },
    },
    defaultVariants: {
        noPaddingBottom: false,
        layout: 'peek',
    },
});

const carouselClasses = cva('block-gallery__carousel', {
    variants: {
        layout: {
            peek: 'block-gallery__carousel--peek',
            centered: 'block-gallery__carousel--centered',
        },
    },
    defaultVariants: {
        layout: 'peek',
    },
});

/**
 * `updateOnMove` moves the is-active / is-prev / is-next classes at the start of
 * the transition instead of the end, so the slide scaling in vendors.css runs in
 * sync with the carousel movement.
 */
const sharedOptions = {
    gap: '2rem',
    pagination: false,
    arrows: false,
    updateOnMove: true,
};

const peekOptions = {
    ...sharedOptions,
    perPage: 2,
    padding: { left: '0', right: '10%' },
    autoHeight: true,
    autoWidth: false,
    breakpoints: {
        1280: {
            perPage: 1,
        },
    },
};

/**
 * `trimSpace: false` lets the first and last slide reach the centre when there
 * are too few items to loop.
 */
const centeredOptions = {
    ...sharedOptions,
    perPage: 2.5,
    focus: 'center',
    trimSpace: false,
    breakpoints: {
        1280: {
            perPage: 1.5,
        },
        768: {
            perPage: 1.1,
            gap: '1rem',
        },
    },
};

/**
 * The centered layout loops: the clones Splide generates are what fill the row
 * on either side of the centred slide, so without them the gallery opens with
 * empty space instead of a full row. A single item has nothing to wrap around.
 */
function canLoop(items: Block[]): boolean {
    return items.length > 1;
}

function GalleryItem({ block }: { block: Block }) {
    const imageData = block.images?.gallery_image?.default || null;
    const caption = block.content?.caption;
    const isVideo = block.content?.item_type === 'video';

    return (
        <SplideSlide className="h-auto!">
            <div className="flex flex-col gap-3">
                {isVideo ? (
                    <VideoEmbed
                        videoType={block.content?.video_type}
                        youtubeInput={block.content?.youtube_id}
                        vimeoInput={block.content?.vimeo_id}
                        fileUrl={block.files?.video_file}
                    />
                ) : (
                    imageData && (
                        <Picture
                            image={imageData}
                            className="w-full"
                            reveal
                        />
                    )
                )}

                {caption && (
                    <p className="group-[.block-text-dark]:text-black group-[.block-text-light]:text-white">
                        {caption}
                    </p>
                )}
            </div>
        </SplideSlide>
    );
}

export default function Gallery({ block }: { block: Block }) {
    const splideRef = useRef<Splide>(null);

    if (!block) return null;

    const items =
        block.children?.filter(
            (child) => child.type === 'dynamic-repeater-gallery_items',
        ) ?? [];

    const noPaddingBottom = block.content?.no_padding_bottom ?? false;
    const textColor = block.content?.text_color ?? 'block-text-dark';
    const layout: GalleryLayout =
        block.content?.layout === 'centered' ? 'centered' : 'peek';
    const arrowClasses =
        'p-2 rounded-full bg-white/80 text-gray-800 shadow-md pointer-events-auto hover:bg-white';

    return (
        <section
            id={`block-${block.id}`}
            className={cn(
                sectionClasses({ noPaddingBottom, layout }),
                'group',
                textColor,
            )}
            style={{ backgroundColor: block.content?.bg_color || undefined }}
        >
            <div className="block-gallery__inner relative container mx-auto">
                <div className="block-gallery__arrows pointer-events-none absolute left-1/2 z-10 container flex h-full -translate-x-1/2 items-center justify-between px-10 max-md:hidden">
                    <button
                        onClick={() => splideRef.current?.splide?.go('<')}
                        className={arrowClasses}
                        aria-label="Previous"
                    >
                        <ArrowLeft size={16} />
                    </button>

                    <button
                        onClick={() => splideRef.current?.splide?.go('>')}
                        className={arrowClasses}
                        aria-label="Next"
                    >
                        <ArrowRight size={16} />
                    </button>
                </div>
                <Splide
                    ref={splideRef}
                    className={carouselClasses({ layout })}
                    options={
                        layout === 'centered'
                            ? {
                                  ...centeredOptions,
                                  type: canLoop(items) ? 'loop' : 'slide',
                              }
                            : peekOptions
                    }
                >
                    {items.map((item) => (
                        <GalleryItem
                            key={item.id}
                            block={item}
                        />
                    ))}
                </Splide>
            </div>
        </section>
    );
}
