<?php
/*
$Author: fernando $
$Revision: 1.478 $
$Date: 2006/11/27 20:59:12 $
*/


require_once(LIB_DIR."/adodb/adodb.inc.php");
require_once(LIB_DIR."/adodb/adodb-pager.inc.php");

define("TIEMPO_INICIO", getmicrotime());
// Chequea la version del sistema operativo en el que se esta
// ejecutando la pagina y define la constante SERVER_OS
if (ereg("Win32",$_SERVER["SERVER_SOFTWARE"]) ||
    ereg("Microsoft",$_SERVER["SERVER_SOFTWARE"]))
	define("SERVER_OS", "windows");
else
	define("SERVER_OS", "linux");

$db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
$db->cacheSecs = 3600;
$result=$db->Execute("SET search_path=".join(",",$db_schemas)) or die($db->ErrorMsg());
unset($result);
$db->debug = $db_debug;

// load phpSecureSite
require(LIB_DIR."/phpss/phpss.php");

// libreria para administrar los permisos
require_once LIB_DIR."/gacl.class.php";
$permisos = new gacl(array("items_per_page"=>500,"max_search_return_items"=>500));

/***********************************
 ** Funciones de ambito general
 ***********************************/

function form_login() {
	global $html_root;
	echo "<html><head><script language=javascript>\n";
	echo "if(parent!=null && parent!=self) { parent.location='$html_root/login.php'; }\n";
	echo "else { window.location='$html_root/login.php'; }\n";
	echo "</script></head></html>";
}

