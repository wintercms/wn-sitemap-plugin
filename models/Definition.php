<?php

namespace Winter\Sitemap\Models;

use Cms\Classes\Theme;
use DOMDocument;
use DOMElement;
use Event;
use Model;
use Request;
use Url;
use Winter\Sitemap\Classes\DefinitionItem;

/**
 * Definition Model
 */
class Definition extends Model
{
    /**
     * Maximum URLs allowed (Protocol limit is 50k)
     */
    const MAX_URLS = 50000;

    /**
     * Maximum generated URLs per type
     */
    const MAX_GENERATED = 10000;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'winter_sitemap_definitions';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var integer A tally of URLs added to the sitemap
     */
    protected $urlCount = 0;

    /**
     * @var array List of attribute names which are json encoded and decoded from the database.
     */
    protected $jsonable = ['data'];

    /**
     * @var array The sitemap items.
     * Items are objects of the \Winter\Sitemap\Classes\DefinitionItem class.
     */
    public $items;

    /**
     * @var DOMDocument element
     */
    protected $urlSet;

    /**
     * @var DOMDocument
     */
    protected $xmlObject;

    public function beforeSave()
    {
        $this->data = (array) $this->items;
    }

    public function afterFetch()
    {
        $this->items = DefinitionItem::initFromArray($this->data);
    }

    /**
     * Generate the sitemap's XML from the definition data
     */
    public function generateSitemap(): string
    {
        if (!$this->items) {
            return '';
        }

        $currentUrl = Request::path();
        $theme = Theme::load($this->theme);

        /*
         * Cycle each page and add its URL
         */
        foreach ($this->items as $item) {

            /*
             * Explicit URL
             */
            if ($item->type === 'url') {
                $this->addItemToSet($item);
            }
            /*
             * Registered sitemap type
             */
            else {
                /**
                 * @NOTE:
                 * - This passes (string, DefinititionItem, string, Theme)
                 * - Winter.Pages passes (string, MenuItem, string, Theme)
                 */
                $menuItemInfo = Event::fire('pages.menuitem.resolveItem', [$item->type, $item, $currentUrl, $theme], true);
                if (!is_array($menuItemInfo)) {
                    continue;
                }

                /*
                 * Single item
                 */
                if (isset($menuItemInfo['url'])) {
                    $this->addItemToSet($item, $menuItemInfo);
                }

                /*
                 * Multiple items
                 */
                if (isset($menuItemInfo['items'])) {
                    $menuItemIterator = function ($menuItems) use (&$menuItemIterator, $item) {
                        foreach ($menuItems as $menuItem) {
                            if (isset($menuItem['url'])) {
                                $this->addItemToSet($item, $menuItem);
                            }

                            if (isset($menuItem['items'])) {
                                $menuItemIterator($menuItem['items']);
                            }
                        }
                    };

                    $menuItemIterator($menuItemInfo['items']);
                }
            }
        }

        $urlSet = $this->getUrlSet();
        $xml = $this->getXmlObject();
        $xml->appendChild($urlSet);

        return $xml->saveXML();
    }

    /**
     * Gets the DomDocument object, creating it if it doesn't exist
     */
    protected function getXmlObject(): DOMDocument
    {
        if ($this->xmlObject !== null) {
            return $this->xmlObject;
        }

        $xml = new DOMDocument;
        $xml->encoding = 'UTF-8';
        $xss = $xml->createProcessingInstruction('xml-stylesheet',
            'type="text/xsl" href="' . Url::buildUrl(Url::current(), ['path' => 'sitemap.xsl']) . '"'
        );
        $xml->appendChild($xss);

        return $this->xmlObject = $xml;
    }

    /**
     * Gets the urlset XML element, creating it if it doesn't exist
     */
    protected function getUrlSet(): DOMElement
    {
        if ($this->urlSet !== null) {
            return $this->urlSet;
        }

        $xml = $this->getXmlObject();
        $urlSet = $xml->createElement('urlset');
        $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlSet->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $urlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

        return $this->urlSet = $urlSet;
    }

    /**
     * Adds the provided item to the sitemap's urlset DOMElement
     */
    protected function addItemToSet(DefinitionItem $item, ?array $itemInfo = null): void
    {
        $xml = $this->getXmlObject();
        $urlSet = $this->getUrlSet();

        if ($this->urlCount >= self::MAX_URLS) {
            return;
        }

        if (!isset($itemInfo['url'])) {
            $itemInfo['url'] = Url::to($item->url);
        }

        $lastModified = $itemInfo['mtime'] ?? null;
        if ($lastModified instanceof \DateTime) {
            $lastModified = $lastModified->getTimestamp();
        }
        $lastModified = $lastModified ? date('c', $lastModified) : date('c');
        $itemInfo['lastModified'] = $lastModified;

        /**
         * @event winter.sitemap.beforeAddItem
         * Provides an opportunity to prevent an element from being produced
         *
         * Example usage (stops the generation process):
         *
         *     Event::listen('winter.sitemap.beforeAddItem', function (DefinitionItem $item, array $itemInfo, Definition $definition, DOMDocument $xml, DOMElement $urlSet) {
         *         if ($itemInfo['url'] === '/ignore-this-specific-page') {
         *             return false;
         *         }
         *     });
         *
         */
        if (Event::fire('winter.sitemap.beforeAddItem', [$item, $itemInfo, $this, $xml, $urlSet], true) === false) {
            return;
        }

        // Ensure that only items with valid absolute URLs are added to the generated sitemap.
        if (empty($itemInfo['url'])) {
            return;
        } else {
            $itemInfo['url'] = Url::to($itemInfo['url']);
        }

        $this->urlCount++;

        $urlElement = $xml->createElement('url');

        $urlElement->appendChild($xml->createElement('loc', $itemInfo['url']));
        $urlElement->appendChild($xml->createElement('lastmod', $itemInfo['lastModified']));
        $urlElement->appendChild($xml->createElement('changefreq', $item->changefreq));
        $urlElement->appendChild($xml->createElement('priority', $item->priority));

        $urlSet->appendChild($urlElement);

        /**
         * @event winter.sitemap.addItem
         * Provides an opportunity to interact with a sitemap element after it has been generated.
         *
         * Example usage:
         *
         *     Event::listen('winter.sitemap.addItem', function (DefinitionItem $item, array $itemInfo, Definition $definition, DOMDocument $xml, DOMElement $urlSet, DOMElement $itemElement) {
         *         $urlElement->appendChild($xml->createElement('bestcmsever', 'WinterCMS');
         *     });
         *
         */
        Event::fire('winter.sitemap.addItem', [$item, $itemInfo, $this, $xml, $urlSet, $urlElement]);

        return;
    }
}
