<?php

namespace Wilr\GoogleSitemaps\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Wilr\GoogleSitemaps\Services\StaticSitemapGenerator;

class GenerateStaticSitemapsTask extends BuildTask
{
    protected string $title = 'Generate static sitemaps';

    protected static string $description = 'Writes sitemap.xml and segmented sitemap XML files to the public webroot';

    protected static string $commandName = 'GenerateStaticSitemapsTask';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        StaticSitemapGenerator::create()->generate();
        $output->writeln('Static sitemaps generated.');

        return Command::SUCCESS;
    }
}
