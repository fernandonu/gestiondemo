<?php
/*AUTOR: Carlitox

MODIFICADO POR:
$Author: cestila $
$Revision: 1.1 $
$Date: 2005/10/05 19:41:20 $
*/

require_once("../../config.php");
//echo $html_header;

variables_form_busqueda("caja_seguridad");

if ($cmd == "") {
	$cmd="exitentes";
	$_ses_caja_seguridad["cmd"] = $cmd;
	phpss_svars_set("_ses_caja_seguridad", $_ses_caja_seguridad);
}

$datos_barra = array(
					array(
						"descripcion"	=> "Existentes",
						"cmd"			=> "exitentes"
						),
					array(
						"descripcion"	=> "Historial",
						"cmd"			=> "historial"
						)
					);

$orden = array(
	"default" => "1",
	"default_up" => 0,
	"1" => "titulo",
	"2" => "monto"
);

$filtro= Array(
	"titulo" => "Titulo",
	"item_caja_seguridad.observacion" => "Observación",
	"monto" => "Monto"
);

$sql_tmp="select id_item,titulo,moneda.simbolo,monto,item_caja_seguridad.observacion from item_caja_seguridad join moneda using(id_moneda) join caja_seguridad using(id_caja_seguridad)";
$where_tmp="caja_seguridad.id_caja_seguridad='1'";
if ($cmd=="exitentes")
	$where_tmp.=" and item_caja_seguridad.estado='existente'";
else
	$where_tmp.=" and item_caja_seguridad.estado='historial'";

echo $html_header;
generar_barra_nav($datos_barra);

echo "<br><br><center><form name='buscar' action='caja_seguridad' method='Post'>\n";
list($sql,$total,$link_pagina,$up2) = form_busqueda($sql_tmp,$orden,$filtro,$link_temp,$where_tmp,"buscar");
$rs = sql($sql) or fin_pagina();
echo "<input type='submit' name='form_busqueda' value='Buscar'>&nbsp;&nbsp;&nbsp;\n";
echo "<input type='button' name='nuevo' value='Nuevo Items' onClick='document.location=\"".encode_link("item_seguridad.php",array())."\"'>\n";
echo "</form><center><br><br>\n";
echo "<table width=99% border=0 cellspacing=2 cellpadding=0>\n";
echo "<tr id=ma><td>\n";
echo "<b>Total: $total Items.</b>\n";
echo "</td>\n";
echo "<td>$link_pagina</td></tr>\n";
echo "<tr id='mo'>\n";
echo "<td><b><a href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))."'>Titulo</a></b></td>\n";
echo "<td><b><a href='".encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))."'>Monto</a></b></td>\n";
echo "</tr>\n";
while ($fila=$rs->fetchRow()) {
	$ref=encode_link("item_seguridad.php",array("id"=>$fila["id_item"]));
	tr_tag($ref);
	echo "<td>".$fila["titulo"]."</td>";
	echo "<td>".$fila["simbolo"]." ".formato_money($fila["monto"])."</td>";
}
echo "</tr>\n";
echo "</table>\n";
fin_pagina();
?>