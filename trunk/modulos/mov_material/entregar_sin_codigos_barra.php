<?
/*
Autor: MAC
Fecha: 03/01/06

MODIFICADA POR
$Author: fernando $
$Revision: 1.11 $
$Date: 2007/01/19 20:58:43 $

*/
require_once("../../config.php");



$desde_pagina=$parametros["desde_pagina"] or $desde_pagina=$_POST["desde_pagina"];

//estas variables tienen que tomar los valores desde ord_compra_fin.php
$producto_nombre=$parametros["producto_nombre"] or $producto_nombre=$_POST["producto_nombre"];
$total_comprado=$parametros["total_comprado"] or $total_comprado=$_POST["total_comprado"];
$total_entregado=$parametros["total_entregado"] or $total_entregado=$_POST["total_entregado"];
$id_prod_esp=$parametros["id_prod_esp"] or $id_prod_esp=$_POST["id_prod_esp"];
$id_movimiento=$parametros["id_movimiento"] or $id_movimiento=$_POST["id_movimiento"];
$id_detalle_movimiento=$parametros["id_detalle_movimiento"] or $id_detalle_movimiento=$_POST["id_detalle_movimiento"];
$es_pedido_material=$parametros["es_pedido_material"] or $es_pedido_material=$_POST["es_pedido_material"];
$deposito_origen=$parametros["deposito_origen"] or $deposito_origen=$_POST["deposito_origen"];
$deposito_destino=$parametros["deposito_destino"] or $deposito_destino=$_POST["deposito_destino"];
$id_licitacion=$parametros["id_licitacion"] or $id_licitacion=$_POST["id_licitacion"];
$cant_insertada=$parametros["cant_entregar"] or $cant_insertada=$_POST["cant_entregar"];
$nro_caso=$parametros["nro_caso"] or $nro_caso=$_POST["nro_caso"];
$id_proveedor_rma=$parametros["id_proveedor_rma"] or $id_proveedor_rma=$_POST["select_proveedor_rma"];
$rma_san_luis = $parametros["rma_san_luis"] or $rma_san_luis = $_POST["rma_san_luis"];

//traemos el id del deposito de RMA
    ($rma_san_luis)? $nombre_rma = "RMA-Produccion-San Luis": $nombre_rma = "RMA";
	$query="select id_deposito from depositos where nombre='$nombre_rma'";
	$st_rma=sql($query,"<br>Error al traer el id de deposito RMA<br>") or fin_pagina();
	$id_stock_rma=$st_rma->fields["id_deposito"];


if($es_pedido_material==1)
 $titulo_pagina="Pedido de Material";
else
 $titulo_pagina="Movimiento de Material";

 echo " deposito_origen : $deposito_origen , deposito_destino : $deposito_destino";
 
if($_POST["guardar"]=="Guardar")
{
 require_once("func.php");
 entregar_material_sin_cb($id_movimiento,$es_pedido_material,$id_detalle_movimiento,$cant_insertada,$id_prod_esp,$deposito_origen,$total_comprado,$id_licitacion,$nro_caso,$id_proveedor_rma,$deposito_destino);

 $link_detalle = encode_link("detalle_movimiento.php",array("pagina"=>"listado","id"=>$id_movimiento));
 echo "<script>window.opener.location.href='$link_detalle';window.close();</script>";
 echo "<center><b>Los productos se entregaron con éxito</b></center>";
}//DE if($_POST["guardar"]=="Guardar")

echo $html_header;

$cantidad_ingresar=$total_comprado - $total_entregado;

?>
<script>
function control_cantidad(controlar_prov_rma)
{
  if(typeof(document.all.stock_entrega)!="undefined" && document.all.stock_entrega.value==-1)
  {alert('Debe elegir un stock desde donde se entregarán los productos');
   return false;
  }
  if(document.all.cant_entregar.value=="" || parseInt(document.all.cant_entregar.value)<1)
  {alert('Debe ingresar una cantidad válida para entregar');
   return false
  }
  if(parseInt(document.all.cant_entregar.value)><?=$cantidad_ingresar?>)
  {alert('La cantidad ingresada supera la cantidad máxima a entregar');
   return false
  }

  if(controlar_prov_rma && document.all.select_proveedor_rma.value==-1)
  {alert('Debe elegir un proveedor para agregar los productos a RMA');
   return false;
  }

  return true;
}//de function control_cantidad()
</script>

