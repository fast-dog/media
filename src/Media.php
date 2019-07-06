<?php

namespace FastDog\Media;


use FastDog\Core\Interfaces\ModuleInterface;
use FastDog\Core\Models\DomainManager;
use FastDog\Core\Models\Module;
use FastDog\Media\Models\GalleryItem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;

/**
 * Управление медиа данными
 *
 * Обеспечивает учет файлов загруженных через менеджер ElFinder
 *
 * @package FastDog\Media
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Media extends GalleryItem implements ModuleInterface
{
    /**
     * Имя родительского списка доступа
     *
     * из за реализации ACL в пакете kodeine/laravel-acl
     * нужно использовать имя верхнего уровня: action.__CLASS__::SITE_ID::access_level
     *
     * @var string $aclName
     */
    protected $aclName = '';

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
     * Маршруты раздела администратора
     *
     * @return void
     */
    public function routeAdmin()
    {
    }

    /**
     * Маршруты публичного раздела
     *
     * @return void
     */
    public function routePublic()
    {
    }


    /**
     * Параметры модуля
     *
     * @return null
     */
    public function getModuleSetting()
    {
        if ($this->module == null) {
            $this->module = Module::where('name', 'Файлы')->first();
        }
        $data = json_decode($this->module->{self::DATA});

        return (isset($data->setting)) ? $data->setting : null;
    }


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
        return [];
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
     * Возвращает возможные типы модулей
     *
     * @return mixed
     */
    public function getModuleType(): array
    {
        return [];
    }


    /**
     * Возвращает маршрут компонента
     *
     * @param Request $request
     * @param Menu $item
     * @return mixed
     */
    public function getMenuRoute(Request $request, $item): array
    {
        return [];
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
     * Инициализация уровней доступа ACL
     *
     * @return null
     */
    public function initAcl()
    {
        $domainList = DomainManager::getAccessDomainList();
        foreach ($domainList as $domain) {
            if ($domain['id'] !== '000') {
                /**
                 * Имя раздела разрешений должно быть в нижнем регистре из за
                 * особенностей реализации методов в пакете kodeine/laravel-acl
                 */
                $name = strtolower(__CLASS__ . '::' . $domain['id']);

                $roleGuest = DomainManager::getRoleGuest($domain['id']);
                $data = [
                    'name' => $name,
                    'slug' => [
                        'create' => false,
                        'view' => true,
                        'update' => false,
                        'delete' => false,
                        'api' => false,
                    ],
                    'description' => \GuzzleHttp\json_encode([
                        'module_name' => 'Файлы',
                        'description' => 'ACL для домена #' . $domain['id'],
                    ]),
                ];
                $permGuest = Permission::where([
                    'name' => $data['name'] . '::guest',
                ])->first();

                if (!$permGuest) {
                    $data['name'] = $name . '::guest';
                    $permGuest = Permission::create($data);
                    $roleGuest->assignPermission($permGuest);
                } else {
                    Permission::where('id', $permGuest->id)->update([
                        'slug' => json_encode($data['slug']),
                    ]);
                }
                $permUser = Permission::where([
                    'name' => $data['name'] . '::user',
                ])->first();
                if (!$permUser) {
                    $data['inherit_id'] = $permGuest->id;
                    $data['name'] = $name . '::user';
                    $permUser = Permission::create($data);
                } else {
                    Permission::where('id', $permUser->id)->update([
                        'slug' => json_encode($data['slug']),
                    ]);
                }
                if ($permUser) {
                    $roleUser = DomainManager::getRoleUser($domain['id']);
                    if ($roleUser) {
                        $roleUser->assignPermission($permUser);
                    }

                    $roleAdmin = DomainManager::getRoleAdmin($domain['id']);
                    $data['slug'] = [
                        'create' => true,
                        'view' => true,
                        'update' => true,
                        'delete' => true,
                        'api' => true,
                    ];
                    $permAdmin = Permission::where([
                        'name' => $data['name'] . '::admin',
                    ])->first();
                    if (!$permAdmin) {
                        $data['name'] = $name . '::admin';
                        $data['inherit_id'] = $permUser->id;
                        $permAdmin = Permission::create($data);
                        $roleAdmin->assignPermission($permAdmin);
                    } else {
                        Permission::where('id', $permAdmin->id)->update([
                            'slug' => json_encode($data['slug']),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Метод возвращает отображаемый в публичной части контнет
     *
     * @param Components $module
     * @return null|string
     */
    public function getContent(Components $module)
    {
        return null;
    }

    /**
     * Директория модуля
     *
     * @return string
     */
    public function getModuleDir()
    {
        return dirname(__FILE__);
    }

    /**
     * Параметры блоков добавляемых на рабочий стол администратора
     *
     * @return array
     */
    public function getDesktopWidget()
    {
        return [];
    }

    /**
     * Схема установки модуля
     *
     * @param $allSteps
     * @return mixed
     */
    public function getInstallStep(&$allSteps)
    {
        $last = array_last(array_keys($allSteps));

        $allSteps[$last]['step'] = 'media_init';
        $allSteps['media_init'] = [
            'title_step' => trans('app.Модуль Файлы: подготовка, создание таблиц'),
            'step' => 'media_install',
            'stop' => false,
            'install' => function ($request) {
                sleep(1);
            },
        ];
        $allSteps['media_install'] = [
            'title_step' => trans('app.Модуль Файлы: таблицы созданы'),
            'step' => '',
            'stop' => false,
            'install' => function ($request) {
                GalleryItem::createDbSchema();
                GalleryItemHistory::createDbSchema();
                sleep(1);
            },
        ];

        return $allSteps;
    }

    /**
     * Возвращает массив таблиц для резервного копирования
     *
     * @return array
     */
    public function getTables()
    {
        // TODO: Implement getTables() method.
    }

    /**
     * События обрабатываемые модулем
     *
     * @return void
     */
    public function initEvents(): array
    {
        // TODO: Implement initEvents() method.
    }
}