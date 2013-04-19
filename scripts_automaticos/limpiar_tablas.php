<?
/*
AUTOR: Norberto
MODIFICADO POR:
$Author: nazabal $
$Revision: 1.1 $
$Date: 2006/07/21 15:40:33 $
*/


include("funciones_generales.php");

$sql = "DELETE FROM permisos.phpss_session";
$result = $db->Execute($sql) or die("Error borrando las sesiones\n");

$sql = "DELETE FROM permisos.phpss_svars";
$result = $db->Execute($sql) or die("Error borrando las variables de sesion\n");
?>