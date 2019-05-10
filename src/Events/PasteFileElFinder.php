<?php

namespace FastDog\Media\Events;

use Barryvdh\Elfinder\Connector;
use elFinder;
use Illuminate\Queue\SerializesModels;

/**
 * Перемещение файла через файловый менеджер elFinder
 *
 * @package FastDog\Media\Events
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class PasteFileElFinder
{
    use  SerializesModels;

    /**
     * @var \Symfony\Component\HttpFoundation\JsonResponse $data
     */
    protected $data;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var elFinder
     */
    protected $elFinder;

    /**
     * PasteFileElFinder constructor.
     * @param \Symfony\Component\HttpFoundation\JsonResponse $data
     * @param Connector $connector
     * @param elFinder $elFinder
     */
    public function __construct(\Symfony\Component\HttpFoundation\JsonResponse $data,
                                Connector $connector, elFinder $elFinder)
    {
        $this->data = &$data;
        $this->connector = $connector;
        $this->elFinder = $elFinder;
    }

    /**
     * @return elFinder
     */
    public function getElFinder()
    {
        return $this->elFinder;
    }

    /**
     * @return Connector
     */
    public function getConnector()
    {
        return $this->connector;
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
     * @return  void
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
