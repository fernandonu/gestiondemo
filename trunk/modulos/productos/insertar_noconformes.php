<?
require_once("../../config.php");

switch($_POST['boton'])
{case "Insertar":{$fecha_emision=date("d/m/Y"); $sql="insert into noconformes(id_proveedor,fecha_evento,usuario,fecha_emision,descripcion_inconformidad,dispocision,id_producto)values(".$_POST['proveedor'].",'".$_POST['fecha_evento']."','".$_POST['usuario']."','".$fecha_emision."','".$_POST['descripcion_inconformidad']."',".$_POST['disposicion'].",".$_POST['descripcion'].");";
                  $resultado=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
                  header("location: noconformes.php");
                  break;}
 case "Cancelar":{header("location: noconformes.php");break;}
 default:{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<SCRIPT language='JavaScript' src="../lib/funciones.js">
<SCRIPT>
cargar_calendario();
</script>
</head>
<body bgcolor="#E0E0E0">
<form name="form1" action="insertar_noconformes.php" method="POST">
<table width="90%"  border="0" align="left">
<?
$sql="select id_proveedor,razon_social from proveedor";
$resultado=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
//nuevo numero
$sql="select max(id_noconforme) from noconformes";
$resultado_nuevo=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);

if ($resultado_nuevo->fields['max']=="")
 $id=0;
else 
 $id=$resultado_nuevo->fields['max'] + 1;
?>
  <tr>
    <td width="40%"><strong>Proveedor</strong></td>
    <td><strong>ID:<INPUT type="text" name="id" value="<? echo $id; ?>" size="3" style="border-style:none;background-color:#E0E0E0;font-weight:bold" readonly></strong></td> 
  </tr>
  <tr>
    <td><select name="proveedor" style="width: 70%;">
<?
while (!$resultado->EOF)
{
?>
<option value="<? echo $resultado->fields['id_proveedor']; ?>" <? if ($_POST['proveedor']==$resultado->fields['id_proveedor']) echo "selected"; ?>><? echo $resultado->fields['razon_social']; ?></option>
<?
$resultado->MoveNext();
}
?>
    </select>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="90%"  border="0">
  <tr>
    <td width="10%"><strong>Producto</strong></td>
    <td width="8%">&nbsp;</td>
    <td width="13%">&nbsp;</td>
    <td width="69%">&nbsp;</td>
  </tr>
  <tr>
    <td><strong>Tipo:</strong></td>
<?
$sql="select codigo from tipos_prod order by codigo";
$resultado=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
?>

    <td><select name="producto" onchange="document.all.form1.submit();">
<?
while (!$resultado->EOF)
{
?>
<option <? if ($_POST['producto']==$resultado->fields['codigo']) echo "selected"; ?>><? echo $resultado->fields['codigo']; ?></option>
<?
$resultado->MoveNext();
}

?>
    </select></td>
    <td><strong>Descripcion:</strong></td>
<?
if (isset($_POST['producto']))
 $campo=$_POST['producto'];
else
 $campo='placa madre';

$sql="select desc_gral,id_producto from productos where tipo='$campo'";
$resultado_producto=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
?>
    <td><select name="descripcion" style="width: 50%;">
<?
while (!$resultado_producto->EOF)
{
?>
<option value="<? echo $resultado_producto->fields['id_producto']; ?>"><? echo $resultado_producto->fields['desc_gral']; ?></option>
<?
$resultado_producto->MoveNext();
}
?>
    </select></td>
    </tr>
  <tr>
    <td><strong>Fecha Evento</strong></td>
    <td><input type="text" name="fecha_evento" value="">&nbsp;<? cargar_calendario(); echo link_calendario("fecha_evento"); ?></td>
    <td><strong>Usuario</strong></td>
    <td><input type="text" name="usuario" value="<? echo $_ses_user['name']; ?>" readonly></td>
    </tr>
</table>
<br>
  <strong>Descripcion de Inconformidad</strong><br>
  <textarea name="descripcion_inconformidad" cols="70" rows="4"></textarea>
  <br><br>
  <strong>Dispocici&oacute;n</strong>
  <select name="disposicion">
  <option value=1>Rechazado</option>
  <option value=2>Usar como esta</option>
  <option value=3>Reclasificar</option>
  <option value=4>Retrabajar</option>
  </select>
<br>
<center>
<input type="submit" name="boton" value='Insertar'>
<input type="submit" name="boton" value='Cancelar'>
</center>
</form>
</body>
</html>
<?
 }//fin default
}//fin switch
?>