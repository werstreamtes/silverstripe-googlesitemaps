<?php

namespace Wilr\GoogleSitemaps\Services;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Model\ArrayData;
use Wilr\GoogleSitemaps\GoogleSitemap;

class SitemapRenderer
{
    use Injectable;

    public function renderIndex(bool $static = false): string
    {
        $urlGenerator = SitemapUrlGenerator::create();
        $sitemaps = GoogleSitemap::inst()->getSitemaps();

        $items = [];
        foreach ($sitemaps as $sitemap) {
            $class = $this->unsanitiseClassName((string) $sitemap->ClassName);

            $items[] = ArrayData::create([
                'Loc' => $urlGenerator->getSectionUrl($class, (int) $sitemap->Page, $static),
                'LastModified' => $sitemap->LastModified,
            ]);
        }

        return ArrayData::create([
            'Sitemaps' => $items,
            'StyleSheetLink' => $urlGenerator->getIndexUrl(false) . '/styleSheetIndex',
        ])->renderWith('Wilr\\GoogleSitemaps\\Control\\GoogleSitemapController')->forTemplate();
    }

    public function renderSection(string $class, int $page, bool $static = false): string
    {
        if (
            $class !== SiteTree::class
            && $class !== 'GoogleSitemapRoute'
            && !GoogleSitemap::is_registered($class)
        ) {
            return '';
        }

        $items = GoogleSitemap::inst()->getItems($class, $page);
        $urlGenerator = SitemapUrlGenerator::create();

        return ArrayData::create([
            'Items' => $items,
            'StyleSheetLink' => $urlGenerator->getIndexUrl(false) . '/styleSheet',
        ])->renderWith('Wilr\\GoogleSitemaps\\Control\\GoogleSitemapController_sitemap')->forTemplate();
    }

    public function asXmlResponse(string $xml): HTTPResponse
    {
        $response = HTTPResponse::create($xml);
        $response->addHeader('Content-Type', 'application/xml; charset="utf-8"');
        $response->addHeader('X-Robots-Tag', 'noindex');

        return $response;
    }

    private function unsanitiseClassName(string $class): string
    {
        return str_replace('-', '\\', $class);
    }
}