<form name="form1" method="POST" action="<? echo $_SERVER['SCRIPT_NAME']; ?>">
 <input type="hidden" name="id_prod_esp" value="<?=$id_prod_esp?>">
 <input type="hidden" name="total_comprado" value="<?=$total_comprado?>">
 <input type="hidden" name="total_entregado" value="<?=$total_entregado?>">
 <input type="hidden" name="id_movimiento" value="<?=$id_movimiento?>">
 <input type="hidden" name="id_detalle_movimiento" value="<?=$id_detalle_movimiento?>">
 <input type="hidden" name="es_pedido_material" value="<?=$es_pedido_material?>">
 <input type="hidden" name="desde_pagina" value="<?=$desde_pagina?>">
 <input type="hidden" name="producto_nombre" value="<?=$producto_nombre?>">
 <input type="hidden" name="deposito_origen" value="<?=$deposito_origen?>">
 <input type="hidden" name="deposito_destino" value="<?=$deposito_destino?>">
 <input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
 <input type="hidden" name="nro_caso" value="<?=$nro_caso?>">
 <input type="hidden" name="rma_san_luis" value="<?=$rma_san_luis?>">

 <?
//traemos los proveedores activos para RMA, en caso de que el PM sea asociado a caso o el deposito destino sea RMA
if($nro_caso!="" || $deposito_destino==$id_stock_rma)
{
	//traemos el id del proveedor RMA de esta fila que se eligio, en caso de que exista
	$query="select id_proveedor,razon_social from detalle_movimiento join general.proveedor using(id_proveedor)
	        where id_detalle_movimiento=$id_detalle_movimiento";
	$prov_rma=sql($query,"<br>Error al traer el proveedor de RMA elegido<br>") or fin_pagina();
    $id_proveedor_rma=$prov_rma->fields["id_proveedor"];
    $razon_social_rma=$prov_rma->fields["razon_social"];

    if($total_entregado=="" && $id_proveedor_rma=="")
    {
		$query="select id_proveedor,razon_social from proveedor where activo='TRUE' order by razon_social";
		$proveedores=sql($query,"<br>Error al traer los proveedores de RMA<br>") or fin_pagina();
		$prov_rma_completo=1;
    }
	$generar_combo_prov_rma=1;
}//de if($nro_caso!="" || $deposito_destino==$id_stock_rma)
?>

 <table width="100%" align="center" border="1">
  <tr>
   <td id="ma">
    Ingrese la cantidad a entregar sin códigos de barra para el producto:<br>"<?=$producto_nombre?>"
   </td>
  </tr>
  <tr>
   <td>
    <table width="100%">
	  <tr>
	   <td>
	    <b>Cantidad de Nº a ingresar: <?=$cantidad_ingresar?></b>
	   </td>
	   <td align="right">
	    <?if($generar_combo_prov_rma==1)
	      {?>
	       <b>Proveedor para RMA</b>
	       <select name="select_proveedor_rma" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();">
	        <?
	        //si hay que generar todo el combo con todos los proveedores
	        if($prov_rma_completo==1)
	        {?>
	        	<option value=-1>Seleccione...</option>
	        	<?
	        	while (!$proveedores->EOF)
		        {?>
		        	<option value="<?=$proveedores->fields["id_proveedor"]?>" <?if($proveedores->fields["id_proveedor"]==$id_proveedor_rma) echo "selected"?>>
		        	  <?=$proveedores->fields["razon_social"]?>
		        	</option>

		         <?
		         	$proveedores->MoveNext();
		        }//de while(!$proveedores->EOF)
	        }//de if($prov_rma_completo==1)
	        else//sino, ya se habia recibido algo, entonces solo ponemos el proveedor elegido la primera vez
	        {?>
	        	<option value="<?=$id_proveedor_rma?>" selected>
		        	  <?=$razon_social_rma?>
		       	</option>
		       <?
	        }//del else de if($prov_rma_completo==1)
	        ?>
	       </select>
	       <?
	      }//de if($generar_combo_prov_rma==1)
	      else
	       echo "&nbsp;";
	    ?>
	   </td>
	  </tr>
	</table>
   </td>
  </tr>
  <tr>
   <td>
    Cantidad a entregar <input type="text" name="cant_entregar" size=8 onkeypress="return filtrar_teclas(event,'0123456789');">
   </td>
  </tr>
 </table>
 <table width="100%" align="center">
  <tr>
   <td align="center">
    <input type="submit" name="guardar" value="Guardar" <?if($cantidad_ingresar<=0 || $total_entregado==$total_comprado) echo "disabled"?> onclick="return control_cantidad(<?=$generar_combo_prov_rma?>)">
   </td>
   <td align="center">
    <input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
   </td>
  </tr>
 </table>
  <?if($cantidad_ingresar<=0 || $total_entregado==$total_comprado)
    echo "<h5>Todos los productos de esta fila fueron entregados</h5>";
 ?>
</from>
</body>
</html>