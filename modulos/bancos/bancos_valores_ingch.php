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
if ($_POST["Guardar"]){
    $fecha_ven = $_POST['Cheques_de_Terceros_Vencimiento'];
    $fecha_ing = date("Y-m-d",mktime());
    $banco = $_POST['Cheques_de_Terceros_Banco'];
    $numero = $_POST['Cheques_de_Terceros_Numero'];
    $importe = $_POST['Cheques_de_Terceros_Importe'];
    $librador = $_POST['Cheques_de_Terceros_Librador'];
    list($d,$m,$a) = explode("/",$fecha_ven);
    if (FechaOk($fecha_ven)) {
        $fecha_ven = "$a-$m-$d";
    }
    else {
        Error("La fecha de vencimiento ingresada es inválida");
    }
    if ($banco == "") {
        Error("Falta ingresar el nombre del Banco");
    }
    if ($numero == "") {
        Error("Falta ingresar el Número del Cheque");
    }
    if ($importe == "") {
        Error("Falta ingresar el Importe del Cheque");
    }
    if ($librador == "") {
        Error("Falta ingresar el Librador del Cheque");
    }
    $sql = "SELECT * FROM bancos.cheques_de_terceros WHERE NúmChe=$numero";
    $result = $db->query($sql) or die($db->ErrorMsg());
    if ($result->RecordCount() > 0) {
        Error("Ya existe un Cheque con el Número $numero!");
    }
    if (!$error) {
        $sql = "INSERT INTO bancos.cheques_de_terceros ";
        $sql .= "(FechaIng, FechaVto, Banco, NúmChe, Importe, Librador) ";
        $sql .= "VALUES ('$fecha_ing','$fecha_ven','$banco',$numero,$importe,'$librador')";
        $result = $db->query($sql) or die($db->ErrorMsg());
        Aviso("Los datos se ingresaron correctamente");
    }
}
$Fecha_Hoy=date("d/m/Y",mktime());
echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
echo "<form action=bancos_valores_ingch.php method=post>\n";
echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor='$bgcolor3'>\n";
echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Cheques de Terceros</td></tr>";
echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0 bgcolor=$bgcolor_out>";
echo "<tr><td align=right><b>Fecha Vencimiento</b></td>";
echo "<td align=left>";
echo "<input type=text size=10 name=Cheques_de_Terceros_Vencimiento value='$Fecha_Hoy' title='Ingrese la fecha de vencimiento del cheque'>";
echo link_calendario("Cheques_de_Terceros_Vencimiento");
echo "</td></tr>\n";
echo "<tr><td align=right><b>Banco</b>\n";
echo "</td><td>";
echo "<input type=text name=Cheques_de_Terceros_Banco size=22 maxlength=50 title='Ingrese el nombre del banco'>&nbsp;";
echo "</td></tr>\n";
echo "<tr><td align=right><b>Número</b>\n";
echo "</td><td>";
echo "<input type=text name=Cheques_de_Terceros_Numero size=22 maxlength=50 title='Ingrese el número del cheque'>&nbsp;";
echo "</td></tr>\n";
echo "<tr><td align=right><b>Importe</b>\n";
echo "</td><td>";
echo "<input type=text name=Cheques_de_Terceros_Importe size=22 maxlength=50 title='Ingrese el importe del cheque'>&nbsp;";
echo "</td></tr>\n";
echo "<tr><td align=right><b>Librador</b>\n";
echo "</td><td>";
echo "<input type=text name=Cheques_de_Terceros_Librador size=22 maxlength=50 title='Ingrese el nombre del librador del cheque'>&nbsp;";
echo "</td></tr>\n";
echo "<tr><td align=center colspan=2>\n";
echo "<input type=submit name=Guardar value='Guardar'>&nbsp;&nbsp;&nbsp;\n";
echo "</td></tr>\n";
echo "</table>";
echo "</td></tr>\n";
echo "</table>\n";
?>