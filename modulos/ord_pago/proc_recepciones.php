<?
/*
Autor: MAC
Fecha: 08/12/05

MODIFICADA POR
$Author: mari $
$Revision: 1.2 $
$Date: 2006/05/03 16:05:20 $
*/
require_once("../../config.php");
require_once("fns.php");

$nro_orden=$_POST["nro_orden"];
$es_stock=$_POST["es_stock"];
$cant_factura=$_POST["cant_factura"];
if ($_POST["guardar_datos"]=="Guardar Datos")
{
  /**********************************************************************************************************************
	ATENCION, A PARTIR DEL GESTION3:
    -LAS RECEPCIONES PARA OC DE TIPO LICITACION, PRESUPUESTO O CASO SIGUEN SUBIENDO A LOS PRODUCTOS A STOCK RESERVADO,
    	Y ESA RESERVA QUEDA ASOCIADA AL CASO, LICITACION O PRESUPUESTO.
    -LAS OC INTERNACIONALES o ASOCIADAS A STOCK AGREGAN A STOCK DISPONIBLE.
    -LAS OC DE TIPO OTRO Y DE HONORARIO DE SERVICIO TECNICO NO RECIBEN MAS PRODUCTOS
    -LAS OC ASOCIADAS A RMA DE PRODUCCION, DESAPARECEN.

    ESTOS CAMBIOS SE DEBEN A LA CREACION DEL NUEVO MODULO: PEDIDO DE MATERIALES
  ***********************************************************************************************************************/
  $db->StartTrans();

  //con el parametro en 1 indicamos que estamos recibiendo productos
  //(el proveedor NO es un stock)
  //con el parametro en 0 indicamos que estamos entregando productos
  //(el proveedor SI es un stock)
   $items=get_items_fin($nro_orden);
   $estado=$items['estado'];
   unset($items['estado']);
   unset($items['cantidad']);
   $msg="<b> NO SE PUDO ACTUALIZAR LA ORDEN";



	$factura=PostvartoArray('id_factura_'); //crea un arreglo con los facturas ingresadas
	$tam=sizeof($factura);

	/***************************************************************
	Mercaderia en Tránsito
	****************************************************************/
	  //$items_merc_trans=get_items_fin();
	  //descontamos de mercaderia en transito los productos que se reciben
	  //solo si no esta asociada con honorario de s tecnico o  es asociada
	  //con otro
	 /* $query_as="select flag_honorario,id_licitacion,orden_prod,flag_stock,nrocaso from orden_de_compra where nro_orden=$nro_orden";
	  $asociada=$db->Execute($query_as) or die($db->ErrorMsg."<br>Error al traer asociaciones de OC");
	  if($asociada->fields['flag_honorario']!=1 && ($asociada->fields['id_licitacion']!="" || $asociada->fields['orden_prod']!="" || $asociada->fields['flag_stock']==1 || $asociada->fields['nrocaso']!=""))
	  {
	  	descontar_merc_trans($nro_orden,$items_merc_trans,$id_proveedor);
	  }*/

	/***************************************************************
	Mercaderia en Tránsito
	****************************************************************/


  //insertamos el registro de los productos recibidos, si el proveedor no es stock
  if(!$es_stock)
  {
   insertar_recibidos($nro_orden,$items,$id_proveedor);
  }
  else
  {
  	/*GESTION2
     $filas_sin_cb=array();
     $ind=0;
     for($t=0;$t<$items["cantidad"];$t++)
     { $ejecutar=0;
	   if($items[$t]['id_recibido']=="")
	   {//insert
	      //ejecutamos la consulta solo si hay algo en el campo de observaciones
	      if($items[$t]['observaciones']!="")
	      {$ejecutar=1;
	       $query1="select nextval('recibidos_id_recibido_seq') as id_recibido";
	       $id_rec=sql($query1,"<br>Error al traer la secuencia del recibido") or fin_pagina();
	       $id_recibido=$id_rec->fields["id_recibido"];
	       $query1="insert into recibidos(id_recibido,id_fila,observaciones,ent_rec)
	       values($id_recibido,".$items[$t]['id_fila'].",'".$items[$t]['observaciones']."',0)";
	      }
	   }
	   else
	   {//update
	      $query1="update recibidos set observaciones='".$items[$t]['observaciones']."' where id_recibido=".$items[$t]['id_recibido'];
	      if($items[$t]['observaciones']!="")
	       $ejecutar=1;
	   }
	     $error_cb="<BR>-----------------------------------------<BR>\n
	                       Error al insertar/actualizar los comentario entregados.\n
	                       <BR>-----------------------------------------<BR><BR>\n
	                       ";
	     if($ejecutar)
	      sql($query1,$error_cb) or fin_pagina();

	     //si el check de entregar sin cb está chequeado, ponemos en 1 el campo correspondiente
	     //en la fila.  Sino lo ponemos en 0.
	     $id_f=$items[$t]['id_fila'];

	     if($_POST["entregar_sin_cb_".$id_f]==1)
	     {
	       if ( $_POST['hidden_sin_cb_'.$id_f] !=1 && !in_array($id_f,$filas_sin_cb))
	             $filas_sin_cb[$ind++]=$id_f;
	      $sin_cb=1;
	     }
	     else
	      $sin_cb=0;
	     $query2="update fila set entregar_sin_cb=$sin_cb where id_fila=$id_f";
	     sql($query2,"<br>Error al actualizar la fila $id_f<br>") or fin_pagina();

	 }//de for($t=0;$t<$items["cantidad"];$t++)

	 if (count($filas_sin_cb)>0)
	        mail_entregar_sin_cb($filas_sin_cb,$internacional);
	 */
  }//del else de if(!$es_stock)

 $flag=0;  //control para ver si seleccionaron todas las facturas
		  //armo arreglo con wury id factura, nro orden
 $query[]="delete from factura_asociadas where nro_orden=$nro_orden";
 if ($factura)
 {
   for ($i=0;$i<$tam;$i++)
   {
		  if ($factura[$i]!='')
		  {
		     $id_fact=$factura[$i];
		     $query[]="insert into factura_asociadas (id_factura, nro_orden) values ($id_fact,$nro_orden)";
		  }
	      else
			 $flag=1;
   }//de for ($i=0;$i<$tam;$i++)
   if (!$_POST['orden_ant'])
		  sql($query,"<br>Error al trabajar con las facturas de proveedores<br>") or fin_pagina();
 }//de if ($factura)
 else
   $flag=0;

 //$_POST['orden_ant']=! significa que la orden es vieja (tiene guardado el numero de factura y la fecha y la factura no esta cargada en factura_proveedores)
 //si $flag =1 significa que faltan cargar facturas entonces no se puede terminar
 $query="update orden_de_compra set  notas_internas='$notas_internas'";
 if (!$_POST['orden_ant'])
   $query.=" , cant_factura=$cant_factura";

 $tipo_log="de recepcion";
 $query.=" where nro_orden=$nro_orden";

 //actualizo el estado de la orden
 sql($query,"<br>Error al actualizar las facturas de la OC<br>") or fin_pagina();

 $fecha=date("Y-m-d H:i:s",mktime());
 $query="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha)
         values ($nro_orden,'$tipo_log','".$_ses_user["login"]."','$fecha')";
 sql($query,"<br>Error al insertar el log de OC para la recepción de productos<br>") or fin_pagina();

	//ACTUALIZO EL STOCK !!!!!!!!!!!!!
	//actualizo (incrementa)el stock si el estado es
	//totalmente pagada

	/*GESTION2
	//Si la OC esta asociada a Stock los productos recibidos se ingresan al stock seleccionado
	{if($es_stock)
	  die("La OC no puede tener un stock proveedor porque esta asociada a stock. Consulte con la Division Software");

		if (($estado_orden=='g')||($estado_orden=='d')||($estado_orden=='e'))
		 {
		 $sql="select prov_prod,id_proveedor,id_producto,id_deposito,r.cantidad,precio_unitario,f.id_fila
		       from ";
		 $sql.="orden_de_compra o join fila f using(nro_orden) join ";
		 $sql.="recibidos r using(id_fila)
		        where o.nro_orden=$nro_orden ";
		 $datos=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
		 $cantidad_filas=$datos->recordcount();

			for ($i=0;$i<$cantidad_filas;$i++)
			{
			  $cantidad= $_POST['cantidadr_'.$datos->fields['id_fila']."_".$datos->fields['id_deposito']];
			  if ($cantidad>0)
			  {
			    $precio  = $datos->fields['precio_unitario'];
				$query="select id_producto,a.id_fila from
	                    (
	                     select max(fecha_cambio) as fecha,id_fila
	                     from compras.cambios_producto
	                     where id_fila=".$datos->fields['id_fila']." group by id_fila
	                    )as a
	                    join compras.cambios_producto as f on a.fecha=f.fecha_cambio and a.id_fila=f.id_fila
	                   ";
				$cambio_pr=sql($query,"<br>Error al traer los cambios de productos para la fila<br>") or fin_pagina();

			    if($cambio_pr->fields["id_producto"])
				 $id_prod =$cambio_pr->fields["id_producto"];
				else
			     $id_prod = $datos->fields['id_producto'];
				if($datos->fields['prov_prod'])
				 $id_prov = $datos->fields['prov_prod'];
				else
				 $id_prov = $datos->fields['id_proveedor'];
				$id_dep  = $datos->fields['id_deposito'];


				if($internacional)
				 $obs="Modificación automática Ordenes de Compra";
				else
				 $obs="Modificación automática Ordenes de Compra Internacional";


				//primero busco si tiene precio ese producto
				//con ese proveedor
				$sql=" select id_producto from precios ";
				$sql.="where id_producto=$id_prod ";
				$sql.="and id_proveedor=$id_prov ";
				$result=$db->execute($sql) or die($db->ErrorMsg()."<br> $sql");
				$cant_precios=$result->recordcount();

				if ($cant_precios>0)
				 {
				 //ya tiene precio y lo modifico
	                        //si esta asociada a un stock
	                         if ($mostrar_dolar){
	                                           insertar_precio($id_prod,$id_prov,$precio);
	                                           /*
				        	   $sql="update precios set precio=$precio";
					           $sql.=",observaciones='$obs'";
						   $sql.=",usuario='$_ses_user_login' ";
						   $sql.="where ";
						   $sql.="id_producto=$id_prod ";
						   $sql.=" AND id_proveedor=$id_prov";
						   $db->execute($sql) or die($sql);
	                                           */
	/*GESTION2
	                               }
	   		     //me fijo si hay stock

	                     $sql="select id_producto from stock";
			     $sql.=" where id_producto=$id_prod ";
			     $sql.=" AND id_proveedor=$id_prov";
			     $sql.=" AND id_deposito=$id_dep";
			     $result_stock=$db->execute($sql) or die($sql);
			     $cant_stock=$result_stock->recordcount();
			    //verifico si ya hay un stock existente
			    //si lo hay lo modifico si no lo inserto
	 		    if ($cant_stock>0)
	 		    {
	                   	                   $sql="update stock set ";
			        		   $sql.="cant_disp=cant_disp+$cantidad,";
						   $sql.=" comentario='$obs' ";
						   $sql.=" where ";
				        	   $sql.="id_producto=$id_prod ";
						   $sql.=" AND id_deposito=$id_dep ";
						   $sql.=" AND id_proveedor=$id_prov";
						   $db->execute($sql) or die($db->ErrorMsg().$sql);

						   }
						   else
						   {
						   $campos="id_producto,id_proveedor,id_deposito,";
						   $campos.="comentario,last_user,cant_disp";
						   $sql="insert into stock ($campos) values ";
						   $sql.="($id_prod,$id_prov,$id_dep,'$obs','$_ses_user_login',$cantidad)";
						   $db->Execute($sql) or die($db->ErrorMsg()."<br>Error al insertar stock $sql");//inserta en stock
	                                           }
									//fin de la verificacion del stock
			 }//de  if ($cant_stock>0)
			else
			{
						   //no hay ninguna relacion e inserto el
						   //producto con el proveedor el precio y el stock

	                                           if (!$mostrar_dolar){
	                                                $precio=$precio/3;
	                                                }
	                                           /*
						   $campos="id_producto,id_proveedor,precio,";
						   $campos.="observaciones,usuario";

	        				   $sql="insert into precios ($campos)";
						   $sql.=" values ";
						   $sql.="($id_prod,$id_prov,$precio,'$obs','$_ses_user_login')";
						   $db->execute($sql) or die($sql);
	                                            */
	   		     /*GESTION2
	                                           insertar_precio($id_prod,$id_prov,$precio);

						   $campos="id_producto,id_proveedor,id_deposito,";
						   $campos.="comentario,last_user,cant_disp";
						   $sql="insert into stock ($campos) values ";
						   $sql.="($id_prod,$id_prov,$id_dep,'$obs','$_ses_user_login',$cantidad)";
						   $db->Execute($sql) or die($db->ErrorMsg()."<br>Error al insertar stock $sql");//inserta en stock
			}//del else de  if ($cant_stock>0)




	         //INSERTO LOS LOG DEL LA MODIFICACION DEL
	         //STOCK MEDIANTE  LA ORDEN DE COMPRA
	         $fecha=date("Y-m-d H:i:s");
	         $usuario=$_ses_user["name"];
	         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
	         $resultado=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock");
	         $id_control_stock=$resultado->fields["id_control_stock"];

	         if($internacional)
	          $texto_int="Internacional";
	         else
	          $texto_int="";

	         $query="insert into control_stock
	                (id_control_stock,fecha_modif,usuario,comentario,estado)
	                 values($id_control_stock,'$fecha','OC Nº $nro_orden','Ingreso generado por la Orden de Compra $texto_int Nº $nro_orden','oc')";
	         $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");

	         $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
	                 values($id_dep,$id_prod,$id_prov,$id_control_stock,$cantidad)";
	         $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento");

	         $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	                 values ($id_control_stock,'$usuario','$fecha','Ingreso de Stock')";
	         $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");
			}//del if de $cantidad > 0
	         $datos->MoveNext();
		  }//for
		}//del if de totalmente pagada
	}*///de if($flag_stock)
	//FIN DE LA ACTUALIZACION DEL STOCK!!!!!!

    $db->CompleteTrans();

    $msg="<b> Su Orden Nº $nro_orden se actualizó exitosamente";

	mail_recibe_productos($nro_orden,$items);
}//de if ($_POST["guardar_datos"]=="Guardar Datos")


