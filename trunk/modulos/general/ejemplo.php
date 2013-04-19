<?php

require_once("./enviar_mensaje.php");

$db_type = 'postgres7'; //mysql, postgres7, sybase, oci8po See here for more: http://php.weblogs.com/adodb_manual#driverguide
if ($_SERVER["HTTP_HOST"]=="admin.coradir.com.ar")
	$db_host = 'localhost';
else
	$db_host = '192.168.1.50';
$db_user = 'projekt';
$db_password = 'propcp';
$db_name = 'Coradir2';
$db_schemas = array(	//Arreglo que contiene los nombres
	"bancos",			//de los esquemas en la base de datos
	"compras",			//para poder acceder a las tablas
	"general",			//sin tener que usar en nombre del
	"internet",			//esquema.
	"licitaciones",
	"ordenes",	   		// del modulo nuevo
	"mensajes",
	"permisos",
	"sistema"
);
$debug = FALSE;

require_once("../../lib/adodb/adodb.inc.php");
require_once("../../lib/adodb/adodb-pager.inc.php");

$db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
$result=$db->Execute("SET search_path=".join(",",$db_schemas)) or die($db->ErrorMsg());
$_ses_user_login="test";


enviar_mensaje("15:30","03/08/2003","Probando la funcion de mensajes","MCP","MTP","test");

?>