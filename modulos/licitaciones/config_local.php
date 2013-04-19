<?php
/*******************************************
 ** Headers para que el explorador no guarde
 ** las páginas en cache.
 *******************************************/

/*******************************************
 ** Colores del sistema.
 *******************************************/

$bgcolor1 = "#5090C0";		// Primer color de fondo
$bgcolor2 = "#D5D5D5";		// Segundo color de fondo
$bgcolor3 = "#E0E0E0";		// Tercer color de fondo
$bgcolor4 = "#FF0000";		// Color de fondo para tareas vencidas
$bgcolor  = "#d1c294";

/*******************************************
 ** Cantidad de items a mostrar por página.
 *******************************************/

$itemspp = 20;

/*******************************************
 ** Configuración de la base de datos.
 *******************************************/

$db_type = 'postgres7';				// Tipo de base de datos.
if ($_SERVER["HTTP_HOST"]=="admin.coradir.com.ar") {
	$db_host = 'localhost';			// Host de la página.
}
else {
	$db_host = '192.168.1.50';		// Host para desarrollo.
//	$db_host = '200.47.8.43';		// Host para desarrollo.
}
$db_user = 'projekt';				// Usuario.
$db_password = 'propcp';			// Contraseña.
$db_name = 'gestion';				// Nombre de la base de datos.
// Arreglo que contiene los nombres de los esquemas en la
// base de datos para poder acceder a las tablas sin tener
// que usar en nombre del esquema.
$db_schemas = array(
	"bancos",
	"compras",
	"general",
	"internet",
	"licitaciones",
	"ordenes",
	"mensajes",
	"permisos",
	"sistema"
);
$db_debug = FALSE;					// Debugger de las consultas.

/*******************************************
 ** Limite de tiempo de inactividad para la
 ** expiración de la sesión (en minutos).
 *******************************************/

$session_timeout = 30;

/*******************************************
 ** Constantes para usar en los include/require
 ** (Directorios relativos al sistema)
 *******************************************/

define("ROOT_DIR", "../..");		       // Directorio raiz del proyecto GESTION
define("LIB_DIR", ROOT_DIR."/lib");		   // Librerias del sistema
define("MOD_DIR", ROOT_DIR."/modulos");		// Modulos del sistema
define("UPLOADS_DIR", ROOT_DIR."/uploads");	// Directorio para uploads

/*******************************************
 ** Variable $html_root que contiene la ruta
 ** a la raíz de la página.
 ** (Ruta relativa al URL de la página)
 *******************************************/

if (ereg("(/modulos)|(/lib)|(/index.php)",$_SERVER["SCRIPT_NAME"],$tmp)) {
	$tmp=explode($tmp[1].$tmp[2].$tmp[3],$_SERVER["SCRIPT_NAME"]);
	$html_root = $tmp[0];
}
unset($tmp);

require_once(LIB_DIR."/adodb/adodb.inc.php");
require_once(LIB_DIR."/adodb/adodb-pager.inc.php");

$db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
$result=$db->Execute("SET search_path=".join(",",$db_schemas)) or die($db->ErrorMsg());
unset($result);
$db->debug = $db_debug;

/***********************************
 ** Funciones de ambito general
 ***********************************/
