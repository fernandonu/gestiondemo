<? 
/*$Author: mari $
$Revision: 1.2 $
$Date: 2006/04/17 19:58:04 $
*/


include("funciones_generales.php");

$fecha_hoy=date("Y/m/d");
$nrodiasemana = date("w");

$dias_semana=array();
$dias_semana[1]='Lunes';
$dias_semana[2]='Martes';
$dias_semana[3]='Miércoles';
$dias_semana[4]='Jueves';
$dias_semana[5]='Viernes';

$para="noelia@coradir.com.ar,juanmanuel@coradir.com.ar,corapi@coradir.com.ar,tedeschi@coradir.com.ar";

if ($nrodiasemana==1) { //si es lunes tambien se avisa de los cheques que vencían el sabado anterior
     $sabado_anterior=dia_anterior_x(fecha($fecha_hoy),2); 
     $or=" or fecha_vencimiento='".fecha_db($sabado_anterior)."'";
}
else $or="";

// mail 1 dia de un dia de semana
$asunto="DEPOSITO DE CHEQUES DIFERIDOS";

$sql="SELECT fecha_vencimiento,fecha_ingreso,monto,comentario,ubicacion,nro_cheque,
	      entidad.nombre as entidad
          FROM cheques_diferidos left join entidad using(id_entidad) 
          WHERE cheques_diferidos.IdDepósito IS NULL and id_ingreso_egreso IS NULL and activo=1 
          and (fecha_vencimiento='$fecha_hoy' $or )"; 
$res=$db->Execute($sql) or die($db->ErrorMsg());

if ($res->RecordCount() > 0) {

$contenido= " CHEQUES DIFERIDOS A DEPOSITAR EL DIA ". $dias_semana[$nrodiasemana]." ".fecha($fecha_hoy)."\n";
   
while (!$res->EOF)  { 
	$contenido.="
     -- Número de cheque: ".$res->fields['nro_cheque']."
        Importe: ".formato_money($res->fields['monto'])."
        Fecha de Emisión: ".fecha($res->fields['fecha_ingreso'])."
        Entidad: ".$res->fields['entidad']."
        Comentarios: ".$res->fields['comentario']."";
	 if ($res->fields['ubicacion'])
	   $contenido.="\nUbicacion: ".$res->fields['ubicacion']."";
     $contenido.="\n\n";
 $res->MoveNext();
}

enviar_mail($para,$asunto,$contenido,'','','',0);
}
//else {
//     $contenido=" No hay Cheques Diferidos a depositar en el día ".fecha($fecha_hoy);
//}

//enviar_mail($para,$asunto,$contenido,'','','',0);


/*********************  MAIL SEMANAL ***********************************/

$fecha=fecha($fecha_hoy);

$contenido="";
if ($nrodiasemana == 1) { //es lunes depositos de toda la semana mail 2
  $asunto="DEPOSITO DE CHEQUES DIFERIDOS (semanal)";
  $fecha_total=split("/",$fecha);
  $fecha_viernes=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]+4,$fecha_total[2]));
  $fecha_fin=fecha_db($fecha_viernes);
  $fecha_ini=dia_anterior_x($fecha,2); //toma tanbien los que se vencen el sabado anterior 
  $sql="SELECT fecha_vencimiento,fecha_ingreso,monto,comentario,ubicacion,nro_cheque,
	      entidad.nombre as entidad 
          FROM cheques_diferidos left join entidad using(id_entidad) 
          WHERE cheques_diferidos.IdDepósito IS NULL and id_ingreso_egreso IS NULL and activo=1 
          and fecha_vencimiento >='".fecha_db($fecha_ini)."' and fecha_vencimiento <= '$fecha_fin' 
          order by fecha_vencimiento"; 
$res=$db->Execute($sql) or die($db->ErrorMsg());

