<?php
define('ROOTPATH', __DIR__);
file_put_contents(ROOTPATH.'/uploads/'.$_GET["filename"], file_get_contents('php://input'));
?>