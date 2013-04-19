<?php
require("../../config.php");
?>
<html>
<head>
<script language="javascript">
function modificar_precio(valor)
{document.all.precio.value=valor;
}

function add()
{var num;
var fila = window.opener.document.all.productos_ad.insertRow(window.opener.document.all.productos_ad.rows.length);
num=parseInt(window.opener.document.all.cant_ad.value,10)+2;
fila.insertCell(0).innerHTML="<input type='text' name=cantidad"+window.opener.document.all.cant_ad.value+" size='5'>";
fila.insertCell(1).innerHTML=window.document.all.select_tipo.options[window.document.all.select_tipo.selectedIndex].value;
fila.insertCell(2).innerHTML="<input type='text' name=descripcion"+window.opener.document.all.cant_ad.value+" value='' style='width=100%'>";
fila.insertCell(3).innerHTML="<input type='text' name=precio"+window.opener.document.all.cant_ad.value+" size='9'><input type='button' value='eliminar' onclick=eliminar_fila("+num+");>";
}


//funcion que me manda el valor a la pagina que la llama
function envia(valor) {
var objeto;
objeto2=eval("window.opener.document.all.estado"+valor);
objeto3=eval("window.opener.document.all.producto"+valor);
objeto4=eval("window.opener.document.all.tipo"+valor);
if (objeto2.value==0) 
 {objeto3.value=objeto4.value;
  objeto2.value=3; //debo eliminar un producto e insertar otro
 }
if (objeto2.value==1)
  objeto2.value=3; //debo eliminar un producto e insertar otro
if (objeto2.value==4) //no habia nada
 objeto2.value=2; //debo insertar un producto


objeto=eval("window.opener.document.all.tip"+valor);
objeto.value=window.document.all.select_tipo.options[window.document.all.select_tipo.selectedIndex].value;
objeto=eval("window.opener.document.all.tipo"+valor);
objeto.value=window.document.all.select_producto.options[window.document.all.select_producto.selectedIndex].value;
objeto=eval("window.opener.document.all.descripcion"+valor);
objeto.value=window.document.all.select_producto.options[window.document.all.select_producto.selectedIndex].text;
objeto=eval("window.opener.document.all.precio"+valor);
objeto.value=this.document.all.precio.value;
objeto=eval("window.opener.document.all.cantidad"+valor);
objeto.value=1;
/*add();
if (window.opener.document.all.cant_ad.value==0)
 window.opener.document.all.productos_ad.style.visibility='visible';
objeto=eval("window.opener.document.form1.precio"+window.opener.document.all.cant_ad.value);
objeto.value=this.document.all.precio.value;
objeto=eval("window.opener.document.form1.descripcion"+window.opener.document.all.cant_ad.value);
objeto.value=window.document.all.select_producto.options[window.document.all.select_producto.selectedIndex].text;
window.opener.document.all.cant_ad.value++;*/
window.opener.focus();
window.close();
}


</script>
</head>
<body bgcolor="#E0E0E0">
<?php
/*switch ($parametros['tipo'])
{case "Computadora Enterprise":
 case "Computadora Matrix"    :
                               //$sql="select descripcion, codigo from tipos_prod where codigo<>'software' and codigo<>'insumos impresora'";
                               $sql="select descripcion, codigo from tipos_prod where codigo <>'conexo' and codigo <> 'garantia' ";
                               $resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
                               $link=encode_link("agregar_productos.php",array("tipo"=>$parametros['tipo']));
                               break;
 case "Impresora"             :$sql="select descripcion, codigo from tipos_prod where codigo <>'conexo' and codigo <> 'garantia' and codigo='impresora'";
                               $resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
                               $link=encode_link("agregar_productos.php",array("tipo"=>$parametros['tipo']));
                               break;
 case "Software"              :$sql="select descripcion, codigo from tipos_prod where codigo='software'";
                               $resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
                               $link=encode_link("agregar_productos.php",array("tipo"=>$parametros['tipo']));
                               break;
}*/
$sql="select descripcion, codigo from tipos_prod where codigo <>'conexo' and codigo <> 'garantia' order by codigo";
$resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
$link=encode_link("agregar_productos.php",array("tipo"=>$parametros['tipo']));
?>
<form name="form1" action="<?php echo $link; ?>" method="POST">
<center>
<font size="5" color="#5090C0">Seleccione un Producto</font><br><br>
<input type="hidden" name="fila" value="<?php if ($_POST['fila']=="") echo $parametros['fila']; else echo $_POST['fila']; ?>">
<table>
<tr bgcolor="#5090C0">
<td colspan="2" align="center"><font color="#E0E0E0"><b>Productos Adicionales</td>
<td><font color="#E0E0E0"><b>Precio</td>
</tr>
<tr bgcolor="#D5D5D5">
<td>
<select name="select_tipo" size="10" onchange="form1.submit();">
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
$sql="select productos.id_producto,productos.desc_gral,precios.precio from ((productos join precios on productos.tipo='".$_POST['select_tipo']."' and productos.id_producto=precios.id_producto) join proveedor on proveedor.id_proveedor=precios.id_proveedor and proveedor.razon_social='licitaciones');";
$resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
?>
<select name="select_producto" size="10" onchange="modificar_precio(document.all.select_producto.options[document.all.select_producto.selectedIndex].id);">
<option value=0 id="0">Seleccione un Producto</option>
<?php
while (!$resultado->EOF)
{
?>
<option value="<?php echo $resultado->fields['id_producto']; ?>" id="<?php echo $resultado->fields['precio']; ?>"><?php echo $resultado->fields['desc_gral']; ?></option>
<?php
$resultado->MoveNext();
}
?>
</select>
</td>
<td valign="top"><input type="text" name="precio" size="8" value="" style="height:20" readonly='true'></td>
</tr>
</table>
<input type="button" name="boton" value="Cargar" style="width:70;height:20" onClick="envia(window.document.all.fila.value);">
<input type="button" name="boton" value="Salir" style="width:70;height:20" onClick="window.opener.document.all.boton<?php if ($_POST['fila']=="") echo $parametros['fila']; else echo $_POST['fila']; ?>.value='agregar';javascript:window.close();">
</center>
</form>
</body>
</html>