<?php

namespace FastDog\Media\Listeners;

use FastDog\Media\Entity\GalleryItem;
use FastDog\Media\Events\AfterUploadFile as EventAfterUploadFile;

use Illuminate\Http\Request;

/**
 * После загрузки файла
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AfterUploadFile
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
     * @param EventAfterUploadFile $event
     */
    public function handle(EventAfterUploadFile $event)
    {
//        $item = $event->getItem();
        $data = $event->getData();
        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}
