<?php
namespace FastDog\Media\Events;
use FastDog\Media\Entity\GalleryItem;

/**
 * Перед загрузкой файла
 *
 * @package FastDog\Media\Events
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BeforeUploadFile
{

    /**
     * @var array|object|\StdClass
     */
    protected $data;

    /**
     * @var GalleryItem
     */
    protected $item;

    /**
     * BeforeUploadFile constructor.
     * @param $data
     */
    public function __construct(&$data)
    {
        $this->data = &$data;
    }


    /**
     * @return array|object|\StdClass
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     */
    public function setData(&$data)
    {
        $this->data = $data;
    }


}
