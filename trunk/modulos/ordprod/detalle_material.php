<?php
require_once("../../config.php");

//print_r($_POST);
$id=$_POST['id'] or $id=$parametros['id'];
$id_entrega_estimada=$parametros['id_entrega_estimada'] or $id_entrega_estimada=$_POST['id_entrega_estimada'];
$link=encode_link("seguimiento_orden.php",array("id"=>$id,"id_entrega_estimada"=>$id_entrega_estimada));
switch($_POST['boton'])
{
case "Cancelar":{header("location: $link");break;}
case "Guardar":{require_once("guardar_detalle.php");break;}
case "Borrar":{require_once("guardar_detalle.php");break;}
default:
{?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Control de Material</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<script language="javascript">
function borrar(i)
{var objeto=eval("document.all.borrar_"+i);
 objeto.value='t';
 document.all.viene.value="borrar";
 return true;
}
</script>
</head>
<body bgcolor="#E0E0E0">
<form name="form1" method="post" action="detalle_material.php">
<input type="hidden" name="viene">
<input type="hidden" name="id" value="<? echo $id; ?>">
<input type="hidden" name="id_entrega_estimada" value="<? echo $id_entrega_estimada; ?>">
<br>
<?php
$sql="select id_entrega_estimada from entrega_estimada where id_licitacion=".$id;
$resultado=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
?>
<input type="hidden" name="id_entrega_estimada" value="<? echo $resultado->fields['id_entrega_estimada']; ?>">
<?
$sql="select producto,tiene,id_material_produccion from material_produccion where id_entrega_estimada=".$resultado->fields['id_entrega_estimada'];
$resultado=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
if ($resultado->RecordCount()>0) //verifico los productos cargados
{?>
 <table border=1 width="80%">
 <tr>
 <td colspan="3" align="center"><b>Productos Ingresados</b></td>
 </tr>
 <tr>
 <td><b>Descripcion del Producto</b></td>
 <td><b>Tiene</b></td>
 <td><b>Borrar</b>
 </tr>
<? $i=1;
 while (!$resultado->EOF)
 {
?>
  <tr>
  <td><b><? echo $resultado->fields['producto']; ?></b></td>
  <td><input type="checkbox" name="tiene_<? echo $i; ?>" value="t" <? if($resultado->fields['tiene']=="t") echo "checked"; ?>></td>
  <td><input type="hidden" name="borrar_<? echo $i; ?>" value="">
      <input type="submit" name="boton" value="Borrar" onclick="return borrar(<? echo $i; ?>);">
  </td>
  <input type="hidden" name="id_material_produccion_<? echo $i; ?>" value="<? echo $resultado->fields['id_material_produccion']; ?>">
  <input type="hidden" name="producto_<? echo $i; ?>" value="<? echo $resultado->fields['producto']; ?>">
  </tr>
<? $resultado->MoveNext();
   $i++;
 }
?>
</table>
<input type="hidden" name="cant_act" value="<? echo $i-1; ?>">
<?
}
?>
<hr>
<table width="90%"  border="0">
  <tr>
<?
$nro_filas=1;
if (!isset($_POST['filas']))
 $cant_filas=1;
else
 $cant_filas=$_POST['filas'];
?>
<input type="hidden" name="cant_filas" value="<? echo $cant_filas; ?>">
    <td width="20%"><b>Cantidad de Filas</b></td>
    <td width="80%"><select name="filas" onchange="form.submit();">
    <?
    while($nro_filas<=10)
    {
    ?>
    <option <? if ($cant_filas==$nro_filas) echo "selected"; ?>><? echo $nro_filas; ?></option>
    <? $nro_filas++;
    }
    ?>
	</select></td>
  </tr>
</table>
<table border="0" width="80%">
<tr>
<td><b>Descripcion del Producto</b></td>
<td><b>Tiene</b></td>
</tr>
<?
while($cant_filas>0)
{
?>
  <tr>
    <td width="60%"><input name="producto1_<? echo $cant_filas; ?>" type="text" size="60"></td>
    <td align="left"><input name="check_<? echo $cant_filas; ?>" type="checkbox" value="t"></td>
  </tr>
<?
 $cant_filas--;
}
?>
</table><br>
<center>
<table width="50%" cellspacing="10">
<tr>
<td align="right"><input name="boton" type="submit" value="Guardar"></td>
<td><input name="boton" type="submit" value="Cancelar"></td>
</tr>
</table>
</center>
</form>
</body>
</html>
<?
 }//fin default
}//fin switch
?>
