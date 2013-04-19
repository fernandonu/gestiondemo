<?
/*
Autor: MAC 
Fecha: 06/07/04

$Author: marco_canderle $
$Revision: 1.26 $
$Date: 2005/11/02 21:12:01 $
*/
require_once("../../config.php");

//print_r($parametros);

$id_proveedor=$parametros['id_proveedor'] or $id_proveedor=$_POST['id_proveedor'];
$id_producto=$parametros['id_producto'] or $id_producto=$_POST['id_producto'];
$id_deposito=$parametros['id_deposito'] or $id_deposito=$_POST['id_deposito'];
$id_info_rma=$parametros['id_info_rma'] or $id_info_rma=$_POST['id_info_rma'];
$cant_cb=$parametros['cant_cb'] or $cant_cb=$_POST['cant_cb'];
$id_control_stock=$parametros['id_control_stock'] or $id_control_stock=$_POST['id_control_stock'];

//if(!permisos_check("inicio","borrar_documentacion") ) $permiso_borrar = "Disabled";

if ($_POST["guardar_ubicacion"]){
  $ubicacion=$_POST["ubicacion"];
  $sql="update stock set ubicacion='$ubicacion'
        where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor";
if (sql($sql) or fin_pagina()) Aviso ("Se guardo la ubicación con éxito");
  } //del if de guardar ubicacion

if ($parametros["download"]) {
	$sql = "select * from archivos_subidos where id_archivos_subidos = ".$parametros["FileID"];
	$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
	

	if ($parametros["comp"]) {
		$FileName = $result->fields["nombre_archivo_comp"];		
		$FileNameFull = UPLOADS_DIR."/stock/RMA/$FileName";		
		$FileType="application/zip";
		$FileSize = $result->fields["filesize_comp"];
		FileDownload(1,$FileName,$FileNameFull,$FileType,$FileSize);
	} else {
		$FileName = $result->fields["nombre_archivo"];
		$FileNameFull = UPLOADS_DIR."/stock/RMA/$FileName";
		$FileType = $result->fields["filetype"];
		$FileSize = $result->fields["filesize"];
		FileDownload(0,$FileName,$FileNameFull,$FileType,$FileSize);
	}	
}


if($_POST["borrar_rma"]=="Eliminar de RMA")
{include_once("funciones.php");
 $db->StartTrans();
 $cantidad_1=$_POST["cantidad_1"];
 $id_producto=$_POST["id_producto"];
 $id_proveedor=$_POST["id_proveedor"];
 $id_deposito=$_POST["id_deposito"];
 $id_info_rma=$_POST["id_info_rma"];
 descontar_stock($cantidad_1,$id_producto,$id_proveedor,$id_deposito,$id_info_rma,2);
 $fecha_hoy=date("Y-m-d H:i:s",mktime());
 $sql="update info_rma set user_historial='".$_ses_user['name']."',fecha_historial='$fecha_hoy',garantia_vencida=1 where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
 $consulta_sql=sql($sql) or fin_pagina();
 $db->CompleteTrans();
 $link=encode_link("stock_descontar_rma.php",array("msg"=>$msg,"id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_deposito"=>$id_deposito,"id_info_rma"=>$id_info_rma,"pagina_listado"=>"historial","cant_cb"=>$cantidad_1));
 header("Location:$link");    
 
 $db->CompleteTrans();
}//de if($_POST["borrar_rma"]=="Eliminar de RMA")

if ($_POST["borrar"]=="Borrar") {
	$i=0;
	$err=1; $elim = 0;
	while($i<$_POST["Cantidad"] && $err) {
		if ($_POST["select_$i"]!=""){
			$db->StartTrans();
			$sql = "Select * from archivos_subidos where id_archivos_subidos=".$_POST["select_$i"];
			$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
				
			$sql = "delete from archivos_subidos where id_archivos_subidos = ".$_POST["select_$i"];			
			if (!$db->Execute($sql)){ 
				$msg="<font color='red'>Error el archivo ".$result->fields["name"]." no pudo eliminarse de la base de datos.</font>";
				$err=0;
			} else {
				if ($result->fields["name_comp"] != '')
					$FileName = ROOT_DIR."/uploads/stock/RMA/".$result->fields["nombre_archivo_comp"];
				else
					$FileName = ROOT_DIR."/uploads/stock/RMA/".$result->fields["nombre_archivo"];
				if (!unlink(enable_path($FileName))) {
					$msg="<font color='red'>Error el archivo ".$result->fields["nombre_archivo"]." no pudo eliminarse fisicamente.</font>";
					$err=0;
					$db->RollBackTrans();
				} else $elim++;
			}
			$db->CompleteTrans();
		}
		$i++;
	}
	if ($err) {
		$msg="<font color='green'>Se eliminaron $elim Archivos.</font>";			
	}
}

