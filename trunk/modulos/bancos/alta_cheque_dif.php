<?

 /*
Author: diegoinga 

MODIFICADO POR:
$Author: mari $
$Revision: 1.5 $
$Date: 2007/02/28 13:48:04 $
*/

require_once("../../config.php");
require_once("../general/func_seleccionar_cliente.php");

echo $html_header;

$db->StartTrans();
$fecha=date("Y-m-d H:m:s");
$usuario=$_ses_user['id'];

if ($_POST['cambio_entidad']=="si_cambio") {
   $id_cliente=$_POST['id_cliente'];
   actualizar_clientes_mas_usuados($id_cliente,$usuario,$fecha);
}//de que se cambio la entidad

if ($_POST['aceptar'])
{$nro_cheque=$_POST['nro_cheque'];
 $banco=$_POST['banco'];
 $fecha_vencimiento=Fecha_db($_POST['fecha_vencimiento']);
 $comentario=$_POST['comentario'];
 $cliente=$_POST['id_cliente'];
 $monto=$_POST['monto'];
 $pertenece = $_POST['pertenece'];
 if ($_POST['ubicacion'])
     $ubicacion = $_POST['ubicacion'];
 $sql="insert into cheques_diferidos(nro_cheque,id_banco,fecha_vencimiento,comentario,id_entidad,monto,fecha_ingreso,id_empresa_cheque,activo,ubicacion) values('$nro_cheque',$banco,'$fecha_vencimiento','$comentario',$cliente,$monto,'".date("Y-m-d")."',$pertenece,1,'$ubicacion');";
 $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
 echo "<br><center><font color=blue size=4><b>Se inserto el cheque $nro_cheque con exito</b></font></center><br>";
}
$db->CompleteTrans();
echo "<script language='javascript' src='../../lib/popcalendar.js'> </script>\n";
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
     (document.all.id_cliente.value=="") ||
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
<form name="form_ingreso" action="alta_cheque_dif.php" method="POST">
<font size="4"><b>Ingresos de Cheques Diferidos</b></font>
<hr>
<table align="center" width="100%" cellspacing="0">
<tr>
<td><b>Nro de Cheque</td>
<td><input type="text" name="nro_cheque" value="" size="30"></td>
<td><b>Fecha Vencimiento</td>
<td><input type="text" name="fecha_vencimiento" value="" readonly size="30"><?=link_calendario("fecha_vencimiento");?></td>
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
<option value="<?=$result_banco->fields['id_banco']?>"><?=$result_banco->fields['nombre']?></option>
<?
$result_banco->MoveNext();
}
?>
</select>
</td>
<td><b>Monto</td>
<td><input type="text" name="monto" value="" size="30"></td>
</tr>
<tr>
<td><b>Cliente</td>
<td colspan="3">
    <input type="button" name="clientes" value="Elegir cliente"  title="Permite elegir cliente " 
      onclick="if (wcliente==0 || wcliente.closed)
	           wcliente=window.open('<?=encode_link('../general/seleccionar_clientes.php',array('onclickaceptar'=>"window.opener.cargar_cliente();window.close()",'onclicksalir'=>'window.close()'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1');
               else
	           if (!wcliente.closed)     
	 	       wcliente.focus();">

    <input type="text" name="cliente" value="" size="50" style="border-style:none;background-color:'transparent';color:'blue'; font-weight: bold;" readonly>
    <input type="hidden" name="id_cliente" value="<?=$id_cliente?>">
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
<option value="<?=$result_empresas->fields['id_empresa_cheque'];?>"><?=$result_empresas->fields['nombre'];?></option>
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
<td align="left" valign="top"><textarea name="comentario" cols="50" rows="3"></textarea>
</tr>
</table>
</td>
<td colspan="2">
<table>
<tr>
<td align="right" valign="top"><b>Ubicación</td>
<td align="left" valign="top"><textarea name="ubicacion" cols="50" rows="3"></textarea>
</tr>
</table>
</td>
</tr>
</table>
<center>
<input type="submit" name="aceptar" value="Aceptar" style="cursor:hand" onclick="return control_datos();">


</center>
</form>
<?
echo $html_footer;
?>