if($_POST["desrecibir_desentregar_fila"]=="Des-Recibir")
{
 include_once("../ord_compra/fns_especial.php");
 $db->StartTrans();
 $msg="";
 $id_fila=$_POST["fila_desentregar"];
 //llamamos a la funcion que va a desentregar la fila
 des_recibir_fila($id_fila);

 $db->CompleteTrans();
}//de if($desrecibir_desentregar_fila=="Des-recibir")



if ($parametros["download"])
{
	$sql = "select * from archivos_subidos_compra where id_archivo_subido = ".$parametros["FileID"];
	$result = sql($sql,"") or die($db->ErrorMsg()."<br>$sql");


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
}//de if ($parametros["download"])

if ($_POST['borrar_archivo']=="Borrar")
{
 $db->StartTrans();
 $aumento=0;
 $msg=" ";
 $cant_archivo=$_POST['cant_archivos'];
 while ($aumento<$cant_archivo)
       {$check=$_POST['eliminar_'.$aumento];
       	if ($check)
       	   {//die($check);
       	    $archivo_comp=$_POST['nom_comp_'.$aumento];

       	    if (unlink(UPLOADS_DIR."/ord_compra/archivos_subidos/$archivo_comp"))
       	       {$sql="delete from compras.archivos_subidos_compra where id_archivo_subido=".$_POST['id_archivo_'.$aumento];
       	        $borrado=sql($sql) or fin_pagina();
       	        $msg.="El Archivo \"$archivo_comp\" se borró correctamente";
       	       }
       	    else $msg.="El Archivo \"$archivo_comp\" no se pudo Borrar";

       	   }
       	$aumento++;
       }

 $db->CompleteTrans();
}//de if ($_POST['borrar_archivo']=="Borrar")