$msg=$parametros['msg'];
if($_POST['guardar']=="Guardar")
{$control=0; 
 $fecha=date("Y-m-d H:i:s",mktime());
 $user=$_ses_user['name'];
 //////////////////////////////////////////////////////////////////////////////////////////////
 /*aca pongo todo lo necesario para el caso de 
 que cambie de producto o proveedor o ambos*/
 $prod_viejo=$_POST['prod_viejo'];
 $prod_nuevo=$_POST['prod_nuevo'];
 if ($prod_nuevo!=$prod_viejo || $_POST['id_prov_nuevo']!=$_POST['id_prov_viejo'])
    {$control=1;
     //Guardo los comentarios de cambio de producto o proveedor
     $query="select nextval('log_cambio_prod_id_log_cambio_prod_seq') as id_log_cambio_prod";
     $id_log_cambio_prod=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de log de cambio de producto o proveedor (agregar_RMA)");           
     $id_log=$id_log_cambio_prod->fields['id_log_cambio_prod'];
     $comentario=$_POST['comentario_cambio'];
     
     $query="insert into log_cambio_prod(id_log_cambio_prod,id_info_rma,id_deposito,id_producto,id_proveedor,usuario,fecha,comentario)
             values ($id_log,$id_info_rma,$id_deposito,$id_producto,$id_proveedor,'$user','$fecha','$comentario')";
     
     $log=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en el log de cambio de producto o proveedor (agregar_RMA)");      
     
     //*************************************************************************************//
     $sql = "select id_ubicacion from info_rma where id_info_rma=$id_info_rma";
     $id_ubi=$db->Execute($sql) or die($db->ErrorMsg()."<br>Error al traer id de ubicacion de RMA $sql");
     if ($id_ubi->fields['id_ubicacion']!="" && $id_ubi->fields['id_ubicacion']>=$_POST['ubicacion_parte']) {$id_ubi=$id_ubi->fields['id_ubicacion'];}
     else $id_ubi=$_POST['ubicacion_parte'];     
       $db->StartTrans();
        //agregamos cada producto por separado al stock RMA con el proveedor
        //seleccionado en la OC (el proveedor que viene en proveedor_reclamo)
        //y luego completamos la informacion correspondiente en la tabla info_rma        
        //seleccionamos el id del deposito RMA
        $query="select id_deposito from depositos where nombre='RMA'";
        $dep_rma=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer id del deposito de RMA");
        $id_dep=$dep_rma->fields['id_deposito'];
        $id_prov=$_POST['id_prov_nuevo'];    
        $obs="Modificación realizada por Cambio de producto o Proveedor";        
           	$id_prod=$_POST['prod_nuevo'];           	            
            $cantidad=$_POST['cantidad_1'];            
            $precio=$_POST['precio'];
            //primero busco si tiene precio ese producto
            //con ese proveedor                                    
	        $sql=" select id_producto,precio from precios ";
	        $sql.="where id_producto=$id_prod ";
	        $sql.="and id_proveedor=$id_prov ";
	        $result=$db->execute($sql) or die($db->ErrorMsg()."<br> Error al revisar los precios (agregar_RMA) <br>$sql");
	        $cant_precios=$result->recordcount();
	        if($cant_precios==0)
	          {insertar_precio($id_prod,$id_prov,$precio);//si no esta el precio lo inserto
	          }
	        elseif ($cant_precios->fields['precio']==0)  
	               {$sql="update precios set precio=$precio where id_producto=$id_prod and id_proveedor=$id_prov";//si ya existe pero es 0 lo modifico
	                $pre=$db->execute($sql) or die($db->ErrorMsg()."<br> Error al actualizar los precios (agregar_RMA) <br>$sql");
	               }
            if($cantidad!="" && $cantidad>0)
              {//revisamos si esta la entrada para ese producto, proveedor, deposito, en el stock.
       	       $query="select count(*)as cuenta from stock where id_producto=$id_prod and id_deposito=$id_dep and id_proveedor=$id_prov";
       	       $esta=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al seleccionar el stock (agregar_RMA)");
       	       if($esta->fields['cuenta']==0)
       	         {$fecha_modif=date("Y-m-d H:i:s",mktime());
       	          $sql="insert into stock(id_producto,id_deposito,id_proveedor,cant_disp,comentario,last_user,last_modif)
       	                values($id_prod,$id_dep,$id_prov,$cantidad,'$obs','".$_ses_user['login']."','$fecha_modif')";	
       	         }
       	       else {
       	       	     $sql="update stock set ";
	                 $sql.="cant_disp=cant_disp+$cantidad,";
	                 $sql.=" comentario='$obs' ";
	                 $sql.=" where ";
	                 $sql.="id_producto=$id_prod ";
	                 $sql.=" AND id_deposito=$id_dep ";
	                 $sql.=" AND id_proveedor=$id_prov";
       	            }       	 
	           $db->execute($sql) or die($db->ErrorMsg()."<br>Error al insertar en stock (agregar_RMA)<br>$sql");	    
	           //registramos en el historial el incremento de stock
               $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
               $id_control_stock_con=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock (agregar_RMA)");  
               $id_control_stock_nuevo;
               $fecha_modif=date("Y-m-d H:i:s",mktime());
               //Insertamos en la tabla info_rma los datos correspondientes 
	           //a la orden de compra y demas datos necesarios	  
	           $query="select nextval('info_rma_id_info_rma_seq')as id_info_rma";
	           $id_info=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer secuencia de info_rma (agregar_RMA)");
	           $id_info_rma_nuevo=$id_info->fields['id_info_rma'];
	           $nro_orden=$_POST['nro_comp'];
	           
	           if ($_POST['nro_prod']!="") $ordprod=$_POST['nro_prod']; else $ordprod="NULL";
	           if ($_POST['nro_caso']!="") $nrocaso=$_POST['nro_caso']; else $nrocaso=" ";          	           
	           $query="insert into info_rma(id_info_rma,id_deposito,id_producto,id_proveedor,nro_ordenc,nro_ordenp,nrocaso,cantidad,id_ubicacion)
	                   values ($id_info_rma_nuevo,$id_dep,$id_prod,$id_prov,$nro_orden,$ordprod,$nrocaso,$cantidad,$id_ubi)";
               $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar info de rma (agregar_RMA) $query");         
               $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                       values(".$id_control_stock_con->fields['id_control_stock'].",'$fecha_modif','Orden de Compra Nº $nro_orden','Incremento generado por cambio de producto o proveedor del info_rma=$id_info_rma','is')";
               $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock (agregar_RMA)");                      
	           $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc,id_info_rma)
	                   values($id_dep,$id_prod,$id_prov,".$id_control_stock_con->fields['id_control_stock'].",$cantidad,$id_info_rma_nuevo)";
	           $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento (agregar_RMA)");	  
	           $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	                   values (".$id_control_stock_con->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Incremento generado por cambio de producto o proveedor del info_rma=$id_info_rma')";
	           $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock (agregar_RMA)");	     	     	          
              }//de if($cantidad!="" && $cantidad>0)
           //}//de for($i=0;$i<$items_rma['cantidad'];$i++) 
       //$db->CompleteTrans();
      //}//de la funcion todabia no se si queda asi
      	
     //*************************************************************************************//    	    	    	     
    }//de cuando entra porque se cambio el producto o el proveedor    
 ///////////////////////////////////////////////////////////////////////////////////////////////
 
 
 if ($_POST['nuevo_coment']!="")//esto es para cuando apreta guardar y no puso ningun comentario                                
    {$db->StartTrans();         //es el caso en que apreta guardar par guardar la ubicacion de la parte     
     $texto=$_POST['nuevo_coment'];
     $query="insert into comentarios_rma(usuario,fecha,texto,id_producto,id_deposito,id_proveedor,id_info_rma) 
             values('$user','$fecha','$texto',$id_producto,$id_deposito,$id_proveedor,$id_info_rma)";
     if($db->Execute($query))
        $msg="<center><b>El comentario se guardó con éxito</b></center>";
     else
        $msg="<center><b>El comentario no se pudo guardar</b></center>"; 
     $db->CompleteTrans();	
    }
 //$db->StartTrans();
  $query="update info_rma set id_ubicacion=".$_POST['ubicacion_parte']." 
          where id_info_rma=$id_info_rma and id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor";
  if($db->Execute($query))
        $msg2="<center><b>Los cambios se Efectuaron con Exito</b></center>";
  else
        $msg2="<center><b>Los cambios no se pudieron guardar</b></center>"; 
  $query="select * from log_ubicacion where id_info_rma=$id_info_rma and id_deposito=$id_deposito 
          and id_producto=$id_producto and id_ubicacion=".$_POST['ubicacion_parte']." and id_proveedor=$id_proveedor";
  $resultado_query=sql($query) or fin_pagina();
  if ($resultado_query->RecordCount()==0)
     {$sql = "select nextval('log_ubicacion_id_log_ubicacion_seq') as id";
      $id_rec = sql($sql) or fin_pagina();
      $query="insert into log_ubicacion (id_log_ubicacion,usuario,fecha,id_info_rma,id_deposito,id_producto,id_proveedor,id_ubicacion)
              values (".$id_rec->fields['id'].",'$user','$fecha',$id_info_rma,$id_deposito,$id_producto,$id_proveedor,".$_POST['ubicacion_parte'].")";
      $resultado_query=sql($query) or fin_pagina();
     }	
 
 if ($control==1)//esto es para que me muestre el ingreso ya modificado y no el que modifique que luego sera borrardo
    {/*aca paso todo los seguimientos, archivos subidos y demas cosas asociadas al viejo seguimiento de rma
       y lo asocio al nuevo*/
     $sql = "update comentarios_rma set id_deposito=$id_dep, id_producto=$id_prod, id_proveedor=$id_prov, id_info_rma=$id_info_rma_nuevo
             where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
     $db->Execute($sql) or die($db->ErrorMsg()."<br>Error al cambiar los cometarios en el rma $sql");
     $sql = "update log_ubicacion set id_deposito=$id_dep, id_producto=$id_prod, id_proveedor=$id_prov, id_info_rma=$id_info_rma_nuevo
             where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
     $db->Execute($sql) or die($db->ErrorMsg()."<br>Error al cambiar los el log_ubicacion en el rma $sql");
     $sql = "update archivos_subidos set id_deposito=$id_dep, id_producto=$id_prod, id_proveedor=$id_prov, id_info_rma=$id_info_rma_nuevo
             where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
     $db->Execute($sql) or die($db->ErrorMsg()."<br>Error al cambiar los archivos_subidos en el rma $sql");         
     $sql = "update log_cambio_prod set id_deposito=$id_dep, id_producto=$id_prod, id_proveedor=$id_prov, id_info_rma=$id_info_rma_nuevo
             where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
     $db->Execute($sql) or die($db->ErrorMsg()."<br>Error al cambiar log de cambio de producto en el rma $sql");    
     include "funciones.php";
     descontar_stock($cantidad,$id_producto,$id_proveedor,$id_deposito,$id_info_rma,3);//doy de baja al rma viejo
     /*asigno los valores del nuevo rma asi al recargar la pagina me la 
       carga con los cambios*/
     $id_proveedor=$id_prov;
     $id_producto=$id_prod;
     $id_deposito=$id_dep;
     $id_info_rma=$id_info_rma_nuevo;
     $id_control_stock=$id_control_stock_nuevo;
    }
  $db->CompleteTrans();     	
}//del if del guardar	

