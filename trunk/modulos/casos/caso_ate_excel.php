<?php
/*
Autor: Broggi
Creado: 01/06/2004

$Author: fernando $
$Revision: 1.2 $
$Date: 2004/11/01 21:06:00 $
*/

require_once("../../config.php");
 $style['ancho']='style=\'mso-height-source:userset;height:34.5pt\'';
 $style['x139']='style=\'color:#003366;
	font-size:9.0pt;
	font-weight:700;
	font-family:"Times New Roman", serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:1.0pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:.5pt solid windowtext;
	white-space:normal;\'';
 $style['x143']='style=\'font-size:8.0pt;
	font-family:"Times New Roman", serif;
	mso-font-charset:0;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	white-space:normal;\'';
 $style['x137']='style=\'color:#003366;
	font-size:16.0pt;
	font-weight:700;
	text-decoration:underline;
	text-underline-style:single;
	font-family:"Times New Roman", serif;
	mso-font-charset:0;
	mso-number-format:"Short Date";
    text-align:center;
	vertical-align:top;\'';
 
excel_header("servicios_activos.xls");
$sql = "select * from cas_ate where activo=1 order by nombre";
$result_consulta = sql($sql) or fin_pagina();

?>
<html>
<body>
<table width=100% align=center border=1  cellspacing="4" cellpadding="10">
<tr>
  <td <?=$style["x137"]?> colspan="9">
   LISTADO DE CENTROS AUTORIZADOS DE SERVICIOS (C.A.S.) - CDR - CORADIR S.A.
  </td>
 </tr>
 <tr>
  <td colspan="9">
   &nbsp
  </td>
 </tr>
<tr <?=$style['ancho']?>  >
 <td  <?=$style["x139"]?>  width="22"> <font color="Black" size="1">&nbsp</td>
 <td  <?=$style["x139"]?>  width="66"> <font color="Black" size="1"> <b>Provincia</b></font></td>
 <td  <?=$style["x139"]?>  width="88"> <font color="Black" size="1"> <b>Ciudad</b></font></td>
 <td  <?=$style["x139"]?>  width="124"> <font color="Black" size="1"> <b>Nombre</b></font></td>
 <td  <?=$style["x139"]?>  width="40"> <font color="Black" size="1"> <b>CP</b></font></td>
 <td  <?=$style["x139"]?>  width="95"> <font color="Black" size="1"> <b>Dirección</b></font></td>
 <td  <?=$style["x139"]?>  width="120"> <font color="Black" size="1"> <b>Teléfono</b></font></td>
 <td  <?=$style["x139"]?>  width="109"> <font color="Black" size="1"> <b>Contacto</b></font></td>
 <td  <?=$style["x139"]?>  width="204"> <font color="Black" size="1"> <b>Dirección E-Mail</b></font></td>
</tr>
<?
 $i=1;
 while (!$result_consulta->EOF)
 {
?>
 <tr <?=$style["ancho"]?>>
  <td  <?=$style["x139"]?> ><?=$i?></td>
  <?
   if ($result_consulta->fields['id_distrito'])
   {$sql = "select nombre from licitaciones.distrito where id_distrito=".$result_consulta->fields['id_distrito'];
    $result_provincia = sql($sql) or fin_pagina();
   } 
  ?>
  <td <?=$style["x143"]?> > <font color="Black" size="1"> <?if($result_consulta->fields['id_distrito']) echo$result_provincia->fields['nombre']; else echo $result_consulta->fields['id_distrito'];?></font></td>
  <td <?=$style["x143"]?> > <font color="Black" size="1"> <?=$result_consulta->fields['ciudad']?></font></td>
  <td <?=$style["x143"]?> > <font color="black" size="1"> <?=$result_consulta->fields['nombre']?></font></td>
  <td <?=$style["x143"]?> > <font color="Black" size="1"> <?=$result_consulta->fields['cp']?></font></td>
  <td <?=$style["x143"]?> > <font color="Black" size="1"> <?=$result_consulta->fields['direccion']?></font></td>
  <td <?=$style["x143"]?> > <font color="Black" size="1"> <?=$result_consulta->fields['tel']?></font></td>
  <td <?=$style["x143"]?> > <font color="Black" size="1"> <?=$result_consulta->fields['contacto']?></font></td>
  <td <?=$style["x143"]?> > <font color="Black" size="1"> <?=$result_consulta->fields['mail']?></font></td>
 </tr> 
<?
 $i++;
 $result_consulta->MoveNext();
 }
?> 
</table>
