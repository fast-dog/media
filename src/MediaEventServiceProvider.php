<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 010 10.05.19
 * Time: 22:16
 */

namespace FastDog\Meida;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class MediaEventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'FastDog\Media\Events\UploadFileElFinder' => [
            'FastDog\Media\Listeners\UploadFileElFinder',
        ],
        'FastDog\Media\Events\DeleteFileElFinder' => [
            'FastDog\Media\Listeners\DeleteFileElFinder',
        ],
        'FastDog\Media\Events\PasteFileElFinder' => [
            'FastDog\Media\Listeners\PasteFileElFinder',
        ],
        'FastDog\Media\Events\RenameFileElFinder' => [
            'FastDog\Media\Listeners\RenameFileElFinder',
        ],
        'FastDog\Media\Events\BeforeUploadFile' => [
            'FastDog\Media\Listeners\BeforeUploadFile',
        ],
        'FastDog\Media\Events\AfterUploadFile' => [
            'FastDog\Media\Listeners\AfterUploadFile',
        ],
        'FastDog\Media\Events\BeforeDeleteFile' => [
            'FastDog\Media\Listeners\BeforeDeleteFile',
        ],
        'FastDog\Media\Events\AfterDeleteFile' => [
            'FastDog\Media\Listeners\AfterDeleteFile',
        ],
        'FastDog\Content\Events\ContentAdminPrepare' => [
            'FastDog\Media\Listeners\ContentAdminPrepare',
        ],
        'FastDog\Media\Events\ItemsAdminPrepare' => [
            'FastDog\Media\Listeners\ItemsAdminPrepare',
        ],
    ];


    /**
     * @return void
     */
    public function boot()
    {
        parent::boot();


        //
    }

    public function register()
    {
        //
    }
}