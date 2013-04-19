<?
/*
$Author: mari $
$Revision: 1.4 $
$Date: 2004/11/04 15:05:29 $
*/
//$proveedor = "Oscar Fuentes";


//insertamos datos de precios del proveedor
if ($_POST['boton']=="Guardar")
 {require("guardar_presupuesto_proveedor.php");
?>
<script>
window.opener.document.all.form.submit();
window.close();
</script>
<?
 }

require_once("../../config.php");


$id_lic_prop=$parametros['id_licitacion_prop'];
$id_proveedor=$parametros['id_proveedor'];
$sql="select titulo,fecha_cotizacion,id_licitacion_prop from licitacion_presupuesto where id_licitacion_prop=".$id_lic_prop;
$resultado_licitacion=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
?>

<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel=stylesheet type='text/css' href='../../lib/estilos.css'>
<script language="javascript">
function cambiar_ad(d)
{var monto=eval("document.all.precio2_"+d);
 var cant=eval("document.all.cant_"+d);
 var total=eval("document.all.total_"+d);
 total.value=parseFloat(cant.value*monto.value).toFixed(2);
}

function cambiar_prod(e)
{var monto=eval("document.all.precio_"+e);
 var cant=eval("document.all.cant_prod_"+e);
 var total=eval("document.all.total_prod_"+e);
 total.value=parseFloat(cant.value*monto.value).toFixed(2);
}

function cambiar_tot(i)
{var total=0;
 var s=1;
 var cant_aux;
 var cant_prod=eval("document.all.cant_prod_reng_"+i);
 var cant_ad=eval("document.all.cant_ad_reng_"+i);
 var monto_tot;
 var cant_prod2=0;
 while(s < i) //cant_prod2 tiene cantidad de productos anteriores
  {cant_aux=eval("document.all.cant_prod_reng_"+s);
   cant_prod2+=parseInt(cant_aux.value);
   s++;
  }
 s=1;
 while(s<=cant_prod.value)
  {monto_tot=eval("document.all.total_prod_"+parseInt(s+cant_prod2));
   total+=parseFloat(monto_tot.value);
   s++;
  }
  s=1;
  cant_prod2=0;
  while(s < i) //cant_prod2 tiene cantidad de productos anteriores
  {cant_aux=eval("document.all.cant_ad_reng_"+s);
   cant_prod2+=parseInt(cant_aux.value);
   s++;
  }
  s=1;
  while(s<=cant_ad.value)
  {monto_tot=eval("document.all.total_"+parseInt(s+cant_prod2));
   total+=parseFloat(monto_tot.value);
   s++;
  }
  monto_tot=eval("document.all.monto_uni_reng_"+i);
  monto_tot.value=parseFloat(total).toFixed(2);
  monto_tot=eval("document.all.monto_tot_reng_"+i);
  cant_aux=eval("document.all.cant_reng2_"+i);
  monto_tot.value=parseFloat(total*cant_aux.value).toFixed(2);
}
</script>
</head>
<body bgcolor="<?=$bgcolor3;?>">
<form name="form1" method="POST" action="ventana_proveedor.php">
<input type="hidden" name="id_lic_prop" value="<? echo $resultado_licitacion->fields['id_licitacion_prop']; ?>">
<input type="hidden" name="id_proveedor" value="<? echo $id_proveedor; ?>">
<table width="100%"  border="0" cellspacing="0">
  <tr>
    <td colspan="4" bgcolor="#EEEEEE"><table width="100%" border="0" cellspacing="0">
      <tr bgcolor="#3399FF">
        <td width="62%"><FONT color="White"><B>Presupuesto para Coradir S.A - Vence el <? echo Fecha($resultado_licitacion->fields['fecha_cotizacion']); ?></span></td>
        <td width="30%">&nbsp;</td>
        <td width="8%"><div align="right">
          <div align="center"><font color=#FFFFFF onclick="window.close();" style="cursor:hand;">[SALIR]</font></div>
        </div></td>
      </tr>
    </table>      
     <table width="100%"  border="0" cellpadding="1">
        <tr>
          <td width="52%"><b>TITULO: <font color="Blue"><? echo $resultado_licitacion->fields['titulo']; ?></b></td>
          <td width="15%">&nbsp;</td>
          <td width="14%">&nbsp;</td>
          <td width="19%">&nbsp;</td>
        </tr>
      </table>
      
