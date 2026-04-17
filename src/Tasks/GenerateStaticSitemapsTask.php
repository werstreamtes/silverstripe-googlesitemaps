<?php

namespace Wilr\GoogleSitemaps\Tasks;

use SilverStripe\Dev\BuildTask;
use Wilr\GoogleSitemaps\Services\StaticSitemapGenerator;

class GenerateStaticSitemapsTask extends BuildTask
{
    protected $title = 'Generate static sitemaps';

    protected $description = 'Writes sitemap.xml and segmented sitemap XML files to the public webroot';

    public function run($request)
    {
        StaticSitemapGenerator::create()->generate();

        echo "Static sitemaps generated.\n";
    }
}
