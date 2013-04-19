<?
/*
Autor: GACZ

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.67 $
$Date: 2005/12/01 21:59:21 $
*/
require_once("../../config.php");
require_once("fns.php");
if ($parametros["download"]) {
	$sql = "select * from archivos_subidos_compra where id_archivo_subido = ".$parametros["FileID"];
	$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
	

	if ($parametros["comp"]) {
		$FileName = $result->fields["nombre_archivo_comp"];	
		//die ($FileName);	
		$FileNameFull = UPLOADS_DIR."/ord_compra/archivos_subidos/$FileName";		
		$FileType="application/zip";
		$FileSize = $result->fields["filesize_comp"];
		FileDownload(1,$FileName,$FileNameFull,$FileType,$FileSize);
	} else {
		$FileName = $result->fields["nombre_archivo"];
		//die ($FileName);
		$FileNameFull = UPLOADS_DIR."/ord_compra/archivos_subidos/$FileName";
		$FileType = $result->fields["filetype"];
		$FileSize = $result->fields["filesize"];
		FileDownload(0,$FileName,$FileNameFull,$FileType,$FileSize);
	}	
}
////////////////////////////////////////////////////////////////////// 


/**********************************************************************************
***********************************************************************************
FUNCIONES PARA GENERAR LA PARTE DE RECEPCION Y ENTREGA DE PRODUCTOS
***********************************************************************************
***********************************************************************************/

/****************************************************************
Funcion que genera los campos necesarios para especificar la 
recepcion de productos. Solo se utiliza cuando el proveedor NO ES
un Stock.
*****************************************************************/
//print_r ($parametros);
//$es_stock_subir=$parametros['es_stock'] or $es_stock_subir=$_POST['es_stock_subir'];
//$mostrar_dolar_subir=$parametros['mostrar_dolar'] or $mostrar_dolar_subir=$_POST['mostrar_dolar_subir'];
//$tipo_lic_subir=$parametros['tipo_lic'] or $tipo_lic_subir=$_POST['tipo_lic_subir'];

function generar_form_recepcion($nro_orden)
{global $db,$permiso,$datos_orden,$es_stock,$flag_stock,$parametros; 	
//	ESTA CONSULTA RECUPERA TODO FILTRAR POR AQUELLAS QUE NO TENGAN id_recibido
//Y TAMBIEN ACTIVAR LA CONDICION EN fns.php->get_items_fin()
$q="select r.*,fd.*,
case when r.cantidad is null then 0 
	  else r.cantidad
end as cantidad_r,
case when cantidad_rt is null then 0 
	  else cantidad_rt
end as cantidad_rt ".
"from ".
"(select * from fila, depositos where (es_agregado isnull or es_agregado<>1)) fd left join recibidos r ".
"on fd.id_fila=r.id_fila AND fd.id_deposito=r.id_deposito and r.ent_rec=1 left join ".
"(select id_fila,sum(cantidad) as cantidad_rt from recibidos where ent_rec=1 group by id_fila) rt ".
"on rt.id_fila=fd.id_fila ".
"where fd.tipo=0 and fd.id_fila in ".
"(select id_fila from fila where nro_orden=$nro_orden) ".
"order by id_producto ";

$datos_recibidos=$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);

