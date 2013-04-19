<?php
/*
AUTOR: Quique (28/10/2005)
MODIFICADO POR:
$Author: mari $
$Revision: 1.1 $
$Date: 2006/12/22 11:42:32 $
*/

include("funciones_generales.php");

//////////////////////////////////////////////
//cálculo de la fecha "hoy + 1 semana"
$fecha_d=date("d");
$fecha_m=date("m");
$fecha_y=date("Y");
$fecha_inicio=date("Y-m-d", strtotime($fecha_y."-".$fecha_m."-".$fecha_d));
$fecha_d=($fecha_d+5);
	
$fecha_limite=date("Y-m-d", strtotime($fecha_y."-".$fecha_m."-".$fecha_d));

/////////////////////////////////////////////

$consulta="select * from tareas_divisionsoft.tareas_soft
	       where tarea_activa=1";

$rta_consulta=$db->Execute($consulta)or die("c47: ".$consulta);
$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";


while ($fila=$rta_consulta->fetchRow())
	{
	$fvenci=$fila["vencimiento"];	
	$fvenci1=Fecha($fila["vencimiento"]);	
	$faviso1=Fecha($fila["aviso"]);
	$faviso=date('Y-m-d',strtotime($fila["aviso"]));
	$f7dias=date('Y-m-d',strtotime($fila["aviso_7dias"]));
	$id_usu=$fila["id_usuario"];
	$id_tarea=$fila["id_tarea_soft"];
	$id_patro=$fila["id_patrocinante"];
	$cons="select nombre,apellido,mail from sistema.usuarios where id_usuario=$id_usu";
	$result=$db->Execute($cons)or die("c60: ".$cons);
	$mail_usu=$result->fields["mail"];
	$nom_usu=$result->fields["nombre"];
	$ape_usu=$result->fields["apellido"];
	$consu="select nombre,apellido,id_patrocinante,mail from tareas_divisionsoft.patrocinantes join sistema.usuarios using(id_usuario) where id_patrocinante=$id_patro";
	$resulta=$db->Execute($consu)or die("c65: ".$consu);
	$mail_pat=$resulta->fields['mail'];
	$n_pat=$resulta->fields['nombre'];
	$a_pat=$resulta->fields['apellido'];
	
	$fecha=date("Y-m-d",mktime());
	
	if(compara_fechas($fecha,$faviso)!=-1)
    {
	 	 $contenido_lic="La tarea asignada esta por vencer \n\n";
		 $contenido_lic.="Asunto ".$fila["asunto"]." \n\n";
		 $contenido_lic.="Vencimiento ".fecha($fila["vencimiento"])." Aviso ".Fecha($fila["aviso"])." \n\n";
		 $contenido_lic.="Descripcion ".$fila["descripcion"]."\n\n";
		 $contenido_lic.="Patrocinante $n_pat  $a_pat \n\n";		  
		 $asunto="Notificación de vencimiento en la tarea asignada";
		 enviar_mail( $mail_usu, $asunto, $contenido_lic, "", "", "", 0);
    	
    }
    
    $dif=diferencia_dias($faviso1,$fvenci1);
    $dif=$dif/2;
    $dif=floor($dif);  	
    	
	list($anio,$mes,$dia)=explode("-",$faviso);
	list($anio_1,$mes_1,$dia_1)=explode("-",$fvenci);
	$mail_1=date("Y-m-d",mktime(0,0,0,$mes,$dia+$dif,$anio));
	$mail_2=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1-1,$anio_1));
    $mail_3=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1+1,$anio_1));
    $mail_4=date("Y-m-d",mktime(0,0,0,$mes_1,$dia_1+4,$anio_1));
	
    if(compara_fechas($fecha,$mail_1)==0)
    {
	 	 $contenido_lic="La tarea asignada a $nom_usu $ape_usu esta a punto de vencer\n\n";
		 $contenido_lic.="Asunto ".$fila["asunto"]." \n\n";
		 $contenido_lic.="Fecha Vencimiento ".fecha($fila["vencimiento"])."Fecha Aviso ".Fecha($fila["aviso"])." \n\n";
		 $contenido_lic.="Descripcion ".$fila["descripcion"]."\n\n";
		 $contenido_lic.="Patrocinante $n_pat  $a_pat \n\n";			 	 
		 $asunto="Notificación de vencimiento en la tarea asignada";
    	 enviar_mail( $mail_pat, $asunto, $contenido_lic, "", "", "", 0);
    	
    }
    
    if(compara_fechas($fecha,$mail_3)==0)
    {
	 	 $contenido_lic="La tarea asignada a $nom_usu $ape_usu a vencido\n\n";
		 $contenido_lic.="Asunto ".$fila["asunto"]." \n\n";
		 $contenido_lic.="Fecha Vencimiento ".fecha($fila["vencimiento"])."Fecha Aviso ".Fecha($fila["aviso"])." \n\n";
		 $contenido_lic.="Descripcion ".$fila["descripcion"]."\n\n";
		 $contenido_lic.="Patrocinante $n_pat  $a_pat \n\n";			 	 
		 $asunto="Notificación de vencimiento en la tarea asignada";
		 $para1="$mail_pat,$mail_usu";
    	 enviar_mail( $para1, $asunto, $contenido_lic, "", "", "", 0);
    	
    }
    
    if(compara_fechas($fecha,$mail_4)==0)
    {
	 	$consultar1="select nombre,apellido,id_patrocinante,mail from tareas_divisionsoft.patrocinantes join sistema.usuarios using(id_usuario)";
		$resultado1=$db->Execute($consultar1)or die("c124: ".$consultar1); 
    	while ($filas1=$resultado1->fetchRow())
		 {
	    	 $mail3=$filas1->fields["mail"];
			 //$para1="$mail3";	
			 $contenido_lic="La tarea asignada a $nom_usu $ape_usu a vencido\n\n";
			 $contenido_lic.="Asunto ".$fila["asunto"]." \n\n";
			 $contenido_lic.="Fecha Vencimiento ".fecha($fila["vencimiento"])."Fecha Aviso ".Fecha($fila["aviso"])." \n\n";
			 $contenido_lic.="Descripcion ".$fila["descripcion"]."\n\n";
			 $contenido_lic.="Patrocinante $n_pat  $a_pat \n\n";			 	 
			 $asunto="Notificación de vencimiento en la tarea asignada";
			 
			 enviar_mail( $mail3, $asunto, $contenido_lic, "", "", "", 0);
		 }
		 
    }
    
     if(compara_fechas($fecha,$f7dias)==0)
     {
	 	$consultar1="select nombre,apellido,id_patrocinante,mail from tareas_divisionsoft.patrocinantes join sistema.usuarios using(id_usuario)";
		$resultado1=$db->Execute($consultar1)or die("c124: ".$consultar1); 
    	
    	while ($filas1=$resultado1->fetchRow())
		 {
	    	
			 $mail3=$filas1["mail"];
			 //$para1="$mail3";	
			 $contenido_lic="La tarea asignada a $nom_usu $ape_usu a vencido\n\n";
			 $contenido_lic.="Asunto ".$fila["asunto"]." \n\n";
			 $contenido_lic.="Fecha Vencimiento ".fecha($fila["vencimiento"])."Fecha Aviso ".Fecha($fila["aviso"])." \n\n";
			 $contenido_lic.="Descripcion ".$fila["descripcion"]."\n\n";
			 $contenido_lic.="Patrocinante $n_pat  $a_pat \n\n";			 	 
			 $asunto="Notificación de vencimiento en la tarea asignada";
			 enviar_mail( $mail3, $asunto, $contenido_lic, "", "", "", 0);
			 
		 }
		 list($anio,$mes,$dia)=explode("-",$fecha);
		 $fechad=date("Y-m-d",mktime(0,0,0,$mes,$dia+7,$anio));
		 $upda="update tareas_divisionsoft.tareas_soft set aviso_7dias='$fechad' where id_tarea_soft=$id_tarea";
		 $upda1=$db->Execute($upda)or die("c162: ".$upda);
     }
    
    if(compara_fechas($fecha,$mail_2)==0)
    {
	 	 $contenido_lic="La tarea asignada a $nom_usu $ape_usu esta a punto de vencer";
		 $contenido_lic.="Asunto ".$fila["asunto"]." \n\n";
		 $contenido_lic.="Fecha Vencimiento ".fecha($fila["vencimiento"])."Fecha Aviso ".Fecha($fila["aviso"])." \n\n";
		 $contenido_lic.="Descripcion ".$fila["descripcion"]."\n\n";		 		 
		 $contenido_lic.="Patrocinante $n_pat  $a_pat \n\n";		 		 
		 $asunto="Notificación de vencimiento en la tarea asignada";
    	 //enviar_mail( $mail_pat, $asunto, $contenido_lic, "", "", "", 0);
    	
    }
    
		
}

?>