<?php

use Winter\Storm\Support\ClassLoader;

/**
 * To allow compatibility with plugins that extend the original RainLab.Sitemap plugin, this will alias those classes to
 * use the new Winter.Sitemap classes.
 */
$aliases = [
    Winter\Sitemap\Plugin::class                   => RainLab\Sitemap\Plugin::class,
    Winter\Sitemap\Classes\DefinitionItem::class   => RainLab\Sitemap\Classes\DefinitionItem::class,
    Winter\Sitemap\Controllers\Definitions::class  => RainLab\Sitemap\Controllers\Definitions::class,
    Winter\Sitemap\FormWidgets\SitemapItems::class => RainLab\Sitemap\FormWidgets\SitemapItems::class,
    Winter\Sitemap\Models\Definition::class        => RainLab\Sitemap\Models\Definition::class,
];

app(ClassLoader::class)->addAliases($aliases);
