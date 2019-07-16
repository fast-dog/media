<?php
$moduleManager = \App::make(\App\Core\Module\ModuleManager::class);

$theme = \App\Modules\Media\Entity\MediaConfig::getValue( 'main','theme', 'default');

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Обозреватель файлов (elFinder 2.0)</title>
    <!-- jQuery and jQuery UI (REQUIRED) -->
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css"/>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>

    <!-- elFinder CSS (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="<?= asset($dir . '/css/elfinder.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset($dir . '/themes/' . $theme . '/css/theme.css') ?>">

    <!-- elFinder JS (REQUIRED) -->
    <script src="<?= asset($dir . '/js/elfinder.min.js') ?>"></script>
    <script src="<?= asset($dir . "/js/i18n/elfinder.$locale.js") ?>"></script>


    <!-- elFinder initialization (REQUIRED) -->
    <script type="text/javascript" charset="utf-8">
        // Helper function to get parameters from the query string.
        function getUrlParam(paramName) {
            var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i');
            var match = window.location.search.match(reParam);

            return (match && match.length > 1) ? match[1] : '';
        }

        $().ready(function () {
            var funcNum = getUrlParam('CKEditorFuncNum');

            var elf = $('#elfinder').elfinder({
                height: $(window).height(),
                lang: 'ru',
                customData: {
                    _token: '<?= csrf_token() ?>',
                    '<?=env('SESSION_COOKIE_NAME')?>': '<?=\Session::getId()?>',
                    'parent_id': <?= (isset($_REQUEST['parent_id'])) ? (int)$_REQUEST['parent_id'] : 0?>,
                    'parent_type': '<?=isset($_REQUEST['parent_type'])   ? $_REQUEST['parent_type'] : ''?>'
                },
                url: '<?= route("elfinder.connector") ?>',  // connector URL
                getFileCallback: function (file) {
                    window.opener.CKEDITOR.tools.callFunction(funcNum, file.url);
                    window.close();
                }
            }).elfinder('instance');
        });
    </script>
</head>
<body style="margin: 0 !important;">
<!-- Element where elFinder will be created (REQUIRED) -->
<div id="elfinder"></div>
</body>
</html>
