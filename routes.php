<?php

use Cms\Classes\Theme;
use Cms\Classes\Controller;
use Winter\Sitemap\Models\Definition;
use Illuminate\Database\Eloquent\ModelNotFoundException as ModelNotFound;

Route::get('sitemap.xml', function ()
{
    $themeActive = Theme::getActiveTheme()->getDirName();

    try {
        $definition = Definition::where('theme', $themeActive)->firstOrFail();
    } catch (ModelNotFound $e) {
        Log::info(trans('winter.sitemap::lang.definition.not_found'));
        return App::make(Controller::class)->setStatusCode(404)->run('/404');
    }

    return Response::make($definition->generateSitemap())
        ->header('Content-Type', 'application/xml');
});

Route::get('sitemap.xsl', function () {
    return Response::make(file_get_contents(plugins_path('winter/sitemap/assets/sitemap.xsl')))
        ->header('Content-Type', 'text/xsl');
});
