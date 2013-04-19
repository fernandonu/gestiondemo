<?php
/*
AUTOR: Gabriel (creado a partir de ordenes_nueva.php)
MODIFICADO POR:
$Author: gabriel $
$Revision: 1.7 $
$Date: 2005/09/20 20:08:52 $
*/

require_once("../../config.php");
if ($_GET["modo"]) $modo=$_GET["modo"];
else $modo=$parametros["modo"] or $modo=$_POST["modo"];
$volver=$_POST["volver"] or $volver=$parametros["volver"];
if (!$modo) $modo="modificar";
$nro_orden=$_POST["nro_orden"] or $nro_orden=$parametros["nro_orden"] or $nro_orden=$_GET["nro_orden"];
$id_licitacion=$parametros["id_licitacion"];
$id_renglon=$parametros["id_renglon"];

echo $html_header;

	cargar_calendario();
	$sql="select titulo_etiqueta,descripcion_etiqueta,orden_de_produccion.id_entidad,orden_de_produccion.id_licitacion,orden_de_produccion.id_renglon,orden_de_produccion.id_ensamblador"
		.",orden_de_produccion.fecha_inicio,orden_de_produccion.fecha_entrega,orden_de_produccion.lugar_entrega"
		.",orden_de_produccion.nserie_desde,orden_de_produccion.nserie_hasta,orden_de_produccion.desc_prod as titulo,orden_de_produccion.cantidad"
		.",orden_de_produccion.comentario,orden_de_produccion.estado,adicionales,rechazada,id_sistema_operativo"
		.",entidad.nombre,entidad.direccion,renglon.codigo_renglon from orden_de_produccion "
		."left join entidad using(id_entidad) "
		."left join renglon using(id_renglon) where nro_orden=$nro_orden";
		
	$licitacion=$db->execute($sql) or die ($db->errormsg(). " - $sql");
	if ($licitacion->RecordCount()>0) $estfield="readonly";
	else $estfield="";
	$entidad=$_POST["id_entidad"] or $entidad=$licitacion->fields["id_entidad"];
	$id_sistema_operativo=$_POST["sist_instalado"] or $id_sistema_operativo=$licitacion->fields["id_sistema_operativo"];
	$fechainicio=$_POST["fechainicio"] or $fechainicio=fecha($licitacion->fields["fecha_inicio"]) or $fechainicio=date("d/m/Y");
	$fechaentrega=$_POST["fechaentrega"] or $fechaentrega=fecha($licitacion->fields["fecha_entrega"]);
	$comentario=$_POST["comentario"] or $comentario=$licitacion->fields["comentario"];
	$cliente=$_POST["cliente"] or $cliente=$licitacion->fields["nombre"];
	$direccion=$_POST["direccion"] or $direccion=$licitacion->fields["direccion"];
	$lugar_entrega=$_POST["lugar_entrega"] or $lugar_entrega=$licitacion->fields["lugar_entrega"] or $lugar_entrega=$licitacion->fields["direccion"];
	$desc_prod=$_POST["desc_prod"] or $desc_prod=$licitacion->fields["titulo"];
	$cant_prod=$_POST["cant_prod"] or $cant_prod=$licitacion->fields["cantidad"];
	$serialp=$_POST["serialp"] or $serialp=$licitacion->fields["nserie_desde"];
	$serialu=$_POST["serialu"] or $serialu=$licitacion->fields["nserie_hasta"];
	$adicionales=$_POST["adicionales"] or $adicionales=$licitacion->fields["adicionales"];
	$rechazada=$_POST["rechazada"] or $rechazada=$licitacion->fields["rechazada"];
	$id_licitacion=$parametros["id_licitacion"] or $id_licitacion=$licitacion->fields["id_licitacion"];
	$codigo_renglon=$parametros["codigo_renglon"] or $codigo_renglon=$licitacion->fields["codigo_renglon"];
	if (!$id_renglon) $id_renglon=$parametros["id_renglon"] or $id_renglon=$licitacion->fields["id_renglon"];
	$estado=$parametros["estado"] or $estado=$licitacion->fields["estado"];
  $titulo_etiqueta=$parametros["titulo_etiqueta"] or $titulo_etiqueta=$licitacion->fields["titulo_etiqueta"];
  $descripcion_etiqueta=$parametros["descripcion_etiqueta"] or $descripcion_etiqueta=$licitacion->fields["descripcion_etiqueta"];
  $titulo_etiqueta=($titulo_etiqueta=="")?$desc_prod:$titulo_etiqueta;
	if (!$msg) $msg=$parametros["msg"] or $msg=$_POST["msg"];
	if ($nro_orden) {
		$sql="SELECT fecha,descripcion,nombre,apellido from log_ord_prod "
			."left join usuarios using(id_usuario) where nro_orden=$nro_orden order by fecha DESC";
		$log=$db->execute($sql) or die($db->errormsg());
		echo "<div style='overflow:auto;";
		if ($log->RowCount() > 3) echo "height:60;";
		echo "'>";
		echo "<table width='95%' cellspacing=0 border=1 bordercolor=#E0E0E0 align='center' bgcolor=#cccccc>";
		while ($fila=$log->FetchRow()) {
			echo "<tr>";
			echo "<td height='20' nowrap>Fecha ".$fila["descripcion"]." ".date("j/m/Y H:i:s",strtotime($fila["fecha"]))."</td>";
			echo "<td nowrap > Usuario : ".$fila["nombre"]." ".$fila["apellido"]."</td>";
			echo "</tr>";
		}
		echo "</table></div>";
	}

