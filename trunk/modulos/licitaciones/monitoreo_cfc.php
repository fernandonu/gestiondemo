<?php
/*
AUTOR: Gabriel (28/10/2005)
MODIFICADO POR:
$Author: nazabal $
$Revision: 1.14 $
$Date: 2006/03/03 18:19:24 $
*/

//IMPORTANTE
//En caso de modificar estas l�neas por alg�n motivo verificar coherencia en
//"lic_cargar_res_nuevo", "monitoreo_cfc" y "lic_cargar_res"


//$LIB_DIR = "/extra/admin/gestion/lib";                          // Librerias del sistema
$LIB_DIR = dirname(__FILE__)."/../../lib";                          // Librerias del sistema
require_once($LIB_DIR."/funciones_monitoreo_cfc.php");
require_once($LIB_DIR."/adodb/adodb.inc.php");
require_once($LIB_DIR."/adodb/adodb-pager.inc.php");
$db_type='postgres7';
$db_host = 'gestion.local';                    // Host de la p�na.
$db_host = 'devel.local';                    // Host de la p�na.

$db_user = 'projekt';                           // Usuario.
$db_password = 'propcp';                        // Contrase�a
$db_name = 'gestion';
$db_schemas = array("licitaciones");
$db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
$db->cacheSecs = 3600;
$result=$db->Execute("SET search_path=".join(",",$db_schemas)) or die($db->ErrorMsg());
unset($result);
$db->debug = $db_debug;

monitorear_cfcs();

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
		las personas direccionadas en el mail, puede contener informaci�n de
		propiedad exclusiva de Coradir S.A. y/o amparada por el secreto profesional.
		El acceso no autorizado, uso, reproducci�n, o divulgaci�n esta prohibido.
		Coradir S.A. no asumir� responsabilidad ni obligaci�n legal alguna por
		cualquier informaci�n incorrecta o alterada contenida en este mensaje.
		Si usted ha recibido este mensaje por error, le rogamos tenga la amabilidad
		de destruirlo inmediatamente junto con todas las copias del mismo, notificando
		al remitente. No deber� utilizar, revelar, distribuir, imprimir o copiar
		este mensaje ni ninguna de sus partes si usted no es el destinatario.
		Muchas gracias.\n";
	}else
  	$confiden="";
	$firma="
		CORADIR S.A.
		San Luis: Tel/Fax: (02652)431134 y rotativas
		Direcci�n: San Mart�n 454 (B5700BQJ)
		Bs.As.: Tel/Fax: (011)5354-0300 y rotativas
		Direcci�n: Patagones 2538 - Parque Patricios - (C1071AAI)
		e-mail: info@coradir.com.ar
		p�gina: www.coradir.com.ar\n";

	return "\n".$firma."\n".$confiden;
}
?>