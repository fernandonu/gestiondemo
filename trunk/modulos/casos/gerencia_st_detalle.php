<?php
/*
AUTOR: Gabriel
MODIFICADO POR:
$Author: gabriel $
$Revision: 1.2 $
$Date: 2005/12/01 21:46:12 $
*/
require("../../config.php");

$id_gerencia_st=$parametros["id_gerencia_st"] or $id_gerencia_st=$_POST["id_gerencia_st"];
$id_licitacion=$parametros["id_licitacion"] or $id_licitacion=$_POST["id_licitacion"];
$nombre=$parametros["nombre"] or $nombre=$_POST["nombre"];
$nro_orden=$parametros["nro_orden"] or $nro_orden=$_POST["nro_orden"];
$nro_serie=$parametros["nro_serie"] or $nro_serie=$_POST["nro_serie"];
$comentario_tecnico=$parametros["comentario_tecnico"] or $comentario_tecnico=$_POST["comentario_tecnico"];
$comentario_gerente=$parametros["comentario_gerente"] or $comentario_gerente=$_POST["comentario_gerente"];
$comentario_directivo=$parametros["comentario_directivo"] or $comentario_directivo=$_POST["comentario_directivo"];
$fallos=$parametros["fallos"] or $fallos=$_POST["fallos"];
$estado_gst=$parametros["estado_gst"] or $estado_gst=$_POST["estado_gst"];
$tipo_gst=$parametros["tipo_gst"] or $tipo_gst=$_POST["tipo_gst"];
$accion_tomada=$parametros["accion_tomada"] or $accion_tomada=$_POST["accion_tomada"];

if ($_POST["guardart"]){
	$consulta="update casos.gerencia_st set comentario_tecnico='".$comentario_tecnico."' where id_gerencia_st=".$id_gerencia_st;
	$rta_consulta=sql($consulta, "c26") or fin_pagina();
}elseif ($_POST["guardarg"]){
	$consulta="update casos.gerencia_st set comentario_gerente='".$comentario_gerente."' where id_gerencia_st=".$id_gerencia_st;
	$rta_consulta=sql($consulta, "c29") or fin_pagina();
}elseif ($_POST["guardard"]){
	$consulta="update casos.gerencia_st set comentario_directivo='".$comentario_directivo."' where id_gerencia_st=".$id_gerencia_st;
	$rta_consulta=sql($consulta, "c32") or fin_pagina();
}elseif ($_POST["guardara"]){
	$consulta="update casos.gerencia_st set accion_tomada='".$accion_tomada."' where id_gerencia_st=".$id_gerencia_st;
	$rta_consulta=sql($consulta, "c35") or fin_pagina();
}elseif ($_POST["hist"]){
	$consulta="update casos.gerencia_st set estado_gst='h' where id_gerencia_st=".$id_gerencia_st;
	$rta_consulta=sql($consulta, "c38") or fin_pagina();
}

$consulta="select o.cantidad, l.nro_lic_codificado, l.exp_lic_codificado, d.nombre as distrito, 
		(CURRENT_DATE - o.fecha_entrega)/30 as meses
	from ordenes.orden_de_produccion o
		join licitaciones.licitacion l using (id_licitacion)
		join licitaciones.entidad e on(l.id_entidad=e.id_entidad)
		join licitaciones.distrito d using (id_distrito)
		join casos.gerencia_st gst ";
	if ($tipo_gst=='p') $consulta.="using(nro_orden) 
		where gst.nro_orden=".$nro_orden;
	else $consulta.="on ((gst.nro_serie >= o.nserie_desde)and(gst.nro_serie <= o.nserie_hasta)) 
		where (('".$nro_serie."' >= o.nserie_desde)and('".$nro_serie."' <= o.nserie_hasta))and (gst.nro_serie='".$nro_serie."')";
$rta_consulta=sql($consulta, "c49") or fin_pagina();

$cantidad=$rta_consulta->fields["cantidad"] or $cantidad=$_POST["cantidad"];
$distrito=$rta_consulta->fields["distrito"] or $distrito=$_POST["distrito"];
$nro_lic_codificado=$rta_consulta->fields["nro_lic_codificado"] or $nro_lic_codificado=$_POST["nro_lic_codificado"];
$meses=$rta_consulta->fields["meses"] or $meses=$_POST["meses"];
$exp_lic_codificado=$rta_consulta->fields["exp_lic_codificado"] or $exp_lic_codificado=$_POST["exp_lic_codificado"];

