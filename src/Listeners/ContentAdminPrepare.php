<?php

namespace FastDog\Media\Listeners;

use FastDog\Content\Entity\Content;
use FastDog\Content\Events\ContentAdminPrepare as EventContentAdminPrepare;
use FastDog\Media\Entity\GalleryItem;
use Illuminate\Http\Request;

/**
 * Обработка параметров при добавление материалов в разделе администрирования
 *
 * Добавляет обязятельные параметры к материалам
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ContentAdminPrepare
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * ContentAdminPrepare constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param EventContentAdminPrepare $event
     */
    public function handle(EventContentAdminPrepare $event)
    {

        $item = $event->getItem();
        $data = $event->getData();

        $data['el_finder'] = [
            GalleryItem::PARENT_TYPE => GalleryItem::TYPE_CONTENT_IMAGE,
            GalleryItem::PARENT_ID => $item->id
        ];

        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }

        $event->setData($data);
    }
}