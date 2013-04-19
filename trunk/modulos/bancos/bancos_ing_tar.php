<?
/*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2005/06/24 20:28:16 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");
echo $html_header;
// Cuerpo de la pagina
if ($_POST["Ingreso_Debito_Guardar"]) {
    $banco = $_POST['Ingreso_Tarjeta_Banco'];
    $tipo = $_POST['Ingreso_Tarjeta_Nombre'];
    $fecha = $_POST['Ingreso_Tarjeta_Fecha'];
    $importe = $_POST['Ingreso_Tarjeta_Importe'];
    list($d,$m,$a) = explode("/",$fecha);
    if (FechaOk($fecha)) {
        $fecha = "$a-$m-$d";
    }
    else {
        Error("La fecha de depósito es inválida");
    }
    if ($tipo == "") {
        Error("Falta ingresar la Tarjeta");
    }
    if ($importe == "") {
        Error("Falta ingresar el Importe");
    }
    elseif (!es_numero($importe)) {
        Error("El Importe ingresado no es válido");
    }

    if (!$error) {
        $sql = "INSERT INTO bancos.tarjetas ";
        $sql .= "(IdBanco, FechaDepTar, IdTipoTar, ImporteDepTar, FechaCrédTar) ";
        $sql .= "VALUES ($banco,'$fecha',$tipo,$importe,NULL)";
        $result = $db->query($sql) or die($db->ErrorMsg());
        Aviso("Los datos se ingresaron correctamente");
    }
}
$Fecha_Hoy=date("Y-m-d",mktime());
$Banco_Default=4;
echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
echo "<form action=bancos_ing_tar.php method=post>\n";
echo "<table align=center cellpadding=2 cellspacing=0 border=1 bordercolor='$bgcolor3'>\n";
echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Tarjetas</td></tr>";
echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0 bgcolor=$bgcolor_out>";
echo "<tr><td align=right><b>Banco</b></td>";
echo "<td align=left>";
echo "<select name=Ingreso_Tarjeta_Banco>\n";
$sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
$result = $db->query($sql) or die($db->ErrorMsg());
while ($fila = $result->fetchrow()) {
	echo "<option value=".$fila['idbanco'];
	if ($fila['idbanco'] == $Banco_Default)
	echo " selected";
	echo ">".$fila['nombrebanco']."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td align=right><b>Tarjeta</b></td>";
echo "<td align=left>";
echo "<select name=Ingreso_Tarjeta_Nombre>\n";
echo "<option value='' selected></option>\n";
$sql = "SELECT * FROM bancos.tipo_tarjeta";
$result = $db->query($sql) or die($db->ErrorMsg());
while ($fila = $result->fetchrow()) {
	echo "<option value=".$fila['idtipotar'].">".$fila['tipotarjeta']."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td align=right><b>Fecha Depósito</b>\n";
echo "</td><td>";
echo "<input type=text name=Ingreso_Tarjeta_Fecha size=10 title='Ingrese la fecha de depósito'>";
echo link_calendario("Ingreso_Tarjeta_Fecha");
echo "</td></tr>\n";
echo "<tr><td align=right><b>Importe</b>\n";
echo "</td><td>";
echo "<input type=text name=Ingreso_Tarjeta_Importe size=22 maxlength=50>&nbsp;";
echo "</td></tr>\n";
echo "<tr><td align=center colspan=2>\n";
echo "<input type=submit name=Ingreso_Debito_Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
echo "</td></tr>\n";
echo "</table>";
echo "</td></tr>\n";
echo "</table>\n";
?>