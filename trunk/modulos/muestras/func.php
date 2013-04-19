<?

/*AUTOR: MAC
  FECHA: 10/06/04 

$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2004/06/23 22:57:09 $
*/

//recupera los items ya sea de la BD si se pasa el id, o del post, si no se pasa
//es similar a la de Ordenes de Compra (fns.php) pero para muestras
function get_items_muestra($id_muestra=false)
{
 global $db;
 $i=0;
 $total_muestra=0;
 //BUSCA LOS ID DE LOS ITEMS EN LA VARIABLE @_POST
 reset($_POST);
 if(!($id_muestra ==false))
 {
	 $query="select * from productos_muestra where id_muestra=$id_muestra";
        $datos=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer productos de la muestra");

	 $items; $i=0;
	 while (!$datos->EOF)
	 {
	   	$items[$i]['id_producto_muestra']=$datos->fields['id_producto_muestra'];
	   	$items[$i]['id_producto']=$datos->fields['id_producto'];
	   	$items[$i]['cantidad']=$datos->fields['cantidad'];
 	  	$items[$i]['descripcion']=$datos->fields['descripcion'];
 	  	$items[$i]['id_deposito']=$datos->fields['id_deposito'];
 	  	$items[$i]['id_proveedor']=$datos->fields['id_proveedor'];
 	  	$items[$i]['precio']=$datos->fields['precio'];
 	  	$total_muestra+=$datos->fields['precio']*$datos->fields['cantidad'];
	   	$i++;
	   	$datos->MoveNext();
	 }
  	 $items['cantidad']=$i;
  	 
 }
 else 
 {	
	 $i=0;
	 while ($clave_valor=each($_POST))
	 {
		   if (is_int(strpos($clave_valor[0],"idp_")))
		   {  
				 $posfijo=substr($clave_valor[0],4);
				 $items[$i]['id_producto_muestra']=$_POST['idf_'.$posfijo];
				 $items[$i]['id_producto']=$_POST['idp_'.$posfijo];
				 $items[$i]['cantidad']=$_POST['cant_'.$posfijo];
				 $items[$i]['descripcion']=$_POST['desc_'.$posfijo];
				 $items[$i]['id_deposito']=$_POST['deposito_'.$posfijo];
				 $items[$i]['id_proveedor']=$_POST['proveedor_'.$posfijo];
				 $items[$i]['precio']=$_POST['precio_unitario_'.$posfijo];
 	  	         $total_muestra+=$_POST['precio_unitario_'.$posfijo]*$_POST['cant_'.$posfijo];
		       $i++;
		   }
	 }
	 $items['cantidad']=$i;

 }
 $items['total_muestra']=$total_muestra;
 return $items;
}

/***************************************************************
FUNCION que inserta los productos de la muestra seleccionada.

Para eso, borra las actuales e inserta las que estan en la tabla.
Asi evita controles de insersion o actualizacion.
****************************************************************/
function insertar_partes($id)
{global $db;
 $db->StartTrans();
 
 //primero borramos todas las partes de ese id
 $query="delete from productos_muestra where id_muestra=$id";
 $db->Execute($query) or die($db->ErrorMsg()."<br>Error al borrar los productos de la muestra");
 
 //luego insertamos los items que estran en la tabla
 $items=get_items_muestra();
 for($i=0;$i<$items['cantidad'];$i++)
 {
  if($items[$i]['id_producto']!="")
  {
  	$query="insert into productos_muestra(id_muestra,id_producto,descripcion,cantidad,id_deposito,id_proveedor,precio)
          values($id,".$items[$i]['id_producto'].",'".$items[$i]['descripcion']."',".$items[$i]['cantidad'].",".$items[$i]['id_deposito'].",".$items[$i]['id_proveedor'].",".$items[$i]['precio'].") 
          ";
   $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar el producto $i de la muestra <br>$query");
  }//de  if($items[$i]['id_producto'])
 }	
 $db->CompleteTrans(); 	
}//de function insertar_partes($id)


