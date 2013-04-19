<?

/*AUTOR: MAC 
  FECHA: 23/06/04 

$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2004/07/01 18:06:07 $
*/

require_once("../../config.php");
include("func.php");

$id_muestra=$parametros['id_muestra'];

//boton de Historial
if($_POST['guardar']=="Guardar")
{$db->StartTrans();
 $id_muestra=$_POST['id_muestra'];
 $msg="";
 //incrementamos el stock
 incrementar_stock();
 if($msg=="")
 {
  $query="update muestra set estado=2 where id_muestra=$id_muestra";
  if($db->Execute($query) OR DIE($query))
  {$fecha_hoy=date("Y-m-d H:i:s",mktime());
    $usuario=$_ses_user['name'];
    $tipo="pasado a Historial";
   	//agregamos el log de historial
   	$query="insert into log_muestra(fecha,usuario,tipo,id_muestra)
   	        values('$fecha_hoy','$usuario','$tipo',$id_muestra)";
    if($db->Execute($query) OR DIE($query))
   	 $msg="<center><b>La Muestra se actualizó con éxito</b></center>";
   	else 
   	 $msg="<center><b>No se pudo actualizar la muestra</b></center>"; 
   }
   else 
    $msg="<center><b>No se pudo actualizar la muestra</b></center>"; 
 }	  
  $link=encode_link("seguimiento_muestras.php",array("msg"=>$msg));  
 
 header("location: $link");
 $db->CompleteTrans();
 
}//de if($_POST['historial']=="Historial")

$query="select  muestra.*,entidad.nombre from muestra join entidad using(id_entidad) where id_muestra=$id_muestra";
$muestras=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer datos de la muestra $query");

if($muestras->fields['estado']==1 ||$muestras->fields['estado']==2)//si esta en curso o en historial debe aparecer todo deshabilitado
 $permiso="disabled";
 
 if(!$descripcion) 
  $descripcion=$muestras->fields['descripcion'];
 if(!$entidad)
  $entidad=$muestras->fields['nombre'];
 if(!$estado)
  $estado=$muestras->fields['estado']; 
 if(!$observaciones) 
  $observaciones=$muestras->fields['observaciones'];
 if(!$fecha_vencimiento) 
  $fecha_vencimiento=$muestras->fields['fecha_vencimiento'];
 if(!$fecha_devolucion) 
  $fecha_devolucion=$muestras->fields['fecha_devolucion'];
 if(!$id_licitacion) 
  $id_licitacion=$muestras->fields['id_licitacion'];

//traemos todos los depositos de tipo stock posibles, para generar la tabla
$depositos=$db->Execute("select * from depositos where tipo=0") or die($db->ErrorMsg()."<br>Error al traer los depositos");

echo $html_header;
         ?>
 <script>

function calcular_recibidos(cant_depositos)
{   
	var total=0;
	var total_pedido;
  <?
   $items_control=get_items_muestra($id_muestra);
   for($i=0;$i<$items_control['cantidad'];$i++)
   {?>
    total=0;
   <?
	$depositos->Move(0);
	while(!$depositos->EOF)
	{?>
		cantidad=eval("document.all.cant_inc_<?=$depositos->fields['id_deposito']?>_<?=$i?>");
		total+=parseInt(cantidad.value);
	<?
	 $depositos->MoveNext();
	}
	?>
    total_pedido=<?=$items_control[$i]['cantidad']?>;
    alert
	if(total!=total_pedido)
	{alert('Para \"<?=$items_control[$i]['descripcion']?>\":\nla cantidad del producto no coincide con la suma de las cantidades especificadas en los depositos para ese producto');
	 return false;
	}
	 <?	
   }// de for($i=0;$i<$items_control['cantidad'];$i++)
   unset($items_control);
  ?> 	
   return true;
}
 </script>
         
   <form name="form1" action="recibir_muestras.php" method="POST">
      <table border="1" width="80%" align="center" cellpadding="3" cellspacing="2">
       <tr>
        <td id=mo colspan="2">
         <font size="3">Muestra Nº <?=$id_muestra?></font>
        </td>
       </tr>
       <tr>
        <td>
         <B>Descripcion <font color="Blue"><?=$descripcion?></font></b>
        </td>
        <td>
         <b>Entidad <font color="Blue"><?=$entidad?></font></b>
        </td>
       </tr>
       <tr>
        <td>
         <b>Fecha Prevista de Devolución <font color="Blue"><?=fecha($fecha_vencimiento)?></font></b>
        </td>
        <td>
         <b>Fecha Devolución <font color="Blue"><?=fecha($fecha_devolucion)?></font></b>
        </td>
       </tr>
      </table>   
      <br>
   <table width="80%" align="center" border="1"> 
     <?
        $items=get_items_muestra($id_muestra);
        $cnr=1;
        
        //SI SE LLEGA A RECARGAR LA PAGINA EN ALGUN MOMENTO, SE PIERDEN ESTOS
        //DATOS. ENTONCES HAY QUE AGREGAR LA PARTE QUE TOMA DATOS DEL POST.
        //FUNCIONAMIENTO SIMILAR AL DE ORDENES DE COMPRA

        for($x=0;$x<$items['cantidad'];$x++)
        {

        	?>
         <input type="hidden" name="idp_<?=$x?>" value="<?=$items[$x]['id_producto']?>">	
         <input name="deposito_<?=$x?>" type="hidden" value="<?=$items[$x]['id_deposito']?>">
         <input name="proveedor_<?=$x?>" type="hidden" value="<?=$items[$x]['id_proveedor']?>">
         <input name="cant_<?=$x?>" type="hidden" value="<?=$items[$x]['cantidad']?>">
         <input name="desc_<?=$x?>" type="hidden" value="<?=$items[$x]['descripcion']?>">
       <tr>
       <td>
        <table width="100%"> 
         <tr id="ma">
           <td align="center" width="80%">
            <font color="Black"><b><?=$items[$x]['descripcion']?></b></font>
          </td>
          <td align="center" width="20%">
           <font color="Black"> Cantidad: <b><?=$items[$x]['cantidad']?></b></font>
          </td>
         </tr>
         <tr>
          <td colspan="2">
           <table align="right" border="1" cellpadding="0" cellspacing="0">
            <tr>
            <td>
             <table border="1" cellpadding="0" cellspacing="0">
              <tr id="ma">
              <td >
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
              <input type="text" size="4" name="cant_inc_<?=$depositos->fields['id_deposito']?>_<?=$x?>" value="0">
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
      </td>
      </tr>  
      </table> 
       <?
   
        }//de for($x=0;$x<$items['cantidad'];$x++)
         $total_muestras=$items['total_muestra'];
         $items=$x;

        ?>
        <input type="hidden" name="items" value="<?=$items?>">
        <input type="hidden" name="id_muestra" value="<?=$id_muestra?>">
       </table>
      </td>
     </tr>
    </table>      
    <table width="80%" align="center">
     <tr>
      <td align="center">
       <input type="submit" name="guardar" value="Guardar" onclick="return calcular_recibidos(<?=$depositos->RecordCount()?>)">
       <?
       $link_volver=encode_link("detalle_muestras.php",array("id_muestra"=>$id_muestra)); 
       ?>
       <input type="button" name="volver" value="Volver" onclick="document.location='<?=$link_volver?>'">
      </td>
     </tr>
    </table>  
  </form>          
</body>
</html>