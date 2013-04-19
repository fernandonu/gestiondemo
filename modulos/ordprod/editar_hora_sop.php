<?php
/*
Autor: Gabriel
MODIFICADO POR:
$Author: ferni $
$Revision: 1.2 $
$Date: 2006/01/05 20:07:52 $
*/

require_once("../../config.php");
$fecha=$parametros["fecha"] or $fecha=Fecha_db($_POST["fecha"]);
$id_entrega_estimada=$parametros["id_entrega_estimada"] or $id_entrega_estimada=$_POST["id_entrega_estimada"];

if ($parametros["fecha_inamovible"])
	$fecha_inamovible=$parametros["fecha_inamovible"];
else{
	if ($_POST["fecha_inamovible"]=='on'){
		$fecha_inamovible=1;
	}
	else{
		$fecha_inamovible=0;
	}
}


if ($_POST["guardar"]){
		
	$sql="update licitaciones.entrega_estimada set fecha_inamovible=$fecha_inamovible, fecha_estimada='".(($fecha)?$fecha:"null")."' where id_entrega_estimada=".$id_entrega_estimada;
	$result=sql($sql, "c16 ".$sql) or fin_pagina();
	?>
		<script>
			window.opener.location.reload();
			window.close();
		</script>
	<?
}
cargar_calendario();
echo $html_header;
?>
<form method="POST" action="editar_hora_sop.php" name="editar_hora_sop">
<input type="hidden" name="id_entrega_estimada" value="<?=$id_entrega_estimada?>">
<table align="center" cellpadding="0" cellspacing="1" width="90%" border="1">
	<tr>
		<td id="mo"><font size="2">Fecha</font></td>
		<td align="right" bgcolor="<?=$bgcolor_out?>">
			<input type="text" name="fecha" value="<?=Fecha($fecha)?>">&nbsp;<?=link_calendario('fecha');?>
		</td>
	</tr>
	<tr>
		<td colspan="3" bgcolor="<?=$bgcolor_out?>" align="right">
			<font size="2"><b>
			Esta Fecha es Inamovible ya se Coordino con el Cliente --> &nbsp;&nbsp;
			</font></b>
			<input type="checkbox" name="fecha_inamovible" title="Click aqui para marcar como Inamovible la Fecha" <?if ($fecha_inamovible==1) echo "checked"?>>
			
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