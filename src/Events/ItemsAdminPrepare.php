<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 19.12.2016
 * Time: 3:47
 */

namespace FastDog\Media\Events;


use FastDog\Menu\Entity\Menu;

/**
 * Просмотр в разделе администрирования
 *
 * @package FastDog\Media\Events
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ItemsAdminPrepare
{

    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @var Menu $item
     */
    protected $item;

    /**
     * MenuItemBeforeSave constructor.
     * @param array $data
     * @param $item
     */
    public function __construct(array &$data, &$item)
    {
        $this->data = &$data;
        $this->item = &$item;
    }

    /**
     * @return Menu
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}