if ($modo=="borrar_archivo") {
	$id_archivo=$parametros["id_archivo"];
	$filename=$parametros["filename"];
	$db->beginTrans();
	$query="delete from archivos_ordprod where id_archivo=$id_archivo and nro_orden=".$parametros["nro_orden"];
	sql($query) or $error="Error al eliminar el Archivo $filename.";
	$query="delete from subir_archivos where id=$id_archivo";
	sql($query) or $error="Error al eliminar el Archivo $filename.";
	if (!$error) {
		if (unlink(UPLOADS_DIR."/archivos/$filename")) {
			$db->commitTrans();
			aviso("El archivo $filename se elimino correctamente.");
		}
		else {
			$db->Rollback();
			error("No se pudo eliminar el archivo $filename");
		}
	}
	else {
		$db->Rollback();
		error("No se pudo eliminar el archivo $filename");
	}
}

	
	echo "<form name='form_auditorias' action='ordenes_nueva_audit.php' method='POST'>";
	if ($nro_orden) echo "<input type=hidden name=nro_orden value='$nro_orden'>";
	echo "<input type=hidden name=id_entidad value='$entidad'>";
	echo "<input type=hidden name=volver value='$volver'>";
	echo "<input type=hidden name=id_renglon value='$id_renglon'>";
	echo "<input type=hidden name=id_licitacion value='$id_licitacion'>";
	if ($msg) aviso($msg);
	$est=array("A"=>"Autorizada", "AN"=>"Anulada", "T"=>"Terminada", "PA"=>"Para Autorizar", "P"=>"Pendiente", "R"=>"Rechazada",	"E"=>"Enviada"); 
	if ($rechazada) {
		$anexo="<tr bgcolor=$bgcolor_out><td colspan=2>
			<font size=2><font color=yellow>ADVERTENCIA:</font> La orden fue rechazada.<br>
			<br>Motivo del rechazo: <b>$rechazada</b></font></td></tr>";
	}	
	$link2=encode_link('../licitaciones/licitaciones_view.php',array("ID"=>$id_licitacion,"cmd1"=>"detalle"));
	$link_op=encode_link("ordenes_nueva.php",array("nro_orden"=>$nro_orden,"modo"=>"modificar","volver"=>"seguimiento_produccion_bsas_audit","cmd"=>"$cmd"));
?>
	<table width='95%' align='center'>
		<?=$anexo?>
		<tr bgcolor=<?=$bgcolor_out?>>
			<td>
				<a target='_blank' href='<?=$link_op?>'><font size=2><b><u>Orden de producci&oacute;n Nro: <?=$nro_orden?></u></b></font><br></a>
				<a target='_blank' href='<?=$link2?>'><font size=2><b><u>Asociada a la Licitaci&oacute;n ID: <?=$id_licitacion?></u></b></font></a>

				<script>var warchivos=0;</script>
				<input name="pasa_id" type="hidden" value="<?=$id_licitacion?>">
				<input name="pasa_titulo" type="hidden" value="<?=$titulo_etiqueta?>">
				<input name="pasa_cantidad" type="hidden" value="<?=$cant_prod?>">
				<input name="pasa_descripcion" type="hidden" value="<?=$descripcion_etiqueta?>">
				<input name="pasa_cliente" type="hidden" value="<?=$cliente?>">
			</td>
			<td>
				Cliente: <b><?=$cliente?></b><br>Direcci&oacute;n: <?=$direccion?>
			</td>
		</tr>
	</table>
	<table width='95%' align='center'>
		<tr id=mo>
			<td colspan=2>
				<font size=2>Productos</font>
			</td>
		</tr>
		<tr id=mo>
			<td width="10%">Cantidad</td><td>Descripci&oacute;n</td>
		</tr>
		<tr bgcolor="<?=$bgcolor_out?>">
				<td align="center"><?=$cant_prod?></td><td><?=$titulo_etiqueta;?></td>
			</tr>
	</table>
	<br>
