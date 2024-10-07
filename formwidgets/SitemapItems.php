<?php

namespace Winter\Sitemap\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Lang;
use Winter\Sitemap\Classes\DefinitionItem as SitemapItem;

/**
 * SitemapItems FormWidget
 */
class SitemapItems extends FormWidgetBase
{
    protected $typeListCache = false;
    protected $typeInfoCache = [];

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'sitemapitems';

    public $referenceRequiredMessage = 'winter.sitemap::lang.item.reference_required';

    public $urlRequiredMessage = 'winter.sitemap::lang.item.url_required';

    public $cmsPageRequiredMessage = 'winter.sitemap::lang.item.cms_page_required';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('sitemapitems');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $sitemapItem = new SitemapItem;

        $this->vars['itemProperties'] = json_encode($sitemapItem->fillable);
        $this->vars['items'] = $this->model->items;

        $emptyItem = new SitemapItem;
        $emptyItem->type = 'url';
        $emptyItem->url = '/';
        $emptyItem->changefreq = 'always';
        $emptyItem->priority = '0.5';

        $this->vars['emptyItem'] = $emptyItem;

        $widgetConfig = $this->makeConfig('$/winter/sitemap/classes/definitionitem/fields.yaml');
        $widgetConfig->model = $sitemapItem;
        $widgetConfig->alias = $this->alias.'SitemapItem';

        $this->vars['itemFormWidget'] = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadAssets()
    {
        $this->addJs('js/sitemap-items-editor.js', 'core');
    }

    //
    // Methods for the internal use
    //

    /**
     * Returns the item reference description.
     */
    protected function getReferenceDescription(SitemapItem $item): string
    {
        if ($this->typeListCache === false) {
            $this->typeListCache = $item->getTypeOptions();
        }

        if (!isset($this->typeInfoCache[$item->type])) {
            $this->typeInfoCache[$item->type] = SitemapItem::getTypeInfo($item->type);
        }

        if (isset($this->typeListCache[$item->type])) {
            $result = Lang::get($this->typeListCache[$item->type]);

            if ($item->type !== 'url') {
                if (isset($this->typeInfoCache[$item->type]['references'])) {
                    $result .= ': '.$this->findReferenceName($item->reference, $this->typeInfoCache[$item->type]['references']);
                }
            }
            else {
                $result .= ': '.$item->url;
            }
        }
        else {
            $result = Lang::get('winter.sitemap::lang.item.unknown_type');
        }

        return $result;
    }

    protected function findReferenceName($search, $typeOptionList)
    {
        $iterator = function ($optionList, $path) use ($search, &$iterator)
        {
            foreach ($optionList as $reference => $info) {
                if ($reference == $search) {
                    $result = $this->getSitemapItemTitle($info);

                    return strlen($path) ? $path.' / '.$result : $result;
                }

                if (is_array($info) && isset($info['items'])) {
                    $result = $iterator($info['items'], $path.' / '.$this->getSitemapItemTitle($info));

                    if (strlen($result)) {
                        return strlen($path) ? $path.' / '.$result : $result;
                    }
                }
            }
        };

        $result = $iterator($typeOptionList, null);
        if (!strlen($result)) {
            $result = Lang::get('winter.sitemap::lang.item.unnamed');
        }

        $result = preg_replace('|^\s+\/|', '', $result);

        return $result;
    }

    protected function getSitemapItemTitle($itemInfo)
    {
        if (is_array($itemInfo)) {
            if (!array_key_exists('title', $itemInfo) || !strlen($itemInfo['title'])) {
                return Lang::get('winter.sitemap::lang.item.unnamed');
            }

            return $itemInfo['title'];
        }

        return strlen($itemInfo) ? $itemInfo : Lang::get('winter.sitemap::lang.item.unnamed');
    }
}
