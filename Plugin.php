<?php namespace Winter\Sitemap;

use Backend;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;

/**
 * Sitemap Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'winter.sitemap::lang.plugin.name',
            'description' => 'winter.sitemap::lang.plugin.description',
            'author'      => 'Winter CMS',
            'icon'        => 'icon-sitemap',
            'homepage'    => 'https://github.com/wintercms/wn-sitemap-plugin',
            'replaces'    => ['RainLab.Sitemap' => '<= 1.0.9'],
        ];
    }

    /**
     * Registers administrator permissions for this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'winter.sitemap.access_definitions' => [
                'tab'   => 'winter.sitemap::lang.plugin.name',
                'label' => 'winter.sitemap::lang.plugin.permissions.access_definitions',
            ],
        ];
    }

    /**
     * Registers settings for this plugin.
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'definitions' => [
                'label'       => 'winter.sitemap::lang.plugin.name',
                'description' => 'winter.sitemap::lang.plugin.description',
                'icon'        => 'icon-sitemap',
                'url'         => Backend::url('winter/sitemap/definitions'),
                'category'    => SettingsManager::CATEGORY_CMS,
                'permissions' => ['winter.sitemap.access_definitions'],
            ]
        ];
    }

    public function registerClassAliases()
    {
        /**
         * To allow compatibility with plugins that extend the original RainLab.Sitemap plugin,
         * this will alias those classes to use the new Winter.Sitemap classes.
         */
        return [
            \Winter\Sitemap\Plugin::class                   => \RainLab\Sitemap\Plugin::class,
            \Winter\Sitemap\Classes\DefinitionItem::class   => \RainLab\Sitemap\Classes\DefinitionItem::class,
            \Winter\Sitemap\Controllers\Definitions::class  => \RainLab\Sitemap\Controllers\Definitions::class,
            \Winter\Sitemap\FormWidgets\SitemapItems::class => \RainLab\Sitemap\FormWidgets\SitemapItems::class,
            \Winter\Sitemap\Models\Definition::class        => \RainLab\Sitemap\Models\Definition::class,
        ];
    }
}
