<?php
/*
Autor: Gabriel
MODIFICADO POR:
$Author: gabriel $
$Revision: 1.5 $
$Date: 2006/01/18 19:30:48 $
*/

require_once("../../config.php");
require_once("../personal/gutils.php");

$usuario=$parametros["usuario"] or $usuario=$_POST["usuario"];
$nro_orden=$parametros["nro_orden"] or $nro_orden=$_POST["nro_orden"];
$comentario=$_POST["comentario"];
$gag_estado=$parametros["gag_estado"];

if (!$gag_estado){
	if ($_POST["ch_vc"]) $gag_estado="#AAFFAA";
	else if ($_POST["ch_rc"]) $gag_estado="#EE6677";
	else $gag_estado="#AAFFAA";
}

if ($_POST["guardar"]){
	$sql="update licitaciones.linea_produccion_bsas set estado_linea_produccion=".(($gag_estado)?"'".$gag_estado."'":"null").", comentario='$comentario', usuario='".$usuario."' where nro_orden=".$nro_orden;
	sql($sql, "c19") or fin_pagina();
?>
	<script>
		window.opener.location.reload();
		window.close();
		
	</script>
<?
}
echo $html_header;
?>
<script>
	function chequear(valor){
		if (valor==1){
			if (document.all.ch_vc.checked){
				document.all.ch_rc.checked=0;
			}else{
				document.all.ch_rc.checked=1;
			}
		}else{
			if (document.all.ch_rc.checked){
				document.all.ch_vc.checked=0;
			}else{
				document.all.ch_vc.checked=1;
			}
		}
	}

</script>
<form method="POST" action="editar_orden.php" name="editar_orden">
<input type="hidden" name="usuario" value="<?=$usuario?>">
<input type="hidden" name="nro_orden" value="<?=$nro_orden?>">

<table align="center" cellpadding="0" cellspacing="1" width="100%" border="1">
	<tr>
		<td colspan="2" bgcolor="#aaffaa">
			<input type="checkbox" id="ch_vc" name="ch_vc" <?=(($gag_estado=="#AAFFAA")?"checked":"")?> onclick="chequear(1);">
			Lista para producir
		</td>
	</tr>
	<tr>
		<td colspan="2" bgcolor="#ee6677">
			<input type="checkbox" id="ch_rc" name="ch_rc" <?=(($gag_estado=="#EE6677")?"checked":"")?> onclick="chequear(2);">
			No debe producirse aún
		</td>
	</tr>
	<tr>
		<td colspan="2" id="mo">
			Escriba el motivo
		</td>
	</tr>
	<tr>
		<td id="mo">Comentario:</td>
		<td align="right">
			<textarea name="comentario" cols="80" rows="5"><?=$_POST["comentario"]?></textarea>
		</td>
	</tr>

	<tr bgcolor="<?=$bgcolor_out?>">
		<td align=center colspan="4">
			<input name="guardar" type="submit" value="Guardar cambios">&nbsp;
			<input name="boton" type="button" value="Cerrar" onclick="window.close();">
    </td>
	</tr>
</table>
</form>
</body>
</html>
<?
fin_pagina();
?>