<?php
namespace FastDog\Media\Entity;

use elFinderVolumeLocalFileSystem;
use FastDog\Core\Models\BaseModel;
use FastDog\Core\Models\ModuleManager;
use FastDog\Media\Media;
use FastDog\Admin\Models\Desktop as Desktop;
use FastDog\Media\Models\ElFinderStorage;
use FastDog\Media\Models\Gallery;

/**
 * Загруженный файл
 *
 * @package FastDog\Media\Entity
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class GalleryItem extends BaseModel
{
    /**
     * Сортировка
     * @const string
     */
    const SORT = 'sort';

    /**
     * Идентификатор родительского элемента
     * Родительский элемент определяется в зависимоти от типа TYPE_CONTENT_*
     * @const string
     */
    const PARENT_ID = 'parent_id';

    /**
     * Идентификатор пользователя
     * @const string
     */
    const USER_ID = 'user_id';

    /**
     * Тип родительского элемента
     * Одно из возможных значений TYPE_CONTENT_*
     * @const string
     */
    const PARENT_TYPE = 'parent_type';

    /**
     * Путь к фалу
     * @const string
     */
    const PATH = 'path';

    /**
     * Хэш пути к файлу
     * @const string
     */
    const HASH = 'hash';
    /**
     * Тип: Неопределено
     * @const int
     */
    const TYPE_UNDEFINED = 0;//неопределено

    /**
     * Тип: Материалы
     * @const int
     */
    const TYPE_CONTENT_IMAGE = 1;//материалы

    /**
     * Тип: Категории материалов
     * @const int
     */
    const TYPE_CONTENT_CATEGORY_IMAGE = 2;//категории материалов

    /**
     * Тип: Баннер
     * @const int
     */
    const TYPE_BANNER = 3;// баннер

    /**
     * Тип: позиция каталога
     * @const int
     */
    const TYPE_CATALOG_ITEM_IMAGE = 4; //картинки позиции каталога

    /**
     * Тип: Категория каталога - логотип
     * @const int
     */
    const TYPE_CATALOG_CATEGORY_LOGO = 5; // логотип категорий

    /**
     * Тип: Категория каталога - картинки
     * @const int
     */
    const TYPE_CATALOG_CATEGORY_IMAGES = 6; // картинки категорий

    /**
     * Тип: Категория каталога - файлы
     * @const int
     */
    const TYPE_CATALOG_CATEGORY_FILE = 7; // файлы категорий

    /**
     * Тип: Фото пользователя
     * @const int
     */
    const TYPE_USER_PHOTO = 8; // Фото пользователя

    /**
     * Тип: Справочники
     * Вычисляемый тип, определяется по формуле: 9 * {ID_DATA_SOURCE}
     * @const int
     */
    const TYPE_DATA_SOURCE = 9; // Справочники = {TYPE_DATA_SOURCE} * {ID_DATA_SOURCE}

    /**
     * Тип: меню навигации
     * @const int
     */
    const TYPE_MENU = 10; // Меню навигации

    /**
     * Тип: позиция каталога
     * @const int
     */
    const TYPE_CATALOG_ITEM = 11; // позиция каталога

    /**
     * Тип: позиция каталога
     * @const int
     */
    const TYPE_PUBLIC_MODULES = 12; // позиция каталога

    /**
     * Тип: сообщение чата
     * @const int
     */
    const TYPE_CHAT_MESSAGE = 13;//прикреплено к сообщению чата

    /**
     * Тип: вложенный файл
     * @const int
     */
    const TYPE_MAILING = 14;//прикреплено к сообщению рассылки


    /**
     * Название таблицы в базе данных
     * @var string $table
     */
    protected $table = 'media_gallery_item';

    /**
     * Массив полей автозаполнения
     * @var array $fillable
     */
    protected $fillable = [self::NAME, self::SORT, self::PARENT_ID, self::PARENT_TYPE, self::DATA,
        self::PATH, self::HASH, self::STATE, self::SITE_ID, self::USER_ID];


    /**
     * Форматированный тип
     * Метод возвращает отформатированную строку с определенным типом файла
     * @return string
     */
    public function getType()
    {
        $externalType = [];
        /**
         * Справочкники
         *
         * @var $moduleManager ModuleManager
         */
        $moduleManager = \App::make(ModuleManager::class);
        if ($moduleManager->hasModule('FastDog\DataSource\DataSource')) {
            $dataSource = DataSource::select('name', 'id')->get();
            foreach ($dataSource as $item) {
                $externalType[self::TYPE_DATA_SOURCE * $item->id] = 'Справочник: ' . $item->name;
            }
        }
        if (isset($externalType[$this->{self::PARENT_TYPE}])) {
            return $externalType[$this->{self::PARENT_TYPE}];
        }
        switch ($this->{self::PARENT_TYPE}) {
            case self::TYPE_CONTENT_IMAGE:
                return 'Оформление материалов';
            case self::TYPE_CONTENT_CATEGORY_IMAGE:
                return 'Оформление категории материалов';
            case self::TYPE_BANNER:
                return 'Баннер';
            case self::TYPE_USER_PHOTO:
                return 'Фотография пользователя';
            case self::TYPE_MENU:
                return 'Меню навигации';
            default:
                return 'Не определено';
        }
    }

    /**
     * Имя файла
     * @return mixed
     */
    public function getName()
    {
        if ($this->{self::NAME}) {
            return $this->{self::NAME};
        }

        return array_last(explode('/', $this->{self::PATH}));
    }

    /**
     * Удаляет файл
     *
     * @param $id
     */
    public static function deleteFile($id)
    {
        $file = self::where('id', $id)->first();
        if ($file) {
            if (is_string($file->data)) {
                $file->data = json_decode($file->data);
            }
            if (isset($file->data->thumb) && $file->data->thumb->exist) {
                if (@file_exists($_SERVER['DOCUMENT_ROOT'] . $file->data->thumb->file)) {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . $file->data->thumb->file);
                }
            }
            if (isset($file->data->file)) {
                if (@file_exists($_SERVER['DOCUMENT_ROOT'] . $file->data->file)) {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . $file->data->file);
                }
            }
            self::where('id', $id)->delete();
        }
    }


    /**
     * Превью для изображения
     *
     * @param $resize
     * @return mixed
     */
    public function getThumb($resize)
    {
        if (is_string($this->{self::DATA})) {
            $this->{self::DATA} = json_decode($this->{self::DATA});
        }
        if (isset($this->{self::DATA}->file)) {
            $thumb = Gallery::getPhotoThumb($this->{self::DATA}->file, $resize);

            if ($thumb['exist'] === true) {
                return $thumb['file'];
            }

            return $this->{self::DATA}->file;
        }

        return null;
    }

    /**
     * Получение хэша
     *
     * Экспериментальный метод, в данный момент нигде не используется
     * @param $path
     * @return string
     */
    public static function getHash($path)
    {
        $opts = config('elfinder.options', []);
        $opts = array_merge($opts, ['roots' => Media::getElFinderDirs()]);

        $elFinder = new ElFinderStorage($opts);
        $volumes = $elFinder->getVolumes();
        /**
         * @var $volume elFinderVolumeLocalFileSystem
         */
        foreach ($volumes as $volume) {
            $mountPath = str_replace(['\\'], ['/'], $volume->getRootPath());
        }

        $volumeId = 'l1_';
        $hash = $volumeId . rtrim(strtr(base64_encode($path), '+/=', '-_.'), '.');

        return $hash;
    }
}