<?

/*AUTOR: MAC

$Author: fernando $
$Revision: 1.7 $
$Date: 2004/07/15 22:24:19 $
*/

require_once("../../config.php");
include("func.php");

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);
	
$items=0;
	
if($_POST['guardar']=="Guardar")
{//incluimos el archivo de funciones relacionadas a las ordenes de compra
 //para poder utilizar la funcion autogenerar_nota_credito
 include("../ord_compra/fns.php");
 
 $db->StartTrans();
 $comentarios=$_POST['comentarios'];
 $id=$_POST['id_reclamo_partes'];
 $nro_orden=$_POST['nro_orden'];
 $proveedor=$_POST['select_proveedor'];
 $descripcion=$_POST['descripcion'];
 $nro_caso=$_POST['nro_caso'];
 $id_nota_credito=$_POST['id_nota_credito'];

 if($_POST['pasar_historial']==1)
  $estado=1;
 else  
  $estado=0;
 if($nro_caso && ($nro_orden=="" ||$nro_orden=="No posee"))
 {$query="select idcaso from casos_cdr where nrocaso='$nro_caso'"; 
  $caso=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al sacar el id del caso");

  if($caso->RecordCount()==0)
  {$msg="<b><center>El C.A.S. Nº $nro_caso, no existe o no es válido.</center></b>";
   $db->CompleteTrans();
   $link=encode_link("seguimiento_reclamo_partes.php",array("msg"=>$msg));  
   header("location: $link");	 
   echo "&nbsp;";
  }
  else 
   $idcaso=$caso->fields['idcaso']; 
 }
 else
  $idcaso="null";

 //si tenemos el id entonces actualizamos 
 if($id)
 {
  if($_POST['generar_nc']!="" && $_POST['generar_nc']==0)
   $recibi_partes=1;
  elseif($_POST['generar_nc']==1)
   $recibi_partes=0;
  else 
   $recibi_partes="null";  

  if($nro_orden!="" && $nro_orden!="No posee")
   $query="update reclamo_partes set estado=$estado, comentarios='$comentarios',recibi_partes=$recibi_partes where id_reclamo_partes=$id";
  else
   $query="update reclamo_partes set estado=$estado, comentarios='$comentarios',recibi_partes=$recibi_partes,idcaso=$idcaso,descripcion='$descripcion',id_proveedor=$proveedor where id_reclamo_partes=$id";

   if($db->Execute($query))
   {$fecha_hoy=date("Y-m-d H:i:s",mktime());
    $usuario=$_ses_user['name'];
    $tipo="modificación";
   	//agregamos el log de modificacion del reclamo de partes
   	$query="insert into log_reclamo_partes(fecha,usuario,tipo,id_reclamo_partes)
   	        values('$fecha_hoy','$usuario','$tipo',$id)";
    if($db->Execute($query))
    {
     //si el estado se pasa a historial
     //agregamos el log de historial del reclamo de partes
   	 if($estado==1)
   	 {$tipo="pasado a historial";
   	  $query="insert into log_reclamo_partes(fecha,usuario,tipo,id_reclamo_partes)
   	        values('$fecha_hoy','$usuario','$tipo',$id)";
   	  if($db->Execute($query))
       $msg="<center><b>El reclamo de partes se actualizó con éxito</b></center>";
      else
       $msg="<center><b>No se pudo actualizar el reclamo de partes</b></center>"; 
   	 }
   	 else 
   	  $msg="<center><b>El reclamo de partes se actualizó con éxito</b></center>";
    }
   	else 
   	 $msg="<center><b>No se pudo actualizar el reclamo de partes</b></center>"; 
   }
   else 
    $msg="<center><b>No se pudo actualizar el reclamo de partes</b></center>"; 
 }//de if($_POST['generar_nc']==0)
 else//sino, lo insertamos
 {//reservamos el id  para insertar
  $query="select nextval('reclamo_partes_id_reclamo_partes_seq') as id_reclamo_partes";
  $id_val=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de reclamo");
  $id=$id_val->fields['id_reclamo_partes'];
  $query="insert into reclamo_partes(id_reclamo_partes,estado,comentarios,idcaso,descripcion,id_proveedor)
          values($id,0,'$comentarios',$idcaso,'$descripcion',$proveedor)"; 
  if($db->Execute($query) or die($db->ErrorMsg()."$query"))
  {$fecha_hoy=date("Y-m-d H:i:s",mktime());
    $usuario=$_ses_user['name'];
    $tipo="creación";
   	//agregamos el log de creción del reclamo de partes
   	$query="insert into log_reclamo_partes(fecha,usuario,tipo,id_reclamo_partes)
   	        values('$fecha_hoy','$usuario','$tipo',$id)";
    if($db->Execute($query))
   	 $msg="<center><b>El reclamo de partes se actualizó con éxito</b></center>";
   	else 
   	 $msg="<center><b>No se pudo actualizar el reclamo de partes</b></center>"; 
  }
  else 
   $msg="<center><b>No se pudo insertar el reclamo de partes</b></center>"; 
 }	  

 //insetamos los productos del reclamo de partes
 //(solo si no esta asociada a una orden)
 if(!$nro_orden || $nro_orden=="No posee")
  insertar_partes($id);
 
 $db->CompleteTrans();
 
 //si se selecciono el radio de generar la nota de credito 
 //se pasa a la pagina para armar la nota de credito 
 //(antes se autogeneraba la nota de credito con la funcion autogenerar_nota_credito)
 if($_POST['generar_nc']==1 && $id && !$id_nota_credito)
 {$descripcion="Nota de Crédito generada a partir del reclamo de partes Nº $id";
  $proveedor=$_POST['h_id_proveedor'];
  $link=encode_link("../ord_compra/nota_credito.php",array("descripcion"=>$descripcion,"id_proveedor"=>$proveedor,"pagina"=>"reclamo_partes","id_reclamo_partes"=>$id,"msg"=>$msg));  
 } 
 else 
 {$link=encode_link("seguimiento_reclamo_partes.php",array("msg"=>$msg));  
 }
 
 header("location: $link");	
 
}		
	