$consulta="select casos_cdr.nrocaso, cas_ate.nombre, fila.nro_orden, fila.precio_unitario, casos_cdr.idcaso, cas_ate.idate
	from casos.casos_cdr 
		left join compras.fila on (fila.id_fila=casos_cdr.fila)
		join casos.cas_ate using (idate),
		ordenes.orden_de_produccion op join casos.gerencia_st ";

	if ($tipo_gst=='c') $consulta.="on((nro_serie >= nserie_desde)and(nro_serie <= nserie_hasta))";
	else $consulta.="using(nro_orden)";

	$consulta.=" where (casos_cdr.nserie >= op.nserie_desde)and(casos_cdr.nserie <= op.nserie_hasta)	and ";
if ($tipo_gst=='p') $consulta.="(op.nro_orden=".$nro_orden.")";
else $consulta.="((nro_serie='".$nro_serie."')and(casos_cdr.nserie=gerencia_st.nro_serie))";
$consulta.=" order by nombre, nro_orden";
$result=sql($consulta, "c66") or fin_pagina();

echo($html_header);
?>
<script>
var img_ext='<?=$img_ext='../../imagenes/drop2.gif' ?>';//imagen extendido
var img_cont='<?=$img_cont='../../imagenes/dropdown2.gif' ?>';//imagen contraido
function muestra_tabla(obj_tabla, nro){
	oimg=eval("document.all.imagen_"+nro);//objeto tipo IMG
 	if (obj_tabla.style.display=='none'){
 		obj_tabla.style.display='inline';
    oimg.show=0;
    oimg.src=img_cont;
    if (nro==1) oimg.title='Ocultar tabla';
		else oimg.title='Imagen no identificada';
	}else{
		obj_tabla.style.display='none';
    oimg.show=1;
		oimg.src=img_ext;
		if (nro==1) oimg.title='Mostrar tabla';
		else oimg.title='Imagen no identificada';
  } 
}
</script>
<form name="form1" method="POST" action="gerencia_st_detalle.php">
	<input type="hidden" name="id_gerencia_st" value="<?=$id_gerencia_st?>">
	<input type="hidden" name="nro_orden" value="<?=$nro_orden?>">
	<input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
	<input type="hidden" name="fallos" value="<?=$fallos?>">
	<input type="hidden" name="tipo_gst" value="<?=$tipo_gst?>">
	<input type="hidden" name="nro_serie" value="<?=$nro_serie?>">
	<table align="center" width="95%" bgcolor="<?=$bgcolor3?>">
		<tr>
			<td id="mo" colspan="2" rowspan="<?=(($tipo_gst=="p")?4:3)?>">Orden de producción: <?=$nro_orden?></td>
			<td id="mo" width="20%">Cantidad de computadoras:</td>
			<td><input type="text" name="cantidad_pcs" class="text_4" value="<?=$cantidad?>"></td>
		</tr>
		<tr>
			<td id="mo">Casos:</td>
			<td colspan="2"><b><?=$result->recordCount()?></b></td>
		</tr>
		<?if ($tipo_gst=="p"){?>
		<tr>
			<td id="mo">Índice de casos: </td>
			<td colspan="2"><b><?=number_format($fallos, 2, ",", "")?>%</b></td>
		</tr>
		<?}?>
		<tr>
			<td id="mo">Período: </td>
			<td colspan="2"><input type="text" class="text_4" name="meses" style="width:'100%'" value="<?=$meses." meses"?>"></td>
		</tr>
		<tr><td colspan="4"><hr></td></tr>
		<tr>
			<td id="mo" rowspan="2" colspan="2">
				Licitación: <?=$id_licitacion?><br>
				<?=$nro_lic_codificado?><br>
				Exp. <?=$exp_lic_codificado?>
			</td>
			<td id="mo">Cliente: </td><td><input type="text" class="text_4" name="nombre" style="width:'100%'" value="<?=$nombre?>"></td>
		</tr>
		<tr>
			<td id="mo">Distrito:</td>
			<td><input type="text" class="text_4" name="distrito" style="width:'100%'" value="<?=$distrito?>"></td>
		</tr>
	</table>
	<br>
	<table align="center" width="95%" bgcolor="<?=$bgcolor3?>" border="1">
		<tr>
			<td id="mo" style="cursor:hand;" onclick="muestra_tabla(document.getElementById('tabla_casos'), 1)">
				<img id="imagen_1" src="../../imagenes/drop2.gif" border=0 title="Mostrar casos" align="left" style="cursor:hand;">
				<font size=+1>Casos</font>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%" align="center"  id="tabla_casos" style="display:none">
					<tr>
						<td>
							<!--///////////////////////////////////////////////////////////////////////////////////////////////////////-->
							<table width="100%"  align="center" class="bordes">
								<tr class="bordes">
									<td id=mo colspan="4" class="bordes">
										<font size="2"><strong> Listado de Casos </strong></font>
									</td>
								</tr>
								<tr class="bordes">
   								<td id=mo width="25%" class="bordes">Nro. de Caso</td> 
									<td id=mo width="45%" class="bordes">Proveedor C.A.S.</td>
   								<td id=mo width="15%" class="bordes">Nro. de Orden de Compra</td>
   								<td id=mo width="15%" class="bordes">Monto de la Fila de la Orden de Compra</td>
   							</tr> 