// Funcion que verifica el estado de la sesión
function Autenticar() {
//    global $bgcolor1;
	$status = phpss_auth();

	switch($status) {
		case PHPSS_AUTH_ALLOW:
			 break;
		case PHPSS_AUTH_NOCOOKIE:
//			   Error("Necesita iniciar sesión para poder ver esta página");
//			 include_once(ROOT_DIR."/login.php");
			 form_login();
			 exit;
			 break;
		case PHPSS_AUTH_INVKEY:
			 phpss_logout();
//			 Error("Usted está usando una sesión no válida");
//			 include_once(ROOT_DIR."/login.php");
			 form_login();
			 exit;
			 break;
		case PHPSS_AUTH_IPACCESS_DENY:
//			 Error("Usted no tiene permitido el acceso desde su dirección IP");
//			 include_once(ROOT_DIR."/login.php");
			 form_login();
			 exit;
			 break;
		case PHPSS_AUTH_ACLDENY:
			 Error("Usted no tiene permiso para ver esta página");
			 exit;
			 break;
		case PHPSS_AUTH_HIJACK:
//			 Error("Su dirección IP es diferente a la que uso para iniciar sesión");
//			 include_once(ROOT_DIR."/login.php");
			 form_login();
			 exit;
			 break;
		case PHPSS_AUTH_TIMEOUT:
//			 if ($parametros["mode"] != "logout") {
//				 Error("Su sesión ha expirado, por favor vuelva a iniciar sesión");
//			 }
//			 include_once(ROOT_DIR."/login.php");
			 form_login();
			 exit;
			 break;
	}

}

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
	return str_replace(" ","+",$string);
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
	$str = comprimir_variable($p);
	$string = mix_string($str);
	if(isset($link))
		return $link."?p=".$string;
	else
		return $string;
}
function decode_link($link) {
    $str = mix_string($link);
	$cant = strlen($str)%4;
    if ($cant > 0) $cant = 4 - $cant;
    for ($i=0;$i < $cant;$i++) {
		 $str .= "=";
    }
    return descomprimir_variable($str);
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
/*
 * @return array
 * @param sql string
 * @param orden array
 * @param filtro array
 * @param link_pagina string
 * @param where_extra string (opcional)
 * @desc Esta funcion genera el formulario de busqueda y divide el resultado
         de una consulta sql por paginas
         Ejemplo:
		 // variables que contienen los datos actuales de la busqueda
         $page = $_GET["page"] or $page = 0;                                                                //pagina actual
				 $filter = $_POST["filter"] or $filter = $_GET["filter"];                //campo por el que se esta filtrando
				 $keyword = $_POST["keyword"] or $keyword = $_GET["keyword"];        //palabra clave

                 $orden = array(                                        //campos que voy a mostar
                        "default" => "2",                                //campo por defecto
                        "1" => "IdProv",
                        "2" => "Proveedor"
                 );

                 $filtro = array(
						"Proveedor"                => "Proveedor",                //elementos en donde se van a hacer las busquedas
                        "Contacto"                => "Contacto",                //el formato del aarreglo es:
                        "Mail"                        => "Mail"                        //$filtro=array("nombre de la columna en la base de datos" => "nombre a mostrar en el formulario");
                 );
                 //sentencia sql que sin ninguna condicion
				 $sql_tmp = "SELECT IdProv,Proveedor,Contacto,Mail,Teléfono,Comentarios FROM bancos.proveedores";
				 //prefijo para los links de paginas siguiente y anterior
                 $link_tmp = "<a id=ma href='bancos.php?mode=$mode&cmd=$cmd";
                 //condiciones extras de la consulta
				 $where_tmp = "";

				 list($sql,$total_Prov,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp);

*/
function form_busqueda($sql,$orden,$filtro,$link_pagina,$where_extra="",$contar=0,$sumas="",$ignorar="",$seleccion="") {

		global $bgcolor2,$page,$filter,$keyword,$sort,$up;
		global $itemspp,$parametros;
		if ($_GET['page'])
			$page=($_GET['page'] > 0)?$_GET['page']-1:0;//controlo que no pongan valores negativos

		if ($up == "") {
			$up = $orden["default_up"];
		}
		if ($up == "") {
			$up = "1";
		}
		if ($up == "0") {
//				$up = $parametros["up"];
				$direction="DESC";
				$up2 = "1";
		}
		else {
				$up = "1";
				$direction = "ASC";
				$up2 = "0";
		}
		if ($sort == "") $sort = "default";
		if ($sort == "default") { $sort = $orden["default"]; }
		if ($orden[$sort] == "") { $sort = $orden["default"]; }
		if ($filtro[$filter] == "") { $filter = "all"; }
		$tmp=es_numero($keyword);
		echo "<input type=hidden name=form_busqueda value=1>";
		echo "<b>Buscar:&nbsp;</b><input type='text' name='keyword' value='$keyword' size=20 maxlength=150>\n";
		echo "<b>&nbsp;en:&nbsp;</b><select name='filter'>&nbsp;\n";
		echo "<option value='all'";
		if (!$filter or $filtro[$filter] == "") echo " selected";
		echo ">Todos los campos\n";
		while (list($key, $val) = each($filtro)) {
				echo "<option value='$key'";
				if ($filter == "$key") echo " selected";
				echo ">$val\n";
		}
		echo "</select>\n";

		//print_r($ignore);


		if ($keyword) {

				$where = "\nWHERE ";
				if ($filter == "all" or !$filter) {
						$where_arr = array();
						if (is_array($ignorar)) $where .= "((";
						else $where .= "(";
						reset($filtro);
						while (list($key, $val) = each($filtro)) {
							    if (is_array($ignorar) && !in_array($key,$ignorar))
							     $where_arr[] = "$key ILIKE '%$keyword%'";
							    if (!is_array($ignorar)) $where_arr[] = "$key ILIKE '%$keyword%'";

						}

						$where .= implode(" OR ", $where_arr);
						$where .= ")";

						if (is_array($seleccion)){
						while (list($key, $val) = each($seleccion)) {
						$where .= " OR ($val)";
						}
						$where .= ")";
						}
				}
				else {if (!is_array($ignorar)) $where .= "$filter ILIKE '%$keyword%'";
					  elseif (is_array($ignorar) && !in_array($filter,$ignorar))
						$where .= "$filter ILIKE '%$keyword%'";
						else $where .= " (".$seleccion[$filter].")";
				}
		}

		$sql .= " $where";
		if ($where_extra != "") {
				if ($where != "")
				{
					 //si no tiene un group by al principio
					 if (!eregi("^group by.*|^ group by.*",$where_extra))
						 $sql .= "\nAND";

				}
				else
				{
					 //si no tiene un group by al principio
					 if (!eregi("^group by.*|^ group by.*",$where_extra))
						 $sql .= "\nWHERE";

				}
				$sql .= " $where_extra";
		}
        //echo $sumas." AAAAAAAAAAAAAAAA<br>";
		if ("$contar"=="buscar") {
			$tipo_res = db_tipo_res("a");
//			$result = sql($sql,"CONTAR") or reportar_error($sql,__FILE__,__LINE__);
			$result = sql($sql,"CONTAR") or fin_pagina();
			$tipo_res = db_tipo_res();
			$total = $result->RecordCount();

			//Sumas de campos de montos caso en que usa la consulta general
			$res_sumas='';

			if (	$sumas!='' &&
					substr_count($sql,$sumas["campo"])>0 &&//si el campo esta definido
					is_array($sumas["mask"])//mascara para configurar el resultado
					) {
						$count_mask = count($sumas["mask"]);//tamaño de la mascara
						if ($count_mask==0) {//caso en que suma solo cantidades
							$acum=0;
							for($i=0;$i<$total;$i++){//for
								$acum+=$result->fields[$sumas["campo"]];
								$result->MoveNext();
							}	//fin de for
							$res_sumas ="$acum";
						}//fin de caso suma cantidades solam.
						elseif(substr_count($sql,$sumas["moneda"])>0) {//otro caso //si la moneda esta definida
							$sql_moneda="Select simbolo,id_moneda from moneda";
							$res_moneda=sql($sql_moneda,"Imposible obtener el listado de moneda") or fin_pagina();
							for($i;$i<$res_moneda->RecordCount();$i++){
								$moneda[$res_moneda->fields["id_moneda"]]=$res_moneda->fields["simbolo"];
								$res_moneda->MoveNext();
							}
								//print_r($moneda);
							for($i=0;$i<$count_mask;$i++) {//preparando el acumulador
								$acum[$i]=0;
							}//fin del for

							for($i=0;$i<$total;$i++){//for
								$pos = array_search($moneda[$result->fields[$sumas["moneda"]]],$sumas["mask"]);
								if (is_int($pos))
									$acum[$pos]+=$result->fields[$sumas["campo"]];
								$result->MoveNext();
							}	//fin de for
							$res_sumas = "";
							for($i=0;$i<$count_mask;$i++) { //preparando el resultado
								$res_sumas.=$sumas["mask"][$i].formato_money($acum[$i])." ";
							}//fin del for

						}//fin otro caso

					}
		}
		elseif($contar)
		{
//		$sql_cont = eregi_replace("^SELECT(.*)FROM", "SELECT COUNT(*) AS total FROM", $sql);
//		$sql_cont = eregi_replace("GROUP BY .*", "", $sql_cont);
			$tipo_res = db_tipo_res("n");
//		$result = $db->Execute($sql_cont) or die($db->ErrorMsg());
			$result = sql($contar,"CONTAR") or fin_pagina();
//		$total = $result->fields[0];
			$tipo_res = db_tipo_res();
			$total = $result->fields[0];


			if (is_string($sumas) && $sumas!="") {
				$tipo_res = db_tipo_res("n");
				$result = sql($sumas,"SUMAS") or fin_pagina();
				$tipo_res = db_tipo_res();
				$res_sumas="";
				for ($i=0;$i<$result->RecordCount();$i++){
					$res_sumas.=$result->fields[0]." ".formato_money($result->fields[1])." ";
					$result->MoveNext();
				}

			}
		}
		else {
			$total = 0;
			$res_sumas="";
		}

// $total=99;
		if ($sort != "") {
		    $sql .= "\nORDER BY ".$orden[$sort]." $direction";
		}

		$sql .= "\nLIMIT $itemspp OFFSET ".($page * $itemspp);

		$page_n = $page + 1;
		$page_p = $page - 1;
		$link_pagina_p = "";
		$link_pagina_n = "";
		if (!is_array($link_pagina)) $link_pagina = array();
//		$link_pagina["sort"] = $sort;
//		$link_pagina["up"] = $up;
//		$link_pagina["keyword"] = $keyword;
//		$link_pagina["filter"] = $filter;
		if ($page > 0) {
			$link_pagina["page"] = $page_p;
			$link_pagina_p = "<a title='Página anterior' href='".encode_link($_SERVER["SCRIPT_NAME"],$link_pagina)."'><<</a>";
		}
		$sum=0;
		if (($total % $itemspp)>0) $sum=1;

		$last_page=(intval($total/$itemspp)+$sum);
		$link_pagina_num = "&nbsp;&nbsp;Página&nbsp;<input type='text' value=".($page+1)." name='page' size=2 style='text-align:right;border:none' onkeypress=\" if ((show_alert=(window.event.keyCode==13)) && parseInt(this.value)>0 && parseInt(this.value)<= $last_page ) {location.href='".encode_link($_SERVER["SCRIPT_NAME"],$link_pagina). "&page='+parseInt(this.value);return false;} else if (show_alert) {alert('Por favor ingrese un número válido'); return false;} \" />&nbsp;de&nbsp;$last_page&nbsp;&nbsp;";
		if ($total > $page_n*$itemspp) {
			$link_pagina["page"] = $page_n;
			$link_pagina_n = "<a title='Página siguiente' href='".encode_link($_SERVER["SCRIPT_NAME"],$link_pagina)."'>>></a>";
		}
		if ($total > 0 and $total > $itemspp) {
			$link_pagina_ret = $link_pagina_p.$link_pagina_num.$link_pagina_n;
		}
		else {
			$link_pagina_ret = "";
		}

		return array($sql,$total,$link_pagina_ret,$up2,$res_sumas);
}

function Error($msg,$num="") {
	global $error;
	echo "<center><font size=4 color=#FF0000>Error $num: $msg</font><br></center>\n";
	$error = 1;
}

function link_calendario($control_pos, $control_dat="") {
	global $html_root;
	if ($control_dat == "") {
		$control_dat = $control_pos;
	}
	return "<img src=$html_root/imagenes/cal.gif border=0 align=middle style='cursor:hand;' alt='Haga click aqui para\nseleccionar la fecha'  onClick=\"javascript:popUpCalendar($control_pos, $control_dat, 'dd/mm/yyyy');\">";
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

function hora_ok($hora) {
    if ($hora) {
         $hora_arr = explode(":", $hora);
         if ( (is_numeric($hora_arr[0])) && ($hora_arr[0]>=0 && $hora_arr[0]<=23))
             $hora_apertura = $hora_arr[0];
         else
             return 0;
         if ( (is_numeric($hora_arr[1]))  && ($hora_arr[1]>=0 && $hora_arr[1]<=59) )
            $hora_apertura .= ":".$hora_arr[1];
        else
            return 0;
        if ( (is_numeric($hora_arr[2]))  && ($hora_arr[2]>=0 && $hora_arr[2]<=59))
            $hora_apertura .= ":".$hora_arr[2];
        else
           return 0;
    }

return $hora_apertura;

}


function Hora($hora_db) {
	if (ereg("([0-9]{2}:[0-9]{2}:[0-9]{2})",$hora_db,$hora))
		return $hora[0];
	else
		return "00:00:00";
}



/**
 * @return string
 * @param fecha string
 * @desc Convierte una fecha de la forma DD/MM/AAAA
 *       a la forma AAAA-MM-DD
 */

//funcion defectuosa
//cuidado
function Fecha_db($fecha) {
		if (strstr($fecha,"/"))
			list($d,$m,$a) = explode("/",$fecha);
		elseif (strstr($fecha,"-"))
			list($d,$m,$a) = explode("-",$fecha);
		else
			return "";
		return "$a-$m-$d";
}




/**
 * @return 1 o 0
 * @param fecha date
 * @desc Devuelve 1 si es fecha y 0 si no lo es.
 */
function FechaOk($fecha) {
	if (ereg("-",$fecha))
		list($dia,$mes,$anio)=split("-", $fecha);
	elseif (ereg("/",$fecha))
		list($dia,$mes,$anio)=split("/", $fecha);
	else
		return 0;
	return checkdate($mes,$dia,$anio);
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
// Manejo de div flotantes
/**
 * @Nombre inicio_barra
 * @param nombre String
 * @param titulo String
 * @param contenido String
 * @param color String
 * @param top integer
 * @param left integer
 * @param height integer
 * @param width integer
 * @param ocultar integer 0 o 1
 * @desc Inserta un div flotante
 *		 Si el top y left no son insertado,
 *		 El div flotante estara en la posicion
 *		 inferior central.
 **/
function inicio_barra($nombre,$titulo,$contenido,$height,$width,$top=null,$left=null,$color="#B7C7D0",$ocultar=1) {
	$he=$height-18;
	echo "<style type='text/css'>
		<!--
		#$nombre	{position: absolute;overflow: hidden; width: $width; height: $height;
			border: 2 outset black; margin: 5px;}
		#title		{background: #006699;padding: 0px; margin: 0px;}
		#inner		{background: $color;border: 2 inset white;overflow: auto; margin: 0px;width: 100%; height: $he;}
		-->
	</style>\n";


	echo "<div id='$nombre'>\n";
	echo "<div class='handle' handlefor='$nombre' id='title'>\n";
	echo "<table width=100% cellspacing=0 cellpadding=0 border=0>\n";
	echo "<tr>\n";
	echo "<td align=center width=90%>\n";
	echo "<font size=2 color='#cdcdcd'><b>$titulo</b></font>\n";
	echo "</td>\n";
	echo "<td align=right width=30%>\n";
	if ($ocultar==1) {
		echo "<img style='cursor: hand;' src='../../imagenes/dropdown2.gif' onClick='ocultar(this,\"$nombre\");'>\n";
		echo "<img style='cursor: hand;' src='../../imagenes/salir.gif' onClick='mini(this,\"$nombre\");'>\n";
	}
	echo "</td></tr></table></div>\n";
	echo "<div id='inner'";
	if ($color) echo " bgcolor=$color";
	echo ">\n";
	echo $contenido;
	echo "</div></div>\n";
	echo "<script>\n";
	//echo "$nombre.style.width=$width;\n";
	//echo "$nombre.style.height=$height;\n";
	if ($top==""){
		echo "$nombre.style.top=(document.body.clientHeight-$height)-5;\n";
		echo "$nombre.top=(document.body.clientHeight)-((document.body.clientHeight-$height)-5);\n";
	}
	else {
		echo "$nombre.style.top=$top;\n";
		echo "$nombre.top=(document.body.clientHeight-$top);\n";
	}
	if ($left=="")
		echo "$nombre.style.left=((document.body.clientWidth/2)-($width/2));\n";
	else
		echo "$nombre.style.left=$left;\n";
	//echo "alert($nombre.style.top);\n";
	echo "</script>\n";
}
// Fin de div flotantes
function html_out($outstr){
  $string=$outstr;
  if ($string <> "") {
	$string=ereg_replace("\"","&#34;",$string);
	$string=ereg_replace("'","&#39;",$string);
	$string=ereg_replace(">","&#62;",$string);
	$string=ereg_replace("<","&#60;",$string);
	$string=ereg_replace("\n","<br>",$string);
  }
  return $string;
}

// the same specialy for hidden form fields and select field option values (uev -> UrlEncodedValues)
//function uev_out($outstr){return ereg_replace("'","&#39;",htmlspecialchars(urlencode($outstr)));}


function atrib_tr($bgcolor_out_int='#B7C7D0'){
  global $bgcolor_over, $text_color_over, $text_color_out ;
  return "bgcolor=$bgcolor_out_int onmouseover=\"this.style.backgroundColor = '$bgcolor_over'; this.style.color = '$text_color_over'\" onmouseout=\"this.style.backgroundColor = '$bgcolor_out_int'; this.style.color = '$text_color_out'\"; style='cursor: hand;'";
         }

function tr_tag ($dblclick,$extra="",$bgcolor_out_int='#B7C7D0') {
  global $atrib_tr, $bgcolor_out, $cnr, $bgcolor1, $bgcolor2;
  if (($cnr/2) == round($cnr/2)) { $color = "$bgcolor1"; $cnr++;}
  else { $color = "$bgcolor2"; $cnr++; }
  if (!(strpos($dblclick,"target" )===false))
  {

	$t1=substr($dblclick,strpos($dblclick,"=")+1);
	$target=substr($t1,0,strpos($t1,";")).".";
	//; separa el target de la URL
	$dblclick=substr($dblclick,strpos($dblclick,";")+1);
  }
  $tr_hover_on = atrib_tr($bgcolor_out_int)." onClick=\"$target"."location.href ='$dblclick'\"";
  echo "<tr $tr_hover_on $extra>\n";
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
	$hora_venc.=":00";
	$fecha_venc=Fecha_db($fecha_venc);
	list($h,$m,$s)= explode(":",$hora_venc);
	if(!(is_numeric($h) && is_numeric($m)))
		$hora='00:00:00';
	$fecha_venc=$fecha_venc.' '.$hora_venc;
	$finicio=date("Y-m-d H:i:s");
	$sql="select nombre,apellido from usuarios where login='".$_ses_user['login'];
	$result=sql($sql) or die;
	$user=$result->fields["nombre"]." ".$result->fields["apellido"];
	$ssql_tit="select titulo from tipo_de_mensaje where tipo1='$tipo1' and tipo2='$tipo2'";
	$result1=sql($ssql_tit) or die;
	$tit=$result1->fields[0].' '.$user;
	$ssql_ins="insert into mensajes (tipo1,tipo2,numero,usuario_origen,comentario,";
	$ssql_ins.=" usuario_destino,fecha_entrega,fecha_vencimiento,nro_orden,recibido,terminado,desestimado,";
	$ssql_ins.="titulo) values ('$tipo1','$tipo2',1,'".$_ses_user['login']."','$mensaje','$para','$finicio', '$fecha_venc',1,false,false,false,'$tit')";
	sql($ssql_ins) or die;
}

function cargar_feriados() {
	global $_ses_feriados;
	$ret = "";
	foreach ($_ses_feriados as $fecha => $descripciones) {
		list($anio,$mes,$dia) = split("-",$fecha);
		foreach ($descripciones as $descripcion) {
			$ret .= "addHoliday($dia,$mes,$anio,'$descripcion');\n";
		}
	}
	return $ret;
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

function verificar_permisos() {
	global $html_root,$bgcolor3,$ouser,$parametros;
	if (ereg("/modulos",$_SERVER["SCRIPT_NAME"])) {
		$tmp = explode("/modulos/",$_SERVER["SCRIPT_NAME"]);
		list($modulo,$pagina) = explode("/",$tmp[1],2);
		$pagina=ereg_replace("\.php","",$pagina);
		$padre = $modulo;
//		echo "<br>tmp:".print_r($tmp);
//		echo "<br>padre:$padre ------- pagina:$pagina<br>";
//		echo "parametros:";print_r($parametros);echo("<br>");
		$i=0;
		while( $i < $ouser->permisos->length) {
			$keyname=$ouser->permisos[$i]->name;
			//si es un item con parametros
			if (ereg("(.*)(\?)(.*)",$keyname,$amenu))
			{
				//si NO es la pagina a checkear permisos
				if ($pagina != $amenu[1])
					$amenu[0]="";
				else
				{
					$menu=$amenu[1].".php";
					$extra2=split("&",$amenu[3]);
					foreach ($extra2 as $key => $value)
					{
						$tmp=split("=",$value,2);
						//si NO vienen los parametros requeridos del item
						if($_GET[$tmp[0]] != $tmp[1])
						{	
							$amenu[0]=""; //para que no checkee y no entre en el 1er if de abajo
							break; 
						}
					}
					unset($extra2);
				}
		 }

//			si es una pagina comun || una con parametros
			if ($keyname == $pagina || $keyname==$amenu[0]) 				
					break;
					
			$i++;
		}

		//si es una pagina comun -> controla con $pagina
		//sino es una pagina con parametros -> controlar con $amenu[0]
//		echo "permisos_check($padre,$pagina) && permisos_check($padre,{$amenu[0]}) {$_SERVER["SCRIPT_NAME"]}<br>";
		if (!permisos_check($padre,$pagina) && !permisos_check($padre,$amenu[0]))
		{
			echo "<html><head><link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>\n";
			echo "</head><body bgcolor=\"$bgcolor3\">\n";
            echo "<!-- Debug:\npagina=".$pagina."\npadre=".$padre."\n-->\n";
			echo "<table width='50%' height='100%' border=2 align=center cellpadding=5 cellspacing=5 bordercolor=$bgcolor3>";
			echo "<tr><td height='50%'>&nbsp;</td></tr>";
			echo "<tr><td align=center bordercolor=#FF0000 bgcolor=#FFFFFF>";
			echo "<table border=0 width='100%'>";
			echo "<tr><td width=15% align=center valign=middle>";
			echo "<img src=$html_root/imagenes/error.gif alt='ERROR' border=0>";
			echo "</td><td width=85% align=center valign=middle>";
			echo "<font size=5 color=#000000 face='Verdana, Arial, Helvetica, sans-serif'><b>";
			echo "USTED NO TIENE PERMISO PARA VER LA PAGINA SOLICITADA</b></font>";
			echo "</td></tr></table>";
			echo "</td></tr>";
			echo "<tr><td height='50%'>&nbsp;</td></tr>";
			echo "</table></body></html>\n";
			exit;
		}
	}
}

function cortar($text, $maxChars = 30, $splitter = '...') {
	$theReturn = $text;
	$lastSpace = false;

	// only do the rest if we're over the character limit
	if (strlen($text) > $maxChars)
	{
		$theReturn = substr($text, 0, $maxChars - 1);
		// add closing punctuation back in if found
		if (in_array(substr($text, $maxChars - 1, 1), array(' ', '.', '!', '?')))
		{
			$theReturn .= substr($text, $maxChars, 1);
		}
		else
		{
			// make room for splitter string and look for truncated words
			$theReturn = substr($theReturn, 0, $maxChars - strlen($splitter));
			$lastSpace = strrpos($theReturn, ' ');
			// Remove truncated words and trailing spaces
			if ($lastSpace !== false)
			{
				$theReturn = substr($theReturn, 0, $lastSpace);
			}
			// Remove trailing commas (add more array elements as desired)
			if (in_array(substr($theReturn, -1, 1), array(',')))
			{
				$theReturn = substr($theReturn, 0, -1);
			}
			// append the splitter string
			$theReturn .= $splitter;
		}
	}
	// all done!
	return $theReturn;
}

function cortar2($text, $maxChars = 30, $splitter = '...', $last = 0) {
	$theReturn = $text;

	// only do the rest if we're over the character limit
	if (strlen($text) > $maxChars)
	{
		if ($last)
			$theReturn = $splitter.substr($text, -$maxChars, $maxChars - 1);
		else
			$theReturn = substr($text, 0, $maxChars - 1).$splitter;
	}
	// all done!
	return $theReturn;
}

function sql($sql, $error = -1) {
	global $db,$contador_consultas,$debug_datos;
	$msg = "";
	$result = null;
	if (count($sql) > 1 or is_array($sql)) {
		$db->StartTrans();
		foreach ($sql as $indice => $sql_str) {
			$debug_datos_temp["sql"] = $sql_str;
			if ($db->Execute($sql_str) === false) {
				$msg .= "(Consulta ".($indice + 1)."): ".$db->ErrorMsg()."<br>";
				$debug_datos_temp["error"] = $db->ErrorMsg();
                  echo $db->ErrorMsg();
				//sql_error($error,$sql_str,$db->ErrorMsg());
			}
			else {
				$debug_datos_temp["affected"] = $db->Affected_Rows();
//				$debug_datos_temp["count"] = $result->RecordCount();
			}
			$debug_datos[] = $debug_datos_temp;
			$contador_consultas++;
		}
		$db->CompleteTrans();
	}
	else {
		$result = $db->Execute($sql);
		$debug_datos_temp["sql"] = $sql;
		if (!$result) {
			$msg .= $db->ErrorMsg()."<br>";
			$debug_datos_temp["error"] = $db->ErrorMsg();
			echo $db->ErrorMsg();
			//sql_error($error,$sql,$db->ErrorMsg());
		}
		else {
			//$debug_datos_temp["affected"] = $db->Affected_Rows();
			$debug_datos_temp["count"] = $result->RecordCount();
		}
		$debug_datos[] = $debug_datos_temp;
		$contador_consultas++;
	}
	if ($msg) {
		echo "</form></center></table><br><font color=#ff0000 size=3><b>ERROR $error: No se pudo ejecutar la consulta en la base de datos.</font><br>Descripción:<br>$msg</b>";
		return false;
	}
	if ($result)
		return $result;
	else
		return true;
}

function sql_error($error,$sql_error,$db_msg) {
	global $_ses_user,$db;
	$error = addslashes($error);
	$sql_error = encode_link($sql_error);
	$db_msg = encode_link($db_msg);
	$sql = "INSERT INTO errores_sql (codigo_error, sql, msg_error, fecha, usuario) ";
	$sql .= "VALUES ('$error', '$sql_error', '$db_msg', '".date("Y-m-d H:i:s")."', ";
	$sql .= "'".$_ses_user["name"]."')";
	$result = $db->Execute($sql);
}


//toma una letra y un string como parametros y devuelve
//el numero de ocurrencias de es letra en ese string
function str_count_letra($letra,$string) {
 $largo=strlen($string);
 $counter=0;
 for($i=0;$i<$largo;$i++)
 {
  if($string[$i]==$letra)
   $counter++;
 }
 return $counter;

}



/**********************************************************************
FUNCION QUE ORDENA UN ARREGLO BIDIMENSIONAL POR EL CAMPO $campo
DE LA SEGUNDA DIMENSON DEL ARREGLO
@bi_array    El arreglo a ordenar
$campo       El campo de la segunda dimension del arreglo por el cual
			 se ordenara el mismo
$tipo_campo  Este parametro se pone con la palabra string,
			 si el $campo es de tipo string
**********************************************************************/
function qsort_second_dimension($bi_array,$campo,$tipo_campo=0)
{
	$i=0;
 $tam=sizeof($bi_array);
 while($i<$tam)
 {$j=$i+1;
  if($tipo_campo=="string")
   $i_item=$bi_array[$i][$campo];
  else
   $i_item=intval($bi_array[$i][$campo]);
  while($j<$tam)
   {
   	if($tipo_campo=="string")
   	{ $j_item=$bi_array[$j][$campo];
   	  if(strcmp($i_item,$j_item)>0)
      {$temp=$bi_array[$i];
       $bi_array[$i]=$bi_array[$j];
       $bi_array[$j]=$temp;
       $j=$tam;
       $i--;
      }
      else
       $j++;
   	}
   	else
   	{
   	  $j_item=intval($bi_array[$j][$campo]);
   	  if($i_item>$j_item)
      {$temp=$bi_array[$i];
       $bi_array[$i]=$bi_array[$j];
       $bi_array[$j]=$temp;
       $j=$tam;
        $i--;
      }
      else
       $j++;
   	}

   }//de while($j<$tam)
   $i++;
 }//de while($i<$tam)
 return $bi_array;
}//de function qsort_second_dimension($bi_array,$campo,$string=0)



/*********************************************************************************
function insertar_string($cadena,$str, $limite)
Proposito:
          Inserta en $cadena, el string $str cada $limite caracteres.

variables utilizadas:
          - $longitud = contador para la longitud de $cadena
          - $tok = division en palabras de $cadena.
          - $palabra = variable utilizada para armar nuevamente $cadena
          - $string = cadena retornada por la funcion es $cadena con $str insertado $limite
          veces.

Logica:
         La funcion recorre $cadena separando a dicha cadena en palabras con la ayuda
         de la funcion strtok().
         Si la longitud de las palabras procesadas hasta el momento supera a $limite entonces
         se concatena al final de dicha palabra $str y se resetea el contador de longitud.
         antes de procesar la proxima palabra se concatena en $string las palabras procesadas
         hasta el momento.

NOTA: funcion implementada para utilizarse en el modulo licitaciones, en pagina
      funciones.php.
**********************************************************************************/
function insertar_string($cadena,$str, $limite){
$longitud=0;
    $tok = strtok ($cadena," ");
    while ($tok) {
        $longitud+=strlen($tok);
        $palabra=$tok;
        $tok = strtok (" ");
        if($longitud>$limite) {$palabra.=$str;$longitud=0;}
        $string.=" ".$palabra;
    }
    return $string;
}
//final de insertar_string


/********************************************************************************
 Funcion que ajusta el texto pasado como parametro en $texto, agregando 'enters'
 donde corresponda para que cada linea de $texto no supere la cantidad de maxima
 de caracteres que se especifican en el parametro $max_long.
*********************************************************************************/
function ajustar_lineas_texto($texto,$max_long)
{
 //tomamos la longitud de la cadena
 $long_texto=strlen($texto);
 $texto_resultado="";
 $contador=0;
 for($i=0;$i<$long_texto;$i++)
 {
  if($texto[$i]=="\r" && $texto[$i+1]=="\n")
  {
   $contador=0;
  }
  else if($contador==$max_long)
  {
   $texto_resultado.="\n";
   $contador=0;
  }
  else
  {
   $contador++;
  }
  $texto_resultado.=$texto[$i];

 }

 return $texto_resultado;
}

function variables_form_busqueda($prefijo,$extra=array()) {
	global $parametros;
	global $page,$keyword,$up,$filter,$sort,$cmd,$cmd1;
	global ${"_ses_".$prefijo};

	if ($_POST["form_busqueda"]) {
		$page = "0";
		$keyword = $_POST["keyword"];

	}
	else {
		if ((string)$_GET["page"] != "")
			$page = $_GET["page"] - 1;
		elseif ((string)$parametros["page"] != "")
			$page = (string)$parametros["page"];
		elseif ((string)${"_ses_".$prefijo}["page"] != "")
			$page = (string)${"_ses_".$prefijo}["page"];
		else
			$page = "0";
	}

	if (!isset($keyword)) {
		if ((string)$parametros["keyword"] != "")
			$keyword = (string)$parametros["keyword"];
		elseif ((string)${"_ses_".$prefijo}["keyword"] != "")
			$keyword = (string)${"_ses_".$prefijo}["keyword"];
		else
			$keyword = "";
	}


	if ((string)$_POST["up"] != "")
		$up = (string)$_POST["up"];
	elseif ((string)$parametros["up"] != "")
		$up = (string)$parametros["up"];
	elseif ((string)${"_ses_".$prefijo}["up"] != "")
		$up = (string)${"_ses_".$prefijo}["up"];
	else
		$up = "";

	if ((string)$_POST["filter"] != "")
		$filter = (string)$_POST["filter"];
	elseif ((string)$parametros["filter"] != "")
		$filter = (string)$parametros["filter"];
	elseif ((string)${"_ses_".$prefijo}["filter"] != "")
		$filter = (string)${"_ses_".$prefijo}["filter"];
	else
		$filter = "";

	if ((string)$_POST["sort"] != "")
		$sort = (string)$_POST["sort"];
	elseif ((string)$parametros["sort"] != "")
		$sort = (string)$parametros["sort"];
	elseif ((string)${"_ses_".$prefijo}["sort"] != "")
		$sort = (string)${"_ses_".$prefijo}["sort"];
	else
		$sort = "default";

	if ((string)$_POST["cmd"] != "")
		$cmd = (string)$_POST["cmd"];
	elseif ((string)$parametros["cmd"] != "")
		$cmd = (string)$parametros["cmd"];
	else
		$cmd = "";

	if ((string)$_POST["cmd1"] != "")
		$cmd1 = (string)$_POST["cmd1"];
	elseif ((string)$parametros["cmd1"] != "")
		$cmd1 = (string)$parametros["cmd1"];
	else
		$cmd1 = "";

	if ((string)$cmd != "") {
		if ((string)$cmd != (string)${"_ses_".$prefijo}["cmd"]) {


			$up = "";
			$page = "0";
			$filter = "";
			$keyword = "";
			$sort = "default";
			if (is_array($extra) and count($extra) > 0) {
				foreach ($extra as $key => $val) {
					global $$key;
					$$key = $val;
				}
			}
			//$flag_vaciar=1;
			$extra = array();
		}
	}
	else $cmd = (string)${"_ses_".$prefijo}["cmd"];

		//if (!$flag_vaciar && is_array($extra) and count($extra) > 0) {
		if (is_array($extra) and count($extra) > 0) {
		foreach ($extra as $key => $val) {
			if ((string)$_POST[$key] != "")
				$extra[$key] = (string)$_POST[$key];
			elseif ((string)$parametros[$key] != "")
				$extra[$key] = (string)$parametros[$key];
			elseif ((string)${"_ses_".$prefijo}[$key] != "")
				$extra[$key] = (string)${"_ses_".$prefijo}[$key];
			global $$key;
			$$key = $extra[$key];
		}
	}

	$variables = array("cmd"=>$cmd,"cmd1"=>$cmd1,"page"=>$page,"keyword"=>$keyword,"filter"=>$filter,"sort"=>$sort,"up"=>$up);
	$variables = array_merge($variables, $extra);
	if (serialize($variables) != serialize(${"_ses_".$prefijo})) {
		phpss_svars_set("_ses_".$prefijo, $variables);
	}

}

function compara_fechas($fecha1, $fecha2) {
	if ($fecha1) {
		$fecha1 = strtotime($fecha1);
	}
	else {
		$fecha1 = 0;
	}
	if ($fecha2) {
		$fecha2 = strtotime($fecha2);
	}
	else {
		$fecha2 = 0;
	}
    if ($fecha1 > $fecha2) return 1;
    elseif ($fecha1 == $fecha2) return 0;
    else return -1; //fecha2 > fecha1
}

/********************************************
Autor: MAC
-funcion que devuelve true si la fecha pasada
es feriado, y false si no lo es
*********************************************/
function feriado($dia_feriado) {
global $_ses_feriados;

$dia_fer=split("/",$dia_feriado);

$feriado=0;
$dia=intval($dia_fer[0]);
$mes=intval($dia_fer[1]);
$anio=intval($dia_fer[2]);

if (is_array($_ses_feriados[$anio."-".$mes."-".$dia])) {
	$feriado = count($_ses_feriados[$anio."-".$mes."-".$dia]);
}
else {
	$feriado = 0;
}
return $feriado;
}



/****************************************************************
Autor: MAC
-funcion que devuelve la cantidad de dias habiles que faltan
desde la $fecha1 de la $fecha2.

-El formato de las fechas debe ser d/m/Y
*****************************************************************/
function diferencia_dias_habiles($fecha1,$fecha2)
{
 $dif_dias=0;
 $fecha_aux=$fecha1;

 while(compara_fechas(fecha_db($fecha_aux),fecha_db($fecha2))==-1) //mientras la fecha2 sea mayor que la 1
 {
  $fecha_split=split("/",$fecha_aux);
  $fecha_dia=date("w",mktime(0,0,0,$fecha_split[1],$fecha_split[0],$fecha_split[2]));

  //si es dia habil, incrementamos la diferencia
  if($fecha_dia!=0 && !feriado($fecha_aux) &&  $fecha_dia!=6)

   $dif_dias++;
  //incrementamos en un dia la fecha
  $fecha_aux=date("d/m/Y",mktime(12,0,0,$fecha_split[1],$fecha_split[0]+1,$fecha_split[2]));

 }
 return $dif_dias;
}

/****************************************************************
Autor: Cestila
-funcion que devuelve la cantidad de dias que faltan
desde la $fecha1 de la $fecha2.

-El formato de las fechas debe ser d/m/Y
/*****************************************************************/

function diferencia_dias($fecha1,$fecha2,$h=0)

{
 $dif_dias=0;
 $fecha_aux=$fecha1;
 $fecha_hasta=$fecha2;
 if ($h) {
         $hora=date("H");
         $minutos=date("i");
         $segundos=date("s");
        while(compara_fechas($fecha_aux,$fecha_hasta)==-1) //mientras la fecha2 sea mayor que la 1
         {
          $fecha_split=split("/",fecha($fecha_aux));
          $dif_dias++;
          $fecha_aux=date("Y-m-d H:i:s",mktime($hora,$minutos,$segundos,$fecha_split[1],$fecha_split[0]+1,$fecha_split[2]));
         }

} //del if
else
   {
   $fecha_hasta=fecha_db($fecha_hasta);
   while(compara_fechas(fecha_db($fecha_aux),$fecha_hasta)==-1) //mientras la fecha2 sea mayor que la 1
    {
     $fecha_split=split("/",$fecha_aux);
     $dif_dias++;
     $fecha_aux=date("d/m/Y",mktime(12,0,0,$fecha_split[1],$fecha_split[0]+1,$fecha_split[2]));
    }
   }

 return $dif_dias;

}//de la funcion dia habiles


//CH_MENU BY GACZ:
//funcion para cambiar el path en el menu
//recibe el nombre del archivo.php o la ruta de este, ej $_SERVER['SCRIPT_NAME']
//SOLO SIRVE PARA ITEMS DENTRO DEL MENU
//SE DEBE ESCRIBIR AL BROWSER EL VALOR RETORNADO
//function ch_menu($nbre_archivo) {
// global $html_root;
// $nbre_archivo=substr($nbre_archivo,0,strrpos($nbre_archivo,"."));
// $slash_pos=strrpos($nbre_archivo,"/");
// $nbre_archivo=substr($nbre_archivo,($slash_pos===false)?0:$slash_pos+1);
// $ret_value="<script>";
// //$ret_value.="if (parent.menu!='$nbre_archivo')";
// $ret_value.= "parent.frame.location.href='".encode_link($html_root.'/menu.php',array('menu'=>$nbre_archivo))."'";
// $ret_value.= "</script>";
// return $ret_value;
//}


/*****************************************************************
Funcion que controla si la licitacion ($licitacion) tiene cargados
en cada renglon, quien gano ese renglon y quien gano cada uno.
-En caso de que todos los renglones tengan un ganador, que no sea
CORADIR, devuelve 1.
-En el caso de que al menos un renglon no tenga cargado quien lo
gano, devuelve un mensaje explicando la situación.
-En el caso de que todos los renglones tengan cargado quien los
gano, pero al menos uno de ellos haya sido ganado por coradir,
devuelve un mensaje explicando la situación.
******************************************************************/
function control_resultados_renglon($id_licitacion, $validar_control)
{global $db;

 //Buscamos el id del competidor Coradir
 $query="select id_competidor from competidores where nombre='".CORADIR."'";
 $r=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer el id de coradir (control_resultados_renglon)");
 $id_coradir=$r->fields['id_competidor'];

 //traemos todos los renglones de la licitacion junto al competidor que
 //lo gano o vacio si nadie lo gano
 $query="select id_renglon,id_competidor from licitacion join renglon using(id_licitacion) left join oferta using (id_renglon)
         where id_licitacion=$id_licitacion and (ganada='t' or oferta.id isnull)";
 $datos=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer datos renglon-competidor (control_resultados_renglon)");

 //variables que diran si hay algun renglon sin ganador cargado y
 //con coradir como ganador, respectivamente
 $sin_ganador=0;
 $con_coradir=0;
 while(!$datos->EOF)
 {
  if($datos->fields['id_competidor']=="")
   $sin_ganador=1;
  elseif($datos->fields['id_competidor']==$id_coradir)
   $con_coradir=1;
  $datos->MoveNext();
 }

 if($sin_ganador && $validar_control!=1)
  return "Existen renglones que no tiene especificado el ganador.";
 elseif($con_coradir)
  return "Existen renglones cuyo ganador es CORADIR.";
 else
  return 1;
}

///adaptacion de la funcion mariela en php
//funcion que  me convierte de numero a letra copia de la funcion de mariela
function Centenas($VCentena) {
$Numeros[0] = "cero";
$Numeros[1] = "uno";
$Numeros[2] = "dos";
$Numeros[3] = "tres";
$Numeros[4] = "cuatro";
$Numeros[5] = "cinco";
$Numeros[6] = "seis";
$Numeros[7] = "siete";
$Numeros[8] = "ocho";
$Numeros[9] = "nueve";
$Numeros[10] = "diez";
$Numeros[11] = "once";
$Numeros[12] = "doce";
$Numeros[13] = "trece";
$Numeros[14] = "catorce";
$Numeros[15] = "quince";
$Numeros[20] = "veinte";
$Numeros[30] = "treinta";
$Numeros[40] = "cuarenta";
$Numeros[50] = "cincuenta";
$Numeros[60] = "sesenta";
$Numeros[70] = "setenta";
$Numeros[80] = "ochenta";
$Numeros[90] = "noventa";
$Numeros[100] = "ciento";
$Numeros[101] = "quinientos";
$Numeros[102] = "setecientos";
$Numeros[103] = "novecientos";
If ($VCentena == 1) { return $Numeros[100]; }
Else If ($VCentena == 5) { return $Numeros[101];}
Else If ($VCentena == 7 ) {return ( $Numeros[102]); }
Else If ($VCentena == 9) {return ($Numeros[103]);}
Else {return $Numeros[$VCentena];}

}
function Unidades($VUnidad) {
$Numeros[0] = "cero";
$Numeros[1] = "uno";
$Numeros[2] = "dos";
$Numeros[3] = "tres";
$Numeros[4] = "cuatro";
$Numeros[5] = "cinco";
$Numeros[6] = "seis";
$Numeros[7] = "siete";
$Numeros[8] = "ocho";
$Numeros[9] = "nueve";
$Numeros[10] = "diez";
$Numeros[11] = "once";
$Numeros[12] = "doce";
$Numeros[13] = "trece";
$Numeros[14] = "catorce";
$Numeros[15] = "quince";
$Numeros[20] = "veinte";
$Numeros[30] = "treinta";
$Numeros[40] = "cuarenta";
$Numeros[50] = "cincuenta";
$Numeros[60] = "sesenta";
$Numeros[70] = "setenta";
$Numeros[80] = "ochenta";
$Numeros[90] = "noventa";
$Numeros[100] = "ciento";
$Numeros[101] = "quinientos";
$Numeros[102] = "setecientos";
$Numeros[103] = "novecientos";
$tempo=$Numeros[$VUnidad];
return $tempo;
}

function Decenas($VDecena) {
$Numeros[0] = "cero";
$Numeros[1] = "uno";
$Numeros[2] = "dos";
$Numeros[3] = "tres";
$Numeros[4] = "cuatro";
$Numeros[5] = "cinco";
$Numeros[6] = "seis";
$Numeros[7] = "siete";
$Numeros[8] = "ocho";
$Numeros[9] = "nueve";
$Numeros[10] = "diez";
$Numeros[11] = "once";
$Numeros[12] = "doce";
$Numeros[13] = "trece";
$Numeros[14] = "catorce";
$Numeros[15] = "quince";
$Numeros[20] = "veinte";
$Numeros[30] = "treinta";
$Numeros[40] = "cuarenta";
$Numeros[50] = "cincuenta";
$Numeros[60] = "sesenta";
$Numeros[70] = "setenta";
$Numeros[80] = "ochenta";
$Numeros[90] = "noventa";
$Numeros[100] = "ciento";
$Numeros[101] = "quinientos";
$Numeros[102] = "setecientos";
$Numeros[103] = "novecientos";
$tempo = ($Numeros[$VDecena]);
return $tempo;
}





function NumerosALetras($Numero){


$Decimales = 0;
//$Numero = intval($Numero);
$letras = "";

while ($Numero != 0){

// '*---> Validación si se pasa de 100 millones

If ($Numero >= 1000000000) {
$letras = "Error en Conversión a Letras";
$Numero = 0;
$Decimales = 0;
}

// '*---> Centenas de Millón
If (($Numero < 1000000000) And ($Numero >= 100000000)){
If ((Intval($Numero / 100000000) == 1) And (($Numero - (Intval($Numero / 100000000) * 100000000)) < 1000000)){
$letras .= (string) "cien millones ";
}
Else {
$letras = $letras & Centenas(Intval($Numero / 100000000));
If ((Intval($Numero / 100000000) <> 1) And (Intval($Numero / 100000000) <> 5) And (Intval($Numero / 100000000) <> 7) And (Intval($Numero / 100000000) <> 9)) {
$letras .= (string) "cientos ";
}
Else {
$letras .= (string) " ";
}
}
$Numero = $Numero - (Intval($Numero / 100000000) * 100000000);
}

// '*---> Decenas de Millón
If (($Numero < 100000000) And ($Numero >= 10000000)) {
If (Intval($Numero / 1000000) < 16) {
$tempo = Decenas(Intval($Numero / 1000000));
$letras .= (string) $tempo;
$letras .= (string) " millones ";
$Numero = $Numero - (Intval($Numero / 1000000) * 1000000);
}
Else {
$letras = $letras & Decenas(Intval($Numero / 10000000) * 10);
$Numero = $Numero - (Intval($Numero / 10000000) * 10000000);
If ($Numero > 1000000) {
$letras .= $letras & " y ";
}
}
}

// '*---> Unidades de Millón
If (($Numero < 10000000) And ($Numero >= 1000000)) {
$tempo=(Intval($Numero / 1000000));
If ($tempo == 1) {
$letras .= (string) " un millón ";
}
Else {
$tempo= Unidades(Intval($Numero / 1000000));
$letras .= (string) $tempo;
$letras .= (string) " millones ";
}
$Numero = $Numero - (Intval($Numero / 1000000) * 1000000);
}

// '*---> Centenas de Millar
If (($Numero < 1000000) And ($Numero >= 100000)) {
$tempo=(Intval($Numero / 100000));
$tempo2=($Numero - ($tempo * 100000));
If (($tempo == 1) And ($tempo2 < 1000)) {
$letras .= (string) "cien mil ";
}
Else {
$tempo=Centenas(Intval($Numero / 100000));
$letras .= (string) $tempo;
$tempo=(Intval($Numero / 100000));
If (($tempo <> 1) And ($tempo <> 5) And ($tempo <> 7) And ($tempo <> 9)) {
$letras .= (string) "cientos ";
}
Else {
$letras .= (string) " ";
}
}
$Numero = $Numero - (Intval($Numero / 100000) * 100000);
}

// '*---> Decenas de Millar
If (($Numero < 100000) And ($Numero >= 10000)) {
$tempo= (Intval($Numero / 1000));
If ($tempo < 16) {
$tempo = Decenas(Intval($Numero / 1000));
$letras .= (string) $tempo;
$letras .= (string) " mil ";
$Numero = $Numero - (Intval($Numero / 1000) * 1000);
}
Else {
$tempo = Decenas(Intval($Numero / 10000) * 10);
$letras .= (string) $tempo;
$Numero = $Numero - (Intval(($Numero / 10000)) * 10000);
If ($Numero > 1000) {
	$rest = substr($letras, -6);
    if ($rest!='veinte'){
	    $resto = substr($letras, -4);
 	    if ($resto!='diez')
           $letras .=(string) " y ";
    }
   if($rest=='veinte') {
      $letras= substr($letras,0, -1);
  	  $letras.='i';
    }
    if ($resto=='diez') {
    	$letras=substr($letras,0, -1);
        $letras.= 'ci';
    }

}
Else {
$letras .= (string) " mil ";

}
}
}


// '*---> Unidades de Millar
If (($Numero < 10000) And ($Numero >= 1000)) {
$tempo=(Intval($Numero / 1000));
If ($tempo == 1) {
$letras .= (string) "un";
}
Else {
$tempo = Unidades(Intval($Numero / 1000));
$letras .= (string) $tempo;
}
$letras .= (string) " mil ";
$Numero = $Numero - (Intval($Numero / 1000) * 1000);
}

// '*---> Centenas
If (($Numero < 1000) And ($Numero > 99)) {
If ((Intval($Numero / 100) == 1) And (($Numero - (Intval($Numero / 100) * 100)) < 1)) {
//$letras = $letras & "cien ";
$letras.="cien";
}
Else {
$temp=(Intval($Numero / 100));
$l2=Centenas($temp);
$letras .= (string) $l2;
If ((Intval($Numero / 100) <> 1) And (Intval($Numero / 100) <> 5) And (Intval($Numero / 100) <> 7) And (Intval($Numero / 100) <> 9)) {
$letras .= "cientos ";
}
Else {
$letras .= (string) " ";
}
}

$Numero = $Numero - (Intval($Numero / 100) * 100);

}

// '*---> Decenas
If (($Numero < 100) And ($Numero > 9) ) {
If ($Numero < 16 ) {
$tempo = Decenas(Intval($Numero));
$letras .= $tempo;
$Numero = $Numero - Intval($Numero);
}
Else {
$tempo= Decenas(Intval(($Numero / 10)) * 10);
$letras .= (string) $tempo;
$Numero = $Numero - (Intval(($Numero / 10)) * 10);
If ($Numero > 0.99) {

	$rest = substr($letras, -6);
   	if ($rest!='veinte'){
	    $resto = substr($letras, -4);
 	    if ($resto!='diez')
           $letras .=(string) " y ";
    }

   if($rest=='veinte') {
   	  $resto="";
      $letras= substr($letras,0, -1);
  	  $letras.='i';
  	}
    if ($resto=='diez') {
       $letras=substr($letras,0, -1);
 	   $letras.= 'ci';
    }

}
}
}

// '*---> Unidades
If (($Numero < 10) And ($Numero > 0.99)) {
$tempo=Unidades(Intval($Numero));
$letras .= (string) $tempo;

$Numero = $Numero - Intval($Numero);
}


// '*---> Decimales
If ($Decimales > 0) {
}
Else {
If (($letras <> "Error en Conversión a Letras") And (strlen(Trim($letras)) > 0)) {
$letras .= (string) " ";

}
}
return $letras;
}
}

function generar_barra_nav($campos_barra) {
	global $cmd,$total_registros,$bgcolor3,$html_root;
	$barra = "";
	$width = floor(100/count($campos_barra));
	foreach ($campos_barra as $clave => $valor) {
         //print_r($valor["extra"]);
		if ($valor["sql_contar"]) {
			$result = sql($valor["sql_contar"]) or die;
			$total_registros[$valor["cmd"]] = $result->fields[0];
			$valor["descripcion"] .= " (".$total_registros[$valor["cmd"]].")";
		}
		if ($cmd == $valor["cmd"]) {
			$menuid="ma background='$html_root/imagenes/btn_verde.gif'";
            $barra .= "<a href='".encode_link($_SERVER["PHP_SELF"],is_array($valor["extra"])?array_merge($valor["extra"],array("cmd" => $valor["cmd"])):array("cmd" => $valor["cmd"]))."'>";
			$barra .= "<td id=$menuid class='bordesderinferior' width='$width%' style='cursor:hand' onmouseover=\"this.style.color='#000000'\" onmouseout=\"this.style.color='#006699'\" >".$valor["descripcion"]."</td>";
                        $barra.="</a>";
		}
		else {
			$menuid="ma"." background='$html_root/imagenes/btn_azul.gif'";
			$barra .= "<a href='".encode_link($_SERVER["PHP_SELF"],is_array($valor["extra"])?array_merge($valor["extra"],array("cmd" => $valor["cmd"])):array("cmd" => $valor["cmd"]))."'>";
			$barra .= "<td id=$menuid class='bordesderinferior' width='$width%' style='cursor:hand' onmouseover=\"this.style.color='#000000'\" onmouseout=\"this.style.color='#006699'\" >".$valor["descripcion"]."</td>";
			$barra.="</a>";
		}
	}
	echo "<table width=95% border=0 cellspacing=3 cellpadding=5 bgcolor='#FFFFFF' align=center>\n";    //bgcolor=$bgcolor3
	echo "<tr>$barra</tr></table>\n";
}


function gestiones_comentarios($id_gestion,$tipo,$editable=0) {
	global $bgcolor3,$permisos,$html_root;
	echo "<b>Comentarios:</b><br>";
	$sql = "SELECT id_comentario,fecha,comentario,ultimo_usuario FROM ";
	$sql .= "gestiones_comentarios WHERE id_gestion=$id_gestion ";
	$sql .= "AND tipo='$tipo' ORDER BY fecha ASC";
	$result = sql($sql) or die;

	if (permisos_check("inicio","gestion_mod_comentario")) {
		$mod_comentario = true;
	}
	else {
		$mod_comentario = false;
	}
	if ($result->RecordCount() > 0) {
		echo "<table width='100%' border='1' cellpadding='2' cellspacing='1' bgcolor='$bgcolor3' bordercolor='#ffffff'>";
		echo "<tr id=ma><td width='25%'>Fecha</td>";
		echo "<td width='75%'>Comentario</td></tr>";
		if ($editable) {
			$result->MoveFirst();
			while (!$result->EOF) {

				echo "<tr><td align=center valign=top><b>".$result->fields["ultimo_usuario"]."<br>";
				echo Fecha($result->fields["fecha"])."&nbsp;".Hora($result->fields["fecha"])."</b>";
				if ($mod_comentario) {
					echo "<br><input style='width:80;' type=button name=modificar_comentario value='Modificar' onClick=\"document.location='".encode_link($_SERVER["PHP_SELF"],array("cmd1"=>"modificar_comentario","id_comentario"=>$result->fields["id_comentario"]))."';\">";
				}
				echo "</td>";
				echo "<td><div name='comentario_".$result->fields["id_comentario"]."' style='width:100%;border: outset 2;background-color: white;'>".html_out($result->fields["comentario"])."</div></td>\n";
				echo "</tr>";
				$result->MoveNext();
			}
			echo "<tr><td align=right valign=top><b>Nuevo:</b></td>";
			echo "<td><textarea name='comentario_nuevo' style='width:100%;' rows=4></textarea></td>\n";
			echo "</tr>";
		}
		else {
			$result->MoveFirst();
			while (!$result->EOF) {
				echo "<tr><td align=center valign=top><b>".$result->fields["ultimo_usuario"]."<br>";
				echo Fecha($result->fields["fecha"])."&nbsp;".Hora($result->fields["fecha"])."</b>";
				echo "</td>";
				echo "<td align=left valign=top>".html_out($result->fields["comentario"])."&nbsp;</td>\n";
				echo "</tr>";
				$result->MoveNext();
			}
		}
		echo "</table>";
	}
	else {
		if ($editable) {
			echo "<table width='100%' border='1' cellpadding='2' cellspacing='1' bgcolor='$bgcolor3' bordercolor='#ffffff'>";
			echo "<tr id=ma><td width='25%'>Fecha</td>";
			echo "<td width='75%'>Comentario</td></tr>";
			echo "<tr><td align=right valign=top><b>Nuevo:</b></td>";
			echo "<td><textarea name='comentario_nuevo' style='width:100%;' rows=4></textarea></td>\n";
			echo "</tr>";
			echo "</table>";
		}
		else {
			echo "<div align=center><b>No hay comentarios cargados</b></div>";
		}
	}
}
function editar_comentario($id_comentario,$cmd_volver) {
	global $html_header,$bgcolor2,$bgcolor3;
	echo $html_header;
	echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>";
	echo "<br><table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=+1>Modificar Comentario</font></td></tr>";
	$sql = "SELECT id_gestion,fecha,comentario FROM ";
	$sql .= "gestiones_comentarios WHERE id_comentario=$id_comentario";
	$result = sql($sql) or die;
	echo "<tr><td width=10% align=right valign=top><b>".Fecha($result->fields["fecha"])."<br>".Hora($result->fields["fecha"])."</b></td>";
	echo "<td><textarea name='comentario' style='width:100%;' rows=10>".$result->fields["comentario"]."</textarea></td>\n";
	echo "</tr><tr>";
	echo "<td align=center colspan=2 style=\"border:$bgcolor2\">";
	echo "<br><input type=hidden name=id_gestion value='".$result->fields["id_gestion"]."'>";
	echo "<input type=hidden name=id_comentario value='$id_comentario'>";
	echo "<input type=submit name=guardar_comentario value='Guardar' style='width:160;'>&nbsp;&nbsp;&nbsp;";
	echo "<input type=reset name=reset value='Deshacer' style='width:160;'>&nbsp;&nbsp;&nbsp;";
	echo "<input type=button name=volver style='width:160;' value='Volver' onClick=\"document.location='".encode_link($_SERVER["PHP_SELF"],array("cmd1"=>$cmd_volver,"id"=>$result->fields["id_gestion"]))."';\">";
	echo "<br><br></td>";
	echo "</tr>";
	echo "</table></form></html>";
}
function guardar_comentario() {
	//global $_ses_user_name;
	$id_comentario = $_POST["id_comentario"] or Error("Falta el ID del comentario");
	$comentarios = $_POST["comentario"] or Error("Debe ingresar el comentario");
	$ultimo_usuario = $_ses_user['name'];
	if (!$error) {
		$sql = "SELECT comentario FROM gestiones_comentarios WHERE id_comentario = $id_comentario";
		$result = sql($sql) or die;
		if ($result->RecordCount() == 0) {
			Error("No existe el comentario número $id_comentario");
		}
		else {
			$comentarios_orig = $result->fields["comentario"];
		}
	}
	else { return false; }
	if (!$error) {
		if ($comentarios != $comentarios_orig) {
			$comentarios=ereg_replace("'","\'",$comentarios);
			$comentarios=ereg_replace("\"","\\\"",$comentarios);
			$sql = "UPDATE gestiones_comentarios SET comentario='$comentarios' ";
//            $sql .= "ultimo_usuario='$ultimo_usuario' ";
//			$sql .= "fecha='".date("Y-m-d H:i:s")."' ";
			$sql .= "WHERE id_comentario=$id_comentario";
			sql($sql) or die;
		}
		return true;
	}
	else {
		Aviso("No se pudo modificar el comentario");
		return false;
	}
}
function nuevo_comentario($id_gestion,$tipo,$comentario) {
	global $_ses_user;
	$sql = "INSERT INTO gestiones_comentarios (id_gestion,";
	$sql .= "fecha,comentario,ultimo_usuario,tipo) VALUES ($id_gestion,";
	$comentario=ereg_replace("'","\'",$comentario);
	$comentarios=ereg_replace("\"","\\\"",$comentarios);
	$sql .= "'".date("Y-m-d H:i:s")."','$comentario','".$_ses_user["name"]."','$tipo')";
	return $sql;
}
function getmicrotime() {
	list($useg, $seg) = explode(" ",microtime());
	return ((float)$useg + (float)$seg);
}
// Funcion que devuelve el tiempo que se demora en generarse la pagina
function tiempo_de_carga () {
	$tiempo_fin = getmicrotime();
	$tiempo = sprintf('%.4f', $tiempo_fin - TIEMPO_INICIO);
	return $tiempo;
}

/* firma_coradir() - version solo texto	*/
function firma_coradir($confiden=true){
	if ($confiden){
	$confiden="NOTA DE CONFIDENCIALIDAD\n";
	$confiden.="Este mensaje (y sus anexos) es confidencial, esta dirigido exclusivamente a\n";
	$confiden.="las personas direccionadas en el mail, puede contener información de\n";
	$confiden.="propiedad exclusiva de Coradir S.A. y/o amparada por el secreto profesional.\n";
	$confiden.="El acceso no autorizado, uso, reproducción, o divulgación esta prohibido.\n";
	$confiden.="Coradir S.A. no asumirá responsabilidad ni obligación legal alguna por\n";
	$confiden.="cualquier información incorrecta o alterada contenida en este mensaje.\n";
	$confiden.="Si usted ha recibido este mensaje por error, le rogamos tenga la amabilidad\n";
	$confiden.="de destruirlo inmediatamente junto con todas las copias del mismo, notificando\n";
	$confiden.="al remitente. No deberá utilizar, revelar, distribuir, imprimir o copiar\n";
	$confiden.="este mensaje ni ninguna de sus partes si usted no es el destinatario.\n";
	$confiden.="Muchas gracias.\n";
	}else
  	$confiden="";
	$firma="CORADIR S.A.\n";
	$firma.="San Luis: Tel/Fax: (02652)458255 y 458256\n";
	$firma.="Dirección: Ruta 3 Km 1.4 -CP 5700\n";
	$firma.="Bs.As.: Tel/Fax: (011)5354-0300 y rotativas\n";
	$firma.="Dirección: Patagones 2538 - Parque Patricios - (C1071AAI)\n";
	$firma.="e-mail: info@coradir.com.ar\n";
	$firma.="página: www.coradir.com.ar\n";

	return "\n".$firma."\n".$confiden;
}



/* firma_coradir() - version html	*/
function firma_coradir_mail($confiden=true)
{
if ($confiden)
{
	$confiden="<br>NOTA DE CONFIDENCIALIDAD<br>\n";
	$confiden.="Este mensaje (y sus anexos) es confidencial, esta dirigido exclusivamente a <br>\n";
	$confiden.="las personas direccionadas en el mail, puede contener información de <br>\n";
	$confiden.="propiedad exclusiva de Coradir S.A. y/o amparada por el secreto profesional. <br>\n";
	$confiden.="El acceso no autorizado, uso, reproducción, o divulgación esta prohibido. <br>\n";
	$confiden.="Coradir S.A. no asumirá responsabilidad ni obligación legal alguna por <br>\n";
	$confiden.="cualquier información incorrecta o alterada contenida en este mensaje. <br>\n";
	$confiden.="Si usted ha recibido este mensaje por error, le rogamos tenga la amabilidad <br>\n";
	$confiden.="de destruirlo inmediatamente junto con todas las copias del mismo, notificando<br>\n";
	$confiden.="al remitente. No deberá utilizar, revelar, distribuir, imprimir o copiar <br>\n";
	$confiden.="este mensaje ni ninguna de sus partes si usted no es el destinatario. <br>\n";
	$confiden.="Muchas gracias.<br>\n";
}
else
	$confiden="";

$firma="CORADIR S.A. <br>\n";
$firma.="San Luis: Tel/Fax: (02652)458255 y 458256 <br>\n";
$firma.="Dirección: Ruta 3 Km 1.4 - CP 5700 <br>\n";
$firma.="Bs.As.: Tel/Fax: (011)5354-0300 y rotativas <br>\n";
$firma.="Dirección: Patagones 2538 - Parque Patricios - (C1071AAI)<br>\n";
$firma.="e-mail: info@coradir.com.ar<br>\n";
$firma.="página: www.coradir.com.ar<br>\n";

return "<br>\n".$firma."<br>\n".$confiden."</body></html>\n";

}


//funciones para automatizar el estado de los renglones
//de las licitaciones
function obtener_estados($id) {
global $db;

$sql="select * from historial_estados ";
$sql.=" where id_renglon=$id and activo=1 order by id_estado_renglon";
$historial=$db->execute($sql) or die($sql."<br>".$db->errormsg());
$cantidad=$historial->recordcount();

for($i=0;$i<$cantidad;$i++){
   switch ($historial->fields["id_estado_renglon"]){

   case 1:
         $estados[1]=1;
         break;
   case 2:
         $estados[2]=2;
         break;
   case 3:
         $estados[3]=3;
         break;

   }//del swicth
 $historial->movenext();
}

return $estados;
}

function eliminar_estado($id_renglon){
global $db;
$sql="delete from historial_estados ";
$sql.=" where id_renglon=$id_renglon";
$db->execute($sql) or die($db->errormsg()."<br>".$sql);

}//fin de la funcion




//$id_renglon le paso el renglon que cambia de estado
//$id_estado  me dice si hay un estado para cambia si no hay nada
// puede ser que se elimine el que estaba o no
// filtrar me pasa el id del estado siempre para poder buscar en la
// base de datos



function insertar_estado($id_renglon,$id_estado,$filtrar=1){
global $db,$_ses_user;

 $fecha=date("Y-m-d H:i:s",mktime());
 $usuario=$_ses_user["name"];

 $db->starttrans();

 $sql="select codigo_renglon from renglon where id_renglon=$id_renglon";
 $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
 $codigo_renglon=$resultado->fields["codigo_renglon"];
 if ($id_estado!=""){
         $sql="select id_historial_renglon,id_renglon,id_estado_renglon,activo from historial_estados ";
         $sql.=" where id_renglon=$id_renglon and id_estado_renglon=$id_estado";
         $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
         $cantidad=$resultado->recordcount();
         $id_historial_renglon=$resultado->fields["id_historial_renglon"];
         $activo=$resultado->fields["activo"];
         if ($cantidad<=0)
             {
             //inserto el estado por que no existe

             $sql="select nextval('historial_estados_id_historial_renglon_seq') as id_historial_renglon";
             $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
             $id_historial_renglon=$resultado->fields["id_historial_renglon"];

             $sql="insert into historial_estados (id_historial_renglon,id_renglon,id_estado_renglon) ";
             $sql.="values ($id_historial_renglon,$id_renglon,$id_estado) ";
             $db->execute($sql) or die($db->errormsg()."<br>".$sql);
             //falta el log

             //obtengo el nombre del estado a insertar
             $sql="select * from estado_renglon where id_estado_renglon=$id_estado";
             $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
             $tipo="Agrego el Estado ".$resultado->fields["nombre"]." al renglon:$codigo_renglon";

             //inserto el log correspondiente
             $sql="insert into log_estado_renglon (id_historial_renglon,tipo,usuario,fecha)";
             $sql.=" values ($id_historial_renglon,'$tipo','$usuario','$fecha')";
             $db->execute($sql) or die($sql."<br>".$db->errormsg());
             }

             else{
               if ($activo==0){
                  //modifico el id del estado
                  //ya existe y le doy de nuebo alta logica
                  $sql="update  historial_estados set activo=1 where ";
                  $sql.=" id_historial_renglon=$id_historial_renglon";
                  $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                  //obtengo el nombre del estado a insertar
                  $sql="select * from estado_renglon where id_estado_renglon=$id_estado";
                  $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                  $tipo="Agrego el Estado ".$resultado->fields["nombre"]." al renglon: $codigo_renglon";

                  //inserto el log correspondiente
                  $sql="insert into log_estado_renglon (id_historial_renglon,tipo,usuario,fecha)";
                  $sql.=" values ($id_historial_renglon,'$tipo','$usuario','$fecha')";
                  $db->execute($sql) or die($sql."<br>".$db->errormsg());
               } //del if de activo

             }


       }//que no viene el id_estado y tengo que borrar
       else {
             $sql="select id_historial_renglon,id_renglon,id_estado_renglon,activo from historial_estados ";
             $sql.=" where id_renglon=$id_renglon and id_estado_renglon=$filtrar";
             $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
             $cantidad=$resultado->recordcount();
             $id_historial_renglon=$resultado->fields["id_historial_renglon"];
             $activo=$resultado->fields["activo"];
             if ($cantidad && $activo){
                //realizo la baja logica
                $sql="update  historial_estados set activo=0 where ";
                $sql.=" id_historial_renglon=$id_historial_renglon";
                $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());

                //obtengo el nombre del estado a insertar
                $sql="select * from estado_renglon where id_estado_renglon=$filtrar";
                $resultado=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                $tipo="Elimino  el Estado ".$resultado->fields["nombre"]." al renglon:$codigo_renglon";

                //inserto el log correspondiente
                $sql="insert into log_estado_renglon (id_historial_renglon,tipo,usuario,fecha)";
                $sql.=" values ($id_historial_renglon,'$tipo','$usuario','$fecha')";
                $db->execute($sql) or die($sql."<br>".$db->errormsg());

            }

      }


 $db->completetrans();
} //del if de la funcion
//********************************************************

function archivo_orden_compra($ID,$id_subir=0){
?>

<?
} // de la funcion



//********************************************************
function mostrar_ordenes_compra($id_licitacion){
global $bgcolor3,$html_root;

   $sql=" select * from (
            select id_subir,nro_orden from
            subido_lic_oc where id_licitacion=$id_licitacion
            ) as sl
            left join
            (
            select sum(cantidad*precio) as total,id_subir from renglones_oc
            group by id_subir
            )  as total
           using (id_subir)
           ";
   $res=sql($sql) or fin_pagina();

   $sql="select simbolo from licitacion join moneda using(id_moneda)
         where id_licitacion=$id_licitacion";
   $moneda=sql($sql) or fin_pagina();

   if ($res->recordcount()>0) {
      //es que hay ordenes

      ?>
      <table width=100% align=center border=1 cellpading=0 cellspacing=0 bordercolor='<?=$bgcolor3?>'>
        <tr>
          <td colspan="<?=$res->recordcount()?>">
          <b>Ordenes de Compra</b>
          </td>
        </tr>
        <tr>
        <?
        $cont=0;
        for($i=1;$i<=$res->recordcount();$i++){
         if (!($cont % 3)) { echo "</tr><tr>"; }
           $link=encode_link("../../lib/archivo_orden_de_compra.php",array("id_subir"=>$res->fields["id_subir"],"solo_lectura"=>1));
        ?>

             <td width=33% align=center valign=bottom >
             <table width=100% align=center border=0>
                <tr>
                  <a href=<?=$link?> target="_blank">
                  <td align=right >
                   <font color='blue'>
                   <?=$res->fields["nro_orden"]?>
                   </font>
                  </td>
                  </a>
                </tr>
                <tr>
                  <td align=right><?=$moneda->fields["simbolo"]?> &nbsp;&nbsp;

                  <?=formato_money($res->fields["total"])?>
                  </td>
                <tr>
            </table>
            </td>

        <?  $cont++;
            $res->movenext();
        }//del for
        ?>
        </tr>
      </table>
      <?
   }  // del if
}//de la funcion mostar_ordenes_compra


function automatizar_estados($id_licitacion){
  global $db;

  $db->starttrans();

  $renglones=array();

   //traigo el valor de dolar de comparacion general
  $sql="select * from dolar_general";
  $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
  $dolar_comparacion=$resultado->fields["valor"];

  if (!$dolar_comparacion) $dolar_comparacion=2.97;

  //selecciono la moneda en que esta echa la oferta de coradir
  $sql=" select id_moneda from licitacion where id_licitacion=$id_licitacion";
  $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
  $moneda_coradir=$resultado->fields["id_moneda"];

  //No tomo en cuenta los renglones que son alternativas
  $sql=" select oferta.*,r.cantidad from licitacion
         left join (select * from renglon where codigo_renglon not ilike '%alt%') r using (id_licitacion)
         left join oferta using (id_renglon)
         where licitacion.id_licitacion=$id_licitacion
         and oferta.id_competidor=1";


  $coradir=$db->execute($sql) or die($sql."<br>".$db->errormsg());

  for ($i=0;$i<$coradir->recordcount();$i++)
      {
      $oferta_coradir=$coradir->fields["monto_unitario"];
      $id_renglon=$coradir->fields["id_renglon"];
      //es que coradir participa en ese renglon
       if ($oferta_coradir){
              //este es el caso que coradir realize oferta en dolares
               if  ($moneda_coradir==2) {
                           //comparo con los que ofrecieron en la misma moneda
                           $sql=" select count(id_competidor) as cantidad from
                                  oferta where id_moneda=$moneda_coradir and id_renglon=$id_renglon
                                  and (monto_unitario < $oferta_coradir) and monto_unitario<>0";
                           $competidores=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                           $competidores_misma_moneda=$competidores->fields["cantidad"];

                          //comparo con los que ofrecieron en distinta moneda
                          $sql=" select count(id_competidor) as cantidad from
                                 oferta where id_moneda<>$moneda_coradir and id_renglon=$id_renglon
                                 and (monto_unitario < ($oferta_coradir*$dolar_comparacion)) and monto_unitario<>0";
                         $competidores=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                         $competidores_distinta_moneda=$competidores->fields["cantidad"];
                         }


                   //este es el caso que coradir realize oferta en pesos
                   if ($moneda_coradir==1){
                          $sql=" select count(id_competidor) as cantidad from
                                 oferta where id_moneda=$moneda_coradir and id_renglon=$id_renglon
                                 and (monto_unitario < $oferta_coradir) and monto_unitario<>0";
                          $competidores=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                          $competidores_misma_moneda=$competidores->fields["cantidad"];
                          //comparo con los que ofrecieron en distinta moneda
                           $sql=" select count(id_competidor) as cantidad from
                                  oferta where id_moneda<>$moneda_coradir and id_renglon=$id_renglon
                                  and ((monto_unitario*$dolar_comparacion) < $oferta_coradir) and monto_unitario<>0";
                          $competidores=$db->execute($sql) or die($sql."<br>".$db->errormsg());
                          $competidores_distinta_moneda=$competidores->fields["cantidad"];
                         }
              //si es igual  que 0 es que gano coradir
               if ($competidores_misma_moneda==0 && $competidores_distinta_moneda==0)
                         {
                          insertar_estado($id_renglon,1,1);
                          $renglones[]=$id_renglon;
                         }
              //no gano coradir entonces pierdo ese renglon
                         else {
                           insertar_estado($id_renglon,"",1);
                         }
           }//del primer if
           //Coradir no oferto nada entonces no gano ese renglon
            else {
                 insertar_estado($id_renglon,"",1);
             }
             $coradir->movenext();
      }//del for

//armo el arreglo para mandar el mail de los resultados y mando el mail

   $datos["id_licitacion"]=$id_licitacion;
   $datos["renglones"]=$renglones;
   //monto_ofertado es monto estimado
   $monto_estimado=monto_estimado_renglones($renglones);

   if (sizeof($renglones)>0)
                             $modificar_estado=",id_estado=2";

   $sql="update licitacion set monto_estimado=$monto_estimado $modificar_estado
         where id_licitacion=$id_licitacion";
   $db->execute($sql) or die($db->errormsg()."<br>".$sql);

   enviar_mail_resultados_cargados($datos);


if ($db->completetrans()) $msg="Operación realizada exitosamente";
                   else   $msg="Ha Ocurrido un error cuando se cargaban los resultados";
return $msg;
}//fin de automatizar estados de las licitaciones



//Funcion para obtener las preferencias
//de las botoneras en licitaciones
function obtener_preferencias($usuario,$tipo){
  global $db;

  $sql="select * from configuracion_botones where usuario='$usuario' and tipo='$tipo'";
  $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
  if ($resultado->recordcount()>0)
                               $return=1;
                               else
                               $return=0;
  return $return;
} // de la funcion obtener_preferencias


//funcion que me devuelve el monto ofertado de los renglones
function monto_estimado_renglones($renglones){

  global $db;
  $monto_ofertado=0;
  if (sizeof($renglones)>0) {
          for ($i=0;$i<sizeof($renglones);$i++){
                 if ($i==0) $condicion_renglones=" and (";
                 $id_renglon=$renglones[$i];
                 if ($i==sizeof($renglones)-1)
                               $condicion_renglones.=" renglon.id_renglon=$id_renglon)";
                               else
                               $condicion_renglones.=" renglon.id_renglon=$id_renglon or ";
           }//del for

          $sql=" select sum(oferta.monto_unitario*renglon.cantidad) as monto_ofertado
                 from licitaciones.renglon
                 join licitaciones.oferta using(id_renglon)
                 where id_competidor=1  $condicion_renglones
               ";

           $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
           $monto_ofertado=$resultado->fields["monto_ofertado"];
   }  // del if de la cantidad de los renglones

 return $monto_ofertado;

} // de la funcion

//funcion que envia los mail los resultados que se han

function enviar_mail_resultados_cargados($datos){
global $db;

 $id_licitacion=$datos["id_licitacion"];
 $renglones    =$datos["renglones"];

 //obtengo el id_licitacion, y el nombre de la entidad
 $sql="select id_licitacion, entidad.nombre,simbolo,estado.nombre as nombre_estado,
      licitacion.monto_ofertado
      from  licitacion join entidad using (id_entidad)
      join moneda using(id_moneda)
      join estado using(id_estado)
      where id_licitacion=$id_licitacion
      ";
  $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
  $nombre_entidad=$resultado->fields["nombre"];
  $simbolo=$resultado->fields["simbolo"];
  $nombre_estado=$resultado->fields["nombre_estado"];
  $monto_ofertado=$resultado->fields["monto_ofertado"];
  //obtengo si tiene alternativas
  $sql=" select count(id_renglon) as cantidad from renglon where
         id_licitacion=$id_licitacion and codigo_renglon ilike '%alt%'";
  $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
  $cantidad_alternativas=$resultado->fields["cantidad"];
  if ($cantidad_alternativas>0) {
                   $text_advertencia.="\n\nAdvertencia: Hay renglones alternativos. Esto se puede traducir\n";
                   $text_advertencia.="en una falla del sistema automático de cambios de estado";
                   $advertencia=" ADVERTENCIA - ";
  }

  //obtengo el monto ofertado
  $monto_estimado=formato_money(monto_estimado_renglones($renglones));

   //obtengo los renglones que se cambio de estado
   if (sizeof($renglones)>0) {
                $and=" and ";
                for ($i=0;$i<sizeof($renglones);$i++){
                              $id_renglon=$renglones[$i];
                              if ($i==0) $condicion_renglones=" (";
                              if ($i==sizeof($renglones)-1)
                                           $condicion_renglones.=" id_renglon=$id_renglon)";
                                           else
                                           $condicion_renglones.=" id_renglon=$id_renglon or ";
                }//del for

              $sql="select titulo,codigo_renglon,cantidad from renglon
                    where id_licitacion=$id_licitacion  $and $condicion_renglones";
              $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);

             //armo el mail con los resultados
             $text="Resumen :\n";
             //renglones que gane
             for ($i=0;$i<$resultado->recordcount();$i++){
                     $text.= "Renglon:".$resultado->fields["codigo_renglon"]."\n";
                     $text.= "Descripción:".$resultado->fields["titulo"]."\n";
                     $text.= "Cantidad:".$resultado->fields["cantidad"]."\n";
                     $text.= "Estado: Presuntamente Ganado \n";
                     $text.= "\n\n";
                     $resultado->movenext();
            }
$sql_renglones=" and not $condicion_renglones";
}  //del if que controla la cantidad de renglones


  //traigo los renglones que no gane
   $sql="select titulo,codigo_renglon,cantidad from renglon
         where id_licitacion=$id_licitacion  $sql_renglones";
   $resultado=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
   for ($i=0;$i<$resultado->recordcount();$i++){
              $text.= "Renglon:".$resultado->fields["codigo_renglon"]."\n";
              $text.= "Descripción:".$resultado->fields["titulo"]."\n";
              $text.= "Cantidad:".$resultado->fields["cantidad"]."\n";
              $text.= "Estado: En Curso \n";
              $text.= "\n\n";
              $resultado->movenext();
    }



  $text.=$text_advertencia;
  $text.="\n\n";
  $text.="Resultado:\n";
  $text.="Ofertado: $simbolo $monto_ofertado\n ";
  $text.="Estimado: $simbolo $monto_estimado \n";
  $text.="Estado: $nombre_estado";

  $asunto="Licitacion $id_licitacion - $advertencia Cambio Automático de Estados";
   //enviar_mail('fernando@coradir.com.ar',$asunto,$text,$nombre_archivo,$path_archivo,$type);
   enviar_mail('juanmanuel@coradir.com.ar,adrian@coradir.com.ar',$asunto,$text,$nombre_archivo,$path_archivo,$type);

}//de la funcion enviar_mail_Resultados()