/*
NO SE HACEN MAS ENTREGAS DESDE ORDEN DE COMPRA. ESTO SERA REEMPLAZADO POR EL MODULO PEDIDO DE MATERIAL
if($boton=="Entregar")
{$db->StartTrans();
   $items=get_items_fin(2);

   $filas_sin_cb=array();
   $ind=0;
   for($t=0;$t<$items["cantidad"];$t++)
   {$ejecutar=0;
    if($items[$t]['id_recibido']=="")
     {//insert
      //ejecutamos la consulta solo si hay algo en el campo de observaciones
      if($items[$t]['observaciones']!="")
      {$ejecutar=1;
       $query1="select nextval('recibidos_id_recibido_seq') as id_recibido";
       $id_rec=sql($query1,"<br>Error al traer la secuencia del recibido") or fin_pagina();
       $id_recibido=$id_rec->fields["id_recibido"];
       $query1="insert into recibidos(id_recibido,id_fila,observaciones,ent_rec)
       values($id_recibido,".$items[$t]['id_fila'].",'".$items[$t]['observaciones']."',0)";
      }
     }
     else
     {//update
      $query1="update recibidos set observaciones='".$items[$t]['observaciones']."' where id_recibido=".$items[$t]['id_recibido'];
      if($items[$t]['observaciones']!="")
       $ejecutar=1;
     }
     $error_cb="<BR>-----------------------------------------<BR>\n
                       Error al insertar/actualizar los comentario entregados.\n
                       <BR>-----------------------------------------<BR><BR>\n
                       ";
     if($ejecutar)
      sql($query1,$error_cb) or fin_pagina();

     //si el check de entregar sin cb está chequeado, ponemos en 1 el campo correspondiente
     //en la fila.  Sino lo ponemos en 0.
     $id_f=$items[$t]['id_fila'];
     if($_POST["entregar_sin_cb_".$id_f]==1) {
          if ( $_POST['hidden_sin_cb_'.$id_f] !=1 &&  !in_array($id_f,$filas_sin_cb))
             $filas_sin_cb[$ind++]=$id_f;
      $sin_cb=1;
     }
     else
      $sin_cb=0;
     $query2="update fila set entregar_sin_cb=$sin_cb where id_fila=$id_f";
     sql($query2,"<br>Error al actualizar la fila $id_f<br>") or fin_pagina();


   }//de for($t=0;$t<$items["cantidad"];$t++)

   $destino_para_autorizar="ord_compra_listar.php";

 if($db->CompleteTrans()) {
  $msg="<b> Su Orden Nº $nro_orden se actualizó exitosamente";
  if (count($filas_sin_cb)>0)
       mail_entregar_sin_cb($filas_sin_cb,$internacional);

 }
}//de elseif($boton="Entregar")
*/