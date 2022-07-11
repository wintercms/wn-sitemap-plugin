<?php namespace Winter\Sitemap\Models;

use Url;
use Model;
use Event;
use Request;
use DOMDocument;
use Cms\Classes\Theme;
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

    public function generateSitemap()
    {
        if (!$this->items) {
            return;
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
            if ($item->type == 'url') {
                $this->addItemToSet($item, Url::to($item->url));
            }
            /*
             * Registered sitemap type
             */
            else {

                $apiResult = Event::fire('pages.menuitem.resolveItem', [$item->type, $item, $currentUrl, $theme]);

                if (!is_array($apiResult)) {
                    continue;
                }

                foreach ($apiResult as $itemInfo) {
                    if (!is_array($itemInfo)) {
                        continue;
                    }

                    /*
                     * Single item
                     */
                    if (isset($itemInfo['url'])) {
                        $this->addItemToSet($item, $itemInfo['url'], array_get($itemInfo, 'mtime'), array_get($item, 'reference'));
                    }

                    /*
                     * Multiple items
                     */
                    if (isset($itemInfo['items'])) {

                        $parentItem = $item;

                        $itemIterator = function($items) use (&$itemIterator, $parentItem)
                        {
                            foreach ($items as $item) {
                                if (isset($item['url'])) {
                                    $this->addItemToSet($parentItem, $item['url'], array_get($item, 'mtime'), array_get($item, 'reference'));
                                }

                                if (isset($item['items'])) {
                                    $itemIterator($item['items']);
                                }
                            }
                        };

                        $itemIterator($itemInfo['items']);
                    }
                }

            }

        }

        $urlSet = $this->makeUrlSet();
        $xml = $this->makeXmlObject();
        $xml->appendChild($urlSet);

        return $xml->saveXML();
    }

    protected function makeXmlObject()
    {
        if ($this->xmlObject !== null) {
            return $this->xmlObject;
        }

        $xml = new DOMDocument;
        $xml->encoding = 'UTF-8';

        return $this->xmlObject = $xml;
    }

    protected function makeUrlSet()
    {
        if ($this->urlSet !== null) {
            return $this->urlSet;
        }

        $xml = $this->makeXmlObject();
        $urlSet = $xml->createElement('urlset');
        $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlSet->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $urlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

        return $this->urlSet = $urlSet;
    }

    protected function addItemToSet($item, $url, $mtime = null, $reference = null)
    {
        if ($mtime instanceof \DateTime) {
            $mtime = $mtime->getTimestamp();
        }

        $xml = $this->makeXmlObject();
        $urlSet = $this->makeUrlSet();
        $mtime = $mtime ? date('c', $mtime) : date('c');



        $urlElement = $this->makeUrlElement(
            $xml,
            $url,
            $mtime,
            $item,
            $reference
        );

        if ($urlElement) {
            $urlSet->appendChild($urlElement);
        }

        return $urlSet;
    }

    /**
     * Build the URL element for the provided information
     *
     * @param DomDocument $xml The XML object to write to
     * @param string $pageUrl The URL to generate an item for
     * @param string $lastModified The ISO 8601 date that the item was last modified
     * @param DefinitionItem $item The actual definition item object
     * @param string $itemReference Reference to the item. Can be an ID or something similar
     */
    protected function makeUrlElement($xml, $pageUrl, $lastModified, $item, $itemReference = null)
    {
        if ($this->urlCount >= self::MAX_URLS) {
            return false;
        }

        /**
         * @event winter.sitemap.beforeMakeUrlElement
         * Provides an opportunity to prevent an element from being produced
         *
         * Example usage (stops the generation process):
         *
         *     Event::listen('winter.sitemap.beforeMakeUrlElement', function ((Definition) $definition, (DomDocument) $xml, (string) &$pageUrl, (string) &$lastModified, (DefinitionItem) $item, (string) $itemReference) {
         *         if ($pageUrl === '/ignore-this-specific-page') {
         *             return false;
         *         }
         *     });
         *
         */
        if (Event::fire('winter.sitemap.beforeMakeUrlElement', [$this, $xml, &$pageUrl, &$lastModified, $item, $itemReference], true) === false) {
            return false;
        }

        $this->urlCount++;

        $urlElement = $xml->createElement('url');
        $urlElement->appendChild($xml->createElement('loc', $pageUrl));
        $urlElement->appendChild($xml->createElement('lastmod', $lastModified));
        $urlElement->appendChild($xml->createElement('changefreq', $item->changefreq));
        $urlElement->appendChild($xml->createElement('priority', $item->priority));

        /**
         * @event winter.sitemap.makeUrlElement
         * Provides an opportunity to interact with a sitemap element after it has been generated.
         *
         * Example usage:
         *
         *     Event::listen('winter.sitemap.makeUrlElement', function ((Definition) $definition, (DomDocument) $xml, (string) $pageUrl, (string) $lastModified, (DefinitionItem) $item, (string) $itemReference, (ElementNode) $urlElement) {
         *         $url->appendChild($xml->createElement('bestcmsever', 'OctoberCMS');
         *     });
         *
         */
        Event::fire('winter.sitemap.makeUrlElement', [$this, $xml, $pageUrl, $lastModified, $item, $itemReference, $urlElement]);

        return $urlElement;
    }
}
