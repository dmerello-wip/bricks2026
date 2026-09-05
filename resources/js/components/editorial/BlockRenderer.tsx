import React from 'react';
import SubscriptionForm from '@/components/form/SubscriptionForm';
import type { Block } from '@/lib/types';
import Abstract from './Abstract';
import CardsList from './CardsList';
import Download from './Download';
import EditorialCard from './EditorialCard';
import Fallback from './Fallback';
import Gallery from './Gallery';
import Hero from './Hero';
import HeroVideo from './HeroVideo';
import MasonryGallery from './MasonryGallery';
import Matrix from './Matrix';
import Paragraph from './Paragraph';
import Reveal from './Reveal';
import Video from './Video';

type BlockType =
    | 'abstract'
    | 'hero'
    | 'herovideo'
    | 'paragraph'
    | 'cardslist'
    | 'editorialcard'
    | 'download'
    | 'gallery'
    | 'masonrygallery'
    | 'matrix'
    | 'video'
    | 'subscriptionform';

const BLOCK_COMPONENTS: Record<
    BlockType,
    React.ComponentType<{ block: Block; children: React.ReactNode }>
> = {
    abstract: Abstract,
    hero: Hero,
    herovideo: HeroVideo,
    paragraph: Paragraph,
    cardslist: CardsList,
    editorialcard: EditorialCard,
    download: Download,
    gallery: Gallery,
    masonrygallery: MasonryGallery,
    matrix: Matrix,
    video: Video,
    subscriptionform: SubscriptionForm,
};

function isBlockType(type: string): type is BlockType {
    return type in BLOCK_COMPONENTS;
}

function getBlockComponent(type: string) {
    if (isBlockType(type)) return BLOCK_COMPONENTS[type];
    return Fallback;
}

export default function BlockRenderer({
    block,
    depth = 0,
}: {
    block: Block;
    depth?: number;
}) {
    if (!block) return null;

    const renderedChildren =
        Array.isArray(block.children) && block.children.length > 0
            ? block.children.map((child) => (
                  <BlockRenderer
                      key={child.id}
                      block={child}
                      depth={depth + 1}
                  />
              ))
            : null;

    const renderedBlock = React.createElement(getBlockComponent(block.type), {
        block,
        children: renderedChildren,
    });

    /**
     * Only top-level blocks own a scroll-reveal root: nested blocks animate
     * with their parent, and a nested root would hide them until it intersects
     * on its own.
     */
    if (depth > 0) return renderedBlock;

    return <Reveal>{renderedBlock}</Reveal>;
}
