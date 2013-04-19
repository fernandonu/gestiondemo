<? 
/*
Autor: GACZ
Creado: jueves 03/06/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2004/06/03 21:58:34 $
*/
require_once("../../config.php");
//variables_form_busqueda("prod_ofer");


//LOS DATOS VIENEN DE ord_compra_prod_comp.php

ob_start();
$itemspp=100;
list($q,$total,$link,$notup)=form_busqueda($q,$orden_array,$filtro_array,$link_tmp,$where);
ob_clean();

$ordenar=$parametros['ordenar'] or $ordenar="nro_orden";
$direccion=$parametros['direccion'] or $direccion="asc";

if ($direccion=="asc")
	$notdir="desc";

//primera expresion busca el subselect
//la segunda una consulta comun
//echo "COSULTA ORIG: $q<br><br>";
//$q=eregi_replace("^select.*from(.*select.*from.*where.*)(where.*)*(group by.*$)|^select.*from(.*)(where.*)*(group by.*$)","select distinct(nro_orden),cliente,p.desc_gral,sum(f.cantidad) as cantidad,razon_social,fecha_entrega from \\1\\4 \\2\\5 AND p.id_producto=$parametros[id_producto] group by nro_orden,p.id_producto,p.desc_gral,razon_social,fecha_entrega,cliente order by $ordenar $direccion",$q);

$exp ="^select.*from(.*select.*from.*where.*)(where.*)(group by.*$)";
//							1					2			3
$exp.="|";
$exp.="^select.*from(.*select.*from.*where.*)(group by.*$)";
//							4						5
$exp.="|";
$exp.="^select.*from(.*)(where.*)(group by.*$)";
//					  6		7		 8
$exp.="|";
$exp.="^select.*from(.*)(group by.*$)";
//					  9		10					
eregi($exp,$q,$arr);

//si tiene where
if ($arr[2] || $arr[7])
	$q="select distinct(nro_orden),cliente,p.desc_gral,sum(f.cantidad) as cantidad,razon_social,fecha_entrega from ".$arr[1].$arr[4].$arr[6].$arr[9]." ".$arr[2].$arr[7]." AND p.id_producto=$parametros[id_producto] group by nro_orden,p.id_producto,p.desc_gral,razon_social,fecha_entrega,cliente order by $ordenar $direccion";
else 
	$q="select distinct(nro_orden),cliente,p.desc_gral,sum(f.cantidad) as cantidad,razon_social,fecha_entrega from ".$arr[1].$arr[4].$arr[6].$arr[9]." where p.id_producto=$parametros[id_producto] group by nro_orden,p.id_producto,p.desc_gral,razon_social,fecha_entrega,cliente order by $ordenar $direccion";

//print_r($arr);	
	
$ordenes=sql($q) or fin_pagina();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Lista de Ordenes de Compra para Productos</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?=$html_header ?>
<script>
// funciones que iluminan las filas de la tabla
function sobre(src,color_entrada) {
    src.style.backgroundColor=color_entrada;
}
function bajo(src,color_default) {
    src.style.backgroundColor=color_default;
}
</SCRIPT>
<table width="100%" border="1" cellpadding="2" cellspacing="0" bordercolor="#000000">
  <tr> 
    <td width="25%" height="26" align="right" id=mo>Descripcion del Producto&nbsp;</td>
    <td><?=$ordenes->fields['desc_gral'] ?></td>
  </tr>
  <tr> 
    <td align="right" id=mo>Cantidad Total&nbsp;</td>
    <td width="75%" ><?=$parametros['total'] //para evitar una consulta?>
<!--	 Sin Alternativas &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Con 
      Alternativas -->
  	</td>
  </tr>
</table>
<br>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr align="center" id=mo> 
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'nro_orden','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
<td width="9%" height="20" style="cursor:hand" >N&ordm; Orden </td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'fecha_entrega','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="11%" style="cursor:hand">Fecha entrega </td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'cliente','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="34%" style="cursor:hand">Cliente</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'razon_social','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="19%" style="cursor:hand">Proveedor</td>
</a>
<!--
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'monto','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="17%" style="cursor:hand">Monto</td>
</a>
-->
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'cantidad','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="10%" title='Cantidad de productos encontrados' style="cursor:hand">Cantidad</td>
</a>
  </tr>
<? while (!$ordenes->EOF)
	{
?>
<a target="_blank" href="<?= encode_link("ord_compra.php",array("nro_orden"=>$ordenes->fields['nro_orden'])) ?>">
	<tr <?="bgcolor=".$color=(++$i%2)?"#E0E0E0":"#5090C0" ?> height=15 style="cursor:hand" onmouseover="sobre(this,'white')" onmouseout="bajo(this,'<?=$color ?>')"> 
    <td align="center"> 
      <?=$ordenes->fields['nro_orden'] ?>
    </td>
    <td align="center"> 
      <?= date2("S",$ordenes->fields['fecha_entrega']) ?>
    </td>
    <td><?=$ordenes->fields['cliente'] ?></td>
    <td><?=$ordenes->fields['razon_social'] ?></td>
<!--    <td align=right><?=$ordenes->fields['simbolo']." ".$ordenes->fields['monto'] ?></td>-->
    <td align=right> 
      <?=$ordenes->fields['cantidad'] ?>
    </td>
  </tr>
</a>
<?
		$ordenes->movenext();
	}
 ?>
</table>
<br>
<?=fin_pagina(); ?> 
<!--</body>
</html>
