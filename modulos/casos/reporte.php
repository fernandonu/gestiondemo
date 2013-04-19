<?php
/*
$Author: nazabal $
$Revision: 1.6 $
$Date: 2005/03/30 21:51:23 $
*/
include "head.php";

$id=$parametros["id"] or $id=$_POST["id"];
if ($_POST["guardar"]=="Guardar") {
	$sql=Array();
	if ($_POST["comentario_nuevo"]) {
		$sql[]=nuevo_comentario($id,"REPORTE_TECNICO",$_POST["comentario_nuevo"]);
	}
	if ($_POST["finalcheck"]==1) {
		$fechafin=date("Y-m-d H:i:s");
		$usuario=$_ses_user["name"];
		$sql[]="UPDATE reporte_tecnico SET fechafinal='$fechafin',usuariofinal='$usuario',estado='Solucionado' where id_reporte=$id";
	}
	else $sql[]="UPDATE reporte_tecnico SET fechafinal=NULL,usuariofinal=NULL,estado='Pendiente' where id_reporte=$id";
	sql($sql);
}
if ($parametros["cmd1"] == "modificar_comentario") {
	editar_comentario($parametros["id_comentario"]," ");
	fin_pagina();
}
if ($_POST["guardar_comentario"]) {
	guardar_comentario();
	$id=$_POST["id_gestion"];
}

$sql="select reporte,licitacion.id_licitacion,entidad.nombre,fechafinal,usuariofinal from reporte_tecnico "
	."LEFT JOIN licitacion USING (id_licitacion) INNER JOIN entidad USING (id_entidad) "
	."WHERE id_reporte=$id";
$result=$db->execute($sql) or die ($db->errormsg());
?>
<form action="reporte.php" name="reporte" method="POST">
	<input type=hidden name=id value='<? echo $id; ?>'>
	<br>
	<table width=95% border=1 cellspacing=1 cellpadding=2 bgcolor=<? echo $bgcolor2; ?> align=center>
	<tr>
		<td colspan=2 style="border:<? echo $bgcolor3; ?>;" colspan=2 align=center id=mo>
			<font size=+1>Reporte de Problema Técnico</font>
		</td>
	</tr>
	<tr>
		<td>
			ID. de licitacion: <b><? echo $result->fields["id_licitacion"]; ?></b>
		</td>
		<td align=right>
			Cliente: <b><? echo $result->fields["nombre"]; ?></b>
		</td>
	</tr>
	<tr>
		<td colspan=2>
			<b>Reporte:</b><br>
			<textarea readonly name=reporte cols=120 rows=10><? echo $result->fields["reporte"]; ?></textarea>
		</td>
	</tr>
	<tr>
		<td colspan=2 align=center>
			<input type=checkbox name=finalcheck <? if ($result->fields["fechafinal"]) echo "checked"; ?> value=1> <b>Problema Técnico Solucionado.</b>
			<? 
			if ($result->fields["fechafinal"]) {
				echo "<br><br><p align=left><font color=red size=+1>El reporte fue finalizado";
				$fecha=substr($result->fields["fechafinal"],0,10);
				$hora=substr($result->fields["fechafinal"],11,8);
				$fech=fecha($fecha);
				echo "<font color=red size=+1>El dia <b>$fech</b> a las <b>$hora</b> por <b>".$result->fields["usuariofinal"]."</b></p></font>";
			}
			?>
		</td>
	</tr>
	<tr>
		<td colspan=2 style="border:<? echo $bgcolor3; ?>;" colspan=2 align=center id=mo>
			<font size=+1>Comentarios</font>
		</td>
	</tr>
	<tr>
		<td colspan=2>
			<? 
			if (!$result->fields["fechafinal"]) $edit=1; else $edit=0;
			gestiones_comentarios($id,"REPORTE_TECNICO",$edit); 
			?>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2 style="border:<? echo $bgcolor2; ?>">
		<input type=submit name=guardar style="width: 100;" value="Guardar">
		</td>
	</tr>
	</table>
</form>