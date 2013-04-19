<?
/*
Autor: MAC

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.5 $
$Date: 2005/03/21 19:48:58 $
*/
require_once("../../config.php");

$id_fila=$parametros["id_fila"] or $id_fila=$_POST["id_fila"];
$permiso_cambio=$parametros["permiso_cambio"] or $permiso_cambio=$_POST["permiso_cambio"];

if($_POST["guardar_cambio"]=="Confirmar cambio de Producto")
{
 $db->StartTrans();
 
 $id_producto=$_POST["nuevo_id_prod"];
 $fecha_cambio=date("Y-m-d H:i:s");
 $fecha_cambio_2=date("d/m/Y");
 //agregamos el cambio de producto  a la tabla respectiva, para la fila elegida
 $query="insert into cambios_producto(id_producto,id_fila,fecha_cambio,usuario_cambio)
         values($id_producto,$id_fila,'$fecha_cambio','".$_ses_user["name"]."')";
 sql($query,"<br>Error al realizar el cambio del producto<br> $query") or fin_pagina();
 
 //Enviamos el e-mail avisando que se cambio el producto
 $sql = "select nro_orden,descripcion_prod from compras.fila where id_fila=$id_fila";
 $resul_fila = sql($sql,"Error al traer la informacion de la Fila") or fin_pagina();
 $sql = "select desc_gral from general.productos where id_producto=$id_producto";
 $resul_prod = sql($sql,"Error al tarer la informacion del Producto") or fin_pagina();
 $usuario=$_ses_user["name"];
 $producto=$resul_prod->fields['desc_gral'];
 $prod_fila=$resul_fila->fields['descripcion_prod'];
 $ord_fila=$resul_fila->fields['nro_orden'];
 $para="juanmanuel@pcpower.com.ar";
 $mensaje="En la orden de compra: $ord_fila, el Producto: \"$prod_fila\" fue cambiado por el producto \"$producto\".\n";
 $mensaje.="\nEl cambio fue relizado el dia: $fecha_cambio_2, por el usuario: $usuario";
 //echo $mensaje;die();
 enviar_mail($para,"Cambio de Producto en Orden Nº $ord_fila",$mensaje,' ',' ',' ',0);
 
 
 $db->CompleteTrans();
}


//traemos la info necesaria. Los cambios de productos realizados hasta el momento y la información del producto original de la fila
$query="select descripcion_prod,cambios_producto.id_producto,desc_gral,fecha_cambio,usuario_cambio 
        from fila left join cambios_producto using (id_fila) left join productos on (cambios_producto.id_producto=productos.id_producto)
        where id_fila=$id_fila order by fecha_cambio DESC
";
$cambios_prod=sql($query,"<br>Error al traer datos de los cambios de productos<br>") or fin_pagina();

//el primer producto es el actual, asi que seteamos la variable correspondiente. Si no hay cambio de producto, el producto actual
//es el mismo que el de la fila
$producto_actual=($cambios_prod->fields['desc_gral'])?$cambios_prod->fields['desc_gral']:$cambios_prod->fields['descripcion_prod'];
//el producto original, que esta cargado en la fila
$producto_original=$cambios_prod->fields['descripcion_prod'];

echo $html_header;
?>
<script>
var wproductos;
function elegir_producto()
{
 pagina_prod="<?=encode_link('../general/productos2.php',array('onclickcargar'=>"window.opener.cargar()",'onclicksalir'=>'window.close()','cambiar'=>0)) ?>"
    	wproductos=window.open(pagina_prod,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');
}	    

function cargar()
{
 document.all.nuevo_id_prod.value=wproductos.document.all.id_producto.value;
 document.all.nuevo_prod.value=wproductos.document.all.descripcion.value;
 document.all.guardar_cambio.disabled=0;
 alert("El producto se cargó con éxito");
 wproductos.focus();
}

function control_nuevo_prod()
{
 return confirm('Está seguro que desea cambiar el producto \n<?=$producto_actual?>\n por el producto elegido?');
}
</script>
<br>
<form name="form1" action="cambios_productos_fila.php" method="POST">
 <input type="hidden" name="id_fila" value="<?=$id_fila?>">
 <input type="hidden" name="permiso_cambio" value="<?=$permiso_cambio?>">
 <table align="center" width="95%">
  <tr>
   <td colspan="2">
    Producto Original: <font color="Blue"><?=$producto_original?></font><hr>
   </td>
  </tr> 
  <tr>
   <td colspan="2">
    <b>Producto Actual para esta fila:</b> <font color="Red"><b><?=$producto_actual?></b></font>
    <br><br>
   </td>
  </tr>
  <?
  if($permiso_cambio)
  {?>
   <tr>
    <td>
     Producto
    </td>
    <td>
     <input type="hidden" name="nuevo_id_prod" value="">
     <input type="text" name="nuevo_prod" value="" readonly size="60" readonly>&nbsp;
     <input type="button" name="cambio_prod" value="Elegir Producto" onclick="elegir_producto()">
    </td>
   </tr>
   <tr> 
    <td colspan="2" align="center">
     <input type="submit" name="guardar_cambio" value="Confirmar cambio de Producto" disabled onclick="return control_nuevo_prod()">
    </td>
   </tr>
  <?
  }//de if($permiso_cambio)
  ?> 
 </table>
 <hr>
 <table align="center" width="100%">
  <tr id=mo>
   <td colspan="3">
    Cambios de productos realizados para la Fila
   </td>
  </tr>
  <tr id=ma>
   <td>
    Producto
   </td>
   <td title="Fecha de cambio del producto">
    Fecha
   </td>
   <td>
    Usuario
   </td>
  </tr>
  <?
  if($cambios_prod->fields["fecha_cambio"]=="")
  {?>
   <tr <?echo atrib_tr($bgcolor_out)?>>
    <td colspan="3" align="center">
     <h5>No se han realizado cambios de productos para esta fila</h5>
    </td>
   </tr>
  <?
  }
  else 
  {
   while (!$cambios_prod->EOF)
   {?>
   	<tr <?echo atrib_tr($bgcolor_out)?> >
  	 <td>
  	  <?=$cambios_prod->fields["desc_gral"]?>
  	 </td>
  	 <td>
  	  <?=fecha($cambios_prod->fields["fecha_cambio"])." ".Hora($cambios_prod->fields["fecha_cambio"])?>
  	 </td>
  	 <td>
  	  <?=$cambios_prod->fields["usuario_cambio"]?>
  	 </td>
  	</tr>
    <?
    $cambios_prod->MoveNext();
   }//de while(!$cambios_prod->EOF)
  }//del else de  if($cambios_prod->RecordCount()==0)
   ?>
  
 </table>
<div align="center">
<?
if($parametros["permiso_cambio"]==1)
 $cerrar="window.opener.location.reload();window.close()";
else 
 $cerrar="window.close()"; 
?>
 <input type="button" name="Cerrar" value="Cerrar" onclick="<?=$cerrar?>">
</div> 
</form>
</body>
</html>