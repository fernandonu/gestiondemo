<?
/*
$Author: cesar $
$Revision: 1.6 $
$Date: 2004/08/06 23:48:02 $
*/

require_once("../../config.php");

echo $html_header;

echo "<script language=javascript src='$html_root/lib/colorpicker.js'></script>";
echo "<form action=licitaciones_config.php method=post>\n";
echo "<table width=100% border=0>";
$error = 0;

if ($_POST["dis_agregar"]) {
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor3><b>";
	if ($_POST["dis_nombre"] == "") {
		Error("Falta ingresar el nombre del nuevo Distrito");
	}
	$sql = "select id_distrito from distrito where nombre='".$_POST["dis_nombre"]."'";
	$result = $db->Execute($sql) or die($db->ErrorMsg());
	if ($result->RecordCount() > 0) {
		Error("Ya existe un distrito con el nombre \"".$_POST["dis_nombre"]."\"");
	}
	if (!$error) {
		$sql = "insert into distrito (nombre) values ('".$_POST["dis_nombre"]."')";
		$result = $db->Execute($sql) or die($db->ErrorMsg());
		Aviso("Los datos se cargaron correctamente");
	}
	unset($_POST);
}
/*if ($_POST["ent_agregar"]) {
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor3><b>";
	if ($_POST["ent_distrito"] == "") {
		Error("Falta seleccionar el Distrito");
	}
	if ($_POST["ent_nombre"] == "") {
		Error("Falta ingresar el nombre de la nueva Entidad");
	}
	$sql = "select id_entidad from entidad where nombre='".$_POST["ent_nombre"]."' and id_distrito=".$_POST["ent_distrito"];
	$result = $db->Execute($sql) or die($db->ErrorMsg());
	if ($result->RecordCount() > 0) {
		Error("Ya existe una entidad con el nombre \"".$_POST["ent_nombre"]."\"");
	}
	if (!$error) {
		$sql = "insert into entidad (id_distrito,nombre) values (".$_POST["ent_distrito"].",'".$_POST["ent_nombre"]."')";
		$result = $db->Execute($sql) or die($db->ErrorMsg());
		Aviso("Los datos se cargaron correctamente");
	}
	unset($_POST);
}*/
if ($_POST["mon_agregar"]) {
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor3><b>";
	if ($_POST["mon_nombre"] == "") {
		Error("Falta ingresar el nombre de la nueva Moneda");
	}
	$sql = "select id_moneda from moneda where nombre='".$_POST["mon_nombre"]."'";
	$result = $db->Execute($sql) or die($db->ErrorMsg());
	if ($result->RecordCount() > 0) {
		Error("Ya existe una moneda con el nombre \"".$_POST["mon_nombre"]."\"");
	}
	if (!$error) {
		$sql = "insert into moneda (nombre) values ('".$_POST["mon_nombre"]."')";
		$result = $db->Execute($sql) or die($db->ErrorMsg());
		Aviso("Los datos se cargaron correctamente");
	}
	unset($_POST);
}
if ($_POST["est_agregar"]) {
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor3><b>";
	if ($_POST["est_nombre"] == "") {
		Error("Falta ingresar el nombre del nuevo Estado");
	}
	if ($_POST["est_color"] == "") {
		$est_color = "#FFFFFF";
	}
	else {
		if (ereg("^\#[0-9a-fA-F]{6}$",$_POST["est_color"])) {
			$est_color = $_POST["est_color"];
		}
		else {
			Error("El formato del color para el nuevo estado no es válido");
		}
	}
	$sql = "select id_estado from estado where nombre='".$_POST["est_nombre"]."'";
	$result = $db->Execute($sql) or die($db->ErrorMsg());
	if ($result->RecordCount() > 0) {
		Error("Ya existe un estado con el nombre \"".$_POST["est_nombre"]."\"");
	}
	if (!$error) {
		$sql = "insert into estado (nombre,color,ubicacion) values ('".$_POST["est_nombre"]."','$est_color','".$_POST["est_mostrar"]."')";
		$result = $db->Execute($sql) or die($db->ErrorMsg());
		Aviso("Los datos se cargaron correctamente");
	}
	unset($_POST);
}
if ($_POST["tipo_agregar"]) {
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor3><b>";
	if ($_POST["tipo_nombre"] == "") {
		Error("Falta ingresar el nombre del nuevo Tipo de entidad");
	}
	$sql = "select id_tipo_entidad from tipo_entidad where nombre='".$_POST["tipo_nombre"]."'";
	$result = $db->Execute($sql) or die($db->ErrorMsg());
	if ($result->RecordCount() > 0) {
		Error("Ya existe un Tipo de entidad con el nombre \"".$_POST["tipo_nombre"]."\"");
	}
	if (!$error) {
		$sql = "insert into tipo_entidad (nombre) values ('".$_POST["tipo_nombre"]."')";
		$result = $db->Execute($sql) or die($db->ErrorMsg());
		Aviso("Los datos se cargaron correctamente");
	}
	unset($_POST);
}

