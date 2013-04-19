<?
/*
$Author: fernando $
$Revision: 1.11 $
$Date: 2006/07/08 19:32:42 $
*/

require_once("../../config.php");

$id_en_produccion=$parametro["id_en_produccion"]  or $id_en_produccion=$parametros["id_en_produccion"];
$id_prod_esp=$_POST["id_prod_esp"];
$id_licitacion=$_POST["id_licitacion"];

if ($_POST["descontar"]=="Aceptar")
{
	include_once("../stock/funciones.php");

      $cantidad=$_POST["cantidad_descontar"];
      $id_en_stock=$_POST["id_en_stock"];

      $db->StartTrans();

      $fecha_modif=date("Y-m-d H:i:s",mktime());


     //buscamos el id del stock Produccion
     $query="select id_deposito from depositos where nombre='Produccion'";
	 $result=sql($query,"<br>Error al traer el id del deposito de produccion<br>") or fin_pagina();
	 $id_deposito_en_produccion=$result->fields["id_deposito"];

    switch ($_POST["accion_stock"])
    {

        case 1: //descuenta de stock en producción manualmente
	              $comentario="Descuento Manual de Stock en Producción, asociado a la licitación Nº $id_licitacion";
				  if ($_POST["motivo"])  $comentario.=" - Motivo: ".$_POST["motivo"];
				  
				  descontar_producto_en_produccion($id_prod_esp,$id_licitacion,$cantidad,$comentario);
                  break;
        case 3:
                //descontamos el producto con la cantidad indicada de Stock en Producción
                $comentario="Descuento de Stock en Producción para pasarlo a RMA. Producto perteneciente a la licitación Nº $id_licitacion";
 			    if ($_POST["motivo"])  $comentario.=" - Motivo: ".$_POST["motivo"];
				descontar_producto_en_produccion($id_prod_esp,$id_licitacion,$cantidad,$comentario);

				//Y lo agregamos a Stock RMA
				$comentario="Ingreso a RMA por descuento desde Stock de producción para la licitación Nº $id_licitacion";
				$tipo_log="Creacion desde Stock en Producción de la Licitación Nº $id_licitacion";
				$id_proveedor_rma=$_POST["select_proveedor_rma"];
				$nro_rma=incrementar_stock_rma($id_prod_esp,$cantidad,"",$comentario,"","","",$id_proveedor_rma,"","","","null","","","",$tipo_log,$id_licitacion);
                enviar_mail_produccion_rma($nro_rma,$id_licitacion,$id_proveedor_rma,$id_prod_esp,$cantidad);
				break;
    }//de switch ($_POST["accion_stock"])

    $db->CompleteTrans();

    $link_list=encode_link("stock_produccion.php",array("msg"=>"Se descontó con éxito la cantidad: $cantidad, para el producto ".$_POST["nbre_prod"]." de la Licitación Nº $id_licitacion"));
	header("Location:$link_list");

}//de if ($_POST["descontar"]=="Aceptar")


$sql="select ep.id_en_produccion,ep.id_licitacion,ep.cantidad as cantidad_disponible,p.id_prod_esp,
                     p.marca,p.modelo,p.descripcion ,es.id_en_stock
             from stock.en_produccion ep
             join stock.en_stock  es using  (id_en_stock)
             join general.producto_especifico p using (id_prod_esp)
             where id_en_produccion=$id_en_produccion
             ";
$result=sql($sql)  or fin_pagina("Error al traer datos del producto stcok de producción");

$id_licitacion=$result->fields["id_licitacion"];
$cantidad_disponible=$result->fields["cantidad_disponible"];
$marca=$result->fields["marca"];
$modelo=$result->fields["modelo"];
$descripcion=$result->fields["descripcion"];
$id_prod_esp=$result->fields["id_prod_esp"];
$id_en_stock=$result->fields["id_en_stock"];

echo $html_header;
?>
<script>
 function control_datos()
 {
 	if(document.all.cantidad_descontar.value==0)
 	{
 	  alert("Debe Ingresar una cantidad válida");
 	  return false;
 	}
    if (parseInt(document.all.cantidad_descontar.value)>parseInt(document.all.cantidad_disponible.value))
    {
      	alert('La cantidad que desea descontar es mayor que la cantidad actual');
      	return false;
    }

    if(typeof(document.all.accion_stock[1])!="undefined" && document.all.accion_stock[1].checked)
    {
      	if(document.all.select_proveedor_rma.value==-1)
      	{
      		alert('Debe elegir un proveedor para generar el RMA');
      		return false;
      	}
    }


 	return true;
 }//de control_datos()
