<?php

namespace Wilr\GoogleSitemaps\Control;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse;
use Wilr\GoogleSitemaps\GoogleSitemap;
use SilverStripe\Model\ArrayData;
use Wilr\GoogleSitemaps\Services\SitemapRenderer;

/**
 * Controller for displaying the sitemap.xml. The module displays an index
 * sitemap at the sitemap.xml level, then outputs the individual objects
 * at a second level.
 *
 * <code>
 * http://site.com/sitemap.xml/
 * http://site.com/sitemap.xml/sitemap/$ClassName-$Page.xml
 * </code>
 *
 * @package googlesitemaps
 */
class GoogleSitemapController extends Controller
{

    /**
     * @var array
     */
    private static $allowed_actions = [
        'index',
        'sitemap',
        'styleSheetIndex',
        'styleSheet'
    ];


    /**
     * Default controller action for the sitemap.xml file. Renders a index
     * file containing a list of links to sub sitemaps containing the data.
     *
     * @return mixed
     */
    public function index($url)
    {
        if (!GoogleSitemap::enabled()) {
            return new HTTPResponse('Page not found', 404);
        }

        $renderer = SitemapRenderer::create();

        return $renderer->asXmlResponse(
            $renderer->renderIndex(false)
        );
    }
    /**
     * Specific controller action for displaying a particular list of links
     * for a class
     *
     * @return mixed
     */
    public function sitemap()
    {
        $class = $this->unsanitiseClassName($this->request->param('ID'));
        $page = intval($this->request->param('OtherID'));

        if ($page && !is_numeric($page)) {
            return new HTTPResponse('Page not found', 404);
        }

        if (
            !GoogleSitemap::enabled()
            || !$class
            || $page <= 0
            || !($class == SiteTree::class || $class == 'GoogleSitemapRoute' || GoogleSitemap::is_registered($class))
        ) {
            return new HTTPResponse('Page not found', 404);
        }

        $renderer = SitemapRenderer::create();

        return $renderer->asXmlResponse(
            $renderer->renderSection($class, $page, false)
        );
    }

    /**
     * Unsanitise a namespaced class' name from a URL param
     * @return string
     */
    protected function unsanitiseClassName($class)
    {
        return str_replace('-', '\\', (string) $class);
    }

    /**
     * Render the stylesheet for the sitemap index
     *
     * @return DBHTMLText
     */
    public function styleSheetIndex()
    {
        $html = $this->renderWith('xml-sitemapindex');
        $this->getResponse()->addHeader('Content-Type', 'text/xsl; charset="utf-8"');

        return $html;
    }

    /**
     * Render the stylesheet for the sitemap
     *
     * @return DBHTMLText
     */
    public function styleSheet()
    {
        $html = $this->renderWith('xml-sitemap');
        $this->getResponse()->addHeader('Content-Type', 'text/xsl; charset="utf-8"');

        return $html;
    }


    public function AbsoluteLink($action = null)
    {
        return rtrim(Controller::join_links(Director::absoluteBaseURL(), 'sitemap.xml', $action), '/');
    }
}
