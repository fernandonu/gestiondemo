<?php
/*AUTOR: MAD
               1 julio 2004
$Author: mari $
$Revision: 1.51 $
$Date: 2006/05/18 15:17:23 $
*/
/*
Sirve para ver el detalle de la orden de quemado, su estado, pasar de un  estado a otro
*/
require_once("../../config.php");

//parte de actualizar la observacion
if($_POST["act"]=="Actualizar"){
	if($_POST["tipo"]!='') {
   		$sql_update="update orden_quemado set obs = '".$_POST["Observacion"]."',id_config = ".$_POST["tipo"]." where nro_orden=".$_POST['orden'];
	}else {
   		$sql_update="update orden_quemado set obs = '".$_POST["Observacion"]."' where nro_orden=".$_POST['orden'];
	}
   	$db->Execute($sql_update) or die($db->ErrorMsg()."<br>$sql_update");
   $parametros["id"] = $_POST["orden"];
}

$fecha = date("Y-m-d h:i:s",mktime());

//parte de comenzar orden
if($_POST["enviar"]=="Comenzar"){
   	//parte de ver si hay alguna orden del mismo ensamblador, si no activo esta
   	$sql_activa = "select ensamblador.id_ensamblador from orden_quemado join orden_de_produccion using(nro_orden) join ensamblador using (id_ensamblador) where nro_orden = ".$_POST['orden'];
	$resultado_activa = $db->Execute($sql_activa) or die($db->ErrorMsg()."<br>".$sql_activa);					
					
	$id_ensamblador = $resultado_activa->fields["id_ensamblador"];
	/*				
   	$sql_activa = "select nro_orden from orden_quemado join orden_de_produccion using(nro_orden) join ensamblador using (id_ensamblador) where orden_quemado.estado = 1 and ensamblador.id_ensamblador = ".$id_ensamblador;
	$resultado_activa = $db->Execute($sql_activa) or die($db->ErrorMsg()."<br>".$sql_activa);					
					
   	if($resultado_activa->RecordCount() == 0){*/ 
   		//pregunto si el esnsamblador tiene definido proceso dequemado
   		$sql_ensambla = "select * from ensamblador_quemado where id_ensamblador = $id_ensamblador and activo <> 0";
		$resultado_ensambla = $db->Execute($sql_ensambla) or die($db->ErrorMsg()."<br>".$sql_ensambla);					
                  		
		if($resultado_ensambla->RecordCount() > 0){ 
			$sql_update="update orden_quemado set estado=1, sinc = 0, fecha_orden = '$fecha', obs=obs||'\nSe inicio la orden...' where nro_orden=".$_POST['orden'];
   			if($db->Execute($sql_update)) {
   				$msg = "<font color='green'><b>Se inicio la orden de quemado.</b></font>";

	   		//parte de logs de eventos del sistema
			$sql_next ="Select nextval('ordenes.logs_quemado_id_log_seq') as id_log";
			$resultado=$db->Execute($sql_next) or die($db->ErrorMsg()."<br>".$sql_next);
			$id_log = $resultado->fields['id_log'];
			$sql_log = "insert into logs_quemado (id_log,fecha,nro_orden,usuario,tipo) values 
			(".$id_log.",'".$fecha."',".$_POST['orden'].",'".$_ses_user["name"]."','Registro la nueva orden de quemado')";
			$db->Execute($sql_log) or die($db->ErrorMsg()."<br>$sql_log");
			//fin de parte de logs
   			}
   			else 
   				$msg = "<font color='red'><b>Error: La orden de quemado no se inicio correctamente.</b></font>";
		} else $msg="<font color='red'>Hubo un error al pasar el item a ordenes en curso: El ensamblador no tiene proceso de quemado .</font>";
   /*	} else 
   			$msg = "<font color='red'><b>Error: Solo puede haber una orden de quemado en curso para el mismo ensamblador.</b></font>";
*/
	$link = encode_link("listado_ordenes.php",array("msg"=>$msg));
	header("Location:$link");
}
//fin de comenzar orden
//parte de finalizar orden
if($_POST["Finalizar"]=="Finalizar P. de vida"){
   $sql_update="update orden_quemado set estado=2, sinc = 0, obs=obs||'\nSe finalizo la orden...' where nro_orden=".$_POST['orden'];
   if($db->Execute($sql_update)){
   		$msg = "<font color='green'><b>La orden de quemado fue finalizada.</b></font>";

   		//parte de logs de eventos del sistema
		$sql_next ="Select nextval('ordenes.logs_quemado_id_log_seq') as id_log";
		$resultado=$db->Execute($sql_next) or die($db->ErrorMsg()."<br>".$sql_next);
		$id_log = $resultado->fields['id_log'];
		$sql_log = "insert into logs_quemado (id_log,fecha,nro_orden,usuario,tipo) values 
		(".$id_log.",'".date("Y-m-d h:i:s")."',".$_POST['orden'].",'".$_ses_user["name"]."','Finalizo la orden de quemado')";
		$db->Execute($sql_log) or die($db->ErrorMsg()."<br>$sql_log");
		//fin de parte de logs
   }
   		else 
   		$msg = "<font color='red'><b>La orden de quemado no pudo finalizarse.</b></font>";
	$link = encode_link("listado_ordenes.php",array("msg"=>$msg));
	header("Location:$link");
}
//fin de finalizar orden

