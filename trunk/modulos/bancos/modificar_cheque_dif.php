<?

 /*
$Author: mari $
MODIFICADO POR:
$Author: mari $
$Revision: 1.4 $
$Date: 2007/02/28 13:48:14 $
*/

require_once("../../config.php");
require_once("../general/func_seleccionar_cliente.php");

if ($_POST['Volver'])
{
 $link = encode_link("ver_chequesdif_pend.php",array());
 header("location: $link");

}

echo $html_header;

$id_chequedif = $parametros['id_chequedif'] or $id_chequedif = $_POST['id_chequedif'];

$db->StartTrans;
$fecha=date("Y-m-d H:m:s");
$usuario=$_ses_user['id'];

if ($_POST['cambio_entidad']=="si_cambio") {
	$id_cliente=$_POST['id_cliente'];
    actualizar_clientes_mas_usuados($id_cliente,$usuario,$fecha);
}//de que se cambio la entidad

if ($_POST['Modificar'])
{$nro_cheque=$_POST['nro_cheque'];
 $banco=$_POST['banco'];
 $fecha_vencimiento=Fecha_db($_POST['fecha_vencimiento']);
 $comentario=$_POST['comentario'];
 $cliente=$_POST['id_cliente'];
 $monto=$_POST['monto'];
 $pertenece = $_POST['pertenece'];
 $ubicacion = $_POST['ubicacion'];
 if ($cliente!="")
  $cliente = "id_entidad=$cliente, ";
 
 $sql="update cheques_diferidos set nro_cheque='$nro_cheque', id_banco=$banco, fecha_vencimiento='$fecha_vencimiento', comentario='$comentario', $cliente monto=$monto, id_empresa_cheque=$pertenece, ubicacion='$ubicacion' where id_chequedif=$id_chequedif";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 echo "<br><center><font color=blue size=4><b>Se Modifico el cheque $nro_cheque con exito</b></font></center><br>";
}
$db->CompleteTrans();
echo "<script language='javascript' src='../../lib/popcalendar.js'> </script>\n";



$sql = "select cheques_diferidos.* from cheques_diferidos where id_chequedif=$id_chequedif";
$result_cheque=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);


?>
<script>
var wcliente=0;
function cargar_cliente() {
 document.all.id_cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].value;
 document.all.cliente.value=wcliente.document.all.select_cliente[wcliente.document.all.select_cliente.selectedIndex].text;
  //indica que se debe actualizar los clientes mas usuados
 document.all.cambio_entidad.value="si_cambio";

}
function control_datos()
{
 if ((document.all.nro_cheque.value=="") ||
     (document.all.banco.value=="") ||
     (document.all.comentario.value=="") ||
     (document.all.fecha_vencimiento.value=="") ||
     (document.all.monto.value=="") ||
     (document.all.pertenece.value=="") 
     )
     {
     alert("Los campos son todos obligatorio");
     return false;
     }
 else
    {document.all.monto.value=document.all.monto.value.replace(',','.');
     return true;
    }
     
}
</script>
<br>
<form name="form_ingreso" action="modificar_cheque_dif.php" method="POST">
<input type="hidden" name="id_chequedif" value="<?=$id_chequedif;?>">
<font size="4"><b>Modificar Cheque Diferido</b></font>
<hr>
<table align="center" width="100%" cellspacing="0">
<tr>
<td><b>Nro de Cheque</td>
<td><input type="text" name="nro_cheque" value="<?=$result_cheque->fields['nro_cheque']?>" size="30"></td>
<td><b>Fecha Vencimiento</td>
<td><input type="text" name="fecha_vencimiento" value="<?=Fecha($result_cheque->fields['fecha_vencimiento'])?>" readonly size="30"><?=link_calendario("fecha_vencimiento");?></td>
</tr>
<tr>
<td><b>Banco</td>
<td>
<select name="banco" style="cursor:hand">
<option value="">Elija Banco</option>
<?
$sql="select id_banco,nombre from bancos_cheques_dif order by nombre";
$result_banco=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
while(!$result_banco->EOF)
{
?>
<option value="<?=$result_banco->fields['id_banco']?>" <?echo ($result_banco->fields['id_banco']==$result_cheque->fields['id_banco'])?"selected":"";?>><?=$result_banco->fields['nombre']?></option>
<?
$result_banco->MoveNext();
}
?>
</select>
</td>
<td><b>Monto</td>
<td><input type="text" name="monto" value="<?=$result_cheque->fields['monto']?>" size="30"></td>
</tr>
<tr>
<td><b>
 <input type="button" name="clientes" value="Elegir cliente"  title="Permite elegir cliente " 
   onclick="if (wcliente==0 || wcliente.closed)
	        wcliente=window.open('<?=encode_link('../general/seleccionar_clientes.php',array('onclickaceptar'=>"window.opener.cargar_cliente();window.close()",'onclicksalir'=>'window.close()'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1');
            else
	        if (!wcliente.closed)     
	 	        wcliente.focus();"
>
</td>
<?
if ($result_cheque->fields['id_entidad']!="") 
{$sql="select nombre from entidad where id_entidad=".$result_cheque->fields['id_entidad'];
 $result_entidad=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 $nombre = $result_entidad->fields['nombre'];
}
else
 $nombre = "";
?>
<td colspan="3">
   
    <input type="text" name="cliente" value="<?=$nombre?>" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" readonly>
    <input type="hidden" name="id_cliente" value="<?=$result_cheque->fields['id_entidad']?>">
    <input name="cambio_entidad" type="hidden" value="no_cambio">
</td>
</tr>
<tr>
<td><b>Pertenece a:</td>
<td colspan="3">
<select name="pertenece">
<?
$sql="select nombre,id_empresa_cheque from empresas_cheques order by id_empresa_cheque";
$result_empresas = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
while (!$result_empresas->EOF)
{
?>
<option value="<?=$result_empresas->fields['id_empresa_cheque'];?>" <?echo ($result_empresas->fields['id_empresa_cheque']==$result_cheque->fields['id_empresa_cheque'])?"selected":"";?>><?=$result_empresas->fields['nombre'];?></option>
<?
$result_empresas->MoveNext();
}
?>
</select>
</td>
</tr>
<tr>
<td colspan="2">
<table>
<tr>
<td align="right" valign="top"><b>Comentario</td>
<td align="left" valign="top"><textarea name="comentario" cols="50" rows="3"><?=$result_cheque->fields['comentario']?></textarea>
</tr>
</table>
</td>
<td colspan="2">
<table>
<tr>
<td align="right" valign="top"><b>Ubicación</td>
<td align="left" valign="top"><textarea name="ubicacion" cols="50" rows="3"><?=$result_cheque->fields['ubicacion']?></textarea>
</tr>
</table>
</td>
</tr>
</table>
<center>
<input type="submit" name="Modificar" value="Modificar" style="cursor:hand" onclick="return control_datos();">&nbsp;&nbsp;&nbsp;
<input type="submit" name="Volver" value="Volver" style="cursor:hand">
</center>
</form>
<?
echo $html_footer;
?>