<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 18.12.2016
 * Time: 23:33
 */

namespace FastDog\Media\Listeners;


use FastDog\Config\Entity\DomainManager;
use FastDog\Media\Entity\GalleryItem;
use FastDog\Media\Events\ItemsAdminPrepare as ItemsAdminPrepareEvent;
use Illuminate\Http\Request;

/**
 * Просмотр в разделе администрирования
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ItemsAdminPrepare
{

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * AfterSave constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param ItemsAdminPrepareEvent $event
     */
    public function handle(ItemsAdminPrepareEvent $event)
    {
        /**
         * @var $data array
         */
        $data = $event->getData();

        if (DomainManager::checkIsDefault()) {
            foreach ($data['items'] as &$item) {
                $item['suffix'] = DomainManager::getDomainSuffix($item[GalleryItem::SITE_ID]);
            }
        }
        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}