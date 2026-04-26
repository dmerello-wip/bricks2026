import '../css/app.css';
import { createRoot } from 'react-dom/client';
import BlockRenderer from '@/components/editorial/BlockRenderer';
import { PreviewContext } from '@/lib/context/preview';
import type { Block } from '@/lib/types';

const blocks = (window as unknown as { __PREVIEW_BLOCKS__: Block[] })
    .__PREVIEW_BLOCKS__;
const container = document.getElementById('module-preview-root')!;

createRoot(container).render(
    <PreviewContext.Provider value={true}>
        <>
            {blocks.map((block, i) => (
                <BlockRenderer
                    key={block.id ?? i}
                    block={block}
                />
            ))}
        </>
    </PreviewContext.Provider>,
);
