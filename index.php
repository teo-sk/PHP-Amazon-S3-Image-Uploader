<?php
include ("./inc/init.php");

$exts = $config->getMimeTypes();
foreach ($exts as $i => $e) {
    $exts[$i] = "*." . $e;
}
$ext = implode(";", $exts);

$exts = $config->getMimeTypes();
foreach ($exts as $i => $e) {
    $exts[$i] = "." . strtoupper($e);
}
$extd = implode(", ", $exts);


$multi = $config->getAmount();
?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

        <title>Uploader</title>
        <meta name="description" content="Uploader">
        <meta name="author" content="Matej Teo Zilak">
        <link href="/js/uploadify/uploadify.css" type="text/css" rel="stylesheet" />

        <script type="text/javascript" src="/js/uploadify/swfobject.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js"></script>
        <script type="text/javascript" src="/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#file_upload').uploadify({
                    'uploader'  : '/js/uploadify/uploadify.swf',
                    'script'    : '/ajax/upload.php',
                    'cancelImg' : '/js/uploadify/cancel.png',
                    'folder'    : '/images',
                    'auto'      : true,
                    'fileExt'   : '<?php echo $ext; ?>',
                    'fileDesc'  : 'Image Files (<?php echo $extd; ?>)',
                    'multi'     : true,
                    'simUploadLimit' : <?php echo $multi; ?>,
                    'onInit'      : function() {
                        $('#responseUploadify').html("");
                    },
                    'onComplete'  : function(event, ID, fileObj, response, data){
                        $('#responseUploadify').append(response + "<br/>");
                    },
                    'onError'     : function (event,ID,fileObj,errorObj) {
                        $('#responseUploadify').append(errorObj.type + ' Error: ' + errorObj.info + "<br/>");
                    }

                });
            });
        </script>

    </head>

    <body>

        <div id="container">
            <header>
                <h1>Uploader</h1>
            </header>
            <div id="main" role="main">
                <input id="file_upload" name="file_upload" type="file" />
            </div>
            <footer>
                <div id="responseUploadify"></div>
            </footer>
        </div>
    </body>
</html>