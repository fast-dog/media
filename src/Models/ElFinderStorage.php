<?php

namespace FastDog\Media\Models;

/**
 * Class ElFinderStorage
 *
 * @package FastDog\Media\Models
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ElFinderStorage extends \elFinder
{

    public function __construct($opts)
    {
        parent::__construct($opts);
    }

    public function getVolumes()
    {
        return $this->volumes;
    }


}