//traemos los depositos de tipo stock
$q="select * from depositos where tipo=0 order by nombre";
$datos_depositos=$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
?>
<script>
//el parametro dep_habilitado indica cual deposito de la fila id_fila, 
//no debe ser deshabilitado el resto es deshabilitado
function deshabilitar_otros_depositos(dep_habilitado,id_fila)
{var rec_fila,pasado;
  pasado=eval("document.all.cantidadr_"+id_fila+"_"+dep_habilitado);
	<? while(!$datos_depositos->EOF)
       {
        if($id_dep_select!=$datos_depositos->fields['id_deposito'])
        {?>
         rec_fila=eval("document.all.cantidadr_"+id_fila+"_<?=$datos_depositos->fields['id_deposito']?>");
         
         if(dep_habilitado!=<?=$datos_depositos->fields['id_deposito']?> && pasado.value!=0)
         {rec_fila.readOnly=1;
          rec_fila.title="Los productos se comenzaron a recibir en otro depósito";
         } 
         else if(pasado.value==0 && pasado.value!="")
         {rec_fila.readOnly=0;
          rec_fila.title="";
         } 
        <?
        }
        $datos_depositos->MoveNext();	
       }//de while(!$datos_depositos->EOF)
?>
}//de function deshabilitar_otros_depositos(dep_habilitado,id_fila)
</script>
	
	<b> INDIQUE LOS PRODUCTOS RECIBIDOS EN EL DEPOSITO CORRESPONDIENTE </b>
  <br>
    <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
      <?
     
    $filas_rec_ent=cant_rec_ent_por_fila($nro_orden);
    $filas_cambios_prod=filas_con_cambios_prod($nro_orden);
      
    while (!$datos_recibidos->EOF)
	{
	  	 
?>
     
      <tr id=mo> 
        <td colspan="3" width="65%" > <div align="left"><strong>Producto </strong> &nbsp;&nbsp; 
            <?
            if($filas_cambios_prod[$datos_recibidos->fields['id_fila']]["id_producto"]!="")
            { $descripcion_producto=$filas_cambios_prod[$datos_recibidos->fields['id_fila']]["desc_gral"];
              $id_producto=$filas_cambios_prod[$datos_recibidos->fields['id_fila']]["id_producto"];
            }
            else 
            { $descripcion_producto=$datos_recibidos->fields['descripcion_prod']." ".$datos_recibidos->fields['desc_adic'];
              $id_producto=$datos_recibidos->fields['id_producto'];
            }
            echo $descripcion_producto;
            ?>
          </div>
        </td>
        <td width="18%" nowrap><strong>Comprado</strong>&nbsp; <input name="comprado_<?=$datos_recibidos->fields['id_fila']?>" type="text" id=mo style="text-align:right; border:none;" readonly value="<?= $datos_recibidos->fields['cantidad'] ?>" size="6" >
          
        </td>
        <td width="18%" nowrap><strong>Recibido</strong>&nbsp;
          <input name="cantidadr_<?=$datos_recibidos->fields['id_fila']?>" type="text" id=mo style="text-align:right; border:none;" value="<?= $datos_recibidos->fields['cantidad_rt'] ?>" readonly size="6" ></td>
      </tr>
       <tr>
       <td width="1%">  
        <? 
        //recordamos el total recibido que esta en la base de datos
        $total_recibido=$datos_recibidos->fields['cantidad_rt'];
        //recordamos el total comprado que esta en la base de datos
        $total_comprado=$datos_recibidos->fields['cantidad'];
        
          
        //si la cantidad recibida es maoyr o igual a la cantidad comprada, 
        //deshabilitamos el campo de cantidades, en cada caso
        if($total_recibido>=$total_comprado)
        { $cant_recibido="readonly title='Ya se ha recibido la cantidad comprada'";

        }
        else 
        { $cant_recibido="";

        }
                     
        
        $link_cb=encode_link("leer_codigos_barra.php",array("desde_pagina"=>"ord_compra_fin","total_comprado"=>$total_comprado,"producto_nombre"=>"$descripcion_producto","id_producto"=>$id_producto,"nro_orden"=>$nro_orden)); ?>
        <input type="button" name="cod_barra" value="Códigos de Barra" onclick="if(vent_cb.closed)vent_cb=window.open('<?=$link_cb?>','','top=130, left=250, width=350px, height=350px, scrollbars=1, status=1,directories=0');else vent_cb.focus();">
       </td>
       <td colspan="4">
       <?
        if(permisos_check("inicio","permiso_cambiar_producto_fila"))
        {
         
         if($filas_rec_ent[$datos_recibidos->fields['id_fila']]["recibidos"]==0 && $filas_rec_ent[$datos_recibidos->fields['id_fila']]["entregados"]==0)
          $link_cambio_prod=encode_link("cambios_productos_fila.php",array("id_fila"=>$datos_recibidos->fields['id_fila'],"permiso_cambio"=>1));
         else 
          $link_cambio_prod=encode_link("cambios_productos_fila.php",array("id_fila"=>$datos_recibidos->fields['id_fila'],"permiso_cambio"=>0));
         
         ?>
         &nbsp;<input type="button" name="cambiar_producto_fila" value="Cambios Producto" class="little_boton" style="width:100px" onclick="if(vent_cambio_prod.closed)vent_cambio_prod=window.open('<?=$link_cambio_prod?>','','top=130, left=200, width=700px, height=350px, scrollbars=1, status=1,directories=0');else vent_cambio_prod.focus();"> 
         <?
        }//de if(permisos_check("inicio","permiso_cambiar_producto_fila"))
        
        if(!$flag_stock && permisos_check("inicio","permiso_desrecibir_desentregar_fila") && ($filas_rec_ent[$datos_recibidos->fields['id_fila']]["entregados"]>0 || $filas_rec_ent[$datos_recibidos->fields['id_fila']]["recibidos"]>0))
        {
         //NOTA: NO SE PUEDE DES-ENTREGAR UNA OC QUE ESTE ASOCIADA A STOCK PORQUE LOS PRODUCTOS QUE LA OC INGRESA AL STOCK,
         //AL RECIBIR, PUDIERON HABER SIDO USADOS EN ALGUN OTRO LADO.	
        ?>
         &nbsp;<input type="submit" name="desrecibir_desentregar_fila" value="Des-Recibir" 
               class="little_boton" style="width:100px" 
               onclick="if(confirm('¿Está seguro que desea des-recibir esta fila?'))
                        {document.all.fila_desentregar.value=<?=$datos_recibidos->fields['id_fila']?>;
                         return true;
                        }
                        else
                         return false;
                       "> 
         <?
        }//de if(permisos_check("inicio","permiso_desrecibir_desentregar_fila"))
       
         ?>  
       </td>
      </tr> 
      <tr>
		 <td colspan="5">
		 <?
		  
		 //traemos el log de recibidos por cada producto que se muestra
		 $query="select log_recibido.*,depositos.nombre,recibidos.ent_rec from recibidos left join log_recibido using(id_recibido) left join depositos using(id_deposito) where id_fila=".$datos_recibidos->fields['id_fila']." and not id_log_recibido isnull and ent_rec=1";
		 $log_recibido=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el log del recibido $query");
		 if($log_recibido->RecordCount()>0)
		 {
		 ?>
          <table width="100%" align="center" border=1 bordercolor=#E0E0E0 bgcolor="#ACACAC">
		    <tr>
		     <td colspan="3" align="center">
		      <font color="#006699"><b>Registro de Ingreso de este producto</b></font>
		     </td>
		    </tr> 
		   <?
		    while(!$log_recibido->EOF)
		    {?> 
		    <tr>
		       <td align="center" width="16%" nowrap>
		         <b><?=$log_recibido->fields['nombre']?></b>
		       </td>
		       <td align="center" width="8%"><b><?=$log_recibido->fields['cant']?></b></td>
		       <?$fecha_log=split(" ",$log_recibido->fields['fecha']);
		       ?>
		       <td align="right" colspan="2"> Usuario: <b><?=$log_recibido->fields['usuario']?></b> - Fecha: <b><?=fecha($fecha_log[0])?> <?=$fecha_log[1]?></b></td>
		    </tr>
		    <?
		    $log_recibido->MoveNext();
		    }
            ?>
		   </table>
		   <br>
		 </td>
	  </tr>   
	  <?
		}//de if($log_recibido->RecordCount()>0)
	  ?>
      <tr id=ma> 
        <td align="center" width="19%" nowrap>Deposito</td>
        <td align="center" width="10%">Cantidad</td>
        <td align="center" colspan="3">Observaciones</td>
      </tr>
      <?
		//fila actual 
		$id_fila=$datos_recibidos->fields['id_fila'];
		$dep_count=$datos_depositos->RowCount() ;
		//mientras sea la misma fila
		
		while (!$datos_recibidos->EOF && $datos_recibidos->fields['id_fila']==$id_fila)
		{
		?>
        <tr> 
         <td align="center" nowrap> 
          <?=$datos_recibidos->fields['nombre']?>
		 </td>
         <td align="center" nowrap> <input name="cantidadr_<?=$datos_recibidos->fields['id_fila'].'_'.$datos_recibidos->fields['id_deposito']?>" type="text" style="text-align:right" value="0" <?=$cant_recibido?> onchange="deshabilitar_otros_depositos(<?=$datos_recibidos->fields['id_deposito']?>,<?=$datos_recibidos->fields['id_fila']?>);calcular_recibidos(this,<?=$total_recibido?>,<?=$total_comprado?>,'<?=$nombre_producto?>',error_cant_recib_<?=$datos_recibidos->fields['id_fila'].'_'.$datos_recibidos->fields['id_deposito']?>)" size="6" <?= $permiso ?>> 
			<input type="hidden" name="clave_<?=$datos_recibidos->fields['id_fila'].'_'.$datos_recibidos->fields['id_deposito']?>" value="<?=$datos_recibidos->fields['id_recibido']?>">
			<input type="hidden" name="error_cant_recib_<?=$datos_recibidos->fields['id_fila'].'_'.$datos_recibidos->fields['id_deposito']?>" value="0">
         </td>
         <td align="center" colspan="3"><textarea <?= $permiso ?> name="obs_<?=$datos_recibidos->fields['id_fila'].'_'.$datos_recibidos->fields['id_deposito'] ?>" cols="90" rows="1" wrap="VIRTUAL"><?=$datos_recibidos->fields['observaciones'] ?></textarea></td>
        </tr>
      <?	
        $datos_recibidos->MoveNext();

	  }//de while (!$datos_recibidos->EOF && $datos_recibidos->fields['id_fila']==$id_fila)
  
	  
	  //traemos los datos de recepcion o entrega, para saber a cuál deposito se debe hacer la entrega
      //solo en caso de que no haya ninguna de las dos cosas, el usuario podrá elegir a cual
      //stock hacer las recepciones
      $query="select id_recibido,cantidad,ent_rec,id_deposito,nombre from recibidos join depositos using(id_deposito) where id_fila=$id_fila order by ent_rec desc";
      $datos_ent_rec=sql($query ,"<br>Error al traer ent_rec de la fila $id_fila") or fin_pagina();
      if($datos_ent_rec->fields["id_deposito"]!="" && $datos_ent_rec->fields["cantidad"]>0)
      {$id_dep_select=$datos_ent_rec->fields["id_deposito"];
       $nbre_dep_select=$datos_ent_rec->fields["nombre"];
      }	
      else  
      {$id_dep_select="";
       $nbre_dep_select="";
      } 

      if($id_dep_select!="")
      {?>
       <script>
       <?
       $datos_depositos->Move(0);
       while(!$datos_depositos->EOF)
       {
        if($id_dep_select!=$datos_depositos->fields['id_deposito'])
        {?>
         document.all.cantidadr_<?=$id_fila."_".$datos_depositos->fields['id_deposito']?>.readOnly=1;
         document.all.cantidadr_<?=$id_fila."_".$datos_depositos->fields['id_deposito']?>.title="Los productos se comenzaron a recibir en otro depósito";
        <?
        }
        $datos_depositos->MoveNext();	
       }//de while(!$datos_depositos->EOF)
       ?>
       </script> 
       <?	
      }//de if($deposito_recibio!="")
    }//de while (!$datos_recibidos->EOF)   
