import { usePage } from '@inertiajs/react';
import Eyelet from '@/components/editorial/atom/Eyelet';
import Title from '@/components/editorial/atom/Title';
import BlockRenderer from '@/components/editorial/BlockRenderer';
import PageLayout from '@/components/layout/PageLayout';
import SeoHead from '@/components/seo/SeoHead';
import type { ArticleModel, SeoData, SharedData } from '@/lib/types';
import type { Block } from '@/lib/types';

type ArticleShowProps = SharedData & {
    article: ArticleModel;
    categoryName: string;
    seo: SeoData;
};

export default function ArticleShow() {
    const { article, blocks, categoryName, seo } =
        usePage<ArticleShowProps>().props;

    const blockList = Array.isArray(blocks) ? blocks : [];

    return (
        <PageLayout>
            <SeoHead seo={seo} />
            <article className="pt-16">
                <div className="container mx-auto flex max-w-4xl flex-col gap-2">
                    {categoryName && <Eyelet content={categoryName} />}
                    <Title
                        content={article.title!}
                        seoTag="h1"
                    />
                </div>
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
            </article>
        </PageLayout>
    );
}
