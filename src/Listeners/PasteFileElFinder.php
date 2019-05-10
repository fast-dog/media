<?php

namespace FastDog\Media\Listeners;

use App\Core\Module\ModuleManager;
use FastDog\Media\Entity\GalleryItem;
use FastDog\Media\Entity\GalleryItemHistory;
use FastDog\Media\Events\PasteFileElFinder as EventPasteFileElFinder;
use Illuminate\Http\Request;

/**
 * Перемещение файла через файловый менеджер elFinder
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class PasteFileElFinder
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
     * @param EventPasteFileElFinder $event
     * @return void
     */
    public function handle(EventPasteFileElFinder $event)
    {
        $response = $event->getData();
        $data = json_decode($response->getContent());
        /**
         * @var $moduleManager ModuleManager
         */
        $moduleManager = \App::make(\App\Core\Module\ModuleManager::class);
        $module = $moduleManager->getInstance('FastDog\Media\Media');
        $setting = $module->getModuleSetting();

        if (isset($data->changed) && isset($data->added)) {
            $volId = '';
            $dir = '';
            foreach ($data->changed as $item) {
                if ($item->mime == 'directory') {
                    $volId = $item->volumeid;
                    $dir = str_replace('\\', '/', base64_decode(str_replace($item->volumeid, '', $item->hash)));
                }
            }
            if (isset($data->removed)) {
                foreach ($data->removed as $hash) {
                    $check = GalleryItem::where(GalleryItem::HASH, md5($hash))->first();
                    if (($setting !== null && $setting->history == true) && $check) {
                        GalleryItemHistory::create([
                            GalleryItemHistory::PARENT_ID => $check->id,
                            GalleryItemHistory::TYPE_ID => GalleryItemHistory::ACTION_MOVE,
                            GalleryItemHistory::DATA => json_encode([
                                'dir' => $dir,
                            ]),
                        ]);
                    }
                }
            }
            if (isset($data->added)) {
                foreach ($data->added as $item) {
                    $check = GalleryItem::where(GalleryItem::HASH, md5($item->hash))->withTrashed()->first();
                    if ($check) {
                        $check->restore();
                    } else {
                        $file = base64_decode(str_replace($volId, '', $item->hash));
                        $file = '/upload/' . str_replace('\\', '/', $file);
                        $path = str_replace($this->request->url(), '', $file);
                        $check = GalleryItem::create([
                            GalleryItem::PARENT_TYPE => $this->request->input(GalleryItem::PARENT_TYPE),
                            GalleryItem::PARENT_ID => $this->request->input(GalleryItem::PARENT_ID),
                            GalleryItem::PATH => $path,
                            GalleryItem::DATA => json_encode($item),
                            GalleryItem::HASH => md5($item->hash),
                        ]);
                    }
                    if (($setting !== null && $setting->history == true)) {
                        GalleryItemHistory::create([
                            GalleryItemHistory::PARENT_ID => $check->id,
                            GalleryItemHistory::TYPE_ID => GalleryItemHistory::ACTION_MOVE,
                            GalleryItemHistory::DATA => json_encode([
                                'dir' => $dir,
                            ]),
                        ]);
                    }
                }
            }
        }
    }
}
