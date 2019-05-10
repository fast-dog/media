<?php

namespace FastDog\Media\Listeners;

use FastDog\Media\Events\AfterDeleteFile as EventAfterDeleteFile;
use Illuminate\Http\Request;

/**
 * После удаления файла
 *
 * Событие вызывается в контроллере при удаление файла
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AfterDeleteFile
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * AfterDeleteFile constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param EventAfterDeleteFile $event
     */
    public function handle(EventAfterDeleteFile $event)
    {
        $data = $event->getData();
        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}
