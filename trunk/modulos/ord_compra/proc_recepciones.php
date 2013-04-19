<?
/*
Autor: MAC
Fecha: 08/12/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.11 $
$Date: 2006/07/18 15:27:20 $
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

   $items=get_items_fin($nro_orden);
   $estado=$items['estado'];
   unset($items['estado']);
   unset($items['cantidad']);
   $msg="<b> NO SE PUDO ACTUALIZAR LA ORDEN";

   guardar_recepciones_oc($nro_orden,$items);

   $factura=PostvartoArray('id_factura_'); //crea un arreglo con los facturas ingresadas
   $tam=sizeof($factura);

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


    $db->CompleteTrans();

    $msg="<b> La Orden de Compra Nº $nro_orden se actualizó exitosamente";

	mail_recibe_productos($nro_orden,$items);
}//de if ($_POST["guardar_datos"]=="Guardar Datos")


if($_POST["confirmar_recepcion"]=="Confirmar Recepción")
{
	$db->StartTrans();

	//traemos todas las filas de la OC para recorrer todas las recepciones de las filas
	//de manera que cualquier boton de confirmar recepciones confirma las de cualquier fila
	$query="select id_fila from compras.fila where nro_orden=$nro_orden and es_agregado=0";
	$filas_id=sql($query,"<br>Error al traer los id de fila para la OC<br>") or fin_pagina();

	$nada_confirmado=1;

	while (!$filas_id->EOF)
	{
		//la fila para la cual se confirman las recepciones seleccionadas
		$id_fila=$filas_id->fields["id_fila"];
	    $id_deposito=$_POST["deposito_".$id_fila];
	    $id_recibido=$_POST["id_recibido_".$id_fila];
	    $nombre_prod_fila=$_POST["nombre_prod_".$id_fila];

		//traemos el log de las recepciones del producto de la fila
	   $query="select id_log_recibido,log_rec_ent.id_prod_esp,usuario,fecha,cant,desde_stock,recepcion_confirmada
	         from compras.log_rec_ent join compras.recibido_entregado using (id_recibido) join compras.fila using(id_fila)
	         where id_fila=$id_fila";
	   $log_recibidos=sql($query,"<br>Error al traer el registro de las recepciones de productos<br>") or fin_pagina();

	   while (!$log_recibidos->EOF)
	   {
	   		$cant=$log_recibidos->fields["cant"];
	   		$id_log_recibido=$log_recibidos->fields["id_log_recibido"];
	   		$id_prod_recibido=$log_recibidos->fields["id_prod_esp"];

	   		//si el checkbox de este log de recepcion esta chequeado, entonces agregamos la reserva correpondiente
	        if($_POST["confirm_log_".$log_recibidos->fields["id_log_recibido"]]==1)
	        {
	         insertar_recibidos($nro_orden,$id_fila,$cant,$id_deposito,$id_prod_recibido,$id_recibido,"",2);

	         $nada_confirmado=0;
	         //ponemos como confirmado el log de recepcion
	         $query="update log_rec_ent set recepcion_confirmada=1 where id_log_recibido=$id_log_recibido";
	         sql($query,"<br>Error al actualizar el log confirmado<br>") or fin_pagina();

	        }//de if($_POST["confirm_log_".$log_recibidos->fields["id_log_recibido"]]==1)

	    	$log_recibidos->MoveNext();
	   }//de while(!$log_recibidos->EOF)

 	$filas_id->MoveNext();
   }//de while(!$filas_id->EOF)
   $db->CompleteTrans();

   if($nada_confirmado)
    $msg="<b>NO se seleccionó ninguna recepción";
   else
    $msg="<b>Las recepciones seleccionadas fueron confirmadas exitosamente";

}//de if($_POST["confirmar_recepcion"]=="Confirmar Recepción")


if($_POST["rechazar_recepcion"]=="Rechazar Recepción")
{
	include_once("../stock/funciones.php");

	$db->StartTrans();
	//traemos todas las filas de la OC para recorrer todas las recepciones de las filas
	//de manera que cualquier boton de confirmar recepciones confirma las de cualquier fila
	$query="select id_fila from compras.fila where nro_orden=$nro_orden and es_agregado=0";
	$filas_id=sql($query,"<br>Error al traer los id de fila para la OC<br>") or fin_pagina();

	$nada_rechazado=1;

	//traemos el tipo de movimiento que indica el rechazo de recepcion: Rechazo de Recepción de productos para una OC
	$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Rechazo de Recepción de productos para una OC'";
	$id_tm_rech=sql($query,"<br>Error al traer el tipo de movimiento para rechazo de recepciones<br>") or fin_pagina();
	if($id_tm_rech->fields["id_tipo_movimiento"])
		$id_tipo_movimiento=$id_tm_rech->fields["id_tipo_movimiento"];
	else
		die("Error Interno RROC163: No se pudo determinar el tipo de movimiento a realizar");

	while (!$filas_id->EOF)
	{
		//la fila para la cual se rechazan las recepciones seleccionadas
		$id_fila=$filas_id->fields["id_fila"];
	    $id_deposito=$_POST["deposito_".$id_fila];
	    $id_recibido=$_POST["id_recibido_".$id_fila];
	    $nombre_prod_fila=$_POST["nombre_prod_".$id_fila];

		//traemos el log de las recepciones del producto de la fila
	   $query="select id_log_recibido,log_rec_ent.id_prod_esp,usuario,fecha,cant,desde_stock,recepcion_confirmada
	         from compras.log_rec_ent join compras.recibido_entregado using (id_recibido) join compras.fila using(id_fila)
	         where id_fila=$id_fila";
	   $log_recibidos=sql($query,"<br>Error al traer el registro de las recepciones de productos<br>") or fin_pagina();

	   while (!$log_recibidos->EOF)
	   {
	   		$cant=$log_recibidos->fields["cant"];
	   		$id_log_recibido=$log_recibidos->fields["id_log_recibido"];
	   		$id_prod_recibido=$log_recibidos->fields["id_prod_esp"];

	   		//si el checkbox de este log de recepcion esta chequeado, entonces agregamos la reserva correpondiente
	        if($_POST["confirm_log_".$log_recibidos->fields["id_log_recibido"]]==1)
	        {

	         //esto no debería pasar jamas. Pero si pasa hay algo que no esta bien y no se puede continuar ejecutando
	   	 	 if($log_recibidos->fields["recepcion_confirmada"]==1)
	   	 	  die("Error Interno OCREC180: Se está intentando rechazar una recepción ya confirmada para la fila con id $id_fila. Consulte a la División Software");

	         //eliminamos el log seleccionado y reducimos la cantidad recibida, en la tabla recibido_entregado
	         $query="delete from compras.log_rec_ent where id_log_recibido=$id_log_recibido";
	         sql($query,"<br>Error al eliminar la recepción elegida con id log: $id_log_recibido<br>") or fin_pagina();

	         $query="update compras.recibido_entregado set cantidad=cantidad-$cant where id_recibido=$id_recibido";
	         sql($query,"<br>Error al actualizar la cantidad recibida de la fila<br>") or fin_pagina();

	         $comentario="Se rechazó la recepción de los productos para la OC Nº 12620";
	         //descontamos los productos que se subieron al stock a confirmar, porque se rechazo la recepcion
	         descontar_a_confirmar($id_prod_recibido,$cant,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila);

	         $nada_rechazado=0;

	        }//de if($_POST["confirm_log_".$log_recibidos->fields["id_log_recibido"]]==1)

	    	$log_recibidos->MoveNext();
	   }//de while(!$log_recibidos->EOF)

 	$filas_id->MoveNext();
   }//de while(!$filas_id->EOF)
   $db->CompleteTrans();

   if($nada_rechazado)
    $msg="<b>NO se seleccionó ninguna recepción";
   else
    $msg="<b>Las recepciones seleccionadas fueron rechazadas exitosamente";
}//de if($_POST["confirmar_recepcion"]=="Confirmar Recepción")


if($_POST["desrecibir_desentregar_fila"]=="Des-Recibir")
{
 include_once("fns_especial.php");
 $db->StartTrans();
 $msg="";
 $id_fila=$_POST["fila_desentregar"];


 //llamamos a la funcion que va a desentregar la fila
 if($_POST["flag_stock"]==1 || $_POST["internacional"]==1)//si la OC es de tipo stock, des-recibimos y descontamos de stock disponible
  des_recibir_fila($id_fila,1,1);
 else //sino, lo hacemos de stock reservado
 des_recibir_fila($id_fila);

 $db->CompleteTrans();
}//de if($desrecibir_desentregar_fila=="Des-recibir")


if($_POST["no_recibir_filas"]=="No Recibir Filas")
{
	$db->StartTrans();
	//traemos las filas de la OC cuyo campo es_agregado no esta seteado en 1
	$query="select fila.id_fila,fila.descripcion_prod,fila.desc_adic,fila.cantidad,tipos_prod.codigo,tipos_prod.descripcion
	        from compras.fila join general.productos using(id_producto) join general.tipos_prod using(id_tipo_prod)
	        where nro_orden=$nro_orden and (es_agregado isnull or es_agregado<>1)";
	$filas_oc=sql($query,"<br>Error al traer las filas de la OC<br>") or fin_pagina();

	//ponemos en un arreglo, los tipos de productos que se pueden poner con es_agregado=1
	$tipos_prod_permitidos=array("garantia","conexos","packaging","armado");
    //en este arreglo guardamos la info de las filas seleccionadas que tienen tipos no permitidos para poner en es_agregado=1
	$filas_tipos_no_permitidos=array();$ind=0;
	$filas_no_recibir="";

	while (!$filas_oc->EOF)
	{
	    $id_fila=$filas_oc->fields["id_fila"];
	    //si el check esta en 1, ponemos el campo es_agregado=1
	    if($_POST["no_recibir_$id_fila"]==1)
	    {
	    	if($filas_no_recibir!="")
	    	 $filas_no_recibir.=",";
	    	$filas_no_recibir.="$id_fila";

	    	//controlamos el tipo del producto de la fila seleccionada
	    	//si es un tipo de procuto que debería ser recibido, lo agregamos al arreglo para luego enviar el mail de alerta
	    	if(!in_array($filas_oc->fields["codigo"],$tipos_prod_permitidos))
	    	{
	    		$filas_tipos_no_permitidos[$ind]=array();
	    	    $filas_tipos_no_permitidos[$ind]["id_fila"]=$id_fila;
	    	    $filas_tipos_no_permitidos[$ind]["producto"]=$filas_oc->fields["descripcion_prod"]." ".$filas_oc->fields["desc_adic"];
	    	    $filas_tipos_no_permitidos[$ind]["tipo"]=$filas_oc->fields["descripcion"];
	    	    $filas_tipos_no_permitidos[$ind]["cantidad"]=$filas_oc->fields["cantidad"];
	    	    $ind++;

	    	}//de if(!in_array($filas_oc->fields["codigo"],$tipos_prod_permitidos))

	    }//de if($_POST["no_recibir_$id_fila"]==1)

	 	$filas_oc->MoveNext();
	}//de while(!$filas_oc->EOF)

	if($filas_no_recibir!="")
	{$query="update compras.fila set es_agregado=1 where id_fila in($filas_no_recibir)";
	 sql($query,"<br>Error al actualizar las filas que no se van a recibir para la OC<br>") or fin_pagina();
	}

	$db->CompleteTrans();

	if($filas_no_recibir!="")
	{
		$msg="<b>Las filas seleccionadas no serán tomadas en cuenta en la recepción de productos para esta Orden de Compra";

		//si hubo alguna fila seleccionado cuyo tipo no esta permitido para ponerlo como es_agregado=1, enviamos un mail de alerta
		$tam_f=sizeof($filas_tipos_no_permitidos);
		if($tam_f>0)
		{
			$para="juanmanuel@coradir.com.ar";
			$asunto="Para la OC Nº $nro_orden se seleccionaron filas posiblemente no debidas, para que no sean tomadas en cuenta en la recepción de productos";
			$texto="Para la Orden de Compra Nº $nro_orden, las siguientes filas se se seleccionaron para que no sean tomadas en cuenta en la recepción de productos:\n";
			$texto.="-------------------------------------------------------------------------------------------\n";
			$texto.="Id Fila - Producto - Tipo de Producto - Cantidad\n";
			$texto.="-------------------------------------------------------------------------------------------\n";

			for($i=0;$i<$tam_f;$i++)
			{
    			 $texto.=$filas_tipos_no_permitidos[$i]["id_fila"]." - ".$filas_tipos_no_permitidos[$i]["producto"]." - ".$filas_tipos_no_permitidos[$i]["tipo"]." - ".$filas_tipos_no_permitidos[$i]["cantidad"]."\n";
			}//de for($i=0;$i<$tam_f;$i++)

			$texto.="-------------------------------------------------------------------------------------------\n";
			$texto.="\nPosiblemente no sea correcto seleccionar estas filas para que no sean tomadas en cuenta en la recepción de los productos de la OC Nº $nro_orden.\n";
			$texto.="Usuario que seleccionó estas filas: ".$_ses_user["name"]." - Fecha: ".date("d/m/Y H:i:s")."\n\n";

			//echo $texto;
			enviar_mail($para,$asunto,$texto,'','','','','');
		}//de if($tam_f>0)
	}//de if($filas_no_recibir!="")
	else
	 $msg="<b>No seleccionó ninguna fila";

}//de if($_POST["no_recibir_filas"]=="No Recibir Filas")

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
       }//de while ($aumento<$cant_archivo)

 $db->CompleteTrans();
}//de if ($_POST['borrar_archivo']=="Borrar")
