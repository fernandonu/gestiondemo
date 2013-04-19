<?php
/*
AUTOR: Gabriel
MODIFICADO POR:
$Author: gabriel $
$Revision: 1.1 $
$Date: 2005/12/01 16:07:42 $
*/
	require("../../config.php");
	variables_form_busqueda("gerencia_st", array());
	if ($cmd == ""){
		$cmd="pPendientes";
		$_ses_gerencia_st["cmd"]=$cmd;
  	phpss_svars_set("_ses_gerencia_st", $_ses_gerencia_st);
	}
	//////////////////////////////////////////////////////////////////////////////
	$datos_barra = array(
		array("descripcion"=> "Pendientes", "cmd"=> "pPendientes"),
		array("descripcion"=> "Historial", "cmd"=> "pHistorial")
	);
	
	$datos_barra2 = array(
		array("descripcion"=> "Pendientes", "cmd"=> "cPendientes"),
		array("descripcion"=> "Historial", "cmd"=> "cHistorial")
	);
	
	$orden = array(
		"default_up"=>"0",
		"default" => "1",
		"1" => "orden_de_produccion.nro_orden",
		"2" => "id_licitacion",
		"3" => "nombre",
		"4" => "fallos",
		"5" => "nro_serie"
		
	);
	$filtro = array(        
  	"orden_de_produccion.nro_orden" => "Orden de Prod.",
  	"id_licitacion" => "Id. Lic.",
  	"nombre" => "Cliente",
  	"comentario_tecnico" => "Comentario del Técnico",
  	"comentario_gerente" => "Comentario del Gerente",
  	"comentario_directivo" => "Comentario del Directivo"
	);

	$sql_tmp="select id_gerencia_st, orden_de_produccion.nro_orden, estado_gst, tipo_gst, fallos, nro_serie, accion_tomada,
			comentario_tecnico, comentario_gerente, comentario_directivo, orden_de_produccion.id_licitacion, entidad.nombre
		from casos.gerencia_st
			join ordenes.orden_de_produccion ";
	
	if (($cmd=="cPendientes")||($cmd=="cHistorial")) $sql_tmp.="on((nro_serie >= nserie_desde)and(nro_serie <= nserie_hasta))";
	else $sql_tmp.="using(nro_orden)";
	
	$sql_tmp.="join licitaciones.entidad using(id_entidad) ";
	
	if ($cmd=="cPendientes") $where_tmp="(estado_gst='p')and(tipo_gst='c')";
	elseif ($cmd=="pPendientes") $where_tmp="(estado_gst='p')and(tipo_gst='p')";
	elseif ($cmd=="cHistorial") $where_tmp="(estado_gst='h')and(tipo_gst='c')";
	else $where_tmp="(estado_gst='h')and(tipo_gst='p')";
	
	if($_POST['keyword'] || $keyword) $contar="buscar";
	//////////////////////////////////////////////////////////////////////////////
	echo($html_header);
?>
	<form name="form_gerencia_st" method="POST" action="gerencia_st.php">
	<table cellspacing=2 cellpadding=2 border=0 bgcolor=<? echo $bgcolor3 ?> width=95% align=center>
		<tr>
			<td align="center">
				<b>Producción</b>
				<? generar_barra_nav($datos_barra);?>  
			</td>
			<td align="center">
				<b>Computadoras</b>
				<? generar_barra_nav($datos_barra2);?>
			</td>
		</tr>
		<tr>
			<td align=center colspan="2">
				<? list($sql, $total_leg, $link_pagina, $up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar"); ?>
				<input type=submit name=buscar value='Buscar'>
			</td>
		</tr>
	</table>
	<table align="center" width="90%" cellpadding="1" cellspacing="0" border="1">
		<tr>
			<td colspan="7">
				<table width=100%>
					<tr id=ma>
						<td width=30% align=left><b>Total:</b> <?=$total_leg?> registros.</td>
						<td width=70% align=right><?=$link_pagina?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id=mo>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Cliente</a></b></td>
 			<td nowrap width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Lic.</a></b></td>
 			<td nowrap width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Orden de P.</a></b></td>
 			<?if(($cmd=="cPendientes")||($cmd=="cHistorial")){?>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Nro. de Serie</a></b></td>
 			<?}?>
 			<td nowrap width="5%"><b>Téc.</b></td>
 			<td nowrap width="5%"><b>Ger.</b></td>
 			<td nowrap width="5%"><b>Dir.</b></td>
		</tr>
		<?
			$result=sql($sql) or fin_pagina();
			while ($fila=$result->fetchRow()){
				$ref = encode_link("gerencia_st_detalle.php",array_merge(array("modo"=>"modif", "pagina"=>"gerencia_st_detalle.php"), $fila));
				tr_tag($ref);
		?>
			<td><?=$fila["nombre"]?>&nbsp;</td>
			<td><?=$fila["id_licitacion"]?>&nbsp;</td>
			<td><?=$fila["nro_orden"]?>&nbsp;</td>
			<?if(($cmd=="cPendientes")||($cmd=="cHistorial")){?>
			<td><?=$fila["nro_serie"]?>&nbsp;</td>
			<?}?>
			<td bgcolor="<?=(($fila["comentario_tecnico"])?"green":"red")?>">&nbsp;</td>
			<td bgcolor="<?=(($fila["comentario_gerente"])?"green":"red")?>">&nbsp;</td>
			<td bgcolor="<?=(($fila["comentario_directivo"])?"green":"red")?>">&nbsp;</td>
		</tr>
		<?
			}
?>
	</table>
	<br>
	<table width="50%" align="center" bgcolor="White" class="bordes">
		<tr>
			<td bgcolor="Green" width="5%" class="bordes">&nbsp;</td><td>Registro con comentario de técnico, gerente y/o directivo</td>
		</tr>
		<tr>
			<td bgcolor="Red" width="5%" class="bordes">&nbsp;</td><td>Registro sin comentario de técnico, gerente y/o directivo</td>
		</tr>
	</table>
</form>
<?
fin_pagina();
?>
?>