function mix_string($string) {
	$split = 4;    // mezclar cada $split caracteres
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
function encode_link() {
	$args = func_num_args();
	if ($args == 2) {
		$link = func_get_arg(0);
		$p = func_get_arg(1);
	}
	elseif ($args == 1) {
		$p = func_get_arg(0);
	}
	//$link="", $p=array()
	$str = base64_encode(serialize($p));
	$string = mix_string($str);
//    echo "str1: $str<br>\n";
//    echo "str2: $string<br>\n";
	if(isset($link))
		return $link."?p=".$string;
	else
		return $string;
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
/* Funcion para cambiar el tipo de arreglo
   que retorna la consulta a la base de datos
   El paramentro puede ser "a" para que retorne
	un arreglo asociativo con los nombres de las
   columnas como indices, y "n" para que retorne
   un arreglo con los indices de forma de numeros
*/
function db_tipo_res($tipo="d") {
	global $db;
	switch ($tipo) {
	   case "a":   // tipo asociativo
		   $db->SetFetchMode(ADODB_FETCH_ASSOC);
		   break;
	   case "n":   // tipo numerico
		   $db->SetFetchMode(ADODB_FETCH_NUM);
		   break;
	   case "d":
		   $db->SetFetchMode(ADODB_FETCH_BOTH);
		   break;
   }
}

/*
 * Funcion para cambiar un color por otro alternativo
 * cuando los colores son parecidos o no contrastan mucho.
 * los parametros son de la forma: #ffffff
*/
function contraste($fondo, $frente, $reemplazo) {
	$brillo = 125;
   $diferencia = 400;
	$bg = ereg_replace("#","",$fondo);
	$fg = ereg_replace("#","",$frente);
	$bg_r = hexdec(substr($bg,0,2));
	$bg_g = hexdec(substr($bg,2,2));
	$bg_b = hexdec(substr($bg,4,2));
	$fg_r = hexdec(substr($fg,0,2));
	$fg_g = hexdec(substr($fg,2,2));
	$fg_b = hexdec(substr($fg,4,2));
	$bri_bg = (($bg_r * 299) + ($bg_g * 587) + ($bg_b * 114)) / 1000;
	$bri_fg = (($fg_r * 299) + ($fg_g * 587) + ($fg_b * 114)) / 1000;
	$dif = max(($fg_r - $bg_r),($bg_r - $bg_r)) + max(($fg_g - $bg_g),($bg_g - $fg_g)) + max(($fg_b - $bg_b),($bg_b - $fg_b));
	if(intval($bri_bg - $bri_fg) > $brillo or $dif > $diferencia) {
   	return $frente;
   }
   else {
   	return $reemplazo;
   }
}
/***************
@sql es parte de la busqueda
@filtro es un arreglo ver /modulos/licitaciones/licitaciones_view
@link_pagina tiene los parametros para invocarse nuevamente o que dependen de la
 pagina a invocar
@where_extra contiene la condicion de busqueda WHERE ....

********/
function form_busqueda($sql,$orden,$filtro,$link_pagina,$where_extra="") {
		global $bgcolor2,$page,$filter,$keyword,$sort,$up;
		global $itemspp,$db,$parametros;
//		$sort = $parametros["sort"] or $sort = "default";
//		if ($parametros["up"] == "0") {
		if ($up == "0") {
				$up = $parametros["up"];
				$direction="DESC";
				$up2 = "1";
		}
		else {
				$up = "1";
				$direction = "ASC";
				$up2 = "0";
		}

		if ($sort == "default") { $sort = $orden[$sort]; }
		$tmp=es_numero($keyword);
		echo "<b>Buscar:&nbsp;</b><input type='text' name='keyword' value='$keyword' size=20 maxlength=20>\n";
		echo "<b>&nbsp;en:&nbsp;<b><select id='filter' name='filter'>&nbsp;\n";
		echo "<option value='all'";
		if (!$filter) echo " selected";
		echo ">Todos los campos\n";
		while (list($key, $val) = each($filtro)) {
				echo "<option value='$key'";
				if ($filter == "$key") echo " selected";
				echo ">$val\n";
		}
		echo "</select>\n";

		if ($keyword) {
				$where = " WHERE ";
				if ($filter == "all" or !$filter) {
						$where_arr = array();
						$where .= "(";
						reset($filtro);
						while (list($key, $val) = each($filtro)) {
								$where_arr[] = "$key ilike '%$keyword%'";
						}
						$where .= implode(" or ", $where_arr);
						$where .= ")";
				}
				else {
						$where .= "$filter ilike '%$keyword%'";
				}
		}

		$sql .= " $where";
		if ($where_extra != "") {
				if ($where != "") $sql .= " AND";
				else $sql .= " WHERE";
				$sql .= " $where_extra";
		}

		$sql_cont = eregi_replace("^SELECT(.*)FROM", "SELECT COUNT(*) AS total FROM", $sql);
		$tipo_res = db_tipo_res();
		$result = $db->Execute($sql_cont) or Error($db->ErrorMsg());
		$total = $result->fields[0];
//        $total = $row["total"];

		$sql .= " ORDER BY ".$orden[$sort]." $direction LIMIT $itemspp OFFSET ".($page * $itemspp);

		$page_n = $page + 1;
		$page_p = $page - 1;
		$link_pagina_p = "";
		$link_pagina_n = "";
		$link_pagina["sort"] = $sort;
		$link_pagina["up"] = $up;
		$link_pagina["keyword"] = $keyword;
		$link_pagina["filter"] = $filter;
		if ($page > 0) {
			$link_pagina["page"] = $page_p;
			$link_pagina_p = "<a id=ma title='Página anterior' href='".encode_link($_SERVER["SCRIPT_NAME"],$link_pagina)."'><<</a>";
		}
		$sum=0;
		if (($total % $itemspp)>0) $sum=1;
		$link_pagina_num = "&nbsp;&nbsp;Página&nbsp;".($page+1)."&nbsp;de&nbsp;". (intval($total/$itemspp)+$sum) . "&nbsp;&nbsp;";
		if ($total > $page_n*$itemspp) {
			$link_pagina["page"] = $page_n;
			$link_pagina_n = "<a id=ma title='Página siguiente' href='".encode_link($_SERVER["SCRIPT_NAME"],$link_pagina)."'>>></a>";
		}
		if ($total > 0 and $total > $itemspp) {
			$link_pagina_ret = $link_pagina_p.$link_pagina_num.$link_pagina_n;
		}
		else {
			$link_pagina_ret = "";
		}

		return array($sql,$total,$link_pagina_ret,$up2);
}
/***RETORNO*****
@sql es la busqueda para ejecutar directamente
@total es la cantidad de entradas
@link_pagina contiene la parte << Pagina X de N >> con los links y todo
@up2 contiene el 

****************/



function Error($msg,$num="") {
	echo "<center><font size=4 color=#FF0000>Error $num: $msg</font><br></center>\n";
}

function link_calendario($control_pos, $control_dat="") {
	global $html_root;
	if ($control_dat == "") {
		$control_dat = $control_pos;
	}
	echo "<img src=$html_root/imagenes/cal.gif border=0 align=middle style='cursor:hand;' alt='Haga click aqui para\nseleccionar la fecha'  onClick=\"javascript:popUpCalendar($control_pos, $control_dat, 'dd/mm/yyyy');\">";
}

function Aviso($msg) {
	echo "<br><center><font size=4><b>$msg</b></font></center><br>\n";
}

/**
 * @return string
 * @param fecha_db string
 * @desc Convierte una fecha de la forma AAAA-MM-DD
 *       a la forma DD/MM/AAAA
 */
function Fecha($fecha_db) {
		$m = substr($fecha_db,5,2);
		$d = substr($fecha_db,8,2);
		$a = substr($fecha_db,0,4);
		if (is_numeric($d) && is_numeric($m) && is_numeric($a)) {
				return "$d/$m/$a";
		}
		else {
				return "";
		}
}

/**
 * @return string
 * @param fecha string
 * @desc Convierte una fecha de la forma DD/MM/AAAA
 *       a la forma AAAA-MM-DD
 */
function Fecha_db($fecha) {
		list($d,$m,$a) = explode("/",$fecha);
		return "$a-$m-$d";
}

/**
 * @return 1 o 0
 * @param fecha date
 * @desc Devuelve 1 si es fecha y 0 si no lo es.
 */
function FechaOk($fecha) {
	list($dia,$mes,$anio)=split("-", $fecha);
	if (($dia >= 1) and ($dia <= 31) and ($mes >= 1) and ($mes <= 12)) {
		return 1;
	}
	else { return 0; }
}

/**
 * @return date
 * @param fecha date
 * @desc Convierte una fecha del formato dd-mm-aaaa al
 *       formato aaaa-mm-dd que usa la base de datos.
 */
function ConvFecha($fecha) {
	list($dia,$mes,$anio)=split("-", $fecha);
	return "$anio-$mes-$dia";
}

/**
 * @return int
 * @param fecha date
 * @desc Compara la fecha $fecha con la fecha actual.
 *       Retorna:
 *               0 si $fecha es mayor de 7 dias.
 *               1 si $fecha esta entre 0 y 7 dias.
 *               2 si $fecha es anterior a la fecha actual.
 */
function check_fecha($fecha) {
	$fecha2=strtotime($fecha);
	$num1=($fecha2-intval(time()))/60/60/24;
//    $res=0;
	if ($num1 > 7) {
	   $res=0;
    } elseif ($num1>=0 and $num1<=7) {
       $res=1;
    } else {
	   $res=2;
    }
	return($res);
}

function html_out($outstr){
  $string=$outstr;
  if ($string <> "") {
	$string=ereg_replace("'","&#39;",htmlspecialchars($string));
	$string=ereg_replace("\n","<br>",htmlspecialchars($string));
  }
  return $string;
}

// the same specialy for hidden form fields and select field option values (uev -> UrlEncodedValues)
//function uev_out($outstr){return ereg_replace("'","&#39;",htmlspecialchars(urlencode($outstr)));}

function tr_tag ($dblclick,$extra="") {
  global $cnr, $bgcolor1, $bgcolor2;
  if (($cnr/2) == round($cnr/2)) { $color = "$bgcolor1"; $cnr++;}
  else { $color = "$bgcolor2"; $cnr++; }
  $tr_hover_on = "onmouseover=\"this.style.backgroundColor = '#ffffff'\" onmouseout=\"this.style.backgroundColor = '$color'\" onClick=\"location.href = '$dblclick'\"";
  echo "<tr style='cursor: hand;' bgcolor=$color $tr_hover_on $extra>\n";
}

function formato_money($num) {
	return number_format($num, 2, ',', '.');
}

function es_numero(&$num) {
	if (strstr($num,",")) {
		$num = ereg_replace("\.","",$num);
		$num = ereg_replace(",",".",$num);
	}
	return is_numeric($num);
}
/**
 * @return void
 * @param hora_venc hora que vence el mensaje Ej: 18:30
 * @param fecha_venc fecha que vence el mensaje dia/mes/año
 * @param mensaje motivo del mensaje
 * @param tipo1 tipo de mensaje Ej: Licitaciones, entonces LIC (Ver tabla tipo_de_mensaje)
 * @param tipo2 segundo tipo del mensaje Ej: Nueva orden necesita control y aprobacion, entonces EDC
 * @param para destinatario del mensaje
 * @desc permite enviar mensajes entre usuarios (en carpeta general hay un ejemplo)
 */
function enviar_mensaje($hora_venc,$fecha_venc,$mensaje,$tipo1,$tipo2,$para) {
	global $db,$_ses_login;
	$hora_venc.=":00";
	$fecha_venc=Fecha_db($fecha_venc);
	list($h,$m,$s)= explode(":",$hora_venc);
	if(!(is_numeric($h) && is_numeric($m)))
		$hora='00:00:00';
	$fecha_venc=$fecha_venc.' '.$hora_venc;
	$finicio=date("Y-m-d H:i:s");
	$sql="select nombre,apellido from usuarios where login='$_ses_login';";
	$result=$db->Execute($sql) or die($db->ErrorMsg());
	$user=$result->fields["nombre"]." ".$result->fields["apellido"];
	$ssql_tit="select titulo from tipo_de_mensaje where tipo1='$tipo1' and tipo2='$tipo2'";
	$result1=$db->Execute($ssql_tit) or die($db->ErrorMsg());
	$tit=$result1->fields[0].' '.$user;
	$ssql_ins="insert into mensajes (tipo1,tipo2,numero,usuario_origen,comentario,";
	$ssql_ins.=" usuario_destino,fecha_entrega,fecha_vencimiento,nro_orden,recibido,terminado,desestimado,";
	$ssql_ins.="titulo) values ('$tipo1','$tipo2',1,'$_ses_login','$mensaje','$para','$finicio', '$fecha_venc',1,false,false,false,'$tit')";
	$db->Execute($ssql_ins) or die($db->ErrorMsg());
}

/*******************************************
 ** Variables Utiles
 *******************************************/

// Tamaño máximo de los archivos a subir
$max_file_size = get_cfg_var("upload_max_filesize");  // Por defecto deberia se 5 MB

// Para usar con los resultados boolean de la base de datos
$sino=array(
	"0" => "No",
	"1" => "Sí"
);
// Para el formato de fecha
$dia_semana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

// El tipo de resultado debe ser n para que funcione la
// libreria phpss
db_tipo_res("d");

$GLOBALS["parametros"] = decode_link($_GET["p"]);

function verificar_permisos() {
	global $permisos,$html_root,$bgcolor3,$_ses_login;
	if (ereg("/modulos",$_SERVER["SCRIPT_NAME"])) {
		$item = array();
		if (file_exists("config.php")) {
			require_once("config.php");
		}
		$tmp = explode("/modulos/",$_SERVER["SCRIPT_NAME"]);
		list($modulo,$pagina) = explode("/",$tmp[1],2);
		$pagina=ereg_replace("\.php","",$pagina);
		$padre = "inicio";
		foreach($item as $item_check) {
			if ($item_check["nombre"] == $pagina) {
				if ($item_check["padre"]) {
					$padre = $item_check["padre"];
				}
				break;
			}
		}
//		echo "login=".$_ses_login."<br>pagina=".$pagina."<br>padre=".$padre;
		
		if (!permisos_check($padre,$pagina)) {
			echo "<html><head><link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>\n";
			echo "</head><body bgcolor=\"$bgcolor3\">\n";
			echo "<table width=50% height=100% border=2 align=center cellpadding=5 cellspacing=5 bordercolor=$bgcolor3>";
			echo "<tr><td height=50%>&nbsp;</td></tr>";
			echo "<tr><td align=center bordercolor=#FF0000 bgcolor=#FFFFFF>";
			echo "<table border=0 width=100%>";
			echo "<tr><td width=15% align=center valign=middle>";
			echo "<img src=$html_root/imagenes/error.gif alt='ERROR' border=0>";
			echo "</td><td width=85% align=center valign=middle>";
			echo "<font size=5 color=#000000 face='Verdana, Arial, Helvetica, sans-serif'><b>";
			echo "USTED NO TIENE PERMISO PARA VER LA PAGINA SOLICITADA</b></font>";
			echo "</td></tr></table>";
			echo "</td></tr>";
			echo "<tr><td height=50%>&nbsp;</td></tr>";
			echo "</table></body></html>\n";
			exit;
		}
	}
}
define("lib_included","1");

function cargar_feriados() {
global $db;
	$sql = "select * from feriados";
	$result = $db->Execute($sql) or die($db->ErrorMsg());
	while (!$result->EOF) {
		echo "addHoliday(".$result->fields["dia"].",".$result->fields["mes"].",0,'".$result->fields["descripcion"]."');\n";
		$result->MoveNext();
	}
}

function cargar_calendario() {
	global $html_root;
	echo "<script language='javascript' src='$html_root/lib/popcalendar.js'></script>\n";
	echo "<script language='javascript'>".cargar_feriados()."</script>\n";
}

function mkdirs($strPath, $mode = "0700") {
//	global $server_os;
	if (SERVER_OS == "windows") {
		$strPath = ereg_replace("/","\\",$strPath);
	}
	if (is_dir($strPath)) return true;
	$pStrPath = dirname($strPath);
	if (!mkdirs($pStrPath, $mode)) return false;
	return mkdir($strPath);
}
// Chequea la version del sistema operativo en el que se esta
// ejecutando la pagina y define la variable $server_os
if (ereg("Win32",$_SERVER["SERVER_SOFTWARE"]))
	define("SERVER_OS", "windows");
else
	define("SERVER_OS", "linux");

/*******************************************
 ** Variable $html_header contiene el
 ** encabezamiento de la página.
 *******************************************/

$html_header = "
<html>
  <head>
	<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>

    <script language='javascript'>
	var winW=window.screen.Width;
	var valor=(winW*25)/100;
	var nombre1;
	var titulo1;
	function insertar(){
 	ventana.document.all.titulo.innerText=titulo1;
 	ventana.frames.frame1.location=nombre1;
 	}

	function abrir_ventana(nombre,titulo){
	var winH=window.screen.availHeight;
	nombre1=nombre;
	titulo1=titulo;
	if ((typeof(ventana) == 'undefined') || ventana.closed) {
	ventana=window.open('$html_root/modulos/ayuda/TITULOS.htm','ventana_ayuda','width=' + valor + ',height=' + (winH)+ ', left=' + (winW - valor ) +'  ,top=0, scrollbars=0 ');
	window.top.resizeBy(-valor,0);
	}
	else {  ventana.focus();
    }

	setTimeout('insertar()',400);
	}
	</script>
  </head>
  <body bgcolor=\"$bgcolor3\" onload='document.focus();'>
";

/*******************************************
 ** Variable $html_footer contiene el
 ** pie de la página.
 *******************************************/

$html_footer = "
  </body>
</html>
";

	
?>