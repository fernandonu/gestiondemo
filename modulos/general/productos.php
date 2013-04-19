<?php
include("../../config.php");

$tipo_p=$_POST["prod"] or $tipo_p=$parametros["tipo"];

if($_POST["nuevo_producto"]=="Nuevo Producto")
{
 
 $link1=encode_link($html_root."/index.php",array("modulo"=>"productos","menu"=>"altas_prod","extra"=>array ("pagina"=>"productos", "tipo"=> $tipo_p)));	
 echo "<html><head><script language=javascript>"; 
 echo "window.parent.location='$link1';";
 echo "</script></head></html>";
}

if($_POST["modif_precio"]=="Modificar Precio")
{
 $link=encode_link($html_root."/index.php",array("modulo"=>"productos","menu"=>"cargar_precio","extra"=>array("id_producto"=> $_POST["producto_id"],"pagina"=>"productos","tipo_prod"=> $_POST["tipo"], "precio"=>$_POST["precio"], "id_proveedor"=>$_POST["proveedor"],"observaciones"=>$_POST["observa"])));	
 echo "<html><head><script language=javascript>"; 
 echo "window.parent.location='$link';";
 echo "</script></head></html>";
}

if($_POST["add"]=="Agregar Precio")
{
 $link=encode_link($html_root."/index.php",array("modulo"=>"productos","menu"=>"cargar_precio","extra"=>array("id_producto"=>$_POST["producto_id"],"pagina"=>"precios","tipo_prod"=> $_POST["tipo"])));
 echo "<html><head><script language=javascript>"; 
 echo "window.parent.location='$link';";
 echo "</script></head></html>";	
}

if($_POST["del"]=="Eliminar Precio")
{
 if($_POST["producto"]!="")
 {
 	$pr=$_POST['producto_id'];
 	$pv=$_POST['proveedor'];	
 	$query="delete from precios where id_producto=$pr and id_proveedor=$pv";
 	$db->Execute($query) or die($db->ErrorMsg().$query);
 }		 
}

if ($_POST['stock']=="Añadir Stock"){
	
$link=encode_link($html_root."/index.php",array("modulo"=>"stock","menu"=>"stock_add","extra"=>array("id_producto" => $_POST['producto_id'],"id_proveedor" => $_POST['proveedor'],"id_deposito" => $_POST['deposito'],"tipo"=>$tipo_p)));
 echo "<html><head><script language=javascript>"; 
 echo "window.parent.location='$link';";
 echo "</script></head></html>";	
}
?>
<html>
<head>
<title>Proveedores-Producto</title>
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<SCRIPT language="javascript">
function cambiar_check(fila)
{if (document.all.producto.length>1)
  document.all.producto[fila].checked="true";
 else
  document.all.producto.checked="true";
}

function cambiar_prov(id_prov)
{document.all.proveedor.value=id_prov;
}

function cambiar_precio(id_prov,precio,id_prod,tipo,obs,dep)
{
 document.all.proveedor.value=id_prov;
 document.all.tipo.value=tipo;	
 document.all.producto_id.value=id_prod;
 document.all.precio.value=precio;
 document.all.deposito.value=dep;
 if(obs!="null")	
  document.all.observa.value=obs;
}

function cambiar_prod(id_prod,tipo,precio)
{
 document.all.precio.value=precio;
 document.all.producto_id.value=id_prod;
 document.all.tipo.value=tipo;	
}

</script>
</head>
<body bgcolor="<?php echo $bgcolor3; ?>">
<center>
<br>
<?php


