<?php
/**
 * To allow compatibility with plugins that extend the original RainLab.Sitemap plugin, this will alias those classes to
 * use the new Winter.Sitemap classes.
 */
$aliases = [
    Winter\Sitemap\Plugin::class                   => 'RainLab\Sitemap\Plugin',
    Winter\Sitemap\Classes\DefinitionItem::class   => 'RainLab\Sitemap\Classes\DefinitionItem',
    Winter\Sitemap\Controllers\Definitions::class  => 'RainLab\Sitemap\Controllers\Definitions',
    Winter\Sitemap\FormWidgets\SitemapItems::class => 'RainLab\Sitemap\FormWidgets\SitemapItems',
    Winter\Sitemap\Models\Definition::class        => 'RainLab\Sitemap\Models\Definition',
];

foreach ($aliases as $original => $alias) {
    if (!class_exists($alias)) {
        class_alias($original, $alias);
    }
} 