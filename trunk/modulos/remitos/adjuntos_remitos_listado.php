<?php
/*
AUTOR: Gabriel
MODIFICADO POR:
$Author: gabriel $
$Revision: 1.2 $
$Date: 2005/11/15 14:28:47 $
*/
	require("../../config.php");
	cargar_calendario();
	variables_form_busqueda("adjuntos_remitos", array());
	//////////////////////////////////////////////////////////////////////////////
	if ($_POST["sel_periodo"]){
		$fecha_desde=$_POST["fecha_desde"];
		$fecha_hasta=$_POST["fecha_hasta"];
		if (($fecha_desde)&&($fecha_hasta)){
			$where_tmp=" ((fecha_remito>='".Fecha_db($fecha_desde)."')and(fecha_remito<='".Fecha_db($fecha_hasta)."'))";
		}elseif ($fecha_desde){
			$where_tmp=" (fecha_remito>='".Fecha_db($fecha_desde)."')";
		}elseif ($fecha_hasta){
			$where_tmp=" (fecha_remito<='".Fecha_db($fecha_hasta)."')";
		}
	}
	
	$orden = array(
		"default_up"=>"0",
		"default" => "1",
		"1" => "nro_remito",
		"2" => "cliente",
		"3" => "nombre_transporte",
		"4" => "id_licitacion"
	);
	$filtro = array(        
  	"nro_remito" => "Nro. de remito",
  	"cliente" => "Cliente",
  	"nombre_transporte" => "Transporte",
  	"nros_serie" => "Nros. de serie",
  	"nro_despacho" => "Nro. de despacho",
  	"id_licitacion" => "Id. Licitación",
  	"id_envio_renglones" => "Nro. de envío"
	);     
	$sql_tmp="select distinct id_licitacion, id_envio_renglones, id_remito, nro_remito, nombre_transporte, nros_serie, cliente, fecha_remito
		from (
			select distinct id_remito, id_envio_renglones 
			from licitaciones_datos_adicionales.renglones_bultos 
			where not id_remito is null
		)as s1
		left join(
			select id_renglones_bultos, cantidad_enviada, titulo_mod, nro_despacho, nro_remito, id_renglones_oc, 
				id_remito, cliente, fecha_remito, id_licitacion
			from licitaciones_datos_adicionales.renglones_bultos
				left join facturacion.remitos using (id_remito)
		)as s2 using (id_remito)
		left join(
			select cantidad_total, nombre_transporte, direccion_transporte, telefono_transporte, id_envio_renglones
			from licitaciones_datos_adicionales.envio_renglones
				left join licitaciones_datos_adicionales.transporte using (id_transporte)
		)as s3 using(id_envio_renglones) 
		left join(
			select licitaciones.unir_texto(nro_serie||', ') as nros_serie, id_envio_renglones
			from licitaciones_datos_adicionales.nro_serie_renglon
				left join licitaciones_datos_adicionales.renglones_bultos using (id_renglones_bultos)
			where (nro_serie <> '' and not nro_serie is null)
			group by id_envio_renglones
		)as s4 using(id_envio_renglones)";
	
	if($_POST['keyword'] || $keyword) $contar="buscar";
	//////////////////////////////////////////////////////////////////////////////
	echo($html_header);
?>
	<form name="form_adjuntos" method="POST" action="adjuntos_remitos_listado.php">
	<table cellspacing=2 cellpadding=2 border=0 bgcolor=<? echo $bgcolor3 ?> width=95% align=center>
		<tr>
			<td align=center>
				<? list($sql, $total_leg, $link_pagina, $up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar"); ?>
				<input type="checkbox" name="sel_periodo" value="avanzado" <?=(($_POST["sel_periodo"])?" checked ":"")?> onclick="document.all.tabla_busqueda_avanzada.style.display=(this.checked)?'inline':'none';">&nbsp;Avanzado
				<input type=submit name=buscar value='Buscar'>
			</td>
		</tr>
		<tr>
			<td>
				<table border=0 bgcolor="<?=$bgcolor3?>" width=70% align="center" id="tabla_busqueda_avanzada" style="display:none">
					<tr>
						<td>
							<b>Entre el día:&nbsp;</b>
							<input type=text name='fecha_desde' readonly size=11 value='<?=$fecha_desde?>'>&nbsp;
							<?=link_calendario('fecha_desde')?>
						</td>
						<td>
							<b>y el día:&nbsp;</b>
							<input type=text name='fecha_hasta' readonly size=11 value='<?=$fecha_hasta?>'>&nbsp;
							<?=link_calendario('fecha_hasta')?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<script>
		if (document.all.sel_periodo.checked) document.all.tabla_busqueda_avanzada.style.display='inline';
	</script>
	<table align="center" width="90%" cellpadding="1" cellspacing="0" border="1">
		<tr>
			<td colspan="6">
				<table width=100%>
					<tr id=ma>
						<td width=30% align=left><b>Total:</b> <?=$total_leg?> ítems listados.</td>
						<td width=70% align=right><?=$link_pagina?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id=mo>
 			<td width="10%" nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Nro. remito</a></b></td>
 			<td width="10%" nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Id. Lic.</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Cliente</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Transporte</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Fecha</a></b></td>
 			<td nowrap width="5%"><b>Adjunto</b></td>
		</tr>
		<?
			$result=sql($sql, "c116") or fin_pagina();
			while ($fila=$result->fetchRow()){
		?>
		<tr <?=atrib_tr()?>>
			<td><?=$fila["nro_remito"]?>&nbsp;</td>
			<td><?=$fila["id_licitacion"]?>&nbsp;</td>
			<td align="right"><?=$fila["cliente"]?>&nbsp;</td>
			<td align="right"><?=$fila["nombre_transporte"]?>&nbsp;</td>
			<td><?=Fecha($fila["fecha_remito"])?>&nbsp;</td>
			<td align="center">
				<input type="button" name="adjunto_remito" value="Adj." title="Imprimir Adjuntos para los Remitos Asociados a este Envío"
   				onclick="window.open('<?=encode_link("../ordprod/adjunto_remito_envio.php", array("id_envio_renglones"=>$fila["id_envio_renglones"]))?>','','toolbar=1,location=0,directories=1,status=1, menubar=1,scrollbars=1,left=125,top=10,width=800,height=600')">
			</td>
		</tr>
		<?}?>
	</table>
</form>
<?
fin_pagina();
?>
