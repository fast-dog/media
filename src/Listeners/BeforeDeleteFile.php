<?php

namespace FastDog\Media\Listeners;

use Illuminate\Http\Request;
use FastDog\Media\Events\BeforeDeleteFile as EventBeforeDeleteFile;

/**
 * Перед удалением файла
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BeforeDeleteFile
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param EventBeforeDeleteFile $event
     */
    public function handle(EventBeforeDeleteFile $event)
    {
        $data = $event->getData();
        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}
