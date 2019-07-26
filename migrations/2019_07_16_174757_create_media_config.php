<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use FastDog\Media\Models\MediaConfig;

class CreateMediaConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('media_config')) {
            Schema::create('media_config', function(Blueprint $table) {
                $table->increments('id');
                $table->string(MediaConfig::NAME)->comment('Название');
                $table->string(MediaConfig::ALIAS)->comment('Псевдоним');
                $table->json(MediaConfig::VALUE)->comment('Значение');
                $table->tinyInteger('priority')->default(100);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(MediaConfig::ALIAS, 'UK_media_config_alias');
            });
            DB::statement("ALTER TABLE `media_config` comment 'Параметры модуля медиа материалы'");

            MediaConfig::create([
                MediaConfig::NAME => trans('media::interface.Настройки'),
                MediaConfig::ALIAS => MediaConfig::CONFIG_MAIN,
                MediaConfig::VALUE => json_encode([
                    [
                        'name' => trans('media::settings.main.allow_upload'),
                        'alias' => 'allow_upload',
                        'description' => trans('media::settings.main.allow_upload_description'),
                        'type' => 'select',
                        'value' => 'Y'
                    ],
                    [
                        'name' => trans('media::settings.main.allow_move'),
                        'alias' => 'allow_move',
                        'description' => trans('media::settings.main.allow_move_description'),
                        'type' => 'select',
                        'value' => 'Y'
                    ],
                    [
                        'name' => trans('media::settings.main.allow_delete'),
                        'alias' => 'allow_move',
                        'description' => trans('media::settings.main.allow_delete_description'),
                        'type' => 'select',
                        'value' => 'Y'
                    ],
                    [
                        'name' => trans('media::settings.main.history'),
                        'alias' => 'allow_move',
                        'description' => trans('media::settings.main.history_description'),
                        'type' => 'select',
                        'value' => 'Y'
                    ],
                    [
                        'name' => trans('media::settings.main.theme'),
                        'alias' => 'allow_move',
                        'description' => trans('media::settings.main.theme_description'),
                        'type' => 'select',
                        'value' => 'default',
                        'values'=>[
                            [
                                'id'=>'default',
                                'name'=>trans('media::settings.main.themes.default')
                            ],
                            [
                                'id'=>'windows-10',
                                'name'=>trans('media::settings.main.themes.win10')
                            ]
                        ]
                    ],
                ])
            ]);


        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_config');
    }
}
