<?
/*
Author: ferni

modificada por
$Author: ferni $
$Revision: 1.42 $
$Date: 2006/05/23 13:53:00 $
*/

require_once ("../../config.php");
require_once("funciones.php");

extract($_POST,EXTR_SKIP);
if ($parametros) extract($parametros,EXTR_OVERWRITE);

if ($_POST['en_reparacion']=="En Reparacion"){
	$id_reparador=$_POST['reparadores'];
	$nro_remito=$_POST['nro_remito'];
	$fecha=date("Y-m-d H:i:s");
	$usuario=$_ses_user['name'];
	$db->StartTrans();
	//traigo el nombre del reparador para insertar en el remito_interno
    $sql="select nombre_reparador,direccion from casos.reparadores where id_reparador=$id_reparador";
    $nombre_reparador_sql = sql($sql,"no se puede traer el nombre del reparador");
    $nombre_reparador= $nombre_reparador_sql->fields['nombre_reparador'];
    $direccion_reparador= $nombre_reparador_sql->fields['direccion'];
	//trae el id de remito_interno
	$q="select nextval('remito_interno_id_remito_seq') as id_remito_interno";
    $id_remito_interno=sql($q) or fin_pagina();
    $id_remito_interno=$id_remito_interno->fields['id_remito_interno'];
    //inserto en la tabla remito_interno
    $sql="insert into remito_interno.remito_interno (id_remito,fecha_remito,cliente,direccion,estado,id_licitacion,nro_orden,entrega)
    		values ($id_remito_interno,'$fecha','$nombre_reparador','$direccion_reparador','h',NULL,NULL,'Se envian Monitores al Reparador: $nombre_reparador con el Remito Número: $nro_remito')";
    sql($sql,"no se puede insertar en la tabla remito_interno");
    //inserto en el log de remito interno como creacion
    $sql="insert into remito_interno.log_remito_interno (usuario,fecha,tipo_log,id_remito)
    		values ('$usuario','$fecha','creacion',$id_remito_interno)";
    sql($sql,"no se puede insertar en la tabla log_remito_interno");
    //inserto en el log de remito interno como finalizacion
    $sql="insert into remito_interno.log_remito_interno (usuario,fecha,tipo_log,id_remito)
    		values ('$usuario','$fecha','finalizacion',$id_remito_interno)";
    sql($sql,"no se puede insertar en la tabla log_remito_interno");
    //traigo el numero de serie de muleto para insertar en la tabla items_remito_interno
	$sql="select nro_serie from casos.muletos where id_muleto=$id_muleto";
	$numero_serie=sql($sql,'no se puede traer el numero de serie del muleto');
	$numero_serie=$numero_serie->fields ['nro_serie'];
	//inserto en la tabla items_remito_interno
    $sql="insert into remito_interno.items_remito_interno (cant_prod,descripcion,id_remito,id_producto) 
    		values ('1','Monitor con numero de Serie: $numero_serie',$id_remito_interno,NULL)";
    sql($sql,"no se puede insertar los items en tabla items_remito_interno");
	//actualizo la tabla de muletos
	$sql="update casos.muletos set id_estado_muleto=3, flag_prueba_vida=0, id_reparador=$id_reparador, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto";
	sql($sql,'no se puede actualizar la tabla muletos') or fin_pagina();
	//inserto en la tabla de reparaciones
	$sql="insert into casos.reparaciones (id_reparador,id_muleto,fecha_rep,nro_remito) values ($id_reparador,$id_muleto,'$fecha','$nro_remito')";
	sql($sql,'no se puede insertar en la tabla reparaciones') or fin_pagina();
	//inserto log en log_muletos
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Paso a En Reparacion',$id_reparador,NULL)";
	sql($log,'no se puede insertar log') or fin_pagina();
	$db->CompleteTrans();		
    $accion="Se Cambio el Estado del Muleto $id_muleto A En Reparacion con el Remito Interno Número $id_remito_interno";
    $link=encode_link('muletos_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['reparados_desde_en_reparacion']=='A Reparados'){
	$fecha=date("Y-m-d H:i:s");
	$db->StartTrans();
	//actualizo la tabla de muletos
	$sql="update casos.muletos set id_estado_muleto=7, flag_prueba_vida=1, id_reparador=NULL, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto";
	sql($sql,'no se puede actualizar la tabla muletos') or fin_pagina();
	//inserto log
	$usuario=$_ses_user['name'];
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Paso a Reparados',NULL,NULL)";
	sql($log,'no se puede insertar log') or fin_pagina();
	$db->CompleteTrans();		
    $accion="Se Cambio el Estado del Muleto $id_muleto A Reparados";
    $link=encode_link('muletos_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['prueba_vida_boton']=='Prueba de Vida'){
	$usuario=$_ses_user['name'];
	$fecha=date("Y-m-d H:i:s");
	$fecha_llegada=$_POST['fecha_llegada'];
	$fecha_llegada=Fecha_db($fecha_llegada);
	$detalle_reparacion=$_POST['detalle_reparacion'];
	$fecha_prueba_vida=$_POST['fecha_prueba_vida'];
	$fecha_prueba_vida=Fecha_db($fecha_prueba_vida).' '.$_POST['hora_prueba_vida'];
	if ($_POST['resultado_prueba']) $resultado_prueba=1;
	else $resultado_prueba=0;
	$db->StartTrans();
	//actualizo la tabla de muletos
	$sql="update casos.muletos set flag_prueba_vida=0 where id_muleto=$id_muleto";
	sql($sql,'no se puede actualizar la tabla muletos') or fin_pagina();
	//inserto en la tabla de reparaciones
	$sql="insert into casos.prueba_vida (id_muleto,fecha_llegada,detalle_reparacion,fecha_prueba_vida,resultado_prueba,usuario_prueba_vida) values ($id_muleto,'$fecha_llegada','$detalle_reparacion','$fecha_prueba_vida','$resultado_prueba','$usuario')";
	sql($sql,'no se puede insertar en la tabla reparaciones') or fin_pagina();
	//inserto log
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Se Cargo la Prueba de Vida',NULL,NULL)";
	sql($log,'no se puede insertar log') or fin_pagina();
	$db->CompleteTrans();		
    $accion="Se Cargo la Prueba de Vida en el Muleto $id_muleto";
    $link=encode_link('muletos_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['disponible']=="Disponible"){
	$fecha=date("Y-m-d H:i:s");
	$db->StartTrans();
	//actualizo la tabal muletos
	$sql="update casos.muletos set id_estado_muleto=1, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto";
	sql($sql) or fin_pagina();
	//cargo log
	$usuario=$_ses_user['name'];
	$fecha=date("Y-m-d H:i:s");
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Paso a Disponible',NULL,NULL)";
	sql($log) or fin_pagina();
	$db->CompleteTrans();
    //redirecciono
	$accion="Se Cambio el Estado del Muleto $id_muleto a Disponible";
    $link=encode_link('muletos_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['scrap']=="Scrap"){
	$fecha=date("Y-m-d H:i:s");
	$db->StartTrans();
	//cambia el estado
	$sql="update casos.muletos set id_estado_muleto=5, idcaso=NULL, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto";
	sql($sql) or fin_pagina();
	//carga los log
	$usuario=$_ses_user['name'];
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Paso Historial (por Scrap)',NULL,NULL)";
	sql($log) or fin_pagina();
	//consulta para traer los datos para el mail
	$sql="select * from casos.muletos where id_muleto=$id_muleto";
	$result_mail = sql($sql) or fin_pagina();
	$db->CompleteTrans();
	//envia el mail
	$mail_local="serviciotecnico@coradir.com.ar";
	$asunto_local="Informacion: Paso de Muleto a Scrap";
	$contenido_local="El Muleto de Marca: " . $result_mail->fields['marca'] . 
	". Modelo: " . $result_mail->fields['modelo'] . 
	". Numero de Serio: " . $result_mail->fields['nro_serie'];
	enviar_mail ($mail_local,$asunto_local,$contenido_local,'','','');
	//mensaje y direcciona a la pagina padre
	$accion="Se Cambio el Estado del Muleto $id_muleto a Historial (Por Scrap)";
    $link=encode_link('muletos_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['a_reparar']=="A Reparar"){
	$fecha=date("Y-m-d H:i:s");
	$db->StartTrans();
	//actualizo la tabla muletos
	$sql="update casos.muletos set id_estado_muleto=4, fecha_llegada_estado='$fecha' where id_muleto=$id_muleto";
	sql($sql) or fin_pagina();
	//cargo los log
	$usuario=$_ses_user['name'];
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Paso A Reparar',NULL,NULL)";
	sql($log) or fin_pagina();
    $db->CompleteTrans();
    //redirecciono
	$accion="Se Cambio el Estado del Muleto $id_muleto A Reparar";
    $link=encode_link('muletos_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['guardar']=="Guardar nuevo Muleto"){
   $fecha=date("Y-m-d H:i:s");
   $db->StartTrans();
   $nro_serie=$_POST['nro_serie'];
   $marca=$_POST['marca'];
   $modelo=$_POST['modelo'];
   $observaciones=$_POST['observaciones'];
   $precio_stock=$_POST['precio_stock'];

    $q="select nextval('muletos_id_muleto_seq') as id_muleto";
    $id_muleto=sql($q) or fin_pagina();
    $id_muleto=$id_muleto->fields['id_muleto'];

    $id_estado_muleto=4;
     
    $query="insert into casos.muletos
             (id_muleto, observaciones, marca, modelo, nro_serie, id_estado_muleto, flag_prueba_vida, idcaso,precio_stock,fecha_llegada_estado)
             values
             ($id_muleto, '$observaciones', '$marca', '$modelo', '$nro_serie', $id_estado_muleto,0, NULL,$precio_stock,'$fecha')";

    sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
    
    $accion="Los datos del Muleto $id_muleto se guardaron con Exito";
	
    /*cargo los log*/ 
    $usuario=$_ses_user['name'];
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Alta del Muleto',NULL,NULL)";
	sql($log) or fin_pagina();
	 
    $db->CompleteTrans();

    $link=encode_link('muletos_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}//de if ($_POST['guardar']=="Guardar nuevo Muleto")

if ($_POST['guardar_editar']=="Guardar"){
   $db->StartTrans();
   $nro_serie=$_POST['nro_serie'];
   $marca=$_POST['marca'];
   $modelo=$_POST['modelo'];
   $observaciones=$_POST['observaciones'];
   $precio_stock=$_POST['precio_stock'];
   
   $query="update casos.muletos set 
             observaciones='$observaciones', marca='$marca', modelo='$modelo', nro_serie='$nro_serie' , precio_stock='$precio_stock'
             where id_muleto=$id_muleto";

   sql($query, "Error al insertar/actualizar el muleto") or fin_pagina();
    
    /*cargo los log*/ 
    $usuario=$_ses_user['name'];
	$fecha=date("Y-m-d H:i:s");
	$log="insert into casos.log_muleto (id_muleto, usuario, fecha, accion, id_reparador, idcaso) values ($id_muleto, '$usuario', '$fecha','Edito el Muleto',NULL,NULL)";
	sql($log) or fin_pagina();
	 
    $db->CompleteTrans();
    
   $accion="Los datos del Muleto $id_muleto se Actualizaron con Exito";
   $link=encode_link('muletos_listado.php',array("accion"=>$accion));
   header("Location:$link") or die("No se encontró la página destino");	
}

if ($_POST['eliminar_muleto']=="Eliminar"){
   $db->StartTrans();
      
    $sql="delete from casos.log_muleto where id_muleto = $id_muleto";
    sql($sql, "Error no se puede eliminar log muletos") or fin_pagina();
    
    $sql="delete from casos.reparaciones where id_muleto = $id_muleto";
    sql($sql, "Error no se puede eliminar reparaciones") or fin_pagina();
    
    $sql="delete from casos.prueba_vida where id_muleto = $id_muleto";
    sql($sql, "Error no se puede eliminar prueba de vida") or fin_pagina();
    
    $sql="delete from casos.muletos where id_muleto = $id_muleto";
    sql($sql, "Error no se puede eliminar muleto") or fin_pagina();
  
   $db->CompleteTrans();
    
   $accion="El Muleto $id_muleto se Elimino con Exito";
   $link=encode_link('muletos_listado.php',array("accion"=>$accion));
   header("Location:$link") or die("No se encontró la página destino");	
}

if ($id_muleto) {
$sql="select * from casos.muletos where id_muleto=$id_muleto";
$res_q_muletos=sql($sql, "Error al traer los datos del caso") or fin_pagina();

$observaciones=$res_q_muletos->fields['observaciones'];
$marca=$res_q_muletos->fields['marca'];
$modelo=$res_q_muletos->fields['modelo'];
$nro_serie=$res_q_muletos->fields['nro_serie'];
$precio_stock=$res_q_muletos->fields['precio_stock'];
}
echo $html_header;
?>
<script>
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{
 if(document.all.nro_serie.value==""){
  alert('Debe ingresar un Nro de Serie');
  return false;
 }
 if(document.all.marca.value==""){
  alert('Debe ingresar una Marca');
  return false;
 }
 if(document.all.modelo.value==""){
  alert('Debe ingresar un Modelo');
  return false;
 }
 if(document.all.precio_stock.value==""){
  alert('Debe ingresar un Precio Stock');
  return false;
 }
 if(document.all.nro_serie.value.indexOf('"')>0){
 	alert('No se puede Agregar Comillas Dobles en Numero de Serie');
  	return false;
 }
 if(document.all.marca.value.indexOf('"')>0){
 	alert('No se puede Agregar Comillas Dobles en la Marca');
  	return false;
 }
 if(document.all.modelo.value.indexOf('"')>0){
 	alert('No se puede Agregar Comillas Dobles en el Modelo');
  	return false;
 }
 if(document.all.observaciones.value.indexOf('"')>0){
 	alert('No se puede Agregar Comillas Dobles a las Observaciones');
  	return false;
 }
 return true;
}//de function control_nuevos()

function control_paso_en_reparacion()
{
 if(document.all.reparadores.value=="-1"){
 	alert('Debe Seleccionar un Instalador');
  	return false;
 }
 if(document.all.nro_remito.value==""){
 	alert('Debe Ingresar un Número de Remito');
  	return false;
 }
 
 return true;
}//de function control_nuevos()

function replaceChars(entry,out,add) {//out: busca para reemplazar por el el string add
temp = "" + entry;
while (temp.indexOf(out)>-1) {
	pos= temp.indexOf(out);
	temp = "" + (temp.substring(0, pos) + add +
	temp.substring((pos + out.length), temp.length));
}
return temp
}

function editar_campos()
{
	document.all.nro_serie.readOnly=false;
	document.all.marca.readOnly=false;
	document.all.modelo.readOnly=false;
	document.all.observaciones.readOnly=false;
	document.all.precio_stock.readOnly=false;
	document.all.precio_stock.value=replaceChars(document.all.precio_stock.value,".","");
	document.all.precio_stock.value=replaceChars(document.all.precio_stock.value,",",".");
	
	document.all.cancelar_editar.disabled=false;
	document.all.guardar_editar.disabled=false;
	document.all.editar.disabled=true;
 	return true;
}//de function control_nuevos()

var img_ext='<?=$img_ext='../../imagenes/rigth2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/down2.gif' ?>';//imagen contraido
function muestra_tabla(obj_tabla,nro){
 oimg=eval("document.all.imagen_"+nro);//objeto tipo IMG
 if (obj_tabla.style.display=='none'){
 	obj_tabla.style.display='inline';
    oimg.show=0;
    oimg.src=img_ext;
 }
 else{
 	obj_tabla.style.display='none';
    oimg.show=1;
	oimg.src=img_cont;
 }
}

</script>

<form name='form1' action='muletos_admin.php' method='POST'>
<br>
<?/***********************************************
 Traemos y mostramos el Log 
************************************************/
if ($id_muleto){
$q="select a.fecha,usuario,accion,nombre_reparador,nrocaso 
	from (
		casos.log_muleto 
		left join casos.reparadores
		using (id_reparador)
		) as a
	left join casos.casos_cdr
	using (idcaso)
	where id_muleto=$id_muleto
	order by a.id_log_muleto";
$log=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");?>
<div align="right">
	<input name="mostrar_ocultar_log" type="checkbox" value="1" onclick="if(!this.checked)
																	  document.all.tabla_logs.style.display='none'
																	 else 
																	  document.all.tabla_logs.style.display='block'
																	  "> Mostrar Logs
</div>	
<!-- tabla de Log de la OC -->
<div style="display:'none';width:98%;overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;' ?> " id="tabla_logs" >
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?while (!$log->EOF){?>
	<tr>
	      <td height="20" nowrap>Fecha <?=fecha($log->fields['fecha']). " " .Hora($log->fields['fecha']);?> </td>
	      <td nowrap > Usuario : <?=$log->fields['usuario']; ?> </td>
	      <td nowrap > Acción : <?=$log->fields['accion']; ?> </td>
	      <td nowrap > Reparador: <?=($log->fields['nombre_reparador']!='')?$log->fields['nombre_reparador'] : "<font color='#9966FF' size=1>Ninguno</font>";?> </td>
	      <td nowrap > Num. Caso: <?=($log->fields['nrocaso']!='')?$log->fields['nrocaso'] : "<font color='#9966FF' size=1>Ninguno</font>";?> </td>
	</tr>
	<?$log->MoveNext();
}?>
</table>
</div>
<hr>
<?}
/*******************  FIN    ****************************/?>
<br>

<input type="hidden" name="id_muleto" value="<?=$id_muleto?>">
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
 <tr id="mo">
    <td>
    <?
    if (!$id_muleto) {
    ?>
     <font size=+1><b> Nuevo Muleto</b></font>
    <? }
        else {
    ?>
      <font size=+1><b>Muletos</b></font>
    <? } ?>
    </td>
 </tr>
 <tr><td><? tabla_datos_muletos(); ?></td></tr>
 
<!--Cambios de Estado-->
<tr><td><table width=100% align="center" class="bordes">
 <?if ($estado=="disponibles"){?>
    <tr id="mo">
   		<td align=center colspan="2">
   			<b>Cambios de Estado</b>
   		</td>
   	</tr>   
    <tr>
    	<td width="25%" align="center" colspan="2"><br>
    	<input type="submit" value="A Reparar" title="Pasa Muleto A Reparar" name="a_reparar" style="width=170" onclick="return confirm ('Esta Seguro que Desea Cambiar el \n Estado del Muleto A Reparar')">
    	</td>
    </tr>
 
 <?}?>
 
 <?if ($estado=="en_reparacion"){?>
    <tr id="mo">
   		<td align=center colspan="2">
   			<b>Cambios de Estado</b>
   		</td>
   	</tr>   
    <tr>
    	<td width="25%" align="center" colspan="2"><br>
    	<input type="submit" value="A Reparados" title="Pasa Muleto A Reparados" name="reparados_desde_en_reparacion" style="width=170" onclick="return confirm ('Esta Seguro que Desea Cambiar de Estado el Monitor?')">
    	</td>
    </tr>
 
 <?}?>
 
 <?if ((($estado=="a_reparar")&&($res_q_muletos->fields['flag_prueba_vida']!=1))||
 	  (($estado=="reparados")&&($res_q_muletos->fields['flag_prueba_vida']!=1))){?>
  	<tr id="mo">
  		<td align=center colspan="2">
  			<b>Cambios de Estado</b>
  		</td>
  	</tr>   
    <tr>
	 <td align="center" colspan="2" class="bordes">
	 <br>
     <b>Reparador:&nbsp;</b>
	 <select name=reparadores>
     <option value=-1>Seleccione</option>
                 <?
                 $sql= "select * from casos.reparadores";
                 $result_reparadores=sql($sql) or fin_pagina();
                 while (!$result_reparadores->EOF){ 
                 	$id_rep=$result_reparadores->fields['id_reparador'];
                 	$nombre=$result_reparadores->fields['nombre_reparador'];
                 ?>
                   <option value=<?=$id_rep;?> ><?=$nombre?></option>
                 <?
                 $result_reparadores->movenext();
                 }
                 ?>
      </select>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <b>Número de Remito:&nbsp;</b><input type="text" value="" name="nro_remito">
      &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="En Reparacion" title="Pasa Monitor a Reparacion" name="en_reparacion" onclick="return control_paso_en_reparacion()">
     </td>
    </tr>
    
    <tr>
	 <td align="center" colspan="2" class="bordes">
	 <br>
	 	<?//print_r($_ses_user);
	 	 if (permisos_check("inicio","permisos_boton_scrap_muleto")){
	 		$boton_scrap="";
	 	}
	 	else{
	 		$boton_scrap="disabled";
	 	}	
	 	?>
	 	<input type="submit" value="Disponible" title="Monitores Disponible" name="disponible" style="width=250" onclick="return confirm ('Esta Seguro que Desea Pasar a Disponible')">&nbsp;&nbsp;&nbsp;&nbsp;
	 	<input type="submit" value="Scrap" title="Pasa Monitor a Historial (por scrap)" name="scrap" style="width=250;background-color:red" <?echo $boton_scrap;?> onclick="return confirm ('Esta Seguro que Desea Pasar a SCRAP')">
	 </td>
 	</tr>
<?}//del if ?>
</table></td></tr>
<!--Fin de Cambios de Estado-->

<?//tabla para cargar prueba de vida
if ((($res_q_muletos->fields['flag_prueba_vida']==1)&&($estado=="a_reparar"))||
	(($res_q_muletos->fields['flag_prueba_vida']==1)&&($estado=="reparados"))){?>
	<tr><td><table width=100% align="center" class="bordes" bgcolor="#FFCCCC">
		<tr id="mo">
  		 <td align=center>
  		 	<b>Carga Prueba de Vida</b>
  		 </td>
	  	</tr>   
	    <tr>
		 <td align="center" class="bordes">
		 	 <table>
			  <tr>
			   <td width="30%" align="left">
				 <?cargar_calendario();?>
				 <b>Fecha de Llegada:&nbsp;</b>
				 <input type=text name='fecha_llegada' value="<?=date("d/m/Y")?>" size=10 readonly>
	             <?=link_calendario("fecha_llegada");?>
               </td>
		       <td width="70%" align="center">
             	 <b>Detalle Reparación:&nbsp;</b>
			 	 <textarea name="detalle_reparacion" cols="50" rows="4"></textarea>
			   </td>
			  </tr>
			  <tr>
			   <td align="center" colspan="2"><br>
			   	 <?cargar_calendario();?>
				 <b>Fecha y Hora de Finalización de Prueba de Vida:&nbsp;</b>
				 <input type=text name='fecha_prueba_vida' value="<?=date("d/m/Y")?>" size=10 readonly>
	             <?=link_calendario("fecha_prueba_vida");?>
	             <select name=hora_prueba_vida>
                  <?$i=1;
                    $h='8';
                  	$m='00';
                  	while($i<=24){?>
                     <option value="<?="$h:$m:00"?>"> <?="$h:$m"?></option>
                  <? if (($i%2)==1) {$m='30';$i++;}
                  	 else {$m='00';$i++;$h++;}
                  }?>   
                 </select>
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                 <b>Prueba de Vida OK:&nbsp;</b>
                 <input type="checkbox" name="resultado_prueba">
			   </td>
			  </tr>
			  <tr>
			   <td align="center" colspan="2"><br>
			   	 <input type="submit" value="Prueba de Vida" title="Cargar Prueba de Vida" name="prueba_vida_boton"> 
			   </td>
			  </tr>
			 </table>
	     </td>
	    </tr>
	</table></td></tr>
<?}//fin tabla para cargar prueba de vida?>

<?//tabla de historia de reparaciones
if (($id_muleto)){
$query="select nombre_reparador,fecha_rep,nro_remito 
		from casos.reparaciones 
		left join casos.reparadores using (id_reparador)
		where id_muleto=$id_muleto 
		order by id_reparaciones";
$reparaciones_muleto=sql($query,"<br>Error al traer las reparaciones<br>") or fin_pagina();
?>
<tr><td><table width="100%" class="bordes" align="center">
	<tr align="center" id="mo">
	  <td align="center" width="3%">
	   <img id="imagen_1" src="<?=$img_ext?>" border=0 title="Mostrar Reparaciones" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.reparaciones,1);" >
	  </td>
	  <td align="center">
	   <b>Reparaciones</b>
	  </td>
	</tr>
</table></td></tr>
<tr><td><table id="reparaciones" border="1" width="100%" style="display:none;border:thin groove">
	<?if ($reparaciones_muleto->RecordCount()==0){?>
	 <tr>
	  <td align="center">
	   <font size="3" color="Red"><b>No existen Reparaciones para este Muleto</b></font>
	  </td>
	 </tr>
	 <?}
	 else{?>
	 	<tr id="sub_tabla">	
	 		<td>Nombre del Reparador</td>
	 		<td>Fecha de Paso A En Reparación</td>
	 		<td>Número de Remito</td>
	 	</tr>
	 	<?
	 	$reparaciones_muleto->movefirst();
	 	while (!$reparaciones_muleto->EOF) {?>
	 		<tr>	
		 		<td><?=$reparaciones_muleto->fields['nombre_reparador']?></td>
		 		<td><?=fecha($reparaciones_muleto->fields['fecha_rep'])?></td>
		 		<td><?=$reparaciones_muleto->fields['nro_remito']?></td>
		 	</tr>	 	
	 		<?$reparaciones_muleto->movenext();
	 	}
	 }?>
</table></td></tr>
<?}//fin tabla de historia de reparaciones?>

<?//tabla de historia de Prueba de Vida
if (($id_muleto)){
$query="select * from casos.prueba_vida where id_muleto=$id_muleto order by id_prueba_vida";
$prueba_vida_muleto=sql($query,"<br>Error al traer las prueba de vida<br>") or fin_pagina();
?>
<tr><td><table width="100%" class="bordes" align="center">
	<tr align="center" id="mo">
	  <td align="center" width="3%">
	   <img id="imagen_2" src="<?=$img_ext?>" border=0 title="Mostrar Prueba de Vida" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.prueba_vida,2);" >
	  </td>
	  <td align="center">
	   <b>Prueba de Vida</b>
	  </td>
	</tr>
</table></td></tr>
<tr><td><table id="prueba_vida" border="1" width="100%" style="display:none;border:thin groove">
	<?if ($prueba_vida_muleto->RecordCount()==0){?>
	 <tr>
	  <td align="center">
	   <font size="3" color="Red"><b>No existen Prueba de Vida para este Muleto</b></font>
	  </td>
	 </tr>
	 <?}
	 else{?>
	 	<tr id="sub_tabla">	
	 		<td>Fecha de Llegada</td>
	 		<td>Detalle de la Reparación</td>
	 		<td>Fecha de la Prueba de Vida</td>
	 		<td>Resultado</td>
	 		<td>Usuario</td>
	 	</tr>
	 	<?
	 	$prueba_vida_muleto->movefirst();
	 	while (!$prueba_vida_muleto->EOF) {?>
	 		<tr>	
		 		<td><?=fecha ($prueba_vida_muleto->fields['fecha_llegada'])?></td>
		 		<td><?=$prueba_vida_muleto->fields['detalle_reparacion']?></td>
		 		<td><?=fecha($prueba_vida_muleto->fields['fecha_prueba_vida']).' '.Hora($prueba_vida_muleto->fields['fecha_prueba_vida'])?></td>
		 		<td><?=($prueba_vida_muleto->fields['resultado_prueba']==1)?'Aprobo':'No Aprobo'?></td>
		 		<td><?=$prueba_vida_muleto->fields['usuario_prueba_vida']?></td>
		 	</tr>	 	
	 		<?$prueba_vida_muleto->movenext();
	 	}
	 }?>
</table></td></tr>
<?}//fin tabla de historia de Prueba de Vida?>
 
<?if (($id_muleto)){//tabla de fotos muletos
$query="select * from casos.foto_muleto where id_muleto=$id_muleto";
$foto_muleto=sql($query,"<br>Error al traer los datos del producto<br>") or fin_pagina();
?>
<tr><td><table width="100%" class="bordes" align="center">
	<tr align="center" id="mo">
	  <td align="center" width="3%">
	   <img id="imagen_3" src="<?=$img_ext?>" border=0 title="Mostrar Foto Muleto" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.foto_muleto,3);" >
	  </td>
	  <td align="center">
	   <b>Foto Muleto</b>
	  </td>
	</tr>
</table></td></tr>
<tr><td><table id="foto_muleto" width="100%" style="display:none;border:thin groove">
      <tr>
       <td width="40%">
        <font size="2" color="Blue"><b> <?=$foto_muleto->fields["comentario_foto"];?></b></font>
       </td>
       <td width="50%"  title="<?=$foto_muleto->fields["nombre_archivo"];?>">
       <?$link_foto=encode_link("foto_ampliada_muleto.php",array("id_muleto"=>$foto_muleto->fields["id_muleto"],"archivo"=>$foto_muleto->fields["nombre_archivo"],"coment"=>$foto_muleto->fields["comentario_foto"]))?>
        <img src="./Fotos/<?=$foto_muleto->fields["id_muleto"];?>/<?=$foto_muleto->fields["nombre_archivo"];?>" width="150" height="150" style='cursor: hand;' onclick="window.open('<?=$link_foto?>')">
       </td>
       <td width="10%">
        <?$link_fotos=encode_link("ver_fotos_muleto.php",array("id_muleto"=>$id_muleto))?>
        <input type="button" name="mas_imagenes" value="Ver más fotos" onclick="window.open('<?=$link_fotos?>')" style="width=150"><br>
		 <?$link_fotos1=encode_link("guardar_foto_muleto.php",array("id_muleto"=>$id_muleto))?>
        <input type="button" name="nueva_fo" value="Nueva Foto" onclick="window.open('<?=$link_fotos1?>')" style="width=150"><br>       
        </td>
      </tr>
</table></td></tr>
<?}//fin tabla de fotos muletos ?>

 <tr><td><table width=100% align="center" class="bordes">
  <tr align="center">
   <td>
     <input type=button name="volver" value="Volver" onclick="document.location='muletos_listado.php'"title="Volver al Listado de Muletos" style="width=150px">     
   </td>
  </tr>
 </table></td></tr>
 
</table> 
<?if ($_ses_user['login']=='ferni'){?>
	<input type="submit" name="eliminar_muleto" value="Eliminar" onclick="return confirm('el muleto se va!!!')" style="width=150px">
<?}?>
</form>
<?=fin_pagina();// aca termino ?>
