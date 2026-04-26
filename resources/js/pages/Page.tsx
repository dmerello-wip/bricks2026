import { usePage } from '@inertiajs/react';
import BlockRenderer from '@/components/editorial/BlockRenderer';
import PageLayout from '@/components/layout/PageLayout';
import SeoHead from '@/components/seo/SeoHead';
import type { SharedData, SeoData, PageModel } from '@/lib/types';
import type { Block } from '@/lib/types';

type CmsPageProps = SharedData & {
    page: PageModel;
    seo: SeoData;
};

export default function Page() {
    const { blocks, seo } = usePage<CmsPageProps>().props;

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
