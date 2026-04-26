/* eslint-disable */
/* tslint:disable */
/*
 * ---------------------------------------------------------------
 * ## THIS FILE WAS GENERATED VIA SWAGGER-TYPESCRIPT-API        ##
 * ##                                                           ##
 * ## AUTHOR: acacode                                           ##
 * ## SOURCE: https://github.com/acacode/swagger-typescript-api ##
 * ---------------------------------------------------------------
 */

export interface ArticleModel {
    /**
     * Article model data as returned by $article->toArray().
     * Traits: HasTranslation (title, description), HasMedias, HasSlug, HasRelated, HasSeoData.
     */
    id: number;
    published: boolean;
    title?: string | null;
    description?: string | null;
    /** @format date-time */
    created_at?: string;
    /** @format date-time */
    updated_at?: string;
    /** @format date-time */
    deleted_at?: string | null;
    medias?: TwillMedia[];
    related?: object[];
}

export interface HomepageModel {
    /**
     * Homepage model data as returned by $homepage->toArray().
     * Traits: HasTranslation (title), HasMedias, HasSeoData.
     */
    id: number;
    published: boolean;
    title?: string | null;
    /** @format date-time */
    created_at?: string;
    /** @format date-time */
    updated_at?: string;
    /** @format date-time */
    deleted_at?: string | null;
    medias?: TwillMedia[];
}

export interface PageModel {
    /**
     * CMS Page model data as returned by $page->toArray().
     * Traits: HasTranslation (title, description), HasMedias, HasSeoData,
     *         HasSlug, HasPosition, HasNesting.
     */
    id: number;
    published: boolean;
    title?: string | null;
    description?: string | null;
    position?: number | null;
    _lft?: number;
    _rgt?: number;
    parent_id?: number | null;
    /** @format date-time */
    created_at?: string;
    /** @format date-time */
    updated_at?: string;
    /** @format date-time */
    deleted_at?: string | null;
    medias?: TwillMedia[];
}

export interface Block {
    /** Formatted block data returned by TwillBlockService::formatBlock(). */
    id: number;
    parent_id?: number | null;
    type: string;
    content: Record<string, any>;
    medias?: TwillMedia[];
    children?: Block[];
    images?: Record<string, Record<string, ImageData>>;
    files?: Record<string, string>;
}

export interface CtaContent {
    /** A Block with its content typed as CtaContent. */
    cta_label: string | null;
    cta_style: 'primary' | 'secondary';
    cta_type: 'internal' | 'external' | 'download';
    cta_link: string | null;
    cta_target_blank: boolean;
    cta_dl_link?: string | null;
    cta_dl_filename?: string | null;
}

export type CtaBlock = Block & {
    content?: CtaContent;
};

export interface ImageData {
    /** Processed image data returned by ImageService::buildImageData(). */
    src: string;
    width?: number | null;
    height?: number | null;
    alt: string;
}

export interface SeoData {
    /** SEO metadata resolved by SeoService::resolve(). */
    title?: string | null;
    description?: string | null;
    canonical: string;
    og_title?: string | null;
    og_description?: string | null;
    og_image?: string | null;
    no_index: boolean;
    alternates: Record<string, string>;
}

export interface TwillMedia {
    /** Raw Twill media model, as returned via the medias() relationship. */
    id: number;
    uuid: string;
    filename: string;
    width?: number | null;
    height?: number | null;
    size?: number;
    mime_type?: string;
    /** @format date-time */
    created_at?: string;
    /** @format date-time */
    updated_at?: string;
    pivot?: {
        role?: string;
        crop?: string;
    } | null;
}