<?
$sql="select renglon_presupuesto.id_renglon_prop,renglon_presupuesto.id_renglon,renglon.titulo,renglon.cantidad,renglon.codigo_renglon from renglon_presupuesto join renglon on renglon_presupuesto.id_renglon=renglon.id_renglon where renglon_presupuesto.id_licitacion_prop=".$id_lic_prop;
$resultado_renglon=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql); 
$i=1;
$d=1;
$e=1;
while (!$resultado_renglon->EOF)
{
?>
        <table width="100%"  border="1" cellpadding="0" cellspacing="0" bgcolor="#EEEEEE">
        <tr>
          <td colspan="5">&nbsp</td>
          <td colspan="3" bgcolor="<?=$bgcolor3;?>"><div align="center"><strong>Precios</strong></div></td>
        </tr>
         <tr bgcolor="#EEEEEE">
          <td width="6%"><p><span><strong>Cant.</strong></span></p></td>
          <td colspan="4" width="30%"><div align="center"><strong>Descripci&oacute;n</strong></div></td>
          <td width="15%"><div>
              <div align="center"><strong>unitario</strong></div>
          </div></td>
          <td width="15%"><div>
              <div align="center"><strong>total</strong></div>
          </div></td>
          <td width="15%"><strong>&nbsp;</strong></td>
        </tr>
        <tr bgcolor="<?=$bgcolor3;?>">
        <input type="hidden" name="cant_reng2_<? echo $i; ?>" value="<? echo $resultado_renglon->fields['cantidad']; ?>">
          <td><div align="left"><span><? echo $resultado_renglon->fields['cantidad']; ?></span></div></td>
          <td colspan="4"><span><strong><? echo $resultado_renglon->fields['titulo']; ?></strong></span></td>
<?
$sql="select adicionales_proveedor.monto_unitario,adicionales.cantidad from renglon_presupuesto join adicionales on renglon_presupuesto.id_renglon_prop=adicionales.id_renglon_prop join adicionales_proveedor on adicionales_proveedor.id_adicional=adicionales.id_adicional and adicionales_proveedor.id_proveedor=$id_proveedor where renglon_presupuesto.id_renglon_prop=".$resultado_renglon->fields['id_renglon_prop'];
$resultado_sum=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
//saco monto total para proveedor
 $monto_total=0;
 while(!$resultado_sum->EOF)
 {$monto_total+=($resultado_sum->fields['cantidad']*$resultado_sum->fields['monto_unitario']);
  $resultado_sum->MoveNext();
 }
 
$sql="select producto_proveedor.monto_unitario,producto_presupuesto.cantidad from renglon_presupuesto join producto_presupuesto on renglon_presupuesto.id_renglon_prop=producto_presupuesto.id_renglon_prop join producto_proveedor on producto_proveedor.id_producto=producto_presupuesto.id_producto and producto_proveedor.id_proveedor=".$id_proveedor." where renglon_presupuesto.id_renglon_prop=".$resultado_renglon->fields['id_renglon_prop'];
$resultado_sum=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql); 
//saco monto total para proveedor
 while(!$resultado_sum->EOF)
 {$monto_total+=($resultado_sum->fields['cantidad']*$resultado_sum->fields['monto_unitario']);
  $resultado_sum->MoveNext();
 }
?>
          <td><div align="center"><strong>u$s <input type="text" name="monto_uni_reng_<? echo $i; ?>" value="<? echo $monto_total; ?>" size="12" readonly></strong></div></td>
          <td><div align="center"><strong>u$s <input type="text" name="monto_tot_reng_<? echo $i; ?>" value="<? echo ($monto_total*$resultado_renglon->fields['cantidad']); ?>" size="12" readonly></strong></div></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
        <td>&nbsp</td>
          <td width="5%" bgcolor="#EEEEEE" width="1%"><strong><span><strong>Cant.</strong></span></strong></td>
          <td colspan="3" bgcolor="#EEEEEE"><div align="center">Producto</div></td>
          <td bgcolor="#EEEEEE" ><div align="center"><strong>unitario</strong></div></td>
          <td bgcolor="#EEEEEE"><div align="center"><strong >total</strong></div></td>
          <td bgcolor="#EEEEEE"><div align="center"><strong >Comentarios</strong></div></td>
        </tr>
