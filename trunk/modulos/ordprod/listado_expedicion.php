<?php
/*
Autor: Gabriel
$Author: gabriel $
$Revision: 1.2 $
$Date: 2005/12/23 17:41:34 $
*/

require("../../config.php");
$id_subir=$parametros["id_subir"] or $id_subir=$_POST["id_subir"];

$sql = "
SELECT 
  licitaciones.entidad.nombre, licitaciones.licitacion.id_licitacion, licitaciones.subido_lic_oc.nro_orden, 
  codigo_renglon, renglones_oc.id_renglon, renglon.titulo, usuarios.nombre||' '||usuarios.apellido as lider
FROM
  licitaciones.subido_lic_oc
  left join licitaciones.renglones_oc ON (licitaciones.renglones_oc.id_subir = licitaciones.subido_lic_oc.id_subir)
  LEFT JOIN licitaciones.renglon ON (licitaciones.renglones_oc.id_renglon = licitaciones.renglon.id_renglon)
  LEFT JOIN licitaciones.licitacion ON (licitaciones.renglon.id_licitacion = licitaciones.licitacion.id_licitacion)
  LEFT JOIN licitaciones.entidad ON (licitaciones.licitacion.id_entidad = licitaciones.entidad.id_entidad)
	left join sistema.usuarios on (lider=id_usuario)
WHERE
  licitaciones.renglones_oc.id_subir = $id_subir";
$rta_gral=sql($sql, "c26") or fin_pagina();

echo $html_header;
?>
<form id="form1" method="POST" action="listado_expedicion.php">
<input type="hidden" name="id_subir" value="<?=$id_subir?>">
<table width="90%" cellpadding="2" cellspacing="1" border="1" bordercolor="black" bgcolor="<?=$bgcolor3?>" align="center">
	<tr>
		<td colspan="3" id="mo">Listado de expedición de orden <?=$rta_gral->fields["nro_orden"]?></td>
	</tr>
	<tr>
		<td colspan="2" nowrap>
			<table width="80%">
				<tr>
					<td id="mo">ID licitación: </td><td><b><?=$rta_gral->fields["id_licitacion"]?><b></td>
					<td rowspan="2" id="mo">Líder:</td><td rowspan="2"><?=$rta_gral->fields["lider"]?></td>
				</tr>
				<tr>
					<td id="mo">Entidad: </td><td><?=$rta_gral->fields["nombre"]?></td>
				</tr>
			</table>
		</td>
	</tr>
<?
	while (!$rta_gral->EOF){
?>
	<tr>
		<td nowrap id="mo_sf" align="left">Renglón: <?=$rta_gral->fields["codigo_renglon"]?></td>
		<td align="center"><b><?=$rta_gral->fields["titulo"]?></b></td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr id="mo">
					<td width="20%">Cantidad</td><td>Producto</td>
				</tr>
<?
$sql = "
SELECT 
  general.productos.desc_gral, licitaciones.producto.precio_licitacion,
   SUM(licitaciones.producto.cantidad * licitaciones.renglones_oc.cantidad) AS cantidad
FROM
  licitaciones.producto
  LEFT JOIN licitaciones.renglones_oc ON (licitaciones.producto.id_renglon = licitaciones.renglones_oc.id_renglon)
  LEFT JOIN general.productos ON (licitaciones.producto.id_producto = general.productos.id_producto)
WHERE
  licitaciones.renglones_oc.id_subir = $id_subir and renglones_oc.id_renglon=".$rta_gral->fields["id_renglon"]."
GROUP BY general.productos.desc_gral, licitaciones.producto.precio_licitacion";
$rta_renglon = sql($sql, "c44") or fin_pagina();

				while (!$rta_renglon->EOF){
?>
					<tr>
						<td align="center"><?=$rta_renglon->fields["cantidad"]?></td>
						<td><?=$rta_renglon->fields["desc_gral"]?></td>
					</tr>
<?
					$rta_renglon->moveNext();
				}
?>
			</table>
		</td>
	</tr>
<?
		$rta_gral->moveNext();
	}
?>
</table>
</form>
<?
fin_pagina();
?>