?>
    </table>
    <br>
    <table width="80%" border="0" cellspacing="1" cellpadding="1">
      <tr> 
      <td width="4%">&nbsp;</td>
      <td width="24%">&nbsp;</td>
     
      <?
      //se genera la funcion de control de las cantidades recibidas
      $datos_recibidos->Move(0);
      ?>
      <script>            
      function control_recib_pedido()
      { var campo;
      	<?
      	while(!$datos_recibidos->EOF)
      	{?>
      	 campo=eval("document.all.error_cant_recib_<?=$datos_recibidos->fields['id_fila']?>_<?=$datos_recibidos->fields['id_deposito']?>");
      	 if(campo.value==1)
	     {alert('Para el producto \"<?=$datos_recibidos->fields['descripcion_prod']." ".$datos_recibidos->fields['desc_adic']?>\":\n el total recibido es mayor que el total pedido. Debe corregir las cantidades antes de guardar los datos');
	      return false;
	     }
      	<?
      	 $datos_recibidos->MoveNext();
      	}	
      	?>
      	return true;
      }	
      
      function controles()
      { if(control_fact()==true)
       {return control_recib_pedido();
       }
       else
        return false;
        
      }	
      </script>
      
      
      <? $id_proveedor = $datos_orden->fields['id_proveedor'];
      $link_calif=encode_link("../calidad/califique_proveedor.php",array("proveedor"=>"$id_proveedor","desde"=>"2"));
      /////////////////////////////////////////////////////////////////////////////////////
      $q="select * from general.calificacion_proveedor 
          where fecha is not null and fecha>(current_date - 7) 
          and id_proveedor=$id_proveedor";
      $re=sql($q) or fin_pagina("error al buscar en la tabla $q");	 
      /////////////////////////////////////////////////////////////////////////////////////
      ?>      
      <td width="12%" align="center"><input name="boton" type="submit" id="boton" value="Guardar Datos" <?=$permiso ?> style="width:95px" onClick="if (controles()) { <?if (!$es_stock && $re->RecordCount()==0) echo "window.open('$link_calif','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=230,top=80,width=500,height=400');";?> return true;} else {return false;}"></td>
 	  <td width="12%" align="center"><input name="boton" type="button" id="boton" value="Volver" style="width:70px" onclick="location.href='<?= encode_link("ord_compra.php",array("nro_orden"=>$nro_orden)) ?>'"></td>
 	  
      <td width="14%">      
      <? if ($pagina){ ?>
  <!--    	 <input name="volver_det_merc_trans" type="button" value="Volver Mercadería en Tránsito" onclick="location.href='<? 
                     // encode_link("../merc_trans/detalle_merc_trans.php", array("id_mercaderia_transito"=>$mt_id_mt,"id_deposito"=>$mt_id_deposito,"id_producto"=>$mt_id_producto,"id_proveedor"=>$mt_id_proveedor))
                      ?>'">  -->
         <input name="volver_det_merc_trans" type="button" value="Cerrar" onclick="window.opener.document.focus();window.close()">
      <? } ?>
      </td>
      <td width="24%">&nbsp;</td>
      <td width="8%">&nbsp;</td>
    </tr>      
  </table>
