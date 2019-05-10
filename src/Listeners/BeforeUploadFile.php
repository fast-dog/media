<?php

namespace FastDog\Media\Listeners;

use App\Core\BaseModel;
use FastDog\Config\Entity\DomainManager;
use FastDog\Media\Events\BeforeUploadFile as EventBeforeUploadFile;

use Illuminate\Http\Request;

/**
 * Перед загрузкой файла
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BeforeUploadFile
{
    /**
     * @var Request
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
     * @param EventBeforeUploadFile $event
     */
    public function handle(EventBeforeUploadFile $event)
    {
        $data = $event->getData();

        $data[BaseModel::SITE_ID] = DomainManager::getSiteId();


        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }

        $event->setData($data);
    }
}
