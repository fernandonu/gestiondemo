<?php
/*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2003/07/01 20:22:37 $
*/
include_once("../../config.php");
$mode = $_GET["mode"];
if ($mode == "usuarios") {
	include_once("usuarios_view.php");
   exit;
}
?>