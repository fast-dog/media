<?php

namespace FastDog\Media\Events;

/**
 * После удаления файла
 *
 * Событие вызывается в контроллере при удаление файла
 *
 * @package FastDog\Media\Events
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AfterDeleteFile
{
    /**
     * @var \Symfony\Component\HttpFoundation\JsonResponse $data
     */
    protected $data;

    /**
     * @var array $result
     */
    protected $result;

    /**
     * AfterDeleteFile constructor.
     * @param $data
     * @param array $result
     */
    public function __construct($data, array &$result)
    {
        $this->data = &$data;
        $this->result = &$result;
    }


    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
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