echo $html_header;
$sql="
   select garantia_vencida,info_rma.cantidad, stock.id_producto,stock.comentario_inventario,stock.ubicacion,id_ubicacion,ubicacion.orden,
          stock.id_deposito,productos.tipo,
          productos.desc_gral,productos.marca,productos.modelo,
          productos.precio_stock,productos.id_producto,
          tipos_prod.descripcion,proveedor.razon_social,proveedor.id_proveedor,proveedor.politica_rma,info_rma.nrocaso,
          info_rma.nro_ordenp,info_rma.nro_ordenc,info_rma.id_nota_credito,info_rma.id_movimiento_material,
          info_rma.deposito,info_rma.user_historial,info_rma.fecha_historial,
          fecha_modif,log_ubicacion.fecha as fecha,nombre_corto          
    from stock
   join general.productos using(id_producto)
   join general.depositos using(id_deposito)
   join general.tipos_prod on (tipos_prod.codigo=productos.tipo)
   join info_rma using (id_deposito,id_producto,id_proveedor)
   join proveedor using (id_proveedor)
   join stock.descuento using (id_info_rma,id_deposito,id_producto,id_proveedor)
   left join stock.ubicacion using (id_ubicacion)
   join stock.control_stock using (id_control_stock)   
   left join stock.log_ubicacion using (id_info_rma,id_deposito,id_producto,id_proveedor,id_ubicacion)      
   where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
   $datos=$db->Execute($sql) or die($db->ErrorMsg()."<br>Error al traer los datos del producto en RMA<BR>$sql");   
   $sql_archivos="select * from archivos_subidos where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
   $consulta_sql_archivos=sql($sql_archivos) or fin_pagina();

