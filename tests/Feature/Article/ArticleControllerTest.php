<?php

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Helper: create an Article with translations and slugs for en and it.
 *
 * @return array{article: Article, articleSlugEn: string, articleSlugIt: string}
 */
function createArticleWithTranslations(string $slugEn = 'article-one', string $slugIt = 'articolo-uno'): array
{
    $article = Article::create(['published' => true]);

    DB::table('article_translations')->insert([
        ['article_id' => $article->id, 'locale' => 'en', 'active' => true, 'title' => 'Article One'],
        ['article_id' => $article->id, 'locale' => 'it', 'active' => true, 'title' => 'Articolo Uno'],
    ]);

    DB::table('article_slugs')->insert([
        ['article_id' => $article->id, 'locale' => 'en', 'slug' => $slugEn, 'active' => true],
        ['article_id' => $article->id, 'locale' => 'it', 'slug' => $slugIt, 'active' => true],
    ]);

    return ['article' => $article, 'articleSlugEn' => $slugEn, 'articleSlugIt' => $slugIt];
}

/**
 * Helper: create a Category with translations and slugs for en and it.
 *
 * @return array{category: Category, categorySlugEn: string, categorySlugIt: string}
 */
function createCategoryWithTranslations(string $slugEn = 'food', string $slugIt = 'cibo'): array
{
    $category = Category::create(['published' => true]);

    DB::table('category_translations')->insert([
        ['category_id' => $category->id, 'locale' => 'en', 'active' => true, 'title' => 'Food'],
        ['category_id' => $category->id, 'locale' => 'it', 'active' => true, 'title' => 'Cibo'],
    ]);

    DB::table('category_slugs')->insert([
        ['category_id' => $category->id, 'locale' => 'en', 'slug' => $slugEn, 'active' => true],
        ['category_id' => $category->id, 'locale' => 'it', 'slug' => $slugIt, 'active' => true],
    ]);

    return ['category' => $category, 'categorySlugEn' => $slugEn, 'categorySlugIt' => $slugIt];
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

    $this->get('/en/articles/food/article-one')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Article/Show'));
});

it('returns 404 for an unpublished article', function () {
    $article = Article::create(['published' => false]);
    DB::table('article_slugs')->insert([
        ['article_id' => $article->id, 'locale' => 'en', 'slug' => 'draft-article', 'active' => true],
    ]);

    $this->get('/en/articles/food/draft-article')->assertNotFound();
});

it('includes correct hreflang alternates with localized module prefix and category slug', function () {
    ['article' => $article] = createArticleWithTranslations('article-one', 'articolo-uno');
    ['category' => $category] = createCategoryWithTranslations('food', 'cibo');
    relateArticleToCategory($article, $category);

    $this->get('/en/articles/food/article-one')
        ->assertInertia(fn ($page) => $page
            ->where('seo.alternates.en', url('/en/articles/food/article-one'))
            ->where('seo.alternates.it', url('/it/articoli/cibo/articolo-uno'))
            ->where('seo.alternates.x-default', url('/en/articles/food/article-one'))
        );
});

it('hreflang alternates omit locales where article translation is inactive', function () {
    $article = Article::create(['published' => true]);
    DB::table('article_translations')->insert([
        ['article_id' => $article->id, 'locale' => 'en', 'active' => true, 'title' => 'Article One'],
        ['article_id' => $article->id, 'locale' => 'it', 'active' => false, 'title' => null],
    ]);
    DB::table('article_slugs')->insert([
        ['article_id' => $article->id, 'locale' => 'en', 'slug' => 'article-one', 'active' => true],
    ]);

    ['category' => $category] = createCategoryWithTranslations();
    relateArticleToCategory($article, $category);

    $this->get('/en/articles/food/article-one')
        ->assertInertia(fn ($page) => $page
            ->has('seo.alternates.en')
            ->missing('seo.alternates.it')
        );
});

it('redirects to canonical slug when article slug has changed', function () {
    $article = Article::create(['published' => true]);
    DB::table('article_translations')->insert([
        ['article_id' => $article->id, 'locale' => 'en', 'active' => true, 'title' => 'Article One'],
    ]);
    DB::table('article_slugs')->insert([
        ['article_id' => $article->id, 'locale' => 'en', 'slug' => 'article-one', 'active' => true],
        ['article_id' => $article->id, 'locale' => 'en', 'slug' => 'old-article-slug', 'active' => false],
    ]);

    ['category' => $category] = createCategoryWithTranslations();
    relateArticleToCategory($article, $category);

    $this->get('/en/articles/food/old-article-slug')
        ->assertRedirect('/en/articles/food/article-one');
});

it('redirects to first category when category slug does not match', function () {
    ['article' => $article] = createArticleWithTranslations();
    ['category' => $category] = createCategoryWithTranslations('food', 'cibo');
    relateArticleToCategory($article, $category);

    $this->get('/en/articles/wrong-category/article-one')
        ->assertRedirect('/en/articles/food/article-one');
});
