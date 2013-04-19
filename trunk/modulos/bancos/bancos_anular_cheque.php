<?
require_once("../../config.php");

function cuenta_bancos($nro_cuenta) {
	global $db;
	global $concepto_cuenta,$parametros,$download;
	$tipo_valor='base';
echo "
       <tr>
	    <td align=right><b>Concepto y Plan </b></td>

        <td align=left>";
//query para traer toda la tabla tipo_cuenta
$con="select * from general.tipo_cuenta order by concepto, plan";
$resul_con=$db->Execute($con) or die ($db->ErrorMsg()."<br>".$con);
$cant_resul_con=$resul_con->RecordCount();
echo "<select name='cuentas' disabled>
       <option value=-1> Seleccionar Concepto y Plan </option>";
      for ($j=0; $j<$cant_resul_con; $j++){
      $numero_cuenta=$resul_con->fields['numero_cuenta'];
      $cuenta=$resul_con->fields['concepto']."&nbsp;&nbsp;[ ".$resul_con->fields['plan']." ] ";
      echo "<option value='".$resul_con->fields['numero_cuenta']."'";
      if($nro_cuenta==$numero_cuenta)
	  echo " selected ";
	  echo"> $cuenta </option>";
	  $resul_con->MoveNext();}
echo "</select>";
//echo "<input type=hidden name=ncuen value='$mod_numero_cuenta'>";
echo "</td></tr>";

}
?>
<script>
function control_comentario()
{
 var co=eval("document.all.causa_anular");
 co=co.value;
 //alert(co);
 if(co=="")
 {
  alert("Debe ingresar un comentario por el cual se anula este cheque");
  return false;
 }
 return true;
}
</script>
<?

echo $html_header;
 $mod_numero = $parametros["num"];
 $id_banco_nuevo=$parametros['idbanco'];
 $cerrar=1;
