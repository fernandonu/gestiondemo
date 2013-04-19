<? 
/*
Autor: GACZ
Creado: jueves 20/05/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.5 $
$Date: 2004/07/13 22:59:20 $
*/
require_once("../../config.php");
//variables_form_busqueda("prod_ofer");


//LOS DATOS VIENEN DE lic_prod_ofer.php
//$q2="select * from productos where id_producto=$parametros[id_producto]";
//$prod=sql($q2) or fin_pagina();;

ob_start();
list($q,$total,$link,$notup)=form_busqueda($q,$orden_array,$filtro_array,$link_tmp,$where,"buscar");
ob_clean();

$ordenar=$parametros['ordenar'] or $ordenar="id_licitacion";
$direccion=$parametros['direccion'] or $direccion="asc";

if ($direccion=="asc")
	$notdir="desc";

//primera expresion busca el subselect
//la segunda una consulta comun
//echo "COSULTA ORIG: $q<br><br>";
if (eregi("^select.*from(.*select.*from.*where.*)(where.*)(group by.*$)|^select.*from(.*select.*from.*where.*)(where.*)*(group by.*$)|^select.*from(.*)(where.*)(group by.*$)|^select.*from(.*)(where.*)*(group by.*$)",$q,$arr))
//									1				2			3					   			4				 5			 6						7		8			9					10	  11		12	
{
	$q="select distinct(id_licitacion),p2.desc_gral,sum(r.cantidad*p1.cantidad) as cantidad,d.nombre as d_nombre,e.nombre as e_nombre,fecha_apertura,nro_lic_codificado from ".$arr[1].$arr[4].$arr[7].$arr[10].$arr[2].$arr[5].$arr[8].$arr[11];
	$q.=(($arr[2] || $arr[5] || $arr[8] || $arr[11])?"AND":"WHERE")." p2.id_producto=$parametros[id_producto] group by id_licitacion,p1.id_producto,p2.desc_gral,d.nombre,e.nombre,fecha_apertura,nro_lic_codificado order by $ordenar $direccion";
}
//echo "CONSULTA: $q <br><br>";
//print_r($arr);

$licitaciones=sql($q) or fin_pagina();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Lista de Licitaciones para Productos</title>
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
    <td><?=$licitaciones->fields['desc_gral'] ?></td>
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
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'id_licitacion','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
<td width="8%" height="20" style="cursor:hand" >ID</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'fecha_apertura','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="12%" style="cursor:hand">Apertura</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'e_nombre','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="40%" style="cursor:hand">Entidad</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'d_nombre','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="13%" style="cursor:hand">Distrito</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'nro_lic_codificado','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="17%" style="cursor:hand">Numero</td>
</a>
<a href='<?=encode_link($_SERVER['SCRIPT_NAME'],array('ordenar'=>'cantidad','id_producto'=>$parametros['id_producto'],'total'=>$parametros['total'],'direccion'=>$notdir)) ?>'>
    <td width="10%" title='Cantidad de productos encontrados' style="cursor:hand">Cantidad</td>
</a>
  </tr>
<? while (!$licitaciones->EOF)
	{
?>
<a target="_blank" href="<?= encode_link("licitaciones_view.php",array("ID"=>$licitaciones->fields['id_licitacion'],"cmd1"=>"detalle")) ?>">
	<tr <?="bgcolor=".$color=(++$i%2)?"#E0E0E0":"#5090C0" ?> height=15 style="cursor:hand" onmouseover="sobre(this,'white')" onmouseout="bajo(this,'<?=$color ?>')"> 
    <td align="center"> 
      <?=$licitaciones->fields['id_licitacion'] ?>
    </td>
    <td align="center"> 
      <?= date2("SHM",$licitaciones->fields['fecha_apertura']) ?>
    </td>
    <td><?=$licitaciones->fields['e_nombre'] ?></td>
    <td><?=$licitaciones->fields['d_nombre'] ?></td>
    <td><?=$licitaciones->fields['nro_lic_codificado'] ?></td>
    <td align="right"> 
      <?=$licitaciones->fields['cantidad'] ?>
    </td>
  </tr>
</a>
<?
		$licitaciones->movenext();
	}
 ?>
</table>
<br>
<?=fin_pagina(); ?> 
<!--</body>
</html>
