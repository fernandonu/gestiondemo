<?php
/*AUTOR: Gabriel
$Author: ferni $
$Revision: 1.6 $
$Date: 2007/01/02 20:09:01 $
*/

/* onclick="if (confirm('¿Desea crear una referencia para esta licitación?')){
		window.open('<?=encode_link("../modulo_clientes/cliente_referencia_editar.php", 
			array("modo"=>"nuevo", "pagina"=>"seguimiento_orden", "id_licitacion"=>$id))?>',
			'','toolbar=0,location=0,directories=0,status=0,resizable=1, menubar=0,
			scrollbars=1,left=190,top=0,width=640,height=300');}"
			*/
	if ($parametros["pagina"]!="seguimiento_orden"){
		require("../../config.php");
	}
	$pagina=$parametros["pagina"] or $pagina=$_POST["pagina"];
	$modo=$parametros["modo"] or $modo=$_POST["modo"];
	$id_cliente_referencia=$parametros["id_cliente_referencia"] or $id_cliente_referencia=$_POST["id_cliente_referencia"];
	$nro_licitacion=$parametros["nro_licitacion"] or $nro_licitacion=$_POST["nro_licitacion"];
	$id_licitacion=$parametros["id_licitacion"] or $id_licitacion=$_POST["id_licitacion"];
	$entidad=$parametros["entidad"] or $entidad=$_POST["entidad"];
	$direccion=$parametros["direccion"] or $direccion=$_POST["direccion"];
	$distrito=$parametros["distrito"] or $distrito=$_POST["distrito"];
	$monto=$parametros["monto"] or $monto=$_POST["monto"];
	$contacto=$parametros["contacto"] or $contacto=$_POST["contacto"];
	$tel_contacto=$parametros["tel_contacto"] or $tel_contacto=$_POST["tel_contacto"];
	$detalle=$parametros["detalle"] or $detalle=$_POST["detalle"];
	$fecha_apertura=$parametros["fecha_apertura"] or $fecha_apertura=Fecha_db($_POST["fecha_apertura"]);
	$fecha_entrega=$parametros["fecha_entrega"] or $fecha_entrega=Fecha_db($_POST["fecha_entrega"]);
	$moneda=$parametros["moneda"] or $moneda=$_POST["moneda"];

	if (!$monto) $monto=0;
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if ($modo=="nuevo"){
		$rta=sql("select nextval('clientes_referencia_id_cliente_referencia_seq') as id_cteref");
		$id_cliente_referencia=$rta->fields["id_cteref"];
		$titulo_tabla="Cliente de referencia nuevo nro. ".$id_cliente_referencia;
		$modo="modif";
		$new="disabled";
	}elseif ($modo=="modif"){
		$titulo_tabla="Modificación de la referencia nro. ".$id_cliente_referencia;
	}else fin_pagina();
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if (($_POST["traerDatos"])||($pagina=="seguimiento_orden")){
		$consulta="select licitacion.id_licitacion, licitacion.nro_lic_codificado, licitacion.fecha_apertura, licitacion.fecha_entrega, 
				moneda.simbolo as moneda, licitacion.monto_ganado, entidad.nombre, entidad.direccion, 
				entidad.localidad||' ('||distrito.nombre||')' as distrito
			from licitaciones.licitacion
				join licitaciones.entidad using (id_entidad)
				join licitaciones.distrito using (id_distrito)
				left join licitaciones.moneda using (id_moneda)
			where licitacion.id_licitacion=".$id_licitacion;
		if ($id_licitacion){
			$rta_consulta=sql($consulta, "c68") or fin_pagina();
		}
		if (($rta_consulta)&&($rta_consulta->recordCount()==1)){
			$fila=$rta_consulta->fetchRow();
			$nro_licitacion=$fila["nro_lic_codificado"];
			$entidad=$fila["nombre"];
			$direccion=$fila["direccion"];
			$distrito=$fila["distrito"];
			$monto=$fila["monto_ganado"];
			$fecha_apertura=$fila["fecha_apertura"];
			$moneda=(($fila["moneda"])?$fila["moneda"]:"$");
			$modo="modif";
			$new="";
		}else{
			$mensaje="<h3><center><b><font color='red'>No se encontró la licitación.</font></b></center></h3>";
			$modo="nuevo";
			$new="algo";
		}
	}
	if ($_POST["bguardar"]){
		$monto=$_POST["t_monto"];		
		$temporal=sql("select * from licitaciones.clientes_referencia where id_cliente_referencia=".$id_cliente_referencia, "c62") or fin_pagina();
		if ($temporal->recordCount()==0){
			$sql="insert into licitaciones.clientes_referencia (id_cliente_referencia, id_licitacion, distrito, entidad, direccion,
  			fecha_apertura, nro_licitacion, moneda, monto, fecha_entrega, contacto, tel_contacto, detalle)";
			$sql.="values ($id_cliente_referencia, $id_licitacion, '$distrito', '$entidad', '$direccion', "
				.(($fecha_apertura)?"'".$fecha_apertura."'":"null").", '$nro_licitacion', '$moneda', ".(($monto)?$monto:"0").", "
				.(($fecha_entrega)?"'".$fecha_entrega."'":"null").", '$contacto', '$tel_contacto', '$detalle')";
		}else{
			$sql="update licitaciones.clientes_referencia set ";
			$sql.="fecha_entrega=".(($fecha_entrega)?"'".$fecha_entrega."'":"null").", contacto='$contacto', 
				tel_contacto='$tel_contacto', detalle='$detalle',monto=".(($monto)?$monto:"0")."";
			$sql.=" where id_cliente_referencia=".$id_cliente_referencia;
		}
		sql($sql, "c76 - Error al agregar/actualizar registro") or fin_pagina();
		if ($pagina=="seguimiento_orden"){
			?>
				<script>
					window.close();
				</script>
			<?
		}
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	cargar_calendario();
	echo($html_header);
	if($parametros['accion']!=""){ Aviso($parametros['accion']);}
	//////////////////////////////////////////////////////////////////////////////
	echo($mensaje);
	?>
	<form name="form1" method="POST" action="cliente_referencia_editar.php">
		<input type="hidden" name="modo" value="<?=$modo?>">
		<input type='hidden' name='id_cliente_referencia' value='<?=$id_cliente_referencia?>'>
		<input type='hidden' name='moneda' value='<?=$moneda?>'>
		<input type='hidden' name='monto' value='<?=$monto?>'>
		<input type='hidden' name='pagina' value='<?=$pagina?>'>

		<table border="1" cellspacing="0" bgcolor="<?=$bgcolor2?>" width="90%" align="center">
			<th align="center" colspan="4" id="mo"><?=$titulo_tabla?></th>
			<tr>
				<td id="mo">
					Id. licitación:
				</td>
				<td colspan="3" nowrap>
					<input type="text" name="id_licitacion" id="id_licitacion" value="<?=$id_licitacion?>">
					<input type="submit" name="traerDatos" value="Traer datos de licitación">
					<input type="button" name="buscar" value="Buscar" onclick="window.open('<?=encode_link("../licitaciones/licitaciones_view.php", array())?>','','toolbar=0,location=0,directories=0,status=0,resizable=1, menubar=0,scrollbars=1,left=190,top=0,width=800,height=600');">
				</td>
			</tr>
			<tr>
				<td id="mo" width="25%">Entidad:</td>
				<td width="25%"><input type="text" name="entidad" id="entidad" class="text_4" value="<?=$entidad?>" style="width:'100%'"></td>
				<td id="mo" width="25%">Distrito:</td>
				<td width="25%"><input type="text" name="distrito" id="distrito" class="text_4" value="<?=$distrito?>" style="width:'100%'"></td>
			</tr>
			<tr>
				<td id="mo">Dirección</td>
				<td colspan="3"><input type="text" name="direccion" id="direccion" class="text_4" value="<?=$direccion?>" style="width:'100%'"></td>
			</tr>
			<tr>
				<td id="mo">Nro. de licitación:</td>
				<td><input type="text" name="nro_licitacion" id="nro_licitacion" class="text_4" value="<?=$nro_licitacion?>" style="width:'100%'"></td>
				<td id="mo">Monto:</td>
				<td><?=$moneda?> <input type="text" name="t_monto" id="t_monto" value="<?=number_format($monto,2,'.','')?>" style="width:'90%'"></td>
			</tr>
			<tr>
				<td id="mo">Fecha de apertura:</td>
				<td align="left"><input type="text" name="fecha_apertura" id="fecha_apertura" class="text_4" value="<?=Fecha($fecha_apertura)?>" style="width:'100%'"></td>
				<td id="mo">
					Fecha de entrega:
				</td>
				<td>
					<input type="text" name="fecha_entrega" id="fecha_entrega" value="<?=Fecha($fecha_entrega)?>"></input>&nbsp;<?=link_calendario("fecha_entrega")?>
				</td>
			</tr>
			<tr>
				<td id="mo">Contacto:</td>
				<td><input type="text" name="contacto" id="contacto" value="<?=$contacto?>" style="width:'100%'"></td>
				<td id="mo">Teléfono:</td>
				<td><input type="text" name="tel_contacto" id="tel_contacto" value="<?=$tel_contacto?>" style="width:'100%'"></td>
			</tr>
			<tr>
				<td id="mo">Detalle</td>
				<td colspan="3">
					<textarea cols="80" rows="4" id="detalle" name="detalle"><?=$detalle?></textarea>
				</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" bgcolor="<?=$bgcolor2?>" width="90%" align="center">
			<tr>
				<td align="center">
					<input type="submit" name="bguardar" value="Guardar cambios" onclick="<?=(($pagina=="seguimiento_orden")?"window.document.form1.submit();":"")?>">
					<input type="button" name="volver" value="<?=(($pagina=="seguimiento_orden")?"Cerrar":"Volver")?>" onclick="<?=(($pagina=="seguimiento_orden")?"window.close();":"document.location='clientes_referencia.php'")?>">
				</td>
			</tr>
		</table>
		<br>
<?
	echo "</form>";
	fin_pagina();
?>