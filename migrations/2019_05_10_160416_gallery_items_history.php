<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GalleryItemsHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('media_gallery_item_history')) {
            Schema::create('media_gallery_item_history', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable()->comment('Идентификатор родителя');
                $table->unsignedSmallInteger('parent_type')->comment('Тип события');
                $table->json('data')->comment('Дополнительные параметры');
                $table->index('parent_id', 'IDX_media_gallery_item_parent_id');
                $table->timestamps();
            });
            DB::statement("ALTER TABLE `media_gallery_item_history` comment 'История изменения файлов'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_gallery_item_history');
    }
}
