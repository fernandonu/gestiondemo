<?
require_once("../../config.php");

switch($_POST['boton'])
{case "Editar":{$fecha_emision=date("d/m/Y"); $sql="update noconformes set id_proveedor=".$_POST['proveedor'].", fecha_evento='".$_POST['fecha_evento']."', usuario='".$_POST['usuario']."', fecha_emision='$fecha_emision', descripcion_inconformidad='".$_POST['descripcion_inconformidad']."', dispocision='".$_POST['disposicion']."', id_producto=".$_POST['descripcion']." where id_noconforme=".$_POST['id'];
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
<form name="form1" action="descripcion_noconformes.php" method="POST">
<table width="90%"  border="0" align="left">
<?
$id=$parametros['id'] or $id=$_POST['id'];
$sql="select tipo,razon_social,proveedor.id_proveedor,productos.id_producto,marca,modelo,fecha_evento,usuario,descripcion_inconformidad,noconformes.dispocision from ((noconformes join productos on noconformes.id_producto=productos.id_producto) join proveedor on proveedor.id_proveedor=noconformes.id_proveedor) where noconformes.id_noconforme=$id";
$resultado=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
?>
  <tr>
    <td width="40%"><strong>Proveedor</strong></td>
    <td><strong>ID:<INPUT type="text" name="id" value="<? echo $id; ?>" size="3" style="border-style:none;background-color:#E0E0E0;font-weight:bold"></strong></td> 
  </tr>
  <tr>
    <td><select name="proveedor" style="width: 70%;">
<?
$sql="select id_proveedor,razon_social from proveedor";
$resultado_proveedor=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
while (!$resultado_proveedor->EOF)
{
?>
<option value="<? echo $resultado_proveedor->fields['id_proveedor']; ?>" <? if (($_POST['proveedor']==$resultado->fields['id_proveedor']) || ($resultado_proveedor->fields['id_proveedor']==$resultado->fields['id_proveedor'])) echo "selected"; ?>><? echo $resultado_proveedor->fields['razon_social']; ?></option>
<?
$resultado_proveedor->MoveNext();
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
$resultado_producto=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
?>

    <td><select name="producto" onchange="document.all.form1.submit();">
<?
while (!$resultado_producto->EOF)
{
?>
<option <? if (($_POST['producto']==$resultado_producto->fields['codigo']) || ($resultado_producto->fields['codigo']==$resultado->fields['tipo'])) echo "selected"; ?>><? echo $resultado_producto->fields['codigo']; ?></option>
<?
$resultado_producto->MoveNext();
}

?>
    </select></td>
    <td><strong>Descripcion:</strong></td>
<?
$campo=$resultado->fields['tipo'];

$sql="select desc_gral,id_producto from productos where tipo='$campo'";
$resultado_producto=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
?>
    <td><select name="descripcion" style="width: 50%;">
<?
while (!$resultado_producto->EOF)
{
?>
<option value="<? echo $resultado_producto->fields['id_producto']; ?>" <? if (($_POST['descripcion']==$resultado_producto->fields['id_producto']) || ($resultado_producto->fields['id_producto']==$resultado->fields['id_producto'])) echo "selected"; ?>><? echo $resultado_producto->fields['desc_gral']; ?></option>
<?
$resultado_producto->MoveNext();
}
?>
    </select></td>
    </tr>
  <tr>
    <td><strong>Fecha Evento</strong></td>
    <td><input type="text" name="fecha_evento" value="<? echo $resultado->fields['fecha_evento']; ?>">&nbsp;<? cargar_calendario(); echo link_calendario("fecha_evento"); ?></td>
    <td><strong>Usuario</strong></td>
    <td><input type="text" name="usuario" value="<? echo $_ses_user['name']; ?>" readonly></td>
    </tr>
</table>
<br>
  <strong>Descripcion de Inconformidad</strong><br>
  <textarea name="descripcion_inconformidad" cols="70" rows="4"><? echo $resultado->fields['descripcion_inconformidad']; ?></textarea>
  <br><br>
  <strong>Dispocici&oacute;n</strong>
  <select name="disposicion">
  <option value=1 <? if ($resultado->fields['dispocision']==1) echo "selected" ?>>Rechazado</option>
  <option value=2 <? if ($resultado->fields['dispocision']==2) echo "selected" ?>>Usar como esta</option>
  <option value=3 <? if ($resultado->fields['dispocision']==3) echo "selected" ?>>Reclasificar</option>
  <option value=4 <? if ($resultado->fields['dispocision']==4) echo "selected" ?>>Retrabajar</option>
  </select>
<br>
<center>
<input type="submit" name="boton" value='Editar'>
<input type="submit" name="boton" value='Cancelar'>
</center>
</form>
</body>
</html>
<?
 }//fin default
}//fin switch
?>