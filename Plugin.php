<?php

namespace Winter\Sitemap;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;

/**
 * Sitemap Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
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
     */
    public function registerPermissions(): array
    {
        return [
            'winter.sitemap.access_definitions' => [
                'tab'   => 'winter.sitemap::lang.plugin.name',
                'label' => 'winter.sitemap::lang.plugin.permissions.access_definitions',
                'roles' => [UserRole::CODE_DEVELOPER],
            ],
        ];
    }

    /**
     * Registers settings for this plugin.
     */
    public function registerSettings(): array
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
}