<?
	$q = "SELECT subir_archivos.*,usuarios.nombre ||' '|| usuarios.apellido as nbre_completo ";
	$q.= "FROM subir_archivos ";
	$q.= "join usuarios on subir_archivos.creadopor=usuarios.login ";
	$q.= "join archivos_ordprod on id_archivo=subir_archivos.id ";
	$w = "where nro_orden=$nro_orden";
	$rs=sql($q.$w) or fin_pagina();
?>
	<table width=95% align=center cellpadding=0 cellspacing=6 border=1 bordercolor="#111111">
		<tr> 
			<td id="ma_mg" > Archivos </td> 
		</tr>
		<tr>
			<td>
				<table width='100%'  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
					<tr>
 						<td colspan=7 style='border-right: 0;' id=ma style="text-align:left">
 							<b>Total:</b><?=$total_archivos=$rs->recordcount() ?>
						</td>
					</tr>
		<?
			if ($total_archivos>0){
		?>
					<tr>
						<td align=right id=mo>Archivo</td>
						<td align=right id=mo>Fecha</td>
						<td align=right id=mo>Subido por</td>
						<td align=right id=mo>Tamaño</td>
						<td align=center id=mo>&nbsp;</td>
					</tr>
		<? 
				while (!$rs->EOF) {
					echo "<tr style='font-size: 9pt'><td align=center>";
 					if (is_file("../../uploads/archivos/".$rs->fields["nombre"])) echo "<a target=_blank href='".encode_link("../archivos/archivos_lista.php", array ("file" =>$rs->fields["nombre"],"size" => $rs->fields["size"],"cmd" => "download"))."'>";
				  echo $rs->fields["nombre"]."</a></td>";
		?>    
  	  			<td align=center>&nbsp;<?= Fecha($rs->fields["fecha"]) ?></td>
				    <td align=center>&nbsp;<?= $rs->fields["nbre_completo"] ?></td>
				    <td align=center>&nbsp;<?= $size=number_format($rs->fields["size"] / 1024); ?> Kb</td>
	    			<td align=center>
		<?    
					$lnk=encode_link("$_SERVER[PHP_SELF]",Array("nro_orden"=>$nro_orden,"id_archivo"=>$rs->fields["id"],"filename"=>$rs->fields["nombre"],"modo"=>"borrar_archivo"));
		      echo "<a href='$lnk'><img src='../../imagenes/close1.gif' border=0 alt='Eliminar el archivo: \"". $rs->fields["nombre"] ."\"'></a>";
	  	    echo "</td></tr>";
   				$rs->MoveNext();
				}
			}
		?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="right" colspan=5>
				<input type="button" name="bagregar" value="Agregar Archivo" style="width:105" onclick="if (typeof(warchivos)=='object' && warchivos.closed || warchivos==false) warchivos=window.open('<?= encode_link($html_root.'/modulos/archivos/archivos_subir.php',array("onclickaceptar"=>"window.opener.location.reload();","nro_orden"=>$nro_orden,"proc_file"=>"../ordprod/orden_file_proc.php")) ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1'); else warchivos.focus()">
			</td>
		</tr>
	</table>
	<br>
	<table width='95%' align="center"  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
		<tr id=mo>
			<td colspan=2>
				<font size=2>Auditor&iacute;as</font>
			</td>
		</tr>
		<? 
		//////////////////////////////////////////////
		if (($_POST["baprobo"])||($_POST["breprobo"])){
			$sql="insert into auditorias (nro_orden, estado, usuario, fecha_hora, accion) values (".$nro_orden.", ";
			if ($_POST["baprobo"]){
				$sql.="'true', ";
				$sql_estado="update orden_de_produccion set estado_audit='t' where nro_orden=".$nro_orden;
				sql($sql_estado) or fin_pagina();//update
			}else{
				$sql.="'false', ";
			}
			$sql.="'".$_ses_user["name"]."', '".date("Y-m-d H:i:s")."', '".$_POST["accion_reproceso"]."')";
			sql($sql) or fin_pagina();//insert
			$sql="select id_licitacion, mail, nro_orden, estado_audit, orden_de_produccion.fecha_entrega 
        from ordenes.orden_de_produccion 
        left join licitaciones.licitacion using (id_licitacion)
        left join sistema.usuarios on (id_usuario=lider)
        where nro_orden=".$nro_orden;
	    $result_audit=sql($sql,"No se pudo traer el lider de la Licitación") or fin_pagina();//select
	    if ($result_audit->fields['estado_audit']=='t') $auditoria="aprobada";
			else $auditoria="reprobada";
	    $mail = array (0 => "juanmanuel@coradir.com.ar", 1 => "carlos@coradir.com.ar", 2 => "aranzubia@coradir.com.ar",
				3 => "valentino@coradir.com.ar", 4 => "andrada@coradir.com.ar");
	    if ($result_audit->RecordCount()>0) $mail[5]=$result_audit->fields['mail'];
	    $para=elimina_repetidos($mail,0);
		  //$para="broggi@coradir.com.ar,nazabal@coradir.com.ar";             
	    $asunto="Auditoría de calidad de Orden de Producción Nº ".$nro_orden;
	    $mensaje="La Orden de Producción Nº ".$nro_orden." está ".$auditoria;
	    $mensaje.="\n--------------------------Breve Descripción de la Orden--------------------------";
	    $mensaje.="\nID. Licitación:        ".$id_licitacion;
	    $mensaje.="\nCliente:               ".$cliente;
	    $mensaje.="\nFecha Entrega:         ".Fecha($result_audit->fields["fecha_entrega"]);
	    $mensaje.="\nCantidad de Maquinas:  ".$cant_prod;
	    $mensaje.="\n----------------------------------------------------------------------------------";
	    $mensaje.="\nAcción de reproceso:\n".$_POST["accion_reproceso"]."\n----------------------------------------------------------------------------------";
	    $mensaje.="\nEl cambio se realizó el día ".date("d/m/Y").", por el Usuario ".$_ses_user['name'];
	    enviar_mail($para,$asunto,$mensaje,"","","",0);
		}else{
			$resul=sql("select * from orden_de_produccion where nro_orden=".$nro_orden) or fin_pagina();
			if ($resul->fields["estado_audit"]=="t") $auditoria="aprobada";
			else $auditoria="reprobada";
		}
			
  	//////////////////////////////////////////////
		if ((permisos_check("inicio", "ord_prod_audit"))&&($auditoria=="reprobada")){ 
			echo '<tr><td colspan=2>Acci&oacute;n de reproceso: <textarea rows="3" cols="80" name="accion_reproceso"></textarea></td></tr>';
			echo '<tr><td align=center><input type="submit" name="baprobo" style="background-color:#66BB66" align="middle" value="Aprob&oacute; auditor&iacute;a de calidad. Listo para entrega"></td>
			<td align="center">	<input type="submit" name="breprobo" style="background-color:#BB5555" align="middle" value="REPRUEBA auditor&iacute;a de calidad. No se puede entregar"></td></tr>';
		}else{
			echo '<tr><td align=center><input disabled type="submit" name="baprobo" style="background-color:#66BB66" align="middle" value="Aprob&oacute; auditor&iacute;a de calidad. Listo para entrega"></td>
				<td align="center"><input disabled type="submit" name="breprobo" style="background-color:#BB5555" align="middle" value="REPRUEBA auditor&iacute;a de calidad. No se puede entregar"></td></tr>';
		}
		echo "</table>";
	////////////////////////////////////////////////////////////////////////////
	$sql='select * from auditorias where nro_orden='.$nro_orden;
	$rs=sql($sql) or fin_pagina();
	if ($rs->recordcount()>0){
		?>
	<table width='95%' align="center"  border='1' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
		<tr>
			<td width="10%" align=right id=mo>Estado</td>
			<td width="20%" align=right id=mo>Usuario</td>
			<td width="15%" align="right" id=mo>Fecha y hora</td>
			<td align="right" id="mo">Acci&oacute;n de reproceso</td>
		</tr>
		<?
		
		while (!$rs->EOF){
			echo "<tr>";
			if ($rs->fields["estado"]=='t'){
				$estado_audit="Aprob&oacute;";
				$color_audit="#66BB66";
			}else{
				$estado_audit="Reprob&oacute;";
				$color_audit="#BB7777";
			}
			$usuario_audit=$rs->fields["usuario"];
			$date_long_audit=$rs->fields["fecha_hora"];
			$fecha_audit=Fecha(substr($date_long_audit, 0, 10))." ".substr($date_long_audit, 11, 5)." hs.";
			$accion_audit=$rs->fields["accion"];
			?>
				<td bgcolor="<?=$color_audit?>"><?=$estado_audit?></td>
				<td><?=$usuario_audit?></td>
				<td><?=$fecha_audit?></td>
				<td><textarea name="no_importa" cols="80" rows="2" readonly><?=$accion_audit?></textarea></td>
				</tr>
			<?
			$rs->MoveNext();
		}
	}
?>
	</table>
	<table width='95%' align="center"  border='0' cellpadding='2' cellspacing='1' bgcolor='<?=$bgcolor3?>' bordercolor='#ffffff'>
		<tr>
			<td align="center">
				<input type="button" name="volver" Value="Volver" onclick="document.location='seguimiento_produccion_bsas_audit.php'">
			</td>
		</tr>
	</table>
</form>
<?
fin_pagina();
?>