echo "<tr><td align=center valign=top>";
//Distritos
echo "<br><table border=1 cellspacing=0 cellpadding=5 align=center bgcolor=$bgcolor_out>\n";
echo "<tr><td colspan=2 id=mo style=\"border:$bgcolor3;\">Distritos</td></tr>";
echo "<tr><td style=\"border:$bgcolor3;\"><b>Datos Actuales:</b></td></tr>";
echo "<tr><td align=left>Distrito: <select name='dis_distrito'>\n";
$sql = "select id_distrito,nombre from distrito order by nombre";
$result = $db->Execute($sql) or die($db->ErrorMsg());
while ($row = $result->FetchRow()) echo "<option value='$row[0]'>$row[1]\n";
echo "</select></td>\n";
echo "</tr><tr>";
echo "<td style=\"border:$bgcolor3;\"><b>Nuevo Distrito:</b></td></tr>\n";
echo "<td>Nombre: <input type='text' name='dis_nombre' size=20 maxlength=50>\n";
echo "<input type=submit name=dis_agregar value='Agregar'></td></tr>\n";
echo "</table>\n";
echo "</td><td align=center valign=top>";
//Estados
echo "<br><table border=1 cellspacing=0 cellpadding=5 align=center bgcolor=$bgcolor_out>\n";
echo "<tr><td colspan=2 id=mo style=\"border:$bgcolor3;\">Estados</td></tr>";
echo "<tr><td style=\"border:$bgcolor3;\"><b>Datos Actuales:</b></td></tr>";
echo "<tr><td align=left>Estado: <select name='est_estado'>\n";
$sql = "select id_estado,nombre,color from estado order by nombre";
$result = $db->Execute($sql) or die($db->ErrorMsg());
while ($row = $result->FetchRow()) {
	echo "<option value='$row[0]' ";
	echo "style='background-color: $row[2]; ";
	echo "color:".contraste($row[2],"#000000","#ffffff").";'";
	echo ">$row[1]\n";
}
echo "</select></td>\n";
echo "</tr><tr>";
echo "<td style=\"border:$bgcolor3;\"><b>Nuevo Estado:</b></td></tr>\n";
echo "<td><table width=100%><tr>";
echo "<td align=left valign=top>Mostrar en:</td>";
echo "<td colspan=2>";
echo "<input type=radio name=est_mostrar value='ACTUALES' checked>Próximas/Presentadas<br>";
echo "<input type=radio name=est_mostrar value='PRESUPUESTO'>Presupuesto<br>";
echo "<input type=radio name=est_mostrar value='HISTORIAL'>Historial";
echo "</tr><tr>";
echo "<td align=right>Color:</td>";
echo "<td align=left><input type='text' name='est_color' id='sel_color_estfield' size=20 maxlength=7></td>";
echo "<td alidn=left><a href=\"javascript:pickColor('sel_color_est');\" id='sel_color_est'><img src='$html_root/imagenes/sel_color.gif' border=0 alt='Haga click aquí para seleccionar el color'></a></td>";
echo "</tr><tr>";
echo "<td align=right>Nombre:</td>";
echo "<td align=left><input type='text' name='est_nombre' size=20 maxlength=50></td>\n";
echo "<td align=left><input type=submit name=est_agregar value='Agregar'></td>";
echo "</tr></table>";
echo "</td></tr>\n";
echo "</td></tr></table>";
//Entidades
/*echo "<br><table border=1 cellspacing=0 cellpadding=5 align=center bgcolor=$bgcolor2>\n";
echo "<tr><td colspan=2 id=mo style=\"border:$bgcolor3;\">Entidades</td></tr>";
echo "<tr><td style=\"border:$bgcolor3;\"><b>Datos Actuales:</b></td></tr>";
echo "<tr><td><table width='100%'><tr>";
echo "<td align=right>Distrito:</td>";
echo "<td align=left><select name=ent_distrito onchange='document.forms[0].submit()'>\n";
if (!$_POST["ent_distrito"]) echo "<option value=''>\n";
$sql = "select id_distrito,nombre from distrito order by nombre";
$result = $db->Execute($sql) or die($db->ErrorMsg());
while ($row = $result->FetchRow()) {
	echo "<option value='$row[0]'";
	if ($row[0] == $_POST["ent_distrito"]) echo " selected";
	echo ">$row[1]\n";
}
echo "</select></td>\n";
echo "</tr><tr>";
echo "<td align=right>Entidad:</td>\n";
echo "<td align=left><select name=ent_entidad>\n";
if ($_POST["ent_distrito"]) {
	$sql = "select id_entidad,nombre from entidad where id_distrito=".$_POST["ent_distrito"]." order by nombre";
	$result = $db->Execute($sql) or die($db->ErrorMsg());
	while ($row = $result->FetchRow()) {
		echo "<option value='$row[0]'";
		if ($row[0] == $ent_entidad) echo " selected";
		echo ">$row[1]\n";
	}
}
else { echo "<option value=''>Seleccione el Distrito\n"; }
echo "</select></td></tr></table>\n";
echo "</td>";
echo "</tr><tr>";
echo "<td style=\"border:$bgcolor3;\"><b>Nueva Entidad:</b></td></tr>\n";
echo "<td>Nombre: <input type='text' name='ent_nombre' size=20 maxlength=50>\n";
echo "<input type=submit name=ent_agregar value='Agregar'></td>\n";
echo "</tr>\n";
echo "</table>";
*/
echo "</td></tr><tr><td align=center valign=top>";
//echo "</tr><tr>";
//Monedas
echo "<br><table border=1 cellspacing=0 cellpadding=5 align=center bgcolor=$bgcolor_out>\n";
echo "<tr><td colspan=2 id=mo style=\"border:$bgcolor3;\">Monedas</td></tr>";
echo "<tr><td style=\"border:$bgcolor3;\"><b>Datos Actuales:</b></td></tr>";
echo "<tr><td align=left>Moneda: <select name='mon_moneda'>\n";
$sql = "select id_moneda,nombre from moneda order by nombre";
$result = $db->Execute($sql) or die($db->ErrorMsg());
while ($row = $result->FetchRow()) echo "<option value='$row[0]'>$row[1]\n";
echo "</select></td>\n";
echo "</tr><tr>";
echo "<td style=\"border:$bgcolor3;\"><b>Nueva Moneda:</b></td></tr>\n";
echo "<td>Nombre: <input type='text' name='mon_nombre' size=20 maxlength=50>\n";
echo "<input type=submit name=mon_agregar value='Agregar'></td></tr>\n";
echo "</table>\n";
echo "</td><td align=center valign=top>";
//Tipo de entidad
echo "<br><br><br><br><table border=1 cellspacing=0 cellpadding=5 align=center bgcolor=$bgcolor_out>\n";
echo "<tr><td colspan=2 id=mo style=\"border:$bgcolor3;\">Tipos de entidades</td></tr>";
echo "<tr><td style=\"border:$bgcolor3;\"><b>Datos Actuales:</b></td></tr>";
echo "<tr><td align=left>Tipo de entidad: <select name='tipo_tipo'>\n";
$sql = "select id_tipo_entidad,nombre from tipo_entidad order by nombre";
$result = $db->Execute($sql) or die($db->ErrorMsg());
while ($row = $result->FetchRow()) echo "<option value='$row[0]'>$row[1]\n";
echo "</select></td>\n";
echo "</tr><tr>";
echo "<td style=\"border:$bgcolor3;\"><b>Nuevo tipo de entidad:</b></td></tr>\n";
echo "<td>Nombre: <input type='text' name='tipo_nombre' size=20 maxlength=50>\n";
echo "<input type=submit name=tipo_agregar value='Agregar'>\n";
//echo "</td></tr></table>\n";

