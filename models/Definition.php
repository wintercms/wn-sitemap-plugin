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
                $this->addItemToSet($item);
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
                        $this->addItemToSet($item);
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
                                    $this->addItemToSet($parentItem, $item);
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

    protected function addItemToSet($itemDefinition, $itemInfo=null)
    {
        $xml = $this->makeXmlObject();
        $urlSet = $this->makeUrlSet();

        $urlElement = $this->makeUrlElement(
            $xml,
            $itemDefinition,
            $itemInfo,
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
     * @param DefinitionItem $itemDefinition The actual definition item object
     * @param array $itemInfo The menuItem from the resolveMenuItem event
     */
    protected function makeUrlElement($xml, $itemDefinition, $itemInfo=null)
    {
        if ($this->urlCount >= self::MAX_URLS) {
            return false;
        }
        
        $pageUrl = $itemInfo ? $itemInfo['url'] : Url::to($itemDefinition->url);
        $lastModified = $itemInfo ? array_get($itemInfo, 'mtime') : null;
        $itemReference = $itemDefinition->reference ?: $itemDefinition->cmsPage;

        if ($lastModified instanceof \DateTime) {
            $lastModified = $lastModified->getTimestamp();
        }
        $lastModified = $lastModified ? date('c', $lastModified) : date('c');

        /**
         * @event winter.sitemap.beforeMakeUrlElement
         * Provides an opportunity to prevent an element from being produced
         *
         * Example usage (stops the generation process):
         *
         *     Event::listen('winter.sitemap.beforeMakeUrlElement', function ((Definition) $definition, (DomDocument) $xml, (string) &$pageUrl, (string) &$lastModified, (DefinitionItem) $itemDefinition, (array) $itemInfo, (string) $itemReference) {
         *         if ($pageUrl === '/ignore-this-specific-page') {
         *             return false;
         *         }
         *     });
         *
         */
        if (Event::fire('winter.sitemap.beforeMakeUrlElement', [$this, $xml, &$pageUrl, &$lastModified, $itemDefinition, $itemInfo, $itemReference], true) === false) {
            return false;
        }

        $this->urlCount++;

        $urlElement = $xml->createElement('url');
        $urlElement->appendChild($xml->createElement('loc', $pageUrl));
        $urlElement->appendChild($xml->createElement('lastmod', $lastModified));
        $urlElement->appendChild($xml->createElement('changefreq', $itemDefinition->changefreq));
        $urlElement->appendChild($xml->createElement('priority', $itemDefinition->priority));

        /**
         * @event winter.sitemap.makeUrlElement
         * Provides an opportunity to interact with a sitemap element after it has been generated.
         *
         * Example usage:
         *
         *     Event::listen('winter.sitemap.makeUrlElement', function ((Definition) $definition, (DomDocument) $xml, (string) $pageUrl, (string) $lastModified, (DefinitionItem) $itemDefinition, array $itemInfo, (string) $itemReference, (ElementNode) $urlElement) {
         *         $urlElement->appendChild($xml->createElement('bestcmsever', 'WinterCMS');
         *     });
         *
         */
        Event::fire('winter.sitemap.makeUrlElement', [$this, $xml, $pageUrl, $lastModified, $itemDefinition, $itemInfo, $itemReference, $urlElement]);

        return $urlElement;
    }
}