//parte de volver orden a pendiente 
if($_POST["volver_pendiente"]=="Volver a Pendiente"){
	$sql_update="update orden_quemado set estado=0, sinc = 0, obs=obs||'\nSe retorno la orden a pendiente...' where nro_orden=".$_POST['orden'];
	if($db->Execute($sql_update)) {
   		$msg = "<font color='green'><b>Se retorno la orden a pendiente.</b></font>";

		//parte de logs de eventos del sistema
		$sql_next ="Select nextval('ordenes.logs_quemado_id_log_seq') as id_log";
		$resultado=$db->Execute($sql_next) or die($db->ErrorMsg()."<br>".$sql_next);
		$id_log = $resultado->fields['id_log'];
		$sql_log = "insert into logs_quemado (id_log,fecha,nro_orden,usuario,tipo) values 
		(".$id_log.",'".$fecha."',".$_POST['orden'].",'".$_ses_user["name"]."','Retornó la orden a pendiente')";
		$db->Execute($sql_log) or die($db->ErrorMsg()."<br>$sql_log");
		//fin de parte de logs
   	}
   	else 
   		$msg = "<font color='red'><b>Error: La orden de quemado no se retorno correctamente.</b></font>";

   	$link = encode_link("listado_ordenes.php",array("msg"=>$msg));
	header("Location:$link");
}
//fin de volver orden a pendiente

echo $html_header;

//por si parametro no trae nada
if (!isset($parametros["id"])) {
	echo "<br> <h3> Error en parametro de entrada</h3>";
	fin_pagina();
}
$q="select nro_orden from orden_quemado where nro_orden=".$parametros["id"];
$r=sql($q) or fin_pagina();
if ($r->RecordCount()<1) {
	echo "<br> <h3>No se ha realizado la orden de Prueba de vida.</h3>";
	echo "<input type=button name=volver value='Volver' onClick='window.location=\"".$parametros["volver"]."\";'><br>\n";
	fin_pagina();
}

//consulta para la orden de quemado actual
$sql1 = "select fecha_orden,ensamblador.nombre,orden_de_produccion.cantidad,orden_quemado.estado,obs,orden_de_produccion.id_licitacion,
fecha_ini_quemado,fecha_fin_quemado,maq_quemadas,id_config,duracion,entidad.nombre as nom_cliente
,estado.nombre as estado_lic,estado.color as color_lic,usuarios.iniciales
from ordenes.orden_quemado 
left join ordenes.orden_de_produccion using (nro_orden)
left join licitacion on orden_de_produccion.id_licitacion = licitacion.id_licitacion
left join estado on licitacion.id_estado = estado.id_estado
left join entidad on orden_de_produccion.id_entidad = entidad.id_entidad
left join ordenes.ensamblador using (id_ensamblador) 
left join renglon using(id_renglon) 
left join ordenes.config_quemado using(id_config) 
left join sistema.usuarios on usuarios.id_usuario=licitacion.lider
where nro_orden = ".$parametros["id"];