echo $html_header;
?>
<script>

//FUNCIONES PARA AGREGAR Y ELIMINAR PRODUCTOS
//variable que contiene la ventana hijo productos
var wproductos=0;
function cargar()
{
/*Para insertar una fila*/
var items=document.all.items.value++;
//inserta al final
var fila=document.all.productos.insertRow(document.all.productos.rows.length );
//inserta al principio
//var fila=document.all.productos.insertRow(1);


fila.insertCell(0).innerHTML="<input type='hidden' id='' value='' name='idprov_"+
items+"'> <div align='center'> <input name='chk' type='checkbox' id='chk' value='1'></div><input type='hidden' name='idp_"+
items +"' value='"+ wproductos.document.all.select_producto.value+"'>";

fila.insertCell(1).innerHTML="<div align='center'><textarea name='desc_"+
items +"' cols='85' rows='1' wrap='VIRTUAL' id='descripcion'>"+
wproductos.document.all.select_producto[wproductos.document.all.select_producto.selectedIndex].text +"</textarea></div>";

fila.insertCell(2).innerHTML="<div align='center'> <input name='cant_"+
items+"' type='text' id='cantidad' size='6' value='1' style='text-align:right' "+
" ></div>";

document.all.guardar_hidden.value++;

}

function nuevo_item()
{var pagina_prod;
 var nbre_prov;
 var stock_page;
 if (wproductos==0 || wproductos.closed)
 {   nbre_prov=document.all.select_proveedor[document.all.select_proveedor.selectedIndex].text;
     pagina_prod="<?=encode_link('../ord_compra/seleccionar_productos.php',array('onclickcargar'=>"window.opener.cargar();",'onclicksalir'=>'window.close()','cambiar'=>0)) ?>"
     	wproductos=window.open(pagina_prod+'&id_proveedor='
	    +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	    ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=0,left=125,top=10,width=600,height=300');
        
 }	
 else
  if (!wproductos.closed)
   wproductos.focus();
}
/*************************************************/

function borrar_items()
{
var i=0,j=0;var aux;

 while (typeof(document.all.chk)!='undefined' &&
		 typeof(document.all.chk.length)!='undefined' &&
		 i < document.all.chk.length)
  {
   /*Para borrar una fila*/
   if (document.all.chk[i].checked)
   {
   	//eliminamos el id del producto del hidden, para indicar que no se debe 
    //volver a insertar
    aux=eval("document.all.idp_"+j);
    aux.value="";
   	document.all.productos.deleteRow(i+1);
    
   }
   else
  	i++;
   j++;	
  }//del while

  if (typeof(document.all.chk)!='undefined' && document.all.chk.checked)
  {//eliminamos el id del producto del hidden, para indicar que no se debe 
    //volver a insertar
    aux=eval("document.all.idp_"+j);
    aux.value="";
   document.all.productos.deleteRow(1);
  }


}

//funcion que controla que se carguen algunos datos obligatorios

function control_datos()
{
  if(document.all.descripcion.value=="")
  {alert('Debe ingresar una descripción para el reclamo de partes');
   return false;
  }	 
  if(document.all.select_proveedor.value==-1)
  {alert('Debe seleccionar un proveedor para el reclamo de partes');
   return false;
  }
 

 return true;
}	

</script>
<?

if($id_reclamo_partes)
{$query="select reclamo_partes.*,pr.razon_social,pr.id_proveedor,caso.nrocaso
        from reclamo_partes join (select razon_social,id_proveedor from proveedor) as pr using(id_proveedor) left join (select nrocaso,idcaso from casos_cdr) as caso using(idcaso) where id_reclamo_partes=$id_reclamo_partes";
 $reclamo_partes=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer datos del reclamo de partes $query");

if($reclamo_partes->fields['estado']==1)//si esta en hisotrial debe aparecer todo deshabilitado
 $permiso="disabled";
 
 if(!$id_reclamo_partes)
  $id_reclamo_partes=$reclamo_partes->fields['id_reclamo_partes'];
 if(!$descripcion) 
  $descripcion=$reclamo_partes->fields['descripcion'];
 if(!$orden_de_compra && $reclamo_partes->fields['nro_orden'])
  $orden_de_compra=$reclamo_partes->fields['nro_orden'];
 elseif(!$orden_de_compra)
  $orden_de_compra="No posee";
 if(!$nro_caso)
  $nro_caso=$reclamo_partes->fields['nrocaso'];
 if(!$id_proveedor)
  $id_proveedor=$reclamo_partes->fields['id_proveedor'];
 if(!$id_nota_credito && $reclamo_partes->fields['id_nota_credito'])
  $id_nota_credito=$reclamo_partes->fields['id_nota_credito'];
 if(!$estado)
  $estado=$reclamo_partes->fields['estado'];
 if(!$comentarios)
  $comentarios=$reclamo_partes->fields['comentarios'];
 if(!$recibi_partes)
  $recibi_partes=$reclamo_partes->fields['recibi_partes'];
}

if($orden_de_compra!="No posee" && $orden_de_compra!="")
 $oc_disabled="disabled";
else 
 $oc_disabled="";

$link=encode_link("detalle_reclamo_partes.php",array("pagina"=>$pagina));

//traemos y luego generamos el log del reclamo de partes
if($id_reclamo_partes)
{$query="select * from log_reclamo_partes where id_reclamo_partes=$id_reclamo_partes";
 $log=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer log");
?>
<center>
<div style='position:relative; width:86%; height:13%; overflow:auto;'>
<table width="100%" cellpadding="1" cellspacing="1" align="center">
<?
while(!$log->EOF)
{list($fecha,$hora)=split(" ",$log->fields['fecha']);
 ?>
 <tr id=ma>
  <td align="left">
   Fecha de <?=$log->fields['tipo']?>: <?=fecha($fecha)?> <?=$hora?>
  </td>
  <td align="right">
   Usuario: <?=$log->fields['usuario']?>
  </td>
 </tr> 
 <?
 $log->MoveNext();
}
?>
</table>
</div>
</center>
<?
}//de if($id_reclamo_partes)
?>
<form name="form1" method="POST" action="<?=$link?>">
<table width="85%" align="center" border="1">
<tr>
 <td align="center">
  <table width="100%" cellpadding="5">
   <tr>
    <td align="center" colspan="2" id=mo>
     <font size="3"><b>Reclamo de Partes Nº <?=$id_reclamo_partes?></b></font>
    </td>
   </tr>
   <tr>
    <td colspan="2">
     <b>Descripción </b>
     <textarea name="descripcion"  rows=5 style='width:100%;' <?=$permiso?> <?=$oc_disabled?> ><?=str_replace("<br>"," \n","$descripcion")?></textarea>


     <!--
     <input type="text" name="descripcion" value="<?=str_replace("<br>"," \n\r","$descripcion")?>" <?=$permiso?> <?=$oc_disabled?> size="87">
     -->
    </td>
   </tr>
   <tr>
    <td width="50%">
     <b>Orden de Compra Nº</b> <?=$orden_de_compra?>
    </td>
    <td width="50%">
     <b>C.A.S. Nº</b> <input type="text" name="nro_caso" value="<?=$nro_caso?>" size="17" <?=$permiso?> <?=$oc_disabled?>>
    </td>
   <tr>
   <tr>
    <td>
     <b>Proveedor</b> 
     <?//traemos los poveedores para mostrar en un combo
      $query="select razon_social,id_proveedor from proveedor order by razon_social";
      $proveedores=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer datos de los proveedores"); 
     ?>
     <select name="select_proveedor" <?=$permiso?> <?=$oc_disabled?>>
      <option value="-1">Seleccione un proveedor</option>
      <?
       while(!$proveedores->EOF)
       {?>
        <option value="<?=$proveedores->fields['id_proveedor']?>" <?if($id_proveedor==$proveedores->fields['id_proveedor'])echo "selected"?>><?=$proveedores->fields['razon_social']?></option>
       <?
       	$proveedores->MoveNext();
       }	
       ?>
      </select> 
    </td>
    <input type="hidden" name="h_id_proveedor" value="<?=$id_proveedor?>">
    <td>
    <?
    if($id_nota_credito)
    {?>
     <b>Nota de Crédito Generada Nº</b> <?=$id_nota_credito?>
    <?
    }	
    ?>
    </td>
   </tr>
  </table>   
 </td>
</tr>
</table>
<br>
<table width="85%" align="center" border="1">
 <tr>
  <td>
   <table width="100%" align="center">
    <tr>
     <td align="center">
      <font size="2" color="blue"><b>Productos del Reclamo de Partes</b></font>
      <hr>
     </td>
    </tr>
    <tr>
     <td>
      <table width="100%" align="center" id="productos">
       <tr id=mo>
        <td width="1%">
         &nbsp;
        </td>
        <td>
         Descripción
        </td>
        <td>
         Cantidad
        </td>
       </tr>
       <?
       if($id_reclamo_partes)
       {//traemos los productos de la orden de compra
        $items=get_items_rp($id_reclamo_partes);
        $cnr=1;
        
        //SI SE LLEGA A RECARGAR LA PAGINA EN ALGUN MOMENTO, SE PIERDEN ESTOS
        //DATOS. ENTONCES HAY QUE AGREGAR LA PARTE QUE TOMA DATOS DEL POST.
        //FUNCIONAMIENTO SIMILAR AL DE ORDENES DE COMPRA

        for($x=0;$x<$items['cantidad'];$x++)
        {

        	?>
         <input type="hidden" name="idp_<?=$x?>" value="<?=$items[$x]['id_producto']?>">	
         <tr>
          <td>
           <input type="checkbox" <?=$oc_disabled?> <?=$permiso?> name="chk" value="1" id="chk">
          </td>
          <td>
           <div align="center">
            <textarea name="desc_<?=$x?>" cols="85" rows="1" wrap="VIRTUAL" <?=$oc_disabled?> <?=$permiso?> id="descripcion"><?=stripcslashes($items[$x]['descripcion']) ?></textarea>
           </div>
          </td>
          <td align="center">
           <input name="cant_<?=$x?>" type="text" size="6" <?=$oc_disabled?> <?=$permiso?> style='text-align:right' value="<?=$items[$x]['cantidad'] ?>" >
          </td>
         </tr>
        <?
         $total+=$items[$x]['cantidad']*$items[$x]['precio_unitario'];
        }//de for($x=0;$x<$items['cantidad'];$x++)
         $items=$x;
       }//de if($id_reclamo_partes)
        ?>
        <input type="hidden" name="items" value="<?=$items?>">
       </table>
       <table width="100%"> 
        <tr>
         <td colspan="5" align="center">
          <br>
          <input type="button" name="Agregar" value="Agregar" <?=$oc_disabled?> <?=$permiso?> onclick="nuevo_item()">
          <input type="button" name="Eliminar" value="Eliminar" <?=$oc_disabled?> <?=$permiso?>
           onclick=
           "
           if (confirm('¿Está seguro que desea eliminar los items seleccionados ?'))
	        borrar_items()
	       "
          >
         </td>
        </tr> 
       </table>
     </td>
    </tr>
   </table> 
  </td>
 </tr>
</table>

<br>
<input type="hidden" name="id_nota_credito" value="<?=$id_nota_credito?>">
<input type="hidden" name="id_reclamo_partes" value="<?=$id_reclamo_partes?>">
<input type="hidden" name="nro_orden" value="<?=$orden_de_compra?>">
<table width="85%" align="center" border="1">
 <tr> 
  <td align="center"> 
   <table>
   <?
   if($id_reclamo_partes)     
   {
   ?>
    <tr>
     <td align="center">
     <?
      //si el estado es historial y el id de nota de credito existe
      //checkeamos el primer check
      if($id_nota_credito!="")
       $checkeo_nc="checked";
      //sino, si el estado es historial y
      //se recibió la parte, entonces checkeamos el segundo check
      elseif($recibi_partes==1)
       $checkeo_partes="checked";

     ?>
      <input type="radio" name="generar_nc" <?=$permiso?> value=1 <?=$checkeo_nc?>> Generar Nota de Crédito&nbsp;&nbsp;&nbsp;
      <input type="radio" name="generar_nc" <?=$permiso?> value=0 <?=$checkeo_partes?>> Se recibieron las partes
     </td> 
    </tr>
   <?
   }
   ?> 
    <tr>
    </tr>
    <tr>
     <td align="center">
      <br><b>Comentarios</b>
     </td>
    </tr>
    <tr>
     <td align="center">
      <textarea cols="100" rows="5" name="comentarios" <?=$permiso?>><?=$comentarios?></textarea>
     </td>
    </tr>
   </table>
  </td>
 </tr>  
</table> 
<?
//si el estado es 0 (pendiente), y se recibio la parte o se hizo la nota de credito
// le damos la opcion de pasar el reclamo a historial
if($estado==0 && $id_reclamo_partes && ($recibi_partes==1 || $id_nota_credito))
{
?>
<br>
<table width="85%" align="center" border="1">
 <tr>
  <td>
   <input type="checkbox" name="pasar_historial" <?=$permiso?> value="1" <?if($estado==1)echo "checked"?>> <b>Pasar este reclamo de partes a historial</b>
  </td>
 </tr>
</table>
<?
}//de if($estado==0)
?>
<br>
<input type="hidden" name="guardar_hidden" value="0">
<div align="center">
 <?
 if($permiso!="disabled")//si esta disabled significa que el estado es historial
 {                       //por lo tanto el boton de guardar se muestra solo en caso que no este en historial
                         //(por lo tanto esta en pendiente)
  if($oc_disabled=="disabled")
   $controles="";
  else 
   $controles="onclick='return control_datos()'";
  if($id_reclamo_partes)
   $si_id=1;
  else  
   $si_id=0; 
 ?>
 <input type="submit" name="guardar" value="Guardar" <?=$controles?>>
 <?
 }
 ?>
 <input type="button" name="volver" value="Volver" onclick="document.location='seguimiento_reclamo_partes.php'">
</div>
</form>
</body>
</html>