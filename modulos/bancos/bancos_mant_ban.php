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
if ($_POST["Nuevo_Banco"]) {
    $nombre = $_POST['Nombre_Banco'];
    if ($nombre == "") {
        Error("Falta ingresar el Nombre del Banco");
    }
    $sql = "SELECT * FROM bancos.tipo_banco WHERE NombreBanco LIKE '$nombre'";
    $result = $db->query($sql) or die($db->ErrorMsg());
    if ($result->RecordCount() > 0) {
        Error("Ya existe un Banco con el Nombre '$nombre'!");
    }
    if (!$error) {
        $sql = "INSERT INTO bancos.tipo_banco ";
        $sql .= "(NombreBanco) ";
        $sql .= "VALUES ('$nombre')";
        $result = $db->query($sql) or die($db->ErrorMsg());
        Aviso("Los datos se ingresaron correctamente");
    }
}
if ($_POST["Modificacion_Banco_Guardar"]) {
    $id = $_POST['Modificacion_IdBanco'];
    $nombrebanco = $_POST['Modificacion_NombreBanco'];
    if (!$id) {
        Error("Debe marcar un banco para modificarlo");
    }
    else {
        if (!$error) {
            $sql = "UPDATE bancos.tipo_banco SET ";
            $sql .="NombreBanco='$nombrebanco' ";
            $sql .="Where IdBanco=$id";
            $result = $db->query($sql) or die($db->ErrorMsg());
            Aviso("Los datos se Modificaron correctamente");
        }
    }
}
// Mostrar tabla de bancos
$sql = "select * from bancos.tipo_banco order by idbanco";
$result = $db->query($sql) or die($db->ErrorMsg());
echo "<form action=bancos_mant_ban.php method=post>\n";
echo "</center><br>\n";
echo "<table align=center width=70%><tr><td>\n";
echo "<table align=center width=90% cellpadding=2 cellspacing=2 class='bordes' bordercolor=$bgcolor3>";
echo "<tr><td id=mo colspan=3 align=center>Bancos</td></tr>";
echo "<tr id=ma><td>Modificar</td><td align=center>ID</td>";
echo "<td>Nombre</td></tr>\n";
while ($fila = $result->fetchrow()) {
	echo "<tr bgcolor=$bgcolor_out>";
	echo "<td align=center><input onClick='javascript:document.forms[2].elements.Modificacion_NombreBanco.value=id_".$fila[idbanco].".value;document.forms[2].elements.Modificacion_IdBanco.value=".$fila[idbanco].";' type=radio name=Modificar_Banco_Id value='".$fila[idbanco]."'></td>";
	echo "<input type=hidden name=id_".$fila['idbanco']." value='".$fila['nombrebanco']."'>";
	echo "<td align=center>".$fila['idbanco']."</td>";
	echo "<td>".$fila['nombrebanco']."</td></tr>\n";
}
echo "</table>";
echo "</form>";
echo "</td><td align=center valign=top>";
echo "<form action=bancos_mant_ban.php method=post>\n";
echo "<table width=90% align=center cellpadding=2 cellspacing=0 class='bordes'>\n";
echo "<tr><td id=mo align=center>Agregar un Banco</td></tr>";
echo "<tr><td align=center >";
echo "<table align=center cellpadding=0 cellspacing=3 bgcolor=$bgcolor_out>\n";
echo "<tr><td>\n";
echo "<b>Nombre</b>\n";
echo "</td><td>\n";
echo "<input type=text name=Nombre_Banco size=25 maxlength=50><br>";
echo "</td></tr><tr><td align=center colspan=2>\n";
echo "<input type=submit name=Nuevo_Banco value='Nuevo Banco'>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "</td></tr></table>\n";
echo "</form>\n";
// Modificar Nombre del banco
echo "<br>\n";
echo "<form action=bancos_mant_ban.php method=post>\n";
echo "<input type=hidden name=Modificacion_IdBanco value=''>";
echo "<table width=90% align=center cellpadding=2 cellspacing=0 class='bordes'>\n";
echo "<tr><td id=mo align=center>Modificar el Banco</td></tr>\n";
echo "<tr><td align=center>\n";
echo "<table align=center cellpadding=0 cellspacing=3 bgcolor=$bgcolor_out>\n";
echo "<tr><td>\n";
echo "<b>Nombre</b>\n";
echo "</td><td>\n";
echo "<input type=text name=Modificacion_NombreBanco size=25 maxlength=50 value=''><br>\n";
echo "</td></tr><tr><td align=center colspan=2>\n";
echo "<input type=submit name=Modificacion_Banco_Guardar value='Guardar'>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</td></tr>\n";
echo "</table>";
echo "</td></tr>\n";
echo "</table>\n";
?>