//modificamos el form dependiendo de donde se llamo la pagina
if ($parametros["modulo"]=="remito") //pagina llamada desde remitos
{$link=encode_link($parametros["nombre_pagina"],array("modulo" => $parametros["modulo"],
                                                 "tipo" => '',
                                                 "remito" => $parametros["remito"],
                                                 "nombre_pagina" => $parametros["nombre_pagina"]));

 $link2=encode_link("productos1.php",array("modulo" => $parametros["modulo"],
                                    "tipo" => '',
                                    "remito" => $parametros["remito"],
                                    "nombre_pagina" => $parametros["nombre_pagina"]));
}
elseif($parametros["modulo"]=="licitaciones") // viene de licitaciones
{$link=encode_link($parametros["nombre_pagina"],array("modulo" => $parametros["modulo"],
                                                 "tipo" => $tipo_p,
                                                 "licitacion" => $parametros["licitacion"],
                                                 "renglon" => $parametros["renglon"],
                                                 "item" => $parametros["item"],
                                                 "nombre_pagina" => "productos.php",
                                                 "producto"=>'',
                                                 "moneda"=>$parametros['moneda'],
                                                 "valor_moneda"=>$parametros['valor_moneda']));

$link2=encode_link("productos1.php",array("modulo" => $parametros["modulo"],
                                 //  "tipo" => $parametros['tipo'],
                                   "licitacion" => $parametros["licitacion"],
                                   "renglon" => $parametros["renglon"],
                                   "item" => $parametros["item"],
                                   "nombre_pagina" => $parametros["nombre_pagina"],
                                   "moneda"=>$parametros['moneda'],
                                    "valor_moneda"=>$parametros['valor_moneda']));



}
else 
{$link2=encode_link("productos1.php",array("modulo" => $parametros["modulo"],
                                  "tipo" => $tipo_p
                                   ));
 $link=encode_link("productos1.php",array("modulo" => $parametros["modulo"],
                                  "tipo" => $tipo_p
                                   ));                                  
}
?>
<form name="form1" action="<?php echo $link2;  ?>" method="POST">
<input type="hidden" name="prod" value="">
<table border="0" cellspacing="2">
<tr>
<td><b><font color="<?php echo $bgcolor1;?>">Buscar Producto:</font></b></td>
<td><select name="tipo_producto">
    <option value="todos" <?php if ($_POST['tipo_producto']=="todos") echo "selected" ?>>Todos
<?php
$sql="select descripcion, codigo from tipos_prod";
$resultado = $db->Execute($sql) or die($db->ErrorMsg());
while (!$resultado->EOF){
?>
<option value="<?php echo $resultado->fields['codigo']; ?>" <?php if ($tipo_p==$resultado->fields['codigo']) echo "selected" ?>><?php echo $resultado->fields['descripcion']; ?>
<?php
$resultado->MoveNext();
}
?>
</td>
<td><input type="submit" name="boton" value="Buscar" onclick="document.all.prod.value=document.all.tipo_producto.options[document.all.tipo_producto.options.selectedIndex].value"></td>
</tr>
</table>
<hr>
<input type="hidden" name="cant" value="<?php if(!isset($_POST['cant'])) echo "-1"; else echo ($_POST['cant']-1); ?>">
<b>Lista de Proveedores para <?php if ($tipo_p=="") echo "productos"; else echo $tipo_p; ?></b>
</form>
<br>
<div style="position:relative; width:100%; height:63%; overflow:auto;">
<form name="form" action="<?php echo $link;  ?>" method="POST">

<table border="0" cellspacing="2" width="100%"> 
<tr title="Vea comentarios de los productos"  id=mo>
<td><font color="<?php echo $bgcolor3; ?>"><b>Descripcion Gral.</b></font></td>
<td><font color="<?php echo $bgcolor3; ?>"><b>Marca</b></font></td>
<td><font color="<?php echo $bgcolor3; ?>"><b>Modelo</b></font></td>
<td><font color="<?php echo $bgcolor3; ?>"><b>Stock</b></font></td>
</tr>
<?php
// ****************************************************
// CHUSMEA COPIA DE PLACA MADRE.PHP (Es practicamente copy-paste de este archivo).
// deberias pasarme también el tipo de producto del que queres la descripcion
// así en el cartel de arriba pongo DESCRIPCION DEL PRODUCTO concatenado con
// $_GET["producto"] por ejemplo.
// Te mande un e-mail.
//								Pablo A. Rojo
// ****************************************************



