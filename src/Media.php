<?php

namespace FastDog\Media;


use FastDog\Core\Models\DomainManager;
use FastDog\Media\Models\GalleryItem;
use FastDog\User\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;

/**
 * Управление медиа данными
 *
 * Обеспечивает учет файлов загруженных через менеджер ElFinder
 *
 * @package FastDog\Media
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Media extends GalleryItem
{
    /**
     * Идентификатор модуля
     * @const string
     */
    const MODULE_ID = 'media';


    /**
     * Параметры конфигурации описанные в module.json
     *
     * @var null|object $data
     */
    protected $data;

    /**
     * @var $module
     */
    public $module;


    /**
     * Доступные шаблоны
     *
     * @return null|array
     */
    public function getTemplates($paths = ''): array
    {
        return [];
    }

    /**
     * Доступные типы меню
     *
     * @return null|array
     */
    public function getMenuType(): array
    {
        return [];
    }

    /**
     * Возвращает информацию о модуле
     *
     * @param bool $includeTemplates
     * @return array
     */
    public function getModuleInfo($includeTemplates = true): array
    {
        $paths = Arr::first(config('view.paths'));
        $templates_paths = [];// $this->getTemplatesPaths();

        return [
            'id' => self::MODULE_ID,
            'menu' => function() use ($paths, $templates_paths) {
                $result = collect();
                foreach ($this->getMenuType() as $id => $item) {
                    $result->push([
                        'id' => self::MODULE_ID . '::' . $item['id'],
                        'name' => $item['name'],
                        'sort' => $item['sort'],
                        'templates' => (isset($templates_paths[$id])) ? $this->getTemplates($paths . $templates_paths[$id]) : [],
                        'class' => __CLASS__,
                    ]);
                }
                $result = $result->sortByDesc('sort');
                return $result;
            },
            'templates_paths' => $templates_paths,
            'module_type' => $this->getMenuType(),
            'admin_menu' => function() {
                return $this->getAdminMenuItems();
            },
            'access' => function() {
                return [
                    '000',
                ];
            },
        ];
    }

    /**
     * Устанавливает параметры в контексте объекта
     *
     * @param $data
     * @return mixed
     */
    public function setConfig(\StdClass $data): void
    {
        $this->data = $data;
    }

    /**
     *  Возвращает параметры объекта
     *
     * @return mixed
     */
    public function getConfig(): \StdClass
    {
        return $this->data;
    }

    /**
     * Список доступных в файловом менеджере директорий
     *
     * @return array
     */
    public static function getAllowStorage()
    {
        $result = [];
        /**
         * @var User $user
         */
        $user = \Auth::getUser();
        /**
         * Подключение директорий доступных для сайтов
         */

        $list = DomainManager::getAccessDomainList();
        $currentDomainCode = DomainManager::getSiteId();

        if (DomainManager::checkIsDefault()) {
            foreach ($list as $item) {
                array_push($result, [
                    'dir' => 'upload/upload.' . $item['id'] . '/',
                    'alias' => '#' . $item['id'] . ' (' . $item['name'] . ')',
                ]);
                array_push($result, [
                    'dir' => 'upload/catalog.' . $item['id'],
                    'alias' => '#Catalog (' . $item['name'] . ')',
                ]);
            }
        } else {
            array_push($result, [
                'dir' => 'upload/upload.000/',
                'alias' => 'Общее хранилище',
            ]);
            if (isset($list[$currentDomainCode])) {
                array_push($result, [
                    'dir' => 'upload/upload.' . $currentDomainCode . '/',
                    'alias' => '#' . $list[$currentDomainCode]['name'],
                ]);
            }
        }

        return $result;
    }

    public static function getElFinderDirs()
    {
        $dirs = Media::getAllowStorage();

        $roots = [];

        foreach ($dirs as $dir) {
            $roots[] = [
                'alias' => $dir['alias'],
                'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                'path' => public_path($dir['dir']), // path to files (REQUIRED)
                'URL' => url($dir['dir']), // URL to files (REQUIRED)
                'accessControl' => config('elfinder.access') // filter callback (OPTIONAL)
            ];
        }

        $disks = (array)config('elfinder.disks', []);

        foreach ($disks as $key => $root) {
            $disk = app('filesystem')->disk($key);
            if ($disk instanceof FilesystemAdapter) {
                $defaults = [
                    'driver' => 'Flysystem',
                    'filesystem' => $disk->getDriver(),
                    'alias' => (isset($root['alias'])) ? $root['alias'] : $key,
                    'accessControl' => config('elfinder.access') // filter callback (OPTIONAL)
                ];
                $roots[] = $defaults;// array_merge($defaults, $root);
            }
        }
        $rootOptions = config('elfinder.root_options', []);
        foreach ($roots as $key => $root) {
            $roots[$key] = array_merge($rootOptions, $root);
        }

        return $roots;
    }

    /**
     * Меню администратора
     *
     * Возвращает пунты меню для раздела администратора
     *
     * @return array
     */
    public function getAdminMenuItems()
    {
        $result = [
            'icon' => 'fa-folder-o',
            'name' => trans('media::interface.Файлы'),
            'route' => '/media',
            'children' => [],
        ];

        array_push($result['children'], [
            'icon' => 'fa-gears',
            'name' => trans('media::interface.Настройки'),
            'route' => '/media/configuration',
        ]);

        $result = [];

        return $result;
    }
}
