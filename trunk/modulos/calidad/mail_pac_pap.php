<?php
/*
AUTOR: Quique (28/10/2005)
MODIFICADO POR:
$Author: enrique $
$Revision: 1.2 $
$Date: 2005/11/23 13:49:45 $
*/




//$LIB_DIR = "/extra/admin/gestion/lib";                          // Librerias del sistema
$LIB_DIR = "../../lib";                          // Librerias del sistema
require_once($LIB_DIR."/adodb/adodb.inc.php");
require_once($LIB_DIR."/adodb/adodb-pager.inc.php");
$db_type='postgres7';
$db_host = 'gestion.local';                    // Host de la pána.
$db_host = 'devel.local';                    // Host de la pána.

$db_user = 'projekt';                           // Usuario.
$db_password = 'propcp';                        // Contraseña
$db_name = 'gestion';
$db_schemas = array("licitaciones");
$db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
$db->cacheSecs = 3600;
$result=$db->Execute("SET search_path=".join(",",$db_schemas)) or die($db->ErrorMsg());
unset($result);
$db->debug = $db_debug;

//////////////////////////////////////////////
//cálculo de la fecha "hoy + 1 semana"
$fecha_d=date("d");
$fecha_m=date("m");
$fecha_y=date("Y");
$fecha_inicio=date("Y-m-d", strtotime($fecha_y."-".$fecha_m."-".$fecha_d));
$fecha_d=($fecha_d+5);
	
$fecha_limite=date("Y-m-d", strtotime($fecha_y."-".$fecha_m."-".$fecha_d));

/////////////////////////////////////////////

$consulta="select id_pac_pap,fecha_cierre,accion_inmediata,causa_nc,fecha_verificacion,verificacion,calidad.pac_pap.descripcion,accion_correctiva,area,calidad.no_conformidad.descripcion as desc 
from calidad.pac_pap join calidad.no_conformidad using (id_no_conformidad)";

$rta_consulta=$db->Execute($consulta)or die("c46: ".$consulta);
$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";


while ($fila=$rta_consulta->fetchRow())
	{
	$fvenci=$fila["fecha_cierre"];	
	$fveri=$fila["fecha_verificacion"];	
	$verificacion=$fila["verificacion"];	
	$f_cierre=Fecha($fvenci);
	$f_ver=Fecha($fveri);
	$id_pac_pap=$fila["id_pac_pap"];
	$fecha=date("Y-m-d",mktime());
	$mail_usu="juanmanuel@coradir.com.ar";
	if ($verificacion==1)
	{
		$si="SI";	
	}
	else 
	{
		$si="NO";	
	}
	
	
	
	if(compara_fechas($fecha,$fvenci)==0)
    {
	 	 $contenido_lic="Id PAC/PAP  $id_pac_pap \n\n";
	 	 $contenido_lic.="Fecha de cierre  $f_cierre \n\n";
		 $contenido_lic.="No Conformidad: \n\n";
		 $contenido_lic.=" ".$fila["desc"]."\n\n";
		 $contenido_lic.="Area: ".$fila["area"]."\n\n";
		 $contenido_lic.="Descripcion: \n\n";
		 $contenido_lic.=" ".$fila["descripcion"]."\n\n";
		 $contenido_lic.="Causa/s de la no conformidad:  \n\n";
		 $contenido_lic.=" ".$fila["causa_nc"]." \n\n";
	 	 $contenido_lic.="Acción inmediata  \n\n";
	 	 $contenido_lic.=" ".$fila["accion_inmediata"]." \n\n";
	 	 $contenido_lic.="Accion Correctiva: \n\n";
	 	 $contenido_lic.=" ".$fila["accion_correctiva"]." \n\n";
	 	 $asunto="Notificación de la fecha de cierre";
		 enviar_mail( $mail_usu, $asunto, $contenido_lic, "", "", "", 0);
    	 //echo"$contenido_lic";
    
    }

    if(compara_fechas($fecha,$fveri)==0)
    {
	 	 $contenido_lic="Id PAC/PAP  $id_pac_pap \n\n";
	 	 $contenido_lic.="Fecha de cierre  $f_cierre \n\n";
	 	 $contenido_lic.="Fecha de verificacion  $f_ver \n\n";
		 $contenido_lic.="No Conformidad:\n\n";
		 $contenido_lic.=" ".$fila["desc"]."\n\n";
		 $contenido_lic.="Area: ".$fila["area"]."\n\n";
		 $contenido_lic.="Descripcion: \n\n";
		 $contenido_lic.=" ".$fila["descripcion"]."\n\n";
		 $contenido_lic.="Causa/s de la no conformidad:  \n\n";
		 $contenido_lic.=" ".$fila["causa_nc"]." \n\n";
	 	 $contenido_lic.="Acción inmediata  \n\n";
	 	 $contenido_lic.=" ".$fila["accion_inmediata"]." \n\n";
	 	 $contenido_lic.="Accion Correctiva: \n\n";
	 	 $contenido_lic.=" ".$fila["accion_correctiva"]." \n\n";
	 	 $contenido_lic.="$si se implantó acción correctiva  \n\n";
	 	 $asunto="Notificación de la fecha evaluación eficacia";
		 enviar_mail( $mail_usu, $asunto, $contenido_lic, "", "", "", 0);
    	 //echo"$contenido_lic";
    
    }       
		
}


	
	
