import { usePage } from '@inertiajs/react';
import BlockRenderer from '@/components/editorial/BlockRenderer';
import PageLayout from '@/components/layout/PageLayout';
import SeoHead from '@/components/seo/SeoHead';
import type { SharedData, SeoData, HomepageModel } from '@/lib/types';
import type { Block } from '@/lib/types';

type HomepageProps = SharedData & {
    page: HomepageModel;
    seo: SeoData;
};

export default function Homepage() {
    const { blocks, seo } = usePage<HomepageProps>().props;

    const blockList = Array.isArray(blocks) ? blocks : [];

    return (
        <PageLayout>
            <SeoHead seo={seo} />
            {blockList.length > 0 && (
                <>
                    {blockList.map((block: Block) => (
                        <BlockRenderer
                            key={block.id}
                            block={block}
                        />
                    ))}
                </>
            )}
        </PageLayout>
    );
}
