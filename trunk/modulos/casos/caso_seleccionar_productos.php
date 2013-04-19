<?php
/*
Autor: Fernando

MODIFICADA POR
$Author: fernando $
$Revision: 1.1 $
$Date: 2004/05/21 17:48:20 $
*/

require("../../config.php");
?>
<script>
function habilitar_boton(select)
{
if (select[select.selectedIndex].id!=-1)
         {
         document.all.cargar.disabled=0;
         }
         else
         {
         document.all.cargar.disabled=1;
         }

}
</script>
<html>
<head>
<title>Productos</title>
</head>
<?
//PARAMETROS DE ENTRADA
$onclickcargar= $parametros['onclickcargar'];
$onclicksalir= $parametros['onclicksalir'];

?>
<body bgcolor="#E0E0E0" topmargin="1" >
<?php
$sql="select descripcion, codigo from tipos_prod ORDER BY descripcion";
$resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);


$link=encode_link("caso_seleccionar_productos.php",array("tipo"=>$tipo,'onclickcargar'=>$onclickcargar,'onclicksalir'=>$onclicksalir,'id_proveedor'=>$id_proveedor));

?>
<link rel=stylesheet type='text/css' href='<? echo "$html_root/lib/estilos.css"?>'>
<form name="form1" action="<?php echo $link; ?>" method="POST">
<input type=hidden name=cambio_producto>
<br>
<br>
<table width="100%" align=center>
<tr id=mo>
  <td colspan=3>Elija el producto a agregar
</tr>
<tr id=ma>
 <td width="33%"> Tipo de Producto </td>
 <td width="33%"> Productos        </td>
 <td  width="15%">Proveedores      </td>
</tr>
<tr id=mo>
<td>
<select name="select_tipo" size="20" onchange=
"
document.all.cambio_producto.value=0;
if (this.selectedIndex!=0) form1.submit();
">
<option value=0>Seleccione un Tipo de Producto</option>
<?php
while (!$resultado->EOF)
{
?>
<option value="<?php echo $resultado->fields['codigo']; ?>" <?php if ($resultado->fields['codigo']==$_POST['select_tipo']) echo "selected";?>><?php echo $resultado->fields['descripcion']; ?></option>
<?php
$resultado->MoveNext();
}
?>
</select>
</td>
<td>
<?php
if ($_POST['select_tipo']!=""){
$sql="select distinct(pp.id_producto),pp.desc_gral";
$sql.=" from general.productos as pp";
$sql.=" where pp.tipo='".$_POST['select_tipo']."'";
$sql.=" order by pp.desc_gral";
$resultado_prod=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
}
?>
<select name="select_producto" size="20" style="width:260px;" onchange=
"
document.all.cambio_producto.value=1;
if (this.selectedIndex!=0) form1.submit();
">
<option value=0 id="0">Seleccione un producto</option>
<?php
if ($_POST['select_tipo']!=""){
while (!$resultado_prod->EOF)
{
 $id_producto=$resultado_prod->fields['id_producto'];
 $descripcion=$resultado_prod->fields['desc_gral'];

 if ($id_producto==$_POST['select_producto'])
						   $selected="selected";
						   else
						   $selected="";
?>
<option value="<?=$id_producto?>"<?=$selected;?>>
 <?=$descripcion?>
</option>
<?php
$resultado_prod->MoveNext();
}
}//del if que muestra los productos
?>
</select>
</td>
<?

$cambio_producto=$_POST["cambio_producto"];
$id_producto=$_POST["select_producto"];
if (($id_producto!="")&&($cambio_producto)){
$sql="select id_proveedor,razon_social from proveedor";
$sql.=" join precios using (id_proveedor)";
$sql.=" where precios.id_producto=$id_producto order by razon_social";
$proveedores=$db->execute($sql) or die ($sql."<br>".$db->errormsg());

}
?>
<td>
<select name="select_proveedores" size="20" style="width:260px;" onclick="habilitar_boton(this);">
 <option id=-1>Elija un Proveedor</option>
 <?

if ($id_producto!=""){
 $cantidad=$proveedores->recordcount();
 for($i=0;$i<$cantidad;$i++){
 $id_proveedor=$proveedores->fields["id_proveedor"];
 $razon_social=$proveedores->fields["razon_social"];
 ?>
  <option value="<?=$id_proveedor?>"><?=$razon_social?></option>
 <?
 $proveedores->movenext();
 }
 }
 ?>
</select>
</td>
</tr>
<tr>
  <td colspan=3 align=center>
  <input type="button" name="cargar" value="Cargar" disabled style="width:70;height:20" onClick="<?=$onclickcargar ?>">
  <input type="button" name="salir" value="Salir"  style="width:70;height:20" onClick="<?=$onclicksalir ?>">
  </td>
</tr>
</table>
</form>
</body>
</html>