<?
	$i=0;
$datos_depositos->Move(0);
 while (!$datos_depositos->EOF)
{
 //para armar el id de los depositos	
?>
  <input type="hidden" name="deposito_<?=$i++?>" value="<?=$datos_depositos->fields['id_deposito'] ?>">
<?
	$datos_depositos->MoveNext();
}
?>
  <input type="hidden" name="dep_count" value="<?=$datos_depositos->RowCount() ?>">
  <!--********Saco Broggi*******
  <input type="hidden" name="guardar_coment" value="0">
  ***************************-->
    
	<?
}// de function generar_form_recibidos()


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
{global $db,$datos_orden;
 //traemos los datos de las filas de la OC
 $items=get_items($nro_orden);
?>
 <b> PARA INDICAR LOS PRODUCTOS QUE SE ENTREGAN, INGRESE LOS CÓDIGOS DE BARRA DE CADA UNO</b>
  <br>
    <table width="100%" cellspacing=0 border=0 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
      <?
      $filas_cambios_prod=filas_con_cambios_prod($nro_orden);
      
      for($j=0;$j<$items["cantidad"];$j++)
      {
      	//traemos la informacion de los productos previamente entregados, para esta fila
      	$query="select recibidos.cantidad,recibidos.observaciones,id_recibido,entregar_sin_cb, recibidos.ent_rec,es_agregado
                from (select id_fila,cantidad,observaciones,id_recibido,ent_rec 
                      from compras.recibidos where ent_rec=0) as recibidos
                right join compras.fila using(id_fila) where id_fila=".$items[$j]['id_fila']." and (es_agregado isnull or es_agregado<>1)";
      	$datos_entregados=sql($query,"<br>Error al traer datos de entregados<br>") or fin_pagina();
      	if($datos_entregados->RecordCount())
      	{
      	?>
         <tr id=mx> 
          <td width="65%" > <div align="left"><strong>Producto </strong> &nbsp;&nbsp; 
             <?
            if($filas_cambios_prod[$items[$j]['id_fila']]["id_producto"]!="")
            { $descripcion_producto=$filas_cambios_prod[$items[$j]['id_fila']]["desc_gral"];
              $id_producto=$filas_cambios_prod[$items[$j]['id_fila']]["id_producto"];
            }
            else 
            { $descripcion_producto=$items[$j]['descripcion_prod']." ".$items[$j]['desc_adic'];
              $id_producto=$items[$j]['id_producto'];
            }
            echo $descripcion_producto; 
             
             ?>
             <input type="hidden" name="id_recibido_<?=$items[$j]['id_fila']?><?if($prov_stock==0)echo "_0"?>" value="<?=$datos_entregados->fields['id_recibido']?>">
           </div>
          </td>
          <td width="18%" nowrap>
           <strong>Comprado</strong>&nbsp; <input name="comprado_<?=$items[$j]['id_fila']?><?if($prov_stock==0)echo "_0"?>" type="text" id=mx style="text-align:right; border:none;" readonly value="<?= $items[$j]['cantidad'] ?>" size="6" >
          </td>
          <td width="18%" nowrap><strong>Entregado</strong>&nbsp;
           <input name="cantidadr_<?=$items[$j]['id_fila']?><?if($prov_stock==0)echo "_0"?>" type="text" id=mx style="text-align:right; border:none;" value="<?=($datos_entregados->fields["cantidad"])?$datos_entregados->fields["cantidad"]:0?>" readonly size="6" ></td>
        </tr>
        
        <? 
        //recordamos el total entregado que esta en la base de datos
        $total_entregado=$datos_entregados->fields["cantidad"];
        //recordamos el total comprado que esta en la base de datos
        $total_comprado=$items[$j]['cantidad'];
        //recordamos el nombre del producto
        $nombre_producto=$items[$j]['descripcion_prod']." ".$items[$j]['desc_adic'];
          
        //si la cantidad entregada es mayor o igual a la cantidad comprada, 
        //deshabilitamos el campo de cantidades, en cada caso
        if($total_entregado>=$total_comprado)
        { $cant_entregado="readonly title='Ya se ha entregado la cantidad comprada'";

        }
        else 
        { $cant_entregado="";

        }
                     
         //traemos el log de recibidos por cada producto que se muestra
		 $query="select log_recibido.*,depositos.nombre from log_recibido join recibidos using(id_recibido) left join depositos using(id_deposito) where id_fila=".$items[$j]['id_fila']." and not id_log_recibido isnull and ent_rec=0";
		 $log_entregado=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el log del entregado $query");
		 if($log_entregado->RecordCount()>0)
		 {
		 ?>
		  <tr>
		  <td colspan="3">
          <table width="95%" align="center" border=1 bordercolor=#E0E0E0 bgcolor="#ACACAC">
		    <tr>
		     <td colspan="3" align="center">
		      <font color="#006699"><b>Registro de Entregas de este producto</b></font>
		     </td>
		    </tr> 
		   <?
		    while(!$log_entregado->EOF)
		    {
            ?> 
		    <tr>
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
        {$link_cb=encode_link("movimiento_codigos_barra.php",array("desde_pagina"=>"ord_compra_fin","total_comprado"=>$total_comprado,"total_entregado"=>$total_entregado,"producto_nombre"=>"$descripcion_producto","id_producto"=>$id_producto,"id_proveedor"=>$datos_orden->fields['id_proveedor'],"nro_orden"=>$nro_orden,"id_fila"=>$items[$j]['id_fila'])); 
         $link_sin_cb=encode_link("sin_codigos_barra.php",array("desde_pagina"=>"ord_compra_fin","total_comprado"=>$total_comprado,"total_entregado"=>$total_entregado,"producto_nombre"=>"$descripcion_producto","id_producto"=>$id_producto,"id_proveedor"=>$datos_orden->fields['id_proveedor'],"nro_orden"=>$nro_orden,"id_fila"=>$items[$j]['id_fila'])); 
        }
        //si se esta generando form de entrega para OC asociada a Lic,RMA,ServTec o Presupuesto
        //y el proveedor de la OC no es un stock.
        else
        {$link_cb=encode_link("movimiento_codigos_barra.php",array("desde_pagina"=>"lic_ord_compra_fin","total_comprado"=>$total_comprado,"total_entregado"=>$total_entregado,"producto_nombre"=>"$descripcion_producto","id_producto"=>$id_producto,"id_proveedor"=>$datos_orden->fields['id_proveedor'],"nro_orden"=>$nro_orden,"id_fila"=>$items[$j]['id_fila'])); 
         $link_sin_cb=encode_link("sin_codigos_barra.php",array("desde_pagina"=>"lic_ord_compra_fin","total_comprado"=>$total_comprado,"total_entregado"=>$total_entregado,"producto_nombre"=>"$descripcion_producto","id_producto"=>$id_producto,"id_proveedor"=>$datos_orden->fields['id_proveedor'],"nro_orden"=>$nro_orden,"id_fila"=>$items[$j]['id_fila'])); 
        }
        
        if($total_comprado==$total_entregado)
          $dis_sin_cb="disabled";
        else 
          $dis_sin_cb="";  
        ?>
        <tr>
       <td width="1%"> 
        <input type="button" name="cod_barra" value="Códigos de Barra" title="Ingresar los Códigos de Barra de los productos a entregar" onclick="if(vent_cbe.closed)vent_cbe=window.open('<?=$link_cb?>','','top=130, left=250, width=350px, height=350px, scrollbars=1, status=1,directories=0');else vent_cb.focus();">
        
        <?
        $check_sin_cb="";
        if(permisos_check("inicio","permiso_entregar_sin_cb"))
        {$check_sin_cb="checked"?>
         <input type="button" name="sin_cod_barra" value="Entregar Sin CB" <?=$dis_sin_cb?> title="Entregar productos que no llevan códigos de barra" onclick="if(vent_sin_cb.closed)vent_sin_cb=window.open('<?=$link_sin_cb?>','','top=130, left=250, width=350px, height=350px, scrollbars=1, status=1,directories=0');else vent_sin_cb.focus();">
         <input type="hidden" name="hidden_sin_cb_<?=$items[$j]['id_fila']?>" value="1" >
        <?
        }
        /*
        if(permisos_check("inicio","permiso_entregar_sin_cb"))
        {?>
         <input type="checkbox" class="estilos_check" name="entregar_sin_cb_<?=$items[$j]['id_fila']?>" <?=$dis_sin_cb?> <?=$check_sin_cb?> value="1"> <b>Entregar sin CB</b>
        <?
        }
        */
        if(permisos_check("inicio","permiso_desrecibir_desentregar_fila") && $total_entregado>0)
        {
        ?>
         &nbsp;<input type="submit" name="desrecibir_desentregar_fila" value="Des-Entregar" 
               class="little_boton" style="width:100px" 
               onclick="if(confirm('¿Está seguro que desea des-entregar esta fila?'))
                        {document.all.fila_desentregar.value=<?=$items[$j]['id_fila']?>;
                         return true;
                        }
                        else
                         return false;
                       "> 
         <?
        }//de if(permisos_check("inicio","permiso_desrecibir_desentregar_fila"))
        ?>
       </td>
       </tr> 
       <tr>
        <td colspan="3">
         <HR>
         <b>Comentarios de Entrega</b>
         <textarea rows="2" cols="120" name="obs_<?=$items[$j]['id_fila']?><?if($prov_stock==0)echo "_0"?>"><?=$datos_entregados->fields['observaciones']?></textarea>
         <br><br>
        </td>
       </tr>
      <?
       }//de if($datos_entregados->RecordCount())
      }//de for($j=0;$j<$items["cantidad"];$j++) 
      ?>
      </table>
      <table width="80%" border="0" cellspacing="1" cellpadding="1">
       <tr>
        <td width="50%" align="right">
        <?
        if($prov_stock)
        {?>
         <input name="boton" type="submit" id="boton" value="Guardar Datos" <?=$permiso ?> style="width:95px">
        <?
        }
        else 
        {?>
         <input name="boton" type="submit" id="boton" value="Entregar" <?=$permiso ?> style="width:95px">
        <?
        }?> 
        </td>
 	    <td width="50%" align="left">
 	     <input name="boton" type="button" id="boton" value="Volver" style="width:70px" onclick="location.href='<?= encode_link("ord_compra.php",array("nro_orden"=>$nro_orden)) ?>'">
 	    </td>
 	    <td width="12%" align="center">
 	     <input name="boton" type="submit" id="boton" value="Generar Remito Interno" style="width:150px">
 	    </td>
 	   </tr>
 	  </table>  

      <?
}//de function generar_form_entrega($nro_orden)	

/**********************************************************************************
***********************************************************************************
FIN DE FUNCIONES PARA GENERAR LA PARTE DE RECEPCION Y ENTREGA DE PRODUCTOS
***********************************************************************************
***********************************************************************************/

extract($_POST,EXTR_SKIP);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);
//tengo estas variables que obtengo desde parametros
//mt_id_deposito - mt_id_producto - mt_id_proveedor - mt_id_mt
if ($nro_orden)
{
 $q="select * from proveedor p join 
  	 orden_de_compra o on o.id_proveedor=p.id_proveedor 
  	 where o.nro_orden=$nro_orden";
 $datos_orden=$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
 $estado=$datos_orden->fields['estado'];
 $clasif=$datos_orden->fields['clasificado']; 
 $flag_stock=$datos_orden->fields['flag_stock'];
 $internacional=$datos_orden->fields['internacional'];
 $notas = $datos_orden->fields['notas'];
 $notas_internas = $datos_orden->fields['notas_internas'];
/* if ($estado=='t')
  $permiso=" disabled ";*/

}
if (!isset ($cant_factura)) {
  if ($datos_orden->fields['cant_factura']!=NULL) $cant_factura=$datos_orden->fields['cant_factura'];
  else $cant_factura=1;
   } 
 
function cargar(&$arr){
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
 }		


 
 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Recepciones/Entregas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel=stylesheet type='text/css' href='<?=$html_root?>/lib/estilos.css'>
<?=$html_header?>
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
</head> 
<script src="<?=$html_root ?>/lib/popcalendar.js"></script>
<script language="JavaScript" src="funciones.js"></script>
<body>
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

function  limpiar(){
<?  for ($i=0;$i<$cant_factura;$i++){
  echo "document.all.id_factura_$i.value='';";
}
?>
}

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

}

