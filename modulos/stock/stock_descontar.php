<?
/*
$Author: marco_canderle $
$Revision: 1.53 $
$Date: 2006/07/18 15:25:57 $
*/

//autoselec_proveedores_reserva

require_once("../../config.php");
require_once("funciones.php");


$id_prod_esp=$parametros["id_prod_esp"];
$id_deposito=$parametros["id_deposito"];
$usuario=$_ses_user["name"];

$pagina_listado=$_ses_stock['pagina_listado'];
$pagina_oc=$parametros['pagina_oc'];
$onclick_cargar= $parametros['onclick_cargar'];
$pagina_volver_muestra=$parametros['pagina_volver_muestra'];
$stock_selec=$parametros['stock_selec'];
$comentario_inventario=$_POST['comentario_inventario'];

   if ($_POST["guardar_ubicacion"]) {
       $ubicacion=$_POST["ubicacion"];
       $sql="update en_stock set ubicacion='$ubicacion' where id_deposito=$id_deposito and id_prod_esp=$id_prod_esp";
       sql($sql) or fin_pagina();
       $link=encode_link("$pagina_listado",array("exito"=>$exito));
       header("Location:$link");
   }


    if ($_POST["Aceptar"])
	{
		$db->StartTrans();
		$cantidad=$_POST["cantidad_descontar"];
		$comentario=$_POST["comentario"];
		$id_tipo_movimiento=2;//el tipo de movimiento es pendiente (la reserva se hace para descontar manualmente los productos
						      //pero deben autorizar o rechazar el descuento)
        //traemos el id del tipo de reserva: "Reserva manual de productos"
        $query="select id_tipo_reserva from tipo_reserva where nombre_tipo='Reserva Manual de Productos'";
        $tipo_reserva=sql($query,"<br>Error al traer el id del tipo de reserva<br>") or fin_pagina();
		$id_tipo_reserva=$tipo_reserva->fields["id_tipo_reserva"];
		reservar_stock($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_tipo_reserva);

 		$exito="Los productos a descontar se reservaron con éxito";
		$link=encode_link("$pagina_listado",array("exito"=>$exito));
		$db->CompleteTrans();
		if ($pagina_oc)
		  $cargar_script=1;
	    else
	      header("Location:$link");

	}//de if ($_POST["Aceptar"])

	if($_POST["liberar_reserva"]=="Liberar")
	{
		$db->StartTrans();

		$a_liberar=$_POST["index_liberar"];
		$cantidad_liberar=$_POST["cantidad_liberar"];
		$id_licitacion_liberar=$_POST["id_licitacion_$a_liberar"];
		$id_mov_material_liberar=$_POST["id_mov_material_$a_liberar"];
		$id_detalle_movimiento_liberar=$_POST["id_detalle_movimiento_$a_liberar"];
		$id_log_mov_stock_liberar=$_POST["id_log_mov_stock_$a_liberar"];
		$nrocaso_liberar=$_POST["nrocaso_$a_liberar"];
		$id_fila_liberar=$_POST["id_fila_$a_liberar"];

		$comentario="Se liberó la reserva hecha previamente";
		if($id_licitacion_liberar)
		 $comentario.=" para la licitación $id_licitacion_liberar";
		elseif($id_mov_material_liberar)
		 $comentario.=" para el Movimiento/Pedido de Material Nº $id_mov_material_liberar";
		elseif($id_log_mov_stock_liberar)
		 $comentario.=" para un descuento manual de productos";
		elseif($nrocaso_liberar)
		 $comentario.=" para el Caso de Servicio Técnico Nº $nrocaso_liberar";
		$comentario.=". Los productos pasaron a Stock disponible.";

		//traemos el id de tipo de movimiento con nombre="Liberación de Reserva"
		$query="select id_tipo_movimiento from tipo_movimiento where nombre='Liberación de Reserva'";
		$tipo_mov=sql($query,"<br>Error al traer el tipo de movimiento<br>") or fin_pagina();
		$id_tipo_movimiento=$tipo_mov->fields["id_tipo_movimiento"];

        //cancelamos la reserva hecha, y eso agrega al stock disponible la cantidad reservada
		cancelar_reserva($id_prod_esp,$cantidad_liberar,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila_liberar,$id_detalle_movimiento_liberar,$id_log_mov_stock_liberar);
		$db->CompleteTrans();

		echo "<center><b>La reserva se liberó con éxito</b></center>";
	}//de if($_POST["liberar_reserva"]=="Liberar")



 //recupero la informacion del producto del stock

  $sql=" select  productos.descripcion,productos.precio_stock,en_stock.ubicacion,
  				en_stock.cant_disp,en_stock.cant_reservada,en_stock.cant_a_confirmar
         from en_stock
         join general.producto_especifico productos using(id_prod_esp)
         join general.depositos using(id_deposito)
         where id_deposito=$id_deposito  and id_prod_esp=$id_prod_esp";

 $resultado=sql($sql,"<br>Error al traer info del producto en stock<br>") or fin_pagina();
 $cantidad=$resultado->RecordCount();