if($_POST["guardar"])
{
	$db->StartTrans();
	$usuario=$_ses_user['name'];
	$comentarios=$_POST['Modificacion_Cheque_Comentarios'];
	$comen=$_POST['causa_anular'];
	$mod_numero=$_POST['Modificacion_Cheque_Numero'];
	$id_banco_nuevo=$_POST['Modificar'];
	$fecha_debito = $_POST['Modificacion_Cheque_Fecha_Debito'];
    $comentarios = $_POST['Modificacion_Cheque_Comentarios'];
    $idbanco = $_POST['idban'];
    $proveedor=$_POST['prov'];
    $fech_emis=$_POST['fec_emi'];
    $fech_venc=$_POST['fec_ven'];
    $impor=$_POST['importe'];
	$nro_cuenta=$_POST['ncuen'];
	$sql = "UPDATE bancos.cheques SET ";
    $sql .= "ImporteCh=0,";
    $sql .= "Comentarios='$comentarios'";
    $sql .= "WHERE NúmeroCh=$mod_numero AND idbanco=$id_banco_nuevo";
    $result = $db->execute($sql) or die($sql."<br>".$db->errormsg());
	$campos1="(usuario,causa_de_anulacion,idbanco,númeroch)";
    $query_insert="INSERT INTO bancos.anular_cheque $campos1 VALUES ".
	"('$usuario','$comen',$id_banco_nuevo,$mod_numero)";
	sql($query_insert) or fin_pagina();

	include_once("../contabilidad/funciones.php");
	//preparamos el parametro para anular la imputacion de este cheque
	$pago[]=array();
	$pago["tipo_pago"]="númeroch";
	$pago["id_pago"]=$mod_numero;
	$pago["id_banco"]=$idbanco;
	anular_imputacion($pago);

	$db->CompleteTrans();
	$fecha=fecha_db(date("d/m/Y",mktime()));
	$para="juanmanuel@coradir.com.ar,corapi@coradir.com.ar,noelia@pcpower.com.ar";
	$asunto="Notificación de cheque Anulado";
	$contenido="Se Anulo el cheque número $mod_numero ,el cual fue anulado con fecha $fecha, por el usuario $usuario .\n El motivo por el cual fue anulado es $comen .\n";
	$contenido.="Importe $impor.\n";
	$contenido.="Fecha de Emision: $fech_emis.\n";
	$contenido.="Fecha de Vencimiento: $fech_venc.\n";
	$contenido.="Nonbre proveedor: $proveedor.\n";
	$contenido.="Comentario: $comentarios.\n";
	enviar_mail($para,$asunto,$contenido,"","","");
	/*echo"$asunto <br>";
	echo"$contenido <br>";
	echo"$para <br>";*/
	//die();
	 $link=encode_link("bancos_movi_chpen.php",array('idbanco'=>$idbanco));
	?>
	<script>
	window.close();
	var lin=window.opener.location='<?=$link?>';
	</script>
	<?
	$cerrar=0;
}


	if (es_numero($mod_numero)) {
		$sql = "SELECT bancos.tipo_banco.NombreBanco,";
		$sql .= "bancos.cheques.IdBanco,";
		$sql .= "general.proveedor.razon_social,";
		$sql .= "bancos.cheques.FechaEmiCh,";
		$sql .= "bancos.cheques.FechaVtoCh,";
		$sql .= "bancos.cheques.FechaDébCh,";
		$sql .= "bancos.cheques.ImporteCh,";
		$sql .= "bancos.cheques.Comentarios, ";
	    $sql .= "bancos.cheques.numero_cuenta ";
		$sql .= "FROM (bancos.cheques ";
		$sql .= "INNER JOIN bancos.tipo_banco ";
		$sql .= "ON bancos.cheques.IdBanco = bancos.tipo_banco.IdBanco) ";
		$sql .= "INNER JOIN general.proveedor ";
		$sql .= "ON bancos.cheques.IdProv = general.proveedor.id_proveedor ";
		$sql .= "WHERE bancos.cheques.NúmeroCh=$mod_numero AND cheques.idbanco=$id_banco_nuevo";
        $sql .= " AND tipo_banco.activo=1";
        $result = $db->Execute($sql) or die($db->ErrorMsg()." - " . $sql);
		list($mod_banco,
		$mod_idbanco,
		$mod_proveedor,
		$mod_fecha_e,
		$mod_fecha_v,
		$mod_fecha_d,
		$mod_importe,
		$mod_comentarios,
		$mod_numero_cuenta) = $result->fetchrow();
		$mod_fecha_d = Fecha($mod_fecha_d);
		$mod_fecha_v = Fecha($mod_fecha_v);
		$mod_fecha_e = Fecha($mod_fecha_e);
		//$mod_importe = formato_money($mod_importe);

		//			if ($mod_fecha_d == "00/00/2000") {
		//				$mod_fecha_d = "";
		//			}
		echo "<script language='javascript' src='../../lib/popcalendar.js'></script>\n";
		?>
		<script src="../../lib/NumberFormat150.js"></script>
		<?
		echo "<form action=bancos_anular_cheque.php method=post>\n";
		if($cerrar==0)
		{
		echo "<table align=center cellpadding=5 cellspacing=0 class='bordes' >\n";//bordercolor='$bgcolor3'
		echo "<tr bordercolor='#000000'><td id=mo align=center>El Cheque Se Anulo Correctamente</td></tr>";
		echo "</table>\n";
		}
		echo "<input type=hidden name=Modificacion_Cheque_Numero value='$mod_numero'>";
		echo "<input type=hidden name=Modificar value='$id_banco_nuevo'>";
		echo "<input type=hidden name=importe value='$mod_importe'>";
		echo "<input type=hidden name=prov value='$mod_proveedor'>";
		echo "<input type=hidden name=idban value='$mod_idbanco'>";
        echo "<table align=center cellpadding=5 cellspacing=0 class='bordes' >\n";//bordercolor='$bgcolor3'
		echo "<tr bordercolor='#000000'><td id=mo align=center>Anular Cheque</td></tr>";
		echo "<tr bordercolor='#000000'><td align=center>";
		echo "<table cellspacing=5 border=0 bgcolor=$bgcolor_out>";//bordercolor='$bgcolor3'
		echo "<tr><td align=right><b>Banco</b></td>";
		//CAMBIOS
		//echo "<td align=left bordercolor=#000000>$mod_banco&nbsp;</td></tr>\n";
		 echo "<td align=left>";
        $sql = "SELECT * FROM bancos.tipo_banco WHERE activo=1 order by nombrebanco";
        $result = $db->execute($sql) or die($db->ErrorMsg());

         echo "<select name=Modificacion_Cheque_IdBanco disabled>\n";
        while ($fila = $result->fetchrow()) {
            echo "<option value=".$fila[idbanco];
            if ($fila[idbanco] == $mod_idbanco)
                echo " selected";
            echo ">".$fila[nombrebanco]."</option>\n";
        }
        echo "</select>\n";
       // echo "<input type=hidden name=nom_ban value='$mod_importe'>";
        echo "</td></tr>\n";


		echo "<tr><td align=right><b>Proveedor</b></td>";

		//echo "<td align=left bordercolor=#000000>$mod_proveedor&nbsp;</td></tr>\n";
		echo "<td align=left>";
		        echo "<select name=Modificacion_Cheque_IdProveedor disabled>\n";
        $sql = "SELECT id_proveedor, razon_social FROM general.proveedor ORDER BY razon_social";
        $result = $db->execute($sql) or die($db->ErrorMsg());
        while ($fila = $result->fetchrow()) {
            echo "<option value='".$fila[id_proveedor]."'";
            if ($fila[razon_social] == "$mod_proveedor") echo " selected";
            echo ">".$fila[razon_social]."</option>\n";
        }
        echo "</select></td></tr>\n";
		echo "<tr><td align=right><b>Fecha de Emisión</b></td>";
		//echo "<td align=left bordercolor=#000000>$mod_fecha_e&nbsp;</td>\n";
		echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Emision value='$mod_fecha_e' title='Ingrese la fecha de emisión del cheque' disabled>";
		echo link_calendario("Modificacion_Cheque_Fecha_Emision");
		echo "<input type=hidden name=fec_emi value='$mod_fecha_e'>";
        echo "</td>\n";

		echo "<tr>\n";
		echo "<tr><td align=right><b>Fecha de Vencimiento</b></td>";
		//echo "<td align=left bordercolor=#000000>$mod_fecha_v&nbsp;</td></tr>\n";
		 echo "<td align=left>";
        echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Vencimiento value='$mod_fecha_v' title='Ingrese la fecha de vencimiento del cheque' disabled>";
		echo link_calendario("Modificacion_Cheque_Fecha_Vencimiento");
		echo "<input type=hidden name=fec_ven value='$mod_fecha_v'>";
        echo "</td></tr>\n";

        echo "<tr><td align=right><b>Fecha Débito</b></td>";
		echo "<td align=left>";
		echo "<input type=text size=10 name=Modificacion_Cheque_Fecha_Debito value='$mod_fecha_d' title='Ingrese la fecha de débito del cheque' disabled>";
		echo link_calendario("Modificacion_Cheque_Fecha_Debito");
		echo "</td></tr>\n";

		echo "<tr><td align=right><b>Número</b>\n";
		//echo "</td><td align=left bordercolor=#000000>$mod_numero&nbsp;</td>\n";
		echo "</td><td align=left>";
		//guarda el número de cheque y el id del banco
        echo "<input type=hidden name=Modificacion_Cheque_Numero_Old value='$mod_numero' disabled>";
        echo "<input type=hidden name=Modificacion_id_banco_Old value='$mod_idbanco' disabled>";

        echo "<input type=text name=Modificacion_Cheque_Numero value='$mod_numero' size=10 maxlength=50 disabled>";
        echo "</td>";

		echo "</tr>\n";
		echo "<tr><td align=right><b>Importe</b>\n";
		echo "</td><td align=left>";
		echo "<input type=text name=Modificacion_Cheque_Importe value='0' size=10 maxlength=50 onchange='setear_montos_imputacion()' disabled>&nbsp;";
		echo "</td></tr>\n";
// concepto y plan cuando se modifica un debito
        cuenta_bancos($mod_numero_cuenta);
        if($cerrar==1)
		$mod_comentarios="$mod_comentarios-ANULADO";
///////////////////////////////////////////////
		echo "<tr><td align=right valign=top><b>Comentarios</b>\n";
		echo "</td><td align=left>";
		echo "<textarea name=Modificacion_Cheque_Comentarios cols=70 rows=3>$mod_comentarios</textarea>";
		//echo "<input type=hidden name=come value='$mod_comentarios'>";
		echo "</td></tr>\n";
		echo"<tr><td colspan=2><hr><br></td></tr>\n";
		echo "<tr><td align=center valign=top colspan=2><font color=Red size=3><b>Especifique el motivo por el cual se anula este cheque</b></font>\n";
		echo "</td></tr><tr><td colspan=2 align=center>";
		echo "<textarea name=causa_anular cols=70 rows=3>$comen</textarea>";
		echo "</td></tr>\n";

		echo "</table>\n";
		?>
		<table align="center">
		<tr>
		<td>
		<?
		if($cerrar==1)
		{
		?>
		<input type="submit" name="guardar" value="Guardar" onclick='return control_comentario();'>
		<?}?>
		<input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
		</td>
		</tr>
		<?
      exit();
	}
	else {
		Error("No hay ningún cheque seleccionado"."Numero".$mod_numero);
	}