if($parametros['pagina_listado']=="historial")
{$mostrar_cb=0;
 ?><br>
 <table width="100%" align="center" id="ma_sf">
  <tr>
   <td>
    <?
     if($datos->fields['id_nota_credito'])
     {?>
      <font size="2" color="Black">Se generó la Nota de Crédito Nº <?=$datos->fields['id_nota_credito']?></font>
     <?
     }
     else
     {if($datos->fields['deposito']!="")
        {$mostrar_cb=1;
        ?>
        <font size="2" color="Black">Se recibió el producto en: <?=$datos->fields['deposito']?></font>
        <?
        }	
      else {if ($datos->fields['garantia_vencida']==1)	
               {
               ?>
               <font size="2" color="Black">Garantía Vencida</font>
               
               <?
               }
           }
     }
     ?>
   </td>
   <td>
    <?
     $fecha_historial=split(" ",$datos->fields['fecha_historial'])
    ?>
    <font size="2" color="Black">Fecha: <?=fecha($fecha_historial[0])." ".$fecha_historial[1]?></font>
   </td>
   <td align="right">
    <font size="2" color="Black"	>Usuario: <?=$datos->fields['user_historial']?></font>
   </td>
  </tr>
 </table>
<?
}
echo $msg;
echo $msg2;
?>
<SCRIPT>
var contador=0;
var wproductos=0;
var wproductos_2=0;
var insertar_ok=0;
var control_log=0;

function cargar()
{insertar_ok=1;
 document.all.descripcion.value=wproductos.document.all.descripcion.value
 document.all.tipo.value=wproductos.document.all.tipo_prod.value 
 document.all.prod_nuevo.value=wproductos.document.all.id_producto.value  
 document.all.precio.value=wproductos.document.all.precio.value;
 if (document.all.precio.value==0)
    {document.all.precio.readOnly=0;    
     eval("document.all.info.style.display='block'");
    }
 else {document.all.precio.readOnly=1;       
       eval("document.all.info.style.display='none'");
      } 	
 eval("document.all.oculta_comentario.style.display='block'");
 wproductos.close();
}	

function es_stock_js(nbre_prov)
{
 if(nbre_prov.substring(0,5)=="Stock")
  return 1;
 else
  return 0;
}


