import '../css/app.css';
import { useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import BlockRenderer from '@/components/editorial/BlockRenderer';
import { PreviewContext } from '@/lib/context/preview';
import type { Block } from '@/lib/types';

const block = (window as unknown as { __PREVIEW_BLOCK__: Block })
    .__PREVIEW_BLOCK__;
const container = document.getElementById('preview-root')!;

function BlockPreview({ block }: { block: Block }) {
    useEffect(() => {
        window.parent.dispatchEvent(new Event('resize'));
    }, []);

    return <BlockRenderer block={block} />;
}

createRoot(container).render(
    <PreviewContext.Provider value={true}>
        <BlockPreview block={block} />
    </PreviewContext.Provider>,
);
