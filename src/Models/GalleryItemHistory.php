<?php

namespace FastDog\Media\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * История выполнения действий
 *
 * Обеспечивает учет загрузки\перемещения файлов
 *
 * @package FastDog\Media\Entity
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class GalleryItemHistory extends Model
{
    /**
     * Идентификатор файла
     * @const string
     */
    const PARENT_ID = 'parent_id';
    /**
     * Тип события
     * @const string
     */
    const TYPE_ID = 'type';
    /**
     * Дополнительные параметры
     * @const string
     */
    const DATA = 'data';

    /**
     * Действие неопределено
     * @const int
     */
    const ACTION_UNDEFINED = 0;
    /**
     * Загрузка файла
     * @const int
     */
    const ACTION_UPLOAD = 1;
    /**
     * Перемещение файлв
     * @const int
     */
    const ACTION_MOVE = 2;
    /**
     * Переименование файла
     * @const int
     */
    const ACTION_RENAME = 3;
    /**
     * Имя таблицы
     *
     * @var string $table
     */
    public $table = 'media_gallery_item_history';

    /**
     * Массив полей автозаполнения
     *
     * @var array $fillable
     */
    public $fillable = [self::PARENT_ID, self::TYPE_ID, self::DATA];

    /**
     * Удаление записи
     * @return bool
     */
    public function delete()
    {
        return false;
    }

}