echo $html_header;

if ($cargar_script)
{
?>
	<script>
	  <?=$onclick_cargar;?>
	</script>
<?
}

?>
<script>

function control_datos()
{
	<?if(!$pagina_oc)
     {?>
	  if (parseInt(document.all.cantidad_descontar.value)<=0)
      {
        alert("Debe ingresar una cantidad válida para descontar");
        return false;
      }

      if(parseInt(document.all.cantidad_descontar.value)>parseInt(document.all.cant_disp.value))
      {
      	alert("La cantidad a descontar es mayor que la actualmente disponible");
      	return false;
      }
     <?
     }//de if($pagina_oc)
     else
     {?>
      if (document.all.cant_reserv.value<=0)
      {
        alert("Debe ingresar una cantidad válida para reservar");
        return false;
      }

      if(parseInt(document.all.cant_reserv.value)>parseInt(document.all.cant_disp.value))
      {
        alert("La cantidad a reservar es mayor que la actualmente disponible");
        return false;
      }
      <?
      echo $onclick_cargar;
     }//del else de if($pagina_oc)
     ?>

     return true;
} //de la funcion que controla los datos

</script>
<br>
<?
 $resultado->move(0);
 $link=encode_link("stock_descontar.php",array("id_deposito"=>$id_deposito,"id_prod_esp"=>$id_prod_esp,"onclick_cargar"=>$onclick_cargar,"pagina_oc"=>$pagina_oc));
?>
<form name="descontar" action="<?=$link?>" method="POST">

<input type="hidden" name="cantidad" value="<?=$cantidad;?>"> <!--paso la variable cantidad al post-->


 <!--Hiddens se usan para cargar los valores correspondientes en la pagina de
  ordenes de compra  (y tambien en muestras)-->
 <input type="hidden" name="id_prod_esp" value="<?=$id_prod_esp?>">
 <input type="hidden" name="precio_producto" value="<?=number_format($resultado->fields["precio_stock"],"2",".","")?>">
 <input type="hidden" name="deposito" value="<?=$id_deposito?>">
 <input type="hidden" name="name_producto" value="<?=$resultado->fields["descripcion"]?>">
 <input type="hidden" name="precio" value="<?=$resultado->fields["precio_stock"]?>">

 <?//traemos el nombre del deposito
  $query="select depositos.nombre from general.depositos where id_deposito=$id_deposito";
  $dep_name=sql($query,"<br>Error al traer el nombre del deposito<br>") or fin_pagina();
 ?>