function control_fact(){
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
}

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
function eliminar() {
	return window.confirm("Esta seguro que quiere eliminar "+contador+" archivos almacenados en el sistema.");
}

</script>

<form name="form1" method="post" action="proc_compra.php">
<input type='hidden' name='estado_orden' value='<? echo $estado ?>'>
<input type='hidden' name='flag_stock' value='<?=$flag_stock?>'>
<input type='hidden' name='mostrar_dolar' value='<?=$mostrar_dolar?>'>
<input type='hidden' name='tipo_lic' value='<?=$tipo_lic?>'>
<input type='hidden' name='es_stock' value='<?=$es_stock?>'>
<input type="hidden" name="fila_desentregar" value="">

<? if ($datos_orden->fields['fecha_factura'] or $datos_orden->fields['nro_factura'])
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
	case 'E':  $estado_orden="Enviada"; break;
    case 'g':
    case 'G':  $estado_orden="Totalmente Pagada"; break;
   	default:  $estado_orden="Desconocido";
  }//de switch ($estado)
	     
  if($internacional)
  {$texto_oc="<font color='#00C021'><b>Orden de Compra Internacional Nº: </b></font>";
  }
  else 	       
  {$texto_oc="<b>Orden de Compra Nº: </b>";
  }
 ?>
<center>
    <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
      <tr>
        <td height="42" colspan="2" align="center"><?=$texto_oc?>&nbsp;&nbsp;
          </strong> <input name="nro_orden" type="text" style="text-align:rigth;border:none;background-color:#cccccc" readonly value="<?=$nro_orden ?>" size="10">
        </td>
         <td height="42" align="center"><strong>Estado de la Orden de Compra &nbsp;&nbsp;
          </strong> <input name="estado" type="text" style="text-align:rigth;border:none;background-color:#cccccc" readonly value="<?=$estado_orden?>" size="10">
        </td>
      </tr>
      <tr>
       <td colspan="4">
         <input type="hidden" name="internacional" value="<?=$internacional?>"> 
         <input type="hidden" name="tipo_lic" value="<?=$tipo_lic?>"> 
         <font size="2" color="Blue"><b>Tipo de Orden de Compra: <?=$tipo_lic?></b></font>
       </td> 
      </tr>
      <tr>
        <td colspan="3"> <strong>Cantidad de facturas &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          </strong> <input name="cant_factura" type="text" onClick="document.all.cant_factura.value=''" onblur="Actualizar.click();"  size="10" value="<?=$cant_factura ?>">
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input name="Actualizar" type="button" value="Actualizar" title="Actualiza el número de facturas" onClick="form1.action='<?= $_SERVER['SCRIPT_NAME'];?>';document.form1.submit();"  <? if ($datos_orden->fields['nro_factura']) echo 'disabled' ?>>
        </td>
      </tr>
      <tr>
        <td width="4%">&nbsp; </td>
        <td width="49%" align="center"><strong> ID Factura </strong> </td>
        <td width="47%" align="center" id="td_fecha_factura"><strong>Fecha Factura</strong>&nbsp; 
        </td>
      </tr>
      <? $query_asoc="select fact_prov.fecha_emision, factura_asociadas.* from factura_asociadas join fact_prov using(id_factura) where nro_orden=$nro_orden";
		$res_asoc=sql($query_asoc) or fin_pagina();
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
				
		for ($i=0; $i<$cant_factura;$i++) {
		
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
      <? }
      ?>
      <input name="id_proveedor" type="hidden" value="<?=$datos_orden->fields['id_proveedor'] //para poder guardar los comentarios?>" > 
    </table>
    <br>
    <table width="100%">
     <tr align="left">
      <td align="left" colspan="2">
       <b><font size="4">Para seguimiento interno del material en Coradir.</font></b>
      <td>
     </tr> 
     <tr>
      <td align="left" width="70%">
       <textarea name="notas_internas" cols="80" rows="3" wrap="VIRTUAL" onkeypress="more_rows(this,5)" ><?=$notas_internas?></textarea>
      </td>
     </tr>
    </table>  
  <br>
