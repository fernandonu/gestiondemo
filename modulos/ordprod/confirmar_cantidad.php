<?
/*
$Author: fernando $
$Revision: 1.5 $
$Date: 2006/12/12 20:25:27 $

*/
require_once("../../config.php");
require_once("funciones_prod_bsas.php");

echo $html_header;
//print_r($parametros); 
//print_r($_POST); 

$nro_orden=$parametros['nro_orden'] or $nro_orden=$_POST['nro_orden'];
$cant_por_tanda=$parametros['cant_por_tanda'] or $cant_por_tanda=$_POST['cant_por_tanda'];
$estado=$parametros['estado'] or $estado=$_POST['estado']; //estado (segun prod bs) actual
$proximo_estado=$parametros['proximo_estado'] or $proximo_estado=$_POST['proximo_estado']; //prox estado (segun prod bs)

if ($parametros) {
	 $por_tanda=$parametros['por_tanda'];
	 $descontar=$parametros['descontar'];
}
  else  {
  	 $por_tanda=$_POST['por_tanda']; 
      $descontar=$_POST['descontar']; //es la cantidad que ya hay en inspeccion  para controlar si estan todos los reportes

  }

//si auditoria es uno aprueba y pasa a historial, si es 0 repureba y vu7elve a pendiente
//$reprueba=0;
if ($_POST['aceptar'] || $_POST['boton_aceptar']) {
	$db->StartTrans(); 
	$cant_a_ingresar=$_POST['cantidad'];
 //$cond para saber si cambio el estado_bsas en orden_de_produccion
 //	por ej si tengo 20 maquinas en produccion y paso las 20 a inspeccion, si no hay ninguna en 
 //pendientes cambio el estado en la orden de produccion a inspeccion 
 $cond=""; 
 switch ($estado) { //estado actual
         case "pendientes": {
         	         $estado_actual=0;
         	         $estado_bsas=1; //estado proximo En Produccion
         	         $cmd_prox="produccion"; //estado proximo En Produccion
         	         $coment="al estado en Producción";
         	         $orden=1;
         	         $cond="";
         }
         break;
         case "produccion": {
         	          $estado_actual=1;
         	          $estado_bsas=5; //estado proximo En Inspeccion
         	          $cmd_prox="inspeccion"; //estado proximo En Inspeccion
         	          $coment="a Inspeccion";
         	          $orden=2;         	          
         	          $cond=" prod_bsas_por_tanda.estado_bsas_por_tanda=0";
         } 
         break;
          case "inspeccion": {
         	         $estado_actual=5; 
         	         $estado_bsas=3; //estado proximo Embalaje
         	         $cmd_prox="embalaje"; //estado proximo Embalaje
         	         $coment="a Embalaje";
         	         $orden=3;
         	         $cond=" prod_bsas_por_tanda.estado_bsas_por_tanda in(0,1)"; 
         }
         break;
         case "embalaje": {
         	          $estado_actual=3;
         	          $estado_bsas=4; //estado proximo Calidad	
         	          $cmd_prox="calidad"; //estado proximo Calidad	
         	          $coment="a Calidad";
         	          $orden=4;   
         	          $cond=" prod_bsas_por_tanda.estado_bsas_por_tanda in(0,1,5)";       
         }
         break;
        
 }

 $coment.=" $cant_por_tanda Máquinas";
 
 //busco si ya hay ordenes en el estado anterior
  buscar_ordenes($nro_orden,$estado_actual,$orden,$cant_a_ingresar,$cant_por_tanda);  //estado actual
  //busco si ya hay ordenes en el estado proximo
  $orden++;
  buscar_ordenes($nro_orden,$estado_bsas,$orden,$cant_a_ingresar);   //estado_proximo
  
 $sql_log="insert into ordenes.log_op_bsas (usuario, nuevo_estado, observaciones, nro_orden)
       values('".$_ses_user["login"]."', $estado_bsas, '$coment', $nro_orden)";
 sql($sql_log,"$sql_log log ") or fin_pagina();
 
 if ($por_tanda==0) { ///si es la primera vez que divide 
  $sql="update ordenes.orden_de_produccion set por_tanda=1
       where nro_orden=$nro_orden";
 sql($sql,"actualiza por tanda") or fin_pagina();
 }
 
 
 $ref=encode_link("ordenes_nueva.php",array("nro_orden"=>$nro_orden,"modo"=>"modificar","volver"=>"seguimiento_produccion_bsas","cmd"=>$cmd_prox,"por_tanda"=>1));
 
 $act=actualiza_estado_bsas($estado_actual,$nro_orden,$cond,$estado_bsas);
  
 //busco datos para enviar mail
 $sql_mail="select orden_de_produccion.id_licitacion,orden_de_produccion.fecha_entrega,orden_de_produccion.titulo_etiqueta,
            orden_de_produccion.cantidad,entidad.nombre,orden_de_produccion.descripcion_etiqueta,
            u1.mail as mail_lider,u2.mail as mail_patrocinador 
            from ordenes.orden_de_produccion
            left join licitaciones.entidad using(id_entidad)
            left join licitaciones.licitacion using (id_licitacion)
            left join sistema.usuarios u1 on (lider=u1.id_usuario and u1.id_usuario<>16)
            left join sistema.usuarios u2 on (patrocinador=u2.id_usuario and u2.id_usuario<>16)
            where orden_de_produccion.nro_orden=$nro_orden"; 
 
 
  $res=sql($sql_mail,"$sql_mail") or fin_pagina();
  $para="";
  if ($res->fields['mail_lider']) 
      $para=$res->fields['mail_lider'];
  if ($res->fields['mail_patrocinador'])  $para.=",".$res->fields['mail_patrocinador'];

  if ($estado_actual==1 && $_POST['menor']==1) { //produccion 
    $para.= "juanmanuel@coradir.com.ar,valentino@coradir.com.ar,andrada@coradir.com.ar";
  }
  elseif ($estado_actual==3 ) { //embalaje
    $para.="aranzubia@coradir.com.ar,carlos@coradir.com.ar,aranzubia@coradir.com.ar";
  }
  
  $fecha=date("d/m/Y");
  
    $asunto="$cant_a_ingresar Máquinas de la Orden de Producción Nº $nro_orden se pasaron al estado $cmd_prox";
    $mensaje="$cant_a_ingresar Máquinas de la Orden de Producción Nº ".$nro_orden." se pasaron al estado $cmd_prox.";
    if($_POST['menor']==1)
           $mensaje.="\n No paso la cantidad de verificaciones correctas de Prueba de vida";
    $mensaje.="\n--------------------------Breve Descripción de la Orden--------------------------";
    $mensaje.="\nID. Licitación:        ".$res->fields['id_licitacion'];
    $mensaje.="\nCliente:               ".$res->fields['nombre'];
    $mensaje.="\nFecha Entrega:         ".fecha($res->fields['fecha_entrega']);
    $mensaje.="\nCantidad de Maquinas:  ".$res->fields['cantidad'];
    $mensaje.="\nTitulo:                ".$res->fields['titulo_etiqueta'];
    $mensaje.="\nDescripción:           ".$res->fields['descripcion_etiqueta'];
    $mensaje.="\n---------------------------------------------------------------------------------------";
    $mensaje.="\nEl cambio se realizo el día $fecha, por el Usuario ".$_ses_user['name'];
  
   if ($para !="")
      enviar_mail($para,$asunto,$mensaje,"","","",0);
     
   if ($estado_actual==3)  { //embalaje
    $mensaje="";
    $id_licitacion_mail="";
	    if ($res->fields['id_licitacion']) $id_licitacion_mail=", asociado al ID Nº ".$res->fields['id_licitacion']. ". ";
        $asunto="$cant_a_ingresar Máquinas de la Orden de Producción Nº $nro_orden se pasaron al estado $cmd_prox  $id_licitacion_mail";
	    $mensaje.="\n----------------------------------ATENCION----------------------------------";
	    $mensaje.="\n $cant_a_ingresar Máquinas de la Orden de Producción Nº ". $nro_orden. " $id_licitacion_mail se pasaron $cmd_prox, lo que";	    $mensaje.="\nsignifica que esta Pronta a ser DESPACHADA.";
	    $mensaje.="\n\nDebería Pedir:";
	    $mensaje.="\n	1) Que se Facturen las Computadoras.";
	    $mensaje.="\n	2) Coordinar con el Cliente la Entrega.";
	    $mensaje.="\n	3) Avisar a las Áreas Interesadas que Coordinen el Trasporte o Custodia.";
	    $mensaje.="\n----------------------------------------------------------------------------";
	    $mensaje.="\nEl cambio se realizo el día $fecha, por el Usuario ".$_ses_user['name'];
   
    enviar_mail($para,$asunto,$mensaje,"","","",0);
   }
     
    
//$db->completeTrans();
 if ($db->CompleteTrans()) { 
 	
 	?>
  <script>
  window.opener.location.href='<?=$ref?>';
  window.close();
  </script>
<? 
}
}
 
