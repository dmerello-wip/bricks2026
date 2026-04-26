export type * from './auth';
export type * from './navigation';
export type * from './ui';
export type {
    ArticleModel,
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
import type { Block } from './swagger';
import type { MenuItem } from './navigation';

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
        [key: string]: any;
    };
    blocks: Block[];
    [key: string]: unknown;
};
