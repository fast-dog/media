<?php

$ctrl = '';// '\FastDog\Media\Http\Controllers\Admin\MediaController';


//список объектов
\Route::post('/public/media/items', $ctrl . '@postItems');

//обновление параметров
\Route::post('/public/media/items/self-update', $ctrl . '@postUpdate');

//обновление параметров
\Route::post('/public/media/config', $ctrl . '@postConfig');

//просмотр информации по модулю
\Route::get('/public/media/admin-info', $ctrl . '@getInfo');

//загрузка?
\Route::post('/public/media/upload', $ctrl . '@postUpload');

//удаление?)
\Route::post('/public/media/delete', $ctrl . '@postDelete');

//очистка кэша
\Route::post('/public/media/clear-cache', $ctrl . '@postClearCache');

//сохранение конфигурации
\Route::post('/public/media/save-module-configurations', $ctrl . '@postSaveModuleConfigurations');