?>

<script>
function control_datos(estado) {
    if (document.all.cantidad.value=='' || isNaN(document.all.cantidad.value)) {
       alert ('Ingrese cantidad valida para el campo cantidad');	
       document.all.no_enviar.value=1;
       return false;    
    }
    if (parseInt(document.all.cantidad.value) > parseInt(document.all.cant_por_tanda.value)) {
       alert ('La cantidad ingresada debe ser menor o igual que ' + document.all.cant_por_tanda.value);	
       document.all.no_enviar.value=1;
       return false;    
    }
    if (estado=='produccion') {
       if (parseInt(document.all.cantidad.value) > (parseInt(document.all.cant_que.value) - parseInt(document.all.descontar.value)))
           {
            if(confirm ("Esta seguro de pasar a Inspección? No cumple con la cantidad de verificaciones correctas requeridas")) {
            	 document.all.menor.value=1;
            	 document.all.no_enviar.value=0;
                 return true; 
            }
            else {
               document.all.no_enviar.value=1;
               return false; 
            }
            }
    }
document.all.no_enviar.value=0;  
return true;
}

</script>

<form name='form1' method="POST" action="confirmar_cantidad.php">
<input type="hidden" name="cant_por_tanda" value="<?=$cant_por_tanda?>">
<input type="hidden" name="nro_orden" value="<?=$nro_orden?>">
<input type="hidden" name="estado" value="<?=$estado?>">
<input type="hidden" name="proximo_estado" value="<?=$proximo_estado?>">
<input type="hidden" name="auditoria" value="<?=$auditoria?>">
<input type="hidden" name="por_tanda" value="<?=$por_tanda?>">
<input type="hidden" name="descontar" value="<?=$descontar?>">
<input type="hidden" name="menor" value=0>
<?
$consulta="select reportes.nro_serie,reportes.mac, reportes.resultado
		   from ordenes.reportes
		   join ordenes.reporteorden  on ( reportes.id_reporte = reporteorden.id_reporte)
		   where reportes.resultado=1 and reporteorden.id_orden =$nro_orden
		   group by reportes.nro_serie, reportes.mac, resultado";
