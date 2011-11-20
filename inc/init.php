<?php

define( "BASEPATH" ,         dirname(__FILE__) . "/.." );
define('DEV_MODE' ,                                   1);
$error_reporting = error_reporting(DEV_MODE ? E_ALL : 0);

//function __autoload( $class_name ) {
//    include BASEPATH . "/lib/" . $class_name . '.class.php';
//}
//cannot use autoload due to conflicts with amazon class autoloader. ^^^can delete this

require_once BASEPATH . "/lib/Image.class.php";
require_once BASEPATH . "/lib/Config.class.php";
require_once BASEPATH . "/lib/Uploader.class.php";
require_once BASEPATH . "/lib/amazon_sdk/sdk.class.php";

$config = Config::getInstance();
$config->setUploadDir( BASEPATH . "/images" ); //path for images uploaded
$config->setBucketName( "yourbucketname" );
$config->setAmount( 250 );  //maximum paralell uploads
$config->setMimeTypes( array( "jpg" , "gif" , "png" ) ); //allowed extensions
$config->setDimensions( array( "300x300" , "640x480" ) );   //resize to these sizes



?>