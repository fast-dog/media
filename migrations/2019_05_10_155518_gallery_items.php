<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GalleryItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('media_gallery_item')) {
            Schema::create('media_gallery_item', function (Blueprint $table) {
                $table->increments('id');
                $table->string('path')->comment('Путь к файлу')->nullable();
                $table->integer('parent_id')->nullable()->comment('Идентификатор родителя');
                $table->unsignedSmallInteger('parent_type')->comment('Тип');
                $table->smallInteger('sort')->default(100)->comment('Сортировка');
                $table->char('hash', 32)->comment('Хэш пути к файлу');
                $table->string('name', 200)->comment('Название');
                $table->json('data')->comment('Дополнительные параметры');
                $table->char('site_id', 3)->default('001')->comment('Код сайта');
                $table->tinyInteger('state')->comment('Состояние')->default(1);

                $table->index(['parent_id', 'parent_type', 'site_id'], 'IDX_media_gallery_item');
                $table->index('hash', 'IDX_media_gallery_item_hash');
                $table->index('parent_id', 'IDX_media_gallery_item_parent_id');
                $table->index('parent_type', 'IDX_media_gallery_item_parent_type');
                $table->index('site_id', 'IDX_media_gallery_item_site_id');
                $table->index('state', 'IDX_media_gallery_item_state');

                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement("ALTER TABLE `media_gallery_item` comment 'Медиа материалы'");

            DB::unprepared("DROP TRIGGER IF EXISTS media_gallery_item_before_delete");

            $user = config('database.connections.mysql.username');
            $host = config('database.connections.mysql.host');
            DB::unprepared("
CREATE  DEFINER = '{$user}'@'{$host}'
TRIGGER media_gallery_item_before_delete
	AFTER DELETE
	ON media_gallery_item
	FOR EACH ROW
BEGIN
  IF (OLD.parent_type = 10) THEN
    INSERT INTO catalog_items_history (item_id, event_type, created_at) VALUES  (OLD.parent_id,'delete_certificate',NOW() );
  END IF;
END");


            DB::unprepared("DROP TRIGGER IF EXISTS media_gallery_item_before_insert");
            DB::unprepared("
CREATE  DEFINER = '{$user}'@'{$host}'
TRIGGER media_gallery_item_before_insert
	AFTER INSERT
	ON media_gallery_item
	FOR EACH ROW
BEGIN
  IF(NEW.parent_type = 10) THEN
    INSERT INTO catalog_items_history (item_id, event_type, created_at) VALUES  (NEW.parent_id,'add_certificate',NOW());
  END IF;
    IF(NEW.parent_type = 8) THEN
    INSERT INTO catalog_items_history (item_id, event_type, created_at) VALUES  (NEW.parent_id,'add_image',NOW());
  END IF;
END");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_gallery_item');
    }
}