//fin de las funciones para automatizar el estado de los renglones


//funcion para enviar los mails
function enviar_mail($para,$asunto,$contenido,$adjunto,$path,$tipo,$adj=1,$para_oculto=0){
 $filename=$adjunto;
 $mailtext=$contenido;
 $mailtext .= firma_coradir();
 $filepath=$path;
 if (SERVER_OS == "windows") {
 	$nl = "\r\n";
 	$mailtext = ereg_replace("\n","\r\n",$mailtext);
 }
 else {
 	$nl = "\n";
 }
 $mail_header="";
 $mail_header .= "MIME-Version: 1.0".$nl;
 $mail_header .= "From: Sistema Inteligente de CORADIR <sistema_inteligente@coradir.com.ar>".$nl;
 $mail_header .= "Return-Path: sistema_inteligente@coradir.com.ar".$nl;
 if ($para_oculto){
     $mail_header .="Bcc: ".$para_oculto.$nl;
 }
 $mail_header .= "Content-Type: text/plain".$nl;
 $mail_header .= "Content-Transfer-Encoding: 8bit".$nl;

 return mail($para,$asunto,$mailtext,$mail_header);
}//fin funcion enviar_mail

function enviar_mail_html($para,$asunto,$contenido,$adjunto,$path,$adj=1){
 $filename=$adjunto;
 $mailtext=$contenido;
 //$mailtext.=firma_coradir_mail();
 $filepath=$path;
 if (SERVER_OS == "windows") {
 	$nl = "\r\n";
 	$mailtext = ereg_replace("\n","\r\n",$mailtext);
 }
 else {
 	$nl = "\n";
 }
 $mail_header="";
 $mail_header .= "MIME-Version: 1.0".$nl;
 $mail_header .= "From: Sistema Inteligente de CORADIR <sistema_inteligente@coradir.com.ar>".$nl;
 $mail_header .= "Return-Path: sistema_inteligente@coradir.com.ar".$nl;
 $mail_header .= "Content-Type: text/html".$nl;
 $mail_header .= "Content-Transfer-Encoding: 8bit".$nl;
 return mail($para,$asunto,$mailtext,$mail_header);
}//fin funcion enviar_mail