if ($res->RecordCount() > 0) {

$datos=array();
$fecha_ant="";
	$i=0;
while (!$res->EOF)  { 
	$fecha=fecha($res->fields['fecha_vencimiento']);
	if ($fecha_ant != $fecha) {
		$i=0;
    }
	$datos[$fecha][$i]['nro_cheque']=$res->fields['nro_cheque'];
	$datos[$fecha][$i]['monto']=formato_money($res->fields['monto']);
	$datos[$fecha][$i]['fecha_ingreso']=fecha($res->fields['fecha_ingreso']);
	$datos[$fecha][$i]['entidad']=$res->fields['entidad'];
	$datos[$fecha][$i]['comentario']=$res->fields['comentario'];
	$datos[$fecha][$i]['ubicacion']=$res->fields['ubicacion'];
	$i++;
	
$fecha_ant=$fecha;
$res->MoveNext();
}


$fecha=fecha($fecha_hoy);
for ($i=0;$i<5;$i++) {
  $datos_parcial=$datos[$fecha];
  $tam_parcial=count($datos_parcial);
  $contenido.= " CHEQUES DIFERIDOS A DEPOSITAR EL DIA ". $dias_semana[$nrodiasemana]." ".$fecha."\n";
  if ($tam_parcial==0) 
        $contenido.=" No hay Cheques Diferidos a depositar en el día ".$fecha."\n\n\n";
  for($j=0;$j<$tam_parcial;$j++) {
  
   $contenido.="
     -- Número de cheque: ".$datos_parcial[$j]['nro_cheque']."
        Importe: ".$datos_parcial[$j]['monto']."
        Fecha de Emisión: ".$datos_parcial[$j]['fecha_ingreso']."
        Entidad: ".$datos_parcial[$j]['entidad']."
        Comentarios: ".$datos_parcial[$j]['comentario']."";
	 if ($datos_parcial[$j]['ubicacion'] != "")
	   $contenido.="\nUbicacion: ".$datos_parcial[$j]['ubicacion']."";
     $contenido.="\n\n";
  }
   $fecha_total=split("/",$fecha);
   $fecha=date("d/m/Y",mktime(0,0,0,$fecha_total[1],$fecha_total[0]+1,$fecha_total[2]));
   $nrodiasemana=date("w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]+1,$fecha_total[2]));
}

}
else {
    $contenido=" No hay Cheques Diferidos a depositar en la semana desde ".$fecha." hasta ".$fecha_viernes;    }

enviar_mail($para,$asunto,$contenido,'','','',0);
}
/*******************************************/


// cheques diferidos que se han vencido hace tres semanas y no estan depositados

$fecha=fecha($fecha_hoy);
$fecha_total=split("/",$fecha);


$fecha_limite_1=date("Y/m/d",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-21,$fecha_total[2]));
$nro_dia_semana_1=date("w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-21,$fecha_total[2]));


$sql="SELECT fecha_vencimiento,fecha_ingreso,monto,comentario,ubicacion,nro_cheque,
	  entidad.nombre as entidad 
      FROM cheques_diferidos left join entidad using(id_entidad) 
      WHERE cheques_diferidos.IdDepósito IS NULL and id_ingreso_egreso IS NULL and activo=1 
      and fecha_vencimiento ='$fecha_limite_1' 
      order by fecha_vencimiento";
$res=$db->Execute($sql) or die($db->ErrorMsg());

if ($res->RecordCount() > 0) {

$contenido= " CHEQUES DIFERIDOS VENCIDOS el día  ". $dias_semana[$nrodiasemana_1]." ".fecha($fecha_limite_1)."\n";
$contenido.=" (Han pasado tres semanas desde la fecha de vencimiento y no se ha depositado).\n";

while (!$res->EOF)  { 
	$contenido.="
     -- Número de cheque: ".$res->fields['nro_cheque']."
        Importe: ".formato_money($res->fields['monto'])."
        Fecha de Emisión: ".fecha($res->fields['fecha_ingreso'])."
        Entidad: ".$res->fields['entidad']."
        Comentarios: ".$res->fields['comentario']."";
	 if ($res->fields['ubicacion'])
	   $contenido.="\nUbicacion: ".$res->fields['ubicacion']."";
     $contenido.="\n\n";
 $res->MoveNext();
}
enviar_mail($para,$asunto,$contenido,'','','',0);

}


/*********************************** cheques diferidos que se vencen al dia habil siguiente 
avisa que mañana o el día habil siguiente es ultimo día para depositar un cheque*/

$fecha_limite_2=date("Y/m/d",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-27,$fecha_total[2]));
$nro_dia_semana_2=date("w",mktime(0,0,0,$fecha_total[1],$fecha_total[0]-27,$fecha_total[2]));



$sql="SELECT fecha_vencimiento,fecha_ingreso,monto,comentario,ubicacion,nro_cheque,
	  entidad.nombre as entidad 
      FROM cheques_diferidos left join entidad using(id_entidad) 
      WHERE cheques_diferidos.IdDepósito IS NULL and id_ingreso_egreso IS NULL and activo=1 
      and fecha_vencimiento ='$fecha_limite_2'  order by fecha_vencimiento";


$res=$db->Execute($sql) or die($db->ErrorMsg());
if ($res->RecordCount() > 0) {

$contenido= " ESTA PRÓXIMA LA FECHA DE VENCIMIENTO DEL DEPOSITO DE LOS SIGUIENTES CHEQUES .\n";
   
while (!$res->EOF)  { 
	$contenido.="
     -- Número de cheque: ".$res->fields['nro_cheque']."
        Importe: ".formato_money($res->fields['monto'])."
        Fecha de Emisión: ".fecha($res->fields['fecha_ingreso'])."
        Fecha de Vencimiento: ".fecha($res->fields['fecha_vencimiento'])."
        Entidad: ".$res->fields['entidad']."
        Comentarios: ".$res->fields['comentario']."";
	 if ($res->fields['ubicacion'])
	   $contenido.="\nUbicacion: ".$res->fields['ubicacion']."";
     $contenido.="\n\n";
 $res->MoveNext();
}
enviar_mail($para,$asunto,$contenido,'','','',0);

}



?>
