<?php
/*
AUTOR: MAC 
FECHA: 06/07/04

$Author: mari $
$Revision: 1.9 $
$Date: 2005/05/23 20:54:48 $
*/
require_once("../../config.php");


$id_proveedor=$parametros['id_proveedor'] or $id_proveedor=$_POST['id_proveedor'];
$id_producto=$parametros['id_producto'] or $id_producto=$_POST['id_producto'];
$id_deposito=$parametros['id_deposito'] or $id_deposito=$_POST['id_deposito'];
$id_info_rma=$parametros['id_info_rma'] or $id_info_rma=$_POST['id_info_rma'];

//traemos todos los depositos de tipo stock posibles, para generar la tabla
$depositos=$db->Execute("select * from depositos where tipo=0") or die($db->ErrorMsg()."<br>Error al traer los depositos");

if ($_POST['continuar']=="Continuar") 
{include("funciones.php");
 $cantidad_1=$_POST['cantidad_desc'];
 //si esta clickeado el radio de recibir las partes, se agrega a los stocks 
 //elegidos los productos
 //if($_POST['nc_o_stock']==1)
 switch ($_POST['nc_o_stock'])
 {case 1: $string_depositos="";
          $db->StartTrans();
          //primero descontamos el producto del RMA
          if(descontar_stock($cantidad_1,$id_producto,$id_proveedor,$id_deposito,$id_info_rma,0))
            {//luego incrementamos los stocks correspondientes
             incrementar_stock($id_producto,$id_proveedor);
            }  
          //finalmente registramos el usuario , fecha y deposito/s
          $fecha_hoy=date("Y-m-d H:i:s",mktime());
          $query="update info_rma set user_historial='".$_ses_user['name']."',fecha_historial='$fecha_hoy',deposito='$string_depositos' where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
          if($db->Execute($query) or die($db->ErrorMsg()))
           $msg="<center><b>Los productos se descontaron de RMA y se agregaron a los stocks seleccionados</b></center>";
          else 
           $msg="<center><b>Los productos no se pudieron descontar de RMA o agregar a los stocks seleccionados</b></center>";
          $db->CompleteTrans();  
          $link=encode_link("stock_descontar_rma.php",array("msg"=>$msg,"id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_deposito"=>$id_deposito,"id_info_rma"=>$id_info_rma,"pagina_listado"=>"historial","cant_cb"=>$cantidad_1));
          header("Location:$link");          
          break;   
 //si esta clickeado el radio de generar nota de credito, se desvia a la nota
 //de credito
 case 2:$descripcion="Nota de Crédito generada a partir del Modulo RMA";
        $link=encode_link("../ord_compra/nota_credito.php",array("descripcion"=>$descripcion,"id_proveedor"=>$id_proveedor,"id_deposito"=>$id_deposito,"id_producto"=>$id_producto,"id_info_rma"=>$id_info_rma,"cantidad"=>$cantidad_1,"pagina"=>"RMA","pagina_volver"=>"../stock/stock_descontar_rma.php"));  
        header("location: $link");	       
        break;
 case 3:$db->StartTrans();
        if(descontar_stock($cantidad_1,$id_producto,$id_proveedor,$id_deposito,$id_info_rma,2))
        {//luego incrementamos los stocks correspondientes
         incrementar_stock($id_producto,$id_proveedor);
        }  
        $fecha_hoy=date("Y-m-d H:i:s",mktime());
        $sql="update info_rma set user_historial='".$_ses_user['name']."',fecha_historial='$fecha_hoy',garantia_vencida=1 where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
        $consulta_sql=sql($sql) or fin_pagina();
        $db->CompleteTrans();
        $link=encode_link("stock_descontar_rma.php",array("msg"=>$msg,"id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_deposito"=>$id_deposito,"id_info_rma"=>$id_info_rma,"pagina_listado"=>"historial","cant_cb"=>$cantidad_1));
        header("Location:$link");          
        break;   
                
 
 }//del switch
}//del if general

echo $html_header;

