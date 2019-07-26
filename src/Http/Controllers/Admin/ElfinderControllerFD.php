<?php

namespace FastDog\Media\Http\Controllers\Admin;


use Barryvdh\Elfinder\Connector;
use Barryvdh\Elfinder\ElfinderController;
use FastDog\Media\Events\DeleteFileElFinder;
use FastDog\Media\Events\PasteFileElFinder;
use FastDog\Media\Events\RenameFileElFinder;
use FastDog\Media\Events\UploadFileElFinder;
use FastDog\Media\Models\GalleryItem;
use FastDog\Media\Models\MediaConfig;
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

            if (isset($_REQUEST['cmd'])) {
                switch ($_REQUEST['cmd']) {
                    case 'upload':
                        if (MediaConfig::getValue(MediaConfig::CONFIG_MAIN, 'allow_upload', 'N') === 'N') {
                            $actionRun = false;
                        }
                        break;
                    case 'rm':
                        if (MediaConfig::getValue(MediaConfig::CONFIG_MAIN, 'allow_delete', 'N') === 'N') {
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
                        if (MediaConfig::getValue(MediaConfig::CONFIG_MAIN, 'allow_move', 'N') === 'N') {
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
        if ($moduleManager->hasModule('media') && isset($_REQUEST['cmd']) && $response) {
            switch ($_REQUEST['cmd']) {
                case 'upload':
                    event(new UploadFileElFinder($response, $connector, $elFinder));
                    break;
                case 'rm':
                    event(new DeleteFileElFinder($response, $connector, $elFinder));
                    break;
                case 'paste':
                    event(new PasteFileElFinder($response, $connector, $elFinder));
                    break;
                case 'rename':
                    event(new RenameFileElFinder($response, $connector, $elFinder));
                    break;
            }
        }

        return $response;
    }

}
