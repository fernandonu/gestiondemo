<? 
/*
Autor: Gabriel
$Author: mari $
$Revision: 1.65 $
$Date: 2006/06/16 17:43:11 $
*/

$LIB_DIR = dirname(__FILE__)."/../../lib";				// Librerias del sistema
require_once($LIB_DIR."/adodb/adodb.inc.php");
require_once($LIB_DIR."/adodb/adodb-pager.inc.php");
$db_type='postgres7';

//$db_host = 'devel.local';			// Host de la página.
$db_host = '192.168.1.44';			// Host de la página.

$db_user = 'projekt';				// Usuario.
$db_password = 'propcp';			// Contraseña.
$db_name = 'gestion';
$db_schemas = array("personal", "sistema");
$db = &ADONewConnection($db_type) or die("Respuesta: 1 CON1 Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
$db->cacheSecs = 3600;
$result=$db->Execute("SET search_path=".join(",",$db_schemas)) or die("Respuesta: 2 SET1 ".$db->ErrorMsg());
unset($result);
$db->debug = $db_debug;

$versionCard=0;
$usuarios=array(
	array("recepcionista", "ABCABC"),
	array("test", "000000"),
	array("gaudina", "FEADEF")
);
$password=array("000000"); 
//ERRORES
$error=array(
	"ERR_OK"=>"Proceso compleatado sin errores! ",
	"ERR_INCONSISTENCIA_ASIST"=>"Inconsistencia en la base de datos: se han registrado mas de una entrada para la misma persona, el mismo d&iacute;a en distintos horarios ",
	"ERR_SIST_ARCHIVOS"=>"Problemas en el sistema de archivos ",
	"ERR_INCONSISTENCIA_LEGAJOS"=>"Incosistencia en la tabla 'legajos' (legajo duplicado) o en tabla usuarios (usuario duplicado)",
	"ERR_REG_NO_ENCONTRADO"=>"No se encuentra el registro correspondiente a la persona ",
	"ERR_CLIENTE_NO_PERMITIDO"=>"El remitente no tiene acceso a estas funciones ",
	"ERR_EMAIL_NO_ENVIADO"=>"El email no se ha podido enviar ",
	"ERR_EMAIL_ENVIADO"=>"El email se ha enviado ",
	"ERR_COMANDO_NO_RECONOCIDO"=>"El comando recibido no corresponde a uno implementado por el sistema ",
	"ERR_CREACION_SIMULTANEA"=>"No se pueden escribir dos tarjetas al mismo tiempo para la misma persona (error de integridad en clave)"
);

//$log_viejo=date("Y").$log_mes.(date("d")-7);
$log_file_old="log".date("Ymd", mktime(0, 0, 0, date("m"), date("d")-7, date("Y"))).".txt";

if (file_exists($log_file_old))	unlink($log_file_old);

if (!($fp = fopen('log'.date("Ymd").'.txt', 'a'))) die("Respuesta: 3 FOP1 ERR_SIST_ARCHIVOS<br>");

////////////////////////
$cliente=$_SERVER["REMOTE_ADDR"];

//comandos válidos: asist, escri, getve, aviso

if ($_POST["cmd"]=="asist"){
	$id_legajo=$_POST["legajo"];
	$fecha=Fecha_db($_POST["fecha"]) or $fecha=date("Y-m-d");;
	$hora=$_POST["hora"] or $hora=date("H:i");
	$login=$_POST["login"];
	
	//VERIFICAR QUE LA PERSONA EXISTA EN LA LISTA DE LEGAJOS
	if ($id_legajo!=""){
		$result=$db->Execute("select * from legajos where id_legajo=".$id_legajo) or die("Respuesta: 4 SEL1 ".$db->ErrorMsg());
	}elseif ($login!=""){
		$result=$db->Execute("select * from usuarios where login='".$login."'") or die("Respuesta: 4 SEL1b ".$db->ErrorMsg());
	}
	
	if ($result->recordCount()==1)  {
		if ($id_legajo!=""){
			$id_legajo=$result->fields["id_legajo"];
			//CONSULTA PARA SABER SI EXISTE UNA ENTRADA PARA ESA PERSONA (YA INGRESO)
			$entro="select * from asistencia where id_legajo=$id_legajo and fecha='$fecha' and hora_sale is NULL";
			$result=$db->Execute($entro) or die("Respuesta: 5 SEL2 ".$db->ErrorMsg());
			if ($result->recordCount()>1){ //SI HAY + DE UN REGISTRO LA TABLA ESTÁ MAL (NO PUEDE HABER MAS DE UNA HORA_SALE NULA PARA LA MISMA PERSONA
				fprintf($fp, date("c")." ==> ".$error["ERR_INCONSISTENCIA_ASIST"]."\n");
				die("Respuesta: 6 ".$error["ERR_INCONSISTENCIA_LEGAJOS"]);
			}elseif ($result->recordCount()==0){//LA PERSONA ESTA ENTRANDO
				$insert="insert into asistencia (id_legajo, fecha, hora_entra, hora_sale) values (".$id_legajo.", '$fecha', '".$hora."', NULL)";
				$result=$db->Execute($insert) or die("Respuesta: 7 INS1 ".$db->ErrorMsg());
				fprintf($fp, date("c")." ==> ".$insert."\n");
				echo "Respuesta: 0 ".$error["ERR_OK"];
			}elseif ($result->recordCount()==1){//LA PERSONA ESTA SALIENDO
				$update="update asistencia set hora_sale ='".$hora."' where (id_legajo=".$id_legajo.") and (fecha='$fecha') and (hora_sale is NULL)";
				$result=$db->Execute($update) or die("Respuesta: 8 UPD1 ".$db->ErrorMsg());
				fprintf($fp, date("c")." ==> ".$update."\n");
				echo "Respuesta: 0 ".$error["ERR_OK"];
			}
		}elseif ($login!=""){
			$id_usuario=$result->fields["id_usuario"];
			//CONSULTA PARA SABER SI EXISTE UNA ENTRADA PARA ESA PERSONA (YA INGRESO)
			$entro="select * from asistencia where id_usuario=$id_usuario and fecha='$fecha' and hora_sale is NULL";
			$result=$db->Execute($entro) or die("Respuesta: 5 SEL2b ".$db->ErrorMsg());
			if ($result->recordCount()>1){ //SI HAY + DE UN REGISTRO LA TABLA ESTÁ MAL (NO PUEDE HABER MAS DE UNA HORA_SALE NULA PARA LA MISMA PERSONA
				fprintf($fp, date("c")." ==> ".$error["ERR_INCONSISTENCIA_ASIST"]."\n");
				die("Respuesta: 6 b ".$error["ERR_INCONSISTENCIA_LEGAJOS"]);
			}elseif ($result->recordCount()==0){//LA PERSONA ESTA ENTRANDO
				$insert="insert into asistencia (id_usuario, fecha, hora_entra, hora_sale) values (".$id_usuario.", '$fecha', '".$hora."', NULL)";
				$result=$db->Execute($insert) or die("Respuesta: 7 INS1b ".$db->ErrorMsg());
				fprintf($fp, date("c")." ==> ".$insert."\n");
				echo "Respuesta: 0 ".$error["ERR_OK"];
			}elseif ($result->recordCount()==1){//LA PERSONA ESTA SALIENDO
				$update="update asistencia set hora_sale ='".$hora."' where (id_usuario=".$id_usuario.") and (fecha='$fecha') and (hora_sale is NULL)";
				$result=$db->Execute($update) or die("Respuesta: 8 UPD1b ".$db->ErrorMsg());
				fprintf($fp, date("c")." ==> ".$update."\n");
				echo "Respuesta: 0 ".$error["ERR_OK"];
			}
		}
	}elseif ($result->recordCount()>1){
		fprintf($fp, date("c")." ==> ".$error["ERR_INCONSISTENCIA_LEGAJOS"]."\n");
		die("Respuesta: 9 ".$error["ERR_INCONSISTENCIA_LEGAJOS"]);
	}else{
		fprintf($fp, date("c")." ==> ".$error["ERR_REG_NO_ENCONTRADO"]."($id_legajo)\n");
		die("Respuesta: 10 ".$error["ERR_REG_NO_ENCONTRADO"]."--> ".$login);
	}
	die ("Respuesta: 0 ".$error["ERR_OK"]);
	//FUNCIONES EXTRAS
}elseif ($_POST["cmd"]=="aviso"){
	$picaro=$_POST["picaro"];
	$nombre=$_POST["nombre"];
	$hora=$_POST["hora"] or $hora=date("H:i");
	
	$to="mari@coradir.com.ar";
	$subject="Pícaro: ".$nombre;
	$body="<html><body><br>El pícaro '<b>".$nombre."</b>' (legajo: ".$picaro.") ingresó la tarjeta en el lector mas de 5 veces en menos de 5 minutos a las ".$hora."<br></html>";
	$firma="ICard 1.0";
	
	if (enviar_mail_html($to, $subject, $body, "", "", "")){
		fprintf($fp, date("c")." ==>".$error["ERR_EMAIL_ENVIADO"].". Causante: ".$nombre." ".$picaro." ".$hora."\n");
		//die("Respuesta: 11 ".$error["ERR_EMAIL_ENVIADO"].". Causante: ".$nombre." ".$picaro." ".$hora);
	}else{
		fprintf($fp, date("c")." ==>".$error["ERR_EMAIL_NO_ENVIADO"].". Causante: ".$nombre." ".$picaro." ".$hora."\n");
		die("Respuesta: 12 ".$error["ERR_EMAIL_NO_ENVIADO"].". Causante: ".$nombre." ".$picaro." ".$hora);
	}
}elseif ($_POST["cmd"]=="escri"){
	$id_legajo=$_POST["legajo"];
	$login=$_POST["login"];
	$nom=$_POST["nombre"];
	$log=$_POST["login"];
	$fec=Fecha_db($_POST["fecha"]);
	$hor=$_POST["hora"];
	$pas=$_POST["pass"];
	$ver=$_POST["version"];
	$ori=$_POST["escritor"];
	
	if ($id_legajo!=""){
		$sel="select * from tarjetas_nuevas where id_legajo=".$id_legajo." and hora='".$hor."'";
		$result=$db->Execute($sel) or die("Respuesta: 15 SEL3 ".$db->ErrorMsg());
		if ($result->recordCount()==0){
			$ins="insert into tarjetas_nuevas (id_legajo, nombre, login, fecha, hora, escritor, pass, version) values (";
			$ins.=$id_legajo.", '".$nom."', '".$log."', '".$fec."', '".$hor."', '".$ori."', '".$pas."', ".$ver.")";
			$result=$db->Execute($ins) or die("Respuesta: 14 INS2 ".$db->ErrorMsg()." ".$ins);
			fprintf($fp, date("c")." ==> ".$ins."\n");
			die("Respuesta: 0 ".$error["ERR_OK"]);
		}else{
			fprintf($fp, date("c")." ==>".$error["ERR_CREACION_SIMULTANEA"]."\n");
			die("Respuesta: 16 ".$error["ERR_CREACION_SIMULTANEA"]);
		}
	}elseif ($login!=""){
		$sel="select * from tarjetas_nuevas where login ilike '".$login."' and hora='".$hor."' and fecha='".$fec."'";
		$result=$db->Execute($sel) or die("Respuesta: 15 SEL3b ".$db->ErrorMsg());
		if ($result->recordCount()==0){
			$ins="insert into tarjetas_nuevas (nombre, login, fecha, hora, escritor, pass, version) values ('";
			$ins.=$nom."', '".$log."', '".$fec."', '".$hor."', '".$ori."', '".$pas."', ".$ver.")";
			$result=$db->Execute($ins) or die("Respuesta: 14 INS2b ".$db->ErrorMsg());
			fprintf($fp, date("c")." ==> ".$ins."\n");
			die("Respuesta: 0 ".$error["ERR_OK"]);
		}else{
			fprintf($fp, date("c")." ==>".$error["ERR_CREACION_SIMULTANEA"]."\n");
			die("Respuesta: 16 ".$error["ERR_CREACION_SIMULTANEA"]);
		}
	}
}elseif (($_POST["cmd"]=="getve")||($_GET["cmd"]=='getve')){
	fprintf($fp, date("c")." ==> Requerimiento de datos de inicialización de ICard v1.0.");
	$msg="datos ".$versionCard." login";
	for ($i=0; $i<count($usuarios); $i++) $msg.=" ".$usuarios[$i][0]." ".$usuarios[$i][1];
	$msg.=" pass ";
	for ($i=0; $i<count($password); $i++) $msg.=$password[$i]." ";
	$msg.=" fin ";
	echo $msg;
	fprintf($fp, date("c")." ==> ".$msg."\n");
}elseif ($_POST["cmd"]=="test"){//test
}elseif ($_POST["cmd"]=="sensor"){//test
}elseif ($_POST["cmd"]=="s_asist") {
	$nro=$_POST["nro"];
	$fecha=date("Y-m-d") or $fecha=Fecha_db($_POST["fecha"]);
    $hora=$_POST["hora"] or $hora=date("H:i");
    $consulta="select usuarios.id_usuario,usuarios.nombre,usuarios.apellido,usuarios.mail,usuarios.pcia_ubicacion
               from sistema.usuarios where nro_tarjeta='".$nro."'";
	$rta_consulta=$db->Execute($consulta) or die("Respuesta: 23 SEL23 No se pudo consultar la tabla de usuarios");
	
	
	if ($rta_consulta->recordCount()==1) { //pregunta si existe el nro de tarjeta??
		$id_usuario=$rta_consulta->fields["id_usuario"];
		$nombre=$rta_consulta->fields["apellido"]." ".$rta_consulta->fields["nombre"];
		$para=$rta_consulta->fields["mail"];
		$prov_ubicacion=$rta_consulta->fields["pcia_ubicacion"];
		if ($prov_ubicacion==2) $para_oculto="bianchi@coradir.com.ar,mari@coradir.com.ar";
		            else $para_oculto="mari@coradir.com.ar";
		$respuesta_remota="Respuesta: 0 datos cmd=asist?legajo=?dni=?nombre=".$rta_consulta->fields["nombre"]." ".$rta_consulta->fields["apellido"]
			."?login=".$rta_consulta->fields["login"]."?fecha=".fecha($fecha)."?hora=$hora?pass=?escritor="
			."?version=".$rta_consulta->fields["version_tarjeta"]."??";
	
	//selecciona la ultima entrada		
	$sql="select d.id_asistencia,d.fecha,d.hora_entra,d.hora_sale from
          (select max(fecha) as fecha
          from personal.asistencia where id_usuario=$id_usuario) as m
          join 
          (select id_asistencia,fecha,hora_entra,hora_sale
          from personal.asistencia where id_usuario=$id_usuario) as d
          using (fecha)";
	$res=$db->Execute($sql) or die("Respuesta: Error al buscar entrada en null ".$db->ErrorMsg());
	
	if ($res->RecordCount() > 1) { //NO PUEDE HABER MAS DE UNA ENTRADA POR DIA
	  fprintf($fp, date("c")." ==> ".$error["ERR_INCONSISTENCIA_ASIST"]."\n");
	  die("Respuesta: 6 c ".$error["ERR_INCONSISTENCIA_ASIST"]);
	} 
	elseif ($res->RecordCount() == 0) { //ingreso y es la primera vez que usa el llavero
	  $ingreso=1;
	}
	elseif ($res->RecordCount() == 1) {  //hay un registro 
	   $fecha_anterior=$res->fields['fecha'];
	   $hora_sale=$res->fields['hora_sale'];
	   $hora_ingreso=g_timeToSec($hora);  // hora ingreso o ingreso
	   $hora_control=g_timeToSec("03:00:00");  // hasta la 3 de la mañana
	   $id_asistencia=$res->fields['id_asistencia'];
	  
	   //si es la misma fecha hace update
	   //si la fecha difiere en un dia y la hora es antes de las 03:00:00 hace update
	   // en otro caso insert	   
	   if ( (compara_fechas($fecha_anterior,$fecha)== 0)  || 
	      ((diferencia_dias(fecha($fecha_anterior),fecha($fecha),0) == 1) && ($hora_ingreso < $hora_control)) )
	     $ingreso=0; //update registra salida
	   else $ingreso=1;
	 } 
		  
	 if ($ingreso==1) { //REGISTRA INGRESO
	    $insert="insert into asistencia (id_usuario, fecha, hora_entra, hora_sale) values (".$id_usuario.", '$fecha', '$hora', NULL)";
		$rta_consulta=$db->Execute($insert) or die("Respuesta: 7 INS1c ".$db->ErrorMsg());
		fprintf($fp, date("c")." ==> ".$insert."\n");
		$asunto="Registro entrada";
		$contenido=$nombre.": Se ha registrado su ingreso del día ". fecha($fecha). " a la hora $hora";
		/*if ($para!= "") {
		    enviar_mail($para,$asunto,$contenido,'','','',1,$para_oculto);
		}
		elseif($para_oculto) {
		    enviar_mail($para_oculto,$asunto,$contenido,'','','','');
		}*/
		enviar_mail($para_oculto,$asunto,$contenido,'','','','');
		die($respuesta_remota);
	 }
	 elseif($ingreso==0) {  //REGISTRA EGRESO
	    $update="update asistencia set hora_sale ='".$hora."' where id_asistencia=$id_asistencia";
		$rta_consulta=$db->Execute($update) or die("Respuesta: 8 UPD1c ".$db->ErrorMsg());
		fprintf($fp, date("c")." ==> ".$update."\n");
		$asunto="Registro Salida";
		$contenido=$nombre.": Se ha registrado su salida del día ". fecha($fecha). " a la hora $hora";
		/*if ($para!= "")
			enviar_mail($para,$asunto,$contenido,'','','',1,$para_oculto);
		elseif($para_oculto) {
		   enviar_mail($para_oculto,$asunto,$contenido,'','','','');
		}*/
		enviar_mail($para_oculto,$asunto,$contenido,'','','','');
	   die($respuesta_remota);
	 }
	}else{
		fprintf($fp, date("c")." ==> ".$_POST[""]."\n");
		die(" Respuesta: 24 No se encuentra un registro con dicho nro. de tarjeta ");
	}
}elseif ($_POST["cmd"]=="s_escri"){
	$login=$_POST["login"];
	$nro=$_POST["nro"];
	$versionCard=$_POST["version"];
	$ori=$_POST["escritor"];
	$pass=$_POST["pass"];
	
	$consulta="select * from sistema.usuarios where login='$login'";
	$rta_consulta=$db->Execute($consulta) or die("Respuesta: 21 SEL21 ".$db->ErrorMsg());
	$nom=$rta_consulta->fields["apellido"].", ".$rta_consulta->fields["nombre"];
	if ($rta_consulta->recordCount()==1){
		$ins="insert into tarjetas_nuevas (nombre, login, fecha, hora, escritor, pass, version) values (";
		$ins.="'".$nom."', '".$login."', '".date("Y-m-d")."', '".date("H:m:s")."', '$ori', '$pass' , $versionCard)";
		$result=$db->Execute($ins) or die("Respuesta: 25 INS ".$db->ErrorMsg()." ".$ins);
		fprintf($fp, date("c")." ==> ".$ins."\n");
		
		$consulta="select * from sistema.usuarios where nro_tarjeta='".$nro."'";
		$rta_consulta=$db->Execute($consulta) or die("Respuesta: 23 SEL23 ".$db->ErrorMsg());
		if ($rta_consulta->recordCount()==1){
			$consulta="update sistema.usuarios set nro_tarjeta=null where login='".$rta_consulta->fields['login']."'";
			$rta_consulta=$db->Execute($consulta) or die("Respuesta: 24 UPD24 ".$db->ErrorMsg());
		}
		$consulta="update sistema.usuarios set nro_tarjeta='$nro', version_tarjeta=$versionCard where login='$login'";
		$rta_consulta=$db->Execute($consulta) or die("Respuesta: 20 UPD20 ".$db->ErrorMsg());
		die("Respuesta: 0 nro. de tarjeta asignada.");
		fprintf($fp, date("c")." ==> ".$consulta."\n");
	}else die("Respuesta: 22 UPD22 ERROR: No se encuentra un usuario con dicho login. ".$db->ErrorMsg());
}else{
	fprintf($fp, date("c")." ==> Mensaje ---> ".$_POST["cmd"]." ".$_POST["login"]." ".$_POST["nro"]."\n");
	die("Respuesta: 13 ".$error["ERR_COMANDO_NO_RECONOCIDO"]);
}
	
function Fecha_db($fecha) {
                if (strstr($fecha,"/"))
                        list($d,$m,$a) = explode("/",$fecha);
                elseif (strstr($fecha,"-"))
                        list($d,$m,$a) = explode("-",$fecha);
                else
                        return "";
                return "$a-$m-$d";
}//function fecha_db
function Fecha($fecha_db) {
	$m = substr($fecha_db,5,2);
	$d = substr($fecha_db,8,2);
	$a = substr($fecha_db,0,4);
	if (is_numeric($d) && is_numeric($m) && is_numeric($a)) {
		return "$d/$m/$a";
	}else {
		return "";
	}
}

//funcion para enviar los mails
function enviar_mail($para,$asunto,$contenido,$adjunto,$path,$tipo,$adj=1,$para_oculto=0){
 $filename=$adjunto;
 $mailtext=$contenido;
 //$mailtext .= firma_coradir();
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


/* fecha1=fecha2= Y-mm-dd
Si fecha1,fecha2 => 0
Si fecha1 < fecha 2 =>-1
SI fecha1 > fecha2 => */
function compara_fechas($fecha1, $fecha2) {
    $fecha1 = strtotime($fecha1);
    $fecha2 = strtotime($fecha2);
    if ($fecha1 > $fecha2) return 1;
    elseif ($fecha1 == $fecha2) return 0;
    else return -1;
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

function g_timeToSec($time){
		$horas=substr($time, 0, 2);
		$minutos=substr($time, 3, 2);
		$segundos=substr($time, 6, 2);
		
		return $segundos+($minutos*60)+($horas*3600);
	}

function enviar_mail_html($para,$asunto,$contenido,$adjunto,$path,$adj=1){
 $filename=$adjunto;
 $mailtext=$contenido;
 $filepath=$path; 
 $mail_header="";
 $mail_header .= "From: Icard v1.0 <sistema_inteligente@coradir.com.ar>";
 $mail_header .= "\nReturn-Path: ''";
 $mail_header .= "\r\nMIME-Version: 1.0";
 $mail_header .= "\r\nContent-Type: text/html";
 $mail_header .= "\r\nContent-Transfer-Encoding: 8bit";
 return mail($para,$asunto,$contenido,$mail_header);
}//fin funcion enviar_mail
?>
