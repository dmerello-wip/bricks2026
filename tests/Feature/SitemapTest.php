<?php

use App\Models\Homepage;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('returns xml content type', function () {
    $this->get('sitemap.xml')
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'application/xml');
});

it('includes a published homepage', function () {
    $homepage = Homepage::create(['published' => true]);
    $homepage->translations()->create(['locale' => 'it', 'active' => true]);

    $response = $this->get('sitemap.xml');

    $response->assertStatus(200);
    expect($response->getContent())->toContain('<loc>');
});

it('excludes homepage when not published', function () {
    Homepage::create(['published' => false]);

    $xml = $this->get('sitemap.xml')->getContent();

    expect($xml)->not->toContain(url('/en'));
});

it('excludes homepage when no_index is true', function () {
    $homepage = Homepage::create(['published' => true]);
    $homepage->translations()->create(['locale' => 'it', 'active' => true]);

    $homepage->seoData()->create(['no_index' => true, 'translations' => []]);

    $xml = $this->get('sitemap.xml')->getContent();

    expect($xml)->not->toContain(url('/en'));
});

it('includes published pages with active slugs', function () {
    $page = Page::create(['published' => true]);
    $page->translations()->create(['locale' => 'it', 'active' => true, 'title' => 'About Us']);
    $page->slugs()->forceCreate(['locale' => 'it', 'active' => true, 'slug' => 'about-us']);

    $xml = $this->get('sitemap.xml')->getContent();

    expect($xml)->toContain('about-us');
});

it('excludes unpublished pages', function () {
    $page = Page::create(['published' => false]);
    $page->translations()->create(['locale' => 'it', 'active' => true, 'title' => 'Hidden']);
    $page->slugs()->forceCreate(['locale' => 'it', 'active' => true, 'slug' => 'hidden-page']);

    $xml = $this->get('sitemap.xml')->getContent();

    expect($xml)->not->toContain('hidden-page');
});

it('excludes pages with no_index set to true', function () {
    $page = Page::create(['published' => true]);
    $page->translations()->create(['locale' => 'it', 'active' => true, 'title' => 'No Index Page']);
    $page->slugs()->forceCreate(['locale' => 'it', 'active' => true, 'slug' => 'no-index-page']);

    $page->seoData()->create(['no_index' => true, 'translations' => []]);

    $xml = $this->get('sitemap.xml')->getContent();

    expect($xml)->not->toContain('no-index-page');
});

it('excludes pages with no active slug', function () {
    $page = Page::create(['published' => true]);
    $page->translations()->create(['locale' => 'it', 'active' => true, 'title' => 'No Slug Page']);

    $xml = $this->get('sitemap.xml')->getContent();

    expect($xml)->not->toContain('no-slug-page');
});

it('caches the sitemap response', function () {
    expect(Cache::has('sitemap.xml'))->toBeFalse();

    $this->get('sitemap.xml')->assertStatus(200);

    expect(Cache::has('sitemap.xml'))->toBeTrue();
});
