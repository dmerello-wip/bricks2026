import { cva } from 'class-variance-authority';
import Eyelet from '@/components/editorial/atom/Eyelet';
import LogoOrnament from '@/components/editorial/atom/LogoOrnament';
import Subtitle from '@/components/editorial/atom/Subtitle';
import Text from '@/components/editorial/atom/Text';
import Title from '@/components/editorial/atom/Title';
import type { Block } from '@/lib/types';
import { cn } from '@/lib/utils';

const heroVideoSectionClasses = cva(
    'block-hero-video relative flex items-center overflow-hidden md:flex-row',
    {
        variants: {
            fullHeight: {
                true: 'min-h-screen',
                false: '',
            },
        },
    },
);

const heroVideoWrapperClasses = cva(
    'block-hero-video__wrapper z-10 container mx-auto flex px-6 py-8 sm:py-24',
    {
        variants: {
            alignment: {
                'text-center': 'justify-center text-center',
                'text-left': 'justify-start text-left',
                'text-right': 'justify-end text-left',
            },
        },
    },
);

const coverIframeStyle: React.CSSProperties = {
    width: '100vw',
    height: '56.25vw',
    minWidth: '177.78vh',
    minHeight: '100%',
};

function extractVideoId(input: string, provider: 'youtube' | 'vimeo'): string {
    if (provider === 'youtube') {
        return (
            input.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/)?.[1] ?? input
        );
    }
    return input.match(/vimeo\.com\/(\d+)/)?.[1] ?? input;
}

function HeroVideoBackground({ block }: { block: Block }) {
    const videoType = (block.content.video_type as string) ?? 'file';

    if (videoType === 'file') {
        const fileUrl = block.files?.video_file;
        if (!fileUrl) return null;

        return (
            <video
                className="absolute inset-0 h-full w-full object-cover"
                autoPlay
                loop
                muted
                playsInline
                preload="auto"
            >
                <source
                    src={fileUrl}
                    type="video/mp4"
                />
            </video>
        );
    }

    if (videoType === 'youtube' && block.content.youtube_id) {
        const id = extractVideoId(block.content.youtube_id, 'youtube');
        const src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&mute=1&loop=1&playlist=${id}&controls=0&showinfo=0&rel=0&playsinline=1&modestbranding=1&disablekb=1&iv_load_policy=3`;

        return (
            <iframe
                className="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
                style={coverIframeStyle}
                src={src}
                title="Background video"
                allow="autoplay; encrypted-media"
            />
        );
    }

    if (videoType === 'vimeo' && block.content.vimeo_id) {
        const id = extractVideoId(block.content.vimeo_id, 'vimeo');
        const src = `https://player.vimeo.com/video/${id}?background=1&autoplay=1&loop=1&muted=1`;

        return (
            <iframe
                className="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
                style={coverIframeStyle}
                src={src}
                title="Background video"
                allow="autoplay; encrypted-media"
            />
        );
    }

    return null;
}

export default function HeroVideo({ block }: { block: Block }) {
    if (!block) return null;

    const alignment = block.content.text_alignment;

    return (
        <section
            id={`block-${block.id}`}
            className={cn(
                heroVideoSectionClasses({
                    fullHeight: block.content.full_height,
                }),
                'group',
                block.content.text_color,
            )}
        >
            {/* Video Background */}
            <div className="block-hero-video__background absolute inset-0 z-0">
                <HeroVideoBackground block={block} />
                {/* Legibility overlay */}
                <div className="absolute inset-0 bg-black/50" />
            </div>

            {/* Content Container */}
            <div className={heroVideoWrapperClasses({ alignment })}>
                <div className="block-hero-video__content relative flex flex-col gap-4 sm:w-1/2 sm:max-w-3xl">
                    <div className="flex flex-col">
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
                    {block.content.text && (
                        <Text content={block.content.text} />
                    )}
                    <LogoOrnament
                        position="bottom"
                        color="secondary"
                    />
                    <LogoOrnament
                        position="top"
                        color="primary"
                    />
                </div>
            </div>
        </section>
    );
}
