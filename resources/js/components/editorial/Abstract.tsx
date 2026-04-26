import { cva } from 'class-variance-authority';
import Cta from '@/components/editorial/atom/Cta';
import Eyelet from '@/components/editorial/atom/Eyelet';
import Subtitle from '@/components/editorial/atom/Subtitle';
import Text from '@/components/editorial/atom/Text';
import Title from '@/components/editorial/atom/Title';
import type { Block, CtaBlock } from '@/lib/types';
import { cn } from '@/lib/utils';
import Picture from './Picture';

const sectionClasses = cva('block-abstract relative w-full', {
    variants: {
        noPaddingBottom: {
            true: 'pt-16',
            false: 'py-16',
        },
    },
});

const containerClasses = cva(
    'block-abstract__container container mx-auto flex flex-col flex-col-reverse items-center gap-14 px-6',
    {
        variants: {
            alignment: {
                left: 'md:flex-row',
                right: 'md:flex-row-reverse',
            },
        },
    },
);

export default function Abstract({ block }: { block: Block }) {
    if (!block) return null;

    const alignment = block.content.alignment ?? 'left';
    const noPaddingBottom = block.content.no_padding_bottom ?? false;
    const imageData = block.images?.abstract_image?.default ?? null;
    const ctas = (block.children?.filter(
        (child) => child.type === 'dynamic-repeater-ctas',
    ) ?? []) as CtaBlock[];

    return (
        <section
            className={cn(
                sectionClasses({ noPaddingBottom }),
                'group',
                block.content.text_color,
            )}
            style={
                block.content.bg_color
                    ? { backgroundColor: block.content.bg_color }
                    : undefined
            }
        >
            <div className={containerClasses({ alignment })}>
                {/* Text content */}
                <div className="block-abstract__content flex flex-1 basis-[45%] flex-col gap-6">
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
                        <div className="flex flex-wrap gap-2 pt-4">
                            {ctas.map((cta) => (
                                <Cta
                                    key={cta.id}
                                    cta={cta.content!}
                                />
                            ))}
                        </div>
                    )}
                </div>

                {/* Image */}
                {imageData && (
                    <div className="block-abstract__image relative flex-1 basis-[55%]">
                        <Picture
                            image={imageData}
                            className="size-full"
                        />
                    </div>
                )}
            </div>
        </section>
    );
}
