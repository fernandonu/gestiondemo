<?php
/*AUTOR: Carlitox

MODIFICADO POR:
$Author: fernando $
$Revision: 1.3 $
$Date: 2005/10/12 20:57:46 $
*/

require_once("../../config.php");

// Modificar 
$id = $parametros["id"] or $id = $_POST["id"];
//print_r($_POST);
if ($_POST["guardar"]=="Ingresar") {
	if(!$_POST["titulo"]) $error="Debe colocar un titulo para poder guardar el item.<br>";
	if(!is_numeric($_POST["monto"])) $error.="Debe colocar un monto para poder guardar el item.";
	if (!$error) {
		$sql="select nextval('caja.item_caja_seguridad_id_item_seq') as id_item";
		$result=sql($sql) or fin_pagina();
		$id=$result->fields["id_item"];
		$q[]="insert into item_caja_seguridad (id_item,id_caja_seguridad,titulo,monto,id_moneda,observacion,estado) VALUES (
			$id,1,'".$_POST["titulo"]."',".$_POST["monto"].",".$_POST["moneda"].",'".$_POST["observacion"]."','existente')";
		$q[]="insert into log_caja_seguridad (id_item,id_usuario,fecha,descripcion) VALUES (
			$id,".$_ses_user["id"].",'".date("Y-m-d H:i:s")."','Nuevo item')";
		sql($q) or fin_pagina();
		aviso("Los Datos se Ingresaron con exito.");
	}
	else {
		error($error);
	}
}

if ($_POST["guardar"]=="Guardar") {
	if(!$_POST["titulo"]) $error="Debe colocar un titulo para poder guardar el item.<br>";
	if(!is_numeric($_POST["monto"])) $error.="Debe colocar un monto para poder guardar el item.";
	if (!$error) {
		$q[]="UPDATE item_caja_seguridad SET titulo='".$_POST["titulo"]."',
			id_moneda=".$_POST["moneda"].",
			monto=".$_POST["monto"].",
			observacion='".$_POST["observacion"]."' 
			where id_item=$id";
		$q[]="insert into log_caja_seguridad (id_item,id_usuario,fecha,descripcion) VALUES (
			$id,".$_ses_user["id"].",'".date("Y-m-d H:i:s")."','Se Modifico el item')";
		sql($q) or fin_pagina();
		aviso("Los Datos se Modificaron con exito.");
	}
	else {
		error($error);
	}
}
if ($_POST["sacar"]=="Sacar") {
	$q[]="UPDATE item_caja_seguridad SET estado='historial'  
		where id_item=$id";
	$q[]="insert into log_caja_seguridad (id_item,id_usuario,fecha,descripcion) VALUES (
		$id,".$_ses_user["id"].",'".date("Y-m-d H:i:s")."','El item se paso a historial')";
	sql($q) or fin_pagina();
	aviso("El item paso a historial.");
}

if ($id) {
	// consulta para el item seleccionado
	$sql="select titulo,monto,observacion,id_moneda,estado from item_caja_seguridad where id_item=$id";
	$rs = sql($sql) or fin_pagina();
	$fila=$rs->fetchrow();
	// consulta para traeer los log del item
	$query="select fecha,(usuarios.nombre || ' ' || usuarios.apellido) as nombre,descripcion from log_caja_seguridad join usuarios using(id_usuario) where id_item=$id order by fecha desc";
	$rs_log = sql($query) or fin_pagina();
}

// variables de datos
$titulo = $fila["titulo"] or $titulo=$_POST["titulo"];
$monto = $fila["monto"] or $monto=$_POST["monto"];
$observacion = $fila["observacion"] or $observacion=$_POST["observacion"];
$moneda = $fila["id_moneda"] or $moneda=$_POST["moneda"];
$estado = $fila["estado"] or $estado=$_POST["estado"];
echo $html_header;

?>
<br>
<center><b>Administracion log</b>
<div align=center bgcolor='white' style='overflow:auto;width: 90%;<?if ($id && $rs_log->RowCount()>3) echo "height: 60;"?>'>
<table align='center' width=100% border='1' cellspacing='2' cellpadding='0'>
<tr id='mo'>
	<td>Fecha</td>
	<td>Usuario</td>
	<td>Descripción</td>
</tr>
<?
while ($id && $log=$rs_log->FetchRow()) {
	echo "<tr id=ma>\n";
	$hora=substr($log["fecha"],11,8);
	echo "<td>".fecha($log["fecha"])." $hora</td>\n";
	echo "<td>".$log["nombre"]."</td>\n";
	echo "<td>".$log["descripcion"]."</td>\n";
	echo "</tr>\n";
}
?>
</table>
</div></center>
<form name="form1" action="item_seguridad.php" method="post">
<input type=hidden name=id value="<?=$id?>">
<input type=hidden name=estado value="<?=$estado?>">
<br>
<table align='center' width='90%' border='1' cellspacing='2' cellpadding='0'>
<tr id='mo'>
	<td  colspan=2>
		<b><? if ($id) echo "Modificar Item";
		else echo "Agregar Item";
		?></b>
	</td>
</tr>
<tr id='ma'>
	<td  align=left colspan=2>
		<b>Titulo:</b> &nbsp;&nbsp;&nbsp;<input type=text name=titulo size=100 value='<?=$titulo?>'>
	</td>
</tr>
<tr id='ma'>
	<td align=left colspan=2>
		<b>Monto:</b> &nbsp;&nbsp;&nbsp;
		<select name=moneda>
			<option value=1 <?if ($moneda==1) echo "selected"; ?>>$</option>
			<option value=2 <?if ($moneda==2) echo "selected"; ?>>U$S</option>
		</select> &nbsp;&nbsp;&nbsp;
		<input type=text name=monto value="<?=$monto?>" size=30>
	</td>
</tr>
<tr id='ma'>
	<td align=rigth valign=top>
		<b>Observaciones:</b>
	</td>
	<td align=left border=0>
		<textarea name=observacion rows=10 cols=60><?=$observacion?></textarea>
	</td>
</tr>
<tr id='ma'>
	<td colspan=2>
		<input type=button value="Volver" onClick="document.location='<?=encode_link("caja_seguridad.php",array());?>'">
		<input type=submit name=guardar value="<?if ($id) echo "Guardar"; else echo "Ingresar";?>">
		<input type=submit name=sacar value="Sacar" <?if (!$id or $estado=="historial") echo "disabled";?>>
	</td>
</tr>
</table>
<form>