//echo "<tr><td align=center valign=top>";
//Tipo de entidad
/*
echo "<br><table border=1 cellspacing=0 cellpadding=5 align=center bgcolor=$bgcolor2>\n";
echo "<tr><td colspan=2 id=mo style=\"border:$bgcolor3;\">Tipos de entidades</td></tr>";
echo "<tr><td style=\"border:$bgcolor3;\"><b>Datos Actuales:</b></td></tr>";
echo "<tr><td align=left>Tipo de entidad: <select name='tipo_tipo'>\n";
$sql = "select id_tipo_entidad,nombre from tipo_entidad order by nombre";
$result = $db->Execute($sql) or die($db->ErrorMsg());
while ($row = $result->FetchRow()) echo "<option value='$row[0]'>$row[1]\n";
echo "</select></td>\n";
echo "</tr><tr>";
echo "<td style=\"border:$bgcolor3;\"><b>Nuevo tipo de entidad:</b></td></tr>\n";
echo "<td>Nombre: <input type='text' name='tipo_nombre' size=20 maxlength=50>\n";
echo "<input type=submit name=tipo_agregar value='Agregar'></td></tr>\n";
echo "</table>\n";*/
echo "</td></tr></table>";
echo "</form><br><br>\n";




if ($_POST["conf_avanzada"]) {
	$sql = "SELECT L.IDLicitación,D.*,E.IDEntidad,E.Descripción ";
	$sql .= "FROM licitaciones.licitaciones AS L,";
	$sql .= "licitaciones.distrito AS D,";
	$sql .= "licitaciones.entidades AS E ";
	$sql .= "WHERE ";
	$sql .= "E.IDDistrito = D.IDDistrito AND ";
	$sql .= "L.IDEntidad = E.IDEntidad AND ";
	$sql .= "GROUP BY L.IDLicitación ";
	$sql .= "ORDER BY D.Distrito,";
	$sql .= "E.Descripción";
	$result = db_query($sql) or db_die($sql);
	while ($row = db_fetch_row($result)) {
		$codigo[$row[1]][$row[3]][$row[5]][] = $row[0];
		$nombre[$row[2]][$row[4]][$row[6]][] = $row[0];
	}
	echo "<font face='Courier New, Courier, mono'>\n";
	foreach ($nombre as $dist => $arr1) {
		echo "|<br>\n";
		echo "+--$dist<br>|&nbsp;&nbsp;|<br>\n";
		foreach ($arr1 as $tipoe => $arr2) {
			echo "|&nbsp;&nbsp;+--$tipoe<br>\n";
			foreach ($arr2 as $ent => $arr3) {
				echo "|&nbsp;&nbsp;|&nbsp;&nbsp;|<br>\n";
				echo "|&nbsp;&nbsp;|&nbsp;&nbsp;+--$ent: (";
				echo implode(", ",$arr3);
				echo ")";
//					echo "|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Licitaciones: \n";
//					foreach ($arr3 as $lic) {
//						echo "$lic, ";
//					}
				echo "<br>\n";
			}
		}
//			echo "|&nbsp;&nbsp;|<br>\n";
	}
	echo "Total: ".db_query_rows($result);
	echo "</font>\n";
}
?>