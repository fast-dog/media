<?php
namespace FastDog\Media\Models;

use FastDog\Core\Models\BaseModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Параметры модуля
 * @package FastDog\Catalog\Entity
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class MediaConfig extends BaseModel
{
    /**
     * Тип: основные параметры
     * @const string
     */
    const CONFIG_MAIN = 'main';
    /**
     * Тип: рабочий стол
     * @const string
     */
    const CONFIG_DESKTOP = 'desktop';
    /**
     * Тип: публичный раздел
     * @const string
     */
    const CONFIG_PUBLIC = 'public';
    /**
     * Значение
     * @const string
     */
    const VALUE = 'value';
    /**
     * Название таблицы
     *
     * @var string $table
     */
    public $table = 'media_config';
    /**
     * Загруженные конфигурации
     *
     * @var array $section
     */
    protected static $section = [];

    /**
     * Все параметры
     *
     * @return array
     */
    public static function getAllConfig()
    {
        $result = [];
        $items = self::orderBy('priority')->get();
        foreach ($items as $item) {

            $data = json_decode($item->{'value'});

            $item->data = $data;
            self::$section[$item->alias] = $item;
            /**
             * Проверка состояния блоков на главной странице
             */
            if ($item->alias == self::CONFIG_DESKTOP) {
                foreach ($data as $key => &$value) {
                    $_item = Desktop::where(Desktop::NAME, $value->name)->withTrashed()->first();
                    if ($_item) {
                        $value->value = ($_item->deleted_at === null) ? 'Y' : 'N';
                    }
                }
            }

            $result[$item->alias] = [
                'open' => ($item->alias == \Request::input('open_section', self::CONFIG_MAIN)),
                'name' => $item->{self::NAME},
                'config' => $data,
            ];
        }

        return $result;
    }

    /**
     * Детальная информация о объекте
     *
     * @return array
     */
    public function getData(): array
    {
        if (is_string($this->{self::VALUE})) {
            $this->{self::VALUE} = json_decode($this->{self::VALUE});
        }
        $result = [
            'id' => $this->id,
            self::NAME => $this->{self::NAME},
            self::ALIAS => $this->{self::ALIAS},
            self::VALUE => $this->{self::VALUE},
        ];

        return $result;
    }

    /**
     * Проверка доступа по ключу
     *
     * @param $access_name
     * @return bool
     */
    public function can($access_name)
    {
        $data = $this->getData();

        foreach ($data[self::VALUE] as $item) {
            if ($item->{'alias'} === $access_name) {
                switch ($item->{'type'}) {
                    case 'select':
                        return ($item->{'value'} === 'Y');
                }
            }
        }

        return false;
    }


    /**
     * Создание таблицы базы данных
     *
     * Будет создана следующая таблица:
     *
     * <pre>
     * CREATE TABLE media_config (
     *          id int(11) NOT NULL AUTO_INCREMENT,
     *          name varchar(50) DEFAULT NULL,
     *          alias varchar(255) DEFAULT NULL,
     *          value longblob DEFAULT NULL,
     *          created_at datetime DEFAULT NULL,
     *          updated_at datetime DEFAULT NULL,
     *          deleted_at datetime DEFAULT NULL,
     *          priority tinyint(4) DEFAULT 0,
     *          PRIMARY KEY (id),
     *          UNIQUE INDEX UK_catalog_media_alias (alias)
     * )
     * COMMENT = 'Параметры модуля каталог'
     * </pre>
     *
     * @return void
     */
    public static function createDbSchema()
    {
        if (!Schema::hasTable('media_config')) {
            Schema::create('media_config', function (Blueprint $table) {
                $table->increments('id');
                $table->string(self::NAME)->comment('Название');
                $table->string(self::ALIAS)->comment('Псевдоним');
                $table->json(self::VALUE)->comment('Значение');
                $table->tinyInteger('priority');
                $table->timestamps();
                $table->softDeletes();
                $table->unique(self::ALIAS, 'UK_media_config_alias');
            });
            DB::statement("ALTER TABLE `catalog_config` comment 'Параметры модуля каталог'");
        }
    }

    /**
     * Получение значения из парамтеров конфигурации
     *
     * @param $alias
     * @param $value
     * @param null $default
     * @return null
     */
    public static function getValue($alias, $value, $default = null)
    {
        if (!isset(self::$section[$alias])) {
            $item = self::where(self::ALIAS, $alias)->first();
        } else {
            $item = self::$section[$alias];
        }
        if ($item) {
            $data = json_decode($item->value);
            $item->value = $data;
            self::$section[$item->alias] = $item;

            foreach ($data as $key => $values) {
                if ($values->alias == $value) {
                    return $values->value;
                }
            }

        }

        return $default;
    }
}