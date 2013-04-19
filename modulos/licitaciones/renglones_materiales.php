<?php
/*
$Author: nazabal $
$Revision: 1.1 $
$Date: 2004/10/07 20:18:01 $
*/

require("../../config.php");

echo $html_header;

$id_subir=$parametros["id_subir"];
if ($id_subir == "") {
	Error("Falta el ID");
	fin_pagina();
}

$up = $parametros["up"];
if ($up == "") {
    $up = "1";
}
if ($up == "1") {
	$direccion = "ASC";
	$up = "0";
}
else {
	$direccion = "DESC";
	$up = "1";
}

$orden = array(
	"default" => "1",
	"1" => "general.productos.desc_gral",
	"2" => "licitaciones.producto.precio_licitacion",
	"3" => "cantidad"
);
$ordenar = $orden[$parametros["sort"]] or $ordenar = $orden[$orden["default"]];

$sql = "
SELECT 
  licitaciones.entidad.nombre,
  licitaciones.licitacion.id_licitacion,
  licitaciones.subido_lic_oc.nro_orden
FROM
  licitaciones.renglones_oc
  LEFT JOIN licitaciones.renglon ON (licitaciones.renglones_oc.id_renglon = licitaciones.renglon.id_renglon)
  LEFT JOIN licitaciones.licitacion ON (licitaciones.renglon.id_licitacion = licitaciones.licitacion.id_licitacion)
  LEFT JOIN licitaciones.entidad ON (licitaciones.licitacion.id_entidad = licitaciones.entidad.id_entidad)
  LEFT JOIN licitaciones.subido_lic_oc ON (licitaciones.renglones_oc.id_subir = licitaciones.subido_lic_oc.id_subir)
WHERE
  licitaciones.renglones_oc.id_subir = $id_subir
";
$result = sql($sql) or fin_pagina();
$entidad = $result->fields["nombre"];
$id_lic = $result->fields["id_licitacion"];
$nro_orden = $result->fields["nro_orden"];

$sql = "
SELECT 
  licitaciones.renglon.codigo_renglon
FROM
  licitaciones.renglon
  LEFT OUTER JOIN licitaciones.renglones_oc ON (licitaciones.renglon.id_renglon = licitaciones.renglones_oc.id_renglon)
WHERE
  licitaciones.renglones_oc.id_subir = $id_subir
";
$result = sql($sql) or fin_pagina();
$renglones_array = array();
while(!$result->EOF){
	$renglones_array[] = $result->fields["codigo_renglon"];
	$result->MoveNext();
}
$renglones = join(", ",$renglones_array);
$sql = "
SELECT 
  general.productos.desc_gral,
  licitaciones.producto.precio_licitacion,
  SUM(licitaciones.producto.cantidad * licitaciones.renglones_oc.cantidad) AS cantidad
FROM
  licitaciones.producto
  LEFT JOIN licitaciones.renglones_oc ON (licitaciones.producto.id_renglon = licitaciones.renglones_oc.id_renglon)
  LEFT JOIN general.productos ON (licitaciones.producto.id_producto = general.productos.id_producto)
WHERE
  licitaciones.renglones_oc.id_subir = $id_subir
GROUP BY
  general.productos.desc_gral,
  licitaciones.producto.precio_licitacion
ORDER BY $ordenar $direccion
";
$result = sql($sql) or fin_pagina();
?>
<table align=center cellpadding=5 cellspacing=0 border=1>
  <tr bordercolor='#000000'>
	<td id=mo colspan=3>
		<table width='100%'>
			<tr id=mo><td align=left>
		  ID Licitaci&oacute;n: <?=$id_lic?><br>
		  Entidad: <?=$entidad?><br>
		  OC N°: <?=$nro_orden?><br>
		  Renglones: <?=$renglones?><br>
		  </td></tr>
		</table>
	</td>
  </tr>
  <tr bordercolor='#000000' id=ma>
	<td align=center><a id=ma href='<?=encode_link("renglones_materiales.php",Array('sort'=>1,'up'=>$up,'id_subir'=>$id_subir))?>'>Producto</a></td>
	<td align=center><a id=ma href='<?=encode_link("renglones_materiales.php",Array('sort'=>2,'up'=>$up,'id_subir'=>$id_subir))?>'>Precio</a></td>
	<td align=center><a id=ma href='<?=encode_link("renglones_materiales.php",Array('sort'=>3,'up'=>$up,'id_subir'=>$id_subir))?>'>Cantidad</a></td>
  </tr>
<?
   while ($fila = $result->fetchrow()) {
        echo "<tr bordercolor='#000000'>\n";
        echo "<td align=left>".$fila["desc_gral"]."</td>";
        echo "<td align=right> U\$S ".formato_money($fila['precio_licitacion'])."</td>\n";
        echo "<td align=center>".intval($fila['cantidad'])."</td>\n";
        echo "</tr>\n";
   }
?>
</table>
<br>
<center>
 <input type=button name=cerrar value=Cerrar onclick="window.close()">
</center>
<?
fin_pagina();
?>