//traemos los datos del producto en RMA
$sql="
   select info_rma.cantidad, stock.id_producto,stock.comentario_inventario,
          stock.id_deposito,productos.tipo,
          productos.desc_gral,productos.marca,productos.modelo,
          productos.precio_stock,
          tipos_prod.descripcion,proveedor.razon_social,info_rma.nrocaso,
          info_rma.nro_ordenp,info_rma.nro_ordenc,info_rma.id_nota_credito,
          info_rma.deposito,info_rma.user_historial,info_rma.fecha_historial
    from stock
   join general.productos using(id_producto)
   join general.depositos using(id_deposito)
   join general.tipos_prod on (tipos_prod.codigo=productos.tipo)
   join info_rma using (id_deposito,id_producto,id_proveedor)
   join proveedor using (id_proveedor)
   where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
$datos=$db->Execute($sql) or die($db->ErrorMsg()."<br>Error al traer los datos del producto en RMA<BR>$sql");   

echo $msg;
if($datos->fields['fecha_historial']!="" || $datos->fields['user_historial']!="")
 $disabled_hist="disabled";
?>
<script>
//funcion para controlar que las cantidades ingresadas en los stock sea igual a 
//la cantidad del producto en stock RMA
function control_cantidades()
{var total_cant=0;
 var cantidad=<?=$datos->fields['cantidad']?>;
 //la funcion controla solo en el caso de que este seleccionado
 //el radiobutton de "Se recibio el producto"
 if(document.all.nc_o_stock[0].checked==1)
 {//sumamos las cantidades de cada stock
  
  <?
  $depositos->Move(0);
  while(!$depositos->EOF)
  {?>
    total_cant+=parseInt(document.all.cant_inc_<?=$depositos->fields['id_deposito']?>.value);
    <?  
    $depositos->MoveNext();
  }	
  ?>
  //y lo comparamos con cantidad
  if(total_cant!=cantidad)
  {alert('Las cantidad del producto no coincide con las cantidades que intenta agregar a los depósitos');
   return false;
  }
  else
   return true;
 }
 else
  return true;  
}	
</script>
<br>
<table width="80%" align="center" border="1">
 <tr>
  <td>
   <table width="100%" align="center" cellpadding="3">
    <tr id=mo>
     <td colspan="2">
      <font size="3">Información del Producto</font>
     </td>
    </tr>
    <tr id=ma_sf>
     <td width="30%">
      <b>Descripción</b>
     </td>
     <td width="70%">
      <b><font color="Blue" size="2"> <?=$datos->fields['desc_gral']?></font></b>
     </td> 
    </tr> 
    <tr id=ma_sf>
     <td width="30%">
      <b>Cantidad</b>
     </td>
     <td width="70%">
      <b><font color="Blue" size="2"> <?=$datos->fields['cantidad']?></font></b>
     </td> 
    </tr> 
    <tr  id=ma_sf>
     <td width="30%">
      <b>Tipo</b>
     </td>
     <td width="70%">
      <b><font color="Blue" size="2"> <?=$datos->fields['descripcion']?></font></b>
     </td> 
    </tr> 
    <tr id=ma_sf>
     <td width="30%">
      <b>Proveedor</b>
     </td>
     <td width="70%">
      <b><font color="Blue" size="2"> <?=$datos->fields['razon_social']?></font></b>
     </td> 
    </tr>
    <tr id=ma_sf>
     <td width="30%">
      <b>Precio</b>
     </td>
     <td width="70%">
      <b><font color="Blue" size="2">U$S <?=formato_money($datos->fields['precio_stock'])?></font></b>
     </td> 
    </tr>
    <tr id=ma_sf>
     <td width="30%">
      <b>Nº Orden de Compra</b>
     </td>
     <td width="70%">
       <b><font color="Blue" size="2"> <?=$datos->fields['nro_ordenc']?></font></b>
     </td> 
    </tr> 
    <tr id=ma_sf>
     <td width="30%">
       <b>Nº Orden de Producción</b>
     </td>
     <td width="70%">
      <b><font color="Blue" size="2"> <?=$datos->fields['nro_ordenp']?></font></b>
     </td> 
    </tr>
    <tr id=ma_sf>
     <td width="30%">
      <b>Nº C.A.S</b>
     </td>
     <td width="70%">
      <b><font color="Blue" size="2"> <?=$datos->fields['nrocaso']?></font></b>
     </td> 
    </tr>   
   </table>
  </td>
 </tr> 