$rta_consulta=sql($consulta, "c871 ") or fin_pagina();
$cant_que=$rta_consulta->recordCount();

echo "<input type=hidden name=cant_que value='$cant_que'>\n";
?>
<table align="center" cellpadding="2" cellspacing="2" class="bordes">
    <tr id="mo" bgcolor="<?=$bgcolor3?>" align="center">
      <td >Ingrese la cantidad de máquinas para <b><?=$proximo_estado?></b></td>
    </tr>
    <tr bgcolor=<?=$bgcolor_out?>>
      <td align="center"><b>Máquinas en Estado <?=$estado?>: <?=$cant_por_tanda?></b></td>
    </tr>
    <tr bgcolor=<?=$bgcolor_out?>> 
      <td align="center"><b> <?=$cartel." ".$proximo_estado?> </b>
       <input type="text" name="cantidad" value="" size=2 onkeypress="if (getkey(event)==13) { document.all.aceptar.click(); if (document.all.no_enviar.value==1)
                                                                                                                                 return false; 
                                                                                                                                 else document.all.boton_aceptar.value=1;
                                                                                             }">
        <b>Máquinas</b></td>
    </tr>
    <tr align="center">
      <td>
      <input type="hidden" name="boton_aceptar" value="0">    
      <input type="hidden" name="no_enviar" value="0">    
      <input type='submit' name="aceptar" value="Aceptar" onclick="return control_datos('<?=$estado?>'); ">
      <input type='button' name="cerrar" value="Cerrar" onclick='window.close();'>

      
      </td>
    </tr>
</table>
<?=fin_pagina();?>
</form>