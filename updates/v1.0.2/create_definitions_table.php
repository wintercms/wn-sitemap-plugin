<?php namespace Winter\Sitemap\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateDefinitionsTable extends Migration
{
    public function up()
    {
        Schema::create('winter_sitemap_definitions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('theme')->nullable()->index();
            $table->mediumtext('data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('winter_sitemap_definitions');
    }
}
