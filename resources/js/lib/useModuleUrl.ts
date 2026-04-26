import { usePage } from '@inertiajs/react';
import { show as articleShow } from '@/actions/App/Http/Controllers/ArticleController';
import type { SharedData } from '@/lib/types';

/**
 * Returns Wayfinder URL builders for localized modules.
 *
 * The route prefix (e.g. "articolo" / "article") is resolved automatically
 * from the current locale using the shared `routePrefixes` prop.
 *
 * Usage:
 *   const { article } = useModuleUrl();
 *   article.url('my-slug')   // → /it/articolo/my-slug
 *   article.show('my-slug')  // → { url: '/it/articolo/my-slug', method: 'get' }
 */
export function useModuleUrl() {
    const { locale, routePrefixes } = usePage<SharedData>().props;
    const prefixes = routePrefixes[locale] ?? {};

    return {
        article: {
            url: (slug: string) =>
                articleShow.url({
                    prefix: prefixes.article ?? 'article',
                    slug,
                }),
            show: (slug: string) =>
                articleShow({ prefix: prefixes.article ?? 'article', slug }),
        },
    };
}
