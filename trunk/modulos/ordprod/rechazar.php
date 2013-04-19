<?php
/*
AUTOR: Carlitos
MODIFICADO POR:
$Author: cestila $
$Revision: 1.2 $
$Date: 2004/12/01 17:47:07 $
*/

require_once("../../config.php");
echo $html_header;
?>
<table width='95%' align='center'>
<tr id=mo>
	<td>
		<font size=3>Motivo del Rechazo</font>
	</td>
</tr>
<tr bgcolor='<?=$bgcolor_out;?>'>
	<td align=center>
		<textarea name=motivo cols=80 rows=5></textarea>
	</td>
</tr>
<tr bgcolor='<?=$bgcolor_out;?>'>
	<td align=center>
		<input type=button value="Cancelar" onClick="window.close();">
		<input type=button value="Aceptar" onClick="window.opener.document.all.rechazada.value=document.all.motivo.value;window.opener.document.all.modo.value='rechazar';window.opener.document.all.frm_guardar.submit();window.close();">
	</td>
</tr>
</table>
<?
fin_pagina();
?>