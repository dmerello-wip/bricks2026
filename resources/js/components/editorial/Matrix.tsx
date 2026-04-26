import { cva } from 'class-variance-authority';
import type { Block } from '@/lib/types';
import Eyelet from '@/components/editorial/atom/Eyelet';
import Title from '@/components/editorial/atom/Title';
import Picture from './Picture';

const sectionClasses = cva('block-matrix', {
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

function MatrixItem({ block }: { block: Block }) {
    const imageData = block.images?.image?.default || null;
    const linkType = block.content?.link_type ?? 'none';
    const externalUrl = block.content?.link_external ?? null;
    const internalUrl = block.content?.cta_link ?? null;

    const href =
        linkType === 'external'
            ? externalUrl
            : linkType === 'internal'
              ? internalUrl
              : null;

    const content = imageData ? (
        <Picture
            image={imageData}
            className="h-20 w-20 object-contain"
        />
    ) : null;

    if (!content) return null;

    if (href) {
        return (
            <a
                href={href}
                target={linkType === 'external' ? '_blank' : undefined}
                rel={
                    linkType === 'external' ? 'noopener noreferrer' : undefined
                }
                className="flex items-center justify-center opacity-80 transition-opacity hover:opacity-100"
            >
                {content}
            </a>
        );
    }

    return <div className="flex items-center justify-center">{content}</div>;
}

export default function Matrix({ block }: { block: Block }) {
    if (!block) return null;

    const items =
        block.children?.filter(
            (child) => child.type === 'dynamic-repeater-matrix_items',
        ) ?? [];

    const noPaddingBottom = block.content?.no_padding_bottom ?? false;

    return (
        <section
            className={sectionClasses({ noPaddingBottom })}
            style={{ backgroundColor: block.content?.bg_color || undefined }}
        >
            <div className="container mx-auto md:max-w-7xl">
                {(block.content?.eyelet || block.content?.title) && (
                    <div className="mb-12 flex flex-col items-center gap-2 text-center">
                        <Eyelet
                            content={block.content.eyelet}
                            seoTag={block.content.eyelet_seo}
                        />
                        <Title
                            content={block.content.title}
                            seoTag={block.content.title_seo}
                        />
                    </div>
                )}

                {items.length > 0 && (
                    <div className="mx-auto flex w-full max-w-344 flex-wrap justify-center gap-12">
                        {items.map((item) => (
                            <MatrixItem
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