if ($_POST['boton']!="Buscar")
{if (($tipo_p!='todos') && ($tipo_p!=''))
{$query="select sum(stock.cant_disp) as cant_disp,productos.id_producto,productos.desc_gral,productos.marca,productos.modelo from (productos left join stock on stock.id_producto=productos.id_producto) where productos.tipo='".$tipo_p."' group by productos.id_producto,productos.desc_gral,productos.marca,productos.modelo";
 $tipo=$tipo_p;
}
else
{
 $query="select sum(stock.cant_disp) as cant_disp,productos.id_producto,productos.desc_gral,productos.marca,productos.modelo from (productos left join stock on productos.id_producto=stock.id_producto) group by productos.id_producto,productos.desc_gral,productos.marca,productos.modelo;";
 $tipo="Productos";
}
}
else
{if ($_POST['tipo_producto']!="todos")
{$query="select sum(stock.cant_disp) as cant_disp,productos.id_producto,productos.desc_gral,productos.marca,productos.modelo from (productos left join stock on stock.id_producto=productos.id_producto) where productos.tipo='".$_POST["tipo_producto"]."' group by productos.id_producto,productos.desc_gral,productos.marca,productos.modelo;";
 $tipo=$_POST['tipo_producto'];
}
 else
 {$query="select sum(stock.cant_disp) as cant_disp,productos.id_producto,productos.desc_gral,productos.marca,productos.modelo from (productos left join stock on stock.id_producto=productos.id_producto) group by productos.id_producto,productos.desc_gral,productos.marca,productos.modelo;";
  $tipo=$_POST['tipo_producto'];
 }
}
$resultado = $db->Execute($query) or die($db->ErrorMsg());
$filas_encontradas=$resultado->RecordCount();
$cnr=1;
$cont_filas=0; //contador para saber en que fila se realizo un click
$tipo_producto2=$_POST['tipo_producto'];
if ($tipo_producto=="")
 $tipo_producto2=$tipo_p;