<?
  //si el proveedor no es un stock, entonces se muestra pantalla para
  //recibir los productos.
  if(!$es_stock)
  { generar_form_recepcion($nro_orden); 
    
     //vemos el tipo de la OC
    $query="select id_licitacion,es_presupuesto,nrocaso,orden_prod from orden_de_compra 
            where nro_orden=$nro_orden";
    $tipo_oc=sql($query,"<br>Error al consultar el tipo de la OC(insertar_recibidos)<br>") or fin_pagina();
    //si la OC esta asociada a licitacion, o a presupuesto, o a RMA, o a Serv Tec,
    //generamos la parte de entregar los productos
    if($tipo_oc->fields["id_licitacion"]||$tipo_oc->fields["nrocaso"]||$tipo_oc->fields["orden_prod"])
     $generar_entrega=1;
    else 
     $generar_entrega=0; 
    /*
    //buscamos en el log, la fecha de autorizacion
    $query="select fecha from log_ordenes where nro_orden=$nro_orden and tipo_log='de autorizacion'";
    $f=sql($query,"<br>Error al traer fecha de autorizacion (form_entrega") or fin_pagina();
    $fecha_auto=split(" ",$f->fields['fecha']);
    $fecha_limite=date("2004-08-18");
    $comp=compara_fechas($fecha_auto[0],$fecha_limite);*/
    $comp=1;
    //si la fecha de autorizacion es mayor(1) o igual(0) y hay que generar entrega, generamos la parte de entrega
    if(($comp==1 ||$comp==0) && $generar_entrega)
    { echo "<hr><hr>";
      generar_form_entrega($nro_orden,0);
    }
  }//de if(!$es_stock)
  //si en cambio el proveedor SI es un stock, entonces muestra pantalla para entregar productos 
  else
   generar_form_entrega($nro_orden,1);
