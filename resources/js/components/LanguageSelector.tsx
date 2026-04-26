import { usePage, router } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/DropdownMenu';
import type { SharedData } from '@/lib/types';

export function LanguageSelector() {
    const props = usePage<SharedData>().props;
    const { locale, locales, localizedURL, routePrefixes } = props;
    const seo = props.seo as
        | { alternates?: Record<string, string> }
        | undefined;

    const resolveTargetUrl = (newLocale: string): string => {
        // Strategy 1: use pre-computed alternate URLs from SEO service (handles translated slugs)
        if (seo?.alternates?.[newLocale]) {
            return seo.alternates[newLocale];
        }

        // Strategy 2: swap locale prefix + translate module segment in the current URL
        try {
            const pathname = new URL(localizedURL).pathname;
            const segments = pathname.split('/').filter(Boolean);

            if (segments[0] === locale) {
                segments[0] = newLocale;

                if (segments[1]) {
                    const currentPrefixes = routePrefixes[locale] ?? {};
                    const newPrefixes = routePrefixes[newLocale] ?? {};
                    const moduleKey = Object.keys(currentPrefixes).find(
                        (key) => currentPrefixes[key] === segments[1],
                    );
                    if (moduleKey && newPrefixes[moduleKey]) {
                        segments[1] = newPrefixes[moduleKey];
                    }
                }

                return '/' + segments.join('/');
            }
        } catch {
            // fallthrough
        }

        // Strategy 3: fallback to locale homepage
        return `/${newLocale}`;
    };

    const changeLanguage = (newLocale: string) => {
        router.visit(resolveTargetUrl(newLocale));
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline">
                    {locale}
                    <ChevronDown />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {Object.entries(locales).map(([code, localeData]) => (
                    <DropdownMenuItem
                        key={code}
                        onClick={() => changeLanguage(code)}
                        className={code === locale ? 'font-semibold' : ''}
                    >
                        {localeData.native} ({localeData.name})
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
