<?php
/*
Autor: Fernando

MODIFICADA POR
$Author: fernando $
$Revision: 1.3 $
$Date: 2006/01/10 20:06:44 $
*/

/*
Pagina provisoria para agregar stock a en produccion solamente
*/

require_once("../../config.php");
require_once("funciones.php");
$elegir_producto=$_POST["elegir_producto"];
$id_prod_esp=$_POST["id_prod_esp"] or $id_pro_esp=$parametros["id_prod_esp"];
$id_deposito=13;
$deposito=$_POST["deposito"] or $deposito=$parametros["deposito"];


$onclick_cargar="window.opener.document.form1.id_prod_esp.value=document.all.id_producto_seleccionado.value;
                 window.opener.document.form1.elegir_producto.value=1;
                 window.opener.document.form1.submit();
                 window.close();";
$link_elegir=encode_link("../productos/listado_productos_especificos.php",array("pagina_viene"=>"stock_agregar.php","onclick_cargar"=>$onclick_cargar));
$link_listado=encode_link("stock_produccion.php",array());

if ($_POST["aceptar"]){
    //ingreso al stock de forma manual
    $db->StartTrans();

    $cantidad=$_POST["cantidad_ingresar"];
    $id_licitacion=$_POST["id_licitacion"];
    $comentario="Ingreso manual de Stock";
    //obtenemos el id de tipo de movimiento: "Ingreso manual de stock"
    $query="select id_tipo_movimiento from tipo_movimiento where nombre='Ingreso manual de stock'";
    $tipo_movimiento=sql($query,"<br>Error al traer el id del movimiento de ingreso manual<br>") or fin_pagina();
    $id_tipo_movimiento=$tipo_movimiento->fields["id_tipo_movimiento"];
    //agregar_stock($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento);
    agregar_a_en_produccion($id_prod_esp,$cantidad,$comentario,$id_licitacion);   
    
    $db->CompleteTrans();

    ?>
    <script>
       //window.opener.document.location.href='<?=$link_listado?>';
       //window.close();
    </script>
    <?
    
   }

if ($id_prod_esp){
    //selecciono el producto para modificar el stock
    $sql="select producto_especifico.*,en_stock.cant_disp
                 from producto_especifico
                 left join en_stock using (id_prod_esp)
                 where id_prod_esp=$id_prod_esp ";
    $producto=sql($sql) or fin_pagina();
    $marca=$producto->fields["marca"];
    $modelo=$producto->fields["modelo"];
    $precio_stock=$producto->fields["precio_stock"];
    $descripcion=$producto->fields["descripcion"];

    if ($producto->fields["cant_disp"])
                            $cantidad_diponible=$producto->fields["cant_disp"];
                            else $cantidad_diponible=0;
   }

echo $html_header;
?>
<script>
 function control_datos()
 {
 	if(document.all.cantidad_ingresar.value<=0)
 	{
 	  alert("Debe Ingresar una cantidad válida");
 	  return false;
 	}
 	
 	return true;
 }//de control_datos()
</script>
<form name=form1 method=post>
<input type=hidden name=id_prod_esp value="<?=$id_prod_esp?>">
<input type=hidden name=id_deposito value="<?=$id_deposito?>">
<input type=hidden name=deposito value="<?=$deposito?>">
<input type=hidden name=pagina_oc value="<?=$pagina_oc?>">
<input type=hidden name=elegir_producto value="<?=$elegir_producto?>">

  <table width=80% align=center class=bordes>
    <tr>
    <td><input type=button name=elegir_producto value="Elegir Producto" onclick="window.open('<?=$link_elegir?>')"></td>
    </tr>

    <tr id=mo><td>Agregar Producto al Stock <?=$deposito?></td></tr>
    <tr id=ma><td>Información del Producto</td></tr>
    <tr>
       <td align=center>
         <table width=100% align=Center class="bordes">
            <tr>
               <td width=40% <?=atrib_tr()?>><b>MARCA:</b></td>
               <td  align=left ><font color="Blue"><b><?=$marca?></b></td>
            </tr>
            <tr>
               <td <?=atrib_tr()?>><b>MODELO:</b></td>
               <td align=left align=left><font color="Blue"><b><?=$modelo?></b></td>
            </tr>
            <tr>
               <td <?=atrib_tr()?>><b>DESCRIPCIÓN:</b></td>
               <td align=left><font color="Blue"><b><?=$descripcion?></b></td>
            </tr>
            <tr>
               <td <?=atrib_tr()?>><b>CANTIDAD DISPONIBLE EN TODOS LOS STOCKS:</b></td>
               <td align=left><font color="Blue"><b><?=$cantidad_diponible?></b></td>
            </tr>
            <tr>
               <td <?=atrib_tr()?>><b>CANTIDAD A INGRESAR:</b></td>
               <td><input type=text name=cantidad_ingresar value="" size=4 onchange="control_numero(this,'Cantidad a Ingresar')"></td>
            </tr>
            <tr>
               <td <?=atrib_tr()?>><b>ID LICITACION</b></td>
               <td><input type=text name=id_licitacion value="" size=4 onchange="control_numero(this,'Cantidad a Ingresar')"></td>
            </tr>
            
         </table>
       </td>
    </tr>
    <tr>
       <td align=center>
       <hr>
          <input type=submit name=aceptar value=Aceptar onclick="return control_datos()">
          &nbsp;
          <input type=button name=cancelar value=Cancelar onclick="window.close()">
       </td>
    </tr>
  </table>
</form>
<?
echo fin_pagina();
?>