?>
<br>
<?$sql_archivos="select * from compras.archivos_subidos_compra where nro_orden=$nro_orden";
        $consulta_sql_archivos=sql($sql_archivos) or fin_pagina();
       if ($consulta_sql_archivos->RecordCount()!=0)
{
?>
<!--/////////////////////////////////////////////////////////////////-->
<?
  if ($msg!=" ")
     {
 ?>
 <table align="center" width="95%">
  <tr>
   <td align="center">
    <font color="Red" size="3"><b><? echo $msg; ?></b></font>
   </td>
  </tr>
 </table> 
 <br>
 <?
     }
 ?>
 <table width="95%" align="center" border="1"> 
  <tr id=mo>
   <td align="center" colspan="5"><font size="2"><b>Archivos Subidos</b></font></td>   
 <tr id=ma >
    <td align="left" colspan="5">
     <b>Documentos:</b> <? echo $consulta_sql_archivos->RecordCount(); ?>.
     <input name="cant_archivos" type="hidden" value="<? echo $consulta_sql_archivos->RecordCount(); ?>">
    </td>	
  </tr>
<tr id=mo>
 <td width='10%'><b><input type="submit" name="borrar_archivo" value="Borrar" title="Eliminar Seleccioneados" disabled onclick="return eliminar();"></b></td>
 <td width='10%'><b>Nº.</b></td>
 <td width='40%'><b>Nombre.</b></td>
 <td width='20%'><b>Fecha y Hora.</b></td>
 <td width='20%'><b>Responsable</b></td>
</tr>

<?  $i=0;
	while(!$consulta_sql_archivos->EOF){ 	
	?>

<TR id="ma" title="<?=$consulta_sql_archivos->fields["comentario"]?>">
	<TD>
    <input type="checkbox" name="eliminar_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields['id_archivo_subido']; ?>" onclick="habilitar_borrar(this);" title="Seleccione para eliminar">
    <input type="hidden" name="id_archivo_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields['id_archivo_subido']; ?>">
	</TD>
	<TD>
	<?=$consulta_sql_archivos->fields["id_archivo_subido"]?>
	</TD>
	<TD>
	<input type="hidden" name="nom_comp_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields["nombre_archivo_comp"]; ?>">
	<a target="_blank" title='<?=$consulta_sql_archivos->fields["nombre_archivo"]?> [<?=number_format($consulta_sql_archivos->fields["filesize_comp"]/1024)?> Kb]' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivo_subido"],"download"=>1,"comp"=>1,"nro_orden"=>$nro_orden,"es_stock"=>$es_stock,"mostrar_dolar"=>$mostrar_dolar,"tipo_lic"=>$tipo_lic))?>'>
	<img align=middle src=../../imagenes/zip.gif border=0></A>
	<a title = 'Abrir archivo' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivo_subido"],"download"=>1,"comp"=>0,"nro_orden"=>$nro_orden,"es_stock"=>$es_stock,"mostrar_dolar"=>$mostrar_dolar,"tipo_lic"=>$tipo_lic))?>'>
	<? echo $consulta_sql_archivos->fields["nombre_archivo"]." (".number_format(($consulta_sql_archivos->fields["filesize_comp"]/1024),"2",".","")."Kb)"?>
	</A>
	</TD>
	<TD>
	<?=$consulta_sql_archivos->fields["fecha"]?>
	</TD>
	<TD>
	<? echo $consulta_sql_archivos->fields["usuario"]?>
	</TD>
</TR>
<? $consulta_sql_archivos->MoveNext(); $i++;}?>
	<INPUT type="hidden" name="Cantidad" value="<?=$i?>">
</table>
<?
}
else echo "<table align=center><tr><td><b><font size=3>No hay Archivos para este Seguimiento.</font></b></td></tr></table>";

?>
<br>
<table align="center">
 <tr>      
   <td width="12%" align="center"><input name="boton" type="button" id="boton" value="Subir Archivos" style="width:90px" onclick="location.href='<?= encode_link("subir_archivo_ord_compra.php",array("nro_orden"=>$nro_orden,"es_stock"=>$es_stock,"mostrar_dolar"=>$mostrar_dolar,"tipo_lic"=>$tipo_lic)) ?>' "></td>
 </tr>
</table>  
</center>
</form>
</body>
</html>