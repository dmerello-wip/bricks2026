import { cva } from 'class-variance-authority';
import type { Block } from '@/lib/types';
import { cn } from '@/lib/utils';
import EditorialCard from './EditorialCard';

const colsByCount: Record<number, string> = {
    0: 'grid-cols-1',
    1: 'grid-cols-1',
    2: 'grid-cols-1 sm:grid-cols-2',
    3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
};

const sectionClasses = cva('block-cards-list', {
    variants: {
        noMargin: {
            true: 'pt-16 pb-0',
            false: 'py-16',
        },
    },
    defaultVariants: {
        noMargin: false,
    },
});

export default function CardsList({ block }: { block: Block }) {
    if (!block) return null;

    const cards =
        block.children?.filter(
            (child) => child.type === 'dynamic-repeater-cards',
        ) ?? [];
    const alignment = block.content.text_alignment;
    const noMargin = block.content.no_margin ?? false;
    const gridColsClass =
        colsByCount[Math.min(cards.length, 4)] ?? 'grid-cols-1';

    return (
        <section
            className={cn(
                sectionClasses({ noMargin }),
                'group',
                block.content.text_color,
            )}
            style={{ backgroundColor: block.content.bg_color || undefined }}
        >
            <div className={'container mx-auto px-6'}>
                <div className={cn('grid gap-12 md:gap-8', gridColsClass)}>
                    {cards.map((card) => (
                        <EditorialCard
                            key={card.id}
                            block={card}
                            alignment={alignment}
                        />
                    ))}
                </div>
            </div>
        </section>
    );
}