/************************************************
Funcion que descuenta del stock correspondiente
a los productos de la muestra
*************************************************/
function descontar_stock()
{global $db,$msg,$_ses_user,$id_muestra;
//descontamos del stock seleccionado los items de la orden de compra
     $items=get_items_muestra();
     $db->StartTrans();
     for($r=0;$r<$items['cantidad'];$r++)
     {	
      $cantidad=$items[$r]['cantidad'];
      $obs="Modificación automática desde el módulo Muestras";
      $id_prod=$items[$r]['id_producto'];
      $id_prov=$items[$r]['id_proveedor'];
      $id_dep=$items[$r]['id_deposito'];
      if($items[$r]['id_producto']!="")
      {//traemos la cantidad actual para ese producto, en ese proveedor
       //en ese deposito
       $query="select cant_disp from stock where id_deposito=$id_dep and id_proveedor=$id_prov and id_producto=$id_prod";
       $result_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el stock actual del producto<br>$query");
       //si hay cantidad disponible, lo descontamos      
       if($cantidad<=$result_stock->fields['cant_disp'])		
       {$sql="update stock set ";
	    $sql.="cant_disp=cant_disp-$cantidad,";
	    $sql.=" comentario='$obs' ";
	    $sql.=" where ";
	    $sql.="id_producto=$id_prod ";
	    $sql.=" AND id_deposito=$id_dep ";
	    $sql.=" AND id_proveedor=$id_prov";
	    $db->execute($sql) or die($sql);
	   
	    //registramos en el historial el descuento de stock
        $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
        $id_control_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock"); 

        $fecha_modif=date("Y-m-d H:i:s",mktime());
        $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
               values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','Muestra Nº $id_muestra','Descuento generado por la Muestra Nº $id_muestra','a')";
	    $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");
       
	    $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
	           values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cantidad)";
	    $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento");
	   
	    $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	           values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Descuento por Muestra Nº $id_muestra')";
	    $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");
	    

       }
       else
       {//sino damos el error y detenemos el for
        $msg="<center><b>La Muestra no se puede pasar a 'En Curso': <br>Las cantidades a descontar del stock son mayores que las actualmente disponibles</b></center>";
        $r=$items['cantidad']+1;
       }	 
      }//de  if($items[$i]['id_producto']!="")
     }//de for($r=0;$r<$items['cantidad'];$r++)		
  $db->CompleteTrans();
}


/************************************************
Funcion que incrementa el stock correspondiente
a los productos de la muestra
*************************************************/
function incrementar_stock()
{global $db,$msg,$_ses_user,$id_muestra;
//descontamos del stock seleccionado los items de la orden de compra
     $items=get_items_muestra();
     $db->StartTrans();
     
     //traemos todos los depositos
     $depositos=$db->Execute("select * from depositos") or die($db->ErrorMsg()."<br>Error al traer los depositos (incrementar_stock)");
     $obs="Modificación automática desde el módulo Muestras";
     for($r=0;$r<$items['cantidad'];$r++)
     {
      $id_prod=$items[$r]['id_producto'];
      $id_prov=$items[$r]['id_proveedor'];
      
      $depositos->Move(0);
      while(!$depositos->EOF)
      {
       $id_dep=$depositos->fields['id_deposito'];
       $cantidad=$_POST['cant_inc_'.$depositos->fields['id_deposito'].'_'.$r];
       if($cantidad!="" && $cantidad>0)
       {
       	//revisamos si esta la entrada para ese producto, proveedor, deposito, en el stock.
       	$query="select count(*)as cuenta from stock where id_producto=$id_prod and id_deposito=$id_dep and id_proveedor=$id_prov";
       	$esta=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al seleccionar el stock (incrementar_stock)");

       	if($esta->fields['cuenta']==0)
       	{$fecha_modif=date("Y-m-d H:i:s",mktime());
       	 $sql="insert into stock(id_producto,id_deposito,id_proveedor,cant_disp,comentario,last_user,last_modif)
       	       values($id_prod,$id_dep,$id_prov,$cantidad,'$obs','".$_ses_user['login']."','$fecha_modif')";	

       	}
       	else 
       	{	
       	 $sql="update stock set ";
	     $sql.="cant_disp=cant_disp+$cantidad,";
	     $sql.=" comentario='$obs' ";
	     $sql.=" where ";
	     $sql.="id_producto=$id_prod ";
	     $sql.=" AND id_deposito=$id_dep ";
	     $sql.=" AND id_proveedor=$id_prov";

       	}
       	 
	     $db->execute($sql) or die($db->ErrorMsg()."<br>Error al insertar en stock<br>$sql");
	    
	     //registramos en el historial el incremento de stock
         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
         $id_control_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock");  

         $fecha_modif=date("Y-m-d H:i:s",mktime());
         $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
               values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','Muestra Nº $id_muestra','Incremento generado por la Muestra Nº $id_muestra','is')";
         $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");
       
	     $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
	           values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cantidad)";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento");
	   
	     $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	           values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Incremento por Muestra Nº $id_muestra')";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");
	     
       }//de if($cantidad!="" && $cantidad>0)
	   $depositos->MoveNext();
      }//de while(!$depositos->EOF)
     }//de for($r=0;$r<$items['cantidad'];$r++)		
  $db->CompleteTrans();
}

?>