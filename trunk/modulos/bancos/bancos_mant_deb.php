<?
/*
$Author: nazabal $
$Revision: 1.1 $
$Date: 2004/12/17 20:04:07 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");
echo $html_header;
// Cuerpo de la pagina
if ($_POST["Modificacion_Tipo_Debito_Guardar"]) {
    $id = $_POST['Modificacion_IdTipoDeb'];
    $nombredeb = $_POST['Modificacion_TipoDebito'];
    if (!$id) {
        Error("Debe marcar el tipo de débito para modificarlo");
    }
    else {
        if (!$error) {
            $sql = "UPDATE bancos.tipo_débito SET ";
            $sql .="TipoDébito='$nombredeb' ";
            $sql .="Where IdTipoDéb=$id";
            $result = $db->query($sql) or die($db->ErrorMsg());
            Aviso("Los datos se Modificaron correctamente");
        }
    }
}
if ($_POST["Nuevo_Tipo_Debito"]) {
    $nombre = $_POST['Nombre_Tipo_Debito'];
    if ($nombre == "") {
        Error("Falta ingresar el Nombre del Tipo de Débito");
    }
    $sql = "SELECT * FROM bancos.tipo_débito WHERE TipoDébito LIKE '$nombre'";
    $result = $db->query($sql) or die($db->ErrorMsg());
    if ($result->RecordCount() > 0) {
        Error("Ya existe un Tipo de Débito con el Nombre '$nombre'!");
    }
    if (!$error) {
        $sql = "INSERT INTO bancos.tipo_débito ";
        $sql .= "(TipoDébito) ";
        $sql .= "VALUES ('$nombre')";
        $result = $db->query($sql) or die($db->ErrorMsg());
        Aviso("Los datos se ingresaron correctamente");
    }
}
echo "<form action=bancos_mant_deb.php method=post>\n";
$sql = "select * from bancos.tipo_débito order by idtipodéb";
$result = $db->query($sql) or die($db->ErrorMsg());
echo "<table align=center width=70%><tr><td>\n";
echo "<table align=center cellpadding=2 cellspacing=2 class='bordes' bordercolor=$bgcolor2>";
echo "<tr><td id=mo colspan=3 align=center>Débitos</td></tr>";
echo "<tr id=ma><td>Modificar</td><td align=center>ID</td>";
echo "<td>Nombre</td></tr>\n";
while ($fila = $result->fetchrow()) {
	echo "<tr bgcolor=$bgcolor_out>";
	echo "<td align=center><input onClick='javascript:document.forms[2].elements.Modificacion_TipoDebito.value=id_".$fila['idtipodéb'].".value;document.forms[2].elements.Modificacion_IdTipoDeb.value=".$fila['idtipodéb'].";' type=radio name=Modificar_TipoDebito value=''></td>";
	echo "<input type=hidden name=id_".$fila['idtipodéb']." value='".$fila['tipodébito']."'>";
	echo "<td align=center>".$fila['idtipodéb']."</td>";
	echo "<td>".$fila['tipodébito']."</td></tr>\n";
}
echo "</form>";
echo "</table></td><td align=center valign=top>";
echo "<form action=bancos_mant_deb.php method=post>\n";
echo "<table width=90% align=center cellpadding=2 cellspacing=0 class='bordes'>\n";
echo "<tr><td id=mo align=center>Agregar nuevo tipo de Débito</td></tr>";
echo "<tr ><td align=center>";
echo "<table align=center cellpadding=0 cellspacing=3 bgcolor=$bgcolor_out>\n";
echo "<tr><td>\n";
echo "<b>Nombre</b>\n";
echo "</td><td>\n";
echo "<input type=text name=Nombre_Tipo_Debito size=25 maxlength=50>";
echo "</td></tr><tr><td align=center colspan=2>\n";
echo "<input type=submit name=Nuevo_Tipo_Debito value='Nuevo Tipo Débito'>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "</td></tr></table>\n";
echo "</form>\n";
// Formulario de modificacion tipo Débito
echo "<br>\n";
echo "<form action=bancos_mant_deb.php method=post>\n";
echo "<input type=hidden name=Modificacion_IdTipoDeb value=''>";
echo "<table width=90% align=center cellpadding=2 cellspacing=0 class='bordes'>\n";
echo "<tr><td id=mo align=center>Modificar el Tipo de Débito</td></tr>\n";
echo "<tr><td align=center>\n";
echo "<table align=center cellpadding=0 cellspacing=3 bgcolor=$bgcolor_out>\n";
echo "<tr><td>\n";
echo "<b>Nombre</b>\n";
echo "</td><td>\n";
echo "<input type=text name=Modificacion_TipoDebito size=25 maxlength=50 value=''><br>\n";
echo "</td></tr><tr><td align=center colspan=2>\n";
echo "<input type=submit name=Modificacion_Tipo_Debito_Guardar value='Guardar'>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</td></tr>\n";
echo "</table>";
echo "</table>\n";
echo "</form>";
?>