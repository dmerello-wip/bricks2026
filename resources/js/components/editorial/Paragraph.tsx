import Eyelet from '@/components/editorial/atom/Eyelet';
import type { Block } from '@/lib/types';
import { cn } from '@/lib/utils';
import Text from './atom/Text';
import Title from './atom/Title';

export default function Paragraph({ block }: { block: Block }) {
    if (!block) return null;

    return (
        <section
            className={cn(
                'block-paragraph group relative flex items-center',
                block.content.text_color,
            )}
            style={{ backgroundColor: block.content.bg_color }}
        >
            {/* Content Container */}
            <div
                className={cn(
                    'block-paragraph__wrapper container max-w-4xl p-6',
                    block.content.no_margin ? 'pt-16' : 'py-16',
                )}
            >
                {/* Content */}
                <div
                    className={cn(
                        'block-paragraph__content',
                        block.content.text_alignment === 'text-left'
                            ? 'text-left'
                            : block.content.text_alignment === 'text-right'
                              ? 'text-right'
                              : 'text-center',
                    )}
                >
                    <div className="flex flex-col gap-2">
                        <Eyelet
                            content={block.content.eyelet}
                            seoTag={block.content.eyelet_seo}
                        />

                        <Title
                            content={block.content.title}
                            seoTag={block.content.title_seo}
                        />
                    </div>

                    <Text
                        content={block.content.text}
                        className={
                            block.content.columns === 'cols-2'
                                ? 'md:columns-2'
                                : 'columns-1'
                        }
                    />
                </div>
            </div>
        </section>
    );
}
