<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 03.12.2016
 * Time: 13:36
 */

namespace FastDog\Media\Controllers\Admin;

use App\Core\Acl\Permission;
use App\Core\Acl\Role;
use App\Core\BaseModel;
use App\Core\Desktop;
use App\Core\Module\Module;
use App\Core\Module\ModuleManager;
use App\Http\Controllers\Controller;
use FastDog\Config\Config;
use FastDog\Config\Entity\DomainManager;
use FastDog\Media\Entity\GalleryItem;
use FastDog\Media\Entity\MediaConfig;
use FastDog\Media\Events\AfterDeleteFile;
use FastDog\Media\Events\AfterUploadFile;
use FastDog\Media\Events\BeforeDeleteFile;
use FastDog\Media\Events\BeforeUploadFile;
use FastDog\Media\Events\ItemsAdminPrepare;
use FastDog\Media\Media;
use FastDog\Media\Request\Upload;
use Illuminate\Http\Request;

/**
 * Управление медиа материалами
 *
 * @package FastDog\Media\Controllers\Admin
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class MediaController extends Controller
{
    /**
     * Просмотр зарегистрированных файлов
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postItems(Request $request)
    {
        $result = [
            'success' => true,
            'items' => [],
            'page_title' => 'Файлы',
            'breadcrumbs' => [
                ['url' => '/', 'name' => 'Главная'],
                ['url' => false, 'name' => 'Обзор зарегистрированных файлов'],
            ],
            'access' => [
                'reorder' => false,
                'delete' => false,
                'update' => false,
                'create' => false,
            ],
            'cols' => [
                [
                    'name' => 'Название',
                    'key' => BaseModel::NAME,
                    'class' => null,
                    'domain' => true,
                ],
                [
                    'name' => 'Дата',
                    'key' => 'created_at',
                    'width' => 150,
                    'class' => 'text-center',
                ],
                [
                    'name' => '#',
                    'key' => 'id',
                    'width' => 80,
                    'class' => 'text-center',
                ],
            ],
            'filters' => [

            ],
        ];

        $scope = 'defaultSite';
        if (DomainManager::checkIsDefault()) {
            $scope = 'default';
            $result['filters'][BaseModel::SITE_ID] = DomainManager::getAccessDomainList();
        }

        $items = GalleryItem::where(function ($query) use ($request, &$scope) {
            $this->_getMenuFilter($query, $request, $scope, GalleryItem::class);
        })->$scope()->orderBy($request->input('order_by', 'created_at'), $request->input('direction', 'DESC'))
            ->paginate($request->input('limit', self::PAGE_SIZE));
        /**
         * @var $item GalleryItem
         */
        foreach ($items as $item) {
            array_push($result['items'], [
                'id' => $item->id,
                'name' => $item->getName(),
                'link' => $item->path,// html href=""
                'blank' => true,// html target="_blank"
                'site_id' => $item->site_id,
                'created_at' => $item->created_at->format('d.m.y H:i'),
                'type' => $item->getType(),
                'state' => $item->state,
                'published' => $item->state,
                'checked' => false,
            ]);
        }

        \Event::fire(new ItemsAdminPrepare($result, $items));


        $this->_getCurrentPaginationInfo($request, $items, $result);

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление модели
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdate(Request $request)
    {
        $result = ['success' => true, 'items' => []];
        $this->updatedModel($request->all(), GalleryItem::class);

        return response()->json($result);
    }

    /**
     * Информация по модулю
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInfo(Request $request)
    {
        $result = ['success' => true,
            'items' => [],
            'page_title' => trans('app.Файлы'),
            'breadcrumbs' => [
                ['url' => '/', 'name' => trans('app.Главная')],
                ['url' => false, 'name' => trans('app.Настройки')],
            ],
        ];

        /**
         * Параметры модуля
         */
        array_push($result['items'], MediaConfig::getAllConfig());

        /**
         * Статистика по состояниям
         */
        array_push($result['items'], Media::getStatistic());

        /**
         * Список доступа ACL
         */
        array_push($result['items'], Config::getAcl(DomainManager::getSiteId(), strtolower(Media::class)));


        return response()->json($result);
    }

    /**
     * Изменение доступа к модулю
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAccess(Request $request)
    {
        $result = ['success' => false];

        $role = Role::where([
            Role::NAME => $request->input('role'),
        ])->first();
        if ($role) {
            $permission = Permission::where(function ($query) use ($request, $role) {
                $query->where(Permission::NAME, $request->input('permission') . '::' . $role->slug);
            })->first();

            if ($permission) {
                if (isset($permission->slug[$request->input('accessName')])) {
                    $permission_slug = $permission->slug;
                    $permission_slug[$request->input('accessName')] = ($request->input('accessValue') == 'Y') ? true : false;
                    Permission::where('id', $permission->id)->update([
                        'slug' => \GuzzleHttp\json_encode($permission_slug),
                    ]);
                }
            }
            $result['acl'] = Config::getAcl(DomainManager::getSiteId(), strtolower(Media::class));
        }

        return $this->json($result, __METHOD__);
    }


    /**
     * Очистка кэша
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postClearCache(Request $request)
    {
        $result = ['success' => true, 'message' => ''];
        $tag = $request->input('tag');
        switch ($tag) {
            case 'all':
                \Cache::flush();
                $result['message'] = 'Кэш успешно очищен.';
                break;
            case 'media':
                if (env('CACHE_DRIVER') == 'redis') {
                    \Cache::tags([$tag])->flush();
                    $result['message'] = 'Кэш "' . $tag . '" успешно очищен.';
                } else {
                    \Cache::flush();
                    $result['message'] = 'Кэш успешно очищен.';
                }
                break;
        }

        return response()->json($result);
    }

    /**
     * Сохранение параметров
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postConfig(Request $request)
    {
        /**
         * @var  $moduleManager ModuleManager
         */
        $moduleManager = \App::make('ModuleManager');
        $module = $moduleManager->getInstance('FastDog\Media\Media');
        $config = $module->getModuleSetting();
        $allConfig = $module->getConfig();

        $field = $request->input('field', null);

        if ($field && isset($config->{$field})) {
            $config->{$field} = $request->input('value', 0) == 1 ? true : false;
            $allConfig->setting = $config;
            Module::where('name', 'Файлы')->update([
                Module::DATA => json_encode($allConfig),
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Загрузка файла
     *
     * @param Upload $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpload(Upload $request)
    {
        $result = ['success' => false];
        $data = [
            'file' => $request->file('file'),
            'target_dir' => _ROOT_ . DIRECTORY_SEPARATOR . _UPLOAD_,
            'success' => false,
        ];
        /**
         * Вызываем событие для определение параметров загрузки
         */
        \Event::fire(new BeforeUploadFile($data));

        if ($data['success']) {
            $file = $data['file'];
            $file->move($data['target_dir'], $data['filename']);
            /**
             * Вызываем событие для регистрации файла и генерации ответа
             */
            if ($this->fireEvent) {
                \Event::fire(new AfterUploadFile($data, $result));
            }
        }

        return response()->json($result);
    }

    /**
     * Удаление файла
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDelete(Request $request)
    {
        $result = ['success' => false];
        /**
         * Request Payload
         */
        $ids = (array)$request->id; //$request->input('id');

        $files = GalleryItem::whereIn('id', $ids)->get();
        foreach ($files as $file) {
            \Event::fire(new BeforeDeleteFile($file, $result));
            GalleryItem::deleteFile($file->id);
            \Event::fire(new AfterDeleteFile($file, $result));
        }
        $result['success'] = true;

        return response()->json($result);
    }

    /**
     * Сохранение параметров модуля
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSaveModuleConfigurations(Request $request)
    {
        $result = ['success' => true, 'items' => []];

        $type = $request->input('type');
        $item = MediaConfig::where(MediaConfig::ALIAS, $type)->first();
        if ($item) {
            $values = $request->input('value');
            switch ($type) {
                case MediaConfig::CONFIG_DESKTOP:
                    foreach ($values as $value) {
                        Desktop::check($value['value'], [
                            'name' => $value['name'],
                            'type' => $value['type'],
                            'data' => [
                                'data' => $value['data'],
                                'cols' => 3,
                            ],
                        ]);
                    }
                    break;
                case MediaConfig::CONFIG_PUBLIC:
                    break;
                default:
                    break;
            }
            MediaConfig::where('id', $item->id)->update([
                MediaConfig::VALUE => json_encode($values),
            ]);
        }

        array_push($result['items'], MediaConfig::getAllConfig());

        return $this->json($result, __METHOD__);
    }
}