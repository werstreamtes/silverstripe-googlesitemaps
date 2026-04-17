<?php

namespace Wilr\GoogleSitemaps\Services;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

class SitemapUrlGenerator
{
    use Configurable;
    use Injectable;

    private static bool $static_mode = false;

    private static string $static_index_path = 'sitemap.xml';

    private static string $static_section_base = 'sitemaps';

    public function getIndexUrl(?bool $static = null): string
    {
        $static = $static ?? (bool) $this->config()->get('static_mode');

        if ($static) {
            return rtrim(Controller::join_links(
                Director::absoluteBaseURL(),
                (string) $this->config()->get('static_index_path')
            ), '/');
        }

        return rtrim(Controller::join_links(
            Director::absoluteBaseURL(),
            'sitemap.xml'
        ), '/');
    }

    public function getSectionUrl(string $class, int $page, ?bool $static = null): string
    {
        $static = $static ?? (bool) $this->config()->get('static_mode');
        $class = $this->sanitiseClassName($class);

        if ($static) {
            return rtrim(Controller::join_links(
                Director::absoluteBaseURL(),
                (string) $this->config()->get('static_section_base'),
                $class,
                $page . '.xml'
            ), '/');
        }

        return rtrim(Controller::join_links(
            Director::absoluteBaseURL(),
            'sitemap.xml',
            'sitemap',
            $class,
            $page . '.xml'
        ), '/');
    }

    public function sanitiseClassName(string $class): string
    {
        return str_replace('\\', '-', $class);
    }
}
