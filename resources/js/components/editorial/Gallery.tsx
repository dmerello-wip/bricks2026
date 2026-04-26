import { Splide, SplideSlide } from '@splidejs/react-splide';
import { cva } from 'class-variance-authority';
import '@splidejs/react-splide/css';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import { useRef } from 'react';
import VideoEmbed from '@/components/VideoPlayer';
import type { Block } from '@/lib/types';
import { cn } from '@/lib/utils';
import Picture from './Picture';

const sectionClasses = cva('block-gallery', {
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

function GalleryItem({ block }: { block: Block }) {
    const imageData = block.images?.image?.default || null;
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
    const arrowClasses =
        'p-2 rounded-full bg-white/80 text-gray-800 shadow-md pointer-events-auto hover:bg-white';

    return (
        <section
            className={cn(
                sectionClasses({ noPaddingBottom }),
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
                    options={{
                        perPage: 2,
                        gap: '2rem',
                        pagination: false,
                        arrows: false,
                        padding: { left: '0', right: '10%' },
                        autoHeight: true,
                        autoWidth: false,
                        breakpoints: {
                            1280: {
                                perPage: 1,
                            },
                        },
                    }}
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
