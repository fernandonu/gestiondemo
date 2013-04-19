<?
/*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2005/06/24 20:28:16 $
*/
require_once("../../config.php");
echo $html_header;
echo "<form action='bancos_movi_chfecha.php' method=post>\n";
if (!$_POST["Mov_Cheques_entre_Fechas_Banco"]) {
	$Banco=4;  // Banco por defecto
	$Fecha_Desde = date("d/m/Y",(mktime() - (40 * 24 * 60 * 60)));
	$Fecha_Desde_db = date("Y-m-d",(mktime() - (40 * 24 * 60 * 60)));
	$Fecha_Hasta = date("d/m/Y",mktime());
	$Fecha_Hasta_db = date("Y-m-d",mktime());
}
else {
	$Banco=$_POST["Mov_Cheques_entre_Fechas_Banco"];
	list($d,$m,$a) = explode("/", $_POST["Mov_Cheques_entre_Fechas_Desde"]);
    $fecha=$d."-".$m."-".$a;
    if (FechaOk($fecha)) {
		$Fecha_Desde = "$d/$m/$a";
		$Fecha_Desde_db = "$a-$m-$d";
	}
	else {
		Error("Fecha de inicio inválida");
		$Fecha_Desde = date("d/m/Y",(mktime() - (40 * 24 * 60 * 60)));
		$Fecha_Desde_db = date("Y-m-d",(mktime() - (40 * 24 * 60 * 60)));
	}
	list($d,$m,$a) = explode("/", $_POST["Mov_Cheques_entre_Fechas_Hasta"]);
    $fecha=$d."-".$m."-".$a;
    if (FechaOk($fecha)) {
		$Fecha_Hasta = "$d/$m/$a";
		$Fecha_Hasta_db = "$a-$m-$d";
	}
	else {
		Error("Fecha de finalización inválida");
		$Fecha_Hasta = date("d/m/Y",mktime());
		$Fecha_Hasta_db = date("Y-m-d",mktime());
	}
}

echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";

//Datos
echo "<table align=center cellpadding=5 cellspacing=0 border=1 bordercolor=$bgcolor3>";
echo "<tr><td colspan=2 align=left><b>Banco</b>";
$sql = "SELECT * FROM bancos.tipo_banco order by nombrebanco";
$result = $db->Execute($sql) or die($db->ErrorMsg());
echo "<select name=Mov_Cheques_entre_Fechas_Banco OnChange=\"document.forms[0].submit();\">\n";
while ($fila = $result->FetchRow()) {
	echo "<option value=".$fila["idbanco"];
	if ($fila["idbanco"] == $Banco)
	echo " selected";
	echo ">".$fila["nombrebanco"]."</option>\n";
}
echo "</select></td>\n";
echo "<td colspan=3 align=right><b>Desde: </b>";
echo "<input type=text size=10 name=Mov_Cheques_entre_Fechas_Desde value='$Fecha_Desde' title='Ingrese la fecha de inicio y\nhaga click en Actualizar'>";
echo link_calendario("Mov_Cheques_entre_Fechas_Desde");
echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>Hasta: </b>\n";
echo "<input type=text size=10 name=Mov_Cheques_entre_Fechas_Hasta value='$Fecha_Hasta' title='Ingrese la fecha de finalización\ny haga click en Actualizar'>";
echo link_calendario("Mov_Cheques_entre_Fechas_Hasta");
echo "</td></tr>";
echo "<tr><td colspan=5 align=center>\n";
//echo "<input type=hidden name=mode value=forms>\n";
//echo "<input type=hidden name=cmd value=Mov_Cheques_entre_Fechas>\n";
echo "<input type=submit name=Form_Cheques_entre_Fechas value='Actualizar'>&nbsp;&nbsp;&nbsp;\n";
//echo "<input type=button name=Volver value='   Volver   ' OnClick=\"javascript:window.location='bancos.php?PHPSESSID=$PHPSESSID&mode=view';\">\n";
echo "</td></tr>\n";
$sql = "SELECT ";
$sql .= "bancos.cheques.FechaVtoCh,bancos.cheques.FechaPrev,";
$sql .= "bancos.cheques.NúmeroCh, bancos.cheques.ImporteCh,";
$sql .= "bancos.proveedores.Proveedor ";
$sql .= "FROM bancos.cheques ";
$sql .= "INNER JOIN bancos.proveedores ";
$sql .= "ON bancos.cheques.IdProv = bancos.proveedores.IdProv ";
$sql .= "WHERE bancos.cheques.FechaPrev Between '$Fecha_Desde_db' AND '$Fecha_Hasta_db' ";
$sql .= "AND bancos.cheques.FechaDébCh IS NULL ";
$sql .= "AND bancos.cheques.IdBanco=$Banco ";
$sql .= "ORDER BY bancos.cheques.FechaPrev";

$result = $db->Execute($sql) or die($db->ErrorMsg());
$SubTotal = 0;
echo "<tr bordercolor='#000000'><td id=mo colspan=5 align=center>Cheques entre Fechas</td></tr>";
echo "<tr bordercolor='#000000' id=ma>";
echo "<td align=center>Vencimiento</td>";
echo "<td align=center>A Debitar</td>";
echo "<td align=center>Número Cheque</td>";
echo "<td align=center>Importe</td>";
echo "<td align=center>Beneficiario</td>";
echo "</tr>\n";
while ($fila = $result->FetchRow()) {
	$SubTotal += $fila["importech"];
	echo "<tr bordercolor='#000000'>\n";
	echo "<td align=center>".Fecha($fila["fechavtoch"])."</td>\n";
	echo "<td align=center>".Fecha($fila["fechaprev"])."</td>\n";
	echo "<td align=center>".$fila['númeroch']."</td>\n";
	echo "<td align=right>\$ ".formato_money($fila['importech'])."</td>\n";
	echo "<td align=left>".($fila['proveedor'])."</td>\n";
	echo "</tr>\n";
}
echo "<tr><td colspan=5 align=center><b>Subtotal Pendiente: \$ ".formato_money($SubTotal)."</b></td></tr>";
echo "</table></form>\n";
?>