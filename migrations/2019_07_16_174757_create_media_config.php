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
        Schema::create('media_config', function (Blueprint $table) {
            $table->increments('id');
            $table->string(MediaConfig::NAME)->comment('Название');
            $table->string(MediaConfig::ALIAS)->comment('Псевдоним');
            $table->json(MediaConfig::VALUE)->comment('Значение');
            $table->tinyInteger('priority');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(MediaConfig::ALIAS, 'UK_media_config_alias');
        });
        DB::statement("ALTER TABLE `media_config` comment 'Параметры модуля медиа материалы'");
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
