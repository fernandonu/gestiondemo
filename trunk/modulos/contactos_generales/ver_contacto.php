<?php
require_once("../../config.php");
$modulo=$parametros["modulo"];
$id_contacto=$parametros["id_contacto"];

global $db;

$sql="select * from contactos_generales where id_contacto_general='$id_contacto'";
$resultado=sql($sql) or fin_pagina();
echo $html_header;
?>
<table  width="70%" align="center" border="1" cellspacing="1" cellpadding="2" bordercolor="#000000" >
<tr id="mo">
<td colspan='2' align="center" ><b>Información Del contacto</td></tr>
<tr height='10%'>
   <td width="25%" id="ma2"  ><b>Nombre:</b></td>
   <td align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["nombre"];?></b></td>
</tr>
<tr height='10%'>
  <td  id="ma2" ><b>Teléfono:</b></td>
  <td align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["tel"];?></b></td>
</tr>
<tr height='10%'>
  <td  id="ma2"><b>Dirección:</b></td>
  <td align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["direccion"];?></b></td>
</tr>
<tr height='10%'>
  <td  id="ma2"><b>Provincia:</b></td>
  <td align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["provincia"];?></b></td>
</tr>
<tr height='10%'>
  <td  id="ma2"><b>Localidad:</b></td>
  <td align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["localidad"];?></b></td>
</tr>
<tr height='10%'>
  <td id="ma2"><b>C.P.:</b></td>
  <td align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["cod_postal"];?></b></td>
</tr>
<tr height='10%'>
  <td id="ma2"><b>Mail:</b></td>
  <td align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["mail"];?></b></td>
</tr>
<tr height='10%'>
  <td id="ma2"><b>Fax:</b></td>
  <td align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["fax"];?></b></td>
</tr>
<tr height='10%'>
  <td id="ma2"><b>ICQ:</b></td>
  <td align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["icq"];?></b></td>
</tr>
<tr height='10%'>
  <td id="ma2" colspan='2'><b>Observaciones:</b></td>
</tr>
<tr>
  <td colspan='2' align="left" bgcolor="<?=$bgcolor3;?>">&nbsp;<b><?=$resultado->fields["observaciones"];?></b></td>
</tr>

<tr>
  <td colspan='2' align="center">
  <input type="button" name="boton" value="Cerrar"  onclick="window.close()">
  </td>
</tr>

</table>
</BODY>
</HTML>
