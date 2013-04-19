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
if ($_POST["Nuevo_Tarjeta"]) {
    $nombre = $_POST['Nombre_Tipo_Tarjeta'];
    if ($nombre == "") {
        Error("Falta ingresar el Nombre de la Tarjeta");
    }
    $sql = "SELECT * FROM bancos.tipo_tarjeta WHERE TipoTarjeta LIKE '$nombre'";
    $result = $db->query($sql) or die($db->ErrorMsg());
    if ($result->RecordCount() > 0) {
        Error("Ya existe una Tarjeta con el Nombre '$nombre'!");
    }
    if (!$error) {
        $sql = "INSERT INTO bancos.tipo_tarjeta ";
        $sql .= "(TipoTarjeta) ";
        $sql .= "VALUES ('$nombre')";
        $result = $db->query($sql) or die($db->ErrorMsg());
        Aviso("Los datos se ingresaron correctamente");
    }
}
if ($_POST["Modificacion_Tarjeta_Guardar"]) {
    $id = $_POST['Modificacion_IdTipoTar'];
    $nombretar = $_POST['Modificacion_TipoTarjeta'];
    if (!$id) {
        Error("Debe marcar el tipo de tarjeta para modificarlo");
    }
    else {
        if (!$error) {
            $sql = "UPDATE bancos.tipo_tarjeta SET ";
            $sql .="TipoTarjeta='$nombretar' ";
            $sql .="Where IdTipoTar=$id";
            $result = $db->query($sql) or die($db->ErrorMsg());
            Aviso("Los datos se Modificaron correctamente");
        }
    }
}

echo "<form action=bancos_mant_tar.php method=post>\n";
$sql = "select * from bancos.tipo_tarjeta order by idtipotar";
$result = $db->query($sql) or die($db->ErrorMsg());
echo "<table align=center width=70%><tr><td>\n";
echo "<table align=center cellpadding=2 cellspacing=2 class='bordes' bordercolor=$bgcolor3>";
echo "<tr><td id=mo colspan=3 align=center>Tipo Tarjeta</td></tr>";
echo "<tr id=ma><td>&nbsp;</td><td align=center>ID</td>";
echo "<td>Nombre</td></tr>\n";
while ($fila = $result->fetchrow()) {
	echo "<tr bgcolor=$bgcolor_out>";
	echo "<td align=center><input onClick='javascript:document.forms[2].elements.Modificacion_TipoTarjeta.value=id_".$fila['idtipotar'].".value;document.forms[2].elements.Modificacion_IdTipoTar.value=".$fila['idtipotar'].";' type=radio name=Modificar_TipoTarjeta value=''></td>";
	echo "<input type=hidden name=id_".$fila['idtipotar']." value='".$fila['tipotarjeta']."'>";
	echo "<td align=center>".$fila['idtipotar']."</td>";
	echo "<td>".$fila['tipotarjeta']."</td></tr>\n";
}
echo "</form>";
echo "</table></td><td align=center valign=top>";
echo "<form action=bancos_mant_tar.php method=post>\n";
echo "<table width=90% align=center cellpadding=2 cellspacing=0 class='bordes' bordercolor='$bgcolor3'>\n";
echo "<tr><td id=mo align=center>Agregar nuevo tipo de Tarjeta</td></tr>";
echo "<tr><td align=center>";
echo "<table width ='100%' align=center cellpadding=0 cellspacing=5 bgcolor=$bgcolor_out>\n";
echo "<tr><td>\n";
echo "<b>Nombre</b>\n";
echo "</td><td>\n";
echo "<input type=text name=Nombre_Tipo_Tarjeta size=25 maxlength=50>";
echo "</td></tr><tr><td align=center colspan=2>\n";
echo "<input type=submit name=Nuevo_Tipo_Tarjeta value='Nuevo Tipo Tarjeta'>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "</td></tr></table>\n";
echo "</form>\n";
// Formulario de modificacion tipo Débito
echo "<form action=bancos_mant_tar.php method=post>\n";
echo "<input type=hidden name=Modificacion_IdTipoTar value=''>";
echo "<table width=90% align=center cellpadding=2 cellspacing=0 class='bordes' bordercolor='$bgcolor2'>\n";
echo "<tr><td id=mo align=center>Modificar el Tipo de Tarjeta</td></tr>\n";
echo "<tr><td align=center>\n";
echo "<table width ='100%' align=center cellpadding=0 cellspacing=5 bgcolor=$bgcolor_out>\n";
echo "<tr><td>\n";
echo "<b>Nombre</b>\n";
echo "</td><td>\n";
echo "<input type=text name=Modificacion_TipoTarjeta size=25 maxlength=50 value=''><br>\n";
echo "</td></tr><tr><td align=center colspan=2>\n";
echo "<input type=submit name=Modificacion_Tarjeta_Guardar value='Guardar'>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</td></tr>\n";
echo "</table>";
echo "</table>\n";
echo "</form>";
?>