</table>  
<br>
<?
$link=encode_link("rma_historial.php",array("id_deposito"=>$id_deposito,"id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_info_rma"=>$id_info_rma));
?>
<form name="form1" action="<?=$link?>" method="POST"> 
<input type="hidden" name="cantidad_desc" value="<?=$datos->fields['cantidad']?>">
<table width="80%" align="center" border="1">
<tr>
<td>
<table width="80%" align="center">
 <tr>
     <td width="30%" align="center">
      <input type="radio" name="nc_o_stock" value="1" onclick="document.all.depositos.disabled=0"> Se Recibió el producto
     </td> 
     <td width="30%" align="center">
      <input type="radio" name="nc_o_stock" value="2" onclick="document.all.depositos.disabled=1"> Se Recibió una Nota de Crédito
     </td>
     <?if (permisos_check("inicio","permiso_garantia_rma"))
     {
     	?>
     <td width="40%" align="center">
      <input type="radio" name="nc_o_stock" value="3" onclick="document.all.depositos.disabled=1"> Garantía vencida, pérdida para Coradir
     </td>
     <?
     }
     ?>
 </tr>
</table> 
</td>
</tr>
<tr>
<td>
<table width="80%" id="depositos" disabled>
<tr>
 <td width="20%">
  <table border="1" cellpadding="0" cellspacing="0">
              <tr id="ma">
              <td>
               Cantidad
              </td>
              <td>
               Depósito
              </td>
             </tr> 
           <?         
           $depositos->Move(0); 
           $cant_filas=0;
           while(!$depositos->EOF)
           {
           if($cant_filas==3)
           {?>
             </table>
            </td>
            <td>
             <table border="1" cellpadding="0" cellspacing="0">
             <tr id="ma">
             <td>
              Cantidad
             </td>
             <td>
              Depósito
             </td>
            </tr> 
           <?
            $cant_filas=0;
           }
           ?> 
            <tr>
             <td align="center"> 
               <input type="text" size="4" name="cant_inc_<?=$depositos->fields['id_deposito']?>" value="0" onchange="if (control_numero(this,'Cantidad en <?=$depositos->fields['nombre']?>')) this.value=0;">
             </td>
             <td>
              <b><?=$depositos->fields['nombre']?></b>
             </td>
            </tr>  
           <?	
            $depositos->MoveNext();	
            ?>
             </td>
            </tr>
            <? 
            $cant_filas++;
           }//de while(!$depositos->EOF)
           ?>
            </td>
            </tr>
            </table>
           </td>
           </tr> 
          
         </table>
     </td>
    </tr>
   </table> 
  </td>
 </tr>
</table> 
<div align="center">
<?
//controlamos el permiso del boton
if(!permisos_check("inicio","permiso_dar_baja_rma"))
 $disabled_permiso="disabled";
 

?>
 <input name="id_proveedor" type="hidden" value="<?=$id_proveedor?>">
 <input name="id_producto" type="hidden" value="<?=$id_producto?>">
 <input name="id_deposito" type="hidden" value="<?=$id_deposito?>">
 <input name="id_info_rma" type="hidden" value="<?=$id_info_rma?>">
 <input type="submit" name="continuar" value="Continuar" <?=$disabled_permiso?> <?=$disabled_hist?> onclick="return control_cantidades()">
 <?
  $link=encode_link("stock_descontar_rma.php",array("id_deposito"=>$id_deposito,"id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_info_rma"=>$id_info_rma,"pagina_listado"=>"real"));
 ?>
 <input type="button" name="volver" value="Volver" onclick="document.location='<?=$link?>'"> 
</div>
</form>
</body>
</html>