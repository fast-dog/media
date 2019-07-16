<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 05.11.2016
 * Time: 17:02
 */

namespace FastDog\Media;

use Barryvdh\Elfinder\ElfinderServiceProvider;
use Illuminate\Routing\Router;

class ElfinderServiceProviderFD extends ElfinderServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        if (!defined('ELFINDER_IMG_PARENT_URL')) {
            define('ELFINDER_IMG_PARENT_URL', $this->app['url']->asset('packages/barryvdh/elfinder'));
        }

        $config = $this->app['config']->get('elfinder.route', []);
        $config['namespace'] = 'Barryvdh\Elfinder';

        $router->group($config, function($router)
        {
            $router->get('/', 'ElfinderController@showIndex');
            $router->any('connector', ['as' => 'elfinder.connector', 'uses' => '\App\Http\Controllers\ElfinderControllerFD@showConnector']);
            $router->get('popup/{input_id}', ['as' => 'elfinder.popup', 'uses' => 'ElfinderController@showPopup']);
            #$router->get('filepicker/{input_id}', ['as' => 'elfinder.filepicker', 'uses' => 'ElfinderController@showFilePicker']);
            #$router->get('tinymce', ['as' => 'elfinder.tinymce', 'uses' => 'ElfinderController@showTinyMCE']);
            #$router->get('tinymce4', ['as' => 'elfinder.tinymce4', 'uses' => 'ElfinderController@showTinyMCE4']);
            $router->get('ckeditor', ['as' => 'elfinder.ckeditor', 'uses' => 'ElfinderController@showCKeditor4']);
        });
    }
}