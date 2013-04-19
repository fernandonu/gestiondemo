<?
/*
Autor: MAC

MODIFICADA POR
$Author: mari $
$Revision: 1.2 $
$Date: 2006/05/03 16:03:09 $
*/
require_once("../../config.php");
require_once("fns.php");

/***********************************************************************************
 EN ESTE ARCHIVO SE DEBEN INCLUIR TODAS LAS EJECUCIONES DE BOTONES PARA ESTA PAGINA*/
include("proc_recepciones.php");
/*POR FAVOR RESPETEN ESTE FORMATO DE PAGINA
************************************************************************************/

/****************************************************************
Funcion que genera los campos necesarios para especificar la
recepcion de productos. Solo se utiliza cuando el proveedor NO ES
un Stock.
*****************************************************************/
function generar_form_recepcion($nro_orden)
{
	global $db,$permiso,$datos_orden,$es_stock,$flag_stock,$parametros,
	       $id_proveedor,$filas_rec_ent,$filas_cambios_prod,$datos_depositos,$fecha_recepcion,$fecha_subida_gestion3;

	$query="select  	filas.id_fila,filas.id_prod_esp,filas.id_producto,filas.descripcion_prod,
		                filas.desc_adic,filas.cantidad_fila,filas.precio_unitario,filas.es_agregado,
		                rec_ent.id_recibido,rec_ent.id_deposito,depositos.nombre as deposito_nombre,rec_ent.observaciones,
		                case when rec_ent.cantidad is null then 0
			                else rec_ent.cantidad
		                end as cantidad_recibida
    		from (
	    		      select id_fila,id_prod_esp,id_producto,descripcion_prod,desc_adic,cantidad as cantidad_fila,precio_unitario,es_agregado
	       			  from compras.fila
	                  where es_agregado isnull or es_agregado<>1
                 ) as filas
            left join
	             (    select id_recibido,id_fila,id_deposito,cantidad,observaciones
	                  from compras.recibido_entregado
	                  where ent_rec=1
                 )as rec_ent
            using(id_fila)
            left join depositos using(id_deposito)
            where id_fila in (select id_fila from compras.fila where nro_orden=$nro_orden)
            order by id_producto";

	$datos_recibidos=sql($query,"<br>Error al traer los datos de la recepción<br>") or fin_pagina();

	?>

	<script>
	var wrecibir_prod=new Object();
	wrecibir_prod.closed=1;
	function elegir_producto_recibido(id_fila)
	{
	   var producto=eval("document.all.prod_rec_"+id_fila);
	   var id_producto=eval("document.all.id_prod_rec_"+id_fila);
	   producto.value=wrecibir_prod.document.all.nombre_producto_elegido.value;
	   id_producto.value=wrecibir_prod.document.all.id_producto_seleccionado.value;
	   wrecibir_prod.close();

	}//de function elegir_producto_recibido(id_fila)


	//funcion que controla los datos ingresados para la recepcion
	//(Por favor respetar el formato de los carteles que se muetran)
	function control_recib_pedido()
    {
      var msg="LOS SIGUIENTES ITEMS REQUIEREN SU ATENCION:\n";
      var hay_errores=0;
      var falta_recibir;
      msg+="~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n";
	  <?
	  $datos_recibidos->Move(0);
	  while(!$datos_recibidos->EOF)
	  {?>
	   falta_recibir=document.all.comprado_<?=$datos_recibidos->fields['id_fila']?>.value - document.all.cantidad_rec_<?=$datos_recibidos->fields['id_fila']?>.value;

	    if(falta_recibir>0)
		{
		    //este incio de mensaje se usa para listar para cada fila todos sus errores. Se concatena al principio del primer mensaje
		    //de error y luego se blanquea para que no se repita mas. Asi no interesa cual de todos es el primer mensaje,
		    //este inicio siempre sale
		    aux_msg="--------------------------------------------------------------------------------\n"+
		            "Para la fila:\n   <?=$datos_recibidos->fields["descripcion_prod"]?> <?=$datos_recibidos->fields["desc_adic"]?>"+
		    		"\n--------------------------------------------------------------------------------\n";

		    if(document.all.cant_rec_<?=$datos_recibidos->fields['id_fila']?>.value!="")
		    {
		    	if(document.all.cant_rec_<?=$datos_recibidos->fields['id_fila']?>.value > falta_recibir)
		    	{
		    	 msg+=aux_msg+"     	*La cantidad que falta recibir es:"+falta_recibir+".\n"+
		    	              " 	  La cantidad que intenta recibir es: "+document.all.cant_rec_<?=$datos_recibidos->fields['id_fila']?>.value+"\n";
		    	 hay_errores=1;
		    	 aux_msg="";
		    	}

		    	if(document.all.deposito_<?=$datos_recibidos->fields["id_fila"]?>.value==-1)
			    {
			      msg+=aux_msg+"	*Debe elegir un depósito.\n";
			      hay_errores=1;
			      aux_msg="";
			    }
			    if(document.all.id_prod_rec_<?=$datos_recibidos->fields['id_fila']?>.value=="")
			    {
			      msg+=aux_msg+"	*Debe elegir un producto específico\n";
			      hay_errores=1;
			      aux_msg="";
			    }
		    }//de if(document.all.cant_rec_.value!="")

		    if(document.all.id_prod_rec_<?=$datos_recibidos->fields["id_fila"]?>.value!="")
			{
		        if(document.all.deposito_<?=$datos_recibidos->fields["id_fila"]?>.value==-1)
			    {
			      msg+=aux_msg+"	*Debe elegir un depósito\n";
			      hay_errores=1;
			      aux_msg="";
			    }
			    if(document.all.cant_rec_<?=$datos_recibidos->fields['id_fila']?>.value=="")
			    {
			      msg+=aux_msg+"	*Debe elegir una cantidad\n";
			      hay_errores=1;
			      aux_msg="";
			    }
		    }//de if(document.all.cant_rec_.value!="")
		}//de if(falta_recibir<=0)
	   <?
	   $datos_recibidos->MoveNext();
	  }//de while(!$datos_recibidos->EOF)
	 ?>
	 if(hay_errores)
	 {
	 	msg+="~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n";
	 	alert(msg);
	 	return false;
	 }
	 else
	  return true;
	}//de function control_recib_pedido()

	</script>

    <?
    $datos_recibidos->Move(0);
	while (!$datos_recibidos->EOF)
	{

	  $cantidad_comprada=$datos_recibidos->fields['cantidad_fila'];
	  $cantidad_recibida=$datos_recibidos->fields['cantidad_recibida'];
	  $observaciones=$datos_recibidos->fields['observaciones'];
	  $id_fila=$datos_recibidos->fields['id_fila'];

	  if($cantidad_comprada<=$cantidad_recibida)
	    $todo_recibido=1;
 	  else
        $todo_recibido=0;
	  ?>
	 <table width="100%" align="center" class="bordes">
	  <tr id="sub_tabla">
        <td>
         <b>
          <?
            if($filas_cambios_prod[$id_fila]["id_producto"]!="")
            { $nombre_producto_fila=$filas_cambios_prod[$id_fila]["desc_gral"];
              $id_producto=$filas_cambios_prod[$id_fila]["id_producto"];
            }
            else
            { $nombre_producto_fila=$datos_recibidos->fields['descripcion_prod']." ".$datos_recibidos->fields['desc_adic'];
              if($datos_recibidos->fields['id_prod_esp'])
               $id_producto=$datos_recibidos->fields['id_prod_esp'];
              else
               $id_producto=$datos_recibidos->fields['id_producto'];
            }
           echo $nombre_producto_fila;
          ?>
         </b>
        </td>
        <td>
         <b>Comprado</b> <input name="comprado_<?=$id_fila?>" type="text" style="text-align:right; border:none;background-color: #DFF4FF;font-weight: bold;color: blue;" readonly value="<?=$cantidad_comprada?>" size="6" >
        </td>
        <td>
         <b>Recibido</b> <input name="cantidad_rec_<?=$id_fila?>" type="text" style="text-align:right; border:none;background-color: #DFF4FF;font-weight: bold;color: blue;" value="<?=$cantidad_recibida?>" readonly size="6" >
        </td>
	  </tr>
	  <tr>
	   <td colspan="3">
	    <table width="100%">

	     <tr>
	      <td>
	       <?$link_cb=encode_link("leer_codigos_barra.php",array("desde_pagina"=>"ord_compra_fin","total_comprado"=>$cantidad_comprada,"producto_nombre"=>"$nombre_producto_fila","id_fila"=>$id_fila,"nro_orden"=>$nro_orden,"id_producto"=>$id_producto)); ?>
           <input type="button" name="cod_barra" value="Códigos de Barra" onclick="if(vent_cb.closed)vent_cb=window.open('<?=$link_cb?>','','top=130, left=250, width=420px, height=450px, scrollbars=1, status=1,directories=0');else vent_cb.focus();">
          </td>
          <td colspan="2" align="right">
           <?
           if($fecha_recepcion!="" && $fecha_recepcion<$fecha_subida_gestion3)
           {
	           if(permisos_check("inicio","permiso_cambiar_producto_fila"))
		        {

		         if($filas_rec_ent[$id_fila]["recibidos"]==0 && $filas_rec_ent[$id_fila]["entregados"]==0)
		         {
		         	$link_cambio_prod=encode_link("cambios_productos_fila.php",array("id_fila"=>$id_fila,"permiso_cambio"=>1));
		         	//no se permiten mas cambios de productos de fila...solo se muestra el boton de cambio de productos
		         	//si ya se ha realizado un cambio de producto antes para esta OC
		         }
		         else
		         {
		           $link_cambio_prod=encode_link("cambios_productos_fila.php",array("id_fila"=>$id_fila,"permiso_cambio"=>0));
		           ?>
		            <input type="button" name="cambiar_producto_fila" value="Cambios Producto" onclick="if(vent_cambio_prod.closed)vent_cambio_prod=window.open('<?=$link_cambio_prod?>','','top=130, left=200, width=700px, height=350px, scrollbars=1, status=1,directories=0');else vent_cambio_prod.focus();">
		           <?
		         }

		        }//de if(permisos_check("inicio","permiso_cambiar_producto_fila"))
           }//de if($fecha_recepcion!="" && $fecha_recepcion<$fecha_subida_gestion3)

	        if(!$flag_stock && permisos_check("inicio","permiso_desrecibir_desentregar_fila") && ($filas_rec_ent[$id_fila]["entregados"]>0 || $filas_rec_ent[$id_fila]["recibidos"]>0))
	        {
	         //NOTA: NO SE PUEDE DES-ENTREGAR UNA OC QUE ESTE ASOCIADA A STOCK PORQUE LOS PRODUCTOS QUE LA OC INGRESA AL STOCK,
	         //AL RECIBIR, PUDIERON HABER SIDO USADOS EN ALGUN OTRO LADO.
	        ?>
	         &nbsp;<input type="submit" name="desrecibir_desentregar_fila" value="Des-Recibir"
 	                onclick="if(confirm('¿Está seguro que desea des-recibir esta fila?'))
	                        {document.all.fila_desentregar.value=<?=$id_fila?>;
	                         return true;
	                        }
	                        else
	                         return false;
	                       "
	               >
	         <?
	        }//de if(permisos_check("inicio","permiso_desrecibir_desentregar_fila"))

	       ?>
	      </td>
	     </tr>
	     <tr>
	      <td colspan="3">
	       <input type="hidden" name="id_recibido_<?=$id_fila?>" value="<?=$datos_recibidos->fields['id_recibido']?>">
	       <?
	       if($datos_recibidos->fields['id_recibido'])
	       {
		       //traemos el log de las recepciones del producto de la fila
		       $query="select id_log_recibido,id_prod_esp,usuario,fecha,cant,desde_stock,producto_especifico.descripcion
		             from log_rec_ent left join producto_especifico using(id_prod_esp)
		             where id_recibido=".$datos_recibidos->fields['id_recibido'];
		       $log_recibidos=sql($query,"<br>Error al traer el registro de las recepciones de productos<br>") or fin_pagina();


		       if($log_recibidos->RecordCount()>0)
		       {?>
			       <table width="95%" align="center" class="bordes">
			        <tr id="mo">
			         <td colspan="4">
			          Registro de Recepciones del Producto en el Stock <?=$datos_recibidos->fields["deposito_nombre"]?>
			         </td>
			        </tr>
			        <tr id="mo_sf6">
			         <td width="50%">
			          <b>Producto</b>
				     </td>
			         <td width="5%">
			          <b>Cantidad</b>
				     </td>
			         <td width="20%">
			          <b>Fecha</b>
				     </td>
			         <td width="20%">
			          <b>Usuario</b>
				     </td>
			        </tr>
			         <?

				     while (!$log_recibidos->EOF)
				     {?>
				       <tr id="ma_mg">
				        <td align="left">
				         <?
				          //si se recibio con un producto especifico (desde gestion3 en adelante, mostramos su descripcion)
	                      if($log_recibidos->fields["descripcion"])
				           echo $log_recibidos->fields["descripcion"];
				          else //sino mostramos el mismo producto que el de la fila
				           echo $nombre_producto_fila;
				         ?>
				        </td>
				        <td align="center">
				         <?=$log_recibidos->fields["cant"]?>
				        </td>
				        <td align="center">
				         <?=fecha($log_recibidos->fields["fecha"])." ".hora($log_recibidos->fields["fecha"])?>
				        </td>
				        <td align="center">
				         <?=$log_recibidos->fields["usuario"]?>
				        </td>
				       </tr>
				      <?
				      $log_recibidos->MoveNext();
				     }//de while(!$log_recibidos->EOF)
				     ?>
			       </table>
			   <?
		       }//de if($log_recibidos->RecordCount()>0)
	       }//de if($datos_recibidos->fields['id_recibido'])
		   ?>
	      </td>
         </tr>
	    </table>
	   </td>
	  </tr>
	  <tr>
	      <td colspan="3">
	       <?
	       $max_800_600=110;
           $max_1024_768=140;
           $max_otro=189;
	       if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	        $longitud_fila=$max_800_600;
	       elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	        $longitud_fila=$max_1024_768;
	       else//si es una resolucion mayor a 1024
	        $longitud_fila=$max_otro;
	       ?>
	       <b>Observaciones </b>
	       <textarea style="width:89%" rows="<?=row_count($observaciones,$longitud_fila)?>" name="observaciones_<?=$id_fila?>"><?=$observaciones?></textarea>
	      </td>
	     </tr>
	  <?
	  //si aun no se ha recibido todo, mostramos la fila para recibir mas productos
	  if(!$todo_recibido)
	  {
	  ?>
		  <tr>
		   <td colspan="3">
		    <hr>
		    <table width="100%" align="center">
		     <tr>
		      <td width="25%">
			    <b>Recibir en</b>
			    <select name="deposito_<?=$id_fila?>">
			     <?
                 //si tenemos un id de deposito significa que ya se ha recibido algo, por lo tanto
	             //solo se puede recibir en ese deposito, y no en el resto
	             echo "El deposito ".$datos_recibidos->fields["id_deposito"];
	             if($datos_recibidos->fields["id_deposito"]!="")
	             {?>
	               <option value="<?=$datos_recibidos->fields["id_deposito"]?>">
	                <?=$datos_recibidos->fields["deposito_nombre"]?>
	               </option>
	              <?
	             }//de if($datos_recibidos->fields["id_deposito"]!="")
	             else//sino mostramos todos los depositos
		         {
				     ?>
				     <option value="-1">Seleccione...</option>
				     <?
				     $datos_depositos->Move(0);
				     while (!$datos_depositos->EOF)
				     {?>
				      <option value="<?=$datos_depositos->fields["id_deposito"]?>">
				       <?=$datos_depositos->fields["nombre"]?>
				      </option>
				      <?
				      $datos_depositos->MoveNext();
				     }//de while(!$datos_depositos->EOF)
		         }//del else de if($datos_recibidos->fields["id_deposito"]!="")
			     ?>
			  </td>
			  <td width="15%">
			    <b>Cantidad</b>
			    <input type="text" name="cant_rec_<?=$id_fila?>" size="4" onchange="control_numero(this,'Cantidad')">
			  </td>
			  <td width="60%">
			    <b>Producto</b>
			    <input type="text" name="prod_rec_<?=$id_fila?>" size="70" readonly>
			    <input type="hidden" name="id_prod_rec_<?=$id_fila?>">
			    <?
			     $funcion_prod_esp="window.opener.elegir_producto_recibido(".$id_fila.")";
			     $link_elegir_prod=encode_link('../productos/listado_productos_especificos.php',array('pagina_viene'=>'ord_compra_recepcion.php','onclick_cargar'=>"$funcion_prod_esp")) ?>
			    <input type="button" name="elegir_producto_<?=$id_fila?>" value="Elegir" onclick="if(wrecibir_prod.closed)
                                                                                                   wrecibir_prod=window.open('<?=$link_elegir_prod?>','','toolbar=0,location=0,directories=0,resizable=1,status=0, menubar=0,scrollbars=1,left=25,top=10,width=950,height=500');
                                                                                                  else
                                                                                                   wrecibir_prod.focus();
                                                                                                 "
			    >
			  </td>
			 </tr>
			</table>
		   </td>
		  </tr>
	 <?
	 }//de if(!$todo_recibido)
	 else
	 {?>
	  	  <tr>
	   	   <td colspan="3">
		    <hr>
		    <div align="center">
		     <font color="Blue"><b>Se recibieron todos los productos para esta Fila</b></font>
		    </div>
		   </td>
		  </tr>
	  <?
	 }//del else de if(!$todo_recibido)
	 ?>
	 </table>
	 <hr style="height:2;color:black">
	 <?
	 $datos_recibidos->MoveNext();
	}//de while(!$datos_recibidos->EOF)
	?>
    <input type="hidden" name="cant_filas" value="<?=$datos_recibidos->RecordCount();?>">
    <?

}//de function generar_form_recepcion($nro_orden)