//funcion para enviar los mails
function enviar_mail($para,$asunto,$contenido,$adjunto,$path,$tipo,$adj=1,$para_oculto=0){
	$filename=$adjunto;
 	$mailtext=$contenido;
 	$filepath=$path;
 	$boundary = strtoupper(md5(uniqid(time())));
 	$mail_header="";
 	$mail_header .= "MIME-Version: 1.0";
 	$mail_header .= "\nFrom: Sistema Inteligente de CORADIR <sistema_inteligente@coradir.com.ar>";
 	$mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
 	//$mail_header .="\nTo: $para";
 	if ($para_oculto){
  	$mail_header .="\nBcc:$para_oculto";
    // die($mail_header);
 	}
 	$mail_header .= "\nContent-Type: multipart/mixed; boundary=$boundary";
 	$mail_header .= "\n\nThis is a multi-part message in MIME format ";
 	// Mail-Text
 	$mail_header .= "\n--$boundary";
 	$mail_header .= "\nContent-Type: text/plain";
 	$mail_header .= "\nContent-Transfer-Encoding: 8bit";
 	$mail_header .= "\n\n" . $mailtext."\n";
 	// Your File
 	$mail_header .= "\n--$boundary";
 	$mail_header .= "\nContent-Type: text/plain";
 	$mail_header .= "\nContent-Transfer-Encoding: 8bit";
 	$mail_header .= "\n\n" . firma_coradir()."\n";
 	// End
 	$mail_header .= "\n--$boundary--";

 	return mail($para,$asunto,"",$mail_header);
}//fin funcion enviar_mail

function firma_coradir($confiden=true){
	if ($confiden){
	$confiden="
		NOTA DE CONFIDENCIALIDAD
		Este mensaje (y sus anexos) es confidencial, esta dirigido exclusivamente a
		las personas direccionadas en el mail, puede contener información de
		propiedad exclusiva de Coradir S.A. y/o amparada por el secreto profesional.
		El acceso no autorizado, uso, reproducción, o divulgación esta prohibido.
		Coradir S.A. no asumirá responsabilidad ni obligación legal alguna por
		cualquier información incorrecta o alterada contenida en este mensaje.
		Si usted ha recibido este mensaje por error, le rogamos tenga la amabilidad
		de destruirlo inmediatamente junto con todas las copias del mismo, notificando
		al remitente. No deberá utilizar, revelar, distribuir, imprimir o copiar
		este mensaje ni ninguna de sus partes si usted no es el destinatario.
		Muchas gracias.\n";
	}else
  	$confiden="";
	$firma="
		CORADIR S.A.
		San Luis: Tel/Fax: (02652)431134 y rotativas
		Dirección: San Martín 454 (B5700BQJ)
		Bs.As.: Tel/Fax: (011)5354-0300 y rotativas
		Dirección: Patagones 2538 - Parque Patricios - (C1071AAI)
		e-mail: info@coradir.com.ar
		página: www.coradir.com.ar\n";

	return "\n".$firma."\n".$confiden;
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


function Fecha_db($fecha) {
		if (strstr($fecha,"/"))
			list($d,$m,$a) = explode("/",$fecha);
		elseif (strstr($fecha,"-"))
			list($d,$m,$a) = explode("-",$fecha);
		else
			return "";
		return "$a-$m-$d";
}

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
?>