</script>
<form name=form1 method=post>
<input type=hidden name=id_prod_esp value="<?=$id_prod_esp?>">
<input type=hidden name=id_licitacion value="<?=$id_licitacion?>">
<input type=hidden name=id_en_stock value="<?=$id_en_stock?>">
<input type=hidden name=cantidad_disponible value="<?=$cantidad_disponible?>">
<input type=hidden name=nbre_prod value="<?=$descripcion?>">

  <table width=80% align=center class=bordes>
    <tr id=mo><td>Descontar Producto al Stock <?=$deposito?></td></tr>
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
               <td <?=atrib_tr()?>><b>ASOCIADO A ID: </b></td>
               <td align=left><font color="blue"><b><?=$id_licitacion?></b></font></td>
            </tr>
            <tr <?=atrib_tr()?>>
              <td>&nbsp;</td>
            </td>
            <tr>
               <td <?=atrib_tr()?>><b>CANTIDAD</b></td>
               <td align=left><font color="Blue"><b><?=$cantidad_disponible?></b></td>
            </tr>
            <tr>
               <td <?=atrib_tr()?>><b>CANTIDAD A DESCONTAR</b></td>
               <td><input type=text name=cantidad_descontar value="" size=4 onchange="control_numero(this,'Cantidad a Ingresar')"></td>
            </tr>
			<tr>
			   <td valign=top <?=atrib_tr()?>><b>MOTIVO DEL DESCUENTO MANUAL</b></td>
			   <td>
			     <textarea name=motivo rows=4 style="width:95%"></textarea>
			   </td>
			</tr>
            <tr>
              <td colspan=2>&nbsp;</td>
            </tr>
            <?
            $mostrar_des_manual=$mostrar_des_rma=0;
            if (permisos_check("inicio","permiso_descontar_produccion_manual"))
              $mostrar_des_manual=1;
            if(permisos_check("inicio","permiso_pasar_productos_produccion_rma"))
              $mostrar_des_rma=1;

            //si tiene permiso para algunos de los dos tipos de descuentos de stock en produccion, mostramos esta parte
            if($mostrar_des_manual || $mostrar_des_rma)
            {
	            ?>
	            <tr>
	             <td colspan="2">
		          <table align="center" width="100%" class="bordes">
		            <tr>
		               <td colspan=2 align=center id=ma>Acción a tomar con el Stock en Producción</td>
		            </tr>
		            <tr>
		              <td colspan=2 align=left>
		                <?if($mostrar_des_manual)
		                  {?>
		                    <input type=radio name=accion_stock value=1 checked
		                    	onclick="if(this.checked)document.all.div_rma.style.display='none'"
		                    >&nbsp; <b>Descontar de Stock en Producción la cantidad ingresada</b><br>
		                  <?
		                  }
		                  /*<input type=radio name=accion_stock value=2 <?=$checked_2?> >&nbsp; <b>Sumar Al Stock en Producción la cantidad descontada</b><br>*/
		                  if($mostrar_des_rma)
		                  {
		                  	$query="select id_proveedor,razon_social from proveedor where activo='TRUE' order by razon_social";
		 					$proveedores=sql($query,"<br>Error al traer los proveedores de RMA<br>") or fin_pagina();
		                  	?>
		                    <input type=radio name=accion_stock value=3
		                     	onclick="if(this.checked)document.all.div_rma.style.display='block'"
		                    >&nbsp; <b>Descontar el producto de Stock en Producción y agregarlo a Stock de RMA</b><br>
		                    <div style="display:none" id="div_rma" class="bordes">
		                     <b>Proveedor para RMA</b>
						       <select name="select_proveedor_rma" onKeypress="buscar_op(this);" onblur="borrar_buffer();" onclick="borrar_buffer();">
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
						        ?>
						       </select>
		                    </div>
		                  <?
		                  }
		                ?>
		              </td>
		            </tr>
		          </table>
		         </td>
		       </tr>
	         <?
            }//de if($mostrar_des_manual || $mostrar_des_rma)
	        ?>
         </table>
       </td>
    </tr>
    <tr>
       <td align=center>
          <input type=submit name=descontar value="Aceptar" onclick="return control_datos()">
          &nbsp;
          <input type=button name=cancelar value="Volver" onclick="window.location='stock_produccion.php'">
       </td>
    </tr>
  </table>
</form>
<?
echo fin_pagina();
?>
