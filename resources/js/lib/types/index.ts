export type * from './auth';
export type * from './navigation';
export type * from './ui';
export type {
    ArticleModel,
    EventModel,
    HomepageModel,
    PageModel,
    ImageData,
    TwillMedia,
    SeoData,
    CtaContent,
    Block,
    CtaBlock,
} from './swagger';

import type { Auth } from './auth';
import type { MenuItem } from './navigation';
import type { Block } from './swagger';

export type Locale = {
    name: string;
    script: string;
    native: string;
    regional: string;
};

export type SharedData = {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    locale: string;
    locales: Record<string, Locale>;
    localizedURL: string;
    /** Route prefix translations per locale, e.g. { it: { article: 'articolo' }, en: { article: 'article' } } */
    routePrefixes: Record<string, Record<string, string>>;
    menu: {
        primary: MenuItem[];
        footer: MenuItem[];
    };
    page: {
        title: string;
        [key: string]: unknown;
    };
    blocks: Block[];
    [key: string]: unknown;
};