$result = $db->Execute($sql1) or die($db->ErrorMsg()."<br>$sql1");

$datos["fecha_orden"] = $result->fields['fecha_orden'];
$datos["ensamblador"] = $result->fields['nombre'];
$datos["cantidad"] = $result->fields['cantidad'];
$datos["obs"] = $result->fields['obs'];
$datos["estado"] = $result->fields['estado'];
$datos["fecha_ini_quemado"] = $result->fields['fecha_ini_quemado'];
$datos["fecha_fin_quemado"] = $result->fields['fecha_fin_quemado'];
$datos["maq_quemadas"] = $result->fields['maq_quemadas'];
$datos["id_config"] = $result->fields['id_config'];
$datos["duracion"] = $result->fields['duracion'];
$datos["estado_lic"] = $result->fields['estado_lic'];
$datos["color_lic"] = $result->fields['color_lic'];
//consulto los logs para esta orden
$query ="select * from logs_quemado where nro_orden = ".$parametros["id"]." order by fecha asc";
$res_log = $db->Execute($query) or die($db->ErrorMsg()."<br>".$query);
$disabled_conf = 0;

//busco la cantidad de errores en esta orden
$sql_cantidad = "select * from reportes join reporteorden  on ( reportes.id_reporte = reporteorden.id_reporte)
                   where resultado = 3 and reporteorden.id_orden = ".$parametros["id"];
$res_cant = sql($sql_cantidad) or fin_pagina();
$cant_errores = $res_cant->RecordCount();
?>
<br>
<!-- Parte de logs del sistema de ordenes -->
<div style="overflow:auto;<? if ($res_log->RowCount() > 5) echo 'height:60;' ?> "  >
<FORM name="form1" method="POST" action="ver_orden.php">
<TABLE width="95%" align="center" class="bordes" id="mo" cellspacing="1" > 
<TR id="mo">
	<TD colspan="3">
	<FONT color="White"><b>
	<b>Sucesos relacionados a ordenes de Prueba de vida.</B>
	</FONT>
	</TD>
</TR>
<TR id="ma">
	<TD width="20%"> 
	<B><FONT color="Black">Fecha</FONT></B>
	</TD>
	<TD width="20%"> 
	<B><FONT color="Black">Usuario</FONT></B>
	</TD>
	<TD width="60%">
	<B><FONT color="Black">Suceso</FONT></B>
	</TD>
</TR>

<? while (!$res_log->EOF){ ?>
<TR id="ma">
	<TD>
	<?//=Fecha($res_log->fields['fecha'])?>
	<?=$res_log->fields['fecha']?>
	</TD>
	<TD>
	<?=$res_log->fields['usuario']?>
	</TD>
	<TD>
	<?=$res_log->fields['tipo']?>
	</TD>
</TR>
<? 
if ($res_log->fields['tipo']=="Retornó la orden a pendiente") $disabled_conf = 1;
$res_log->MoveNext(); } ?>
</TABLE>
</DIV>

<br>
<!-- Parte de mostrar la orden -->
<TABLE width='95%' class="bordes" align="center" cellspacing="1" cellpadding="5" bgcolor="Black">
<TR id=mo>
	<TD colspan="4" align="center" class="bordes">
		<FONT color="White"><b>
		<?
		switch ($datos["estado"]){
			case 0:
				echo 'Detalle de la orden de Prueba de vida pendiente';
			break;
			case 1:
				echo 'Detalle de la orden de Prueba de vida en curso';				
			break;
			case 2:
				echo 'Detalle de la orden de Prueba de vida desde el historial';				
			break;
		}
		?>
		</b></FONT>
	</TD>
