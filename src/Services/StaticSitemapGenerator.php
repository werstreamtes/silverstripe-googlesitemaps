<?php

namespace Wilr\GoogleSitemaps\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Path;
use Wilr\GoogleSitemaps\GoogleSitemap;

class StaticSitemapGenerator
{
    use Configurable;
    use Injectable;

    private static bool $enabled = false;

    private static string $output_dir = 'sitemaps';

    private static string $index_filename = 'sitemap.xml';

    private static bool $clear_output_dir = true;

    public function generate(): void
    {
        $this->ensureBaseDirectories();

        if ($this->config()->get('clear_output_dir')) {
            $this->clearOutput();
        }

        $renderer = SitemapRenderer::create();
        $sitemaps = GoogleSitemap::inst()->getSitemaps();

        foreach ($sitemaps as $sitemap) {
            $class = $this->unsanitiseClassName((string) $sitemap->ClassName);
            $page = (int) $sitemap->Page;

            $xml = $renderer->renderSection($class, $page, true);
            if ($xml === '') {
                continue;
            }

            $this->writeFileAtomically(
                $this->getSectionPath($class, $page),
                $xml
            );
        }

        $this->writeFileAtomically(
            $this->getIndexPath(),
            $renderer->renderIndex(true)
        );
    }

    public function clearOutput(): void
    {
        $dir = $this->getOutputDirPath();

        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }
    }

    private function ensureBaseDirectories(): void
    {
        $dir = $this->getOutputDirPath();

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    private function getIndexPath(): string
    {
        return Path::join(
            Director::publicFolder(),
            (string) $this->config()->get('index_filename')
        );
    }

    private function getOutputDirPath(): string
    {
        return Path::join(
            Director::publicFolder(),
            (string) $this->config()->get('output_dir')
        );
    }

    private function getSectionPath(string $class, int $page): string
    {
        $classDir = Path::join(
            $this->getOutputDirPath(),
            SitemapUrlGenerator::create()->sanitiseClassName($class)
        );

        if (!is_dir($classDir)) {
            mkdir($classDir, 0775, true);
        }

        return Path::join($classDir, $page . '.xml');
    }

    private function writeFileAtomically(string $path, string $content): void
    {
        $tmpPath = $path . '.tmp';
        file_put_contents($tmpPath, $content);
        rename($tmpPath, $path);
    }

    private function unsanitiseClassName(string $class): string
    {
        return str_replace('-', '\\', $class);
    }
}
