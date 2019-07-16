<?php
/**
 * @var $moduleManager \App\Core\Module\ModuleManager
 */
$moduleManager = \App::make(\App\Core\Module\ModuleManager::class);

/**
 * @var $user \App\Modules\Users\Entity\User
 */
$user = Auth::getUser();

$theme = \App\Modules\Media\Entity\MediaConfig::getValue( 'main','theme', 'default');


$id = \Request::input('popup_id', 'elFinderModal');

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Обозреватель файлов (elFinder 2.0)</title>

    <!-- jQuery and jQuery UI (REQUIRED) -->
    <link rel="stylesheet" type="text/css"
          href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/themes/smoothness/jquery-ui.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
    <!-- elFinder CSS (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="<?= asset($dir . '/css/elfinder.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset($dir . '/themes/' . $theme . '/css/theme.css') ?>">
    <!-- elFinder JS (REQUIRED) -->
    <script src="<?= asset($dir . '/js/elfinder.min.js') ?>"></script>
    <!-- elFinder translation (OPTIONAL) -->
    <script src="<?= asset($dir . "/js/i18n/elfinder.$locale.js") ?>"></script>
    <!-- Include jQuery, jQuery UI, elFinder (REQUIRED) -->

    <script type="text/javascript">
        $().ready(function () {
            window.parent.parameters.elfinder = $('#elfinder').elfinder({
                height: 600,
                width: 900,
                lang: 'ru',
                customData: {
                    _token: '<?= csrf_token() ?>',
                    user_id: '<?= $user->id ?>',
                    '<?=env('SESSION_COOKIE_NAME')?>': '<?=\Session::getId()?>',
                    'parent_id': window.parent.parameters.parent_id,
                    'parent_type': window.parent.parameters.parent_type,
                },
                url: '<?= route("elfinder.connector") ?>',  // connector URL
                resizable: false,
                commandsOptions: {
                    getfile: { multiple: true }
                },
                getFileCallback: function (file) {
                    if (typeof window.parent.elfinderSelectFile == "function") {
                        window.parent.elfinderSelectFile(file);
                    }
                    //$('#<?= $input_id?>', window.parent.document).val(file.path).trigger('change')
                    $('button[data-dismiss="modal"]', $('#<?=$id?>', window.parent.document)).click();
                    return false;
                }
            }).elfinder('instance');
        });
    </script>


</head>
<body>
<!-- Element where elFinder will be created (REQUIRED) -->
<div id="elfinder"></div>

</body>
</html>
