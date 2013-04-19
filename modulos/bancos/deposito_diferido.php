<?
  /*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2005/06/24 20:28:16 $
*/
// Cabecera Configuracion requerida
require_once("../../config.php");

if ($_POST['Volver'])
{
 $link = encode_link("ver_chequesdif_pend.php",array());
 header("location: $link");

}


$id_chequedif = $parametros['id_chequedif'] or $id_chequedif = $_POST['id_chequedif'];


echo $html_header;
$inserto = 0;
// Cuerpo de la pagina
if ($_POST["Ingreso_Deposito_Guardar"]) {
    $banco = $_POST['Ingreso_Deposito_Banco'];
    $tipo = $_POST['Ingreso_Deposito_Tipo'];
    $fecha = $_POST['Ingreso_Deposito_Fecha'];
    $importe = $_POST['Ingreso_Deposito_Importe'];
    $comentario =$_POST['coment'];
    list($d,$m,$a) = explode("/",$fecha);
    if (FechaOk($fecha)) {
        $fecha = "$a-$m-$d";
    }
    else {
        Error("La fecha de depósito es inválida");
    }
    if ($tipo == "") {
        Error("Falta ingresar el Tipo de Depósito");
    }
    if ($importe == "") {
        Error("Falta ingresar el Importe del Depósito");
    }
    elseif (!es_numero($importe)) {
        Error("El Importe ingresado es inválido");
    }
    if (!$error) {
        
    	$db->StartTrans();
    	
    	$sql = "INSERT INTO bancos.depósitos ";
        $sql .= "(IdBanco, FechaDepósito, FechaCrédito, IdTipoDep, ImporteDep, comentario) ";
        $sql .= "VALUES ($banco,'$fecha',NULL,$tipo,$importe,'$comentario')";
        $result = $db->query($sql) or die($db->ErrorMsg()."<br>".$sql);
        
        $sql = "select max(iddepósito) from depósitos";
        $result = $db->query($sql) or die($db->ErrorMsg()."<br>".$sql);
        
        $sql = "update cheques_diferidos set iddepósito=".$result->fields['max']." where id_chequedif=$id_chequedif";
        $db->query($sql) or die($db->ErrorMsg()."<br>".$sql);
        
        $db->CompleteTrans();
        
        Aviso("Los datos se ingresaron correctamente");
        $inserto = 1;
    }
}
$Fecha_Hoy=date("Y-m-d",mktime());
$Banco_Default=4;
$deposito = $_POST['Ingreso_Deposito_Banco'] or $deposito = 2;

$sql = "select cheques_diferidos.* from cheques_diferidos where id_chequedif=$id_chequedif";
$result_cheque=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);

echo "<br><font size=4><b>Deposito Cheque Diferido</b></font>";
echo "<hr>";
echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
echo "<form action=deposito_diferido.php method=post>\n";
echo "<input type=hidden name=id_chequedif value=$id_chequedif>";
echo "<table align=center cellpadding=2 cellspacing=0 border=1 bordercolor='$bgcolor3'>\n";
echo "<tr bordercolor='#000000'><td id=mo align=center>Ingreso de Depósitos</td></tr>";
echo "<tr bordercolor='#000000'><td align=center><table cellspacing=5 border=0 bgcolor=$bgcolor_out>";
echo "<tr><td align=right><b>Banco</b></td>";
echo "<td align=left>";
echo "<select name=Ingreso_Deposito_Banco>\n";
$sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
$result = $db->query($sql) or die($db->ErrorMsg());
while ($fila = $result->fetchrow()) {
	echo "<option value=".$fila['idbanco'];
	if ($fila['idbanco'] == $Banco_Default)
	echo " selected";
	echo ">".$fila['nombrebanco']."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td align=right><b>Tipo de Depósito</b></td>";
echo "<td align=left>";
echo "<select name=Ingreso_Deposito_Tipo>\n";
echo "<option value='' selected></option>\n";
$sql = "SELECT * FROM bancos.tipo_depósito";
$result = $db->query($sql) or die($db->ErrorMsg());
while ($fila = $result->fetchrow()) {
	echo "<option value=".$fila['idtipodep']." ".(($fila['idtipodep']==$deposito)?"selected":"").">".$fila['tipodepósito']."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td align=right><b>Fecha Depósito</b></td>";
echo "<td align=left>";
echo "<input type=text size=10 name=Ingreso_Deposito_Fecha title='Ingrese la fecha del depósito'>";
echo link_calendario("Ingreso_Deposito_Fecha");
echo "</td></tr>\n";
echo "<tr><td align=right><b>Importe</b>\n";
echo "</td><td>";
echo "<input type=text name=Ingreso_Deposito_Importe size=22 maxlength=50 value=".$result_cheque->fields['monto'].">&nbsp;";
echo "</td></tr>\n";
echo "<tr><td align='right'><b>Comentarios</td>";
echo "<td><textarea name='coment' cols=25 wrap='FISICAL' >".$result_cheque->fields['comentario']."</textarea></td></tr>";
echo "<tr><td align=center colspan=2>\n";
echo "<input type=submit name=Ingreso_Deposito_Guardar value='Guardar' style='cursor:hand' ".(($inserto==1)?"disabled":"").">&nbsp;&nbsp;&nbsp;\n";
echo "<input type=submit name=Volver value=Volver style=cursor:hand>";
echo "</td></tr>\n";
echo "</table>";
echo "</td></tr>\n";
echo "</table>\n";
?>