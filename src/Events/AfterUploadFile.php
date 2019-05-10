<?php

namespace FastDog\Media\Events;


use FastDog\Media\Entity\GalleryItem;

/**
 * После загрузки файла
 *
 * @package FastDog\Media\Events
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AfterUploadFile
{

    /**
     * @var array|object|\StdClass $data
     */
    protected $data;

    /**
     * @var array $result
     */
    protected $result;

    /**
     * AfterUploadFile constructor.
     * @param GalleryItem $data
     * @param array $result
     */
    public function __construct(&$data, array &$result)
    {
        $this->data = &$data;
        $this->result = &$result;
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
     * @return void
     */
    public function setData($data)
    {
        $this->data = &$data;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param $result
     * @return void
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

}
