<?php
/*
///////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////// OBSOLETO ///////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
// LA FUNCIONALIDAD DE ESTA PÁGINA FUE INCORPORADA A LA PAGINA ////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
// PRODUCCTION/PRODUCCION BS AS ///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
AUTOR: Gabriel
MODIFICADO POR:
$Author: gabriel $
$Revision: 1.14 $
$Date: 2006/01/11 19:06:08 $
*/
	require("../../config.php");
	cargar_calendario();

	//////////////////////////////////////////////////////////////////////////////
	if(Fechaok($_POST['keyword']))
    {
 	 $_POST['keyword']=Fecha_db($_POST['keyword']);
    }
    variables_form_busqueda("lpba", array());
	$orden = array(
		"default_up"=>"1",
		"default" => "7",
		"1"=>"orden_de_produccion.nro_orden",
		"2"=>"orden_de_produccion.cantidad",
		"3"=>"usuarios.apellido||', '||usuarios.nombre",
		"4"=>"lpb.prioridad DESC, orden_de_produccion.fecha_entrega",
		"5"=>"entidad.nombre",
		"6"=>"licitacion.id_licitacion",
		"7"=>"lpb.prioridad DESC, orden_de_produccion.fecha_entrega"
	);
	$filtro = array(
		"orden_de_produccion.nro_orden" => "Id. OP",
		"orden_de_produccion.cantidad" => "Cantidad pcs",
		"usuarios.apellido||', '||usuarios.nombre" => "Líder",
		"orden_de_produccion.fecha_entrega" => "Fecha de entrega",
		"entidad.nombre" => "Cliente",
		"licitacion.id_licitacion" => "ID"
	);

	$sql_tmp="select distinct(orden_de_produccion.nro_orden), orden_de_produccion.id_licitacion, orden_de_produccion.cantidad, entidad.nombre,
		usuarios.apellido||', '||usuarios.nombre as lider, orden_de_produccion.fecha_entrega, tmp1.*,
		lpb.estado_linea_produccion, lpb.usuario, lpb.fecha, lpb.comentario, lpb.prioridad, licitaciones.licitacion.id_licitacion,estado,
		(tmp1.comprados - tmp1.recibido_entregado) as diferencia
	from ordenes.orden_de_produccion
   	left join licitaciones.entidad using (id_entidad)
		left join licitaciones.licitacion using (id_licitacion)
		left join sistema.usuarios on (lider=id_usuario)
		left join ordenes.ensamblador using (id_ensamblador)
		left join licitaciones.linea_produccion_bsas lpb on(orden_de_produccion.nro_orden=lpb.nro_orden)
		 join(
			select id_licitacion, sum(fila.cantidad) as comprados,
				case when sum(recibido_entregado.cantidad) is null then 0
					else sum(recibido_entregado.cantidad)
				end as recibido_entregado
			from compras.orden_de_compra
				join compras.fila using (nro_orden)
				left join compras.recibido_entregado using (id_fila)
				left join general.proveedor using (id_proveedor)
			where id_licitacion is not null
				and estado <> 'n' and razon_social not ilike '%Stock%' and (ent_rec=1 or ent_rec is null) and (es_agregado is null or es_agregado<>1)
			group by id_licitacion
		)as tmp1 using (id_licitacion) ";
	$where_tmp="(estado_bsas is null)and(not orden_de_produccion.estado ilike 'an')";
	if($_POST['keyword'] || $keyword) $contar="buscar";
	//////////////////////////////////////////////////////////////////////////////
	if (($_POST["cambiar"])&&($_POST["cambiar_nro"])&&($_POST["estado_linea_produccion"])){
		$cambiar_nro=$_POST["cambiar_nro"];
		$estado_linea_produccion=$_POST["estado_linea_produccion"];
		$rta_consulta=sql("update licitaciones.linea_produccion_bsas set estado_linea_produccion='#AAFFAA' where nro_orden=
			values ($cambiar_nro, '".$_ses_user["login"]."', '$estado_linea_produccion')", "c47") or fin_pagina();
	}
	if(($_POST["mas_prioridad"])&&($_POST["cambiar_pop"])){
		$prioridad=substr($_POST["cambiar_pop"], strpos($_POST["cambiar_pop"], ",")+1);
		$op=substr($_POST["cambiar_pop"], 0, strpos($_POST["cambiar_pop"], ","));
		if ($prioridad<9)	sql("update licitaciones.linea_produccion_bsas set prioridad=".($prioridad+1)." where nro_orden= $op", "c70") or fin_pagina();
	}
	if(($_POST["menos_prioridad"])&&($_POST["cambiar_pop"])){
		$prioridad=substr($_POST["cambiar_pop"], strpos($_POST["cambiar_pop"], ",")+1);
		$op=substr($_POST["cambiar_pop"], 0, strpos($_POST["cambiar_pop"], ","));
		if ($prioridad>0)	sql("update licitaciones.linea_produccion_bsas set prioridad=".($prioridad-1)." where nro_orden= $op", "c70") or fin_pagina();
	}
	//////////////////////////////////////////////////////////////////////////////
	echo($html_header);
?>
	<form name="form_lpba" method="POST" action="linea_produccion_bs_as.php">
	<input type="hidden" name="cambio_nro" value="">
	<input type="hidden" name="estado_linea_produccion" value="">
	<input type="hidden" name="comentario" value="<?=$_POST["comentario"]?>">
	<input type="hidden" name="cambiar_pop" value="<?=$_POST["cambiar_pop"]?>">
	<table cellspacing=2 cellpadding=2 border=0 bgcolor=<?=$bgcolor3?> width=95% align=center>
		<tr>
			<td align=center>
				<? list($sql, $total_leg, $link_pagina, $up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar"); ?>
				<input type=submit name=buscar value='Buscar'>
			</td>
		</tr>
	</table>
	<table align="center" width="90%" cellpadding="1" cellspacing="0" border="1" bgcolor="<?=$bgcolor3?>">
		<tr>
			<td colspan="8">
				<table width=100%>
					<tr id=ma>
						<td width=30% align=left><b>Total:</b> <?=$total_leg?> registros.</td>
						<td width=70% align=right><?=$link_pagina?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id=mo>
		    <td width="1%">&nbsp;</td>
		    <td width="1%">Prioridad</td>
			<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>ID</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Id. Orden</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Cant. PC</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Líder</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>F. entrega</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Cliente</a></b></td>
		</tr>
		<?
			$result=sql($sql) or fin_pagina();
			$i=0;
			while ($fila=$result->fetchRow()){

				if ($fila["diferencia"]=="0")  $color_celda="#00FF00";
				else $color_celda=$fila["estado_linea_produccion"] or $color_celda="#FF0000";

				$rta_consulta2=sql("select * from licitaciones.linea_produccion_bsas where nro_orden=".$fila["nro_orden"], "c105") or fin_pagina();

				if ($rta_consulta2->recordCount()>0) sql("update licitaciones.linea_produccion_bsas set estado_linea_produccion='".$color_celda."' where id_linea_produccion_bsas=".$fila["nro_orden"], "c101") or fin_pagina();
				else sql("insert into licitaciones.linea_produccion_bsas (nro_orden, usuario, estado_linea_produccion)values (".$fila["nro_orden"].", '', '".$color_celda."')", "c102") or fin_pagina();

		    $id=$fila['id_licitacion'];
		    $id_nro=$fila["nro_orden"];
		    $estado=$fila["estado"];
		    if($estado=="E")
		    	$cmd1="en";
		    if(($estado=="P")||($estado=="R"))
		    	$cmd1="apa";
		    if($estado=="PA")
		    	$cmd1="ap";
		    if($estado=="A")
		    	$cmd1="aa";
			$ref1=encode_link("ordenes_nueva.php",Array("modo"=>"modificar","nro_orden"=>"$id_nro"));
			$ref=encode_link("ver_seguimiento_ordenes.php",Array("cmd"=>"actuales","filtro"=>"licitacion.id_licitacion","keyword"=>"$id"));
			$fecha_pd=Fecha($fila["fecha"]);

			//date("d/m/Y h:m", $fila["fecha"])
			//echo"$fecha_pd <br>";

			?>
		    <tr <?=atrib_tr()?> title="<?if($color_celda=="#AAFFAA") echo $fila["usuario"]." (".$fecha_pd.")\n".$fila["comentario"]?>">

			<td nowrap align="center">
			<?
			if (($color_celda=="#FF0000")&&(permisos_check("inicio", "permiso_cambiar_estado_linea_produccion"))){?>
				<input type="button" name="cambiar" value="C" onclick="window.open('<?= encode_link('editar_orden.php',array("usuario"=>$_ses_user["login"], "nro_orden"=>$fila["nro_orden"]))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1, height=300, width=600');">
			<?}else echo("&nbsp;");?>
			</td>
			<td nowrap align="center">
			<?if (permisos_check("inicio", "permiso_cambiar_prioridad")){?>
				&nbsp;
					<input type="submit" name="mas_prioridad" value="+" onclick='document.all.cambiar_pop.value="<?=$fila["nro_orden"].", ".$fila["prioridad"]?>"'>
				<?}?>
					<b><?=$fila["prioridad"]?></b>
				<?if (permisos_check("inicio", "permiso_cambiar_prioridad")){?>
					<input type="submit" name="menos_prioridad" value="-" onclick='document.all.cambiar_pop.value="<?=$fila["nro_orden"].", ".$fila["prioridad"]?>"'>
				<?}?>
			</td>
			<a href="<?=$ref?>" target="_blank">
			<td><?=$fila["id_licitacion"]?>&nbsp;</td></a>
			<a href="<?=$ref1?>" target="_blank">
			<td <?=(($color_celda)?"bgcolor='".$color_celda."'":"")?>><?=$fila["nro_orden"]?>&nbsp;</td></a>
			<a href="<?=$ref?>" target="_blank">
			<td><?=$fila["cantidad"]?>&nbsp;</td>
			<td><?=$fila["lider"]?>&nbsp;</td>
			<td><?=fecha($fila["fecha_entrega"])?>&nbsp;</td>
			<td><?=$fila["nombre"]?>&nbsp;</td>
			</a>
		</tr>
		<?
			$i++;
			}
?>
	</table>
	<br>
	<table border="1" bordercolor='black' cellpadding="2" cellspacing="2" align="center" bgcolor="White">
		<tr bordercolor='white'>
			<td bgcolor="#00FF00" width="15" height="15" bordercolor='black'><font color="#00FF00">&nbsp;</font></td><td bordercolor='white'> Lista para producir</td>
		</tr>
		<tr bordercolor='white'>
			<td bgcolor="#AAFFAA" width="15" height="15" bordercolor='black'><font color="#AAFFAA">&nbsp;</font></td><td bordercolor='white'> Lista para producir (a pesar de los faltantes)</td>
		</tr>
		<tr bordercolor='white'>
			<td bgcolor="#FF0000" width="15" height="15" bordercolor='black'><font color="#FF0000">&nbsp;</font></td><td bordercolor='white'> No está lista para producir</td>
		</tr>
	</table>
</form>
<?
fin_pagina();
?>
?>