/*****************************************************
Funcion que genera la parte de entregar productos.
----
El campo prov_stock indica si lo que se va a generar
es para entregar productos de una OC con proveedor
de tipo Stock (valor del parametro en 1), o es para
entregar productos de una OC asociada a licitacion,
presupuesto, RMA o Serv Tec
******************************************************/
function generar_form_entrega($nro_orden,$prov_stock)
{global $db,$datos_orden,$filas_cambios_prod,$datos_depositos,$title_gestion3;

?>
 <b> PARA INDICAR LOS PRODUCTOS QUE SE ENTREGAN, INGRESE LOS CÓDIGOS DE BARRA DE CADA UNO</b>
  <br>

      <?
      $query="select  	filas.id_fila,filas.id_prod_esp,filas.id_producto,filas.descripcion_prod,
		                filas.desc_adic,filas.cantidad_fila,filas.precio_unitario,filas.es_agregado,
		                rec_ent.id_recibido,rec_ent.id_deposito,rec_ent.observaciones,
		                case when rec_ent.cantidad is null then 0
			                else rec_ent.cantidad
		                end as cantidad_entregada
    		from (
	    		      select id_fila,id_prod_esp,id_producto,descripcion_prod,desc_adic,cantidad as cantidad_fila,precio_unitario,es_agregado
	       			  from compras.fila
	                  where es_agregado isnull or es_agregado<>1
                 ) as filas
            left join
	             (    select id_recibido,id_fila,id_deposito,cantidad,observaciones
	                  from compras.recibido_entregado
	                  where ent_rec=0
                 )as rec_ent
            using(id_fila)
            where id_fila in (select id_fila from compras.fila where nro_orden=$nro_orden)
            order by id_producto";

	  $datos_entregados=sql($query,"<br>Error al traer los datos de la entrega<br>") or fin_pagina();

      while (!$datos_entregados->EOF)
      {
      	 $cantidad_comprada=$datos_entregados->fields['cantidad_fila'];
	     $cantidad_entregada=$datos_entregados->fields['cantidad_entregada'];
	     $observaciones=$datos_entregados->fields['observaciones'];
	     $id_fila=$datos_entregados->fields['id_fila'];

	     if($cantidad_comprada<=$cantidad_entregada)
	     {
	     	$todo_entregado=1;
	        $cant_entregado="readonly title='Ya se ha entregado la cantidad comprada'";
	        $dis_sin_cb="disabled";//para el boton de Entregar Sin CB
	     }
 	     else
 	     {
 	     	$todo_entregado=0;
 	        $cant_entregado="";
 	        $dis_sin_cb="";//para el boton de Entregar Sin CB
 	     }
      	?>
        <table width="100%" cellspacing=0 border=0 bordercolor=#E0E0E0 align="center" class="bordes">
         <tr id=mx2>
          <td width="65%">
             <?
	            if($filas_cambios_prod[$id_fila]["id_producto"]!="")
	            { $nombre_producto_fila=$filas_cambios_prod[$id_fila]["desc_gral"];
	              $id_producto=$filas_cambios_prod[$id_fila]["id_producto"];
	            }
	            else
	            { $nombre_producto_fila=$datos_entregados->fields['descripcion_prod']." ".$datos_entregados->fields['desc_adic'];
	              if($datos_entregados->fields['id_prod_esp'])
	               $id_producto=$datos_entregados->fields['id_prod_esp'];
	              else
	               $id_producto=$datos_entregados->fields['id_producto'];
	            }
	           echo $nombre_producto_fila;

             ?>
             <input type="hidden" name="id_recibido_<?=$id_fila?><?if($prov_stock==0)echo "_0"?>" value="<?=$datos_entregados->fields['id_recibido']?>">
          </td>
          <td width="18%" nowrap>
           <strong>Comprado</strong>&nbsp; <input name="comprado_<?=$id_fila?><?if($prov_stock==0)echo "_0"?>" type="text" id=mx2 style="text-align:right; border:none;" readonly value="<?=$cantidad_comprada?>" size="6" >
          </td>
          <td width="18%" nowrap><strong>Entregado</strong>&nbsp;
           <input name="cantidad_ent_<?=$id_fila?><?if($prov_stock==0)echo "_0"?>" type="text" id=mx2 style="text-align:right; border:none;" value="<?=$cantidad_entregada?>" readonly size="6" >
          </td>
        </tr>

        <?

         //traemos el log de recibidos por cada producto que se muestra
		 $query="select log_rec_ent.* from log_rec_ent join recibido_entregado using(id_recibido) where id_fila=$id_fila and not id_log_recibido isnull and ent_rec=0";
		 $log_entregado=sql($query,"Error al traer el registro de lo entregado") or fin_pagina();
		 if($log_entregado->RecordCount()>0)
		 {
		 ?>
		  <tr>
		  <td colspan="3">
          <table width="95%" align="center" border=1 bordercolor=#E0E0E0>
		    <tr id=mo_sf6>
		     <td colspan="3" align="center">
		      <b>Registro de Entregas de este producto</b>
		     </td>
		    </tr>
		   <?
		    while(!$log_entregado->EOF)
		    {
             ?>
		     <tr id=ma>
		      <?if($log_entregado->fields['cant']>0)
		        {
		         $texto_log="Entregados";
		        }
		        else
		         $texto_log="ELIMINACION DE ENTREGA";
		        ?>
		       <td title="<?=$log_entregado->fields['nombre']?>"><b><?=$texto_log?>: <?=$log_entregado->fields['cant']?></b></td>
		       <?$fecha_log=split(" ",$log_entregado->fields['fecha']);

		         if($log_entregado->fields['desde_stock']==1)
		          $desde_stock="(Entregado desde stock)";
		         else
		          $desde_stock="";
		       ?>
		       <td align="center" colspan="2"> Usuario: <b><?=$log_entregado->fields['usuario']?></b> - Fecha: <b><?=fecha($fecha_log[0])?> <?=$fecha_log[1]?> <?=$desde_stock?></b></td>
		     </tr>
		     <?
		     $log_entregado->MoveNext();
		    }
            ?>
		   </table>
		  </td>
	     </tr>

      	<?
		 }//de if($log_entregado->RecordCount()>0)
        //si se esta generando form de entrega para OC con proveedor Stock
		if($prov_stock==1)
          $desde_pagina="ord_compra_recepcion";
        //si se esta generando form de entrega para OC asociada a Lic,RMA,ServTec o Presupuesto
        //y el proveedor de la OC no es un stock.
        else
         $desde_pagina="lic_ord_compra_recepcion";

        $link_cb=encode_link("movimiento_codigos_barra.php",array("desde_pagina"=>"$desde_pagina","total_comprado"=>$cantidad_comprada,"total_entregado"=>$cantidad_entregada,"producto_nombre"=>"$nombre_producto_fila","id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"nro_orden"=>$nro_orden,"id_fila"=>$id_fila));
        $link_sin_cb=encode_link("sin_codigos_barra.php",array("desde_pagina"=>"$desde_pagina","total_comprado"=>$cantidad_comprada,"total_entregado"=>$cantidad_entregada,"producto_nombre"=>"$nombre_producto_fila","id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"nro_orden"=>$nro_orden,"id_fila"=>$id_fila));
        ?>
        <tr>
       <td width="1%">
        <input type="button" name="cod_barra" value="Códigos de Barra" title="Ingresar los Códigos de Barra de los productos a entregar" onclick="if(vent_cbe.closed)vent_cbe=window.open('<?=$link_cb?>','','top=130, left=250, width=320px, height=350px, scrollbars=1, status=1,directories=0');else vent_cb.focus();">

        <?
        $check_sin_cb="";
        if(permisos_check("inicio","permiso_entregar_sin_cb"))
        {$check_sin_cb="checked"?>
         <input type="button" name="sin_cod_barra" value="Entregar Sin CB" <?=$dis_sin_cb?>  <?=$title_gestion3?> onclick="if(vent_sin_cb.closed)vent_sin_cb=window.open('<?=$link_sin_cb?>','','top=130, left=250, width=320px, height=350px, scrollbars=1, status=1,directories=0');else vent_sin_cb.focus();" disabled>
         <input type="hidden" name="hidden_sin_cb_<?=$id_fila?>" value="1" >
        <?
        }

        if(permisos_check("inicio","permiso_desrecibir_desentregar_fila") && $cantidad_entregada>0)
        {
        ?>
         &nbsp;<input type="submit" name="desrecibir_desentregar_fila" value="Des-Entregar"
               class="little_boton" style="width:100px"
               onclick="if(confirm('¿Está seguro que desea des-entregar esta fila?'))
                        {document.all.fila_desentregar.value=<?=$id_fila?>;
                         return true;
                        }
                        else
                         return false;
                       "
               disabled <?=$title_gestion3?>
               >
         <?
        }//de if(permisos_check("inicio","permiso_desrecibir_desentregar_fila"))
        ?>
       </td>
       </tr>
       <tr>
        <td colspan="3">
         <HR>
         <b>Comentarios de Entrega</b>
         <textarea rows="2" cols="120" name="observaciones_<?=$id_fila?><?if($prov_stock==0)echo "_0"?>"><?=$datos_entregados->fields['observaciones']?></textarea>
         <br><br>
        </td>
       </tr>
       </table>
       <hr style="height:2;color:black">

       <?
       $datos_entregados->MoveNext();
      }//de while(!$datos_entregados->EOF)

}//de function generar_form_entrega($nro_orden)

/*************************************************************
 Funcion relacionada con las facturas de proveedores
**************************************************************/
function cargar(&$arr)
{
 $i=0;
 while($i<sizeof($arr)){
    if ($arr[$i][0]!=-1){
	   $id=$arr[$i];
	   $arr[$i][0]=-1;
	   return $id;
	}
	else $i++;
	}
 return false;
}//de function cargar(&$arr)

extract($_POST,EXTR_SKIP);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

switch ($modo) {
      	case "oc_serv_tec": $titulo_pagina="Orden de Servicio Técnico";
      						break;
      	default:			$titulo_pagina="Orden de Pago";
      	             		break;
      }

//tengo estas variables que obtengo desde parametros
//mt_id_deposito - mt_id_producto - mt_id_proveedor - mt_id_mt
if ($nro_orden)
{
 $q="select orden_de_compra.id_proveedor,orden_de_compra.estado,orden_de_compra.id_licitacion,orden_de_compra.es_presupuesto,
     orden_de_compra.internacional,orden_de_compra.flag_stock,orden_de_compra.nrocaso,orden_de_compra.flag_honorario,
     orden_de_compra.orden_prod,orden_de_compra.fecha_entrega,orden_de_compra.notas,orden_de_compra.notas_internas,
     proveedor.clasificado,proveedor.razon_social
     from orden_de_compra join proveedor using(id_proveedor) where orden_de_compra.nro_orden=$nro_orden";
 $datos_orden=sql($q,"<br>Error al traer los datos de la OC<br>") or fin_pagina();
 $estado=$datos_orden->fields['estado'];
 $id_proveedor=$datos_orden->fields['id_proveedor'];
 $clasif=$datos_orden->fields['clasificado'];
 $flag_stock=$datos_orden->fields['flag_stock'];
 $internacional=$datos_orden->fields['internacional'];
 $id_licitacion=$datos_orden->fields['id_licitacion'];
 $es_presupuesto=$datos_orden->fields['es_presupuesto'];
 $flag_stock=$datos_orden->fields['flag_stock'];
 $nrocaso=$datos_orden->fields['nrocaso'];
 $flag_honorario=$datos_orden->fields['flag_honorario'];
 $orden_prod=$datos_orden->fields['orden_prod'];
 $fecha_entrega=$datos_orden->fields['fecha_entrega'];
 $nombre_proveedor=$datos_orden->fields['razon_social'];
 $notas = $datos_orden->fields['notas'];
 $notas_internas = $datos_orden->fields['notas_internas'];

  //averiguamos la fecha de recepcion de la OC, para ver que archivo mostramos
  $query="select fecha from log_ordenes where nro_orden=$nro_orden and tipo_log='de recepcion'";
  $fff=sql($query,"<br>Error al traer la fecha de recepcion de la OC<br>") or fin_pagina();
  $fecha_recepcion=$fff->fields["fecha"];
  $fecha_subida_gestion3="2006-01-04 00:00:00";

}//de if ($nro_orden)
else
 die("Error: no se encontro el Nº de Orden");

  $query_asoc="select fact_prov.fecha_emision, factura_asociadas.* from factura_asociadas join fact_prov using(id_factura) where nro_orden=$nro_orden";
  $res_asoc=sql($query_asoc) or fin_pagina();

if (!isset ($cant_factura))
{
  if ($res_asoc->RecordCount()>0)
   $cant_factura=$res_asoc->RecordCount();
  else
   $cant_factura=1;
}//de if (!isset ($cant_factura))

echo $html_header
?>

<style type="text/css">
<!--
.unnamed1 {
	border: medium solid #006699;
}
-->
</style>
<style type="text/css">
<!--
.unnamed2 {
	color: #FFFFFF;
	background-color: #990000;
	font-weight: bold;
	font-size: small;
	text-transform: uppercase;
}
.unnamed3 {
	font-weight: bold;
	font-variant: normal;
	color: #FF0000;
	text-transform: uppercase;
	font-size: small;
}
-->
</style>
<script language="JavaScript" src="funciones.js"></script>
<script>
//ventana de codigos de barra recibir
var vent_cb=new Object();
vent_cb.closed=true;

//ventana de codigos de barra entrega
var vent_cbe=new Object();
vent_cbe.closed=true;

//ventana de entrega sin codigos de barra
var vent_sin_cb=new Object();
vent_sin_cb.closed=true;

//ventana de cambios de productos para una fila
var vent_cambio_prod=new Object();
vent_cambio_prod.closed=true;

var contador=0;

/******************************************************
 Funcion que limpia los campos de facturas de proveedor
*******************************************************/
function  limpiar()
{
<?  for ($i=0;$i<$cant_factura;$i++)
    {
      echo "document.all.id_factura_$i.value='';";
    }
?>
}//de function  limpiar()

/*****************************************************
Modificaciones MAC:
-El prametro total_recibido es el total recibido que
esta en la base de datos.
-El parametro total_pedido es la cantidad de la
fila.

Se usan para controlar que no reciban mas de lo que
esta pedido

-El parametro producto, es el nombre del producto al
que se le calcula el total recibido.
******************************************************/
function calcular_recibidos(textfield,total_recibido,total_pedido,producto,error_cant_recib)
{
	var id_fila=textfield.name.substring(textfield.name.indexOf("_")+1,textfield.name.lastIndexOf("_"));
	var id_cant_recib=eval("document.all.cantidadr_"+ id_fila);

	var total=total_recibido;
	for (var i=0; i < document.all.dep_count.value ; i++)
	{
		cantidad=eval("document.all.cantidadr_"+ id_fila +"_"+ eval("document.all.deposito_"+ i +".value"));
		total+=parseInt(cantidad.value);
	}
	if(total>total_pedido)
	{alert('Para el producto \"'+producto+'\":\nel total recibido es mayor que el total pedido. Debe corregir las cantidades antes de guardar los datos');
	 error_cant_recib.value=1;
	}
	else
	 error_cant_recib.value=0;
	id_cant_recib.value=total;

}//de function calcular_recibidos(textfield,total_recibido,total_pedido,producto,error_cant_recib)

function control_fact()
{
 cant=document.all.cant_factura.value;
 for (i=0;i<cant;i++)
 {
  obj=eval ("document.all.id_factura_"+ i);
    for(j=i+1;j < cant; j++){
	  obj1=eval ("document.all.id_factura_"+ j);
	   if ((obj.value!="") && (obj.value==obj1.value)) {
	           alert('Ha seleccionado números iguales de factura');
			   return false
			   }
	   }
 }
 return true;
}//de function control_fact()

function habilitar_borrar(valor)
{
	if (valor.checked)
	   contador++;
	else
	   contador--;
	if (contador>=1){
	   window.document.all.borrar_archivo.disabled=0;
	      }
	else{
	    window.document.all.borrar_archivo.disabled=1;
	   }
}//fin function habilitar borrar

function eliminar()
{
	return window.confirm("Esta seguro que quiere eliminar "+contador+" archivos almacenados en el sistema.");
}

/*function controles()
{ if(control_fact()==true)
   return control_recib_pedido();
  else
   return false;
}//de function controles()*/

function controles() { 
	return(control_fact())
   
}//de function controles()
</script>

<div style="overflow:auto;width:100%;position:relative" id="div_formulario">
<?
echo "<center><font color='Red' size=2><b>$msg</b></font></center>";

$link_recepciones=encode_link($_SERVER['SCRIPT_NAME'],array('nro_orden'=>$nro_orden,'mostrar_dolar'=>$mostrar_dolar));
?>
<form name="form1" method="post" action="<?=$link_recepciones?>">
 <input type='hidden' name='estado_orden' value='<?=$estado?>'>
 <input type='hidden' name='flag_stock' value='<?=$flag_stock?>'>
 <input type='hidden' name='mostrar_dolar' value='<?=$mostrar_dolar?>'>
 <input type='hidden' name='tipo_lic' value='<?=$tipo_lic?>'>
 <input type='hidden' name='es_stock' value='<?=$es_stock?>'>
 <input type="hidden" name="fila_desentregar" value="">
<?
 if ($datos_orden->fields['fecha_factura'] or $datos_orden->fields['nro_factura'])
    echo "<input name='orden_ant' type='hidden' value='1'>";

 switch ($estado)
  {
   	case 'P':
   	case 'p':  $estado_orden="Pendiente"; break;
   	case 'A':
   	case 'a':  $estado_orden="Autorizada"; break;
	case 't':
	case 'T':  $estado_orden="Terminada"; break;
	case 'r':  $estado_orden="Rechazada"; break;
	case 'n':  $estado_orden="Anulada"; break;
	case 'u':  $estado_orden="Por Autorizar"; break;
   	case 'd':  $estado_orden="Parcialmente Pagada"; break;
	case 'e':
	case 'E':  $estado_orden="Autorizada"; break;
    case 'g':
    case 'G':  $estado_orden="Totalmente Pagada"; break;
   	default:  $estado_orden="Desconocido";
  }//de switch ($estado)

  if($internacional)
  { $texto_oc="<b>Orden de Pago Internacional Nº: </b>";
    $color_titulo="color='#00C021'";
  }
  else
  {$texto_oc="<b>Orden de Pago Nº: </b>";
   $color_titulo="";
  }
?>
 <table width="100%" class="bordes" align="center" bgcolor='<?=$bgcolor_out?>' cellspacing="3" cellpadding="3">
   <tr>
    <td align="center" id=mo colspan="2">
      <font size="3">
        <?=$texto_oc." ".$nro_orden?>
      </font>
      <input name="nro_orden" type="hidden" value="<?=$nro_orden?>">
    </td>
   </tr>
   <tr>
     <td width="30%">
      <b>Fecha</b>
    </td>
    <td width="70%">
     <font color="Blue" size="2">
      <b><?=fecha($fecha_entrega)?></b>
     </font>
    </td>
   </tr>
   <tr>
     <td width="30%">
      <b>Proveedor</b>
    </td>
    <td width="70%">
     <font color="Blue" size="2">
      <b><?=$nombre_proveedor?></b>
     </font>
    </td>
   </tr>
   <tr>
     <td width="30%">
      <b>Estado</b>
    </td>
    <td width="70%">
     <font color="Blue" size="2">
      <b><?=$estado_orden?></b>
     </font>
    </td>
   </tr>
   <tr>
    <td colspan="2">
	  <table class="bordes" width="100%">
	   <tr id="sub_tabla">
	    <td>
	     Facturas del Proveedor
	    </td>
	   </tr>
	   <tr>
		 <td>
		   <b>Cantidad de facturas</b>
		   <input name="cant_factura" type="text" onClick="document.all.cant_factura.value=''" onblur="Actualizar.click();"  size="10" value="<?=$cant_factura ?>">
		   <input name="Actualizar" type="button" value="Actualizar" title="Actualiza el número de facturas" onClick="form1.action='<?= $_SERVER['SCRIPT_NAME'];?>';document.form1.submit();"  <? if ($datos_orden->fields['nro_factura']) echo 'disabled' ?>>
		 </td>
	   </tr>
	   <tr>
	    <td>
	     <table border="1" align="center">
		   <tr>
		    <td width="4%">&nbsp; </td>
		    <td width="49%" align="center"><strong> ID Factura </strong> </td>
		    <td width="47%" align="center" id="td_fecha_factura"><strong>Fecha Factura</strong>&nbsp;
		    </td>
		   </tr>
		   <?
		    $filas=$res_asoc->RecordCount();
			if ($filas>0){ //armo un arreglo con los id  y las fechas asociados a la orden
			  for ($i=0;$i<$filas;$i++){
			     $aux=array();
				 $aux[0]=$res_asoc->fields['id_factura'];
				 $aux[1]=$res_asoc->fields['fecha_emision'];
		         $list[$i]=$aux;
				 $res_asoc->MoveNext();
		      }
		    }

			for ($i=0; $i<$cant_factura;$i++)
			{
			?>
			   <tr>
			    <td><input name="Buscar" type="button" value="Buscar" onClick="window.open('<? echo encode_link("../factura_proveedores/fact_prov_listar.php",array("nro_orden"=>$nro_orden,"estado"=>$estado,"fila"=>"$i","cant_factura"=>$cant_factura))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=50,top=30,width=700,height=450')" <? if ($datos_orden->fields['nro_factura']) echo 'disabled' ?>></td>
			    <?
				$id=cargar($list);
				if ($datos_orden->fields['nro_factura']) $value=$datos_orden->fields['nro_factura'];
				   else
				  $value=$_POST["id_factura_$i"] or $value=$id[0];
				if ($datos_orden->fields['fecha_factura']) $value_fecha=Fecha($datos_orden->fields['fecha_factura']);
			    else
				 $value_fecha= $_POST["fecha_factura_$i"] or $value_fecha=Fecha($id[1]);
				 //Fecha($parametros['fecha']) or?>
			    <td align="center"> <input name="ver_factura_<?=$i?>" type="button"  value="ir" title='ver detalles de la factura'  onclick="<? if ($datos_orden->fields['nro_factura']) {?>alert ('La factura asociada no está cargada'); return false; <? }?> if (document.all.id_factura_<?=$i?>.value=='') return false; window.open('<?=encode_link("../factura_proveedores/fact_prov_subir.php",array("nro_orden"=>$nro_orden,"estado"=>$estado,"fila"=>$i)) ?>&id_fact='+document.all.id_factura_<?=$i ?>.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=50,top=30,width=700,height=400')">
			      <input name="id_factura_<?=$i?>" type="text" value="<?=$value?>"> <input name="Nueva Factura" type="button" value="Nueva"  onclick="window.open('<? echo encode_link("../factura_proveedores/fact_prov_subir.php",array("nro_orden"=>$nro_orden,"estado"=>$estado,"fila"=>"$i"))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=50,top=30,width=700,height=450')" <? if ($datos_orden->fields['nro_factura']) echo 'disabled' ?>>
			    </td>
			    <td align="center"><input name="fecha_factura_<?=$i ?>" <?= $permiso ?> readonly="true" type="text" value="<?=$value_fecha;?>" size="10">
			    </td>
			   </tr>
		   <?
		   }//de for ($i=0; $i<$cant_factura;$i++)
		   ?>
		 </table>
		</td>
	   </tr>
	   <input name="id_proveedor" type="hidden" value="<?=$datos_orden->fields['id_proveedor'] //para poder guardar los comentarios?>" >
	  </table>
	</td>
   </tr>
  </table>
  <?
  $sql_archivos="select * from compras.archivos_subidos_compra where nro_orden=$nro_orden";
  $consulta_sql_archivos=sql($sql_archivos,"<br>Error al traer los archivos subidos para la OC<br>") or fin_pagina();
 	?>
 	<br/>
	 <table width="100%" align="center" class="bordes" bgcolor='<?=$bgcolor_out?>'>
	  <tr id=mo>
	   <td align="center" colspan="5">
	    <font size="3"><b>Archivos Subidos</b></font>
	   </td>
	 <tr>
	    <td align="left" colspan="5">
	     <b>Documentos:</b> <? echo $consulta_sql_archivos->RecordCount(); ?>
	     &nbsp;
	     <input type="button" name="bagregar" value="Agregar archivo" style="width:105px" onclick="location.href='<?= encode_link("ord_pago_subir_archivo.php",array("nro_orden"=>$nro_orden,"mostrar_dolar"=>$mostrar_dolar)) ?>'">
	     <input name="cant_archivos" type="hidden" value="<? echo $consulta_sql_archivos->RecordCount(); ?>">
	    </td>
	  </tr>
	<tr id=mo_sf6>
	 <td width='10%'><b><input type="submit" name="borrar_archivo" value="Borrar" title="Eliminar Seleccioneados" disabled onclick="return eliminar();"></b></td>
	 <td width='10%'><b>ID Archivo</b></td>
	 <td width='40%'><b>Nombre</b></td>
	 <td width='20%'><b>Fecha</b></td>
	 <td width='20%'><b>Responsable</b></td>
	</tr>

	<?  $i=0;
		while(!$consulta_sql_archivos->EOF)
		{
		?>
			<tr id="ma" title="<?=$consulta_sql_archivos->fields["comentario"]?>">
				<td>
			     <input type="checkbox" name="eliminar_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields['id_archivo_subido']; ?>" onclick="habilitar_borrar(this);" title="Seleccione para eliminar">
			     <input type="hidden" name="id_archivo_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields['id_archivo_subido']; ?>">
				</td>
				<td>
				 <?=$consulta_sql_archivos->fields["id_archivo_subido"]?>
				</td>
				<td>
				 <input type="hidden" name="nom_comp_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields["nombre_archivo_comp"]; ?>">
				 <a target="_blank" title='<?=$consulta_sql_archivos->fields["nombre_archivo"]?> [<?=number_format($consulta_sql_archivos->fields["filesize_comp"]/1024)?> Kb]' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivo_subido"],"download"=>1,"comp"=>1,"nro_orden"=>$nro_orden,"es_stock"=>$es_stock,"mostrar_dolar"=>$mostrar_dolar,"tipo_lic"=>$tipo_lic))?>'>
				 <img align=middle src=../../imagenes/zip.gif border=0></A>
				 <a title = 'Abrir archivo' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivo_subido"],"download"=>1,"comp"=>0,"nro_orden"=>$nro_orden,"es_stock"=>$es_stock,"mostrar_dolar"=>$mostrar_dolar,"tipo_lic"=>$tipo_lic))?>'>
				 <? echo $consulta_sql_archivos->fields["nombre_archivo"]." (".number_format(($consulta_sql_archivos->fields["filesize_comp"]/1024),"2",".","")."Kb)"?>
				 </a>
				</td>
				<td>
				 <?=fecha($consulta_sql_archivos->fields["fecha"])." ".hora($consulta_sql_archivos->fields["fecha"])?>
				</td>
				<td>
				 <? echo $consulta_sql_archivos->fields["usuario"]?>
				</td>
			</tr>
			<?
			$i++;
			$consulta_sql_archivos->MoveNext();
		}//de while(!$consulta_sql_archivos->EOF)
		?>
		<input type="hidden" name="Cantidad" value="<?=$i?>">
	</table>
	<br>
</div><!--div_formulario-->
<?
/********************************************************************************************
  SECCION BOTONERA
*********************************************************************************************/
?>
<div style="background-color:<?=$bgcolor_out?>;height:50px;position:relative" id="div_botonera">
<table width="100%" id=history class="bordessininferior">
 <tr>
  <td width="37.5%">
   Estado: <?=$estado_orden?>
  </td>
  <td width="25%">
   <?=$titulo_pagina?> Nº <?=$nro_orden?>
  </td>
 </tr>
</table>
<table width="100%" class="bordes">
 <?
 $link_calif=encode_link("../calidad/califique_proveedor.php",array("proveedor"=>"$id_proveedor","desde"=>"2"));
 $query="select * from general.calificacion_proveedor
         where fecha is not null and fecha>(current_date - 7)
         and id_proveedor=$id_proveedor";
 $re=sql($query,"<br>Error al traer informacion de la calificacion del proveedor<br>") or fin_pagina();
 ?>
 <tr>
  <td width="50%" align="right">
    <input name="guardar_datos" type="submit"  value="Guardar Datos" <?=$permiso ?> style="width:95px" onClick="if (controles())
                                                                                                                   { <?if (!$es_stock && $re->RecordCount()==0)
                                                                                                                        echo "window.open('$link_calif','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=230,top=80,width=500,height=400');";
                                                                                                                     ?>
                                                                                                                     return true;
                                                                                                                   }
                                                                                                                   else
                                                                                                                    return false;
                                                                                                                 "
    >
  </td>
  <td width="50%" align="left">
   <input name="boton" type="button" id="boton" value="Volver" style="width:70px" onclick="location.href='<?= encode_link('ord_pago.php',array("nro_orden"=>$nro_orden)) ?>'">
  </td>
 </tr>
</table>
</div>
</form>
<script>
//dependiendo del largo del formulario, seteamos el largo del div del formulario
 var largo_form=parseInt(document.body.clientHeight)-parseInt(document.all.div_botonera.style.height);

 document.all.div_formulario.style.height=largo_form+"px";
</script>

<?
/*if($_ses_user["login"]=="marcos" || $_ses_user["login"]=="fernando" || $_ses_user["login"]=="norberto"
   || $_ses_user["login"]=="gonzalo" || $_ses_user["login"]=="mariela")
  echo fin_pagina();
else*/
 echo "</body></html>";
?>