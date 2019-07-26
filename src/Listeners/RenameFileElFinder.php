<?php

namespace FastDog\Media\Listeners;

use FastDog\Core\Models\ModuleManager;
use FastDog\Media\Entity\GalleryItemHistory;
use FastDog\Media\Events\RenameFileElFinder as EventRenameFileElFinder;
use FastDog\Media\Models\GalleryItem;
use Illuminate\Http\Request;

/**
 * Перемещение файла через файловый менеджер elFinder
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class RenameFileElFinder
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * PasteFileElFinder constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param EventRenameFileElFinder $event
     * @return void
     */
    public function handle(EventRenameFileElFinder $event)
    {
        $response = $event->getData();
        $data = json_decode($response->getContent());
        /**
         * @var $moduleManager ModuleManager
         */
        $moduleManager = \App::make(ModuleManager::class);
        $module = $moduleManager->getInstance('FastDog\Media\Media');
        $setting = $module->getModuleSetting();
        $elfinder = $event->getElFinder();


        $check = null;
        if (isset($data->removed)) {
            foreach ($data->removed as $hash) {
                $check = GalleryItem::where(GalleryItem::HASH, md5($hash))->first();
            }
        }
        if (isset($data->added)) {
            foreach ($data->added as $item) {
                if ($check) {
                    $newPath = str_replace(public_path(), '', $elfinder->realpath($item->hash));
                    $newPath = str_replace('\\', '/', $newPath);
                    GalleryItem::where('id', $check->id)->update([
                        GalleryItem::PATH => $newPath,
                        GalleryItem::HASH => md5($item->hash),
                        GalleryItem::DATA => json_encode($item),
                    ]);
                    if (($setting !== null && $setting->history == true)) {
                        GalleryItemHistory::create([
                            GalleryItemHistory::PARENT_ID => $check->id,
                            GalleryItemHistory::TYPE_ID => GalleryItemHistory::ACTION_RENAME,
                            GalleryItemHistory::DATA => json_encode([
                                'dir' => $newPath,
                            ]),
                        ]);
                    }
                }
            }
        }
    }
}
