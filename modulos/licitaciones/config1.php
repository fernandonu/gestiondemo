<?php
/*
$Author: fernando $
$Revision: 1.1 $
$Date: 2003/07/29 21:10:59 $
*/

//$session_time_limit = 30;
define("ROOT_DIR", dirname(__FILE__));
define("LIB_DIR", "../../lib");
define("MOD_DIR", ROOT_DIR."../modulos");

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
//	"protocolo",
	"sistema",
	"public"
);
$debug = FALSE;

require_once(LIB_DIR."/adodb/adodb.inc.php");
require_once(LIB_DIR."/adodb/adodb-pager.inc.php");

$db = &ADONewConnection($db_type);
//$db->SetFetchMode(ADODB_FETCH_ASSOC);
$db->SetFetchMode(ADODB_FETCH_NUM);
$db->Connect($db_host, $db_user, $db_password, $db_name);
$result=&$db->Execute("SET search_path=".join(",",$db_schemas)) or $db->ErrorMsg();
unset($result);
$db->debug = $debug;

// load phpSecureSite
require(LIB_DIR."/phpss/phpss.php");

$sino=array(
	"0" => "No",
	"1" => "Sí"
);

/**************************************
 **  Configuracion global de colores
 **************************************/

$bgcolor1  = "#5090C0";      // Primer color de fondo
$bgcolor2 = "#D5D5D5";       // Segundo color de fondo
//$bgcolor2 = "#FFFFF5";       // Segundo color de fondo
//$bgcolor2 = "#5090C0";       // Segundo color de fondo
$bgcolor3 = "#E0E0E0";       // Tercer color de fondo
//$bgcolor3 = "#FFFFF5";       // Tercer color de fondo
$bgcolor4 = "#FF0000";      // Color de fondo para tareas vencidas
//$bgcolor = "#FFFFCC";
$bgcolor = "#d1c294";
//$bgcolor2 = "#cccccc";
$colorOk="#7CB656"; //"#CCFFFF"; //"#66CC66";
$color7dias="#FFCC00";
$colorVencido="#FF0000";
$colorBorrado="#FFFFFF";
$colorCerrado="#CCFFFF";

$itemspp = 20;			// Items por pagina por defecto

// Para el formato de fecha
$dia_semana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

function db_tipo_res($tipo) {
	global $db;
	switch ($tipo) {
	case "a":   // tipo asociativo
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		   break;
	   case "n":   // tipo numerico
			$db->SetFetchMode(ADODB_FETCH_NUM);
		   break;
	   case "default":
		$db->SetFetchMode(ADODB_FETCH_NUM);
		   break;
   }
}

function mix_string($string) {
    $split = 8;    // mezclar cada $split caracteres
    $str = str_replace("=","",$string);
    $string = "";
    $str_tmp = explode(":",chunk_split($str,$split,":"));
    for ($i=0;$i<count($str_tmp);$i+=2) {
         if (strlen($str_tmp[$i+1]) != $split) {
             $string .= $str_tmp[$i] . $str_tmp[$i+1];
         }
         else {
               $string .= $str_tmp[$i+1] . $str_tmp[$i];
		 }
    }
    return $string;
}

function encode_link($link, $p=array()) {
    $str = base64_encode(serialize($p));
    $string = mix_string($str);
//    echo "str1: $str<br>\n";
//    echo "str2: $string<br>\n";
    return $link."?p=".$string;
}


function decode_link($link) {
    $str = mix_string($link);
//    echo "link: $link\n";
//    echo "str1: $str\n";
    $cant = strlen($str)%4;
    if ($cant > 0) $cant = 4 - $cant;
    for ($i=0;$i < $cant;$i++) {
         $str .= "=";
    }
//    echo "link: $link<br>\n";
    return unserialize(base64_decode($str));
}
$GLOBALS["parametros"] = decode_link($_GET["p"]);

?>
