<?php
namespace FastDog\Media\Events;
use FastDog\Media\Entity\GalleryItem;

/**
 * Перед удалением файла
 *
 * @package FastDog\Media\Events
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BeforeDeleteFile
{
    /**
     * @var GalleryItem $data
     */
    protected $data;

    /**
     * @var array
     */
    protected $result;

    /**
     * BeforeDeleteFile constructor.
     * @param GalleryItem $data
     * @param array $result
     */
    public function __construct(GalleryItem $data, array $result)
    {
        $this->data = &$data;
        $this->result = &$result;
    }


    /**
     * @return GalleryItem
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param GalleryItem $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param $result
     * @return void
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }
}