function nuevo_item()
{var pagina_prod;
 var nbre_prov;
 var stock_page;
 if (wproductos==0 || wproductos.closed)
 {nbre_prov=document.all.id_proveedor.value;
    /*si el nombre del proveedor empieza con la palabra 'Stock' entonces
      los productos a seleccionar deben ser solo los que esten en ese stock seleccionado*/
    if(es_stock_js(nbre_prov))
    {    switch(nbre_prov)
         {case "Stock San Luis": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_san_luis.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock Buenos Aires": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_buenos_aires.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock New Tree": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_new_tree.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock ANECTIS": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_anectis.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock SICSA": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_sicsa.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;
          case "Stock Serv. Tec. Bs. As.": pagina_prod="<?=encode_link($html_root.'/modulos/stock/stock_st_ba.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>"
                                     wproductos=window.open(pagina_prod+'&id_proveedor='
	                                 +document.all.select_proveedor[document.all.select_proveedor.selectedIndex].value
	                                 ,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=20,width=800,height=450');
                         break;               
         }
    }
    /*En otro caso funciona como es usual, trayendo todos los productos cargados*/
    else
    {	pagina_prod="<?=encode_link('../general/productos2.php',array('onclickcargar'=>"window.opener.cargar()",'onclicksalir'=>'window.close()','cambiar'=>0,'viene'=>'rma')) ?>"
    	wproductos=window.open(pagina_prod+'&id_proveedor='+document.all.id_prov_nuevo.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');
	    //wproductos=window.open(pagina_prod,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=25,top=10,width=750,height=300');
    }
 }
 else
  if (!wproductos.closed)
   wproductos.focus();
}
/*************************************************/

/*****************************************************************/
function cargar_2()
{//document.all.proveedor.value=wproductos_2.document.all.proveedor.options[wproductos_2.document.all.proveedor.options.selectedIndex].text;
 //document.all.id_prov_nuevo.value=wproductos_2.document.all.proveedor.value;
 document.all.precio.value=wproductos_2.document.all.precio.value;
 document.all.id_prov_nuevo.value=wproductos_2.document.all.id_prov.value;
 document.all.proveedor.value=wproductos_2.document.all.razon_social.value;
 //document.all.precio_nuevo.value=wproductos_2.document.all.precio.value;
 if (document.all.precio.value==0)
    {document.all.precio.readOnly=0;
     //document.all.precio.Class="text_1";
     eval("document.all.info.style.display='block'");
    }
 else {document.all.precio.readOnly=1;
       //document.all.precio.Class="text_4"; 
       eval("document.all.info.style.display='none'");
      } 	
 eval("document.all.oculta_comentario.style.display='block'");     
 wproductos_2.close();
}	
/*****************************************************************/

/*********************************************************************/
function nuevo_item_2()
{pagina_prod="<?=encode_link('stock_cambio_producto.php',array('onclickcargar'=>"window.opener.cargar_2()",'onclicksalir'=>'window.close()','cambiar'=>0,'viene'=>'rma')) ?>"
 wproductos_2=window.open(pagina_prod+'&id_producto='+document.all.prod_nuevo.value,'','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=300,width=550,height=150');
}	
/*********************************************************************/


function habilitar_borrar(valor)
{
 if (valor.checked)
             contador++;
             else
             contador--;
 if (contador>=1){
         window.document.all.borrar.disabled=0;
 		}
        else{
         window.document.all.borrar.disabled=1;
        }
}//fin function
function eliminar() {
	return window.confirm("Esta seguro que quiere eliminar "+contador+" archivos almacenados en el sistema.");
}

function control_datos()
{if (document.all.precio.value==0) 
    {if (!confirm ("El precio ingresado es el Correcto?.\nEsta Seguro?")) return false;     
    }
  if ((document.all.prod_viejo.value!=document.all.prod_nuevo.value || document.all.id_prov_nuevo.value!=document.all.id_prov_viejo.value) && document.all.comentario_cambio.value=="")
    {alert ("Debe llenar el campo Motivos del Cambio de Producto")
     return false;
    }	
 return true;   
}	

function mostrar_logs()
{if (control_log==0)
    {eval("document.all.oculta_log.style.display='block'");
     document.all.muestra_log.value="Ocultar log de Cambios";    
     control_log=1;
    }
 else {eval("document.all.oculta_log.style.display='none'");
       document.all.muestra_log.value="Muestrar log de Cambios";    
       control_log=0;
      }   
}	
//ventana de codigos de barra
var vent_cb=new Object();
vent_cb.closed=true;
</SCRIPT>


<form name='form1' action="<?=$link?>" method="POST">
<? 
 $sql_2="select * from log_cambio_prod where id_info_rma=$id_info_rma and id_proveedor=$id_proveedor and id_producto=$id_producto and id_deposito=$id_deposito";
 $resul_log=sql($sql_2) or fin_pagina($sql_2);
 if ($resul_log->RecordCount()!=0)
    {$linea=0;
    ?>
   	<table align="center"><tr><td align="center"><input name="muestra_log" type="button" size="23" value="Muestrar log de Cambios" onclick="mostrar_logs()"></td></tr></table>
    <div id="oculta_log" style="display:none">
     <table align="center" width="80%" class="bordes">
     <?while (!$resul_log->EOF)
      {if ($linea>=1)
          {
          ?>
           <tr>
           <td colspan="2">
           <hr width="100%" >
           </td>
           </tr>
          <?	 
          }	
      ?>
      <tr>
       <td>
        <font size="3"><b>Usuario:&nbsp;<b></font><b><?=$resul_log->fields['usuario']?></b>   
       </td>
       <td>
        <font size="3"><b>Fecha:&nbsp;<b></font><b><?=$resul_log->fields['fecha']?></b>
       </td>
      </tr> 
      <tr>
       <td colspan="2">
        <font size="3"><b>Comentario:&nbsp;</b></font> <b><?=$resul_log->fields['comentario']?></b>
       </td>       
      </tr>  
      <?
      $linea=1;
     $resul_log->MoveNext();
    }
  ?>      
     </table>
    </div>
    <?
    }
    ?>
<br>
<table width="80%" align="center">
 <tr>
  <td>
   <table width="100%" align="center" cellpadding="3" border="1">
    <tr id=mo>
     <td colspan="2">
      Información del Producto
     </td>
    </tr>
    <tr id=ma_sf>
     <td width="30%">
      <b>Descripción</b>
      <?if($parametros["pagina_listado"]=="real")
      {
      ?>
       &nbsp;&nbsp;&nbsp;<input name="cambiar" type="button" value="Cambiar" title="Cambiar Producto" onclick="nuevo_item()">
      <?
      }
      ?>
     </td>
     <td width="70%" >
      <b><font color="black" size="2"> <input name="descripcion" type="text" readonly style="width=100%" style="font-size: 10pt" class="text_4" value="<?=$datos->fields['desc_gral']?>"></font></b>
      <input name="prod_viejo" type="hidden" value="<?=$datos->fields['id_producto']?>">
      <input name="prod_nuevo" type="hidden" value="<?=$datos->fields['id_producto']?>">      
     </td> 
    </tr> 
    <tr id=ma_sf>
     <td width="30%">
      <b>Cantidad</b>
     </td>
     <td width="70%">
      <b><font color="Black" size="2"> <?=$datos->fields['cantidad']?></font></b>
      <?
      if($mostrar_cb)
      {
       $link_cb=encode_link("../ord_compra/leer_codigos_barra.php",array("total_comprado"=>$cant_cb,"producto_nombre"=>$datos->fields['desc_gral'],"id_producto"=>$datos->fields['id_producto'],"nro_rma"=>$id_info_rma)); 
       ?>
       <input name="cant_cb" type="hidden" value="<?=$cant_cb?>">
       <input type="button" name="cod_barra" value="Códigos de Barra" onclick="if(vent_cb.closed)vent_cb=window.open('<?=$link_cb?>','','top=130, left=250, width=320px, height=350px, scrollbars=1, status=1,directories=0');else vent_cb.focus();">
      <?
      } 
      ?>
      <input name="cantidad_1" type="hidden" value="<?=$datos->fields['cantidad']?>">
     </td> 
    </tr> 
    <tr  id=ma_sf>
     <td width="30%">
      <b>Tipo</b>
     </td>
     <td width="70%">
      <b><font color="Black" size="2"> <input name="tipo" type="text" readonly style="width=100%" style="font-size: 10pt" class="text_4" value="<?=$datos->fields['descripcion']?>"></font></b>
      <input name="tipo_nuevo" type="hidden" value="<?=$datos->fields['descripcion']?>">
      <input name="tipo_viejo" type="hidden" value="<?=$datos->fields['descripcion']?>">      
     </td> 
    </tr> 
    <tr id=ma_sf>
     <td width="30%">
      <b>Proveedor</b>
      <?if($parametros["pagina_listado"]=="real")
      {
      ?>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="cambiar_prov" type="button" value="Cambiar" title="Cambiar Proveedor" onclick="nuevo_item_2()">
      <?
      }
      ?>
     </td>
     <td width="70%">
      <b><font color="Black" size="2">
       <input name="proveedor" readonly type="text" style="width=100%" style="font-size: 10pt" class="text_4" value="<?=$datos->fields['razon_social']?>"></font></b>
       <input name="id_prov_nuevo" type="hidden" value="<?=$datos->fields['id_proveedor']?>">
       <input name="id_prov_viejo" type="hidden" value="<?=$datos->fields['id_proveedor']?>">
	   <input type="button" value="Política de RMA" onClick="if (ventana.style.visibility=='visible') ventana.style.visibility='hidden'; else {
		   ventana.style.visibility='visible';
		   ventana.style.top=((document.body.clientHeight/2)-(300/2))+document.body.scrollTop;}">
	   <div id="ventana" style="background-color: white;position: absolute;overflow: auto;width: 600;height: 300;border: outset 2;visibility: hidden;">
		<table width=100% cellspacing=0 cellpadding=0 border=0>
		<tr bgcolor="#006699">
			<td width=100% align="center">
				<font size=2 color='#cdcdcd'><b>Política de RMA</b></font>
			</td>
			<td>
				<img src="../../imagenes/salir.gif" style="cursor: hand;" onClick="ventana.style.visibility='hidden'">
			</td>
		</tr>
		<tr>
			<td colspan=2>
				<p><?echo html_out($datos->fields["politica_rma"]);?></p>
			</td>
		</tr>
		</table>
		<script>
			ventana.style.left=((document.body.clientWidth/2)-(600/2));
		</script>
	   </div>
     </td> 
    </tr>
    <tr id=ma_sf>
     <td width="30%">
      <b>Precio</b>
     </td>
     <td width="70%">
      <b><font color="Black" size="2">U$S <input name="precio" readonly type="text" style="width=20%" style="font-size: 10pt" value="<?=formato_money($datos->fields['precio_stock'])?>" onkeypress="return filtrar_teclas(event,'0123456789.')"></font></b><div id="info" style="display:none"><font color="Red"><b>No use separador de Miles</b></font></div>      
      <input name="precio_nuevo" type="hidden" value="<?=$datos->fields['precio_stock']?>">
      <input name="precio_viejo" type="hidden" value="<?=$datos->fields['precio_stock']?>">

     </td> 
    </tr>
    <?
    if($datos->fields['nro_ordenc'])
    {?>
    <tr id=ma_sf>
     <td width="30%">
      <b>Nº Orden de Compra</b>
     </td>
     <td width="70%">
      <?
      if($datos->fields['nro_ordenc'])
      {
       //dada la OC, vemos si es una Orden de Servicio Tecnico o no (asociada a Serv tec y con proveedor Stock Serv Tec Bs As)
       $query="select razon_social,nrocaso from orden_de_compra join proveedor using(id_proveedor)
               where nro_orden=".$datos->fields['nro_ordenc'];
       $oc_serv_tec=sql($query,"<br>Error al traer datos de la OC para Servicio Tecnico<br>") or fin_pagina();
      }//de if($datos->fields['nro_ordenc'])
      
      if($oc_serv_tec->fields["nrocaso"]!="" && $oc_serv_tec->fields["razon_social"]=="Stock Serv. Tec. Bs. As.")
       $modo="oc_serv_tec";
      else 
       $modo=""; 
      ?>
       <a target="_blank" href="<?=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$datos->fields['nro_ordenc'],"modo"=>$modo))?>"><b><font color="Blue" size="2"> <?=$datos->fields['nro_ordenc']?></font></b></a>
       <input name="nro_comp" type="hidden" value="<?=$datos->fields['nro_ordenc']?>">
     </td> 
    </tr> 
    <?
    }//de if($datos->fields['nro_ordenc'])
    
    if($datos->fields['nro_ordenp'])
    {
    ?>
    <tr id=ma_sf>
     <td width="30%">
       <b>Nº Orden de Producción</b>
     </td>
     <td width="70%">
      <a target="_blank" href="<?=encode_link("../ordprod/ordenes_nueva.php",array("nro_orden"=>$datos->fields['nro_ordenp'],"modo"=>"modificar"))?>"><b><font color="Blue" size="2"> <?=$datos->fields['nro_ordenp']?></font></b></a><?if ($datos->fields['nro_ordenp']!="") { $sql="select id_licitacion from orden_de_produccion where nro_orden=".$datos->fields['nro_ordenp']; $no=sql($sql) or fin_pagina($sql); if ($no->fields['id_licitacion']!="") {?> &nbsp;&nbsp;&nbsp; <b>Licitación: <?=$no->fields['id_licitacion'] ?></b><?}}?>
      <input name="nro_prod" type="hidden" value="<?=$datos->fields['nro_ordenp']?>">
     </td> 
    </tr>
    <?
    }//de if($datos->fields['nro_ordenp'])
    
    if($datos->fields['nrocaso'])
    {
    ?>
    <tr id=ma_sf>
     <td width="30%">
      <b>Nº C.A.S</b>
     </td>
     <td width="70%">
      <?if ($datos->fields['nrocaso']!="")
           {$sql="select cas_ate.nombre from casos_cdr left join cas_ate using (idate) where nrocaso=".$datos->fields['nrocaso'];
            $no=sql($sql) or fin_pagina($sql);
           }	
      ?>
      <b><font color="Black" size="2"> <?=$datos->fields['nrocaso']; echo "</font></b>"; if ($datos->fields['nrocaso']!="") {?>&nbsp;&nbsp;&nbsp;<b>Atendido por: <font color="Black"><?=$no->fields['nombre'];}?></b></font>
      <input name="nro_caso" type="hidden" value="<?=$datos->fields['nrocaso']?>">
     </td> 
    </tr>
    <?
    }//de if($datos->fields['nrocaso'])
    
    if($datos->fields['id_movimiento_material'])
    {
    ?>
    <tr id=ma_sf>
     <td width="30%">
       <b>Nº Movimiento Material</b>
     </td>
     <td width="70%">
      <a target="_blank" href="<?=encode_link("../mov_material/detalle_movimiento.php",array("id_movimiento_material"=>$datos->fields['id_movimiento_material']))?>"><b><font color="Blue" size="2"> <?=$datos->fields['id_movimiento_material']?></font></b></a>
      <input name="nro_prod" type="hidden" value="<?=$datos->fields['id_movimiento_material']?>">
     </td> 
    </tr>   
    <?
    }//de if($datos->fields['id_movimiento_material'])
    ?>
   </table>
  </td>
 </tr> 
 
<?
if($id_control_stock)
{
 ?> 
 <tr>
 <td>
 <br>
 <table width=100% align=center>
     <tr id="mo">
       <td>Usuario</td>
       <td>Acción</td>
       <td>Fecha</td>
     </tr>
   <?
   $sql="select * from log_stock ";
   $sql.=" where id_control_stock=$id_control_stock";
   $log_stock=$db->execute($sql)  or die($db->errormsg()."<br>".$sql);
   $cant_log=$log_stock->recordcount();

   for($i=0;$i<$cant_log;$i++){
   ?>
   <tr id=ma>
    <td><b><?=$log_stock->fields["usuario"]?>     </b></td>
    <td><b><?=$log_stock->fields["tipo"]?>        </b></td>
    <td><b><?=fecha($log_stock->fields["fecha"])?></b></td>
   </tr>
   <?
   $log_stock->movenext();
   }
   ?>
   </table>
  </td>
 </tr> 
<?
}//de if($id_control_stock)
//style="display:none"
?> 
</table> 
<div id="oculta_comentario" style="display:none"> 
<br>
<table  align="center" width="80%" class="bordes">
 <tr><td><b>Motivos del Cambio de Producto:</b>&nbsp;<b>(<font color="Red">Campo Obligatorio</font>)</b></td></tr>
 <tr><td><textarea name="comentario_cambio" rows="3" cols="95" ></textarea></b></td></tr>
</table>
<br>
</div>
<input type=hidden name="id_control_stock" value="<?=$id_control_stock?>">


<br>
<?
//traemos los comentarios del producto en rma
$query="select * from comentarios_rma where id_producto=$id_producto 
        and id_deposito=$id_deposito and id_proveedor=$id_proveedor and id_info_rma=$id_info_rma";
$comentarios=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer los comentarios del RMA");

$link=encode_link("stock_descontar_rma.php",array("id_deposito"=>$id_deposito,"id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_info_rma"=>$id_info_rma,"id_control_stock"=>$id_control_stock,"pagina_listado"=>$parametros['pagina_listado']));


?>

<!--/////////////////CODIGO BROGGI////////////////////-->
<?
 $consulta="select usuario,fecha,lugar from log_ubicacion 
            join ubicacion using(id_ubicacion) 
            where  id_info_rma=$id_info_rma and id_deposito=$id_deposito 
            and id_producto=$id_producto and id_proveedor=$id_proveedor order by fecha";
 $resultado_consulta=sql($consulta) or fin_pagina(); 
/*
?>
<table width="80%" align="center" border="1">
 <tr id=mo>
  <td align="center" colspan="3"><b>Log. de Ubicación de Producto</b></td>
 </tr>
 <tr id=ma>
  <td align="center"><b>Usuario</b></td>
  <td align="center"><b>Fecha</b></td>
  <td align="center"><b>Paso a</b></td>
 </tr>
 <?
  while(!$resultado_consulta->EOF)
       {
       ?>
        <tr bgcolor="<?=$bgcolor_out?>">
         <td align="center"><b><?=$resultado_consulta->fields['usuario']?></b></td>
         <td align="center"><b><?=$resultado_consulta->fields['fecha']?></b></td>
         <td align="center"><b><?=$resultado_consulta->fields['lugar']?></b></td>                  
        <tr>
       <?
        $resultado_consulta->MoveNext();  
       }
       	
 ?>
</table>
<br>
*/?>
<table align="center" border="1" bgcolor="<?=$bgcolor_out?>">
 <? 
 
  //$que=trim($datos->fields['nombre_corto']);
      $fecha_actual=date("d/m/Y");           
      $fecha_base_1=fecha($datos->fields["fecha_modif"]);  
       
      $que=trim($datos->fields['nombre_corto']);//aca le saco los espacios que tiene adelante y atras     
     if ($datos->fields['fecha_historial']=="") $dias_3=0;
     else {$fecha_base_3=fecha($datos->fields["fecha_historial"]);           
            $dias_3=diferencia_dias_habiles($fecha_base_3,$fecha_actual);
           } 
      //$dias_1 son los dias transcurridos desde que se creo el seguimiento hasta la fecha
      //$dias_2 son los dias transcurridos desde que la parte llego al proveedor hasta la fecha
      //$dias_3 son los dias transcurridos desde que se dio de baja hasta la fecha
         
      if ($que!="P") $dias_2=0;
     else {$fecha_base_2=fecha($datos->fields["fecha"]);            
           $dias_2=diferencia_dias_habiles($fecha_base_2,$fecha_actual);
           } 
      $color_1="";
      $color_2="";
     $dias_1=diferencia_dias_habiles($fecha_base_1,$fecha_actual);
      
      if (($dias_1-$dias_2-$dias_3)>3) $color_1="yellow";
      if (($dias_1-$dias_2-$dias_3)>8) $color_1="red";
      if (($dias_2-$dias_3)>10) $color_2="yellow";
      if (($dias_2-$dias_3)>20) $color_2="red";
 ?> 
     <tr>
      <td ><b>Días de Creación:&nbsp;<?=$dias_1-$dias_2-$dias_3?></b></td> 
      
      <td ><b>Días de Envio:&nbsp;<?=$dias_2-$dias_3?></b></td>     
     </tr>  
</table>
<table align="center" border="1">
<input name="id_proveedor" value="<?=$id_proveedor?>" type="hidden">
<input name="id_producto" value="<?=$id_producto?>" type="hidden">
<input name="id_deposito" value="<?=$id_deposito?>" type="hidden">
<input name="id_info_rma" value="<?=$id_info_rma?>" type="hidden">
<input name="id_control_stock" value="<?=$id_control_stock?>" type="hidden">
<?if($parametros['pagina_listado']!="historial")
 {
?> 	
 <tr>
  <td>
   <b>Ubicación de la Parte:&nbsp;</b><select name="ubicacion_parte" >   
   <?
    $sql_ubicacion="select * from stock.ubicacion order by id_ubicacion";//esto es para saver cual es el codigo de en transito
    $resultado_sql_ubicacion=sql($sql_ubicacion) or fin_pagina();
    while (!$resultado_sql_ubicacion->EOF)
          {if ($resultado_sql_ubicacion->fields['orden']<=$datos->fields['orden'])
              {if ($resultado_sql_ubicacion->fields['id_ubicacion']==$datos->fields['id_ubicacion']){ $selected="selected";}
               ?>
               <option  <?=$selected?> value="<?=$resultado_sql_ubicacion->fields['id_ubicacion']?>"><?=$resultado_sql_ubicacion->fields['lugar']?></option>                     
               <?
               $selected="";    	               
              }
           elseif ($datos->fields['orden']=="")  {?>
                   <option  <?=$selected?> value="<?=$resultado_sql_ubicacion->fields['id_ubicacion']?>"><?=$resultado_sql_ubicacion->fields['lugar']?></option>                     
                  <?
           } 
                
           $resultado_sql_ubicacion->MoveNext();   
          }//del while	     
   ?>   
   </select>
  </td>
 </tr>
 <?
 }
 ?>
</table>
<br>
<!--//////////////////////////////////////////////////-->
<table width="80%" align="center" border="1" >
 <tr id=mo bgcolor="<?=$bgcolor_out?>">
  <td>
   Comentarios
  </td>
 </tr>
 <tr>
  <td>
   <table align="center" width="100%">
    <?
    //generamos los comentarios ya cargados
    while(!$comentarios->EOF)
    {?>
     <tr>
      <td>
       <table width="100%">
        <tr  id="ma_sf" bgcolor="<?=$bgcolor_out?>">
          <td width="65%" align="right">
          <b>
          <?
           $fecha=split(" ",$comentarios->fields['fecha']);
           echo fecha($fecha[0])." ".$fecha[1]; 
          ?>
          </b>
          </td>
         </tr> 
         <tr id="ma_sf">
          <td align="right">
           <?=$comentarios->fields['usuario']?>
          </td>
        </tr>
       </table>
      </td> 
      <td>
       <textarea rows="4" cols="90" readonly name="coment_<?=$comentarios->fields['id_comentario_rma']?>"><?=$comentarios->fields['texto']?></textarea>
      </td>
     </tr>
     <?
     $comentarios->MoveNext();
    }
    //y luego damos la opcion a guardar uno mas
    ?>
    <tr>
     <td colspan="2" bgcolor=<?=$bgcolor_out?>> 
      
      <table>
       <tr>
        <td width="25%"  id="ma_sf">
         <b>Nuevo Comentario</b>
        </td>
        <td width="75%">
         &nbsp;<textarea rows="4" cols="70" name="nuevo_coment"></textarea>
        </td>
       </tr>
      </table>
      
     </td>
    </tr>   
   </table>
  </td>
 </tr> 
</table>
<?$datos->MoveFirst();?>

&nbsp;

<table width="80%" align="center" border="1"> 
	<tr>
  		<td id="mo">Ubicación</td>
	</tr>
	<tr>
  		<td bgcolor=<?=$bgcolor_out?> align=center>
  		<textarea name="ubicacion" rows=1 style="width:80%"><?=$datos->fields["ubicacion"]?></textarea>
  		</td>
	</tr>
	<tr bgcolor=<?=$bgcolor_out?>>
   		<td align=center>
   		<input type=submit name=guardar_ubicacion value="Guardar Ubicación">
   		</td>
	</tr>	
</table>
&nbsp;
&nbsp;
<br>

<?if ($consulta_sql_archivos->RecordCount()!=0)
{
?>
<!--/////////////////////////////////////////////////////////////////-->
 <table width="95%" align="center" border="1">
  <tr id=mo>
   <td align="center" colspan="5"><font size="2"><b>Archivos Subidos</b></font></td>   
 <tr id=ma >
    <td align="left" colspan="5">
     <b>Documentos:</b> <?=$consulta_sql_archivos->RecordCount()?>.
    </td>
	
  </tr>
<tr id=mo>
 <td width='10%'><b><INPUT type="submit" name="borrar" value="Borrar" title="Eliminar Seleccioneados" disabled onclick="return eliminar();"></b></td>
 <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Nº.</a></b></td>
 <td width='40%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Nombre.</a></b></td>
 <td width='20%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Fecha y Hora.</A></b></td>
 <td width='20%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Responsable.</A></b></td>
</tr>

<?  $i=0;
	while(!$consulta_sql_archivos->EOF){ 
	$link = encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_atchivos_subidos"],"download"=>1,"comp"=>0));
	?>

<TR id="ma" title="<?=$consulta_sql_archivos->fields["comentario"]?>">
	<TD>
    <input type="checkbox" name="select_<? echo $i; ?>" value="<? echo $consulta_sql_archivos->fields['id_archivos_subidos']; ?>" <?=$permiso_borrar?> onclick="habilitar_borrar(this);" title="Seleccione para eliminar">
	</TD>
	<TD>
	<?=$consulta_sql_archivos->fields["id_archivos_subidos"]?>
	</TD>
	<TD>
	<a title='<?=$consulta_sql_archivos->fields["nombre_archivo"]?> [<?=number_format($consulta_sql_archivos->fields["filesize_comp"]/1024)?> Kb]' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivos_subidos"],"download"=>1,"comp"=>1))?>'>
	<img align=middle src=<?=$html_root?>/imagenes/zip.gif border=0></A>
	<a title = 'Abrir archivo' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$consulta_sql_archivos->fields["id_archivos_subidos"],"download"=>1,"comp"=>0))?>'>
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
<!--/////////////////////////////////////////////////////////////////-->   

<table align="center">   
  <tr><td><input name="subir_archivos" type="button" value="Subir Archivos" onclick='window.open("<?=encode_link("subir_archivo_rma.php",array("id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_deposito"=>$id_deposito,"id_info_rma"=>$id_info_rma));?>","","resizable=1,scrollbars=yes,width=700,height=300,left=20,top=50,status=yes");'></td></tr>      
 </table>     
<br>
<table width="80%" align="center">
 <tr>
  <td align="right">
   <input type="submit" name="guardar" value="Guardar" onclick="return control_datos()">
   <?
   /*/////////////////////ESTO ESTABA EN EL BOTON GUARDAR/////////////////////
   onclick="if(document.all.nuevo_coment.value=='')
              {alert('No se puede guardar un comentario vacio');
               return false;
              } 
             "
   /////////////////////////////////////////////////////////////////////////*/
   ?>
   <input type="button" name="volver" value="Volver" onclick="document.location='listado_rma.php'">
  </td>
  <td align="right">
   <?
    if($parametros["pagina_listado"]=="real")
    { 
     $link=encode_link("rma_historial.php",array("id_producto"=>$id_producto,"id_proveedor"=>$id_proveedor,"id_deposito"=>$id_deposito,"id_info_rma"=>$id_info_rma));
   ?>
     <input type="button" name="historial" value="Dar de Baja" onclick="document.location='<?=$link?>'">
   <?
    }
    else  
     echo "&nbsp;&nbsp;";
   ?>
  </td>
 </tr>
</table> 
<?
if(($_ses_user["login"]=="juanmanuel" ||$_ses_user["login"]=="marcos" ||$_ses_user["login"]=="ferni")&& $parametros["pagina_listado"]=="real")
{?>
 <input type="submit" name="borrar_rma" value="Eliminar de RMA" onclick="if(confirm('¿Está seguro que desea eliminar este producto del stock?'))return true; else return false;">
 <?
}
?>
</form>
<?
fin_pagina();

?>