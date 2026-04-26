import { Link, usePage } from '@inertiajs/react';
import Eyelet from '@/components/editorial/atom/Eyelet';
import PageLayout from '@/components/layout/PageLayout';
import SeoHead from '@/components/seo/SeoHead';

import AppLink from '@/components/ui/AppLink';
import type { SeoData, SharedData } from '@/lib/types';

type ArticleListItem = {
    id: number;
    title: string | null;
    description: string | null;
    created_at: string;
    url: string;
};

type PaginatedArticles = {
    data: ArticleListItem[];
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type ArticleListProps = SharedData & {
    category: {
        title: string | null;
        description: string | null;
    };
    articles: PaginatedArticles;
    seo: SeoData;
};

export default function ArticleList() {
    const { category, articles, seo } = usePage<ArticleListProps>().props;

    return (
        <PageLayout>
            <SeoHead seo={seo} />

            <div className="container mx-auto max-w-6xl px-6 pt-16 pb-24">
                <header className="mb-12">
                    <h1 className="text-4xl font-bold text-primary md:text-5xl">
                        {category.title}
                    </h1>
                    {category.description && (
                        <p className="mt-4 text-lg text-muted-foreground">
                            {category.description}
                        </p>
                    )}
                </header>

                {articles.data.length === 0 ? (
                    <p className="text-muted-foreground">
                        Nessun articolo trovato.
                    </p>
                ) : (
                    <div className="articles">
                        {articles.data.map((article) => (
                            <Link
                                className="articles__item flex border-b py-8"
                                key={article.id}
                                href={article.url}
                            >
                                <div className="articles__item__content">
                                    <Eyelet content={article.created_at} />
                                    <div className="grow text-2xl font-bold text-primary">
                                        {article.title}
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}

                {articles.last_page > 1 && (
                    <nav className="mt-12 flex justify-center gap-2">
                        {articles.links.map((link, index) =>
                            link.url ? (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={[
                                        'rounded border px-3 py-1 text-sm',
                                        link.active
                                            ? 'border-primary bg-primary text-primary-foreground'
                                            : 'border-border hover:bg-muted',
                                    ].join(' ')}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ) : (
                                <span
                                    key={index}
                                    className="rounded border border-border px-3 py-1 text-sm text-muted-foreground"
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ),
                        )}
                    </nav>
                )}
            </div>
        </PageLayout>
    );
}