<table align="center" width="90%" border="1" cellspacing="0" bordercolor="#A3A3A3" cellpadding="0">
 <tr>
   <td  id="mo">Detalle de Producto en Stock <?=$dep_name->fields["nombre"]?></td>
 </tr>
 <tr>
 <td >
	     <table width=100% bgcolor=<?=$bgcolor_out?>>
	         <tr>
		        <td width="20%">Producto:</td>
		        <td width="80%"><b><?=$resultado->fields["descripcion"]?></b></td>
                <td>
                <?
                $link=encode_link("stock_excel.php",array("id_deposito"=>$id_deposito,"id_prod_esp"=>$id_prod_esp,"download"=>1));

                ?>
                <a target=_blank title='Bajar datos en un excel' href='<?=$link?>'>
                <img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' >
                </a>
                </td>
	           </tr>
	           <tr>
               <td width="20%">Precio de Stock:</td>
        	   <td width="80%">
        	    <b><font color="Red">U$S <?=number_format($resultado->fields["precio_stock"],"2",".","")?></font></b>
        	   </td>
        	  </tr>
              <tr>
                    <td>Cantidad Disponible</td>
                    <td>
                      <b><?=$resultado->fields["cant_disp"]?></b>
                      <input type="hidden" name="cant_disp" value="<?=$resultado->fields["cant_disp"]?>">
                    </td>
              </tr>
              <tr>
                    <td>Cantidad Reservada</td>
                    <td>
                      <b><?=$resultado->fields["cant_reservada"]?></b>
                      <input type="hidden" name="cant_reservada" value="<?=$resultado->fields["cant_reservada"]?>">
                    </td>
              </tr>
              <tr>
                    <td>Cantidad a Confirmar</td>
                    <td>
                      <b><?=$resultado->fields["cant_a_confirmar"]?></b>
                      <input type="hidden" name="cant_a_confirmar" value="<?=$resultado->fields["cant_a_confirmar"]?>">
                    </td>
              </tr>
              <tr>
              	<td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                    <td><b>Cantidad Total en Stock<b></td>
                    <td>
                      <b>
                      	<?
                      	$cant_total_prod=$resultado->fields["cant_disp"]+$resultado->fields["cant_reservada"]+$resultado->fields["cant_a_confirmar"];
                      	echo "<font color='red'>$cant_total_prod</font>";
                      	?>
                      </b>
                      <input type="hidden" name="cant_total" value="<?=$cant_total_prod?>">
                    </td>
              </tr>
              <tr>
               <td colspan="3">
                   <table width="100%" class="bordes">
	                 <tr>
	                  <td colspan=2 id="mo">Reservar productos</td>
	                 </tr>
	                 <tr>
	                   <?
	                   if($pagina_oc)
	                   {?>
	                     <td>Cantidad a Reservar</td>
	                     <td><input type=text name="cant_reserv" value="" size="4" onchange="control_numero(this,'Cantidad a Reservar')"></td>
	                   <?
	                   }
	                   else
	                   {?>
	                     <td>Cantidad a Descontar</td>
	                     <td><input type=text name="cantidad_descontar" value="" size="4" onchange="control_numero(this,'Cantidad a Descontar')"></td>
	                   <?
	                   }
	                   ?>
	                 </tr>
	                 <tr bgcolor=<?=$bgcolor_out?>>
	                    <td>
	                     Comentarios de la Reserva
	                    </td>
	                    <td>
	                     <textarea name="comentario" rows=3 cols=80></textarea>
	                    </td>
	                 </tr>
	            </table>
	         </td>
           </tr>
  	   </table>
    </td>
  </tr>
  <tr>
    <td align=center bgcolor=<?=$bgcolor_out?>><b>Ubicación</b></td>
  </tr>
  <tr>
    <td align=center bgcolor=<?=$bgcolor_out?>>
    <textarea rows=2 style="width:80%" name="ubicacion"><?=$resultado->fields["ubicacion"]?></textarea>
    </td>
  </tr>
  <tr>
     <td bgcolor=<?=$bgcolor_out?> align=center>
     <input type=submit name=guardar_ubicacion value="Guardar Ubicación">
     </td>
  </tr>
 </table>

<?

mostrar_ing_egr($id_prod_esp,$id_deposito);

include_once("funciones_stock_completo.php");

//hacemos que solo muestre el detalle para el stock seleccionado, a traves del filtro
$filtro="id_deposito=$id_deposito";

if($pagina_oc=="")
 $mostrar_liberar=1;
else
 $mostrar_liberar=1;
mostrar_reservas_stock($id_prod_esp,$filtro,$mostrar_liberar);
?>
<input type="hidden" name="index_liberar" value="">
<input type="hidden" name="cantidad_liberar" value="">
<?
//mostramos el detalle de los productos a confirmar, si hay
mostrar_a_confirmar_stock($id_prod_esp,$filtro);

?>

<table align="center" border="0">
 <tr>
 <?if ($pagina_oc)
   {?>
     <td align=center>
      <input name=aceptar type=button value="Aceptar" onclick="return control_datos()">
     </td>
   <?
   }
   else
   {?>
     <td align="center">
      <input name="Aceptar" type="submit" value="Aceptar"  onclick="return control_datos();">
     </td>
   <?
   }

   if($pagina_volver_muestra==1)
     $link_volver=encode_link('selec_prod_muestra.php',array('onclick_cargar'=>"$onclick_cargar",'cambiar'=>0,'pagina_oc'=>1,'pagina_volver_muestra'=>$pagina_volver_muestra,'stock_selec'=>$stock_selec));
   else
     $link_volver=encode_link("listado_depositos.php",array("id_deposito"=>$id_deposito,"deposito"=>$deposito,"cmd"=>$cmd,"pagina_oc"=>$pagina_oc,"onclick_cargar"=>$onclick_cargar));;
   ?>
   <td align="center"><input name="Volver" type="button" value="Volver" onclick="document.location='<?=$link_volver?>'"></td>
 </tr>
</table>
<?
if($pagina_oc!="")
{
?>
     <div align="center">
      <input type="button" name="Salir" value="Salir" onclick="window.close()">
     </div>
<?
}
?>
</form>
</body>
</html>
<?
echo fin_pagina();
?>