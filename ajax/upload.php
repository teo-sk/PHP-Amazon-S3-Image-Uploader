<?php 

include ("../inc/init.php");

$uid = 777;


//usage of uploader class - this simple :)

$uploader = new Uploader($uid);
$uploader->upload();

?>

