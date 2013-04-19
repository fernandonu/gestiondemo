<?php
/*
$Author: cestila $
$Revision: 1.1 $
$Date: 2005/05/03 19:32:33 $
*/

require_once("../../config.php");
//include("func.php");

variables_form_busqueda("listado_anticipo");//para que funcione el form busqueda

if (!$cmd) $cmd="P";

$datos_barra = array(
	array(
		"descripcion"    => "Pendientes",
		"cmd"            => "P"
	),
	array(
		"descripcion"    => "Historial",
		"cmd"            => "H"
	)
);

$orden= array(
	"default" 		=> "1",
	"default_up" 	=> "0",
	"1" => "fecha_entrega",
	"2" => "apellido",
	"3" => "Monto"
);

$filtro = array(
	"fecha_entrega" => "Fecha de Entrega",
	"apellido" 		=> "Apellido",
	"usuarios.nombre" => "Nombre",
	"monto" 		=> "Monto",
	"comentario" 	=> "Comentario"
);

$sql_tmp="select id_anticipo,fecha_entrega,usuarios.nombre as username,usuarios.apellido,monto,moneda.simbolo from anticipo 
	join usuarios using(id_usuario) 
	join moneda using(id_moneda)";
$where_tmp="id_distrito=2";
if ($cmd=="P") $where_tmp.=" and estado='pendiente'";
if ($cmd=="H") $where_tmp.=" and estado='historial'";

echo $html_header;


generar_barra_nav($datos_barra);
?>
<form action="listado_anticipo.php" method="post">
<br><center>
<?
list($query,$total,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar");
$res=sql($query) or fin_pagina();
?>
<input type=submit name=form_busqueda value='Buscar'>&nbsp;&nbsp;
<input type=button name=nuevo_anticipo value='Nuevo anticipo a rendir' onClick="window.location='<?=encode_link("nuevo_anticipo.php",array());?>';">
</center><br>
<table align="center" width="95%" cellspacing="2" cellpadding="2" class="bordes">
<tr id=ma>
  <td align="left">
   <b>Total: <?=$total?> Anticipos Encontrada/s.</b>
  </td>
  <td align="right" width=70% colspan="2">
   <?=$link_pagina?>
  </td>
 </tr>
 <tr id=mo>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Fecha Entrega.</a></b></td>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Apellido y Nombre.</a></b></td>
	<td><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Monto.</a></b></td>
 </tr>
<?
	while ($fila=$res->fetchrow()) {
		$href=encode_link("nuevo_anticipo.php",array("id_anticipo"=>$fila["id_anticipo"]));
		tr_tag($href,"Modificar anticipo a rendir");
		echo "<td>".fecha($fila["fecha_entrega"])."</td>\n";
		echo "<td>".$fila["apellido"]." ".$fila["username"]."</td>\n";
		echo "<td>".$fila["simbolo"]." ".formato_money($fila["monto"])."</td>\n";
		echo "</tr>\n";
	}
?>
</table>
<?
fin_pagina();
?>