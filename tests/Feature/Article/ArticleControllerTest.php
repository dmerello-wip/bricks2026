<?php

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Helper: create an Article with Italian translation and slug.
 *
 * @return array{article: Article, articleSlugIt: string}
 */
function createArticleWithTranslations(string $slugIt = 'articolo-uno'): array
{
    $article = Article::create(['published' => true]);

    DB::table('article_translations')->insert([
        ['article_id' => $article->id, 'locale' => 'it', 'active' => true, 'title' => 'Articolo Uno'],
    ]);

    DB::table('article_slugs')->insert([
        ['article_id' => $article->id, 'locale' => 'it', 'slug' => $slugIt, 'active' => true],
    ]);

    return ['article' => $article, 'articleSlugIt' => $slugIt];
}

/**
 * Helper: create a Category with Italian translation and slug.
 *
 * @return array{category: Category, categorySlugIt: string}
 */
function createCategoryWithTranslations(string $slugIt = 'cibo'): array
{
    $category = Category::create(['published' => true]);

    DB::table('category_translations')->insert([
        ['category_id' => $category->id, 'locale' => 'it', 'active' => true, 'title' => 'Cibo'],
    ]);

    DB::table('category_slugs')->insert([
        ['category_id' => $category->id, 'locale' => 'it', 'slug' => $slugIt, 'active' => true],
    ]);

    return ['category' => $category, 'categorySlugIt' => $slugIt];
}

/**
 * Helper: link an Article to a Category via twill_related.
 */
function relateArticleToCategory(Article $article, Category $category): void
{
    DB::table('twill_related')->insert([
        'subject_id' => $article->id,
        'subject_type' => Article::class,
        'related_id' => $category->id,
        'related_type' => Category::class,
        'browser_name' => 'categories',
        'position' => 1,
    ]);
}

it('returns a successful response for a published article', function () {
    ['article' => $article] = createArticleWithTranslations();
    ['category' => $category] = createCategoryWithTranslations();
    relateArticleToCategory($article, $category);

    $this->get('/it/novita/cibo/articolo-uno')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Article/Show'));
});

it('returns 404 for an unpublished article', function () {
    $article = Article::create(['published' => false]);
    DB::table('article_slugs')->insert([
        ['article_id' => $article->id, 'locale' => 'it', 'slug' => 'bozza-articolo', 'active' => true],
    ]);

    $this->get('/it/novita/cibo/bozza-articolo')->assertNotFound();
});

it('includes correct hreflang alternates with localized module prefix and category slug', function () {
    ['article' => $article] = createArticleWithTranslations('articolo-uno');
    ['category' => $category] = createCategoryWithTranslations('cibo');
    relateArticleToCategory($article, $category);

    $this->get('/it/novita/cibo/articolo-uno')
        ->assertInertia(fn ($page) => $page
            ->where('seo.alternates.it', url('/it/novita/cibo/articolo-uno'))
            ->where('seo.alternates.x-default', url('/it/novita/cibo/articolo-uno'))
        );
});

it('redirects to canonical slug when article slug has changed', function () {
    $article = Article::create(['published' => true]);
    DB::table('article_translations')->insert([
        ['article_id' => $article->id, 'locale' => 'it', 'active' => true, 'title' => 'Articolo Uno'],
    ]);
    DB::table('article_slugs')->insert([
        ['article_id' => $article->id, 'locale' => 'it', 'slug' => 'articolo-uno', 'active' => true],
        ['article_id' => $article->id, 'locale' => 'it', 'slug' => 'vecchio-slug-articolo', 'active' => false],
    ]);

    ['category' => $category] = createCategoryWithTranslations();
    relateArticleToCategory($article, $category);

    $this->get('/it/novita/cibo/vecchio-slug-articolo')
        ->assertRedirect('/it/novita/cibo/articolo-uno');
});

it('redirects to first category when category slug does not match', function () {
    ['article' => $article] = createArticleWithTranslations();
    ['category' => $category] = createCategoryWithTranslations('cibo');
    relateArticleToCategory($article, $category);

    $this->get('/it/novita/categoria-sbagliata/articolo-uno')
        ->assertRedirect('/it/novita/cibo/articolo-uno');
});
