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
if ($_POST["Actualizar"]) {
while (list($id,$estado) = each($_POST['Estado_Cheque'])) {
        $estado_old = $_POST['Estado_Cheque_Old'][$id];
        $num_cheque = substr($id,2);
        if ($estado != "") {
            if ($estado != $estado_old) {
                $sql = "UPDATE bancos.cheques_de_terceros ";
                $sql .= "SET Estado='$estado' ";
                $sql .= "WHERE NúmChe=$num_cheque ";
                $result = $db->query($sql) or die($db->ErrorMsg());
                $actualizado = 1;
            }
        }
        else {
            Error("Falta ingresar el Estado del cheque número $num_cheque");
            exit();
        }
    }
    if (!$error) {
        if ($actualizado) {
            Aviso("Los datos se actualizaron correctamente");
        }
        else {
            Aviso("No había ningún dato para actualizar");
        }
    }
}
echo "<form action=bancos_valores_chter.php method=post>\n";
//echo "<input type=hidden name=mode value=data>\n";

//Total
$sql = "SELECT sum(Importe) AS total FROM bancos.cheques_de_terceros";
$result = $db->query($sql) or die($db->ErrorMsg());
$res_tmp = $result->fetchrow();
$Total = formato_money($res_tmp['total']);

//Total Vencido
$sql = "SELECT sum(Importe) AS total FROM bancos.cheques_de_terceros WHERE Estado='Caja' AND FechaVto<='".date("Y-m-d",mktime())."'";
$result = $db->query($sql) or die($db->ErrorMsg());
$res_tmp = $result->fetchrow();
$Total_Vencido = formato_money($res_tmp['total']);

//Javascript
echo "<script language='JavaScript' type='text/JavaScript'>\n";
echo "<!--\n";
echo "function ConfirmaUpdate(Msg,Obj) {\n";
echo "if (Obj.value != \"\") {\n";
echo "if (confirm(Msg)) {\n";
echo "eval('document.forms[0].submit()');";
echo "}\n}\n";
echo "return true;\n";
echo "}\n-->\n</script>\n";

//Datos
echo "<table width='95%' align=center>";
echo "<tr><td colspan=3 align=left><b>Cheques a depositar hoy: \$ $Total_Vencido</b>";
echo "</td>\n";
echo "<td colspan=3 align=right><b>Valores recibidos: \$ $Total</b>";
echo "</td></tr>";
echo "<tr><td colspan=6 align=center>";
echo "<input type=submit name=Actualizar value='Actualizar'>&nbsp;&nbsp;&nbsp;";
//echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">";
echo "</td></tr>\n";
$sql = "SELECT * FROM bancos.cheques_de_terceros ";
$sql .= "WHERE bancos.cheques_de_terceros.Estado='Caja' ";
$sql .= "ORDER BY bancos.cheques_de_terceros.FechaVto";
$result = $db->query($sql) or die($db->ErrorMsg());
$SubTotal = 0;
echo "</table>";
echo "<table width='95%' align=center class='bordes'>";
echo "<tr ><td id=ma colspan=6 align=center>Cheques de Terceros</td></tr>";//bordercolor='#000000'
echo "<tr id=mo>";//bordercolor='#000000'
echo "<td align=center>Vencimiento</td>";
echo "<td align=center>Banco</td>";
echo "<td align=center>Número</td>";
echo "<td align=center>Importe</td>";
echo "<td align=center>Librador</td>";
echo "<td align=center>Estado</td>";
echo "</tr>\n";
while ($fila = $result->fetchrow()) {
	$SubTotal += $fila['importe'];
	echo "<tr bgcolor=$bgcolor_out>\n";// bordercolor='#000000'
	echo "<td align=center>".Fecha($fila['fechavto'])."</td>\n";
	echo "<td align=left>".$fila['banco']."</td>\n";
	echo "<td align=center>".$fila['númche']."</td>\n";
	echo "<td align=right>\$ ".formato_money($fila['importe'])."</td>\n";
	echo "<td align=left>".$fila['librador']."</td>\n";
	echo "<td align=center>";
	//		echo "<input type=text size=10 maxlength=50 name=Estado_Cheque[NC".$fila[IdReg]."] value='".$fila[Estado]."' onBlur=\"ConfirmaUpdate('¿Desea actualizar los datos del cheque número ".$fila[NúmChe]."?',this);\">";
	echo "<input type=text size=10 maxlength=50 name=Estado_Cheque[NC".$fila['númche']."] value='".$fila['estado']."'>";
	echo "<input type=hidden size=10 maxlength=50 name=Estado_Cheque_Old[NC".$fila['númche']."] value='".$fila['estado']."'>";
	echo "</td>\n";
	echo "</tr>\n";
}
echo "<tr><td colspan=6 align=center bgcolor='$bgcolor3'><b>Total en caja: \$ ".formato_money($SubTotal)."</b></td></tr>";
echo "</table></form>\n";
?>