<?
$sql="select producto_presupuesto.id_producto,producto_presupuesto.desc_nueva,producto.desc_gral,producto_presupuesto.cantidad,producto.id from producto left join producto_presupuesto on producto.id=producto_presupuesto.id left join renglon_presupuesto on producto_presupuesto.id_renglon_prop=renglon_presupuesto.id_renglon_prop where renglon_presupuesto.id_renglon_prop=".$resultado_renglon->fields['id_renglon_prop'];
$resultado_producto=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
$sql="select adicionales.id_adicional,adicionales.desc_nueva,productos.desc_gral,adicionales.cantidad,productos.id_producto from renglon_presupuesto left join adicionales on adicionales.id_renglon_prop=renglon_presupuesto.id_renglon_prop left join productos on adicionales.id_producto=productos.id_producto where adicionales.id_renglon_prop=".$resultado_renglon->fields['id_renglon_prop'];
$resultado_adicionales=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
$j=1;
while (!$resultado_producto->EOF)
{
$sql="select monto_unitario,comentario from producto_proveedor where id_proveedor=$id_proveedor and id_producto=".$resultado_producto->fields['id_producto'];
$resultado_productoprov=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
?>
 
        <tr bgcolor="<?=$bgcolor3;?>">
        <td>&nbsp</td>
          <td><span><? echo $resultado_producto->fields['cantidad']; ?></span>
          <input type="hidden" name="cant_prod_<? echo $e; ?>" value="<? echo $resultado_producto->fields['cantidad']; ?>">
          </td>
          <td colspan="3"><span><? echo $resultado_producto->fields['desc_nueva']; ?></span></td>
          <td><div align="center"><span>u$s
            <input name="precio_<? echo $e; ?>" type="text" size="12" value="<? echo $resultado_productoprov->fields['monto_unitario']; ?>" onchange="cambiar_prod(<? echo $e; ?>);cambiar_tot(<? echo $i; ?>);">
          </span></div></td>
          <td><div align="center"><span>u$s <input type="text" name="total_prod_<? echo $e; ?>" value="<? echo ($resultado_productoprov->fields['monto_unitario']*$resultado_producto->fields['cantidad']); ?>" size="12" readonly></span></div></td>
          <input type="hidden" name="prod_<? echo $e; ?>" value="<? echo $resultado_producto->fields['id_producto']; ?>">
          <td><input type="text" name="comentario_<?=$e;?>" value="<?=$resultado_productoprov->fields['comentario'];?>"></td>
        </tr>
<?
$resultado_producto->MoveNext();
$j++;
$e++;
}
?>
<input type="hidden" name="cant_prod_reng_<? echo $i; ?>" value="<? echo ($j-1);?>">
<?
$cant_ad_reng=1;
while (!$resultado_adicionales->EOF)
{
$sql="select comentario,monto_unitario from adicionales_proveedor where id_proveedor=$id_proveedor and id_adicional=".$resultado_adicionales->fields['id_adicional'];
$resultado_productoprov=$db->Execute($sql) or die ($db->ErrorMsg()."<br>".$sql);
?>
        
        <tr>
        <td>&nbsp</td>
          <td><span><? echo $resultado_adicionales->fields['cantidad']; ?></span>
          <input type="hidden" name="cant_<? echo $d; ?>" value="<? echo $resultado_adicionales->fields['cantidad']; ?>">
          </td>
          <td colspan="3"><span><? echo $resultado_adicionales->fields['desc_nueva']; ?></span></td>
          <td><div align="center"><span>u$s
            <input name="precio2_<? echo $d; ?>" type="text" size="12" value="<? echo $resultado_productoprov->fields['monto_unitario']; ?>" onchange="cambiar_ad('<? echo $d; ?>');cambiar_tot(<? echo $i; ?>);">
          </span></div></td>
          <td><div align="center"><span>u$s <input type="text" name="total_<? echo $d; ?>" value="<? echo  ($resultado_productoprov->fields['monto_unitario']*$resultado_adicionales->fields['cantidad']); ?>" size="12" readonly></span></div></td>
          <input type="hidden" name="adic_<? echo $d; ?>" value="<? echo $resultado_adicionales->fields['id_adicional']; ?>">
         <td><input type="text" name="comentario_adic_<?=$d;?>" value="<?=$resultado_productoprov->fields['comentario'];?>"></td>
        </tr>
<?
$resultado_adicionales->MoveNext();
$d++;
$j++;
$cant_ad_reng++;
}
?>
<input type="hidden" name="cant_ad_reng_<? echo $i; ?>" value="<? echo ($cant_ad_reng-1);?>">
<?
$i++;
$resultado_renglon->MoveNext();
?>
</table><br>
<?
}
?>      
<input type="hidden" name="cant_productos" value="<? echo ($e-1); ?>">
<input type="hidden" name="cant_adicionales" value="<? echo ($d-1); ?>">
      <table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="22%">&nbsp;</td>
          <td width="18%">&nbsp;</td>
          <td width="20%">&nbsp;</td>
          <td width="20%">&nbsp;</td>
          <td width="20%"><table align="right" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
            <tr>
              <td><div align="center"><strong><font color="#808080"><input type="submit" name="boton" value="Guardar" style="cursor:hand;"></font></strong></div></td>
            </tr>
          </table></td>
        </tr>
      </table><br>
      <p align="left">&nbsp;</p></td>
  </tr>
</table>
</form>
</body>
</html>