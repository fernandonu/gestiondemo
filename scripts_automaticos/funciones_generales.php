<?
/*
AUTOR: nazabal
MODIFICADO POR:
$Author: mari $
$Revision: 1.24 $
$Date: 2006/12/22 11:42:52 $
*/


define(LIB_DIR, dirname(__FILE__)."/../lib");                          // Librerias del sistema
require_once(LIB_DIR."/adodb/adodb.inc.php");
require_once(LIB_DIR."/adodb/adodb-pager.inc.php");

// Chequea la version del sistema operativo en el que se esta
// ejecutando la pagina y define la constante SERVER_OS
if (ereg("Win32",$_SERVER["SERVER_SOFTWARE"]) ||
    ereg("Microsoft",$_SERVER["SERVER_SOFTWARE"]))
	define("SERVER_OS", "windows");
else
	define("SERVER_OS", "linux");

$db_type = 'postgres7';
if ($_SERVER['SCRIPT_FILENAME'] == "/extra/admin/gestion/scripts_automaticos/".basename($PHP_SELF)) {
	$db_host = 'localhost';			// Host de la página.
}
else {
	$db_host = 'devel.local';		// Host para desarrollo.
}

$db_user = 'projekt';
$db_password = 'propcp';
$db_name = 'gestion';
$db_schemas = array(
	"bancos",
	"compras",
	"general",
	"internet",
	"licitaciones",
	"ordenes",
	"mensajes",
	"permisos",
	"sistema",
	"facturacion",
    "caja",
	"personal",
	"casos",
	"maquinas",
	"encuestas",
	"calidad",
	"reclamo_partes",
	"muestras",
	"mov_material",
	"reporte_tecnico",
	"tareas_divisionsoft",
	"stock",
    "remito_interno",
    "comidas",
    "pcpower_presupuesto",
    "licitaciones_datos_adicionales",
    "asientos",
    "compras_adicional",
    "pymes",
    "contabilidad"
);

$db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
$db->cacheSecs = 3600;
$result = $db->Execute("SET search_path=".join(",",$db_schemas)) or die($db->ErrorMsg());
unset($result);

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
	$firma.="San Luis: Tel/Fax: (02652)431134 y rotativas\n";
	$firma.="Dirección: San Martín 454 (B5700BQJ)\n";
	$firma.="Bs.As.: Tel/Fax: (011)5354-0300 y rotativas\n";
	$firma.="Dirección: Patagones 2538 - Parque Patricios - (C1071AAI)\n";
	$firma.="e-mail: info@coradir.com.ar\n";
	$firma.="página: www.coradir.com.ar\n";

	return "\n".$firma."\n".$confiden;
}

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
$firma.="San Luis: Tel/Fax: (02652)431134 y rotativas <br>\n";
$firma.="Dirección: San Martín 454 (B5700BQJ) <br>\n";
$firma.="Bs.As.: Tel/Fax: (011)5354-0300 y rotativas <br>\n";
$firma.="Dirección: Patagones 2538 - Parque Patricios - (C1071AAI)<br>\n";
$firma.="e-mail: info@coradir.com.ar<br>\n";
$firma.="página: www.coradir.com.ar<br>\n";

return "<br>\n".$firma."<br>\n".$confiden."</body></html>\n";

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
}//function compara_fechas

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

//formato de fecha dd/mm/año
function dia_anterior_x($fecha,$x){
$fecha_aux=$fecha;

for ($i=0;$i<$x;$i++){
  $fecha_total=split("/",$fecha_aux);
  $dfecha=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
  $fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
}
$fecha_retornar=split("/",$fecha_aux);
$a=date("d/m/Y",mktime(0,0,0,$fecha_retornar[1],$fecha_retornar[0],$fecha_retornar[2]));
return $a;
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
}//de function elimina_repetidos($entrada,$retorna_en=1)


//fecha d/m/Y
function dia_habil_anterior($fecha){
$fecha_aux=$fecha;
$feriado=0;
$dia_anterior=0;

while(!$dia_anterior) {
  $fecha_total=split("/",$fecha_aux);
  $dfecha=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
  $fecha_aux=date("d/m/Y/w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-1,$fecha_total[2]));
  $fecha_test=split("/",$fecha_aux);
  if($fecha_test[3]!=0 && !feriado($dfecha))
      $dia_anterior=1;
}

$fecha_retornar=split("/",$fecha_aux);
$a=date("d/m/Y",mktime(0,0,0,$fecha_retornar[1],$fecha_retornar[0],$fecha_retornar[2]));

return $a;

}

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


?>