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
        Schema::table($table, function ($blueprint) use ($from, $to) {
            foreach ($this->getIndexes($blueprint) as $index) {
                if (is_object($index) ? $index->isPrimary() : $index['primary']) {
                    continue;
                }

                $old = is_object($index) ? $index->getName() : $index['name'];
                $new = str_replace($from, $to, $old);

                $blueprint->renameIndex($old, $new);
            }
        });
    }

    public function getIndexes($blueprint)
    {
        $connection = Schema::getConnection();
        $table = $blueprint->getTable();

        if (method_exists($connection, 'getDoctrineSchemaManager')) {
            $sm = $connection->getDoctrineSchemaManager();
            return $sm->listTableDetails($table)->getIndexes();
        } else {
            return $connection->getSchemaBuilder()->getIndexes($table);
        }
    }
}