/*========================================================
Funcion para mandar mail a grupos determinados por el alias del mismo
==========================================================*/

function to_group($alias_g = array()){
	if (is_array($alias_g)) {
		$grupos = "'".join("','",$alias_g)."'";
	}
	elseif (strlen($alias_g) > 0) {
		$grupos = "'".$alias_g."'";
	}
	else {
		Error("No se especificó el grupo al que se debe enviar el mail");
		return;
	}
	$sql = "SELECT DISTINCT
				usuarios.mail
			FROM
				sistema.usr_mail
				LEFT OUTER JOIN sistema.grupo_mail ON (sistema.usr_mail.id_grupo = sistema.grupo_mail.id_grupo)
				LEFT OUTER JOIN sistema.usuarios ON (sistema.usr_mail.id_usuario = sistema.usuarios.id_usuario)
			WHERE
				grupo_mail.alias in ($grupos)";
	$result = sql($sql) or fin_pagina();
	$mails = array();
	while (!$result->EOF) {
		if (strlen($result->fields["mail"]) > 0) {
			$mails[]=$result->fields["mail"];
		}
		$result->MoveNext();
	}
	return (join(",",$mails));
}     // de la funcion

/* funcion que me muestra el listado de los archivos de muestras*/

function muestra_licitacion($id_licitacion){
        global $bgcolor3,$html_root;

   $sql=" select licitacion_muestra.*,archivos.nombre as nombre_archivo from
           entrega_estimada join
           licitacion_muestra using(id_entrega_estimada) join
           archivos using(idarchivo)

           where entrega_estimada.id_licitacion=$id_licitacion
           order by fecha_devolucion ASC
           ";
   $res=sql($sql) or fin_pagina();

   $sql="select simbolo from licitacion join moneda using(id_moneda)
         where id_licitacion=$id_licitacion";
   $moneda=sql($sql) or fin_pagina();

   if ($res->recordcount()>0) {
      //es que hay ordenes

      ?>
      <table width=100% align=center border=1 cellpading=0 cellspacing=0 bordercolor='<?=$bgcolor3?>'>
        <tr>
          <td colspan=3 align=Center><b>Muestras</b></td>
        </tr>
        <tr>
          <td width=20%><b>Archivo</b></td>
          <td width=15%align=center><b>F. de Devolución</b></td>
          <td align=center><b>Descripción</b></td>
        </tr>
        <?
        for($i=1;$i<=$res->recordcount();$i++){
            $id_licitacion_muestra=$res->fields["id_licitacion_muestra"];

            $link=encode_link("../../lib/archivo_muestras.php",array("id_licitacion_muestra"=>$id_licitacion_muestra,"solo_lectura"=>1));

        ?>
         <tr>
            <a href=<?=$link?> target="_blank">
            <td align=left><font color=blue> <?=$res->fields["nombre_archivo"]?></font></td>
            <td align=center ><?=fecha($res->fields["fecha_devolucion"])?>  </td>
            <td align=left>&nbsp;<?=html_out($res->fields["descripcion"])?></td>
            </a>

         </tr>
        <?
         $cont++;
         $res->movenext();

     }  // del for
     ?>
     </table>
     <?
}

}   // de la funcion


