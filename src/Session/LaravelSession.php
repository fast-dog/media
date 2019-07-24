<?php

namespace FastDog\Media\Session;


use Barryvdh\Elfinder\Session\LaravelSession as BaseSession;


/**
 * Расширение стандартного обработчика сессий, нужна для работы файломого менеджера
 *
 * пакет  Barryvdh\Elfinder
 *
 * @package App\Core\ElFinderFD
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class LaravelSession extends BaseSession
{

    /**
     * Старт сессии
     *
     * @return $this
     */
    public function start()
    {
        if ($this->store->getId() == null) {
            $this->store->start();
        }

        return $this;
    }

}