<?php
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
 
excel_header("nota_credito.xls");
//$sql = $parametros['sql'];
$sql = "SELECT nota_credito.*, moneda.simbolo,moneda.nombre,moneda.id_moneda,
				proveedor.id_proveedor,proveedor.razon_social,fecha,id_info_rma
		from general.nota_credito 
		JOIN licitaciones.moneda using(id_moneda) 
		JOIN general.proveedor Using (id_proveedor) 
		left join general.log_nota_credito using (id_nota_credito) 
		left join stock.info_rma using (id_nota_credito)
		WHERE (tipo ilike '%creación' or tipo is null) and (estado=0 or estado=1)
		ORDER BY nota_credito.id_nota_credito ";
$result_consulta = sql($sql) or fin_pagina();
?>
<html>
<body>
<table width=100% align=center border=1  cellspacing="4" cellpadding="6">
<tr>
  <td <?=$style["x137"]?> colspan="6">
   LISTADO DE NOTAS DE CREDITO
  </td>
 </tr>
 <tr>
  <td colspan="6">
   &nbsp
  </td>
 </tr>
<tr <?=$style['ancho']?> > 
 <td  <?=$style["x139"]?>  width="65" bgcolor="Silver"> <font color="Black" size="2"> <b>Número</b></font></td>
 <td  <?=$style["x139"]?>  width="90" bgcolor="Silver"> <font color="Black" size="2"> <b>Fecha Creación</b></font></td>
 <td  <?=$style["x139"]?>  width="120" bgcolor="Silver"> <font color="Black" size="2"> <b>Monto</b></font></td>
 <td  <?=$style["x139"]?>  width="300" bgcolor="Silver"> <font color="Black" size="2"> <b>Proveedor</b></font></td>
 <td  <?=$style["x139"]?>  width="450" bgcolor="Silver"> <font color="Black" size="2"> <b>Obsevaciones</b></font></td> 
 <td  <?=$style["x139"]?>  width="120" bgcolor="Silver"> <font color="Black" size="2"> <b>Estado</b></font></td> 
 <td  <?=$style["x139"]?>  width="120" bgcolor="Silver"> <font color="Black" size="2"> <b>Nº Rma</b></font></td> 
</tr>
<?
 $i=1;
 while (!$result_consulta->EOF)
 {
 	if (($i%2)==0) $color_fondo='#FFFFCC';
 	else $color_fondo='#FFFFFF';
?>
 
 <tr <?=$style["ancho"]?>>  
  <td <?=$style["x139"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?=$result_consulta->fields['id_nota_credito']?></font></td>
  <td <?=$style["x143"]?> bgcolor='<?=$color_fondo?>'> <font color="black" > <?=fecha($result_consulta->fields["fecha"])?></font></td>
  <td <?=$style["x144"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?echo $result_consulta->fields['simbolo'].' '.formato_money($result_consulta->fields['monto']) ?></font></td>
  <td <?=$style["x144"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?=$result_consulta->fields['razon_social']?></font></td>
  <td <?=$style["x144"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?=$result_consulta->fields['observaciones']?></font></td>
  <?
  if ($result_consulta->fields['estado']==0){
  	$estado_excel="Pendiente";
  }
  if ($result_consulta->fields['estado']==1){
  	$estado_excel="Pendiente (Reservada para pago de OC)";
  }  	
  ?>
  <td <?=$style["x144"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?=$estado_excel?></font></td>
  <?
  if ($result_consulta->fields['id_info_rma'])
    $id_info_rma=$result_consulta->fields['id_info_rma'];
  else $id_info_rma="";
  ?>
  <td <?=$style["x144"]?> bgcolor='<?=$color_fondo?>'> <font color="Black" > <?=$id_info_rma?></font></td>
 </tr> 
<?
 $i++;
 $result_consulta->MoveNext();
 }
?> 
</table>