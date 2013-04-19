<?php
/*
Autor: GACZ - Ingaramo

MODIFICADA POR
$Author: nazabal $
$Revision: 1.5 $
$Date: 2004/12/20 21:17:39 $
*/

require("../../config.php");
?>
<html>
<head>
<script language="javascript">
function modificar_precio(valor)
{document.all.precio.value=valor;
}

</script>
<title>Productos</title>
</head>
<?
//PARAMETROS DE ENTRADA
$onclickcargar= $parametros['onclickcargar'];
$onclicksalir= $parametros['onclicksalir'];
$tipo=$parametros['tipo'];
$cambiar=$parametros['cambiar'];

$id_proveedor=$_POST['select_proveedor'] or 
$id_proveedor=$parametros['id_proveedor'] or 
$id_proveedor=$_GET['id_proveedor']; 
if ($id_proveedor=="")
{
//redireccionar a otra pagina para que elija en proveedor	
 $id_proveedor=700;//licitaciones
}
?>
<body bgcolor="#E0E0E0" topmargin="1" >
<!--
onUnload="<? //echo $onclicksalir; ?>">
El problema es que on unload se ejecuta tambien cuando se refresca la ventana

-->
<?php 
switch ($tipo)
{case "Computadora Enterprise":
 case "Computadora Matrix"    :$sql="select descripcion, codigo from tipos_prod where codigo<>'software' and codigo<>'insumos impresora' ORDER BY descripcion";
                               $resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);
                               break;
 case "Impresora"             :$sql="select descripcion, codigo from tipos_prod where codigo='insumos impresora' ORDER BY descripcion";
                               $resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);                           
                               break;
 case "Software"              :$sql="select descripcion, codigo from tipos_prod where codigo='software' ORDER BY descripcion";
                               $resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);                           
                               break;          
 default								:$sql="select descripcion, codigo from tipos_prod ORDER BY descripcion";
 										 $resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);                           
 										 break;
}
$link=encode_link("seleccionar_productos.php",array("tipo"=>$tipo,'onclickcargar'=>$onclickcargar,'onclicksalir'=>$onclicksalir,'id_proveedor'=>$id_proveedor));
$sql="select * from proveedor where id_proveedor=$id_proveedor";
$proveedor=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);

?>
<form name="form1" action="<?php echo $link; ?>" method="POST">
<center>
<font size="5" color="#5090C0">Seleccione un Producto</font>
<BR><BR>
<div align="left" >
      <table width="100%" border="0" cellspacing="1" cellpadding="1">
        <tr> 
          <td><font size="+1" color="#5090C0"> Proveedor:&nbsp;&nbsp;</font><font size="+1"> 
            <?=($proveedor->fields['razon_social'])?$proveedor->fields['razon_social']:'DESCONOCIDO'?>
            </font>&nbsp;&nbsp; <input name="ch_proveedor" type="button" id="ch_proveedor" value="Cambiar" <?if ($cambiar==0) echo ' disabled' ?> title="Cambiar el proveedor del producto" onclick="location.href='<?=encode_link('elegir_prov.php',array('id_proveedor'=>$id_proveedor,'volver'=>$_SERVER['SCRIPT_NAME'],'onclickcargar'=>$onclickcargar,'onclicksalir'=>$onclicksalir)) ?>'"></td>
        </tr>
      </table>
</div>
<table width="100%">
<tr bgcolor="#5090C0">
<td colspan="2" align="center" width="85%" nowrap><font color="#E0E0E0"><b>Productos</td>
<td align="center" width="15%" nowrap><font color="#E0E0E0"><b>Precio U$S</b> </td>
</tr>
<tr bgcolor="#D5D5D5">
<td>
<select name="select_tipo" size="10" onchange="if (this.selectedIndex!=0) form1.submit();">
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
$sql="select distinct(pp.id_producto),
(case when precios.id_proveedor=$id_proveedor then precios.precio else 0 end) as precio,".
"pp.desc_gral,pp.id_proveedor from ".
"(productos left join ".
"proveedor on proveedor.id_proveedor=$id_proveedor) pp left join ".
"precios on pp.id_producto=precios.id_producto AND pp.id_proveedor=precios.id_proveedor ".
"where pp.tipo='".$_POST['select_tipo']."'".
"ORDER BY desc_gral";

$resultado=$db->Execute($sql) or Error($db->ErrorMsg()."<br>".$sql);

?>
<select name="select_producto" size="10" style="width:260px;" onchange=
"modificar_precio(document.all.select_producto.options[document.all.select_producto.selectedIndex].id);
 if (document.all.select_producto.options[document.all.select_producto.selectedIndex].value!=0)
   document.all.boton[0].disabled=0;
 else
    document.all.boton[0].disabled=1;

">
<option value=0 id="0">Seleccione un producto</option>
<?php
while (!$resultado->EOF)
{
?>
<option value="<?php echo $resultado->fields['id_producto']; ?>" id="<?=number_format($resultado->fields['precio'],2,".","") ?>"><?php echo $resultado->fields['desc_gral']; ?></option>
<?php
$resultado->MoveNext();
}
?>
</select>
</td>
<td valign="top" align="center"><input type="text" name="precio" size="8" value="" style="height:20;text-align:right;" readonly='true'></td>
</tr>
</table>
<input type="button" name="boton" value="Cargar" disabled style="width:70;height:20" onClick="<?=$onclickcargar ?>">
<input type="button" name="boton" value="Salir" style="width:70;height:20" onClick="<?=$onclicksalir ?>">
</center>
</form>
<?
/*
<script>
window.resizeTo(600,400);
</script>
*/
?>
</body>
</html>