while (!$resultado->EOF)
{
if ($parametros['modulo']=="remito")
{$link=encode_link("detalle_productos.php",array("modulo"=> $parametros['modulo'],
                                                "nombre_pagina" => $parametros["nombre_pagina"],
                                                "producto"=>$resultado->fields["id_producto"],
                                                "remito"=>$parametros["remito"],
                                                "tipo_producto"=>$tipo_producto2));
}
elseif($parametros['modulo']=="licitaciones")
{$link=encode_link("detalle_productos.php",array("modulo"=> $parametros['modulo'],
                                                "nombre_pagina" => $parametros["nombre_pagina"],
                                                "producto"=>$resultado->fields["id_producto"],
                                                "tipo" =>$tipo_p,
                                                 "licitacion" => $parametros["licitacion"],
                                                 "renglon" => $parametros["renglon"],
                                                 "item" => $parametros["item"],
                                                 "moneda"=>$parametros['moneda'],
                                                  "valor_moneda"=>$parametros['valor_moneda'],
                                                   "tipo_producto"=>$tipo_producto2));
}
else 
{$link=encode_link("detalle_productos.php",array("modulo"=> $parametros['modulo'],
                                                "nombre_pagina" => $parametros["nombre_pagina"],
                                                "producto"=>$resultado->fields["id_producto"],
                                                "tipo" => $tipo_p,
                                                 "licitacion" => $parametros["licitacion"],
                                                 "renglon" => $parametros["renglon"],
                                                 "item" => $parametros["item"],
                                                 "moneda"=>$parametros['moneda'],
                                                  "valor_moneda"=>$parametros['valor_moneda'],
                                                   "tipo_producto"=>$tipo_producto2));
}

$atrib ="bgcolor='#eeeeee'";
$color2="Black";
$atrib.=" style=cursor:hand";
$sql="select precios.id_proveedor,proveedor.razon_social,precios.precio,precios.observaciones,stock.cant_disp,depositos.nombre,depositos.id_deposito from ((precios join proveedor on proveedor.id_proveedor=precios.id_proveedor and precios.id_producto=".$resultado->fields['id_producto'].")left join stock on stock.id_proveedor=precios.id_proveedor and stock.id_producto=precios.id_producto) left join depositos on depositos.id_deposito=stock.id_deposito";
$result = $db->Execute($sql) or die($db->ErrorMsg());
$filas=$result->RecordCount();

//traemos las descripciones tenicas de cada producto y lo almacenamos en
//un string, para poder mostrarlo.

$query="select titulo from descripciones where id_producto=".$resultado->fields['id_producto'];
$desc_prod=$db->Execute($query) or die ($db->ErrorMsg().$query);
if($desc_prod->RecordCount()>0)
{
 $descripcion="DESCRIPCIONES:";
 $desc_prod->Move(0);
 while(!$desc_prod->EOF)
 {if($desc_prod->CurrentRow() == 0)
   $descripcion.=" ".$desc_prod->fields["titulo"];
  else 
   $descripcion.=", ".$desc_prod->fields["titulo"]; 
  $desc_prod->MoveNext();
 }

?>
<tr <?php echo $atrib; ?> title="<?echo $descripcion?>">
<?
}//del if
else 
{?>
<tr <?php echo $atrib; ?>>
<?
}
?>
<td <?php echo "onClick=\"location.href='$link'\";"; ?>><font color="<?php echo $color2; ?>"><b><?php echo $resultado->fields["desc_gral"]; ?></b></font></td>
<td <?php echo "onClick=\"location.href='$link'\";"; ?>><font color="<?php echo $color2; ?>"><b><?php echo $resultado->fields["marca"]; ?></b></font></td>
<td <?php echo "onClick=\"location.href='$link'\";"; ?>><font color="<?php echo $color2; ?>"><b><?php echo $resultado->fields["modelo"]; ?></b></font></td>
<td <?php echo "onClick=\"location.href='$link'\";"; ?>><font color="<?php echo $color2; ?>"><b><?php if ($resultado->fields["cant_disp"]=="") echo "0"; else echo $resultado->fields["cant_disp"]; ?></b></font></td>
</tr>
<tr>
<td colspan="4">
<table align="center" width="95%">
<?php
$cnr=0;$first=1;
if ($filas==0) //imprimo el boton de agregar precios aunque no tenga precios de antes
{?>
   <td width="25%"></td><td width="75%"></td>
   <td width="5%"><input type="submit" name="add" value="Agregar Precio" style="width:100%" onclick="cambiar_prod(<?php echo $resultado->fields['id_producto'];?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value)"></td>
<?
}
while (!$result->EOF){
if ($cnr==1)
{$color2=$bgcolor1;
 $color=$bgcolor2;
 $atrib ="bgcolor='$bgcolor2'";
 $cnr=0;
}
else
{$color2=$bgcolor2;
$color=$bgcolor1;
$atrib ="bgcolor='$bgcolor1'";
$cnr=1;}
$atrib.=" style=cursor:hand";
if(($parametros['modulo']=="remito")||($parametros['modulo']=="licitaciones"))
{?>
<tr <?php echo $atrib; ?> onclick="document.all.boton1.disabled=false; cambiar_prov(<?php echo $result->fields['id_proveedor']; ?>);">
<td width="5%" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $result->fields['id_proveedor']?>,<?echo $result->fields['precio']?>,<?echo $resultado->fields['id_producto']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<?echo $observac?>',<?if($result->fields['id_deposito']=="")echo "0";else echo $result->fields['id_deposito']?>); document.all.modif_precio.disabled=false;document.all.del.disabled=false;document.all.stock.disabled=false;"><input type="radio" name="producto" value="<?php  echo $resultado->fields["id_producto"]; ?>">
</td>
<td width="15%" title="<?echo $observac?>" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $result->fields['id_proveedor']?>,<?echo $result->fields['precio']?>,<?echo$resultado->fields['id_producto']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<?echo $observac?>',<?if($result->fields['id_deposito']=="")echo "0";else echo $result->fields['id_deposito']?>);"><b><font color="<?php echo $color2; ?>"><?php echo "U\$S ".$result->fields['precio']; ?></font></b></td>
<td width="63%" title="Proveedor" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $result->fields['id_proveedor']?>,<?echo $result->fields['precio']?>,<?echo$resultado->fields['id_producto']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<?echo $observac?>',<?if($result->fields['id_deposito']=="")echo "0";else echo $result->fields['id_deposito']?>);"><b><font color="<?php echo $color2; ?>"><?php echo $result->fields['razon_social']; ?></font></b></td>
<td title="Deposito" width="10%" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $result->fields['id_proveedor']?>,<?echo $result->fields['precio']?>,<?echo$resultado->fields['id_producto']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<?echo $observac?>',<?if($result->fields['id_deposito']=="")echo "0";else echo $result->fields['id_deposito']?>); "><? echo $result->fields['nombre'];?></td>
<td width="10%" align="center" title="Stock para este deposito"><?if($result->fields['cant_disp']!=null)echo $result->fields['cant_disp'];else echo "0"?></td>


<?}
else
{$observac=$result->fields['observaciones'];

	?>	
 <tr <?php echo $atrib; ?>>
<td width="5%" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $result->fields['id_proveedor']?>,<?echo $result->fields['precio']?>,<?echo $resultado->fields['id_producto']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<?echo $observac?>',<?if($result->fields['id_deposito']=="")echo "0";else echo $result->fields['id_deposito']?>); document.all.modif_precio.disabled=false;document.all.del.disabled=false;document.all.stock.disabled=false;"><input type="radio" name="producto" value="<?php  echo $resultado->fields["id_producto"]; ?>">
</td>
<td width="15%" title="<?echo $observac?>" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $result->fields['id_proveedor']?>,<?echo $result->fields['precio']?>,<?echo$resultado->fields['id_producto']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<?echo $observac?>',<?if($result->fields['id_deposito']=="")echo "0";else echo $result->fields['id_deposito']?>); document.all.modif_precio.disabled=false;document.all.del.disabled=false;document.all.stock.disabled=false;"><b><font color="<?php echo $color2; ?>"><?php echo "U\$S ".$result->fields['precio']; ?></font></b></td>
<td width="63%" title="Proveedor" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $result->fields['id_proveedor']?>,<?echo $result->fields['precio']?>,<?echo$resultado->fields['id_producto']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<?echo $observac?>',<?if($result->fields['id_deposito']=="")echo "0";else echo $result->fields['id_deposito']?>); document.all.modif_precio.disabled=false;document.all.del.disabled=false;document.all.stock.disabled=false;"><b><font color="<?php echo $color2; ?>"><?php echo $result->fields['razon_social']; ?></font></b></td>
<td title="Deposito" width="10%" onclick="cambiar_check(<?php echo $cont_filas; ?>);cambiar_precio(<?php echo $result->fields['id_proveedor']?>,<?echo $result->fields['precio']?>,<?echo$resultado->fields['id_producto']; ?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value,'<?echo $observac?>',<?if($result->fields['id_deposito']=="")echo "0";else echo $result->fields['id_deposito']?>); document.all.modif_precio.disabled=false;document.all.del.disabled=false;document.all.stock.disabled=false;"><? echo $result->fields['nombre'];?></td>
<td width="10%" align="center" title="Stock para este deposito"><?if($result->fields['cant_disp']!=null)echo $result->fields['cant_disp'];else echo "0"?></td>
<?
if($first)
 {$first=0;
 ?>
 <td width="5%" bgcolor="<?echo $bgcolor1?>">
   <input type="submit" name="add" value="Agregar Precio" title="Agregar Precio/Agregar Proveedor" style="width:100%" onclick="cambiar_prod(<?php echo $resultado->fields['id_producto'];?>,document.all.tipo_producto.options[document.all.tipo_producto.selectedIndex].value)">
 </td>
<?
 }
}
?>
</tr>
<?php
$cont_filas++;
$result->MoveNext();
}
?>
</table>
</td>
</tr>
<?php
$resultado->MoveNext();
}
if ($filas_encontradas==0)  //envio cartel de que no hay en stock ese producto
{
?>
  <tr><td colspan=4 bgcolor="<?php echo $bgcolor1; ?>" align="center"><font color="<?php echo $bgcolor2; ?>"><b> No hay <?php echo $tipo; ?> en stock </b></font></td></tr>
<?php
}
?>
</table>
</div>

