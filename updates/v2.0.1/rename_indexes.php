<?php namespace Winter\sitemap\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class RenameIndexes extends Migration
{
    const TABLES = [
        'definitions',
    ];

    public function up()
    {
        foreach (self::TABLES as $table) {
            $from = 'rainlab_sitemap_' . $table;
            $to = 'winter_sitemap_' . $table;

            $this->updateIndexNames($from, $to, $to);
        }
    }

    public function down()
    {
        foreach (self::TABLES as $table) {
            $from = 'winter_sitemap_' . $table;
            $to = 'rainlab_sitemap_' . $table;

            $this->updateIndexNames($from, $to, $from);
        }
    }

    public function updateIndexNames($from, $to, $table)
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();

        foreach ($sm->listTableIndexes($table) as $index) {
            if ($index->isPrimary() === false) {
                $old = $index->getName();
                $new = str_replace($from, $to, $old);
                Schema::table($table, function ($table) use ($old, $new) {
                    $table->renameIndex($old, $new);
                });
            }
        }
    }
}
