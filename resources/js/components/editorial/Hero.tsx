import { cva } from 'class-variance-authority';
import Cta from '@/components/editorial/atom/Cta';
import Eyelet from '@/components/editorial/atom/Eyelet';
import Subtitle from '@/components/editorial/atom/Subtitle';
import Text from '@/components/editorial/atom/Text';
import Title from '@/components/editorial/atom/Title';
import type { Block, CtaBlock } from '@/lib/types';
import { cn } from '@/lib/utils';
import Picture from './Picture';

const heroSectionClasses = cva(
    'block-hero relative flex items-center md:flex-row',
    {
        variants: {
            mobileTextUnder: {
                true: 'flex-col',
                false: 'flex-row',
            },
            fullHeight: {
                true: 'min-h-screen',
                false: '',
            },
        },
    },
);

const heroPictureClasses = cva('block-hero__picture z-0', {
    variants: {
        mobileTextUnder: {
            true: 'relative w-full sm:absolute sm:inset-0',
            false: 'absolute inset-0',
        },
    },
});

const heroPictureImageClasses = cva('', {
    variants: {
        mobileTextUnder: {
            true: 'relative w-full sm:absolute sm:h-full sm:object-cover',
            false: 'absolute h-full w-full object-cover',
        },
    },
});

const heroWrapperClasses = cva(
    'block-hero__wrapper z-10 container mx-auto flex px-6 py-8 sm:py-24',
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

const heroCtasWrapper = cva(
    'block-hero__ctas flex w-full flex-col items-center justify-center gap-4 pt-4 sm:flex-row',
    {
        variants: {
            alignment: {
                'text-center': 'justify-center text-center',
                'text-left': 'justify-start text-left',
                'text-right': 'justify-end text-right',
            },
        },
    },
);

export default function Hero({ block }: { block: Block }) {
    if (!block) return null;

    const ctas = (block.children?.filter(
        (child) => child.type === 'dynamic-repeater-ctas',
    ) ?? []) as CtaBlock[];

    const imageDesktopData = block.images?.hero_image_desktop?.default || null;
    const imageMobileData = block.images?.hero_image_mobile?.default ?? null;
    const mobileTextUnder = block.content.text_under_mobile ?? false;
    const alignment = block.content.text_alignment;

    return (
        <section
            className={cn(
                heroSectionClasses({
                    mobileTextUnder,
                    fullHeight: block.content.full_height,
                }),
                'group',
                block.content.text_color,
                mobileTextUnder && 'hero-text-under',
            )}
        >
            {/* Picture Background */}
            {(imageDesktopData || imageMobileData) && (
                <div className={heroPictureClasses({ mobileTextUnder })}>
                    <Picture
                        image={imageDesktopData}
                        imageMobile={imageMobileData}
                        className={heroPictureImageClasses({ mobileTextUnder })}
                    />
                    <div className="absolute inset-0 bg-black/30" />
                </div>
            )}

            {/* Content Container */}
            <div className={heroWrapperClasses({ alignment })}>
                {/* Content */}
                <div className="block-hero__content relative flex flex-col gap-6 sm:max-w-3xl sm:bg-black/50 sm:p-12 sm:backdrop-blur-xs">
                    <div className="flex flex-col gap-2">
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

                    <Text content={block.content.text} />

                    {ctas.length > 0 && (
                        <div className={heroCtasWrapper({ alignment })}>
                            {ctas.map((cta) => (
                                <Cta
                                    key={cta.id}
                                    cta={cta.content!}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}
