<?php

namespace FastDog\Media\Listeners;

use FastDog\Config\Entity\DomainManager;
use FastDog\Media\Entity\GalleryItem;
use FastDog\Media\Events\UploadFileElFinder as EventUploadFileElFinder;
use Illuminate\Http\Request;

/**
 * Загрузка файла через файловый менеджер elFinder
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class UploadFileElFinder
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * UploadFileElFinder constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param EventUploadFileElFinder $event
     */
    public function handle(EventUploadFileElFinder $event)
    {
        $response = $event->getData();
        $data = json_decode($response->getContent());
        if (isset($data->added)) {
            foreach ($data->added as $item) {
                $check = GalleryItem::where([
                    GalleryItem::PATH => $item->url,
                    GalleryItem::PARENT_TYPE => $this->request->input(GalleryItem::PARENT_TYPE),
                    GalleryItem::PARENT_ID => $this->request->input(GalleryItem::PARENT_ID),
                    GalleryItem::SITE_ID => DomainManager::getSiteId()//TODO: не учитывает загрузку с главного домена
                ])->first();

                if (!$check) {
                    $path = str_replace(url('/'), '', $item->url);
                    if ($path !== '') {
                        GalleryItem::create([
                            GalleryItem::PARENT_TYPE => $this->request->input(GalleryItem::PARENT_TYPE),
                            GalleryItem::PARENT_ID => $this->request->input(GalleryItem::PARENT_ID),
                            GalleryItem::PATH => $path,
                            GalleryItem::DATA => json_encode($item),
                            GalleryItem::HASH => md5($item->hash),
                            GalleryItem::SITE_ID => DomainManager::getSiteId(),
                        ]);
                    }
                }
            }
        }
    }
}
