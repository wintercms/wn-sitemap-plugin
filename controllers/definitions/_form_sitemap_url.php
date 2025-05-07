        <div class="layout-row min-size">
            <div class="scoreboard padded-container">
                <div data-control="toolbar">
                    <div class="scoreboard-item title-value">
                        <h4><?= e(trans('winter.sitemap::lang.plugin.name')) ?></h4>
                        <p><?= e($themeName) ?></p>
                        <p class="description">
                            <?= e(trans('winter.sitemap::lang.item.location')) ?> <a href="<?= $sitemapUrl ?>" target="_blank"><?= $sitemapUrl ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