</TR>
<tr id=ma>
<td colspan=2><FONT color="Black">Licitacion N°:</font> 
<? if($result->fields['id_licitacion']){ 
	$link = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$result->fields["id_licitacion"]));
	echo "<a href=$link title='Ver Licitación\nEstado: ".$datos["estado_lic"]."' target='_blank'>";
	echo "<span style='text-decoration:underline;background-color:".$datos["color_lic"].";color:".contraste($datos["color_lic"],"#006699","#ffffff").";'>";
	echo $result->fields['id_licitacion']."</span></a>";
	if ($result->fields['iniciales']) echo "&nbsp; <font color='black'>Lider:</font> ".$result->fields['iniciales'];
	
}
	else echo 'no hay lic.';
	?>
</td>
<td colspan="2"><FONT color="Black">Cliente:</font> <?=$result->fields['nom_cliente']?></td>
</tr>
<TR id=ma>
		<TD colspan=2 align="left" >
		<B><FONT color="Black">Seguimiento Orden de Producción</FONT></B>
		</TD>
		<TD colspan=2 align="center">
		<?
		if($result->fields['id_licitacion']!="")
		{
		 $re=sql("select id_entrega_estimada,id_subir,subido_lic_oc.nro_orden as numero,
			entrega_estimada.nro from 
			entrega_estimada left join subido_lic_oc  USING (id_entrega_estimada) 
			where entrega_estimada.id_licitacion=".$result->fields['id_licitacion']) or fin_pagina(); 
		 while (!$re->EOF) 
		 {
			$ref = encode_link("../ordprod/ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$result->fields["id_licitacion"],"id_entrega_estimada"=>$re->fields['id_entrega_estimada'],"nro"=>$re->fields["nro"],"nro_orden_cliente"=>$re->fields["numero"],"id_subir"=>$re->fields["id_subir"]));
	 	 ?>
	 	 <a href="<? echo $ref;?>" title="Ver orden de Produccion" target="_blank"><U><?=$re->fields("id_entrega_estimada")  //Orden de Produccion?></U></a>&nbsp;
		 <? 
			$re->MoveNext();
		 }//de while (!$re->EOF) 
		}//de if($result->fields['id_licitacion']!="")   
		 
		?>
		</TD>
</TR>
<TR id=ma>
		<TD align="left" >
		<B><FONT color="Black">Orden de Producción</FONT></B>
		</TD>
		<? 
		 $sql="select estado_bsas from ordenes.orden_de_produccion where nro_orden=".$parametros["id"];
		 $estado_bsas=sql($sql,"Error al traer el estado de bsas") or fin_pagina();
		 if ($estado_bsas->fields['estado_bsas']=='') {$bsas_color='red'; $bsas_title='Pendiente';}
	     if ($estado_bsas->fields['estado_bsas']==1) {$bsas_color='yellow'; $bsas_title='En Producción';}
	     if ($estado_bsas->fields['estado_bsas']==2) {$bsas_color='green'; $bsas_title='Historial';}
        ?>
		<TD align="center" title="Estado: <?=$bsas_title?>">		       
		<a href="<? echo encode_link("../ordprod/ordenes_nueva.php",Array("nro_orden"=>$parametros["id"],"modo"=>"modificar"));?>" target="_blank"><span style='background-color:<?=$bsas_color?>;color:<?=contraste($bsas_color,"#006699","#ffffff")?>'><font color="Black"><U> <?=$parametros["id"]//Orden de Produccion?> </U></font></span></a>		
		</TD>

		<TD align="left">
		<B><FONT color="Black">Ensamblador</FONT></B> 
		</TD>
		<TD align="center">
		<?=$datos["ensamblador"] //nombre del esnsamblador?> 
		</TD>

</TR>
<TR id=ma>
		<TD align="left">
		<B><FONT color="Black">Fecha de activación</FONT></B> 
		</TD>
		<TD align="center">
  		<? if ($datos['fecha_orden'] == NULL) echo "No Activo";
  			else echo Fecha($datos['fecha_orden']);?>
		</TD>
		<TD align="left">
		<B><FONT color="Black">Cantidad de máquinas involucradas en esta orden</FONT></B> 
		</TD>
		<TD align="center">
		<?=$datos["cantidad"]?> 
		</TD>
</TR>
<TR id=ma>
		<TD align="left">
		<B><FONT color="Black">Fecha del fin de la Prueba de vida</FONT></B> 
		</TD>
		<TD align="center">
  		<? if ($datos['fecha_fin_quemado'] == NULL) echo "No se termino";
  			else echo Fecha($datos['fecha_fin_quemado']);?>
		</TD>
		<?
////////////////////////////////// GABRIEL //////////////////////////////////////////////
$consulta="select nro_serie, mac, resultado
	from ordenes.reportes 
		join ordenes.reporteorden  on ( reportes.id_reporte = reporteorden.id_reporte)
	where resultado=1 and reporteorden.id_orden =".$parametros["id"]." group by nro_serie, mac, resultado";
$rta_consulta=sql($consulta, "c397 ") or fin_pagina();
$gMaquinasOk=$rta_consulta->recordCount();
?>
		<td align="left">
			<font color="Black">Cantidad de máquinas con al menos un reporte exitoso
		</td>
		<td <?=(($gMaquinasOk>=$datos["cantidad"])?" bgcolor='green' ":"")?>>
			</font> <?=(($gMaquinasOk)?$gMaquinasOk:"0")?>
		</td>
<?
/////////////////////////////////////////////////////////////////////////////////////////
?>
</TR>

<?//parte opcional
switch ($datos["estado"]){
	case 1:{
	echo '
<TR id=ma>
		<TD align="left">
		<B><FONT color="Black">Fecha de inicio de la Prueba de vida</FONT></B>
		</TD>
		<TD align="center">';
		if ($datos["fecha_ini_quemado"] == "")
		echo"<font color='red'><b>No iniciado</b></font>";
		else echo Fecha($datos["fecha_ini_quemado"]);
	if ($datos["activa"] == 1) $chk = 'checked'; else $chk = '';
	echo /*'</TD>
		<TD align="left">
		<B><FONT color="Black">Cantidad de máquinas quemadas correctas hasta el momento</FONT></B>
		</TD>
		<TD align="center">'.
		$datos["maq_quemadas"].*/
		'<td colspan="2">&nbsp;</td>
</TR>';
/*<TR id=ma>
		/*<TD align="left">
		<B><FONT color="Black">Cantidad de Máquinas con errores</FONT></B>
		</TD>
		<TD align="center" class="bordes">
		<font color = "red">
		<b>'.$cant_errores.'</b>
		</font>
		</TD>
		<TD colspan="2">
		</TD>
</TR>';*/
	}
	break;
	case 2:{
	echo '
<TR id=ma>
		<TD align="left">
		<B><FONT color="Black">Período de duración de la Prueba de vida</FONT></B>
		</TD>
		<TD align="center">';
	if(Fecha($datos["fecha_ini_quemado"])=='')
		echo 'No hubo Prueba de vida';
	else
		echo Fecha($datos["fecha_ini_quemado"]).' hasta '.Fecha($datos["fecha_fin_quemado"]);
	echo '
		</TD>
		<TD align="left" colspan="2">
		<!--<B><FONT color="Black">Cantidad de Máquinas con errores </FONT></B>-->&nbsp;
		</TD>'
		/*<TD align="center" class="bordes">
		<font color = "red">
		<b>'.$cant_errores.'</b>
		</font>
		</TD>*/


	.'</TR>';
	}
	break;
}
?>
<tr id="ma">
	<TD align="left">
		<B><FONT color="Black">Configuración del tiempo de Prueba de vida 2X</FONT></B> 
	</TD>
	<TD align="center">
		<?
		$sql_config = "Select * from config_quemado where id_config <> ".$datos["id_config"]." order by duracion asc";
		$result_config = $db->Execute($sql_config) or die($db->ErrorMsg()."<br>$sql_config");
		?>
		<SELECT name="tipo" <?if ($datos["estado"] != 0 || $disabled_conf) echo "disabled"; ?> onchange="tipo_change()"> 
			<OPTION selected  value="<?=$datos["id_config"]?>"><?=$datos["duracion"]?> horas</OPTION>
			<?
			while(!$result_config->EOF) {
				echo "<option value='".$result_config->fields["id_config"]."'>".$result_config->fields["duracion"]." horas</opttion>";
				$result_config->MoveNext();
			}
			?>
		</SELECT>
	</TD>
	<td colspan="2">
		&nbsp;
	</td>
</tr>


<TR id=ma>
	<TD align="center" colspan="4">
	<TABLE width='70%' border="0" align="center">
	<TR>
		<TD align="center" colspan="2">
		<b>Observaciones</b><br>
		<TEXTAREA cols="70" rows="5" name="Observacion" onkeypress="obs_change()"><?=$datos["obs"]?></TEXTAREA>
		</TD>
	</TR>
	<TR>
		<TD align="center" colspan="2">
		<?
		echo '<input type="button" name="volver" value="volver" onclick = "document.location= '."'listado_ordenes.php'".'">';
		echo '<INPUT type="submit" name="act" value="Actualizar" disabled>';
		echo '<input type="hidden" name="orden" value="'.$parametros["id"].'">';
		
		$link = encode_link("ver_reportes.php",array("id"=>$parametros["id"],"cant"=>$datos["cantidad"]));//link para pagina nueva
         $link_config = encode_link("comprobar_configuracion.php",array("id"=>$parametros["id"]));//link para pagina nueva
 

		//if ($datos['maq_quemadas'] > 0 || $cant_errores > 0)
		if ($datos["fecha_ini_quemado"] != "") {
			echo "<INPUT type='button' name='ver_reportes' value='Ver Reportes' onclick=window.open('$link','','left=40,top=10,width=700,height=470,resizable=1,scrollbars=1,status=1')>";
			echo "<INPUT type='button' name='configuracion' value='Comprobar Configuracion' onclick=window.open('$link_config','','left=40,top=10,width=600,height=270,resizable=1,scrollbars=1,status=1')>";
		}
		
		switch ($datos["estado"]){
			case 0:
				echo '<INPUT type="submit" name="enviar" value="Comenzar">';				
			break;
		}
		?>
		</TD>
	</TR>
	<?if ($datos["estado"]==1){?>
	<TR>
		<TD align="center">
		<INPUT type="checkbox" name="manejar_estado" onclick="if (this.checked) document.all.div.style.display = 'block'; else document.all.div.style.display = 'none';"> Manejar el estado
		</TD>
		<td align="center">
		<div id='div'  style='display:none'><br>
<?
		if(!permisos_check("inicio","finalizar_quemado") ) $disabled = "Disabled";
		if(!permisos_check("inicio","volver_pendiente") ) $disabled_pend = "Disabled";
		echo "<INPUT type='submit' name='volver_pendiente' value='Volver a Pendiente' $disabled_pend>";
		echo "<INPUT type='submit' name='Finalizar' value='Finalizar P. de vida' $disabled>";				
?>
		</DIV>
		</TD>
		<TD>
	</TR>
	<?}?>
	</TABLE>
	</TD>
</TR>
</TABLE>
</FORM>
</DIV>	 
<SCRIPT>
var obs
function obs_change(){
	document.all.form1.act.disabled = 0;		
}
function tipo_change(){
	document.all.form1.act.disabled = 0;		
}
function check(obj){
	if (obj.checked) {
		alert();
	}
}
</SCRIPT>
<?
echo fin_pagina();
?>