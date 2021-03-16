<?php namespace Winter\Sitemap\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class RenameTables extends Migration
{
    public function up()
    {
        $from = 'rainlab_sitemap_definitions';
        $to = 'winter_sitemap_definitions';

        if (Schema::hasTable($from) && !Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }

    public function down()
    {
        $from = 'winter_sitemap_definitions';
        $to = 'rainlab_sitemap_definitions';
        
        if (Schema::hasTable($from) && !Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }
}