<?php 
if(($parametros['modulo']=="remito")||($parametros['modulo']=="licitaciones"))
{
?>	
<hr>
<input type="submit" name="boton1" value="Agregar" disabled>
<?php $link=encode_link($parametros['nombre_pagina'],array("modulo" => $parametros["modulo"],
                                                 "tipo"=>$parametros['tipo_producto'],
                                                 "licitacion" => $parametros["licitacion"],
                                                 "renglon" => $parametros["renglon"],
                                                 "item" => $parametros["item"],
                                                 "nombre_pagina" => $parametros['nombre_pagina'],
                                                 "producto"=>'',
                                                 "moneda"=>$parametros['moneda'],
                                                 "remito" => $parametros["remito"],
                                                 "valor_moneda"=>$parametros['valor_moneda']));
?>                                                 
<input type="button" name="boton2" value="Volver" Onclick="location.href='<?php echo $link; ?>';">
</center>

<?PHP

}//cerramos el if(($parametros['modulo']=="remito")||($parametros['modulo']=="licitaciones"))

else //estamos en el modulo productos
{
 ?>
 <hr>
 <center>
   <input type="submit" name="nuevo_producto" value="Nuevo Producto">&nbsp;&nbsp;
   <input type="submit" name="modif_precio" value="Modificar Precio" disabled>&nbsp;&nbsp;
   <input type="submit" name="del" value="Eliminar Precio" disabled>&nbsp;&nbsp;          
   <input type="submit" name="stock" value="Añadir Stock" disabled>&nbsp;&nbsp;
 </center>  
<? 
}

?>

<input type="hidden" name="producto_id" value="0">
<input type="hidden" name="proveedor" value="0">
<input type="hidden" name="precio" value="0">
<input type="hidden" name="tipo" value="0">
<input type="hidden" name="observa" value="0">
<input type="hidden" name="deposito" value="1">

</form>
</html>