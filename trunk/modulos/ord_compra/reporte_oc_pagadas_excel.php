<?php
/*
Autor: ferni
Creado: 30/03/2006
*/

require_once("../../config.php");
 $style['ancho']='style=\'mso-height-source:userset;height:34.5pt\'';
 $style['x139']='style=\'color:#003366;
	font-size:11.0pt;
	font-weight:700;
	font-family:"Tahoma", serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:1.0pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:.5pt solid windowtext;
	white-space:normal;\'';
 $style['x143']='style=\'font-size:11.0pt;
	font-family:"Tahoma", serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	white-space:normal;\'';
 $style['x144']='style=\'font-size:11.0pt;
	font-family:"Tahoma", serif;
	mso-font-charset:0;
	text-align:left;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	white-space:normal;\'';
 $style['x137']='style=\'color:#003366;
	font-size:11.0pt;
	font-weight:700;
	text-decoration:underline;
	text-underline-style:single;
	font-family:"Tahoma", serif;
	mso-font-charset:0;
	mso-number-format:"Short Date";
    text-align:center;
	vertical-align:top;\'';
 
excel_header("servicios_activos.xls");
$sql = "SELECT distinct(o.nro_orden),o.desc_prod,total_orden,total_sum,p.razon_social,o.estado,f.id_fact_asociada, ord_pago, simbolo  
			FROM compras.orden_de_compra o 
			left join general.proveedor p using(id_proveedor) 
			left join
				(select sum(cantidad*(case when estado='n' then 0 else precio_unitario end)) as total_sum,sum(cantidad*precio_unitario) as total_orden,nro_orden 
					from compras.fila 
					join compras.orden_de_compra using (nro_orden) 
					group by nro_orden
				) costo using(nro_orden) 
			left join licitaciones.moneda on(moneda.id_moneda=o.id_moneda) 
			left join compras.factura_asociadas f using (nro_orden) 
			WHERE  (estado='d' OR estado='g' OR estado='e') AND id_fact_asociada is null AND razon_social not like '%Stock%' AND no_tiene_factura!=1
			ORDER BY nro_orden DESC";
$result_consulta = sql($sql) or fin_pagina();

?>
<html>
<body>
<table width=100% align=center border=1  cellspacing="4" cellpadding="6">
<tr>
  <td <?=$style["x137"]?> colspan="6">
   LISTADO DE ORDENES DE COMPRA/PAGO QUE NO ESTAN ASOCIADOS A NINGUNA FACTURA 
  </td>
 </tr>
 <tr>
  <td colspan="6">
   &nbsp
  </td>
 </tr>
<tr <?=$style['ancho']?> >
 <td  <?=$style["x139"]?>  width="80" bgcolor="Silver"> <font color="Black" size="2">&nbsp</td>
 <td  <?=$style["x139"]?>  width="86" bgcolor="Silver"> <font color="Black" size="2"> <b>OC</b></font></td>
 <td  <?=$style["x139"]?>  width="70" bgcolor="Silver"> <font color="Black" size="2"> <b>Orden de Pago</b></font></td>
 <td  <?=$style["x139"]?>  width="320" bgcolor="Silver"> <font color="Black" size="2"> <b>Proveedor</b></font></td>
 <td  <?=$style["x139"]?>  width="120" bgcolor="Silver"> <font color="Black" size="2"> <b>Monto</b></font></td>
 <td  <?=$style["x139"]?>  width="190" bgcolor="Silver"> <font color="Black" size="2"> <b>Estado</b></font></td>
</tr>
<?
 $i=1;
 while (!$result_consulta->EOF)
 {
 	if (($i%2)==0) $color_fondo='#FFFFCC';
 	else $color_fondo='#FFFFFF';
?>
 
 <tr <?=$style["ancho"]?>>
  <td <?=$style["x139"]?> bgcolor='<?=$color_fondo?>'><?=$i?></td>
  <td <?=$style["x143"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?=$result_consulta->fields['nro_orden']?></font></td>
  <td <?=$style["x143"]?> bgcolor='<?=$color_fondo?>'> <font color="black" > <?=$result_consulta->fields['ord_pago']?></font></td>
  <td <?=$style["x144"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?=$result_consulta->fields['razon_social']?></font></td>
  <td <?=$style["x144"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?echo $result_consulta->fields['simbolo'] .'  ' . number_format($result_consulta->fields['total_sum'],2,',','.')?></font></td>
  <? switch ($result_consulta->fields['estado']){
		   	case 'd' : $estado_mostrar='Pagadas Parcialmente';
		   		break;
		   	case 'g' : $estado_mostrar='Pagadas Totalmente';
		   		break;	
		   	case 'e' : $estado_mostrar='Enviadas';
		   		break;	   		
		   }
  ?>
  <td <?=$style["x144"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?=$estado_mostrar?></font></td>
 </tr> 
<?
 $i++;
 $result_consulta->MoveNext();
 }
?> 
</table>