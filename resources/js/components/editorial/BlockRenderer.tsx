import React from 'react';
import type { Block } from '@/lib/types';
import Abstract from './Abstract';
import Download from './Download';
import CardsList from './CardsList';
import EditorialCard from './EditorialCard';
import Fallback from './Fallback';
import Hero from './Hero';
import Paragraph from './Paragraph';
import Gallery from './Gallery';
import Matrix from './Matrix';
import Video from './Video';

type BlockType =
    | 'abstract'
    | 'hero'
    | 'paragraph'
    | 'cardslist'
    | 'editorialcard'
    | 'download'
    | 'gallery'
    | 'matrix'
    | 'video';

const BLOCK_COMPONENTS: Record<
    BlockType,
    React.ComponentType<{ block: Block; children: React.ReactNode }>
> = {
    abstract: Abstract,
    hero: Hero,
    paragraph: Paragraph,
    cardslist: CardsList,
    editorialcard: EditorialCard,
    download: Download,
    gallery: Gallery,
    matrix: Matrix,
    video: Video,
};

function isBlockType(type: string): type is BlockType {
    return type in BLOCK_COMPONENTS;
}

function getBlockComponent(type: string) {
    if (isBlockType(type)) return BLOCK_COMPONENTS[type];
    return Fallback;
}

export default function BlockRenderer({ block }: { block: Block }) {
    if (!block) return null;

    const renderedChildren =
        Array.isArray(block.children) && block.children.length > 0
            ? block.children.map((child) => (
                  <BlockRenderer
                      key={child.id}
                      block={child}
                  />
              ))
            : null;

    return React.createElement(getBlockComponent(block.type), {
        block,
        children: renderedChildren,
    });
}
