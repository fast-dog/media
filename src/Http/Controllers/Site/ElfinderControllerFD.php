<?php

namespace FastDog\Media\Http\Controllers\Site;


use Barryvdh\Elfinder\Connector;
use Barryvdh\Elfinder\ElfinderController;
use FastDog\Media\Session\LaravelSession;
use FastDog\Core\Models\ModuleManager;
use FastDog\Media\Media;

/**
 * Поддержка просмотра и загрузки файлов
 *
 * Расширение базового контроллера файлового менеджера elFinder  (barryvdh/laravel-elfinder)
 *
 * @package App\Http\Controllers
 */
class ElfinderControllerFD extends ElfinderController
{
    /**
     * @inheritdoc
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function showConnector()
    {

        /**
         * @var $moduleManager ModuleManager
         *
         * Проверка доступных действий
         */
        $moduleManager = \App::make(ModuleManager::class);


        $roots = Media::getElFinderDirs();

        if (app()->bound('session.store')) {
            $sessionStore = app('session.store');
            $session = new LaravelSession($sessionStore);
        } else {
            $session = null;
        }

        $opts = $this->app->config->get('elfinder.options', []);
        $opts = array_merge($opts, ['roots' => $roots, 'session' => $session]);
        $elFinder = new \elFinder($opts);
        $response = null;
        $actionRun = true;
        if (true /*$moduleManager->hasModule('App\Modules\Media\Media')*/) {
            $module = $moduleManager->getInstance('media');
            $setting = $module->getModuleSetting();
            if (isset($_REQUEST['cmd'])) {
                switch ($_REQUEST['cmd']) {
                    case 'upload':
                        if (!$setting->allow_upload) {
                            $actionRun = false;
                        }
                        break;
                    case 'rm':
                        if (!$setting->allow_delete) {
                            $actionRun = false;
                        } else {
                            foreach ($_REQUEST['targets'] as $hash) {
                                $path = str_replace(public_path(), '', $elFinder->realpath($hash));
                                $path = str_replace('\\', '/', $path);
                                /**
                                 * @var $check GalleryItem
                                 */
                                $check = GalleryItem::where([
                                    GalleryItem::PATH => $path,
                                ])->first();
                                if ($check) {
                                    if ($check->{GalleryItem::PARENT_ID} !== null) {
                                        $message = trans('app.Удаляемый файл имеет привязку к: ":type", идентификатор привязки: ":id".', [
                                            'type' => $check->getType(),
                                            'id' => $check->{GalleryItem::PARENT_ID},
                                        ]);

                                        return response()->json(['error' => $message]);
                                    }
                                }
                            }
                            if ($actionRun === false) {

                                return response()->json(['error' => 'Данный файл используется в ХХХ']);
                            }
                        }
                        break;
                    case 'paste':
                        if (!$setting->allow_move) {
                            $actionRun = false;
                        }
                        break;
                }
            }
        }

        if ($actionRun) {
            $connector = new Connector($elFinder);
            $connector->run();
            $response = $connector->getResponse();
        } else {
            return response()->json(['error' => 'Действие запрещено параметрами модуля "Файлы"']);
        }
        if ($moduleManager->hasModule('App\Modules\Media\Media') && isset($_REQUEST['cmd']) && $response) {
            switch ($_REQUEST['cmd']) {
                case 'upload':
                    \Event::fire(new UploadFileElFinder($response, $connector, $elFinder));
                    break;
                case 'rm':
                    \Event::fire(new DeleteFileElFinder($response, $connector, $elFinder));
                    break;
                case 'paste':
                    \Event::fire(new PasteFileElFinder($response, $connector, $elFinder));
                    break;
                case 'rename':
                    \Event::fire(new RenameFileElFinder($response, $connector, $elFinder));
                    break;

            }
        }

        return $response;
    }

}