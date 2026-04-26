import { Head } from '@inertiajs/react';
import type { SeoData } from '@/lib/types';

type Props = {
    seo: SeoData;
};

export default function SeoHead({ seo }: Props) {
    return (
        <Head>
            {seo.title && <title>{seo.title}</title>}
            {seo.description && (
                <meta
                    name="description"
                    content={seo.description}
                />
            )}
            <link
                rel="canonical"
                href={seo.canonical}
            />
            {seo.no_index && (
                <meta
                    name="robots"
                    content="noindex, nofollow"
                />
            )}
            {seo.og_title && (
                <meta
                    property="og:title"
                    content={seo.og_title}
                />
            )}
            {seo.og_description && (
                <meta
                    property="og:description"
                    content={seo.og_description}
                />
            )}
            {seo.og_image && (
                <meta
                    property="og:image"
                    content={seo.og_image}
                />
            )}
            {Object.entries(seo.alternates).map(([locale, href]) => (
                <link
                    key={locale}
                    rel="alternate"
                    hrefLang={locale}
                    href={href}
                />
            ))}
        </Head>
    );
}