/**********************************************
FUNCIONES PARA SUBIR ARCHIVOS DE LICITACIONES
ProcForm,FileUpload,GetExt
***********************************************/
function ProcForm($FVARS,$tipo="Licitacion") {
	global $max_file_size,$extensiones,$ID,$bgcolor2,$db;
	global $html_root;
	//global $_ses_user_name;
	global $nombre_entidad;
    global $traspaso_pyme;
    global $_ses_user;

    $usuario_especial="juanmanuel";
   //$usuario_especial="fernando";

   $db->StartTrans();
   //print_r($_POST);
   //obtengo la fecha de apertura
   $sql="select fecha_apertura, simbolo from licitacion
                join moneda using (id_moneda)
                where id_licitacion=$ID";
   $result_fecha=sql($sql) or fin_pagina();//die($sql."<br>".$db->ErrorMsg());
   $fecha_apertura=substr($result_fecha->fields["fecha_apertura"],0,10);
   $simbolo=$result_fecha->fields["simbolo"];


	echo "<table border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2  align=center>";
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor2 id=ma>
                  Agregando archivos</td></tr>\n";
	$path=UPLOADS_DIR."/Licitaciones/$ID";	// linux
	$files_arr = array();
    $id_archivos_subidos = array();
        //print_r($FVARS);
	for($i=0;$i<count($FVARS["archivo"]["tmp_name"]);$i++) {

		$size=$FVARS["archivo"]["size"][$i];
		$type=$FVARS["archivo"]["type"][$i];
		$name=$FVARS["archivo"]["name"][$i];
		$temp=$FVARS["archivo"]["tmp_name"][$i];
		$ret = FileUpload($temp,$size,$name,$type,$max_file_size,$path,"",$extensiones,"",1);

                $id_tipo_archivo=$_POST["tipo_archivo"][$i];
                $sql="select tipo from tipo_archivo_licitacion
                        where id_tipo_archivo=$id_tipo_archivo";
                 $res_arch=sql($sql) or fin_pagina();
                 $archivos_tipos[]=$res_arch->fields["tipo"];

		insertar_arch_lic($ID, $name, $ret["filenamecomp"], $size, $ret["filesizecomp"], $type,$id_tipo_archivo);

                $sql="select idarchivo as id_archivo from archivos where id_licitacion = $ID and nombre = '$name'";
                $res=sql($sql) or fin_pagina();

                $id_archivos_subidos[]=$res->fields["id_archivo"];

		//$ret = 0;
		if ($ret["error"] == 0) $files_arr[$i] = $name;
	}

	if (count($files_arr) > 0) {
		if ($ID){
			$consulta="select id_licitacion, e.nombre, lider, u1.apellido||', '||u1.nombre as nombre_lider,
					patrocinador, u2.apellido||', '||u2.nombre as nombre_patrocinador
				from licitaciones.licitacion l
					left join licitaciones.entidad e using (id_entidad)
					left join sistema.usuarios u1 on (lider=u1.id_usuario)
					left join sistema.usuarios u2 on (patrocinador=u2.id_usuario)
				where id_licitacion=".$ID;
			$rta_consulta=sql($consulta, "Code2646") or fin_pagina();
		}
		for ($i = 0; $i < count($_POST["tipo_archivo"]); $i++)
		  {
               $envio=0;
		  	   $name=$FVARS["archivo"]["name"][$i];
  	           $cantidad=sizeof($_POST["avisar"][$i]);
               //manda mail si es distindo de orden de compra
               //busco el tipo de archivo en la base de datos
               $tipo_archivo=$archivos_tipos[$i];
		   if ($tipo_archivo != "Orden de Compra")
		   {
           $envio=1;
           $aviso_especial=1;
 		   $nombre_archivo=substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
		   $subido="Archivo ";
		   $asunto="Archivo Subido - $tipo ID: $ID ";
		   $contenido="$tipo ID: $ID \n";
		   $contenido.="Nombre Archivo: $nombre_archivo \n";
		   $contenido.="Archivo subido por: ".$_ses_user['name']."\n";
		   $contenido.="\nDatos de la licitación:\nLíder: ".$rta_consulta->fields["nombre_lider"]."\nPatrocinador: ".$rta_consulta->fields["nombre_patrocinador"]."\nEntidad: ".$rta_consulta->fields["nombre"]."\n";
		   $path_archivo=$path."/";

		   for($y=0;$y<$cantidad;$y++){
			  $login=$_POST["avisar"][$i][$y];
			  //control de que se envia correo a Juan Manuel
			  if ($login==$usuario_especial) $aviso_especial=0;

              $sql="select mail from usuarios where login='$login'";
			  $res=sql($sql) or fin_pagina();
			  $mail=$res->fields["mail"];
			  if ($mail!="") //echo "$mail";

				enviar_mail($mail,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);

			  }//del for que envia los mails

                  //si la fecha de apertura es menor a la fecha en que suben el archivo
           $control=0;
           if(($aviso_especial) && (compara_fechas($fecha_apertura,date('Y-m-d'))==-1))
              {
              $control=1;
			  $subido="Archivo ";
			  $asunto="Archivo Subido a Licitacion Cerrada!!! - $tipo ID: $ID \n";
              $contenido=" Licitacion Nro $ID con fecha de apertura:".fecha($fecha_apertura)."\n";
		      $contenido.=" Archivo subido por: ".$_ses_user['name']."\n";
              $contenido.=" Se subio archivo $nombre_archivo \n";
              $contenido.="\nDatos de la licitación:\nLíder: ".$rta_consulta->fields["nombre_lider"]."\nPatrocinador: ".$rta_consulta->fields["nombre_patrocinador"]."\nEntidad: ".$rta_consulta->fields["nombre"]."\n";
              $para="juanmanuel@coradir.com.ar";
              enviar_mail($para,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);

              }

              $cons="select  lider,patrocinador,u1.mail as mail_lider,u2.mail as mail_patrocinador from licitaciones.licitacion l
	          left join sistema.usuarios u1 on (lider=u1.id_usuario)
	          left join sistema.usuarios u2 on (patrocinador=u2.id_usuario)where id_licitacion=".$ID;
              $rta_cons=sql($cons, "Code2697") or fin_pagina();

              while (!$rta_cons->EOF)
              {
              	$m_lider=$rta_cons->fields["mail_lider"];
              	$m_patro=$rta_cons->fields["mail_patrocinador"];
              	//$aviso_especial=1;
 		        $nombre_archivo=substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
		        $subido="Archivo ";
			    $asunto="Archivo Subido - $tipo ID: $ID ";
			    $contenido="$tipo ID: $ID \n";
			    $contenido.="Nombre Archivo: $nombre_archivo \n";
			    $contenido.="Archivo subido por: ".$_ses_user['name']."\n";
			    $contenido.="\nDatos de la licitación:\nLíder: ".$rta_consulta->fields["nombre_lider"]."\nPatrocinador: ".$rta_consulta->fields["nombre_patrocinador"]."\nEntidad: ".$rta_consulta->fields["nombre"]."\n";
			    $path_archivo=$path."/";

				if ($control==1)
				{
				 if(($m_lider!="juanmanuel@coradir.com.ar")&&($m_lider!=$mail))
				 {
				  enviar_mail($m_lider,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				  //echo"$m_lider !Orden de Compra";
				 }
				 if(($m_patro!="juanmanuel@coradir.com.ar")&&($m_patro!=$mail))
				 {
				  enviar_mail($m_patro,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				  //echo"$m_patro";

				 }
				}
				else
				{
				 if($m_lider!=$mail)
				 {
				   enviar_mail($m_lider,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				   //echo"$m_lider !Orden de Compra";
				 }
				 if($m_patro!=$mail)
				 {
				   enviar_mail($m_patro,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				  //echo"$m_patro";
				 }
				}


			  $rta_cons->MoveNext();

			}

		  }//del if que ve que tipo de archivo es

           //si es de orden de compra las acciones son distintas
		  // if ($_POST["tipo_archivo"][$i] == "Orden de Compra") {
          if ($tipo_archivo == "Muestras"){
             $envio=1;
          	 $fecha_devolucion=fecha_db($_POST["fecha_devolucion"]);
             $descripcion=$_POST["descripcion"];
             $ID=$_POST["ID"];
             $fecha_hoy=date("Y-m-d H:i:s",mktime());

              $sql=" select id_entidad from licitacion where id_licitacion=$ID";
              $res=sql($sql) or fin_pagina();
              $id_entidad=$res->fields["id_entidad"];

              $query="select nextval('muestra_id_muestra_seq') as id_muestra";
              $id_val=sql($query) or fin_pagina();
              $id=$id_val->fields['id_muestra'];
              $query="insert into muestra(id_muestra,estado,descripcion,id_entidad,fecha_devolucion,id_licitacion)
                      values($id,0,'$descripcion',$id_entidad,'$fecha_devolucion',$ID)";
              if(sql($query,"Error:$query") or fin_pagina())
                   {

                   $usuario=$_ses_user['name'];
                   $tipo="creación";
   	               //agregamos el log de creción del reclamo de partes
   	               $query="insert into log_muestra(fecha,usuario,tipo,id_muestra)
   	                       values('$fecha_hoy','$usuario','$tipo',$id)";
                   sql($query,"Error:$query") or fin_pagina();
                   }
               $items_muestras=$_POST["items_muestras"];
	  		   $sql_max ="select nextval ('entrega_estimada_id_entrega_estimada_seq') as max";
			   $res_max=sql($sql_max,"Error:$sql_max") or fin_pagina();
		       $id_ent=$res_max->fields['max'];

			   $sql="select max(nro) from entrega_estimada where id_licitacion=".$_POST["ID"];
			   $result_max=sql($sql,"Error:$sql") or fin_pagina();

		       $sql="insert into entrega_estimada (id_entrega_estimada,nro,id_licitacion,finalizada)
                      values($id_ent,".($result_max->fields['max']+1).",".$_POST['ID'].",0)";
        	   sql($sql,"Error:$sql") or fin_pagina();




                /**/
               $sql="select nextval('licitaciones.licitacion_muestra_id_licitacion_muestra_seq') as id_licitacion_muestra ";
               $res=sql($sql,"Error:$sql") or fin_pagina();
               $id_licitacion_muestra=$res->fields["id_licitacion_muestra"];
               $id_archivo=$id_archivos_subidos[$i];

        	   $sql="insert into licitacion_muestra
                           (id_licitacion_muestra,idarchivo,id_entrega_estimada,
                            fecha_devolucion,descripcion)
                            values
                           ($id_licitacion_muestra,$id_archivo,$id_ent,'$fecha_devolucion','$descripcion')";

                sql($sql,"Error:$sql") or fin_pagina();


                //Aca insertamos la parte de orden de compra
                //para que alla compatibilidad con el resto de los modulos
                   $sql="select nextval('subido_lic_oc_id_subir_seq') as id_subir ";
                   $res=sql($sql) or fin_pagina();
                   $id_subir=$res->fields["id_subir"];

                   $sql_subir="insert into subido_lic_oc
                                (id_subir,id_licitacion,idarchivo,fecha_subido,id_entrega_estimada,vence_oc,
                                 fecha_notificacion,lugar_entrega,nro_orden,tipo_muestras)
                                 values
                                ($id_subir,$ID,$id_archivo,'$fecha_hoy',$id_ent,'$fecha_devolucion',
                                '$fecha_hoy',' ','Muestra Correspondiente a la licitación $ID',1)";
                     sql($sql_subir) or fin_pagina();



                    //ahora inserto los renglones de las muestras
                    //junto con los renglones de licitaciones

                    for ($j=0;$j<$items_muestras;$j++){
                          if ($_POST["items_muestras_$j"]==1) {
                                          $id_renglon=$_POST["id_renglon_muestras_$j"];

                                          $sql="insert into renglones_muestra
                                                (id_licitacion_muestra,id_renglon)
                                                 values
                                                 ($id_licitacion_muestra,$id_renglon)
                                                ";
                                           sql($sql,"Error:$sql") or fin_pagina();

                                           $sql="insert into renglones_oc
                                                     (id_subir,id_renglon,precio,cantidad)
                                                     values
                                                     ($id_subir,$id_renglon,0,1)
                                                        ";
                                            sql($sql,"Erro:$sql") or fin_pagina();
                                           }//del if
                         } //del for
                    //fin de la parte de muestras

              /*$cons="select  lider,patrocinador,u1.mail as mail_lider,u2.mail as mail_patrocinador
                    ,u1.apellido||', '||u1.nombre as nombre_lider,
		            u2.apellido||', '||u2.nombre as nombre_patrocinador
                    from licitaciones.licitacion l
	          		left join sistema.usuarios u1 on (lider=u1.id_usuario)
	          		left join sistema.usuarios u2 on (patrocinador=u2.id_usuario)
	          		where id_licitacion=".$ID;
              $rta_cons=sql($cons, "Code2697") or fin_pagina();

              while (!$rta_cons->EOF)
              {
              	$name=$FVARS["archivo"]["name"][$i];
              	$m_lider=$rta_cons->fields["mail_lider"];
              	$m_patro=$rta_cons->fields["mail_patrocinador"];
              	//$aviso_especial=1;
 		        $nombre_archivo=substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
		        $subido="Archivo ";
			    $asunto="Archivo Subido - $tipo ID: $ID ";
			    $contenido="$tipo ID: $ID \n";
			    $contenido.="Nombre Archivo: $nombre_archivo \n";
			    $contenido.="Archivo subido por: $_ses_user_name\n";
			    $contenido.="\nDatos de la licitación:\nLíder: ".$rta_cons->fields["nombre_lider"]."\nPatrocinador: ".$rta_cons->fields["nombre_patrocinador"]."\nEntidad: ".$rta_consulta->fields["nombre"]."\n";
			    $path_archivo=$path."/";

				enviar_mail($m_lider,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				echo"$m_lider Muestras";
				enviar_mail($m_patro,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				echo"$m_patro";
				$rta_cons->MoveNext();
			  }
               */




 }   //del if de tipo muestras



 if ($tipo_archivo == "Orden de Compra") {
 	        $envio=1;
            /*
            if ($tipo=="Presupuesto")
                    $campo_presupuesto=" ,viene_de_presupuesto=1";
            $traspaso_pyme=1;
            $sql="update licitacion set es_presupuesto=0  $campo_presupuesto where id_licitacion=$ID";
            sql($sql) or fin_pagina();
            */
			$subido="orden";
			$asunto="Orden de Compra - $tipo ID:$ID - Entidad:$nombre_entidad";
			/////////////////////////////////////////
			//aca agregar el cuerpo del mail con los detalles
			//recupero los datos q tengo q mostrar en el cuerpo del mail
			$fecha_subido=date("Y-m-d"); //fecha actual
		    $fecha_notificacion=$_POST["fecha_notificacion"];
            //$lugar_entrega=$_POST["lugar_entrega"];
            //$nro_orden=$_POST["nro_orden"];
            $id_dias=$_POST["dias"];
            $tipo_dias=$_POST["tipo_dias"];
            $items=$_POST["items"];
            $fecha_vencimiento=arma_fecha_venc($_POST["fecha_notificacion"],$id_dias,$tipo_dias);
            //$contenido.="\n";
            //$contenido.="Fecha de notificación: $fecha_notificacion \n";
            $contenido="Vencimiento de la Orden de Compra: $fecha_vencimiento \n";
            //$contenido.="Venc. especificado por el cliente: $id_dias días $tipo_dias \n";
            //$contenido.=" \n\n";
			// Agregar lider y fecha de vencinmiento
			$query="select usuarios.nombre,usuarios.apellido from usuarios join licitacion on id_usuario=lider where id_licitacion=$ID";
			$rs=sql($query) or fin_pagina();
			$contenido.="Lider: ".$rs->fields["nombre"]." ".$rs->fields["apellido"]."\n\n\n";
            //para recuperar los items q se van a comprar y q se tienen q mostar en el mail
            $contenido.="Archivo subido por ".$_ses_user['name']." \n\n";
			$contenido.="Cant  Renglón     Descripción                           Precio \n";
            $contenido.="---------------------------------------------------------------\n";
            for ($j=0;$j<$items;$j++){
                if ($_POST["items_$j"]==1) {
                        //$id_renglon=$_POST["id_renglon_$i"];
                        $codigo=$_POST["codigo_$j"];
                        $descripcion=$_POST["descripcion_$j"];
                        $precio=$_POST["precio_$j"];
                        $cantidad=$_POST["cant_$j"];
                        $contenido.="$cantidad    $codigo    $descripcion       $simbolo $precio\n";
                        $precio_total+=$precio*$cantidad;
                }
            }
            $contenido.="---------------------------------------------------------------\n";
            $contenido.="Precio total: $simbolo $precio_total ";

			///////////////////////////////////////

			$nombre_archivo=substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
			$path_archivo=$path."/";
			//hay q descomentar la funcion con el grupo y sacar la q tiene mi nombre
			enviar_mail(to_group(array("gerencia","produccion","compras","licitaciones")),$asunto,$contenido,$nombre_archivo,$path_archivo,$type);
                    //   enviar_mail("juanmanuel@coradir.com.ar",$asunto,$contenido,$nombre_archivo,$path_archivo,$type);
                    //   enviar_mail("lizi@coradir.com.ar",$asunto,$contenido,$nombre_archivo,$path_archivo,$type);
		         /************************************************
			 Este for es para que mande mail a todos los demas
			 ***********************************************/
             $cantidad=sizeof($_POST["avisar"][$i]);
                        //$aviso_especial=1;
		     for($y=0;$y<$cantidad;$y++){
  			  $login=$_POST["avisar"][$i][$y];
			  $sql=" select mail from usuarios where login='$login'";
			  $res=sql($sql) or fin_pagina();
			  $mail=$res->fields["mail"];
			  //if ($login==$usuario_especial)  $aviso_especial=0;
			  if ($mail!="") //echo "$mail";
                		enviar_mail($mail,$asunto,$contenido,$nombre_archivo,$path_archivo,$type);

				//echo $mail;
			  }//del for que envia los mails

			 /********************************************
			 fin del for que manda los mail
			 ********************************************/
             $query="update entregar_lic set orden_subida=1,".
                     "lugar_entrega_productos='".$_POST['lugar_entrega'] ."'".
                     "where id_licitacion=".$_POST["ID"];
			 sql($query) or die;

			 //aca insertamos un nuevo seguimiento poque la licitación se pasa a orden de compra

			 $sql_max ="select nextval ('entrega_estimada_id_entrega_estimada_seq') as max";
			 $res_max=sql($sql_max) or fin_pagina();
		         $id_ent=$res_max->fields['max'];

			 $sql="select max(nro) from entrega_estimada where id_licitacion=".$_POST["ID"];
			 $result_max=sql($sql) or fin_pagina();

		     $sql="insert into entrega_estimada (id_entrega_estimada,nro,id_licitacion,finalizada) values($id_ent,".($result_max->fields['max']+1).",".$_POST['ID'].",0)";
        	 sql($sql) or fin_pagina();


             // Parte de Mariela
             //inserto en tabla subido_licOC para saber la fecha en que se sube cada archivo
             //y la fecha de vencimineto de cada orden de compra para la licitacion
        	 $fecha_subido=date("Y-m-d"); //fecha actual
		     //$fecha_vencimiento=fecha_db($_POST["fecha_vencimiento"]);
             $fecha_notificacion=fecha_db($_POST["fecha_notificacion"]);
             $lugar_entrega=$_POST["lugar_entrega"];
             $nro_orden=$_POST["nro_orden"];
             $id_dias=$_POST["dias"];
             $tipo_dias=$_POST["tipo_dias"];
             $ID=$_POST["ID"];
             $items=$_POST["items"];

             $fecha_vencimiento=arma_fecha_venc($_POST["fecha_notificacion"],$id_dias,$tipo_dias);
             $fecha_vencimiento=fecha_db($fecha_vencimiento);

             $sql="select nextval('subido_lic_oc_id_subir_seq') as id_subir ";
             $res=sql($sql) or fin_pagina();
             $id_subir=$res->fields["id_subir"];
             $id_archivo=$id_archivos_subidos[$i];

        	 $sql_subir="insert into subido_lic_oc
                        (id_subir,id_licitacion,idarchivo,fecha_subido,id_entrega_estimada,vence_oc,
                         fecha_notificacion,lugar_entrega,nro_orden,id_dias,tipo_dias)
                         values
                        ($id_subir,$ID,$id_archivo,'$fecha_subido',$id_ent,'$fecha_vencimiento',
                        '$fecha_notificacion','$lugar_entrega','$nro_orden',$id_dias,'$tipo_dias')";
             sql($sql_subir) or fin_pagina();

             //ahora inserto los renglones que van a comprar
             $precio_total=0;
             for ($j=0;$j<$items;$j++){
                  if ($_POST["items_$j"]==1) {
                                      $id_renglon=$_POST["id_renglon_$j"];
                                      $precio=$_POST["precio_$j"];
                                      $cantidad=$_POST["cant_$j"];
                                      $precio_total+=$precio*$cantidad;

                                      $sql="insert into renglones_oc
                                            (id_subir,id_renglon,precio,cantidad)
                                             values
                                             ($id_subir,$id_renglon,$precio,$cantidad)
                                            ";
                                       sql($sql) or fin_pagina();
                                       insertar_estado($id_renglon,3);
                                       $cambiar_estado_lic=1;
                                       }//del if
             } //del for
                  if ($cambiar_estado_lic){
                                        //si un renglon esta en estado orden de compra actualizo el estado de la licitacion
                                        $sql="update licitacion set id_estado=7
                                              where id_licitacion=$ID";
                                        sql($sql) or fin_pagina();
                                          }

	////////////////////////////////////////////// Gabriel //////////////////////////////////////////////////
	if ($tipo=="Licitacion"){
		$rta_consulta=sql("select * from licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento where id_licitacion=".$ID, "c2947");
		if ($rta_consulta->recordCount()==0){
			$consulta="insert into licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento(id_licitacion, vencimiento_presentacion, fecha_registro)
				values($ID, ".$_POST["t_vencimiento_entrega"].", '".date("Y-m-d", strtotime("+ ".$_POST["t_vencimiento_entrega"]." days "))."')";
		}else{
			$consulta="update licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento set
				vencimiento_presentacion=".$_POST["t_vencimiento_entrega"]." where id_licitacion=$ID";
			//, fecha_registro='".date("Y-m-d", strtotime("+ ".$_POST["t_vencimiento_entrega"]." days "))."'
		}
		sql($consulta, "c-lib-1955: ".$consulta);
	}

	$cons="select  lider,patrocinador,u1.mail as mail_lider,u2.mail as mail_patrocinador
                    ,u1.apellido||', '||u1.nombre as nombre_lider,
		            u2.apellido||', '||u2.nombre as nombre_patrocinador
                    from licitaciones.licitacion l
	          		left join sistema.usuarios u1 on (lider=u1.id_usuario)
	          		left join sistema.usuarios u2 on (patrocinador=u2.id_usuario)
	          		where id_licitacion=".$ID;
                    $rta_cons=sql($cons, "Code3052") or fin_pagina();
                    $m_lider=$rta_cons->fields["mail_lider"];
              	    $m_patro=$rta_cons->fields["mail_patrocinador"];

	$con="SELECT DISTINCT
				usuarios.mail
			FROM
				sistema.usr_mail
				LEFT OUTER JOIN sistema.grupo_mail ON (sistema.usr_mail.id_grupo = sistema.grupo_mail.id_grupo)
				LEFT OUTER JOIN sistema.usuarios ON (sistema.usr_mail.id_usuario = sistema.usuarios.id_usuario)
			WHERE
				grupo_mail.alias in ('gerencia','licitaciones','produccion','compras') and usuarios.mail='$m_lider'";

	$rta_con=sql($con, "Code3065") or fin_pagina();
	if($rta_con)
	{

	}
	else
	{
		if($m_lider!=$mail)
				 {
				   enviar_mail($m_lider,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				  //echo"$m_lider Orden de Compra";
				 }

	}
	$con="SELECT DISTINCT
				usuarios.mail
			FROM
				sistema.usr_mail
				LEFT OUTER JOIN sistema.grupo_mail ON (sistema.usr_mail.id_grupo = sistema.grupo_mail.id_grupo)
				LEFT OUTER JOIN sistema.usuarios ON (sistema.usr_mail.id_usuario = sistema.usuarios.id_usuario)
			WHERE
				grupo_mail.alias in ('gerencia','licitaciones','produccion','compras') and usuarios.mail='$m_patro'";

	$rta_con=sql($con, "Code3087") or fin_pagina();
	if($rta_con)
	{

	}
	else
	{
		if($m_patro!=$mail)
				 {
				   enviar_mail($m_patro,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				   //echo"$m_patro";
				 }

	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////

                require(MOD_DIR."/licitaciones/presup_auto.php");
 } //del if de archivo == orden

 	     //if ($_POST["tipo_archivo"][$i] == "oferta") {
             if ($tipo_archivo == "Oferta") {
		  	    $query="update entregar_lic set oferta_subida=1, archivo_oferta='".$FVARS["archivo"]["name"][$i]."' where id_licitacion=".$_POST["ID"];
			     sql($query) or die;
			     } //del if de tipo archivo == oferta
	    // if ($_POST["tipo_archivo"][$i] == "gar_contrato") {
             if ($tipo_archivo == "Garantia de Contrato") {
			    $query="update entregar_lic set garantia_contrato_subida=1 where id_licitacion=".$_POST["ID"];
			    sql($query) or die;
			    //////////////////////////////////////////// GABRIEL ////////////////////////////////////////////////////
			    // cambia el estado de la garantía de contrato a "pr" (para recuperar)
			    $rta_consulta=sql("select * from licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento where id_licitacion=".$_POST["ID"], "c2947: ".$_POST["ID"]);
					if ($rta_consulta->recordCount()==0){
						$consulta="insert into licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento(id_licitacion, vencimiento_presentacion, fecha_registro)
							values($ID, ".(($_POST["t_vencimiento_entrega"])?$_POST["t_vencimiento_entrega"]:8).", '".date("Y-m-d", strtotime("+ ".(($_POST["t_vencimiento_entrega"])?$_POST["t_vencimiento_entrega"]:8)." days "))."')";
					}else{
						$consulta="update licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento set estado_garantia='pr'
							where id_licitacion=".$_POST["ID"];
					}
					sql($consulta, "c-lib-1979: ".$consulta);
			    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
			    }
			 if ($tipo_archivo == "Prorroga-Pedido/Contestación") {
	         	$sql="select id_entrega_estimada from entrega_estimada where id_licitacion=".$_POST["ID"];
			    $resultado_entrega = $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
			    $sql="insert into prorroga(id_entrega_estimada) values(".$resultado_entrega->fields['id_entrega_estimada'].")";
			    $db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
	      	    }

	      	 /* if($envio==0)
	      	  {
	      	  	$cons="select  lider,patrocinador,u1.mail as mail_lider,u2.mail as mail_patrocinador
                    ,u1.apellido||', '||u1.nombre as nombre_lider,
		            u2.apellido||', '||u2.nombre as nombre_patrocinador
                    from licitaciones.licitacion l
	          		left join sistema.usuarios u1 on (lider=u1.id_usuario)
	          		left join sistema.usuarios u2 on (patrocinador=u2.id_usuario)
	          		where id_licitacion=".$ID;
               $rta_cons=sql($cons, "Code2697") or fin_pagina();

               while (!$rta_cons->EOF)
               {
              	$name=$FVARS["archivo"]["name"][$i];
              	$m_lider=$rta_cons->fields["mail_lider"];
              	$m_patro=$rta_cons->fields["mail_patrocinador"];
              	//$aviso_especial=1;
 		        $nombre_archivo=substr($name,0,strlen($name) - strpos(strrev($name),".") - 1).".zip";
		        $subido="Archivo ";
			    $asunto="Archivo Subido - $tipo ID: $ID ";
			    $contenido="$tipo ID: $ID \n";
			    $contenido.="Nombre Archivo: $nombre_archivo \n";
			    $contenido.="Archivo subido por: $_ses_user_name\n";
			    $contenido.="\nDatos de la licitación:\nLíder: ".$rta_cons->fields["nombre_lider"]."\nPatrocinador: ".$rta_cons->fields["nombre_patrocinador"]."\nEntidad: ".$rta_consulta->fields["nombre"]."\n";
			    $path_archivo=$path."/";

				enviar_mail($m_lider,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				echo"$m_lider otros---";
				enviar_mail($m_patro,$asunto,$contenido,$nombre_archivo,$path_archivo,$type,0);
				echo"$m_patro";
				$rta_cons->MoveNext();
			   }
	      	  }*/

			    //del if de tipo archivo == gar_contrato
	}   //del for
   } // del if (count($files_arr) > 0) {
	echo "</table>\n";

$db->CompleteTrans();
}//fin de la funcion

//funcion que me arma la fecha sumando  la fecha la cantidad de dias y el tipo de dias
function arma_fecha_venc($fecha,$id_dias,$tipo_dias){

  $sql="select dias from dias_oc where id_dias=$id_dias";
  $resultado=sql($sql) or fin_pagina();
  $dias=$resultado->fields["dias"];
  $i=1;

  if ($tipo_dias=="Corridos") {
             $fecha_split=split("/",$fecha);
             $fecha_aux=date("d/m/Y",mktime(0,0,0,$fecha_split[1],$fecha_split[0] + $dias,$fecha_split[2]));
     }
     //es que son dias habiles
     else {
          //echo "Fecha Parametros:$fecha ***** $dias **********";
          $fecha_aux=$fecha;
          while($i<=$dias){
              $fecha_split=split("/",$fecha_aux);
              //print_r($fecha_split);echo"<br>";
              $fecha_aux=date("d/m/Y",mktime(0,0,0,$fecha_split[1],$fecha_split[0]+1,$fecha_split[2]));
              $no_es_domingo=date("w",mktime(0,0,0,$fecha_split[1],$fecha_split[0]+1,$fecha_split[2]));
              $sabado=date("w",mktime(0,0,0,$fecha_split[1],$fecha_split[0]+1,$fecha_split[2]));
              //echo "$fecha_aux , domingo: $no_es_domingo, i:$i<br>";
              if (!feriado($fecha_aux) && $no_es_domingo && $sabado!=6)  $i++;
         }
     }//del else
return $fecha_aux;
}//de la arma_fecha_venc


function FileUpload($TempFile, $FileSize, $FileName, $FileType, $MaxSize, $Path, $ErrorFunction, $ExtsOk, $ForceFilename, $OverwriteOk,$comprimir=1,$mostrar_carteles=1) {
	global $ID,$id_archivo;
	//global $ID,$_ses_user_name,$id_archivo;
	$retorno["error"] = 0;
	if (strlen($ForceFilename)) { $FileName = $ForceFilename; }
	//$err=`mkdir -p '$Path'`;
	mkdirs (enable_path($Path));

	if (!function_exists($ErrorFunction)) {
		if (!function_exists('DoFileUploadDefErrorHandle')) {
			function DoFileUploadDefErrorHandle($ErrorNumber, $ErrorText) {
				echo "<tr><td colspan=2 align=center><font color=red><b>Error $ErrorNumber: $ErrorText</b></font><br><br></td></tr>";
			}
		}
		$ErrorFunction = 'DoFileUploadDefErrorHandle';
	}
        if($mostrar_carteles)
	{echo "<tr><td>Nombre:</td><td>$FileName</td></tr>\n";
	 echo "<tr><td>Tamaño:</td><td>$FileSize</td></tr>\n";
	 echo "<tr><td>Tipo MIME:</td><td>$FileType</td></tr>\n";
	}
	if($TempFile == 'none' || $TempFile == '') {
		$ErrorTxt = "No se especificó el nombre del archivo<br>";
		$ErrorTxt .= "o el archivo excede el máximo de tamaño de:<br>";
		$ErrorTxt .= ($MaxSize / 1024)." Kb.";
		$retorno["error"] = 1;
		$ErrorFunction($retorno["error"], $ErrorTxt);
		return $retorno;
	}

	if(!is_uploaded_file($TempFile)) {
		$ErrorTxt = "File Upload Attack, Filename: \"$FileName\"";
		$retorno["error"] = 2;
		$ErrorFunction($retorno["error"], $ErrorTxt);
		return $retorno;
	}

	if($FileSize == 0) {
		$ErrorTxt = 'El archivo que ha intentado subir, está vacio!';
		$retorno["error"] = 3;
		$ErrorFunction($retorno["error"], $ErrorTxt);
		return $retorno;
	}

/*
	$TheExt = GetExt($FileName);

	foreach ($ExtsOk as $CurNum => $CurText) {
		if ($TheExt == $CurText) { $FileExtOk = 1; }
	}

	if($FileExtOk != 1) {
		$ErrorTxt = 'You attempted to upload a file with a disallowed extention!';
		$ErrNo = 4;
		$ErrorFunction($ErrNo, $ErrorTxt);
		return $ErrNo;
	}
*/
	if($FileSize > $MaxSize) {
		$ErrorTxt = 'El archivo que ha intentado subir excede el máximo de ' . ($MaxSize / 1024) . 'kb.';
		$retorno["error"] = 5;
		$ErrorFunction($retorno["error"], $ErrorTxt);
		return $retorno;
	}

	$FileNameFull = enable_path($Path."/".$FileName);
	$FileNameFullComp = substr($FileNameFull,0,strlen($FileNameFull) - strpos(strrev($FileNameFull),".") - 1).".zip";

	clearstatcache();
	if((file_exists($FileNameFull) || file_exists($FileNameFullComp)) && !strlen($OverwriteOk)) {
		$ErrorTxt = 'El archivo que ha intentado subir ya existe. Por favor especifique un nombre distinto.';
		$retorno["error"] = 6;
		$ErrorFunction($retorno["error"], $ErrorTxt);
		return $retorno;
	}

	move_uploaded_file ($TempFile, $FileNameFull) or die("error al mover el temporal <br> $TempFile <br> hasta <br> $FileNameFull");

	if ($comprimir) {
		$ext = strtolower(GetExt($FileNameFull));
		if ($ext != "zip") {
			$FileNameOld = $FileNameFull;
			$FileNameFull = $FileNameFullComp;
	//			$err = `/bin/pkzip -add -dir=none "$FileNameFull" "$FileNameOld"`;
			if (SERVER_OS == "linux") {
				$err = `/usr/bin/zip -j -9 -q "$FileNameFull" "$FileNameOld"`;
			} elseif (SERVER_OS == "windows"){
				$paso = ROOT_DIR."\\lib\\zip";
				$err = shell_exec("$paso\\zip.exe -j -9 -q  \"$FileNameFull\" \"$FileNameOld\"");

			} else {
				die("Error en compresión.");
			}
			//echo "<br> $TempFile <br> $FileNameFull<br> $FileNameOld<br>";
			unlink($FileNameOld);

			if ($err) {
				$ErrorTxt = "No se pudo comprimir el archivo $FileName";
				$retorno["error"] = 8;
				$ErrorFunction($retorno["error"], $ErrorTxt);
				return $retorno;
			}
		}

		$FileSizeComp=filesize($FileNameFull);
		if($mostrar_carteles)
		 echo "<tr><td>Tamaño comprimido:</td><td>$FileSizeComp</td></tr>\n";
	}
	chmod ($FileNameFull, 0600);

	if (SERVER_OS == "linux") {
		$FileNameComp = substr($FileNameFull,strrpos($FileNameFull,"/") + 1);
	} elseif (SERVER_OS == "windows"){
		$FileNameComp = substr($FileNameFull,strrpos($FileNameFull,"\\") + 1);
	} else {
		die("Error en conocer el sistema operativo.");
	}

	$retorno["filenamecomp"] = $FileNameComp;
	$retorno["filesizecomp"] = $FileSizeComp;


	if($mostrar_carteles)
	 echo "<tr><td colspan=2 align=center><b>Archivo subido correctamente!</b><br><br></td></tr>\n";


	return $retorno;
}

function GetExt($Filename) {
	$RetVal = explode ( '.', $Filename);
	return $RetVal[count($RetVal)-1];
}


/***********************************************************************
FileDownload sirve para bajar archivos, ya sea comprimidos o no

@Comp Sirve para indicar que se quiere bajar el archivo sin descomprimir
************************************************************************/
function FileDownload($Comp, $FileName, $FileNameFull, $FileType, $FileSize, $zipguardado = 1){
     //si $zipguardado es 1 significa que el archivo esta almacenado en servidor como zip

	if ($zipguardado){
		if (($Comp) or (substr($FileName,strrpos($FileName,".")) == ".zip"))
		{
			if (file_exists($FileNameFull))
			{
				Mostrar_Header($FileName,$FileType,$FileSize);
				readfile($FileNameFull);
				exit();
			}
			else
			{
				Mostrar_Error("Se produjo un error al intentar abrir el archivo comprimido");
			}
		}
		else {
			$FileNameFull = substr($FileNameFull,0,strrpos($FileNameFull,"."));

			if(SERVER_OS == "linux")
			{
				$fp = popen("/usr/bin/unzip -p \"$FileNameFull\" 2> /dev/null","r");
			}
			elseif (SERVER_OS == "windows")
			{
			   	$fp = popen(enable_path(LIB_DIR)."\\zip\\unzip.exe -p \"$FileNameFull\"","rb");
		    }

			if (!$fp)
			{
				Mostrar_Error("Se produjo un error al intentar descomprimir el archivo");
			}
			else
			{
				//echo "NAME $FileName - TYPE $FileSize - SIZE $FileSize";
				Mostrar_Header($FileName,$FileType,$FileSize);
				fpassthru($fp);
				pclose($fp);
				exit();
			}
		}
	}
	else //guardado sin comprimir
	{
		if (file_exists($FileNameFull))
		    {
				Mostrar_Header($FileName,$FileType,$FileSize);
				readfile($FileNameFull);
			}
			else
			{
				Mostrar_Error("Se produjo un error al intentar abrir el archivo comprimido");
			}
	}
}

/******************************************************************
Funciones para download de archivos de licitaciones
*******************************************************************/
function download_file($ID)
{ global $parametros;
	$FileID = $parametros["FileID"];
	$Comp = $parametros["Comp"];
	if ((!$ID) or (!$FileID)) {
		listado();
	}
	$sql = "SELECT archivos.*,licitacion.fecha_apertura ";
	$sql .= "FROM archivos ";
	$sql .= "INNER JOIN licitacion ";
	$sql .= "ON archivos.id_licitacion=licitacion.id_licitacion ";
	$sql .= "WHERE archivos.idarchivo=$FileID";
	$result = sql($sql,1001) or die();
	if ($result->RecordCount() <= 0) {
		Mostrar_Error("No se encontró el archivo");
	}
	else {
		if ($Comp) {
			$FileName=$result->fields["nombrecomp"];
			$FileType="application/zip";
			$FileSize=$result->fields["tamañocomp"];
		}
		else {
			$FileName=$result->fields["nombre"];
			$FileType=$result->fields["tipo"];
			$FileSize=$result->fields["tamaño"];
		}
        if ($result->fields['id_producto']==""){
		$fecha = substr($result->fields["fecha_apertura"],0,4);
		$sql = "SELECT entidad.nombre as nombre_entidad,";
		$sql .= "distrito.nombre as nombre_distrito ";
		$sql .= "FROM (licitacion ";
		$sql .= "INNER JOIN entidad ";
		$sql .= "ON licitacion.id_entidad=entidad.id_entidad) ";
		$sql .= "INNER JOIN distrito ";
		$sql .= "ON entidad.id_distrito=distrito.id_distrito ";
		$sql .= "WHERE licitacion.id_licitacion=$ID";
		$result = sql($sql) or die;
		$distrito = $result->fields["nombre_distrito"];
		$entidad = $result->fields["nombre_entidad"];
//		$FilePath=UPLOADS_DIR."/Licitaciones/$distrito/$entidad/$fecha/$ID";
		$FilePath=UPLOADS_DIR."/Licitaciones/$ID";

		$FileNameFull="$FilePath/$FileName";
        }
        else {
             $FilePath=UPLOADS_DIR."/folletos";
             $FileNameFull="$FilePath/$FileName";
             }

	}
	FileDownload($Comp, $FileName, $FileNameFull, $FileType, $FileSize);
}

function Mostrar_Error($msg) {
	global $bgcolor3, $ID, $cmd;
	echo "<html><body bgcolor=$bgcolor3>";
	echo "<form action='".$_SERVER["PHP_SELF"]."' method=post>\n";
	echo "<input type=hidden name=cmd1 value=detalle>\n";
	echo "<input type=hidden name=ID value=$ID>\n";
	echo "<table bgcolor=$bgcolor3 align=center width=80%>\n";
	echo "<tr><td align=center>\n";
	echo "<font size=4 color=#FF0000><b>$msg</b></font>\n";
	echo "</td></tr>\n";
	echo "<tr><td align=center>\n";
	echo "<input type=submit name=down_error value='Volver a la licitación'>\n";
	echo "</td></tr>\n";
	echo "</table></form>\n";
	echo "</body></html>";
}

/***************************************
Genera la entrada en la base de datos
en la tabla archivo para licitaciones
****************************************/
function insertar_arch_lic($ID, $FileName, $FileNameComp, $FileSize, $FileSizeComp, $FileType,$id_tipo){
    global $_ses_user;
	if ($ID) {

		$FileDateUp = date("Y-m-d H:i:s", mktime());
                error_reporting(0);
		$sql = "select idarchivo,id_licitacion,nombre from archivos where id_licitacion = $ID and nombre = '$FileName'";
		$result = sql($sql) or die;
//                $id_archivo=$result->fields["idarchivo"];

		$cant_filas = $result->RecordCount();
		if ($cant_filas == 0) {
	//		$sql = "select nombre,apellido from usuarios where login='$_ses_user_login'";
	//		$result = $db->Execute($sql) or die($db->ErrorMsg());
	//		$user_name = $result->fields["nombre"]." ".$result->fields["apellido"];
                        if (!$id_tipo) $id_tipo="NULL";
			$sql = "insert into archivos
                               (id_licitacion, nombre, nombrecomp, tamaño, tamañocomp, tipo, subidofecha, subidousuario,id_tipo_archivo)
                                values
                                ($ID, '$FileName', '$FileNameComp', $FileSize, $FileSizeComp, '$FileType', '$FileDateUp', '".$_ses_user["name"]."',$id_tipo)";

			$result = sql($sql) or die;
/*
                        $sql = "select max(idarchivo)  from archivos ";
                        $res_archivo=sql($sql) or fin_pagina();
                        $id_archivo=$res_archivo->fields["idarchivo"];
*/
		}
		else {
			$sql = "update archivos set tamaño=$FileSize, tamañocomp=$FileSizeComp, subidofecha='$FileDateUp', subidousuario='".$_ses_user["name"]."' where id_licitacion = $ID and nombre = '$FileName'";
			$result = sql($sql) or die;
		}
	}

}

function Mostrar_Header($FileName,$FileType,$FileSize) {
if (isset($_SERVER["HTTPS"])) {
	 /**
	  * We need to set the following headers to make downloads work using IE in HTTPS mode.
	  */
	header("Pragma: ");
	header("Cache-Control: ");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
//	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
}
else {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: must-revalidate");
//	header("Pragma: no-cache");
}
header("Content-Type: application/octet-stream");
header("Content-Transfer-Encoding: binary");
header("Content-Disposition: attachment; filename=\"$FileName\"");
header("Content-Description: $FileName");
header("Content-Length: $FileSize");
header("Content-Connection: close");
}



/******************************************************************
Genera la parte de eliminar archivos relacionados con la licitacion
*******************************************************************/
function genera_det_delfile($ID)
{   global $html_header,$bgcolor2;
	echo $html_header;
	if (is_array($_POST["file_id"])) {
		echo "<br><table width=70% border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
		foreach ($_POST["file_id"] as $id_archivo) {
			if ($result=sql("select nombre from archivos where idarchivo=$id_archivo")) {
				$nombre_archivo = $result->fields["nombre"];
				if (sql("delete from archivos where idarchivo=$id_archivo")) {
					$msg = "Se elimino el archivo \"$nombre_archivo\"";
				}
				else {
					$msg = "<font color='#FF0000'>No se pudo eliminar el archivo \"$nombre_archivo\"</font>";
				}
			}
			else {
				$msg = "<font color='#FF0000'>No existe ningún archivo con el ID $id_archivo</font>";
			}
			echo "<tr><td align=center bgcolor=$bgcolor2><br><b>";
			echo "<font size=3>$msg</font>";
			echo "</b></td></tr>";
		}
		echo "<tr><td>&nbsp;</td></tr>";
		echo "</table><br>\n";
	}

}

/******************************************************************
Genera la parte de agregar archivos relacionados con la licitacion
*******************************************************************/
/*
Aclaracion: no utilizar variables que comienzen con "archivo" pues interfiere en java Script
con el control de subir archivos por lo menos en esta funcion
*/
function genera_det_addfile($pagina)
{
 global $bgcolor2,$bgcolor3,$html_header,$html_root,$es_pymes;
 echo $html_header;
 
	$ID = $_POST["id"] or $ID = $_POST["ID"];
	$nombre_entidad = $_POST['nombre_entidad'];
	$files_cant = $_POST["files_cant"] or $files_cant = 1;
	cargar_calendario();
        $sql="select * from tipo_archivo_licitacion  where activo=1 order by tipo";
        $tipo_archivo=sql($sql) or fin_pagina();

	echo "<form action='".$_SERVER["PHP_SELF"]."' method=POST enctype='multipart/form-data' name=form1>\n";
	echo "<input type=hidden name=ID value='$ID'>\n";
    echo "<input type=hidden name=es_pymes value='$es_pymes'>";
	echo "<input type=hidden name='nombre_entidad' value='$nombre_entidad'>\n";
	echo "<input type=hidden name=det_addfile value='1'>\n";
	echo "<br><table border=1 cellspacing=0 cellpadding=5 bgcolor=$bgcolor2 align=center>\n";
	echo "<tr><td style=\"border:$bgcolor3;\" colspan=2 align=center id=mo><font size=3><b>Agregar archivos</b></td></tr>";
	echo "<tr>\n";
	echo "<td align=right><b>Cantidad de archivos:</b>\n";
	echo "<select name=files_cant onchange='document.forms[0].submit()'>\n";
	for ($i=1; $i<=20; $i++) {
		echo "<option value='$i'";
		if ($i == $files_cant) echo " selected";
		echo ">$i\n";
	}
	echo "</td>\n";
	echo "</tr><tr>\n";
	echo "<td align=center>\n";
	echo "<input type='hidden' name='MAX_FILE_SIZE' value='$max_file_size'>\n";
	$sql = "SELECT login,nombre,apellido FROM usuarios LEFT JOIN phpss_account ON usuarios.login = phpss_account.username WHERE phpss_account.active = 'true' ORDER BY nombre,apellido";
	$result = sql($sql) or die;
	$usuarios_arr = array();
	while (!$result->EOF) {
		$usuarios_arr[$result->fields["login"]] = $result->fields["nombre"]." ".$result->fields["apellido"];
		$result->MoveNext();
	}

        echo "<input type='hidden' name='cant_files' value='$files_cant'>";
	for ($i=0; $i<$files_cant; $i++) {
		echo "<table border=1 width=100% cellpadding=2 cellspacing=0>";
		echo "<tr><td colspan=4 style='border:$bgcolor1;'><b>Archivo ";
		echo ($i + 1).": </b>";
		echo "</td></tr>";
		echo "<tr><td align=right>";
		echo "Nombre: ";
		echo "</td><td>";
		echo "<input type=file name='archivo[$i]' size=20 onkeypress='return false'>\n";
		echo "</td><td align=right>";
		echo "Tipo: ";
		echo "</td><td>";
                if ($_POST["h_tipo_archivo_$i"]) $value_h=$_POST["h_tipo_archivo_$i"];
                                           else  $value=0;
                echo "<input type='hidden' name='h_tipo_archivo_$i' value='$value'>";
		echo "<select name='tipo_archivo[$i]' id='tipo_archivo' onchange='habilitar_datos(this,$i)';>";
        $tipo_archivo->move(0);
        for($y=0;$y<$tipo_archivo->recordcount();$y++){
                  $id_tipo_archivo=$tipo_archivo->fields["id_tipo_archivo"];
                  $tipo=$tipo_archivo->fields["tipo"];
                  if ($id_tipo_archivo==$_POST["tipo_archivo"][$i]) $selected="selected";
                                                               else $selected="";

                  echo "<option value='$id_tipo_archivo' $selected>$tipo</option>";
                  $tipo_archivo->movenext();
                }
		echo "</select>\n";
		echo "</td></tr>";

		if($pagina!="PcPower")
		{
		  echo "<tr valign=top><td align=right >";
		  echo "Avisar a: ";
		  echo "</td><td colspan=3 align=center>";

		  echo "<select multiple size=10 name='avisar[$i][]'>";
		  foreach ($usuarios_arr as $login_usuario => $nombre_usuario) {
			echo "<option value='$login_usuario'>$nombre_usuario";
                  }
          echo "</select>";

		  echo "</td>";
                echo "</tr>";
         }//de if($pagina!="PcPower")
		echo "</table>";
	}
	echo "</td>\n";
	echo "</tr>";
    echo "<tr>";
    echo "<td>";
//    echo $_POST["visible"];
    ?>
   <script>



   function habilitar_datos(select,orden) {
     var sen;
     var cant_files;
     var hay_orden=0;

    //el valor de document.all.h_tipo_archivo_$i es
    // 1 hay una select con orden de compra
    // 0 el select correspondiente no tiene orden de compra

    cant_files=parseInt(document.all.cant_files.value);
    if (select.options[select.selectedIndex].text=="Muestras")
                               {
                                Mostrar('tabla_muestras');
                                //pongo visible la tabla
                                document.all.visible_muestras.value='visible';
                                sen="document.all.h_tipo_archivo_"+orden+".value=1";
                                eval(sen);

                                }
                                else
                                {
                                //saco el select de orden y veo si no hay otra orden
                                sen="document.all.h_tipo_archivo_"+orden+".value=0";
                                eval(sen);
                                for(i=0;i<cant_files;i++)
                                          {
                                           sen="document.all.h_tipo_archivo_"+i+".value";
                                           if (eval(sen)==1) {
                                                        hay_orden=1;
                                                        break;
                                                        }
                                          } //del for
                               if (hay_orden==0) {
                                      Ocultar('tabla_muestras');
                                      document.all.visible_muestras.value='none';
                                      }//del if de hay orden
                                } //del else


    if (select.options[select.selectedIndex].text=="Orden de Compra")
                               {
                                //pongo visible la tabla
                                Mostrar('tabla_orden_de_compra');
                                document.all.visible.value='visible';
                                sen="document.all.h_tipo_archivo_"+orden+".value=1";
                                eval(sen);
                                }
                                else
                                {
                                //saco el select de orden y veo si no hay otra orden
                                sen="document.all.h_tipo_archivo_"+orden+".value=0";
                                eval(sen);
                                for(i=0;i<cant_files;i++)
                                          {
                                           sen="document.all.h_tipo_archivo_"+i+".value";
                                           if (eval(sen)==1) {
                                                        hay_orden=1;
                                                        break;
                                                        }
                                          } //del for
                               if (hay_orden==0) {
                                      Ocultar('tabla_orden_de_compra');
                                      document.all.visible.value='none';
                                      }//del if de hay orden
                                } //del else
  }  //de la  funcion habilitar_datos


  /*---------------------------------------------------------------------
  -------------- Funcion para controlar la subida de archivos------------
  -----------------------------------------------------------------------*/
    function control_files() {
    	var msg = "Faltan completar los siguientes datos:\n";
    	var err = 0;
    	for(i=0;i<document.all.form1.length;i++){
    		var obj = document.all.form1.elements[i];
    		if (obj.name.substring(0,7) == "archivo") {
    			var nro = obj.name.charAt(8);
    			if (obj.name.charAt(9) != ']') nro = nro+ obj.name.charAt(9);
    			nro++;
    			if (obj.value == "") {
    				msg = msg + "Archivo "+nro+"\n";
    				err=1;
    			}
    		}
    	}
    	if (err) {
    		alert(msg);
    		return false;
    	} else return true;
    }

  function control() {

    if (document.all.visible_muestras.value=='visible'){

                        return control_datos_muestras();
                        }
    if (document.all.visible.value=='visible')
                        {

                        return control_datos();
                        }

    return true;
    } //de la funcion control


  function funcion_aceptar_archivo_pcpower() {
       document.all.files_add.value = 'Aceptar';
       document.form1.submit();
  }


    </script>
    <?
    //echo "visibilitu:".$visibility;
    $visibility=$_POST["visible"];
    $visibility_muestras=$_POST["visible_muestras"];

    if (!$visibility) $visibility="none";
    if (!$visibility_muestras) $visibility_muestras="none";
          
    ?>
    <input type=hidden name=visible value="<?=$visibility?>">
    <input type=hidden name=visible_muestras value="<?=$visibility_muestras?>">

    <div id='tabla_orden_de_compra' style='display:<?=$visibility?>'>
    <table width=100% align=center id="tabla_fecha">
    <tr>
      <td align=center>
      <?
      if($pagina!="PcPower")
       require_once("archivo_orden_de_compra.php");

      ?>
      </td>
    </tr>
    </table>
    </div>
    <div id='tabla_muestras' style='display:<?=$visibility_muestras?>'>
    <table width=100% align=center >
    <tr>
      <td align=center>
      <?
     if($pagina!="PcPower")
       require_once("archivo_muestras.php");

      ?>
      </td>
    </tr>

    </table>
    </div>
   
  <?
    echo "</td>";
    echo "</tr>";
    echo "<tr>\n";
	echo "<td align=center colspan=2>\n";
	
	if($pagina!="PcPower")
     //$onclick=" onclick='if (control_files() && control()) { if (document.form1.visible_muestras.value=\"visible\") funcion_aceptar_muestras(); else funcion_aceptar();}'";
	 //$control_aceptar="control_files() && control_datos()";
     $onclick=" onclick='if (control_files() && control()) { if (document.form1.visible_muestras.value==\"visible\") funcion_aceptar_muestras();  else funcion_aceptar();}'";
	else
     $onclick=" onclick='if (control_files() ) funcion_aceptar_archivo_pcpower();'";
	 //$control_aceptar="control_files()";

        //echo "<input type=button name='files_add_1' value='Aceptar' onclick='if ($control_aceptar) funcion_aceptar();'>&nbsp;&nbsp;&nbsp;\n";
        echo "<input type=button name='files_add_1' value='Aceptar' $onclick>&nbsp;&nbsp;&nbsp;\n";
        echo "<input type=hidden name='files_add'>\n";


       // echo "<input type=submit name='files_add' value='Aceptar' onclick='return $control_aceptar' >&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=submit name='files_cancel' value='Cancelar'>\n";
	echo "</table>\n";
	echo "</form><br>\n";
	
}

/**************************************************
Lista los archivos subidos para la licitacion dada
@empresa indica si la empresa es coradir (el default)
         o pcpower.
***************************************************/

function lista_archivos_lic($ID,$empresa="coradir")
{global $bgcolor3,$html_root;
       echo "<table cellpadding=3 cellspacing=3 width=100%>\n";
			echo "<tr><td colspan=5 align=left></td></tr>\n";
			if($empresa=="coradir")
                           $sql="select archivos.*,tipo_archivo_licitacion.tipo as tipo_archivo
                                 from archivos
                                 left join tipo_archivo_licitacion using(id_tipo_archivo)
                                 where id_licitacion=$ID order by subidofecha DESC";
                           elseif($empresa=="pcpower")
                           $sql="select  pcpower_archivos.*,tipo_archivo_licitacion.tipo as tipo_archivo
	                             from pcpower_presupuesto.pcpower_archivos
								 left join licitaciones.tipo_archivo_licitacion using(id_tipo_archivo)
                                 where id_licitacion=$ID order by subidofecha DESC";

		        $result1 = sql($sql) or fin_pagina();
			if ($result1->RecordCount() > 0) {
				echo "<tr bgcolor=$bgcolor3>\n";
				echo "<td align=center><b>Eliminar</b></td>\n";
				echo "<td align=left><b>Nombre</b></td>\n";
                                echo "<td align=center><b>Tipo</b></td>\n";
				echo "<td align=center><b>Fecha de cargado</b></td>\n";
				echo "<td align=left><b>Cargado por</b></td>\n";
				echo "</tr>\n";
				while (!$result1->EOF) {
					$mc = substr($result1->fields["subidofecha"],5,2);
					$dc = substr($result1->fields["subidofecha"],8,2);
					$yc = substr($result1->fields["subidofecha"],0,4);
					$hc = substr($result1->fields["subidofecha"],11,5);
					$imprimir = $result1->fields["imprimir"];
					if ($imprimir == "t") $color_imprimir = "#00cc00";
					else $color_imprimir = "#cc2222";
					echo "<tr bgcolor=$bgcolor3>\n";
					echo "<td width=10% align=center bgcolor='$color_imprimir'>\n";
					echo "<input type=checkbox name=file_id[] value='".$result1->fields["idarchivo"]."'>\n";
					echo "</td>\n";
					echo "<td width=40% align=left>\n";
					echo "<a title='Archivo: ".$result1->fields["nombrecomp"]."\nTamaño: ".number_format($result1->fields["tamañocomp"]/1024)." Kb' href='".encode_link($_SERVER["PHP_SELF"],array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download","Comp"=>1))."'>\n";
					echo "<img align=middle src=$html_root/imagenes/zip.gif border=0>\n";
					echo "</a>&nbsp;&nbsp;";
					echo "<a title='Archivo: ".$result1->fields["nombre"]."\nTamaño: ".number_format($result1->fields["tamaño"]/1024)." Kb' href='".encode_link($_SERVER["PHP_SELF"],array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download"))."'>".$result1->fields["nombre"]."</a>\n";
					echo "</td>\n";
                                        echo "<td width=10% align=center>";
                                        if ($result1->fields["tipo_archivo"]){
                                             $id_archivo=$result1->fields["idarchivo"];
                                             if($empresa=="coradir"){
                                                $link=encode_link("modif_tipo_archivo.php",array("id_archivo"=>$id_archivo));
                                                if(permisos_check("inicio","permiso_boton_cambiar_tipo_arch"))
                                                echo "<input type=button name=cambiar_tipo value='C' onclick=\"window.open('$link','','toolbar=1,location=1,directories=0,status=1, menubar=1,scrollbars=1')\">";
                                             }
                                               /* elseif($empresa=="pcpower")
                                                $link=encode_link("../../licitaciones/modif_tipo_archivo.php",array("id_archivo"=>$id_archivo));
                                               */
                                             echo $result1->fields["tipo_archivo"];

                                        }
                                        echo "</td>\n";
					echo "<td width=15% align=center>$dc/$mc/$yc $hc hs.</td>\n";
					echo "<td width=25% align=left>".$result1->fields["subidousuario"]."</td>\n";
					echo "</tr>\n";
					$result1->MoveNext();
				}
			}
			else {
				echo "<tr><td colspan=5 align=center><b>No hay archivos disponibles para esta licitación</b></td></tr>\n";
			}
            echo "</table>\n";
}

function lista_archivos_lic_prorroga($ID,$empresa="coradir")
{global $bgcolor3,$html_root;
       echo "<table cellpadding=3 cellspacing=3 width=100%>\n";
			echo "<tr><td colspan=5 align=left></td></tr>\n";
			if($empresa=="coradir")
                           $sql="select archivos.*,tipo_archivo_licitacion.tipo as tipo_archivo
                                 from archivos
                                 left join tipo_archivo_licitacion using(id_tipo_archivo)
                                 where id_licitacion=$ID and (tipo_archivo_licitacion.id_tipo_archivo=9 or tipo_archivo_licitacion.id_tipo_archivo=12) order by subidofecha DESC";
                           elseif($empresa="pcpower")
                           $sql="select * from pcpower_archivos
                                 left join tipo_archivo_licitacion using(id_tipo_archivo)
                                 where id_licitacion=$ID order by subidofecha DESC";

		        $result1 = sql($sql) or fin_pagina();
			if ($result1->RecordCount() > 0) {
				echo "<tr bgcolor=$bgcolor3>\n";
				echo "<td align=center><b>Eliminar</b></td>\n";
				echo "<td align=left><b>Nombre</b></td>\n";
                                echo "<td align=center><b>Tipo</b></td>\n";
				echo "<td align=center><b>Fecha de cargado</b></td>\n";
				echo "<td align=left><b>Cargado por</b></td>\n";
				echo "</tr>\n";
				while (!$result1->EOF) {
					$mc = substr($result1->fields["subidofecha"],5,2);
					$dc = substr($result1->fields["subidofecha"],8,2);
					$yc = substr($result1->fields["subidofecha"],0,4);
					$hc = substr($result1->fields["subidofecha"],11,5);
					$imprimir = $result1->fields["imprimir"];
					if ($imprimir == "t") $color_imprimir = "#00cc00";
					else $color_imprimir = "#cc2222";
					echo "<tr bgcolor=$bgcolor3>\n";
					echo "<td width=10% align=center bgcolor='$color_imprimir'>\n";
					echo "<input type=checkbox name=file_id[] value='".$result1->fields["idarchivo"]."'>\n";
					echo "</td>\n";
					echo "<td width=40% align=left>\n";
					echo "<a title='Archivo: ".$result1->fields["nombrecomp"]."\nTamaño: ".number_format($result1->fields["tamañocomp"]/1024)." Kb' href='".encode_link($html_root."/modulos/licitaciones/licitaciones_view.php",array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download","Comp"=>1))."'>\n";
					echo "<img align=middle src=$html_root/imagenes/zip.gif border=0>\n";
					echo "</a>&nbsp;&nbsp;";
					echo "<a title='Archivo: ".$result1->fields["nombre"]."\nTamaño: ".number_format($result1->fields["tamaño"]/1024)." Kb' href='".encode_link($html_root."/modulos/licitaciones/licitaciones_view.php",array("ID"=>$ID,"FileID"=>$result1->fields["idarchivo"],"cmd1"=>"download"))."'>".$result1->fields["nombre"]."</a>\n";
					echo "</td>\n";
                    echo "<td width=10% align=center>";
                    if ($result1->fields["tipo_archivo"]){
                              $id_archivo=$result1->fields["idarchivo"];
                              $link=encode_link("$html_root/modulos/licitaciones/modif_tipo_archivo.php",array("id_archivo"=>$id_archivo));
                              if(permisos_check("inicio","permiso_boton_cambiar_tipo_arch"))
                                   echo "<table width=100% align=center border=1>";
                                      echo "<tr><td width=5% align=center>";
                                      echo "<input type=button name=cambiar_tipo value='C' onclick=\"window.open('$link','','toolbar=1,location=1,directories=0,status=1, menubar=1,scrollbars=1')\">";
                                      echo "</td>";
                                      echo "<td>";
                                      echo $result1->fields["tipo_archivo"];
                                      echo "</td>";
                                    echo "</tr>";
                                   echo "</table>";


                              }
                    echo "</td>\n";
					echo "<td width=15% align=center>$dc/$mc/$yc $hc hs.</td>\n";
					echo "<td width=25% align=left>".$result1->fields["subidousuario"]."</td>\n";
					echo "</tr>\n";
					$result1->MoveNext();
				}
			}
			else {
				echo "<tr><td colspan=5 align=center><b>No hay archivos disponibles para esta licitación</b></td></tr>\n";
			}
            echo "</table>\n";
}



function fin_pagina($debug=true,$mostrar_tiempo=true,$mostrar_consultas=true) {
	global $_ses_user,$debug_datos,$parametros;
	if ($debug and $_ses_user["debug"] == "on") {
		echo "<pre>\$debug_datos=";
		print_r($debug_datos);
		echo "</pre>";
		echo "<pre>\$parametros=";
		print_r($parametros);
		echo "</pre>";
		echo "<pre>\$_GET=";
		print_r($_GET);
		echo "</pre>";
		echo "<pre>\$_POST=";
		print_r($_POST);
		echo "</pre>";
	}
	if ($mostrar_tiempo) {
		echo "Página generada en ".tiempo_de_carga()." segundos.<br>";
	}
	if ($mostrar_consultas) {
		echo "Se utilizaron ".(count($debug_datos))." consulta/s SQL.<br>";
	}
	die("</body></html>\n");
}

function permisos_check($modulo, $item) {
	global $_ses_user;
	global $ouser;
//	if ($item=='ord_compra')
//	echo "modulo:$modulo pagina:$item<br>";	
//	print_r($_ses_user);
//	die;
//	si existe el permiso, y esta permitido y (es un permiso sin directorio o tiene directorio y es igual al modulo requerido)
//	if ($_ses_user["permisos"][$item] && $_ses_user["permisos"][$item]['allow'] && ($_ses_user["permisos"][$item]['dir']=="" || $_ses_user["permisos"][$item]['dir']==$modulo))
	if ($ouser->permisos[$item])// && ($ouser->permisos[$item]->dir=="" || $ouser->permisos[$item]->dir==$modulo))
		return true;
	else
		return false;
}

/*function permisos_actualizar() {
	global $_ses_user;
	$_ses_user["permisos"] = permisos_cargar($_ses_user["login"]);
	phpss_svars_set("_ses_user", $_ses_user);
}*/

function nombre_archivo($nombre) {
	$nombre = ereg_replace("[()]","",$nombre);
	$nombre = ereg_replace("[^A-Za-z0-9,.+-]","_",$nombre);
//	$nombre = ereg_replace("['`\"/\()<>]","",$nombre);
	return $nombre;
}

/**************************************************************************
Funcion que genera codigo de barra
/**************************************************************************/

require(LIB_DIR."/barcode/barcode.php");
require(LIB_DIR."/barcode/c128aobject.php");

//Esta funcion es exclusiva para Orden de Produccion, ya que no muestra el codigo de barra por el navegador
//Ver funcion mas abajo

function generar_codigo_barra($barcode='0123456789',$output='png',$width='460',$height='120',$xres='2',$font='5',$border='off',$drawtext='off',$stretchtext=' ',$negative='off',$redimweight='',$redimheight='')
{
global $html_root;
//Genración del Código de Barras
if (isset($barcode) && strlen($barcode)>0) {
  $style  = BCS_ALIGN_CENTER;
  $style |= ($output  == "png" ) ? BCS_IMAGE_PNG  : 0;
  $style |= ($output  == "jpeg") ? BCS_IMAGE_JPEG : 0;
  $style |= ($border  == "on"  ) ? BCS_BORDER 	  : 0;
  $style |= ($drawtext== "on"  ) ? BCS_DRAW_TEXT  : 0;
  $style |= ($stretchtext== "on" ) ? BCS_STRETCH_TEXT  : 0;
  $style |= ($negative== "on"  ) ? BCS_REVERSE_COLOR  : 0;

  $obj = new C128AObject(250, 120, $style, $barcode);

  if ($obj) {
   $obj->DrawObject($xres);

   ob_start();
	imagepng($obj->mImg);
   $buffer = ob_get_contents();
   ob_end_clean();
   return $buffer;
  }

}

}//fin funcion generar_codigo_barra

//Esta es la funcion que genera el codigo de barra para que sea mostrado por el navegador

function codigo_barra($barcode='0123456789',$output='png',$width='460',$height='120',$xres='2',$font='5',$border='off',$drawtext='off',$stretchtext=' ',$negative='off',$redimweight='',$redimheight='')
{
global $html_root;
//Genración del Código de Barras
if (isset($barcode) && strlen($barcode)>0) {
  $style  = BCS_ALIGN_CENTER;
  $style |= ($output  == "png" ) ? BCS_IMAGE_PNG  : 0;
  $style |= ($output  == "jpeg") ? BCS_IMAGE_JPEG : 0;
  $style |= ($border  == "on"  ) ? BCS_BORDER 	  : 0;
  $style |= ($drawtext== "on"  ) ? BCS_DRAW_TEXT  : 0;
  $style |= ($stretchtext== "on" ) ? BCS_STRETCH_TEXT  : 0;
  $style |= ($negative== "on"  ) ? BCS_REVERSE_COLOR  : 0;

  $obj = new C128AObject(250, 120, $style, $barcode);

  if ($redimweight!='')
   $estilo="style='width:$redimweight";
  if ($redimheight!='')
  {if ($redimweight!='')
   $estilo.=",height:$redimheight'";
   else
   $estilo="style='height:$redimheight'";
  }
  if (($redimweight!='') && ($redimheight==''))
   $estilo.='\'';

  if ($obj) {
     if ($obj->DrawObject($xres)) {
         echo "<table align='center'><tr><td><img src='$html_root/lib/barcode/image.php?code=".$barcode."&style=".$style."&type=".$type."&width=".$width."&height=".$height."&xres=".$xres."&font=".$font."' $estilo></td></tr></table>";
     } else echo "<table align='center'><tr><td><font color='#FF0000'>".($obj->GetError())."</font></td></tr></table>";
  }

}

}//fin funcion generar_codigo_barra

/**************************************************************************
Fin Funciones que genera codigo de barra
/**************************************************************************/




//funcion para corregir el path segun el sistema operativo
function enable_path($paso){
	if (($paso != "") && ((str_count_letra('/',$paso) > 0) || (str_count_letra('\\',$paso) > 0))) {
		if (SERVER_OS == "linux") {
			$ret = str_replace("\\","/",$paso);
		} elseif (SERVER_OS == "windows") {
			$ret = str_replace("/","\\",$paso);
		}
	} else $ret = $paso;

	return $ret;
}


/*****************************************************************************
Función que maneja las cuentas por default de los proveedores cuando se va a
pagar un cheque, o un egreso de caja, o un debito.

-Si la cuenta default del proveedor es cambiada en el select, envia un mail
 avisando el hecho.
-Si el proveedor no tenia cuenta por default, la cuenta elegida en el select
 pasa a ser la cuenta por default, y envia un mail avisando el hecho.

@proveedor  el id del proveedor elegido
@cuenta     el nro de cuenta elegida
@tipo_pago  string que describe si el pago fue
            hecho con un cheque, un egreso de caja
            o un débito.
            (Puede contener descripción del pago)
@nbre_prov  el nombre del proveedor elegido
******************************************************************************/
function cuenta_proveedor_default($proveedor,$cuenta,$tipo_pago,$nbre_prov)
{global $db,$_ses_user;
//	echo "$proveedor,$cuenta,$tipo_pago,$nbre_prov";die;
 $db->StartTrans();




 //obtenemos las cuentas del proveedor, si es que tiene alguna
 $query="select id_cuenta,es_default,numero_cuenta,concepto,plan from cuentas join tipo_cuenta using (numero_cuenta) where id_proveedor=$proveedor ";
 $cuentas_prov=sql($query) or fin_pagina();

 $esta_cargada=0;
 $hay_default=0;
 $cambio_default=0;$cuenta_default="";
 //recorremos las cuentas que el proveedor tiene cargado
 while (!$cuentas_prov->EOF)
 {//si la cuenta que estamos viendo es igual a la que pasaron por
  //parametro, sabemos que la cuenta ya estaba cargada para ese
  //proveedor desde antes.
  if($cuentas_prov->fields["numero_cuenta"]==$cuenta)
   $esta_cargada=1;

  //recordamos, si existe, la cuenta por default actual del proveedor
  if($cuentas_prov->fields["es_default"]==1)
  { $hay_default=1;
    $cuenta_default=$cuentas_prov->fields["concepto"]." [".$cuentas_prov->fields["plan"]."]";
    //si el Nº de cuenta pasado es diferente al Nº de la cuenta
    //por default del proveedor, seteamos $cambio_default=1
    if($cuentas_prov->fields["numero_cuenta"]!=$cuenta)
     $cambio_default=1;//debe mandar un mail en este caso
  }//de if($cuentas_prov->fields["es_default"]==1)

  $cuentas_prov->MoveNext();
 }
 //si no se encontró la cuenta pasada por parametro ($cuenta) en la lista de las cuentas
 //cargadas para $proveedor, insertamos la nueva cuenta a la lista
 //de cuentas del proveedor, solo si no tenia una por default
 if($esta_cargada==0)
 {
 	//si habia cuenta por default para este proveedor
 	//seteamos $cambio_default=1 y no insertamos nada
 	if($hay_default==1)
 	 $cambio_default=1;//en este caso tambien envia el mail.
 	else//si el proveedor no tiene cuenta por default,
 	{   //la insertamos como default, y mandamos mail avisando
 	  $query="insert into cuentas(id_proveedor,numero_cuenta,es_default)
 	          values($proveedor,$cuenta,1)";
 	  sql($query,"<br>Error al insertar cuenta por default<br>") or fin_pagina();

   //$para="juanmanuel@coradir.com.ar,noelia@coradir.com.ar";
   $para="noelia@coradir.com.ar";
   $asunto="Se agregó la cuenta por defecto al proveedor \"$nbre_prov\"";

   $texto="Al realizar $tipo_pago para el proveedor \"$nbre_prov\", se eligió una cuenta que quedó guardada por defecto para este proveedor porque antes no tenia una.\n";

   //traemos los datos de la cuenta que eligieron por la default
   $query="select concepto,plan from tipo_cuenta where numero_cuenta=$cuenta";
   $esta_vez=sql($query,"<br>Error al traer datos de cuenta no default<br>") or fin_pagina();
   $cuenta_esta_vez=$esta_vez->fields["concepto"]." [".$esta_vez->fields["plan"]."]";

  $texto.="\nLa cuenta por defecto elegida para este proveedor es: \"$cuenta_esta_vez\"\n\n";
  $texto.="Usuario que realizó esta operación: ".$_ses_user['name'];

  //echo $texto;
  enviar_mail($para,$asunto,$texto,"","","");
 }
 }
 //si se eligió una cuenta distinta de la por default, avisamos por mail
 //este hecho
 if ($cambio_default==1)
 {
  //$para="juanmanuel@coradir.com.ar,noelia@coradir.com.ar";
  $para="noelia@coradir.com.ar";
  $asunto="Se pagó a un proveedor con una cuenta que no es la guardada por defecto";

  $texto="Al realizar $tipo_pago para el proveedor \"$nbre_prov\", se eligió una cuenta que no es la que está guardada por defecto para este proveedor.\n";

  //traemos los datos de la cuenta que eligieron esta vez, en lugar de la default
  $query="select concepto,plan from tipo_cuenta where numero_cuenta=$cuenta";
  $esta_vez=sql($query,"<br>Error al traer datos de cuenta no default<br>") or fin_pagina();
  $cuenta_esta_vez=$esta_vez->fields["concepto"]." [".$esta_vez->fields["plan"]."]";

  $texto.="\nLa cuenta por defecto para este proveedor es: \"$cuenta_default\".\nLa cuenta elegida esta vez fue: \"$cuenta_esta_vez\"\n\n";
  $texto.="Usuario que realizó esta operación: ".$_ses_user['name'];

  //echo $texto;
  enviar_mail($para,$asunto,$texto,"","","");
 }

 $db->CompleteTrans();
}//de function cuenta_proveedor_default

function comprimir_variable($var) {
	$ret = "";
	if ($var != "") {
		$var = serialize($var);
		if ($var != "") {
			$gz = @gzcompress($var);
			if ($gz != "") {
				$ret = base64_encode($gz);
			}
		}
	}
	return $ret;
return base64_encode(gzcompress(serialize($var)));
}
function descomprimir_variable($var) {
	$ret = "";
	if ($var != "") {
		$var = base64_decode($var);
		if ($var != "") {
			$gz = @gzuncompress($var);
			if ($gz != "") {
				$ret = unserialize($gz);
			}
		}
	}
	return $ret;
}

function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
	global $_ses_user,$_ultimo_error,$html_root;
	$mostrar = 0;
	switch ($errno) {
		case E_USER_WARNING:
			$tipo_error = "USER_WARNING";
			$mostrar = 0;
			break;
		case E_USER_NOTICE:
			$tipo_error = "USER_NOTICE";
			$mostrar = 1;
			break;
		case E_WARNING:
			$tipo_error = "WARNING";
			$mostrar = 2;
			break;
		case E_NOTICE:
			$tipo_error = "NOTICE";
			$mostrar = 0;
			break;
		case E_CORE_WARNING:
			$tipo_error = "CORE_WARNING";
			$mostrar = 2;
			break;
		case E_COMPILE_WARNING:
			$tipo_error = "COMPILE_WARNING";
			$mostrar = 2;
			break;
		case E_USER_ERROR:
			$tipo_error = "USER_ERROR";
			$mostrar = 0;
			break;
		case E_ERROR:
			$tipo_error = "ERROR";
			$mostrar = 2;
			break;
		case E_PARSE:
			$tipo_error = "PARSE";
			$mostrar = 2;
			break;
		case E_CORE_ERROR:
			$tipo_error = "CORE_ERROR";
			$mostrar = 2;
			break;
		case E_COMPILE_ERROR:
			$tipo_error = "COMPILE_ERROR";
			$mostrar = 2;
			break;
		case 2048:
			$mostrar = 0;
	}
	if ($mostrar == 2) {
		$_ultimo_error[] = $errstr;
	}
	$msg_error = "<table width='50%' height='100%' border=0 align=center cellpadding=0 cellspacing=0>";
	$msg_error .= "<tr><td height='50%'>&nbsp;</td></tr>";
	$msg_error .= "<tr><td align=center>";
	$msg_error .= "<table border=2 width='100%' bordercolor='#FF0000' bgcolor='#FFFFFF' cellpadding=0 cellspacing=0>";
	if ($mostrar == 1) {
		if  ($_SERVER["HTTP_HOST"]=="gestion.coradir.com.ar" || $_SERVER["HTTP_HOST"]=="gestion.local") {
			$msg_error .= "<tr><td width=15% align=center valign=middle style='border-right:0'>";
			$msg_error .= "<img src=$html_root/imagenes/error.gif alt='ERROR' border=0>";
			$msg_error .= "</td><td width=85% align=center valign=middle style='border-left:0'>";
			$msg_error .= "<font size=2 color=#000000 face='Verdana, Arial, Helvetica, sans-serif'><b>";
			$msg_error .= "SE HA PRODUCIDO UN ERROR EN EL SISTEMA<br>";
			$msg_error .= "El error fue notificado a los programadores y sera solucionado a la brevedad<br>";
			$msg_error .= "</b></font>";
			$msg_error .= "</td></tr>";
		}
		else {
			$msg_error .= "TIPO:$tipo_error<br>";
			$a = explode("\t\n\t",$errstr);
			if (substr($a[0],0,2) == "a:") {
				$a[0] = unserialize($a[0]);
			}
				echo "DESCRIPCION:<pre>";
				if (is_array($a[0])) {
					print_r($a[0]);
				}
				else {
					echo $a[0];
				}
				echo "</pre><br>";
				echo "ARCHIVO:".$a[1]."<br>";
				echo "LINEA:".$a[2]."<br>";
			if (count($_ultimo_error) > 0) {
				echo "ERRORES:<pre>";
				print_r($_ultimo_error);
				echo "</pre>";
				$_ultimo_error = array();
			}
			echo "USUARIO:".$_ses_user["name"]."<br>";
		}
		$msg_error .= "</table></td></tr>";
		$msg_error .= "<tr><td height='50%' align='center'>";
		/*$link_volver = "";
		if ($_SERVER["REQUEST_URI"] != "") {
			$link_volver .= $_SERVER["REQUEST_URI"];
		}
		elseif ($_SERVER["HTTP_REFERER"] != "") {
			$link_volver .= $_SERVER["HTTP_REFERER"];
		}
		if ($link_volver == "") {
			$msg_error .= "&nbsp;";
		}
		else {*/
			//$msg_error .= "<input type=button value='Volver' onClick=\"document.location='$link_volver';\" style='width:100px;height:30px;'>";
			$msg_error .= "<input type=button value='Volver' onClick=\"history.back();\" style='width:100px;height:30px;'>";
		//}
		$msg_error .= "</td></tr>";
		$msg_error .= "</table>\n";
		echo $msg_error;
		//phpinfo();
	}
}

function reportar_error($descripcion,$archivo,$linea) {
	if (is_array($descripcion)) {
		$descripcion = serialize($descripcion);
	}
	trigger_error($descripcion."\t\n\t".$archivo."\t\n\t".$linea);
	//fin_pagina();
	exit();
}


//////////////////////////////////////ACA VOY A PONER LA FUNCION QUE NO
//////////////////////////////////////LE GUSTO A NADIE, PERO A MI SIIIII.
function busca_segundo_nivel($arreglo,$indice,$argumento,$operador="==")
{$cant=count($arreglo);
 $indi=0;
 $control=0;
 $arreglo_retorno=array();
 while ($indi<$cant)
       {eval('$condicion=($arreglo[$indi][$indice]'. $operador .'$argumento);');
        if ($condicion)
           {$arreglo_retorno[$control]=$indi;
            $control++;
           }
        $indi++;
       }
 return $arreglo_retorno;
}//function busca_segundo_nivel

/////////////////////////////////////////////////////////////////////////////


///////////////////////////////PARA ELIMINAR ELEMENTOS REPETIDOS EN UN ARRAY////////////////////////////////////
///////////////////////////////BROGGI///////////////////////////////////////////////////////////////////////////
//si retorna_en = 1 la salida es es un arreglo
//si retorna_en = 0 la salida es es un string
function elimina_repetidos($entrada,$retorna_en=1)
{$copia=array();
 $tamaño=count($entrada);
 $indice=0;
 $indice_copia=0;
 while ($indice<$tamaño)
       {$auja=$entrada[$indice];
        $entrada[$indice]="";
        if (in_array($auja,$entrada))
           {
           }
        else {$copia[$indice_copia]=$auja;
              $indice_copia++;
             }
        $indice++;
       }
 if ($retorna_en==1) return $copia;
 else {$tamaño=count($copia);
       $indice=0;
       $string=$copia[$indice];
       $indice++;
       while ($indice<$tamaño)
             {$string.=",".$copia[$indice];
              $indice++;
             }
       return $string;
      }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function isIPIn($ip,$net,$mask) {
   $lnet=ip2long($net);
   $lip=ip2long($ip);
   $binnet=str_pad( decbin($lnet),32,"0","STR_PAD_LEFT" );
   $firstpart=substr($binnet,0,$mask);
   $binip=str_pad( decbin($lip),32,"0","STR_PAD_LEFT" );
   $firstip=substr($binip,0,$mask);
   return(strcmp($firstpart,$firstip)==0);
}

function getIP() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
       $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    elseif (isset($_SERVER['HTTP_VIA'])) {
       $ip = $_SERVER['HTTP_VIA'];
    }
    elseif (isset($_SERVER['REMOTE_ADDR'])) {
       $ip = $_SERVER['REMOTE_ADDR'];
    }
    else {
       $ip = "unknown";
    }
	return $ip;
}

/**
 * Carga en las variables de sesion los datos del usuario
 *
 * @param string $username login de usuario
 */
function cargar_user($username)
{
	global $ouser;
	$ouser=new user($username);

	$user = array(
		"id"		=> $ouser->id_usuario,
		"login"		=> $ouser->login,
		"name"		=> $ouser->nombre." ".$ouser->apellido,
		"mail"		=> $ouser->mail,
		"home"		=> $ouser->pagina_inicio,
		"res_width" => $_POST["resolucion_ancho"],
		"res_height"=>$_POST["resolucion_largo"],
		"acceso1"	=> $ouser->accesos[0],
		"acceso2"	=> $ouser->accesos[1],
		"acceso3"	=> $ouser->accesos[2],
		"acceso4"	=> $ouser->accesos[3],
    "acceso5"	=> $ouser->accesos[4],
    "acceso6"	=> $ouser->accesos[5],
    "acceso7"   => $ouser->accesos[6],
    "acceso8"   => $ouser->accesos[7],
    "acceso9"   => $ouser->accesos[8],
    "acceso10"   => $ouser->accesos[9],
    "acceso11"   => $ouser->accesos[10],
    "acceso12"   => $ouser->accesos[11],
		"debug"		=> "off"
//		"permisos"	=> permisos_cargar($ouser)
	);

	phpss_svars_set("_ses_user", $user);
}


/**
 * Guarda los permisos de un usuario en la tabla permisos_actualizar
 *
 * @param id de usuario o arreglo con id de usuarios
 */
function actualizar_permisos_bd($id) {
if (!is_array($id)) {
$array_usuario[0]=$id;
}
else $array_usuario=$id;

foreach($array_usuario as $key => $id_usuario) {
$sql_login="select login from sistema.usuarios where id_usuario=$id_usuario";
$res_login=sql($sql_login) or fin_pagina();
$login=$res_login->fields['login'];

$usuario=new user($login);

$arbol=new ArbolOfPermisos("root");	
$arbol->createMenu($usuario);
ob_start();
$arbol->saveXMLMenu();
$menu_new=ob_get_contents();
$menu_guardar=comprimir_variable($menu_new);
ob_clean();
//guardar permisos
$query="select data from permisos.permisos_sesion
		where id_usuario=$id_usuario";
$result=sql($query,"<br>Error <br>") or fin_pagina();

if ($result->RecordCount()>0) {
    $query="update permisos.permisos_sesion set data='$menu_guardar'
            where id_usuario=$id_usuario";
}
else {
	$query="insert into permisos.permisos_sesion (id_usuario,data) values($id_usuario,'$menu_guardar')";
}

sql($query,"<br>Error al insertar/actualizar los permisos actualizados para el usuario<br>") or fin_pagina();
}
}

//obtener los id de usuarios que tienen permiso
// al permiso que se esta borrando y a sus hijos

function obtener_id_usuarios($id) {
	$sql="select uname from permisos.permisos where id_permiso=$id";
	$res=sql($sql) or die();
	$nombre=$res->fields['uname'];
	ob_start();
    
	$arbol=new ArbolOfPermisos($nombre);
    $arbol->createTree();
    $arbol->saveXML();
    ob_end_clean(); 
    $hijos=array();
    $cant_hijos=$arbol->childcount();

    for ($i=0;$i<$cant_hijos;$i++){
      $hijos[$i]=$arbol->getChild($i)->get_id();
    }
    $hijos[$cant_hijos]=$id; //agrego el id del item seleccinado
   
    $sql="select distinct id_usuario
		  from 
		  (select distinct id_usuario from permisos.permisos_usuarios where id_permiso in (".join(",",$hijos).")
		  union
		  select distinct id_usuario from permisos.grupos_usuarios 
		  join permisos.permisos_grupos using (id_grupo)
		  where id_permiso in (".join(",",$hijos).")) as total";
    $res=sql($sql,"$sql") or fin_pagina();
    
    $usuarios=array();
    $i=0;
    while(!$res->EOF) {
       $usuarios[$i++]=$res->fields['id_usuario'];
       $res->MoveNext();	
    }
  
    return $usuarios;
    
}

/*******************************************
 ** Autenticar el usuario
 *******************************************/

//set_error_handler('errorHandler');
require_once(MOD_DIR."/permisos/permisos.class.php");
if (isset($_POST["loginform"])) {
	// Verificar que el ip sea valido
	$myip = getIP();
	$ip_permitida = false;
	foreach ( $ip_permitidas as $k=>$v ) {
		list($net,$mask)=split("/",$k);
		if (isIPIn($myip,$net,$mask)) {
			$ip_permitida = true;
		}
	}
	
	if (!$ip_permitida) {
		$acceso_remoto = false;
		$sql = "select login from usuarios where acceso_remoto=1";
		$result = sql($sql) or die("No se pudo verificar el usuario");
		while (!$result->EOF) {
			if ($result->fields['login'] == $_POST['username'])
				$acceso_remoto = true;
			$result->MoveNext();
		}
		if (!$acceso_remoto) {
			//Registrar el evento para bloquear la cuenta a los 3 intentos
			//phpss_event("session_create_fail", array("username" => $_POST['username'], "password" => $_POST['password']));
			//Mostrar error
			echo "<html><head><link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>\n";
			echo "</head><body bgcolor=\"$bgcolor3\">\n";
			echo "<table width='60%' height='100%' border=2 align=center cellpadding=5 cellspacing=5 bordercolor=$bgcolor3>";
			echo "<tr><td height='50%'>&nbsp;</td></tr>";
			echo "<tr><td align=center bordercolor=#FF0000 bgcolor=#FFFFFF>";
			echo "<table border=0 width='100%'>";
			echo "<tr><td width=15% align=center valign=middle>";
			echo "<img src=$html_root/imagenes/warning.jpg alt='ADVERTENCIA' border=0>";
			echo "</td><td width=85% align=center valign=middle>";
			echo "<font size=5 color=#000000 face='Verdana, Arial, Helvetica, sans-serif'><b>";
			echo "USTED ESTA TRATANDO DE ACCEDER DESDE UNA UBICACION NO PERMITIDA</b></font>";
			echo "</td></tr></table>";
			echo "</td></tr>";
			echo "<tr><td height='50%'>&nbsp;</td></tr>";
			echo "</table></body></html>\n";
			$para = "nazabal@coradir.com.ar,juanmanuel@coradir.com.ar";
			$asunto = "Intento de conexion externa bloqueado";
			$texto = "Login: ".$_POST['username']." desde el IP $myip<br>";
			$texto .= "Fecha: ".date("d/m/Y H:i:s")."<br>";
			enviar_mail_html($para,$asunto,$texto,"","","");			
			exit;			
		}
	}

	$status = phpss_login($_POST['username'], $_POST['password']);
	// check if the user is allowed access
	if ($status <= 0) {
	// check the error code
		switch ($status) {
			case PHPSS_LOGIN_AUTHFAIL:
				Error("Su nombre de usuario o contraseña son incorrectos");
				break;
			case PHPSS_LOGIN_IPACCESS_DENY:
				Error("No se permite iniciar sesión desde su dirección IP");
				break;
			case PHPSS_LOGIN_BRUTEFORCE_LOCK_ACCOUNT:
				Error("Esta cuenta ha sido bloqueada debido a varios intentos fallidos de inicio de sesión");
				break;
			case PHPSS_LOGIN_BRUTEFORCE_LOCK_SRCIP:
				Error("No se puede iniciar sesión desde su dirección IP porque hubieron muchos intentos fallidos de inicio de sesión");
				break;
			default:
				Error("Valor de retorno desconocido cuando se intentaba autenticar el usuario");
		}

	   include_once(ROOT_DIR."/login.php");
	   exit;
	}
	

	//Carga las variables de sesion para el usuario
	cargar_user($_POST["username"]);
    $sql = "SELECT dia,mes,anio,descripcion FROM feriados";

	$result = sql($sql) or fin_pagina();
	while (!$result->EOF) {
		$feriados[$result->fields["anio"]."-".$result->fields["mes"]."-".$result->fields["dia"]][] = $result->fields["descripcion"];
		$result->MoveNext();
	}
	phpss_svars_set("_ses_feriados", $feriados);

	if (!$ip_permitida) {
		$para = "nazabal@coradir.com.ar,juanmanuel@coradir.com.ar";
		$asunto = "Usuario conectado usando una IP insegura";
		$texto = "Se ha conectado el usuario ".$_ses_user["login"]."(".$_ses_user["name"].") desde el IP $myip<br>";
		$texto .= "Fecha: ".date("d/m/Y H:i:s")."<br>";
		enviar_mail_html($para,$asunto,$texto,"","","");
	}
	unset($myip,$ip_permitida,$k,$v,$net,$mask,$para,$asunto,$texto);
	unset($user, $feriados, $sql, $result);
	header("Location: $html_root/index.php");
}


/*******************************************
 ** Variables Utiles
 *******************************************/

// Tamaño máximo de los archivos a subir
$max_file_size = get_cfg_var("upload_max_filesize");  // Por defecto deberia se 5 MB

// Para usar con los resultados boolean de la base de datos
$sino=array(
	"0" => "No",
	"f" => "No",
	"false" => "No",
	"NO" => "No",
	"n" => "No",
	"1" => "Sí",
	"t" => "Sí",
	"true" => "Sí",
	"SI" => "Sí",
	"s" => "Sí"
);
// Para el formato de fecha
$dia_semana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

// El tipo de resultado debe ser n para que funcione la
// libreria phpss
db_tipo_res("d");

Autenticar();

$GLOBALS["parametros"] = decode_link($_GET["p"]);

if ($_POST['cambiar_usr'])
{
	$usr_login=$_ses_user['login'];
	cargar_user($_POST['new_login']);
	$_ses_user['original_usr']=$usr_login;
	phpss_svars_set("_ses_user", $_ses_user);
}
elseif ($parametros['restaurar_usr'])
{
	cargar_user($_ses_user['original_usr']);
//	phpss_svars_set("_ses_user", $_ses_user);
}
elseif (!$ouser)
	$ouser=new user($_ses_user['login']);
	
//if (is_array($_ses_user)) {
	//$_ses_user_login = $_ses_user["login"];
	//$_ses_user_name = $_ses_user["name"];
//	$_ses_user_mail = $_ses_user["mail"];
	//$_ses_user_home = $_ses_user["home"];
//	$_ses_user_acceso1 = $_ses_user["acceso1"];
//	$_ses_user_acceso2 = $_ses_user["acceso2"];
//	$_ses_user_acceso3 = $_ses_user["acceso3"];
//	$_ses_user_acceso4 = $_ses_user["acceso4"];
//    $_ses_user_acceso5 = $_ses_user["acceso5"];
//    $_ses_user_acceso6 = $_ses_user["acceso6"];
//    $_ses_user_acceso7 = $_ses_user["acceso7"];
//    $_ses_user_acceso8 = $_ses_user["acceso8"];
//    $_ses_user_acceso9 = $_ses_user["acceso9"];
//    $_ses_user_acceso10 = $_ses_user["acceso10"];
//    $_ses_user_acceso11 = $_ses_user["acceso11"];
//    $_ses_user_acceso12 = $_ses_user["acceso12"]; 
//}

//$GLOBALS["parametros"] = decode_link($_GET["p"]);

verificar_permisos();

define("lib_included","1");
require("fns.gacz.php");

//if ($_SERVER['SCRIPT_NAME']=='/permisos/modulos/admin/usuarios_perfil.php') {
//  echo "_ses_cambiar_perfil_usuario=".$_ses_cambiar_perfil_usuario;
//  echo "<br>parametros['cmd']=".$parametros['cmd'];
//  echo "<br>_POST[cmd]=".$_POST["cmd"];
//  echo "<br>_POST['cambiar_pagina_inicio']=". $_POST['cambiar_pagina_inicio'];
//  echo "<br>_ses_cambiar_acceso=". $_ses_cambiar_acceso;
//  echo "<br>_ses_pagina_inicio=". $_ses_pagina_inicio;
//}


if ($_ses_cambiar_perfil_usuario==1 && !($parametros['cmd']=="cambiar_acceso" || $parametros['cmd']=="actualizar_item" ||
      $_POST["cmd"] == "cambiar_acceso" || $_POST['cambiar_pagina_inicio'] || $_ses_pagina_inicio || $_ses_cambiar_acceso || 
      $_POST["guardar_perfil_uid_usuario >= 117suario"]))
    phpss_svars_set("_ses_cambiar_perfil_usuario", "");
   
if ($_ses_pagina_inicio) {
phpss_svars_set("_ses_pagina_inicio", "");

//phpss_svars_set("_ses_cambiar_perfil_usuario", 1); //para que no actualize la ruta cuando cambia la pagina
$link=encode_link($html_root."/modulos/admin/usuarios_perfil.php",array("pagina_item"=>$_SERVER["REQUEST_URI"],"cmd"=>"actualizar_item"));
header("Location:$link");
exit;
}

if ($_ses_cambiar_acceso) {
phpss_svars_set("_ses_cambiar_acceso", "");
//phpss_svars_set("_ses_cambiar_perfil_usuario", 1); //para que no actualize la ruta cuando cambia la pagina
$link=encode_link($html_root."/modulos/admin/usuarios_perfil.php",array("pagina_item"=>$_SERVER['REQUEST_URI'],"cmd"=>"cambiar_acceso"));
header("Location:$link");
exit;
}

?>
