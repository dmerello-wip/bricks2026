import Cta from '@/components/editorial/atom/Cta';
import Eyelet from '@/components/editorial/atom/Eyelet';
import Text from '@/components/editorial/atom/Text';
import Title from '@/components/editorial/atom/Title';
import type { Block, CtaBlock } from '@/lib/types';
import { cn } from '@/lib/utils';
import Picture from './Picture';

type EditorialCardProps = {
    block: Block;
    alignment?: string;
};

export default function EditorialCard({
    block,
    alignment = 'text-left',
}: EditorialCardProps) {
    if (!block) return null;

    const imageData = block.images?.card_image?.default || null;
    const ctas = (block.children?.filter(
        (child) => child.type === 'dynamic-repeater-ctas',
    ) ?? []) as CtaBlock[];

    return (
        <div className="block-editorial-card flex flex-col">
            {imageData && (
                <div className="relative w-full">
                    <Picture image={imageData} />
                </div>
            )}

            <div className={cn('flex flex-col gap-4 pt-5', alignment)}>
                <div className="flex flex-col gap-2">
                    <Eyelet
                        content={block.content.eyelet}
                        seoTag={block.content.eyelet_seo}
                    />

                    <Title
                        content={block.content.title}
                        seoTag={block.content.title_seo}
                        className="text-3xl font-medium md:text-3xl"
                    />
                </div>

                <Text
                    content={block.content.text}
                    className="line-clamp-3"
                />

                {ctas.length > 0 && (
                    <div
                        className={cn(
                            'mt-auto flex flex-wrap gap-3 pt-2',
                            (alignment === 'text-center' && 'justify-center') ||
                                (alignment === 'text-right' && 'justify-end'),
                        )}
                    >
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
    );
}