<?
	$result->MoveFirst();
	while (!$result->EOF){
		$link=encode_link("caso_estados.php",Array("id"=>$result->fields["idcaso"],"id_entidad"=>$result->fields['idate']));
?>
								<tr <?=atrib_tr()?> onclick="window.open('<?=$link?>')">
  								<td class="bordes">
										<b><font size="2"> <strong><?=$result->fields["nrocaso"]?></strong></font></b>
  								</td>
 							  	<td class="bordes">
 										<b><?echo $result->fields["nombre"];?></b>
  								</td>
  								<td align="center" class="bordes">
 										<b><?echo number_format($result->fields["nro_orden"],0,'.','');?></b>
  								</td>
  								<td align="center" class="bordes">
 										<b><?echo number_format($result->fields["precio_unitario"],2,'.','');?></b>
  								</td>
 								</tr>
<?
	$result->MoveNext();
	}
?>
							</table>
							<!--///////////////////////////////////////////////////////////////////////////////////////////////////////-->
						</td>
					</tr>
					<tr><td><hr></td></tr>
					<tr>
						<td>
							Reporte del Técnico:<br>
							<?if (permisos_check("inicio", "guardar_tecnico_gerencia_st")){?>
							<input type="submit" name="guardart" value="Guardar reporte">
							<?}?>
							<br>
							<textarea name="comentario_tecnico" rows="10" cols="150"><?=$comentario_tecnico?></textarea>
						</td>
					</tr>
					<tr><td><hr></td></tr>
					<tr>
						<td>
							Reporte del Gerente:<br>
							<?if (permisos_check("inicio", "guardar_gerente_gerencia_st")){?>
							<input type="submit" name="guardarg" value="Guardar reporte">
							<?}?>
							<br>
							<textarea name="comentario_gerente" rows="10" cols="150"><?=$comentario_gerente?></textarea>
						</td>
					</tr>
					<tr><td><hr></td></tr>
					<tr>
						<td>
							Reporte de la Dirección:<br>
							<?if (permisos_check("inicio", "guardar_directivo_gerencia_st")){?>
							<input type="submit" name="guardard" value="Guardar reporte">
							<?}?>
							<br>
							<textarea name="comentario_directivo" rows="10" cols="150"><?=$comentario_directivo?></textarea>
						</td>
					</tr>
					<tr><td><hr></td></tr>
					<tr>
						<td>
							Acción tomada:<br>
							<input type="submit" name="guardara" value="Guardar reporte">
							<br>
							<textarea name="accion_tomada" rows="10" cols="150"><?=$accion_tomada?></textarea>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<table width="95%" bgcolor="<?=$bgcolor3?>" align="center">
		<tr>
			<?if ($estado_gst!='h'){?>
			<td align="center"><input type="submit" name="hist" value="Pasar a Historial"></td>
			<?}?>
		</tr>
	</table>
</form>
<?

fin_pagina();
	