<?
/*
Autor: GACZ - MAC

MODIFICADA POR
$Author: mari $
$Revision: 1.265 $
$Date: 2007/01/05 18:52:18 $
*/

//NOTA SE REQUIERE QUE ANTES SE INCLUYA LA LIBRERIA CON FUNCIONES ADODB

//Obtengo los datos que vienen pos post
function obtener_casos(){
$i=0;
         while ($clave_valor=each($_POST))
         {
                   if (is_int(strpos($clave_valor[0],"idp_")))
                   {
                                 $posfijo=substr($clave_valor[0],4);
                                 $items[$i]=$_POST['nro_caso_'.$posfijo];
                       $i++;
                   }
         }
         $items['cantidad']=$i;

 return $items;
}//de la funcion obtener datos


/*****************************************************************************
 * get_ids_fila by GACZ
 * @return void
 * @param array filasDB contiene la pseudotabla de filas despues de haber insertado
 * @param array productos_presup contiene los id_producto_presupuesto de cada fila
 * @desc Recupera los id_fila despues de haberlos insertado y los reasigna
 				 a sus correspondientes id_producto_presupuesto
 				 NOTA: los dos parametros deberian tener la misma cantidad de filas
 ****************************************************************************/
function get_ids_fila(&$productos_presup,$filasDB)
{
	$i=0;
	$cant_filas=count($filasDB);
	for ( ; $i < $cant_filas; $i++)
		$productos_presup[$i]['id_fila']=$filasDB[$productos_presup[$i]['pos_fila']]['id_fila'];
}//de function get_ids_fila(&$productos_presup,$filasDB)


/*****************************************************************************
 * insertar_oc_pp Fernando > modificada por GACZ
 * @return void
 * @param nro_orden orden de compra a modificar
 * @param producto es un array que contiene los id de los productos
 * @desc inserta en oc_pp la relacion orden de compra con los productos presupuesos que se compram
 ****************************************************************************/
function insertar_oc_pp($productos)
{
 global $nro_orden;
 for ($i=0; $i < sizeof($productos);$i++)
 {
     $id_productos=split(",",$productos[$i]['id_producto_presupuesto']);
     $cantidades=split(",",$productos[$i]['cantidades_pres']);
     $id_fila=$productos[$i]['id_fila'];
     $j=0;//contador de ids_prod_pres,cantidades

      foreach ($id_productos as $id_prod )
      {
          $sql="insert into oc_pp (nro_orden,id_fila,id_producto_presupuesto,cantidad_oc)
                values ($nro_orden,$id_fila,$id_prod,".$cantidades[$j++].");";
          $sql_array[]=$sql;
      }
 }

  if(sizeof($sql_array)>0)
    sql($sql_array,"<br>Error al insertar en los productos del presupuesto de licitacion para la OC (insertar_oc_pp)<br>") or fin_pagina();

} //de function insertar_oc_pp($productos)

/*****************************************************************************
 * relacionar_presupuesto_oc Fernando
 * @return void
 * @param nro_orden orden de compra a modificar
 * @param id_renglon_prop renglon con el cual voy a obtener el presupuesto a relacionar con la orden de compra
 * @desc Relaciona las orden de compra con licataciones y el seguimiento de producion
 ****************************************************************************/
function  relacionar_presupuesto_oc($nro_orden,$id_renglon_prop)
{

 $array_renglon_prep=split(",",$id_renglon_prop);
 $id_renglon=$array_renglon_prep[0];

 $sql="select id_subir from renglon_presupuesto_new
       join licitacion_presupuesto_new using(id_licitacion_prop)
       join entrega_estimada using(id_entrega_estimada)
       join subido_lic_oc using(id_entrega_estimada)
       where renglon_presupuesto_new.id_renglon_prop=$id_renglon";
 $res=sql($sql,"<br>Error al traer los datos del presupuesto de licitación<br>") or fin_pagina();
 $id_subir=$res->fields["id_subir"];
 $sql="update orden_de_compra set id_subir=$id_subir
       where nro_orden=$nro_orden";
 sql($sql,"<br>Error al actualizar la relacion entre la OC y el presupuesto de licitación<br>") or fin_pagina();
}//de function  relacionar_presupuesto_oc($nro_orden,$id_renglon_prop)


function insertar_fila_en_caso($nro_orden,$casos)
{
	global $db;

	$sql="select fila.* from orden_de_compra join fila using (nro_orden)";
	$sql.=" where nro_orden=$nro_orden order by id_fila";
	$resultado=sql($sql,"<br>Error al traer la info de las filas de OC (insertar_fila_en_caso)<br>") or fin_pagina();
    for ($i=0;$i<$resultado->recordcount();$i++)
	{
	     $id_fila=$resultado->fields["id_fila"];
	     $idcaso=$casos[$i];
	     $sql=" update casos_cdr set fila=$id_fila where idcaso=$idcaso";
	     sql($sql,"<br>Error al actualizar el caso de cdr para la OC (insertar_fila_en_caso)<br>") or fin_pagina();
	     $resultado->movenext();
	}


}//de function insertar_fila_en_caso($nro_orden,$casos)

function buscar_caso($ordenes)
{
	global $db;
	$indice=0;
	$casos=array();
	   for ($i=0;$i<sizeof($ordenes);$i++)
	      {
	       $sql="Select idcaso from casos_cdr where nro_orden=".$ordenes[$i];
	       $resultado=sql($sql) or fin_pagina();;
	       for ($y=0;$y<$resultado->recordcount();$y++)
	        {
	        $casos[$indice]=$resultado->fields["idcaso"];
	        $resultado->MoveNext();
	        $indice++;
	        }
	      }

	return $casos;
}//de function buscar_caso($ordenes)

//funcion que le paso un arreglo con las nro de ordenes
//y la funcion me paga los casos correspondientes
function pagar_casos($ordenes)
{
 global $db;
 for ($i=0;$i<sizeof($ordenes);$i++)
 {
      $nro_orden=$ordenes[$i];
      $sql="select fila.* from orden_de_compra join fila using (nro_orden)";
      $sql.=" where nro_orden=$nro_orden order by id_fila";
      $filas=sql($sql) or fin_pagina();;
      $cant_filas=$filas->recordcount();
      for ($y=0;$y<$cant_filas;$y++)
      {
           $id_fila=$filas->fields["id_fila"];
           $sql="select idcaso,fila from casos_cdr where fila=$id_fila";
           $resultado=sql($sql) or fin_pagina();;
           if ($resultado->recordcount()>0)
                                           {
                                            $idcaso=$resultado->fields["idcaso"];
                                            $sql="update casos_cdr set pagado_orden=1";
                                            $sql.=" where idcaso=$idcaso";
                                            sql($sql) or fin_pagina();;

                                           }//del if
      $filas->movenext();
      }//del for


 }//del for


}//de function pagar_casos($ordenes)

//elimina los campos demas en el arreglo filas
//pone comillas necesarias y agrega el nro_orden
//recupera los id_producto_presupuesto en la variable global $productos_pres
function prepare_orden($nro_orden,&$filas)
{
 global $productos_pres;
 while ($filas['cantidad']--)
 {
	$filas[$filas['cantidad']]['nro_orden']=$nro_orden;
 	$filas[$filas['cantidad']]['descripcion_prod']="'".$filas[$filas['cantidad']]['descripcion_prod']."'";
 	$filas[$filas['cantidad']]['desc_adic']="'".$filas[$filas['cantidad']]['desc_adic']."'";

 	$productos_pres[$filas['cantidad']]['pos_fila']=$filas['cantidad'];//posicion dentro de la pseudotabla
 	$productos_pres[$filas['cantidad']]['id_producto_presupuesto']=$filas[$filas['cantidad']]['id_producto_presupuesto'];
 	$productos_pres[$filas['cantidad']]['cantidades_pres']=$filas[$filas['cantidad']]['cantidades_pres'];

 	unset($filas[$filas['cantidad']]['id_producto_presupuesto']);
 	unset($filas[$filas['cantidad']]['cantidades_pres']);
 	unset($filas[$filas['cantidad']]['subtotal']);
 }
 unset($filas['cantidad']);
}//de function prepare_orden($nro_orden,&$filas)

/**************************************************************
 Funcion que recupera informacion de las filas de la OC,
 ya sea desde la BD (si se pasa el parametro $nro_orden)
 o desde el POST, si no se pasa dicho parametro.
***************************************************************/
function get_items($nro_orden="")
{
 global $db;
 $i=0;
 //BUSCA LOS ID DE LOS ITEMS EN LA VARIABLE @_POST
 reset($_POST);
 if($nro_orden!="")
 {
	 $query="SELECT * FROM orden_de_compra as o JOIN fila as f on ".
		   	 "o.nro_orden=f.nro_orden WHERE o.nro_orden=$nro_orden";
	 $datos=sql($query,"<br>Error al consultar los datos de la OC<br>") or fin_pagina();
	 $items; $i=0;
	 while (!$datos->EOF)
	 {
	   	$items[$i]['id_fila']=$datos->fields['id_fila'];

        if($datos->fields['id_producto']!="")
	   	 $items[$i]['id_producto']=$datos->fields['id_producto'];
	   	else
	   	 $items[$i]['id_prod_esp']=$datos->fields['id_prod_esp'];

	   	$items[$i]['cantidad']=$datos->fields['cantidad'];
	   	$items[$i]['precio_unitario']=$datos->fields['precio_unitario'];
	   	$items[$i]['descripcion_prod']=$datos->fields['descripcion_prod'];
	   	$items[$i]['desc_adic']=$datos->fields['desc_adic'];
	   	/*******************************************************************
	   	 Datos para las OC Internacionales
	   	********************************************************************/
	   	$items[$i]['id_posad']=$datos->fields['id_posad'];
	   	$items[$i]['proporcional_flete']=$datos->fields['proporcional_flete'];
	   	$items[$i]['base_imponible_cif']=$datos->fields['base_imponible_cif'];
	   	$items[$i]['derechos']=$datos->fields['derechos'];
	   	$items[$i]['iva']=$datos->fields['iva'];
	   	$items[$i]['ib']=$datos->fields['ib'];
	   	/*******************************************************************
	   	 Fin de datos para las OC Internacionales
	   	********************************************************************/

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
	 	if(!$_POST["es_stock"])//si el proveedor NO es un stock, se utilizan productos generales, por lo tanto idp_
	 	  $prefijo="idp_";
	 	elseif($_POST["es_stock"])//en cambio si el proveedor SI es un stock, se utilizan productos especificos, es decir: id_p_esp_
	 	  $prefijo="id_p_esp_";
	 	else
	 	{ //hablar con MAC si salta este error
	 	 die("Error al intentar obtener los items de la OC. NO se puede determinar el tipo de proveedor. Contacte a la División Software");
	 	}

		   if (is_int(strpos($clave_valor[0],$prefijo)))
		   {
				 $posfijo=substr($clave_valor[0],strlen($prefijo));

				 $items[$i]['id_fila']=$_POST['idf_'.$posfijo];

                 if($_POST['idp_'.$posfijo])
				  $items[$i]['id_producto']=$_POST['idp_'.$posfijo];
				 else
				  $items[$i]['id_prod_esp']=$_POST['id_p_esp_'.$posfijo];

				 $items[$i]['cantidad']=$_POST['cant_'.$posfijo];
				 $items[$i]['precio_unitario']=$_POST['unitario_'.$posfijo];
				 $items[$i]['descripcion_prod']=$_POST['desc_orig_'.$posfijo];
				 $items[$i]['subtotal']=$_POST['subtotal_'.$posfijo];

				 $items[$i]['desc_adic']=$_POST['h_desc_'.$posfijo];
         		 /*******************************************************************
 			   	  Datos para las OC Internacionales
 			   	 ********************************************************************/
			   	 $items[$i]['id_posad']=$_POST['select_posad_'.$posfijo];
			   	 $items[$i]['proporcional_flete']=$_POST['proporcional_flete_'.$posfijo];
			   	 $items[$i]['base_imponible_cif']=$_POST['base_imponible_cif_'.$posfijo];
			   	 $items[$i]['derechos']=$_POST['derechos_'.$posfijo];
			   	 $items[$i]['iva']=$_POST['iva_'.$posfijo];
			   	 $items[$i]['ib']=$_POST['ib_'.$posfijo];
			   	 /*******************************************************************
			   	  Fin de datos para las OC Internacionales
			   	 ********************************************************************/

         		 $items[$i]['id_producto_presupuesto']=$_POST['id_prod_pres_'.$posfijo];
                 $items[$i]['cantidades_pres']=$_POST['hcantidades_pres_'.$posfijo];

		       $i++;
		   }//de if (is_int(strpos($clave_valor[0],$prefijo)))
	 }//de while ($clave_valor=each($_POST))
	 $items['cantidad']=$i;
 }

 return $items;
}//de function get_items($nro_orden=false)

/*****************************************************************
 Funcion que elimina las filas de la orden
  @items contiene todos los items que van a quedar
  @nro_orden es el numero de orden por la cual buscar
******************************************************************/
function del_items($items,$nro_orden)
{
	global $db,$es_stock,$era_stock;
	//obtengo las filas que estan guardadas en la BD, antes de borrarlas
	$items2=get_items($nro_orden);
	//en este string se almacenan los id´s de filas que ya no estan en la OC
	//asi se cuales tengo que borrar
	$borrar="";

	for($i=0;$i < $items2['cantidad'];$i++)
	{
		for($j=0;$j < $items['cantidad'];$j++)
		{//recorro el arreglo de productos que se tienen que insertar
			if ($items[$j]['id_fila']==$items2[$i]['id_fila'])
			 break;
			//si coinciden, corto el for, porque encontre la fila
		}
		//si $j==$items['cantidad'] significa que una fila
		//que estaba en la BD, no esta mas en la OC, por lo que
		//hay que borrarla. Entonces la incluimos en el string
		//de las filas a borrar
		if ($j==$items['cantidad'])
		{
		  $borrar.=$items2[$i]['id_fila'].",";
		}

	}//de for($i=0;$i < $items2['cantidad'];$i++)
	$borrar=substr($borrar,0,strlen($borrar)-1);

	if ($borrar!="")
	{//si hay filas a borrar, y el proveedor es un stock, o era un stock
	 //pero se cambio a uno que no es stock, debemos
	 //eliminar las reservas en el stock, para ese producto, de esa OC
	 //si es que existe dicha reserva
	 if($es_stock || $era_stock)
	 {
      //traemos el id del tipo de movimiento referido a la cancelación de productos
	  $query="select id_tipo_movimiento from tipo_movimiento where nombre='Se canceló la reserva hecha previamente para este producto'";
      $id_tipo_mov=sql($query,"<br>Error al traer el id del tipo de movimiento para cancelar reservas (del_items)<br>") or fin_pagina();
	  $id_tipo_movimiento=$id_tipo_mov->fields["id_tipo_movimiento"];
	  if($id_tipo_movimiento=="")
	   die("<br>Error: no se pudo determinar el tipo de movimiento de stock (del_items). Contacte a la division software<br>");

	  //seleccionamos los datos de las reservas hechas por cada fila a borrar de la OC
	  $query="select id_en_stock,id_detalle_reserva,
	          detalle_reserva.cantidad_reservada,id_deposito,id_prod_esp
	          from stock.detalle_reserva join stock.en_stock using(id_en_stock) where id_fila in ($borrar)";
	  $reservas=sql($query,"<br>Error al traer las reservas de la fila Nº $id_fila que se desea borrar (del_items)<br>") or fin_pagina();

	  $a_borrar=split(",",$borrar);
	  $tam=sizeof($a_borrar);
	  //por cada fila a borrar, cancelamos las reservas hechas
      for($f=0;$f<$tam;$f++)
      {
      	$id_fila=$a_borrar[$f];

       	   $id_prod_esp=$reservas->fields["id_prod_esp"];
      	   $cantidad=$reservas->fields["cantidad_reservada"];
      	   $id_deposito=$reservas->fields["id_deposito"];
      	   $comentario="Cancelación de reservas realizada por la Orden de Compra Nº $nro_orden";

      	   //se eliminar la reserva, solo si esta fila tenia una reserva antes, y ahora se eliminara
      	   if($reservas->fields["id_detalle_reserva"]!="")
            cancelar_reserva($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila="");

      	$reservas->MoveNext();
	  }//de for($f=0;$f<$tam;$f++)
     }//de if($es_stock)

	 $q= "delete from fila where nro_orden=$nro_orden AND id_fila in ($borrar)";
	 sql($q,"<br>Error al eliminar las filas de la OC (del_items)<br>") or die($db->ErrorMsg()."<br>$q");

	}//de if ($borrar!="")
}//de del_items($items,$nro_orden)


/*************************************************************************************************
 Funcion que busca los datos de la fila y arma una arreglo con los mismos.
**************************************************************************************************/
function get_items_fin($nro_orden)
{
	$i=0;
		reset($_POST);
		//traemos todos los id de filas guardadas para la OC
		$query="select id_fila from fila where nro_orden=$nro_orden and es_agregado<>1";
		$filas=sql($query,"<br>Error al traer los id de filas (get_items_fin)<br>") or fin_pagina();
		while (!$filas->EOF)
		{
			   	 $id_fila=$filas->fields["id_fila"];
					 $cant_comprada=$_POST['comprado_'.$id_fila];
					 $cant_recibida=$_POST['cantidadr_'.$id_fila];


		 				 $items[$i]['id_recibido']=$_POST['id_recibido_'.$id_fila];
						 $items[$i]['id_fila']=$id_fila;
						 $items[$i]['id_deposito']=$_POST['deposito_'.$id_fila];
						 $items[$i]['cantidad']=$_POST['cant_rec_'.$id_fila];
						 $items[$i]['id_prod_recibido']=$_POST['id_prod_rec_'.$id_fila];
						 $items[$i]['observaciones']=$_POST['observaciones_'.$id_fila];
						 $i++;

					  if ($cant_comprada > $cant_recibida)
					 	$items['estado']='d';//por ahora reclamadas

            $filas->MoveNext();
		 }//de while (!$filas->EOF)
	 $items['cantidad']=$i;
	return $items;
}//de function get_items_fin($recibir=1)

/*
 * ***************************************************************************
 * devuelve el resultado de la diferencia entre los precios
 * si la diferencia es mayor que el porcentaje pasado devueve esa diferencia
 * si es menor devuelve 0
 * @param unknown_type $precio1
 * @param unknown_type $precio2
 * @param unknown_type $porcentaje
 */
function diferencia($precio_viejo,$precio_nuevo,$porcentaje){
	//saco el porcentaje del precio viejo
	$por = ($porcentaje*$precio_viejo/100);
	//tomo la diferencia absoluta
	$dif = abs($precio_nuevo - $precio_viejo);
	
	($dif > $por)? $return = $dif : $return = 0;
	
	return $return;
}
/**
 * envia el mail correspondiente informando la diferencia de precio 
 *
 * @param unknown_type $id_prod_recibido
 * @param unknown_type $precio_anterior
 * @param unknown_type $nuevo_precio_stock
 */
function enviar_mail_diferencia_precio($nro_orden,$descripcion,$precio_anterior,$nuevo_precio_stock){
	
	$para = "juanmanuel@coradir.com.ar,noelia@coradir.com.ar";	
	$asunto = " Modificación de Precios Superior al 5% en el producto $descripcion";
	$contenido = "Modificación de Precios\n
                  El precio del  producto $descripcion al ser recibido  tuvo una variación del 5 % \n
                  con respecto al precio anterior\n\n\n
                  Se recibio mediante la OC N° $nro_orden\n\n
	              Precio Anterior = u\$s $precio_anterior \n
	              Precio Nuevo    = u\Ss $nuevo_precio_stock \n
	              Por favor tomar las medidas correspondientes \n
	";
	enviar_mail($para,$asunto,$contenido,"","","");
}


function enviar_mail_recepcion_oc_inter($nro_orden,$descripcion){
	
    $para = "juanmanuel@coradir.com.ar,noelia@coradir.com.ar";	
	$asunto = " OC Internacional - No se modificaron precios para el  producto $descripcion ";
	$contenido = " Modificación de Precios\n
                   El precio del  producto \"$descripcion\"  no se actualizó\n 	                       
	               al ser recibido mediante la OC Internacional N° $nro_orden\n
	               \n\n
	               Por Favor Modificarlo manualmente
	";
	enviar_mail($para,$asunto,$contenido,"","","");	
}
function enviar_mail_recepcion_en_pesos($nro_orden,$descripcion)	{
	$para = "juanmanuel@coradir.com.ar,noelia@coradir.com.ar";	
	$asunto = " OC en Pesos - No se modificaron precios para el  producto $descripcion ";
	$contenido = " Modificación de Precios\n
                   El precio del  producto \"$descripcion\"  no se actualizó\n 	                       
	               por ser una  OC en PESOS \n
	               OC nro: $nro_orden\n
	               \n\n
	               Por Favor Modificarlo manualmente
	";
	enviar_mail($para,$asunto,$contenido,"","","");	
}
/******************************************************************************
Inserta en la tabla recibido, las cantidades recibidas.
Si ya existe una entrada para ese producto, actualiza la que
está, sumandole la nueva cantidad ingresada.
Tambien agrega los productos a stock disponible o reservado dependiendo del
tipo de OC. Mediante el parametro @accion se puede especificar exactamente
cuales tareas debe realizar la funcion (registrar recepciones, agregar a stock,
o ambas cosas).

@nro_orden			El Nº de OC a la que se le agregaran los
					productos recibidos
@id_fila			La fila para la cual se recibiran los productos
@cant				La cantidad de productos que se recibiran para esa fila
@id_deposito 		El deposito donde se recibiran los productos
@id_prod_recibido	El id del producto especifico que se recibe
@id_recibido		El id de recepcion. Si existe se actualiza esa entrada
					en la tabla recibido_entregado, sino se genera una nueva.
@observaciones		Las observaciones ingresadas para la recepcion de la fila.
@accion				Indica el conjunto de acciones que esta
					funcion debe realizar, mediante un numero:
				   		1-> Agrega las recepciones de la OC
				   			y agrega los productos recibidos al stock a confirmar.
				   		2-> Solo agrega las actualizaciones de
				   			Stock (disponible o reservado)
				   			pero no modifica las recepciones de la OC.
				   		3-> Realiza tanto la tarea 1 como la 2.
*******************************************************************************/
function insertar_recibidos($nro_orden,$id_fila,$cant,$id_deposito,$id_prod_recibido,$id_recibido="",$observaciones="",$accion)
{
 global $db,$_ses_user;

 //esto no deberia pasar, pero por las dudas...
 if($accion!=1 && $accion!=2 && $accion!=3)
  die("Error Interno OCREC449: No se pudo determinar el tipo de acción a realizar para la recepción de productos. Consulte con la División Software");

 include_once("../stock/funciones.php");//incluimos el archivo de funciones de stock

 $db->StartTrans();

 $fecha=date("Y-m-d H:i:s",mktime());
 $usuario=$_ses_user["name"];


    //si la accion es 1 o 3 se agrega las recepciones de la OC
	if($accion==1 || $accion==3)
	{
	    //controlamos cuantos recibidos y entregados tiene esta fila.
	    $query="select sum (cantidad)as cantidad,id_recibido,ent_rec,id_deposito
	            from recibido_entregado where id_fila=$id_fila and ent_rec=1
	            group by id_recibido,ent_rec,id_deposito
	            order by ent_rec desc";
	    $datos_ent_rec=sql($query ,"<br>Error al traer ent_rec (insertar_recibidos) de la fila $id_fila") or fin_pagina();

	    if($id_deposito==-1)
	      $id_deposito="NULL";

	    if($id_recibido=="")
	    {//insert
	     $query="select nextval('recibidos_id_recibido_seq') as id_recibido";
	     $id_rec=sql($query,"<br>Error al traer la secuencia de recibido<br>") or fin_pagina();
	     $id_recibido=$id_rec->fields['id_recibido'];

	     $query="insert into recibido_entregado(id_recibido,id_fila,id_deposito,cantidad,observaciones,ent_rec)
	     values(".$id_rec->fields['id_recibido'].",$id_fila,$id_deposito,$cant,'$observaciones',1)";
	    }
	    else
	    {//update

	     if($id_deposito!="" && $id_deposito!=-1)
	      $dep="id_deposito=$id_deposito,";
	     else
	      $dep="";
	     $query="update recibido_entregado set $dep observaciones='$observaciones',cantidad=cantidad+$cant
	     where id_recibido=$id_recibido";
	    }

	    sql($query,"<br>Error al insertar/actualizar los recibidos de $id_recibido<br>") or fin_pagina();

	    //Se agrega el log para registrar la recepcion del producto
	    if($cant!="" && $cant>0)
	    {$query_ins="insert into log_rec_ent(id_recibido,usuario,fecha,cant,id_prod_esp)
	          values($id_recibido,'$usuario','$fecha',$cant,$id_prod_recibido)";
	     sql($query_ins,"<br>Error al insertar el log de recepcion (insertar_recibidos)<br>") or fin_pagina();
	    }

	    //si la accion a realizar es la 1, entonces agregamos los productos recibidos al stock a confirmar
	    if($accion==1)
	    {
	    	//si la moneda es $ tomamos el valor del dolar del sistema,  sino tomamos 1 como valor del dolar
		     if($tipo_oc->fields["simbolo"]=="$")
		     {
		     	//tomamos el valor actual del dolar
		     	$query="select valor from dolar_general";
		     	$dolar_general=sql($query,"<br>Error al traer el valor del dolar general<br>") or fin_pagina();
		     	$valor_dolar=$dolar_general->fields["valor"];
		     }
		     else //la moneda es U$S
		      $valor_dolar=1;


	    	//Si el producto ya se encuentra en algun stock, traemos el precio_stock actual, para tomarlo en cuenta para el promedio
		    //de precio que vamos a hacer entre el precio actual y el que tiene la fila
		    $query="select distinct precio_stock from general.producto_especifico join stock.en_stock using(id_prod_esp)
		    	    where id_prod_esp=$id_prod_recibido";
		    $precio_stock_c=sql($query,"<br>Error al traer el precio actual del producto en stock<br>") or fin_pagina();

		    //si no es OC internacional
		    if(!$internacional)
		    {
		    	//traemos el precio unitario total de la fila
		    	$query="select precio_unitario
		            from compras.fila
		            where id_fila=$id_fila";
		    }
		    //si es OC Internacional
		    else
		    {
			    //traemos el precio unitario de la fila tomando en cuenta las cosas extra de la OC internacional
			    $query="select (((precio_unitario*cantidad)+proporcional_flete+derechos+iva+ib)/cantidad) as precio_unitario
			            from compras.fila
			            where id_fila=$id_fila";
		    }
		    $precio_fila=sql($query,"<br>Error al traer el monto total de la fila<br>") or fin_pagina();
		    $precio_unitario_fila=$precio_fila->fields["precio_unitario"]/$valor_dolar;

		     //si tenemos precio_stock  del producto el nuevo precio de stock es
		     //el promedio entre el precio de stock y el de la fila
		     if($precio_stock_c->fields["precio_stock"]!="" && $precio_stock_c->fields["precio_stock"]>0)
		     {
		     	//traemos la cantidad total de ese producto en los stocks de San Luis, Buenos Aires, Serv Tec Bs As y Produccion
			    $sql="select sum(cant_disp+cant_reservada+cant_a_confirmar) as cant_stock
			          from   stock.en_stock
			    		     join general.depositos using (id_deposito)
			    	  where  id_prod_esp=$id_prod_recibido
			    	  	     and (depositos.nombre='San Luis' or depositos.nombre='Buenos Aires'
			    	  	     	  or depositos.nombre='Serv. Tec. Bs. As.' or depositos.nombre='Produccion')
			    	  ";
				$cant_total_en_stock=sql($sql, "Error al traer cantidad total del producto en todos los stocks") or fin_pagina();

				//el nuevo precio de stock sera un promedio del precio del producto en la fila de la OC y el que esta
				//en stock. Para eso se suma todo el monto total de ese producto en los stocks mencionados arriba, y a eso se
				//le agrega el monto total recibido de la fila.
				//La suma de esos dos montos se divide por la cantidad total en stock actual + la cantidad recibida de la fila.
				//Eso nos da el nuevo precio de stock
				$nuevo_precio_stock=(
				 					  ($precio_stock_c->fields["precio_stock"]*$cant_total_en_stock->fields["cant_stock"])
									   +  //monto total en los stocks + monto total de la fila (divido el valor_dolar para poner el precio siempre en dolares)
									  ($precio_unitario_fila*$cant)
									)
									/     //dividido la cantidad en stock + la cantidad recibida
									($cant+$cant_total_en_stock->fields["cant_stock"])
									;
				/*echo "valor dolar $valor_dolar<br>";
			    echo "precio_stock: ".$precio_stock_c->fields["precio_stock"]." - cantidad total en stock: ".$cant_total_en_stock->fields["cant_stock"]."<br>
			  	      precio de la fila: $precio_unitario_fila - cantidad recibida: $cant -  cant total stock: ".$cant_total_en_stock->fields["cant_stock"];
			    echo "<br>EL PRECIO NUEVO $nuevo_precio_stock";die;*/

		     }//de if($precio_stock_c->fields["precio_stock"]!="" && $precio_stock_c->fields["precio_stock"]>0)
		     else//sino, el nuevo precio es el unitario de la fila
		      $nuevo_precio_stock=$precio_unitario_fila;

	         //determinamos el tipo de detalle a confirmar que vamos a ingresar, dependiendo del tipo de la OC
		     $query="select orden_de_compra.id_licitacion,orden_de_compra.es_presupuesto,orden_de_compra.nrocaso,
		     				orden_de_compra.flag_stock,orden_de_compra.internacional,moneda.simbolo
		             from compras.orden_de_compra join licitaciones.moneda using(id_moneda)
		             where orden_de_compra.nro_orden=$nro_orden";
		     $tipo_oc=sql($query,"<br>Error al consultar el tipo de la OC(insertar_recibidos)<br>") or fin_pagina();

		     if($tipo_oc->fields["id_licitacion"]!="" && $tipo_oc->fields["es_presupuesto"]!=1)
		     {$tipo_a_confirmar="la Licitación Nº ".$tipo_oc->fields["id_licitacion"];
		      $clave_id="Licitación";
		      $id_licitacion_a_confirmar=$tipo_oc->fields["id_licitacion"];
		     }
		     elseif($tipo_oc->fields["id_licitacion"]!="" && $tipo_oc->fields["es_presupuesto"]==1)
		     {$tipo_a_confirmar="el Presupuesto  Nº ".$tipo_oc->fields["id_licitacion"];
		      $clave_id="Presupuesto";
		      $id_licitacion_a_confirmar=$tipo_oc->fields["id_licitacion"];
		     }
		     elseif($tipo_oc->fields["nrocaso"]!="")
		     {$tipo_a_confirmar="el C.A.S. de Servicio Técnico Nº ".$tipo_oc->fields["nrocaso"];
		      $clave_id="C.A.S.";
		      $nrocaso_a_confirmar=$tipo_oc->fields["nrocaso"];
		     }
		     elseif($tipo_oc->fields["flag_stock"] || $tipo_oc->fields["internacional"])
		     {
		      if($tipo_oc->fields["flag_stock"])
		     	$tipo_a_confirmar="Stock";
		      else
		     	$tipo_a_confirmar="OC Internacional";
		      $flag_stock=$tipo_oc->fields["flag_stock"];
		      $internacional=$tipo_oc->fields["internacional"];
		     }
		     else
		      $tipo_a_confirmar="";

 		     //traemos el id del tipo de detalle a confirmar, para poder insertarlo
			 $query="select id_tipo_detalle_a_confirmar from stock.tipo_detalle_a_confirmar
			 		 where nombre_tipo_detalle ilike '%$clave_id%'";
			 $tipo_det_ac=sql($query,"<br>Error al traer el id del tipo de detalle a confirmar(insertar_recibidos)<br>") or fin_pagina();
		     $id_tipo_detalle_a_confirmar=$tipo_det_ac->fields["id_tipo_detalle_a_confirmar"];

			 //traemos el id de tipo de movimiento de stock con nombre: Se recibieron productos de una Orden de Compra
		     $query="select id_tipo_movimiento from tipo_movimiento where nombre='Se recibieron productos de una Orden de Compra'";
		     $id_mov_stock=sql($query,"<br>Error al traer el tipo de movimiento de stock<br>") or fin_pagina();
		     if($id_mov_stock->fields["id_tipo_movimiento"])
		     	$id_tipo_movimiento=$id_mov_stock->fields["id_tipo_movimiento"];
		     else
		     	die("Error Interno IRS623: No se pudo determinar el tipo de movimiento de stock a realizar. Contactese con la División Software.");

		     $comentario="Productos recibidos para $tipo_a_confirmar mediante la OC Nº $nro_orden";
		     agregar_stock($id_prod_recibido,$cant,$id_deposito,$comentario,$id_tipo_movimiento,"a confirmar","",$id_fila,"",$id_licitacion_a_confirmar,$nrocaso_a_confirmar,$id_tipo_detalle_a_confirmar);

             
		    $sql = "select id_prod_esp from general.prod_no_se_actualizan_precios where id_prod_esp = $id_prod_recibido ";
		    $bloqueo_precios = sql($sql) or fin_pagina(); 

         	$sql="select descripcion,precio_stock from producto_especifico
                         where id_prod_esp=$id_prod_recibido";
	        $res=sql($sql) or fin_pagina();	    
	        $descripcion = $res->fields["descripcion"];
	        $precio_stock = $res->fields["precio_stock"];
		     //si el producto esta en prod_no_se_actualizan_precios no lo actualizo
		     //y si corresponde a un oc internacional

		     if (!$bloqueo_precios->recordcount() && !$tipo_oc->fields["internacional"] && $tipo_oc->fields["simbolo"]!="$"){
                          //recuperamos el precio anterior
 	                        ($precio_stock!="")? $precio_anterior=$res->fields["precio_stock"]: $precio_anterior = $nuevo_precio_stock ;
                             $dif = diferencia($precio_anterior,$nuevo_precio_stock,5);
 	                        
 	                        if ($dif) 
 	                          	enviar_mail_diferencia_precio($nro_orden,$descripcion,$precio_anterior,$nuevo_precio_stock); 	                           

	                       //actualizamos el precio del stock con el nuevo precio obtenido antes
                            $comentario_precio="Modificación por recepción de $cant productos mediante Orden de Compra Nº $nro_orden";
		                    modif_precio($id_prod_recibido,$nuevo_precio_stock,$comentario_precio);
			              
		     }elseif ($tipo_oc->fields["internacional"]){
		     	enviar_mail_recepcion_oc_inter($nro_orden,$descripcion);
		     }elseif($tipo_oc->fields["simbolo"]=="$")
		     {
		     enviar_mail_recepcion_en_pesos($nro_orden,$descripcion);	
		     }

	    }//if($accion==1)

	}//de if($accion==1 && $accion==3)

	//si la accion es 2 o 3 se agregan las actualizaciones de Stock
	if($accion==2 || $accion==3)
	{
		//si la acción elegida es 2, significa que no solo se debe pasar los productos a stock disponible o reservado
		//sino que antes tambien hay que descontarlos de stock a confirmar
		//(porque fueron agregados cuando se pidio la accion 1, anteriormente)
		if($accion==2)
		{
			//log del movimiento realizado
			//obtenemos el id del movimiento que vamos a realizar
			$query="select id_tipo_movimiento from stock.tipo_movimiento where nombre='Confirmación de Recepción de productos para una OC'";
			$id_mv=sql($query,"<br>Error al traer el id del movimiento para descontar cantidades a confimar<br>") or fin_pagina();
			if($id_mv->fields["id_tipo_movimiento"])
				$id_tipo_movimiento=$id_mv->fields["id_tipo_movimiento"];
			else
				die("Error Interno SF655: No se pudo determinar el tipo de movimiento de stock a realizar. Consulte a la División Software");

			$comentario="Se confirmó la recepción de los productos para la OC Nº $nro_orden";
			descontar_a_confirmar($id_prod_recibido,$cant,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila);

		}//de if($accion==2)

	    //vemos el tipo de la OC
	    $query="select id_licitacion,es_presupuesto,nrocaso,flag_stock,internacional,simbolo
	            from compras.orden_de_compra join licitaciones.moneda using(id_moneda)
	            where nro_orden=$nro_orden";
	    $tipo_oc=sql($query,"<br>Error al consultar el tipo de la OC(insertar_recibidos)<br>") or fin_pagina();

	    //si la moneda es $ tomamos el valor del dolar del sistema,  sino tomamos 1 como valor del dolar
	    if($tipo_oc->fields["simbolo"]=="$")
	    {
	    	//tomamos el valor actual del dolar
	    	$query="select valor from dolar_general";
	    	$dolar_general=sql($query,"<br>Error al traer el valor del dolar general<br>") or fin_pagina();
	    	$valor_dolar=$dolar_general->fields["valor"];
	    }
	    else //la moneda es U$S
	     $valor_dolar=1;

	    if($tipo_oc->fields["id_licitacion"]!="" && $tipo_oc->fields["es_presupuesto"]!=1)
	    {$tipo_reserva="la Licitación Nº ".$tipo_oc->fields["id_licitacion"];
	     $clave_id="Licitación";
	     $id_licitacion_reserva=$tipo_oc->fields["id_licitacion"];
	    }
	    elseif($tipo_oc->fields["id_licitacion"]!="" && $tipo_oc->fields["es_presupuesto"]==1)
	    {$tipo_reserva="el Presupuesto  Nº ".$tipo_oc->fields["id_licitacion"];
	     $clave_id="Presupuesto";
	     $id_licitacion_reserva=$tipo_oc->fields["id_licitacion"];
	    }
	    elseif($tipo_oc->fields["nrocaso"]!="")
	    {$tipo_reserva="el C.A.S. de Servicio Técnico Nº ".$tipo_oc->fields["nrocaso"];
	     $clave_id="C.A.S.";
	     $nrocaso_reserva=$tipo_oc->fields["nrocaso"];
	    }
	    elseif($tipo_oc->fields["flag_stock"] || $tipo_oc->fields["internacional"])
	    {
	     if($tipo_oc->fields["flag_stock"])
	     	$tipo_a_confirmar="Stock";
	     else
	     	$tipo_a_confirmar="OC Internacional";

	     $flag_stock=$tipo_oc->fields["flag_stock"];
	     $internacional=$tipo_oc->fields["internacional"];
	    }
	    else
	     $tipo_reserva="";

	    //traemos el id del tipo de reserva, para poder insertarlo
		$query="select id_tipo_reserva from tipo_reserva where nombre_tipo ilike '%$clave_id%'";
		$tipo_reserv=sql($query,"<br>Error al traer el id del tipo de reserva(insertar_recibidos)<br>") or fin_pagina();
	    $id_tipo_reserva=$tipo_reserv->fields["id_tipo_reserva"];

	    $fecha_modif=date("Y-m-d H:i:s",mktime());
	    $agregado_stock=0;
	    if($tipo_reserva!="" && $cant>0)
	    {
	     /**********************************************************************************
	      Hacemos la reserva de los productos recibidos, poniendo en cada caso para que se
	      reservan esos productos (y agregando a que estan asociados). Esto es valido solo
	      para las OC asociadas a Licitacion, Presupuesto o Sev Tec.
	     ***********************************************************************************/
	     $comentario="Recepción Confirmada para OC Nº $nro_orden (asociada a $tipo_reserva)";

	     if($accion==2)
	     	$tipo_mov="Recepción Confirmada para OC";
	     else
	     	$tipo_mov="Se recibieron productos de una Orden de Compra";
	     //traemos el id del tipo de movimiento de stock correspondiente
	     $query="select id_tipo_movimiento from tipo_movimiento where nombre='$tipo_mov'";
	     $id_mov_stock=sql($query,"<br>Error al traer el tipo de movimiento de stock<br>") or fin_pagina();
	     $id_tipo_movimiento=$id_mov_stock->fields["id_tipo_movimiento"];

	     agregar_stock($id_prod_recibido,$cant,$id_deposito,$comentario,$id_tipo_movimiento,"reservado",$id_tipo_reserva,$id_fila,'',$id_licitacion_reserva,$nrocaso_reserva);
         $agregado_stock=1;

	    }//de if($tipo_reserva!="")
	    elseif (($flag_stock || $internacional) && $cant>0)
	    {
	     if(!$internacional)
	       $comentario="Recepción Confirmada para Orden de Compra Nº $nro_orden";
		 else
		   $comentario="Recepción Confirmada para Orden de Compra Internacional Nº $nro_orden";

		 if($accion==2)
	     	$tipo_mov="Recepción Confirmada para OC";
	     else
	     	$tipo_mov="Ingreso por Orden de Compra";
	     //traemos el id de tipo del movimiento de stock correspondiente
	     $query="select id_tipo_movimiento from tipo_movimiento where nombre='$tipo_mov'";
	     $id_mov_stock=sql($query,"<br>Error al traer el tipo de movimiento de stock<br>") or fin_pagina();
	     $id_tipo_movimiento=$id_mov_stock->fields["id_tipo_movimiento"];

	     agregar_stock($id_prod_recibido,$cant,$id_deposito,$comentario,$id_tipo_movimiento,"disponible");
	     $agregado_stock=1;

	    }//de elseif (($flag_stock || $internacional) && $cant>0)

	}//de if($accion==2 || $accion==3)

 $db->CompleteTrans();

}//de function insertar_recibidos($nro_orden,$items)



function insertar_pago_orden($parametros,$datos)
{
 global $db,$_ses_user;
 if (($parametros['pagina']=="orden_de_compra") or ($parametros['pagina']=="egreso"))
   {

   //inserto el numero de pagos
   $nro_orden=$datos["nro_orden"];
   $fecha=date("Y-m-d H:i:s",mktime());
   $importe=$datos['importe'];
   $usuario=$_ses_user['name'];
   $id_pago=$datos["id_pago"];
   if ($parametros["valor_dolar"]) {
                                   $valor_dolar=$parametros["valor_dolar"];
                                   $importe=number_format($importe/$valor_dolar,"2",".","");
                                   }
                                 else   $valor_dolar=0;
   switch($datos["pagina"])
      { //dependiendo de la pagina que venga tomo una accion
      case "cheques":
                     $numero_cheque=$datos["numero_cheque"];
                     $id_banco=$datos["id_banco"];
                     $sql="update ordenes_pagos set  NúmeroCh=$numero_cheque,IdBanco=$id_banco, ";
                     $sql.=" monto=$importe,fecha='$fecha',usuario='$usuario',valor_dolar=$valor_dolar where id_pago=$id_pago";
                     sql($sql) or fin_pagina();;
                     break;
      case "efectivo":
                     //importe va aca por que se paga con la moneda de origen
                     $importe=$datos['importe'];
                     $id_ingreso_egreso=$datos["id_ingreso_egreso"];
                     /*
                     $sql="insert into ordenes_pagos (nro_orden,id_ingreso_egreso,monto,fecha,usuario,valor_dolar) ";
                     $sql.="  values ($nro_orden,$id_ingreso_egreso,$importe,'$fecha','$usuario',$valor_dolar)";
                     */
                     $sql="update ordenes_pagos set id_ingreso_egreso=$id_ingreso_egreso,monto=$importe,";
                     $sql.=" fecha='$fecha',usuario='$usuario',valor_dolar=$valor_dolar where id_pago=$id_pago";
                     sql($sql) or fin_pagina();
                      break;
      case "transferencia":
                     $iddebito=$datos["id_debito"];
                     $sql="update ordenes_pagos set idDébito=$iddebito,monto=$importe,fecha='$fecha',usuario='$usuario',";
                     $sql.="valor_dolar=$valor_dolar where id_pago=$id_pago";
                     sql($sql) or fin_pagina();;

                     break;
           }//del switch
     } // del then del if

} //de function insertar_pago_orden($parametros,$datos)

function datos_orden_compra($parametros)
{
	global $db;
	$importe=0;
	$importe_dolares=0;
	//funcion que obtiene todos los datos de la orden de compra
	if (($parametros['pagina_viene']=="orden_de_compra") || ($parametros['pagina']=="orden_de_compra"))
	   {
	    //obtengo el total de la orden de compra
	    $nro_orden=$parametros['nro_orden'];
	    $id_pago=$parametros['id_pago'];

	    $sql="select id_pago,monto,valor_dolar,dias from ordenes_pagos join forma_de_pago using (id_forma) where id_pago=$id_pago";
	    $resultado=sql($sql) or fin_pagina();;
	    $importe=number_format($resultado->fields['monto'],"2",".","");
	    $valor_dolar=$resultado->fields['valor_dolar'];
	    $dias=$resultado->fields['dias'];
	    if ($parametros["valor_dolar"]) {
	                                    $importe_dolares=number_format($importe*$parametros["valor_dolar"],"2",".","");
	                                    }
	                                    else
	                                    {
	                                    $importe_dolares=0;
	                                    }

	    $sql="select id_proveedor,id_moneda,razon_social from orden_de_compra join general.proveedor using (id_proveedor) where nro_orden=$nro_orden";
	    $resultado=sql($sql) or die();
	    $proveedor=$resultado->fields['id_proveedor'];
	    $id_moneda=$resultado->fields['id_moneda'];
	    $filtro_proveedor=substr($resultado->fields['razon_social'],0,1);
	   //retorno el id del tipo de pago
	    $sql="select id_tipo_egreso from tipo_egreso where nombre='Licitaciones'";
	    $resultado=sql($sql)or die();
	    $id_tipo_egreso=$resultado->fields['id_tipo_egreso'];
	    //formo el arreglo que devuelvo

	    $datos=array("nro_orden"=>$nro_orden,"importe"=>$importe,"valor_dolar"=>$valor_dolar,"proveedor"=>$proveedor,"importe_dolares"=>$importe_dolares,"id_moneda"=>$id_moneda,"id_tipo_egreso"=>$id_tipo_egreso,"filtro_proveedor"=>$filtro_proveedor,"dias"=>$dias);
	    return $datos;
	}//fin then del if
	    else return 0;
}//de function datos_orden_compra($parametros)


function estado_orden_compra($nro_orden)
{
	//funcion que me devuelve los totales
	global $db;
	       $datos;  //arreglo que devuelvo con los datos
	       $nro_pagos=1;
	$sql="select * from (orden_de_compra join moneda using(id_moneda)) where nro_orden=$nro_orden";
	$resultado=sql($sql) or fin_pagina();;
	if ($resultado->fields['nombre']=="Dólares") $moneda=1;
	                                             else $moneda=0;
	$id_moneda=$resultado->fields['id_moneda'];
	$numeros_pagos=$resultado->fields['numeros_pagos'];
	$comentario=$resultado->fields['comentario'];
	if ($resultado->fields['pago_especial']==1) $pago_especial=1;
	                                             else $pago_especial=0;
	//obtengo el monto a percibir por la orden de compra
	//obtengo lo que ya pago de la orden de compra
	$sql="select sum(ordenes_pagos.monto) as total_monto from ordenes_pagos join pago_orden using (id_pago) where nro_orden=$nro_orden";
	$resultado=sql($sql) or fin_pagina();;
	$monto=$resultado->fields['total_monto'];
	if (!($monto)) $monto=0;

	//obtengo los totales
	$sql="select sum(cantidad * precio_unitario) as monto_total from fila where nro_orden=$nro_orden";
	$resultado=sql($sql) or fin_pagina();;
	$importe=$resultado->fields['monto_total'];
	$datos=array("id_moneda"=>$id_moneda,"moneda"=>$moneda,"numeros_pagos"=>$numeros_pagos,"monto"=>$monto,"importe"=>$importe,"comentario"=>$comentario,"pago_especial"=>$pago_especial);

	return $datos;
}//de function estado_orden_compra($nro_orden)


function monto_pagado($nro_orden)
{
	global $db;

	$sql="select * from pago_orden join ordenes_pagos using (id_pago) ";
	$sql.="where pago_orden.nro_orden=$nro_orden";
	$resultado=sql($sql) or fin_pagina();;
	$cantidad_filas=$resultado->RecordCount();
	$importe=0;
	for($i=0;$i<$cantidad_filas;$i++){
	       if ($resultado->fields['númeroch'] || $resultado->fields['iddébito']||$resultado->fields['id_ingreso_egreso'])
	        $importe+=$resultado->fields['monto'];
	        $resultado->MoveNext();

	    }//del for

	return number_format($importe,"2",".","");
}//de function monto_pagado($nro_orden)


/********************************************************************************
 Funcion que devuelve el monto a pagar de la OC pasada como parametro.
 El parametro opcional $internacional se utiliza cuando la OC es internacional
 y hay que agregarle al monto total de las filas los montos propios de las
 OC Internacionales (Flete, Total I.V.A./Ganancias, Total I.B., Total Derechos y
 Honorarios y Gastos)
*********************************************************************************/
function monto_a_pagar($nro_orden)
{
global $db;
$importe=0;

//dado el nro de OC, vemos si es Internacional o no
$query="select internacional from orden_de_compra where nro_orden=$nro_orden";
$int=sql($query,"<br>Error al traer el dato de internacional de la OC<br>") or fin_pagina();
$internacional=$int->fields["internacional"];

//if ($nro_orden!=""){
$sql="select sum(cantidad*precio_unitario) as total from fila where nro_orden=$nro_orden";
$resultado=sql($sql) or fin_pagina();;
$importe=$resultado->fields["total"];
//}

//si la OC es internacional, debemos tomar en cuenta los montos extra que forman el monto global
//(Flete, Total I.V.A./Ganancias, Total I.B., Total Derechos y Honorarios y Gastos)
if($internacional)
{
 //traemos el total de "Total de IVA/Ganancias", el total de "Total IB" y el total de "Derechos"
 $query="select sum(iva) as total_iva,sum(ib) as total_ib,sum(derechos) as total_derechos
         from fila where nro_orden=$nro_orden";
 $montos_fila_int=sql($query,"<br>Error al traer los montos internacionales de las filas<br>") or fin_pagina();

 //traemos el flete y los honorarios y gastos de la OC
 $query="select monto_flete,honorarios_gastos from datos_oc_internacional where nro_orden=$nro_orden";
 $montos_oc_int=sql($query,"<br>Error al traer datos internacionales de la OC<br>") or fin_pagina();

 $importe+=$montos_fila_int->fields["total_iva"];
 $importe+=$montos_fila_int->fields["total_ib"];
 $importe+=$montos_fila_int->fields["total_derechos"];
 $importe+=$montos_oc_int->fields["monto_flete"];
 $importe+=$montos_oc_int->fields["honorarios_gastos"];


}//de if($internacional)

//die("el importe a pagar es $importe");

return $importe;

}//de function monto_a_pagar($nro_orden)

//funcion que se usa cuando hay pagos multiples
//para calcular el total a pagar entre todas las
//ordenes pasdas por parametro en el arreglo $nro_orden
function monto_pago_multiple($nro_orden)
{
 $tam=sizeof($nro_orden);
 $total=0;
 for($i=0;$i<$nro_orden;$i++)
 {$monto=monto_a_pagar($nro_orden[$i]);
  //vemos el tipo de moneda de la orden, para saber si el monto obtendio
  //es en pesos o en dolares
  $query="select nombre,valor_dolar from moneda join orden_de_compra using (id_moneda) where nro_orden=".$nro_orden[$i];
  $moneda=sql($query) or fin_pagina();;
  if($moneda->fields['nombre']=="Dólares")
  {
   $total+=$monto*$moneda->fields['valor_dolar'];
  }
  else
   $total+=$monto;
 }//del for
 return $total;
}//de function monto_pago_multiple($nro_orden)



function pagos_restantes($nro_orden)
{
   global $db;
  $sql="select * ";
  $sql.=" from  pago_orden  join ordenes_pagos using(id_pago)";
  $sql.=" where pago_orden.nro_orden=$nro_orden";
  $orden_compra_pagos=sql($sql) or fin_pagina();;
  $cantidad_pagos=$orden_compra_pagos->recordcount();
  $pagos_hechos=0;

    for ($i=0;$i<$cantidad_pagos;$i++){
     if ($orden_compra_pagos->fields['númeroch'] || $orden_compra_pagos->fields['iddébito']||$orden_compra_pagos->fields['id_ingreso_egreso'])
     {
     $pagos_hechos++;
     }
    $orden_compra_pagos->Movenext();
    } //del for


 return ($cantidad_pagos-$pagos_hechos);
}//de function pagos_restantes($nro_orden)


/*************************************************
FUNCION PARA GUARDAR LAS FORMAS DE PAGO
retorna 1 si no hubo problemas, o el mensaje de error
si lo hubo
**************************************************/
function guardar_forma_pago($cant,$nro_orden,$cambios_forma)
{global $db,$id_forma,$id_plantilla;
  $db->StartTrans();
  $msg="";
  $desc=$_POST['titulo_pago'];
  //si esta chequeado por default, ponemos en 1 el campo $mostrar, sino en 0.
  if($_POST['chk_default']=="1")
   $mostrar=1;
  else
   $mostrar=0;
  if(!$mostrar)
  {
   $query="insert into plantilla_pagos (descripcion,mostrar) values('$desc',$mostrar)";
   sql($query) or fin_pagina(); ;
   $query="select max(id_plantilla_pagos) as id from plantilla_pagos";
   $res_plant=sql($query) or fin_pagina();
   $id_plantilla=$res_plant->fields['id'];

  }
  elseif($mostrar)
  {//si se guarda por default, chequeamos si el nombre existe
   $query="select id_plantilla_pagos from plantilla_pagos where descripcion='$desc'";
   $res=sql($query) or fin_pagina();
   //si el nombre existe no se puede hacer nada
   $existe=$res->RecordCount();
   if($existe)
   {$id_plantilla=$res->fields['id_plantilla_pagos'];
    if($cambios_forma==1)
     $msg="<font size=3><b>ERROR: No se puede agregar o modificar la forma de pago.<br> El nombre elegido para la forma de pago por default ya está en uso</b><BR><BR></font>";
    /*$query="update plantilla_pagos set mostrar=$mostrar where id_plantilla_pagos=$id_plantilla";
   	$db->Execute($query) or die ($db->ErrorMsg()."Actualizacion de la plantila de pagos");	*/
   	//echo "No se puede guardar una por default";
   }
   //sino, el nombre no existe entonces se debe insertar una nueva plantilla
   //por default
   else
   {$query="insert into plantilla_pagos (descripcion,mostrar) values('$desc',$mostrar)";
    sql($query) or fin_pagina();
    $query="select max(id_plantilla_pagos) as id from plantilla_pagos";
    $res_plant=sql($query) or fin_pagina();
    $id_plantilla=$res_plant->fields['id'];
   }
  }
  //borramos las entradas existentes si existen en las tablas:
  //forma de pago y pago_plantilla (siempre que la plantilla no sea por default y se la este actualizando)
  if(!$existe)
  {$query="select id_forma from forma_de_pago join pago_plantilla using(id_forma) where id_plantilla_pagos=$id_plantilla";
   $res_formas_borrar=sql($query) or fin_pagina();

   $query="delete from pago_plantilla where id_plantilla_pagos=$id_plantilla";
   sql($query) or fin_pagina();
   while(!$res_formas_borrar->EOF)
   {$query="delete from forma_de_pago where id_forma=".$res_formas_borrar->fields['id_forma'];
    sql($query) or fin_pagina();
    $res_formas_borrar->MoveNext();
   }

   //insersion de las forma de pago nuevas
   for($i=0;$i<$cant;$i++)
    {
     $dias=$_POST["cantidad_dias_".$i];
     $tipo_pago=$_POST["select_tipo_pago_".$i];
     $query="insert into forma_de_pago (dias,id_tipo_pago) values($dias,$tipo_pago)";
     sql($query) or fin_pagina();
     $query="select max(id_forma) as id from forma_de_pago";
     $res_forma=sql($query) or fin_pagina();
     $id_forma=$res_forma->fields['id'];

     //insertamos la relacion de la planilla de pago con las formas de pago
     $query="insert into pago_plantilla (id_forma,id_plantilla_pagos) values($id_forma,$id_plantilla)";
     sql($query) or fin_pagina();
    }
  }
    //actualizamos la plantilla de la orden de compra
    if($nro_orden!=""&&$msg=="")
   {$query="update orden_de_compra set id_plantilla_pagos=$id_plantilla where nro_orden=$nro_orden";
    sql($query) or fin_pagina();
   }
 $db->CompleteTrans();
 return $msg;
}//de function guardar_forma_pago($cant,$nro_orden,$cambios_forma)

/************************************************
FUNCION PARA GUARDAR LAS ORDENES DE PAGO
Toma el numero de orden de compras a la que se
le asocian los pagos, y el monto de las cuotas
de cada pago. El parametro opcional dolar,
especifica el valor del dolar para cada cuota.
*************************************************/
function insertar_ordenes_pagos($nro_orden,$cuotas,$dolar=0)
{global $db;
  $db->StartTrans();
 //traemos las formas de pago asociadas a la orden de compras
 //para asociarlas a la orden de pagos
 $query="select id_forma from forma_de_pago join pago_plantilla using (id_forma) join plantilla_pagos using(id_plantilla_pagos) join orden_de_compra using (id_plantilla_pagos) where nro_orden=$nro_orden order by id_forma";
 $formas=sql($query,"Error en la seleccion de las formas de pago<br>") or fin_pagina();
 $long=sizeof($cuotas);

 //seleccionamos las ordenes de pagos vieja si es que hay y las borramos
 $query="select id_pago from ordenes_pagos join pago_orden using(id_pago) join orden_de_compra using (nro_orden) where nro_orden=$nro_orden";
 $res_ord_pago=sql($query,"<br>Error en la seleccion de ordenes_pago<br>") or fin_pagina();

 //borramos las entrada de pago_orden viejas si es que hay
  $query="delete from pago_orden where nro_orden=$nro_orden";
  $a=sql($query,"Error en el borrado de pago_orden<br>") or fin_pagina();

 while(!$res_ord_pago->EOF)
 {$query="delete from ordenes_pagos where id_pago=".$res_ord_pago->fields['id_pago'];
  sql($query,"<br>Error en el borrado de ordenes_pago<br>") or fin_pagina();
  $res_ord_pago->MoveNext();
 }

 for($i=0;$i<$long;$i++)
 {if($dolar==0)
   $valor=1;
  else
   $valor=$dolar[$i];
  $id_forma=$formas->fields['id_forma'];

  //insertamos la orden de pago para esta cuota
  $query="insert into ordenes_pagos (monto,valor_dolar,id_forma) values($cuotas[$i],$valor,$id_forma)";
  sql($query,"<br>Error en la insercion de orden de pago $i<br>") or fin_pagina();
  //traemos el id de la orden de pago insertada
  $query="select max(id_pago) as id from ordenes_pagos";
  $res_id=sql($query,"<br>Error en la seleccion del max id en $i<br>") or fin_pagina();
  $id_pago=$res_id->fields['id'];

  //relacionamos la orden de pago con la orden de compra
  $query="insert into pago_orden (id_pago,nro_orden) values($id_pago,$nro_orden)";
  sql($query,"<br>Error en la insercion de orden de pago $i<br>") or fin_pagina();
  $formas->MoveNext();
 }//de for($i=0;$i<$long;$i++)

 $db->CompleteTrans();
}//de function insertar_ordenes_pagos($nro_orden,$cuotas,$dolar=0)


//funcion que me actualiza los montos de los pagos
//y de los dolares (si los montos son en dolares)
//esta hecha para no perder los datos de los montos
function actualizar_pagos_ordenes($id_pagos,$montos,$valor_dolar=0)
{
	global $db;

	$cantidad_pagos=sizeof($id_pagos);
	for ($i=0;$i<$cantidad_pagos;$i++){
	    $id_pago=$id_pagos[$i];
	    $monto=$montos[$i];
	    if ($valor_dolar) $dolar=$valor_dolar[$i];
	                      else $dolar=0;
	    $sql="update ordenes_pagos set monto=$monto, valor_dolar=$dolar where id_pago=$id_pago";
	    sql($sql) or fin_pagina();
	} //del for


}//de function actualizar_pagos_ordenes($id_pagos,$montos,$valor_dolar=0)


/***********************************************************
FUNCION QUE RETORNA LOS DETALLES DE LA ORDEN DE COMPRA
EN UN STRING, Y SE USA PARA AGREGAR EL DETALLE DE LA MISMA
EN LOS MAILS QUE SE ENVIEN

EL PARAMETRO IMPRIMIR TODO TIENE COMO FIN NO MOSTRAR TODO EL
DETALLE DE LA ORDEN DE COMPRA A FIN DE OCULTAR INFORMACION A
PERSONAS FUERA DE LA EMPRESA
************************************************************/
function detalle_orden($nro_orden,$imprimir_todo=1,$ocultar_cliente=1)
{   global $db;
	//obtengo los datos de la orden de compra
	$sql="select orden_de_compra.cliente,orden_de_compra.fecha_entrega,orden_de_compra.lugar_entrega,";
	$sql.=" orden_de_compra.cliente,orden_de_compra.internacional,honorarios_gastos,monto_flete,";
	$sql.=" proveedor.razon_social, plantilla_pagos.descripcion, ";
	$sql.=" moneda.nombre,moneda.simbolo ";
	$sql.=" from orden_de_compra";
	$sql.=" join moneda using(id_moneda) ";
	$sql.=" join proveedor using(id_proveedor) ";
	$sql.=" join plantilla_pagos using(id_plantilla_pagos)
	        left join datos_oc_internacional using (nro_orden)";
	$sql.=" where orden_de_compra.nro_orden=$nro_orden ";
	$resultado=sql($sql,"<br>Error al traer los datos de la OC para el detalle") or fin_pagina();

	if($ocultar_cliente)
	 $cliente=CORADIR;
	else
	 $cliente=$resultado->fields["cliente"];
	$fecha_entrega=fecha($resultado->fields["fecha_entrega"]);
	$lugar_entrega=$resultado->fields["lugar_entrega"];
	$proveedor=$resultado->fields["razon_social"];
	$forma_pago=$resultado->fields["descripcion"];
	$moneda=$resultado->fields["nombre"];
	$moneda_simbolo=$resultado->fields["simbolo"];
	$internacional=$resultado->fields["internacional"];
	$total_flete=$resultado->fields["monto_flete"];
	$total_honorarios=$resultado->fields["honorarios_gastos"];

	//obtengo la descripcipon de las filas de la orden de compra
	$sql="select descripcion_prod,desc_adic,cantidad,precio_unitario";
	if($internacional)
	 $sql.=",proporcional_flete,base_imponible_cif,derechos,iva,ib";
	$sql.=" from fila";
	$sql.=" where nro_orden=$nro_orden";
	$filas_orden_compra=sql($sql,"<br>Error en Consulta de Cantidades de Orden de Compra<br>") or fin_pagina();
	$cantidad_filas=$filas_orden_compra->RecordCount();

	if(!$internacional)
	 $mensaje="Orden de Compra Nº $nro_orden";
	else
	 $mensaje="Orden de Compra Internacional Nº $nro_orden";

	$mensaje.="\nDescripción";
	if ($imprimir_todo==1)
	{
	$mensaje.="\nCliente: $cliente";
	$mensaje.="\nFecha Entrega: $fecha_entrega ";
	$mensaje.="\nLugar Entrega: $lugar_entrega";
	$mensaje.="\nProveedor: $proveedor";
	$mensaje.="\nForma de Pago: $forma_pago";
	$mensaje.="\nMoneda: $moneda";
	}
	$mensaje.="\n";
	$mensaje.="\nProductos:";
	if(!$internacional)
	{
	 $mensaje.="\n---------------------------------------------------";
	 $mensaje.="\nCantidad      Descripción   P. Unitario   P.Total";
	 $mensaje.="\n---------------------------------------------------";
	}
	else
	{
	 $mensaje.="\n----------------------------------------------------------------------------------------------------";
	 $mensaje.="\nCantidad - Descripción - P. Unitario - P.Total - Prop. Flete - Base Imp. CIF - Derechos - IVA - IB";
	 $mensaje.="\n----------------------------------------------------------------------------------------------------";
	}
	$total_general=0;
	$total_iva_ganancias=0;
	$total_ib=0;
	$total_derechos=0;
	for($i=0;$i<$cantidad_filas;$i++)
	{
	    $cantidad=$filas_orden_compra->fields["cantidad"];
	    $descripcion=$filas_orden_compra->fields["descripcion_prod"]." ".$filas_orden_compra->fields["desc_adic"];
	    $precio_unitario=$filas_orden_compra->fields["precio_unitario"];
	    $precio=number_format($precio_unitario,"2",".","");
		$precio_total=number_format(($cantidad*$precio_unitario),"2",".","");
		if($internacional)
		{
		 $prop_flete=number_format($filas_orden_compra->fields["proporcional_flete"],2,'.','');
		 $base_imp_cif=number_format($filas_orden_compra->fields["base_imponible_cif"],2,'.','');
		 $derechos=number_format($filas_orden_compra->fields["derechos"],2,'.','');
		 $iva=number_format($filas_orden_compra->fields["iva"],2,'.','');
		 $ib=number_format($filas_orden_compra->fields["ib"],2,'.','');

		 $total_iva_ganancias+=$filas_orden_compra->fields["iva"];
		 $total_ib+=$filas_orden_compra->fields["ib"];
		 $total_derechos+=$filas_orden_compra->fields["derechos"];
		}

		$total_general+=$precio_total;
	    $mensaje.="\n$cantidad - $descripcion - $moneda_simbolo $precio - $moneda_simbolo $precio_total";
	    if($internacional)
	     $mensaje.=" - $moneda_simbolo $prop_flete - $moneda_simbolo $base_imp_cif - $moneda_simbolo $derechos - $moneda_simbolo $iva - $moneda_simbolo $ib";
	    $filas_orden_compra->MoveNext();

	}//de for($i=0;$i<$cantidad_filas;$i++)
	$mensaje.="\n---------------------------------------------------";
	if(!$internacional)
	 $mensaje.="\nPrecio Total de la Orden: $moneda_simbolo ".number_format($total_general,"2",".","");
	else //si la OC es internacional, agregamos los datos correspondientes
	{$mensaje.="\nTotal FOB: $moneda_simbolo ".number_format($total_general,"2",".","");
	 $mensaje.="\nFlete: $moneda_simbolo ".number_format($total_flete,2,'.','');
	 $mensaje.="\nTotal I.V.A./Ganancias: $moneda_simbolo ".number_format($total_iva_ganancias,2,'.','');
	 $mensaje.="\nTotal I.B.: $moneda_simbolo ".number_format($total_ib,2,'.','');
	 $mensaje.="\nTotal Derechos: $moneda_simbolo ".number_format($total_derechos,2,'.','');
	 $mensaje.="\nHonorarios y Gastos: $moneda_simbolo ".number_format($total_honorarios,2,'.','');
	 $total_global=$total_general+$total_flete+$total_iva_ganancias+$total_ib+$total_derechos+$total_honorarios;
	 $mensaje.="\nTOTAL GLOBAL: $moneda_simbolo ".number_format($total_global,2,'.','');
	}

	return $mensaje;
}//de function detalle_orden($nro_orden,$imprimir_todo=1,$ocultar_cliente=1)



function mandar_mail($direccion,$nro_orden,$monto_total,$monto_acreditado,$monto_nc,$simbolo,$ordenes_atadas,$usuario)
{


	$cantidad_direccion=sizeof($direccion);
	for($i=0;$i<$cantidad_direccion;$i++){
	    if($i>0)
	     $para.=",";
	    $para.=$direccion[$i];
	}
    $cant_ordenes=sizeof($ordenes_atadas);
    if($cant_ordenes<=1)//si es una orden solitaria (sin pago multiple)
    {$mailtext=" El monto elegido para pagar la ORDEN DE COMPRA NRO:$nro_orden no concuerda con el monto verdadero  de la orden de compra.\n ";
     $mailtext.="\n-------------------------------------------------------------------------------------\n";
     $mailtext.="Monto Orden de Compra:          $simbolo ".formato_money($monto_total)."\n";
     if($monto_nc!=0)
      $mailtext.="Monto Notas de Crédito usadas:  $simbolo ".formato_money($monto_nc);
     $mailtext.="Monto que se intenta pagar:     $simbolo ".formato_money($monto_acreditado)."\n";
     $mailtext.="\n";

     $diferencia=$monto_acreditado-($monto_total-$monto_nc);
     $mailtext.="Diferencia:                 $simbolo ".formato_money($diferencia)."\n";
     $mailtext.="\n-------------------------------------------------------------------------------------\n";
     $mailtext.=detalle_orden($nro_orden);
     $asunto="Conflictos con los montos de la orden de compra Nº:$nro_orden";
    }
    else //es una orden en un pago multiple
    {$mailtext=" El monto elegido para el pago multiple de las ORDENES DE COMPRA NRO:";
     $asunto="Conflictos con los montos del pago múltiple de las ordenes de compra Nº:";
     for($i=0;$i<$cant_ordenes;$i++)
     {$mailtext.=" ".$ordenes_atadas[$i];
      $asunto.=" ".$ordenes_atadas[$i];
     }
     $mailtext.="\nno concuerda con el monto verdadero  de la orden de compra.\n";
     $mailtext.="\n-------------------------------------------------------------------------------------\n";
     $mailtext.="Monto Pago Múltiple:        $simbolo ".formato_money($_POST['total_a_pagar'])."\n";
     $mailtext.="Monto que se intenta pagar: $simbolo ".formato_money($monto_acreditado)."\n";
     $mailtext.="\n";
     $diferencia=$monto_acreditado-$_POST['total_a_pagar'];
     $mailtext.="Diferencia:                 $simbolo ".formato_money($diferencia)."\n";

    }
    $mailtext.="\n\nUsuario que generó este e-mail: $usuario ";
    //$mailtext.="<a href='http://www.coradir.com.ar'>CORADIR</a>";
    $mail_header="";
    $mail_header .= "MIME-Version: 1.0";
    $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <>";
    $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
    $mail_header .="\nTo: $para";
    $mail_header .= "\nContent-Type: text/plain";
    $mail_header .= "\nContent-Transfer-Encoding: 8bit";
    $mail_header .= "\n\n" . $mailtext."\n";
	$mail_header .= "\n\n" . firma_coradir()."\n";

    mail("",$asunto,"",$mail_header);

}//fin de la funcion mandar mail


//funcion que devuelve un control para ver
//si las ordenes de pagos estan pagados correctamentes
//un arreglo cuya primer elemento es el monto a pagar
//un segundo elemento es la el monto pagado
//un tercer elemento es la diferencia
//un cuarto elemento es el valor booleano que me indica si es correcto o no el pago
//-total_nc es el total de las notas de credito usadas (si hay)
function control_montos_pagados($nro_orden,$total_pago_multiple,$total_nc=0)
{

	//si la orden es parte de un pago multiple,
	//el monto_total es el del pago multiple,
	//sino es el de la orden de compra por si sola.
	if($total_pago_multiple==0)
	 $monto_total=monto_a_pagar($nro_orden);
	else
	 $monto_total=$total_pago_multiple;
	$monto_total-=$total_nc;
	$monto_pagado=monto_pagado($nro_orden);
	$diferencia=number_format($monto_pagado-$monto_total,"2",".","");
	if (($diferencia<=0.10)&&($diferencia>=0) || ($diferencia>=-0.10)&&($diferencia<=0))
	    {
	    $correcto=1;
	    }
	    else
	    {
	    $correcto=0;
	    }
	$estado=array("monto_total"=>$monto_total,"monto_pagado"=>$monto_pagado,"diferencia"=>$diferencia,"correcto"=>$correcto);
	return $estado;
}//de function control_montos_pagados($nro_orden,$total_pago_multiple,$total_nc=0)

function retornar_moneda($moneda)
{
	global $db;
	$sql="select nombre,id_moneda from moneda where nombre='$moneda'";
	$resultado=sql($sql) or fin_pagina();
	return $resultado->fields["id_moneda"];
}//de function retornar_moneda($moneda)

//esta funcion solo sirve para cuando pagamos
//no se puede usar en otro lado
//y lo que hace es guardar el comentario y el valor del dolar de cada pago
//toma como parametro el simbolo de la moneda

function guardar_montos_dolares()
{
	global $db,$estado,$orden_compra_pagos,$nro_orden;
	global $orden_inf_pago,$comentario_pagos;
	/*if ($_POST['monto_error']=="true") {
                                //si hubo un error con los montos
                                //envio un mail para confirmar esto
                                $monto_total=$_POST["monto_total"];
                                $monto_acreditado=$_POST["monto_acreditado"];

                                $direccion=array();
                                $direccion[0]="corapi@coradir.com.ar";
                                $direccion[1]="noelia@pcpower.com.ar";
                                $direccion[2]="tedeschi@coradir.com.ar";
                                $direccion[3]="juanmanuel@coradir.com.ar";
                                mandar_mail($direccion,$nro_orden,$monto_total,$monto_acreditado,$simbolo,$ordenes_atadas,$usuario);
                                }*/
    //$montos=array($_POST['cantidad_pagos']);
    $montos=array();
    $valores_dolares=array();
    $id_pagos=array();
    switch ($estado){
    case 'p':
    case 'u':
    case 'a':
           for($i=0;$i<$_POST['cantidad_pagos'];$i++){
                 $montos[$i]=$_POST["monto_$i"];
                 $valores_dolares[$i]=$_POST["valor_dolar_$i"];

            }  //del for
            if ($_POST["moneda_dolares"]) {
                                          insertar_ordenes_pagos($nro_orden,$montos,$valores_dolares);
                                          }
                                          else
                                          {
                                          insertar_ordenes_pagos($nro_orden,$montos);
                                          }

               break;
    default:$y=0;
            for($i=0;$i<$_POST['cantidad_pagos'];$i++){
               if (!($orden_compra_pagos->fields['númeroch'] || $orden_compra_pagos->fields['iddébito']||$orden_compra_pagos->fields['id_ingreso_egreso']))
                   {
                    $montos[$y]=$_POST["monto_$i"];
                    $valores_dolares[$y]=$_POST["valor_dolar_$i"];
                    $id_pagos[$y]=$_POST["id_pago_$i"];
                    $y++;
                   }
               $orden_compra_pagos->movenext();
           }  //del for
            if ($_POST["moneda_dolares"]) {
                                         actualizar_pagos_ordenes($id_pagos,$montos,$valores_dolares);
                                         }
                                         else{
                                         actualizar_pagos_ordenes($id_pagos,$montos);
                                         }
            break;
         }//del switch
        //guardo el comentario de la orden de pago
        $comentario_pagos=$_POST["comentario_pagos"];
        $sql="update orden_de_compra set comentario_pagos='$comentario_pagos' where nro_orden=$nro_orden";
        sql($sql) or fin_pagina();
        if ($pagina_viene){
                         $link="ord_compra_listar.php";
                         header("location:$link") or die();
                       }

         //vuelvo a ejecutar la consulta para que muestre correctamente los cambios
        $sql="select * ";
        $sql.=" from  pago_orden  join ordenes_pagos using(id_pago)";
        $sql.=" where pago_orden.nro_orden=$nro_orden";
        $orden_compra_pagos=sql($sql) or fin_pagina();
        $orden_compra_pagos->move(0);
        $sql="select moneda.simbolo,orden_de_compra.habilitar_pago_especial as pago_especial,orden_de_compra.id_proveedor,orden_de_compra.comentario_pagos,";
        $sql.="orden_de_compra.estado,orden_de_compra.valor_dolar,forma_de_pago.dias as dias_pago,";
        $sql.= "plantilla_pagos.descripcion as nombre_pago, tipo_pago.descripcion as nombre_tipo_pago , forma_de_pago.dias ";
        $sql.=" from orden_de_compra join licitaciones.moneda using(id_moneda)";
        $sql.=" left join plantilla_pagos using (id_plantilla_pagos)";
        $sql.=" join pago_plantilla using(id_plantilla_pagos) ";
        $sql.=" join forma_de_pago using (id_forma) ";
        $sql.=" join tipo_pago using(id_tipo_pago) ";
        $sql.=" where orden_de_compra.nro_orden=$nro_orden ";
        $orden_inf_pago=sql($sql) or fin_pagina();
        $comentario_pagos=$orden_inf_pago->fields['comentario_pagos'];





} //de la funcion guardar_montos_dolares



function nombre_pila($nombre)
{
$buffer=array();
$buffer=split(" ",$nombre);
return $buffer[0];
} //de function nombre_pila($nombre)


/*********************************************************************
FUNCION que genera tabla con las ordenes atadas por pago multiple
-el primer parametro es un arreglo con las ordenes del pago multiple.
-el segundo es el simbolo de la moneda, que se mostrara en la pantalla
-el tercer parametro especifica el ancho de la tabla.
-el cuarto parametro es un boooleano que indica si se debe mostrar
 el total a pagar (indicado con 1), en la tabla que se va a generar.
-retorna el total a pagar por el pago multiple
**********************************************************************/
function ordenes_pago_multiple($nro_orden,$simbolo_moneda,$width,$mostrar_total)
{global $bgcolor3;

 $colspan=2;
 $colspan2=1;

?>

 <table border=1 width="<?=$width?>" cellspacing=0 cellpadding=5 align="center">
  <tr>
   <td style=<?="border:$bgcolor3;"?>  align="center" id=mo colspan=<?=$colspan?>>
     <b>Ordenes de Compra que se incluyen en el pago</b>
   </td>
 </tr>
 <?//generamos la tabla con detalle de las ordenes de compra
  $tam=sizeof($nro_orden);
  $total_a_pagar=0;
  for($i=0;$i<$tam;$i++)
  {?>
   <tr>
    <td colspan=<?=$colspan2?>>
      <b>Nro Orden: </b><?=$nro_orden[$i]?>
    </td>
    <td colspan=<?=$colspan2?>>
      <b>Monto: </b><?$m_orden=monto_a_pagar($nro_orden[$i]);
                      $total_a_pagar+=$m_orden;
                      echo "$simbolo_moneda ";
                      echo formato_money($m_orden);
                    ?>
    </td>
   </tr>
 <?
 }//del for
 if($mostrar_total)
 {?>
  <tr>
    <td colspan=<?=$colspan?> align="center">
     <b>Total a Pagar <?echo "<font color=red size=2>";
                         echo "$simbolo_moneda ";
                         echo formato_money($total_a_pagar);
                       ?>
   </td>
  </tr>
  </table>
 <?
 }//de if($mostrar_total)
 return $total_a_pagar;
}//de  function ordenes_pago_multiple($nro_orden)


/*********************************************************************
FUNCION que devuelve un arreglo con las ordenes asociadas
(en un pago multiple), a la orden pasada como parametro (en el arreglo
que se devuelve, se incluye la orden pasada como parametro).
Si no encuentra ninguna, devuelve la OC pasada como parámetro,
en la primera posicion del arreglo
**********************************************************************/
function PM_ordenes($nro_orden)
{global $db;

  //seleccionamos las ordenes que estan relacionadas con el mismo pago
  //que la orden pasada como paramtero

  $query="select distinct nro_orden from pago_orden where id_pago in(select id_pago from pago_orden join orden_de_compra using(nro_orden) where nro_orden=$nro_orden)";
  $ordenes=sql($query,"<br>Error al traer las OC relacionadas (PM_Ordenes)<br>") or fin_pagina();
  $ordenes_array=array();
  $i=0;
  while(!$ordenes->EOF)
  {$ordenes_array[$i]=$ordenes->fields['nro_orden'];
   $i++;
   $ordenes->MoveNext();
  }
  return $ordenes_array;
}//de function PM_ordenes($nro_orden)


/***********************************************************************
FUNCION que guarda la forma de pago de un pago multiple tomando como
parametro el arreglo de ordenes a las que se le guarda el pago multiple.
************************************************************************/
function PM_guardar_forma_pago($cantidad_pagos,$nro_orden,$cambios)
{global $db;
  $db->StartTrans();

   //insertamos la nueva forma de pago de las ordenes
  $msg=guardar_forma_pago($cantidad_pagos,$nro_orden[0],$cambios);
  if($msg!="")
   return $msg;
  //seleccionamos el id de la forma de pago de la primer orden
  $query="select id_plantilla_pagos from orden_de_compra where nro_orden=".$nro_orden[0];
  $max=sql($query) or fin_pagina();
  $id_plantilla=$max->fields['id_plantilla_pagos'];

  $cant_ordenes=sizeof($nro_orden);
  for($i=1;$i<$cant_ordenes;$i++)
  { //actualizamos la plantilla de la orden de compra
    $query="update orden_de_compra set id_plantilla_pagos=$id_plantilla where nro_orden=$nro_orden[$i]";
    sql($query) or fin_pagina();
  }

 $db->CompleteTrans();
 return "";
}//de function PM_guardar_forma_pago($cantidad_pagos,$nro_orden,$cambios)


/*********************************************************************
FUNCION que guarda las ordenes pagos y la relacion respectiva con las
ordenes de compra de un pago multiple
**********************************************************************/
function PM_insertar_ordenes_pagos($nro_orden,$cuotas,$dolar=0)
{global $db;

  $db->StartTrans();

  //borramos las entrada de pago_orden viejas si es que hay,
  //para todas las ordenes
  $cant_ordenes=sizeof($nro_orden);
  for($i=0;$i<$cant_ordenes;$i++)
  {$query="delete from pago_orden where nro_orden=$nro_orden[$i]";
   $a=sql($query) or fin_pagina();
  }
  //seleccionamos las ordenes de pagos vieja si es que hay y las borramos
  $query="select id_pago from ordenes_pagos join pago_orden using(id_pago) join orden_de_compra using (nro_orden) where nro_orden=$nro_orden[0]";
  $res_ord_pago=sql($query) or fin_pagina();
  while(!$res_ord_pago->EOF)
  {$query="delete from ordenes_pagos where id_pago=".$res_ord_pago->fields['id_pago'];
  sql($query) or fin_pagina();
   $res_ord_pago->MoveNext();
  }

  //insertamos la nueva entrada en ordenes_pagos para la primer orden
  insertar_ordenes_pagos($nro_orden[0],$cuotas,$dolar);
  //traemos el id_pago de la primer orden
  $query="select id_pago from pago_orden where nro_orden=".$nro_orden[0];
  $pagos=sql($query) or fin_pagina();
  //para insertar las entradas correspondientes (en pago_orden) para las demas ordenes

  for($i=1;$i<$cant_ordenes;$i++)
  {
    //borramos las entrada de pago_orden viejas si es que hay
    $query="delete from pago_orden where nro_orden=".$nro_orden[$i];
    $a=sql($query) or fin_pagina();

    $pagos->Move(0);
    while(!$pagos->EOF)
    { //relacionamos la orden de pago con la orden de compra
      $query="insert into pago_orden (id_pago,nro_orden) values(".$pagos->fields['id_pago'].",".$nro_orden[$i].")";
      sql($query) or fin_pagina();
      $pagos->MoveNext();
    }
  }//del for

  $db->CompleteTrans();
}//de function PM_insertar_ordenes_pagos($nro_orden,$cuotas,$dolar=0)

/**********************************************************************
FUNCION que devuelve si un pago de una plantilla fue realizado o no.
Toma los siguientes parametros:
-como 1er parametro el nro de orden al que pertenece el pago.
-como 2do parametro el id del pago.

Y devuelve verdadero (un 1) en caso de que el pago haya sido realizado
o falso (0) en otro caso.
***********************************************************************/

function pago_realizado($nro_orden,$id_pago)
{global $db;
 $query="select iddébito,id_ingreso_egreso,idbanco,númeroch from orden_de_compra join pago_orden using(nro_orden) join ordenes_pagos using(id_pago) where nro_orden=$nro_orden and id_pago=$id_pago";
 $result=sql($query) or fin_pagina();

 if($result->fields['iddébito']!=null || $result->fields['id_ingreso_egreso']!=null || ($result->fields['númeroch']!=null && $result->fields['idbanco']!=null))
  return 1;
 else
  return 0;

}//de function pago_realizado($nro_orden,$id_pago)


/***********************************************************************
FUNCION que actualiza formas de pago que ya tienen algunos pagos hechos
y otros no.
************************************************************************/
function actualizar_forma_pago($nro_orden,$id_plantilla)
{global $db;

 $db->StartTrans();
 //traemos los pagos de la plantilla de pagos
 $query="select id_pago,id_forma,mostrar from plantilla_pagos join pago_plantilla using(id_plantilla_pagos) join forma_de_pago using (id_forma) join ordenes_pagos using(id_forma) where id_plantilla_pagos=$id_plantilla";
 $pagos=sql($query) or fin_pagina();

 if($pagos->fields['mostrar']==0)
 {while(!$pagos->EOF)
  {//si el pago no se ha realizado aun, se puede cambiar
   if(!pago_realizado($nro_orden[0],$pagos->fields['id_pago']))
   {//lo borramos de la BD (de pago_orden,
    //de ordenes_pagos, de pago_plantilla y de la forma_de_pago)
    $query="delete from pago_orden where id_pago=".$pagos->fields['id_pago'];
    sql($query) or fin_pagina();

    $query="delete from ordenes_pagos where id_pago=".$pagos->fields['id_pago'];
    sql($query) or fin_pagina();

    $query="delete from pago_plantilla where id_forma=".$pagos->fields['id_forma'];
    sql($query) or fin_pagina();

    $query="delete from forma_de_pago where id_forma=".$pagos->fields['id_forma'];
    sql($query) or fin_pagina();
   }
  $pagos->MoveNext();
  }
  //a continuacion se agregan los nuevos pagos a la plantilla de la orden
  //y se registran los pagos en ordenes_pagos y pago_orden
  $cantidad_pagos=$_POST['select_cantidad_pagos'];

    for($i=0;$i<$cantidad_pagos;$i++)
    {if($_POST["state_pago_".$i]!="" && $_POST["state_pago_".$i]=="no_pagado")
     {
      $dias=$_POST["cantidad_dias_".$i];
      $tipo_pago=$_POST["select_tipo_pago_".$i];
      $query="insert into forma_de_pago (dias,id_tipo_pago) values($dias,$tipo_pago)";
      sql($query) or fin_pagina();
      $query="select max(id_forma) as id from forma_de_pago";
      $res_forma=sql($query) or fin_pagina();
      $id_forma=$res_forma->fields['id'];

      //insertamos la relacion de la planilla de pago con las formas de pago
      $query="insert into pago_plantilla (id_forma,id_plantilla_pagos) values($id_forma,$id_plantilla)";
      sql($query) or fin_pagina();

      //registramos el pago (no pagado) de la orden, y la relacionamos con la misma
      //insertamos la orden de pago para esta cuota
      $cuota=$_POST["monto_".$i];
      $valor=$_POST["valor_dolar_".$i];
      if($valor=="")
       $valor=1;
      $query="insert into ordenes_pagos (monto,valor_dolar,id_forma) values($cuota,$valor,$id_forma)";
      sql($query) or fin_pagina();
      //traemos el id de la orden de pago insertada
      $query="select max(id_pago) as id from ordenes_pagos";
      $res_id=sql($query) or fin_pagina();


      //relacionamos la orden de pago con la/s orden/es de compra
      $cant_ordenes=sizeof($nro_orden);
      for($j=0;$j<$cant_ordenes;$j++)
      {$query="insert into pago_orden (id_pago,nro_orden) values(".$res_id->fields['id'].",$nro_orden[$j])";
       sql($query) or fin_pagina();
      }

      //actualizamos el nombre de la plantilla de pagos por las dudas
      //que loe hayan cambiado
      $query="update plantilla_pagos set descripcion='".$_POST['titulo_pago']."' where id_plantilla_pagos=$id_plantilla";
      sql($query) or fin_pagina();

     }//de if($_POST["state_pago_".$i]!="" && $_POST["state_pago_".$i]=="no_pagado")
    }//de for($i=0;$i<$cantidad_pagos;$i++)
 }//de if($pagos->fields['mostrar']==0)
 else
  die("No se puede cambiar una forma de pago que se usa como plantilla de pago por default.");

 $db->CompleteTrans();
}//de function actualizar_forma_pago($nro_orden,$id_plantilla)

/******************************************************************
Funcion que liga las notas de credito seleccionadas con la/las
orden/es de compra y desliga aquellas que se han desseleccioando

Los parametros son:
-$nro_orden tiene todas las ordenes a las que se ligaran las notas
 de credito
-$notas_c tiene todas las notas de credito seleccionadas
*******************************************************************/
function admin_notas_c($nro_orden,$and_nc,$armando)
{global $db,$_ses_user;
 $db->StartTrans();
 $cant_ordenes=sizeof($nro_orden);

 //Se traen las mismas notas de creditos que se generaron en la pagina ord_pagos
$and_nc="";
//si se esta armando el pago multiple, traemos las notas de credito que estan
//relacionadas con cada orden que participan en el pago

if($armando=="si")
{for($h=0;$h<$cant_ordenes;$h++)
 { if($h!=0)
    $and_nc.=" or";
   $and_nc.=" nro_orden=".$nro_orden[$h];
 }
}
//sino traemos las que estan relacionadas con la primera de ellas
//(ya que son las mismas que estan relacioandas con las otras
//ordenes que participan en el pago mutiple)
//Y en el caso de que no haya pago multiple tambien se aplica este caso
else
{$and_nc=" nro_orden=".$nro_orden[0];
}
//si el estado de la orden es totalmente pagada, solo se muestran las notas de credito
//que se usaron para pagar esa orden
if($estado=='g')
{$and_where="and (estado=2 and ($and_nc))";
}
//sino, se muestran aquellas disponibles + las que ya estan relacionadas con
//las ordenes de compra
else
{$and_where="and ((estado=1 and ($and_nc)) or estado=0)";
}

$query="select id_nota_credito,nota_credito.monto,nota_credito.estado,n_credito_orden.nro_orden,n_credito_orden.valor_dolar,nota_credito.observaciones,id_moneda,simbolo from general.nota_credito join licitaciones.moneda using (id_moneda) left join compras.n_credito_orden using(id_nota_credito) where id_proveedor=".$_POST['id_proveedor']." $and_where";
$notas_credito=sql($query) or fin_pagina();


  //borramos todas las entradas de n_credito_orden que esten relacionadas con ordenes de compra
  while(!$notas_credito->EOF)
  {
     $query="delete from n_credito_orden where id_nota_credito=".$notas_credito->fields['id_nota_credito'];
     sql($query) or fin_pagina();

     //se cambia el estado de la nota de credito otra vez a pendiente
     $query="update nota_credito set estado=0 where id_nota_credito=".$notas_credito->fields['id_nota_credito'];
     sql($query) or fin_pagina();
     $notas_credito->MoveNext();
  }//del while(!$notas_credito->EOF)

  //insertamos las nc seleccionadas, nuevamente en la tabla correspondiente
  //por cada orden de compra que esté en el pago
  for($j=0;$j<$cant_ordenes;$j++)
  {$notas_credito->Move(0);
   while(!$notas_credito->EOF)
   {if($_POST['nota_'.$notas_credito->fields['id_nota_credito']]==1)
    {if($_POST["valor_dolar_nota_".$notas_credito->fields['id_nota_credito']]=="No se aplica" || $_POST["valor_dolar_nota_".$notas_credito->fields['id_nota_credito']]=="")
      $dolar="null";
     else
      $dolar=$_POST["valor_dolar_nota_".$notas_credito->fields['id_nota_credito']];

     $fecha_hoy=date("Y-m-d H:i:s",mktime());
     $query="insert into n_credito_orden (id_nota_credito,nro_orden,valor_dolar,usuario,fecha) values(".$notas_credito->fields['id_nota_credito'].",".$nro_orden[$j].",".$dolar.",'".$_ses_user['name']."','$fecha_hoy')";
     sql($query) or fin_pagina();
    }
    $notas_credito->MoveNext();
   }//de  while(!$notas_credito->EOF)
  }//de for($j=0;$j<$cant_ordenes;$j++)

  //cambiamos el estado de las notas de credito que esten seleccionadas
  //a reservadas
  $notas_credito->Move(0);
  while(!$notas_credito->EOF)
  {if($_POST['nota_'.$notas_credito->fields['id_nota_credito']]==1)
   { //se cambia el estado de la nota de credito otra vez a pendiente
     $query="update nota_credito set estado=1 where id_nota_credito=".$notas_credito->fields['id_nota_credito'];
     sql($query) or fin_pagina();
   }
   $notas_credito->MoveNext();
  }//de while(!$notas_credito->EOF)

 $db->CompleteTrans();
}//de 	function admin_notas_c($nro_orden,$and_nc)


/******************************************************************************
Funcion que genera una tabla con el detalle de las notas de credito utilizadas
para pagar la orden nro $nro_orden, y devuelve el monto total pagado con las
notas de credito, en la moneda nativa de la orden ($simbolo).
*******************************************************************************/
function detalle_nc($nro_orden,$simbolo)
{global $db;
//traemos los datos de las notas de credito asociadas (si es que hay)
     $query="select id_nota_credito,nota_credito.monto,nota_credito.observaciones,oc.valor_dolar,moneda.id_moneda,moneda.simbolo from (select * from n_credito_orden where nro_orden=$nro_orden) as oc join nota_credito using(id_nota_credito) join moneda using(id_moneda)";
     $notas_credito=sql($query) or fin_pagina();
     if($notas_credito->RecordCount())
     {$ftotal_nc=0;
      ?>
      <table align="center" width="100%" border='0' cellspacing='1'  bordercolor='#000000'>
      <tr align="center" bgcolor="#800000">
       <td colspan="4">
        <font color="#FFFFFF"><b>Notas de Crédito uitilizadas para el pago</b></font>
       </td>
      </tr>
       <tr id=mo>
        <td width="10%">
         Nro
        </td>
        <td width="20%">
         Monto
        </td>
        <td width="20%">
         Valor Dolar
        </td>
        <td  width="50%">
         Observaciones
        </td>
       </tr>

      <?
      while(!$notas_credito->EOF)
      {?>
       <tr id=ma>
        <td align="center">
         <?=$notas_credito->fields['id_nota_credito']?>
        </td>
        <td align="center">
         <?=$notas_credito->fields['simbolo']?> -<?=$notas_credito->fields['monto']?>
        </td>
      <?
       //mostramos el valor dolar si viene cargado
       $valor_dolar_nc=($notas_credito->fields['valor_dolar']!="")?formato_money($notas_credito->fields['valor_dolar']):"No se aplica";
      ?>
        <td align="center">
         <?=$valor_dolar_nc?>
        </td>
        <td align="center">
         <?=$notas_credito->fields['observaciones']?>
        </td>
       </tr>
      <?
       $fmonto=$notas_credito->fields['monto'];
       //si la orden es en pesos y la nota de credito en dolares
       //se multiplica el monto por el valor dolar
       if($simbolo=="$" && ($notas_credito->fields['simbolo']=="U\$S"||$notas_credito->fields['simbolo']=="u\$s"))
        $fmonto=$notas_credito->fields['monto']*$notas_credito->fields['valor_dolar'];
       //si la orden es en dolares y la nota de credito en pesos
       //se divide el monto por el valor dolar
       elseif(($simbolo=="U\$S"||$simbolo=="u\$s") && $notas_credito->fields['simbolo']=="\$")
        $fmonto=$notas_credito->fields['monto']/$notas_credito->fields['valor_dolar'];
       $ftotal_nc+=$fmonto;
       $notas_credito->MoveNext();
      }?>
       <tr bgcolor="#800000">
        <td colspan="4">
         <font color="#FFFFFF"><b>Monto Total de Notas de Crédito: <?=$simbolo?> -<?=formato_money($ftotal_nc)?></b></font>
        </td>
       </tr>
      </table>
     <?
     }//del if($notas_credito->RecordCount())
     return $ftotal_nc;
}//de function detalle_nc($nro_orden)


/**************************************************************************************
Funcion que se encarga de generar automaticamente un reclamo de partes para
la orden de compra ($nro_orden), el cual luego se decidirá si se han recibido
las partes correspondientes, o se genero una nota de credito a favor de Coradir.
Si $id_caso == -1 el raclamo es para una orden de compra asociada a orden de produccion
****************************************************************************************/
function autogenerar_reclamo_parte($nro_orden,$id_proveedor,$id_caso,$descripcion)
{global $db,$_ses_user;

 $db->StartTrans();
 if ($id_caso != -1) {
  $query="select idcaso from casos_cdr where nrocaso=$id_caso";
  $r=sql($query) or fin_pagina();
 }
  //seleccionamos el id de reclamo de partes, para insertarlo
  $query="select nextval('reclamo_partes_id_reclamo_partes_seq') as id_reclamo_partes";
  $id_r=sql($query) or fin_pagina();
  $id_reclamo_partes=$id_r->fields['id_reclamo_partes'];

  if ($id_caso !=-1) $caso=$r->fields['idcaso'];
  else $caso="null";

  $query="insert into reclamo_partes(id_reclamo_partes,nro_orden,id_proveedor,idcaso,descripcion,estado)
         values($id_reclamo_partes,$nro_orden,$id_proveedor,".$caso.",'$descripcion',0) ";
  sql($query) or fin_pagina();

  //agregamos los productos de la orden de compra en las partes del reclamo de partes
  $items_rec=get_items($nro_orden);
  for($i=0;$i<$items_rec['cantidad'];$i++)
  {$id_producto=$items_rec[$i]["id_producto"];
   $descripcion=$items_rec[$i]["descripcion_prod"]." ".$items_rec[$i]["desc_adic"];
   $cantidad=$items_rec[$i]["cantidad"];
   $query="insert into partes(id_reclamo_partes,id_producto,descripcion,cantidad)
           values($id_reclamo_partes,$id_producto,'$descripcion',$cantidad)";
   sql($query) or fin_pagina();
  }

  $query="update orden_de_compra set reclamo_activado=2 where nro_orden=$nro_orden";
  sql($query) or fin_pagina();

  //generacion del log de reclamo de partes
  $fecha_hoy=date("Y-m-d H:i:s",mktime());
  $usuario=$_ses_user['name'];
  $tipo="creación por OC Nº $nro_orden";
  //agregamos el log de modificacion del reclamo de partes
  $query="insert into log_reclamo_partes(fecha,usuario,tipo,id_reclamo_partes)
  	        values('$fecha_hoy','$usuario','$tipo',$id_reclamo_partes)";
  sql($query) or fin_pagina();

  $db->CompleteTrans();

}//de function autogenerar_reclamo_parte($nro_orden,$id_proveedor,$id_caso,$descripcion)

/****************************************************************************
Funcion q genera un remito interno desde la orden de compra, recibe como
parametro el nro de orden para recuperar los productos
****************************************************************************/
function autogenerar_remito_interno($nro_orden)
{
 global $db, $_ses_user;
 $codigos_b=Array();
 $db->StartTrans();
 //recupero la info de la orden de compra: datos del cliente y
 //con la funcion get_items recupero los productos
 $q="select cliente, fecha_entrega, lugar_entrega, id_fila, descripcion_prod,
     id_producto, fila.desc_adic
     from compras.orden_de_compra
     left join compras.fila using (nro_orden)
     where nro_orden=$nro_orden";
 $rq=sql($q, "Error al traer los datos de la Orden de Compra") or fin_pagina();

 $nbre=$rq->fields['cliente'];
 $dir=$rq->fields['lugar_entrega'];
 $fecha_hoy=date("Y-m-d H:i:s",mktime());
 $fecha=$rq->fields['fecha_entrega'];

 //recupero el id que se le asignara al remito
 $q="select nextval('remito_interno_id_remito_seq') as id_remito";
 $id_remito=sql($q) or fin_pagina();
 $id_remito=$id_remito->fields['id_remito'];
 //echo $id_remito;

 // x ahora los remitos se generan con estado pendiente, pero los
 // remitos internos en el listado pueden ser modificados (agregar/editar items),
 // para remitos desde ordenes de compra no deben modificarse, xq se generan con las entregas
 $campos="id_remito, fecha_remito, cliente, direccion, estado, nro_orden ";
 $valores="$id_remito, '$fecha_hoy', '$nbre', '$dir', 'p', $nro_orden";

 $q="insert into remito_interno ($campos) values ($valores)";

       if (sql($q, "Error cuando inserta un remito interno") or fin_pagina()){
			$usuario=$_ses_user['name'];
			$q2="insert into log_remito_interno (id_remito,tipo_log,usuario,fecha) values ($id_remito,'creacion','$usuario','$fecha_hoy')";
			sql($q2, "Error cuando inserta un log remito interno") or fin_pagina();
			}
	   else {
			$msg="NO SE PUDO GUARDAR EL REMITO, YA EXISTE EL NUMERO \"$nro_remito\" ";
            //die ("aca entro ");
			header("location: ".encode_link("ord_compra_fin.php",array("msg"=>$msg)));
			}

 $filas_cambios_prod=filas_con_cambios_prod($nro_orden);
 $items_remito_interno=get_items($nro_orden);
 for($i=0;$i<$items_remito_interno['cantidad'];$i++) {
 	$cant=0;
 	$cant_insert_rem=0;
 	$cant_scb=0;
 	$cant_comprados=0;
 	$cant_rec=0;
 	$cant_codigos_b=0;
 	$id_fila=$items_remito_interno[$i]["id_fila"];

 	if($filas_cambios_prod[$id_fila]["id_producto"]!="")
 	{
 	 $id_producto=$filas_cambios_prod[$id_fila]["id_producto"];
 	 $descripcion=$filas_cambios_prod[$id_fila]["desc_gral"];
 	}
 	else
 	{
 	 $id_producto=$items_remito_interno[$i]["id_producto"];
 	 $descripcion=$items_remito_interno[$i]["descripcion_prod"]." ".$items_remito_interno[$i]["desc_adic"];
 	}

 	// para recuperar cuantas entregas se hicieron sin cb
    // consultar en la tabla fila cant_scb_sr
 	$q_cant="select id_producto, fila.cantidad as cant_prod, recibidos.cantidad as cant_rec, cant_scb_sr
             from compras.fila
             left join compras.recibidos using (id_fila)
             where nro_orden=$nro_orden and id_fila=$id_fila";
 	$res_cant=sql($q_cant) or fin_pagina();
 	// var donde voy a guardar cantidades para controlar cuantas
    // entergas se hacen con cb y sin cb
    $cant_comprados=$res_cant->fields['cant_prod'];
    $cant_scb=$res_cant->fields['cant_scb_sr'];

    if ($res_cant->fields['cant_rec']=='')
        $cant_rec=0;
    else
        $cant_rec=$res_cant->fields['cant_rec'];

 	$q_cb="select distinct codigo_barra,tiene_remito
 	       from general.codigos_barra
 	       join general.log_codigos_barra using (codigo_barra)
           where id_producto=$id_producto and tipo ilike '%entregado%'
 	       and nro_orden=$nro_orden and tiene_remito=0";
 	$res_cb=sql($q_cb) or fin_pagina();
 	$cant_codigos_b=$res_cb->recordCount();
 	//$tiene_remito=$res_cb->fileds['tiene_remito'];

 	// en este arreglo guardo todos los cb para el producto para despues
 	// actualizar el campo tiene_remito
 	for($j=0;$j<$cant_codigos_b;$j++){
 		$codigos_b[$j]['cb']=$res_cb->fields['codigo_barra'];
 		$codigos_b[$j]['tr']=$res_cb->fields['tiene_remito'];
 		if ($codigos_b[$j]['tr']==0) $cant_insert_rem++;
 		$res_cb->MoveNext();
 		}

    $descripcion.= "\nCódigos de Barra: ";


    //if ($cant_rec>=$cant_codigos_b)
    //   $cant_scb=$cant_rec-$cant_codigos_b;

    $cant=$cant_insert_rem+$cant_scb;

    for ($a=0;$a<$cant_scb;$a++)
    	$descripcion.="Entregado sin código de Barra - ";

    $res_cb->Move(0);
    while (!$res_cb->EOF){
 	      $descripcion.=$res_cb->fields['codigo_barra']." - ";
          $res_cb->MoveNext();
         }

       if ($cant_rec!=0 && $cant!=0) {

       $cantidad=$items_remito_interno[$i]["cantidad"];
       $q="select nextval('items_remito_interno_id_item_seq') as id_item";
       $id_item=sql($query) or fin_pagina();
       $id_item=$id_item->fields['id_item'];

       $query="insert into items_remito_interno(id_item, cant_prod, descripcion, id_remito, id_producto)
               values($id_item, $cant,'$descripcion', $id_remito, $id_producto)";
       sql($query, "Error al insertar los items del remito interno") or fin_pagina();
       for($k=0;$k<$cant_codigos_b;$k++){
       $query="update log_codigos_barra set tiene_remito=1
               where nro_orden=$nro_orden and codigo_barra='".$codigos_b[$k]['cb']."'";
       //$query.=" and tiene_remito=0";
       sql($query, "Error al actualizar los logs del remito interno") or fin_pagina();
         } // fin for($k=0;$k<$cant_codigos;$k++)
       $scb_sr="update compras.fila set cant_scb_sr=0 where id_fila=$id_fila";
       sql($scb_sr, "Error al actualizar cantidad de entregas sin cb y sin remitos internos") or fin_pagina();
       } // fin fi de cant_rec
    } // fin for($i=0;$i<$items_remito_interno['cantidad'];$i++)

 $db->CompleteTrans();
 $nro_remito_interno=$id_remito;
 return $nro_remito_interno;
}//de function autogenerar_remito_interno($nro_orden)

/****************************************************************************
Funcion que autogenera una nota de credito a partir de los datos que se le
pasan como parametros.

-Dado el nro de orden ($nro_orden), obtiene los datos necesarios para generar
 la nota de credito, y genera el resto, para poder llenar la tabla.
 Estos son: id_moneda (será la misma que la orden de compra),
            monto (será el total de la orden de compra),
            El resto de los datos necesarios son generados por la funcion
            misma, salvo el proveedor que lo toma como parametro.
-La función devuelve el nro de nota de credito generada,
*****************************************************************************/
function autogenerar_nota_credito($reclamo,$nro_orden,$proveedor)
{global $db;
 $db->StartTrans();
 $descripcion="Nota de crédito generada a partir del reclamo de partes Nº $reclamo";
 //traemos la moneda de la orden de compra
 $query="select id_moneda from orden_de_compra where nro_orden=$nro_orden";
 $moneda=sql($query) or fin_pagina();
 $id_moneda=$moneda->fields['id_moneda'];
 //obtenemos el total de la orden de compra
 $monto=monto_a_pagar($nro_orden);

 //generamos la nota de credito
 $query="select nextval('nota_credito_id_nota_credito_seq') as id_nota_credito";
 $id_nc=sql($query) or fin_pagina();
 $query="insert into nota_credito(id_nota_credito,id_proveedor,id_moneda,descripcion,monto,observaciones,estado)
         values(".$id_nc->fields['id_nota_credito'].",$proveedor,$id_moneda,'$descripcion',$monto,'$descripcion',0)";
 sql($query) or fin_pagina();

 $db->CompleteTrans();
 return $id_nc->fields['id_nota_credito'];
}//de function autogenerar_nota_credito($reclamo,$nro_orden,$proveedor)

/***********************************************************************
Esta funcion pasa todas las notas de credito relacionadas con la orden
de compra ($nro_orden) a estado utilizadas.
ATENCION: SOLO SE DEBE USAR ESTA FUNCION CUANDO LAS ORDEN DE COMPRA
PASA A ESTADO "TOTALMENTE PAGADAS"
************************************************************************/
function pasa_nc_utilizada($nro_orden)
{global $db;
 $db->StartTrans();

 $query="select id_nota_credito from n_credito_orden where nro_orden=$nro_orden";
 $result=sql($query) or fin_pagina();
 while(!$result->EOF)
 {$query="update nota_credito set estado=2 where id_nota_credito=".$result->fields['id_nota_credito'];
  sql($query) or fin_pagina();
  $result->MoveNext();
 }
 $db->CompleteTrans();
}//de function pasa_nc_utilizada($nro_orden)


/************************************************
Autor: MAC
Funcion que agrega al stock de RMA los productos
de la orden de compra, bajo el proveedor
seleccionado.
Esta funcion se invoca solo si la orden de compra
esta asociada a un CAS o a RMA de Produccion
*************************************************/
function agregar_RMA($nro_orden,$nrocaso,$ordprod)
{global $db,$_ses_user;

 $db->StartTrans();
 //agregamos cada producto por separado al stock RMA con el proveedor
 //seleccionado en la OC (el proveedor que viene en proveedor_reclamo)
 //y luego completamos la informacion correspondiente en la tabla info_rma
 $items_rma=get_items();

 //seleccionamos el id del deposito RMA
 $query="select id_deposito from depositos where nombre='RMA'";
 $dep_rma=sql($query) or fin_pagina();
 $id_dep=$dep_rma->fields['id_deposito'];
 $id_prov=$_POST['proveedor_reclamo'];
     $obs="Modificación automática para Orden de Compra Nº $nro_orden";
      for($i=0;$i<$items_rma['cantidad'];$i++)
      {
       $id_prod=$items_rma[$i]["id_producto"];
       $cantidad=$items_rma[$i]["cantidad"];
       $precio=$items_rma[$i]["precio_unitario"];
       //primero busco si tiene precio ese producto
       //con ese proveedor
	   $sql=" select id_producto from precios ";
	   $sql.="where id_producto=$id_prod ";
	   $sql.="and id_proveedor=$id_prov ";
	   $result=sql($sql) or fin_pagina();
	   $cant_precios=$result->recordcount();
	   if($cant_precios==0)
	   {
	    insertar_precio($id_prod,$id_prov,$precio);
	   }

       if($cantidad!="" && $cantidad>0)
       {
       	//revisamos si esta la entrada para ese producto, proveedor, deposito, en el stock.
       	$query="select count(*)as cuenta from stock where id_producto=$id_prod and id_deposito=$id_dep and id_proveedor=$id_prov";
       	$esta=sql($query) or fin_pagina();

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

	     sql($sql) or fin_pagina();

	     //registramos en el historial el incremento de stock
         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
         $id_control_stock=sql($query) or fin_pagina();

         $fecha_modif=date("Y-m-d H:i:s",mktime());
         $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
               values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','Orden de Compra Nº $nro_orden','Incremento generado por la Orden de Compra Nº $nro_orden','is')";
         sql($query) or fin_pagina();

         //Insertamos en la tabla info_rma los datos correspondientes
	     //a la orden de compra y demas datos necesarios
	      $query="select nextval('info_rma_id_info_rma_seq')as id_info_rma";
	      $id_info=sql($query) or fin_pagina();
	      $id_info_rma=$id_info->fields['id_info_rma'];

	      /////////////////////////////////Broggi//////////////////////////////////////////////////////////////////////
	      //////////////////////TRAIGO EL IDE DE LA UBICACION EN TRANSITO//////////////////////////////////////////////
	      $sql_tran="select id_ubicacion from stock.ubicacion where lugar='Tránsito'";
	      $resul_tran=sql($sql_tran,"error al traer el id_ubicacion") or fin_pagina();
	      $id_ubicacion=$resul_tran->fields['id_ubicacion'];
	      /////////////////////////////////////////////////////////////////////////////////////////////////////////////

	      $query="insert into info_rma(id_info_rma,id_deposito,id_producto,id_proveedor,id_ubicacion,nro_ordenc,nro_ordenp,nrocaso,cantidad)
	             values ($id_info_rma,$id_dep,$id_prod,$id_prov,$id_ubicacion,$nro_orden,$ordprod,$nrocaso,$cantidad)";
          sql($query) or fin_pagina();

	     $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc,id_info_rma)
	           values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cantidad,$id_info_rma)";
	     sql($query) or fin_pagina();

	     $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	           values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Incremento por Orden de Compra Nº $nro_orden')";
	     sql($query) or fin_pagina();



       }//de if($cantidad!="" && $cantidad>0)

      }//de for($i=0;$i<$items_rma['cantidad'];$i++)

 $db->CompleteTrans();
}//de function agregar_RMA($nro_orden,$nrocaso,$ordprod)


/************************************************
Funcion que descuenta del stock correspondiente
los productos de la orden de compra
-El parametro $id_dep es el id de deposito de los
 productos a descontar

(SOLO SE DEBE INVOCAR SI EL PROVEEDOR DE LA ORDEN
 ES UNO DE LOS STOCKS DE LA EMPRESA)
*************************************************/
function descontar_stock($id_dep)
{global $db,$msg, $nro_orden,$_ses_user;
//descontamos del stock seleccionado los items de la orden de compra
     $items=get_items();
     $db->StartTrans();
     for($r=0;$r<$items['cantidad'];$r++)
     {

      $obs="Productos reservados por OC Nº $nro_orden";
      $id_prod=$items[$r]['id_producto'];

      //si la fila fue creada, no hay que descontar aqui del stock
      //porque ya se desconto la primera vez que se creo la fila
      if($items[$r]['id_fila']=="")
      {
       //recorremos el arreglo de proveedores seleccionados para la reserva
       //y vamos haciendo los descuentos respectivos
       $tam_prov_cant=sizeof($items[$r]['proveedores']);
       for($gh=0;$gh<$tam_prov_cant;$gh++)
       {
        $cantidad=$items[$r]['proveedores'][$gh]['cantidad'];
        $id_prov=$items[$r]['proveedores'][$gh]['id_proveedor'];;

        //traemos la cantidad actual para ese producto, en ese proveedor
        //en ese deposito
        $query="select cant_disp from stock where id_deposito=$id_dep and id_proveedor=$id_prov and id_producto=$id_prod";
        $result_stock=sql($query) or fin_pagina();
        //si hay cantidad disponible, lo descontamos

        if($cantidad<=$result_stock->fields['cant_disp'])
        {
         $sql="update stock set ";
 	     $sql.="cant_disp=cant_disp-$cantidad,";
 	     $sql.=" comentario='$obs' ";
	     $sql.=" where ";
	     $sql.="id_producto=$id_prod ";
	     $sql.=" AND id_deposito=$id_dep ";
	     $sql.=" AND id_proveedor=$id_prov";
	     sql($sql) or fin_pagina();;

	     //registramos en el historial el descuento de stock
         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
         $id_control_stock=sql($query) or fin_pagina();

         $fecha_modif=date("Y-m-d H:i:s",mktime());
         $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                 values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','OC Nº $nro_orden','Productos reservados por la Orden de Compra Nº $nro_orden','res_up')";
	     sql($query) or fin_pagina();

	     $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
	             values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cantidad)";
	     sql($query) or fin_pagina();

	     $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	             values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Productos Reservados por OC Nº $nro_orden')";
	     sql($query) or fin_pagina();
        }
        else
        {//sino damos el error y detenemos el for
         $msg="<center><b>La Orden de Compra no se puede guardar: <br>Las cantidades a reservar del stock son mayores que las actualmente disponibles</b></center>";
         $r=$items['cantidad']+1;
         return 0;
        }
       }//de for($gh=0;$gh<$tam_prov_cant;$gh++)
      }//de  if($ff->fields['id_fila']=="")
     }//de for($r=0;$r<$items['cantidad'];$r++)
  $db->CompleteTrans();
  return 1;
}//de function descontar_stock($id_dep)

/*********************************************************
Funcion que vuelve a reservar un producto en el stock
pasado como parametro. Esto se usa cuando se borra un
código de barras de una entrega de una OC dada.
**********************************************************/
function volver_a_reservar($nro_orden,$id_prod,$id_fila,$id_dep,$code,$desde_pagina)
{global $db,$_ses_user,$error_cb;
  $db->StartTrans();

  $fecha_modif=date("Y-m-d H:i:s",mktime());
  $obs="Se eliminó le código de barras $code de la entrega de la OC $nro_orden";
   //controlamos la fecha de autorizacion de la OC. Esto para compatibilizar
 //las ordenes autorizadas antes de la eliminacion de merc_trans. Asi, no intentara
 //descontar erroneamente del stock, cuando la fecha de autorizada
 //sea antes de haber subido lo de la eliminacion de merc_trans. En otro caso, si.

  //buscamos en el log, la fecha de autorizacion
  $query="select fecha from log_ordenes where nro_orden=$nro_orden and tipo_log='de autorizacion'";
  $f=sql($query) or fin_pagina();
  $fecha_auto=split(" ",$f->fields['fecha']);
  $fecha_limite=date("2005-01-10");
  $comp=compara_fechas($fecha_auto[0],$fecha_limite);
  //si la fecha de autorizacion es mayor(1) o igual(0), descontamos del stock
  //sino, significa que cuando se autorizo la OC
  if(($comp==1) ||($comp==0))
  {$volver_reservar=1;
  	if($desde_pagina=="lic_ord_compra_fin")
  	{
  	 //traemos el nro de recepciones y de entregas de esta fila.
  	 $query="select descripcion_prod,desc_adic,precio_unitario,fila.cantidad,simbolo,
                cant_rec,cant_ent,rec.nombre
 		from compras.fila join compras.orden_de_compra using (nro_orden) join licitaciones.moneda using(id_moneda)
 		left join (select recibidos.cantidad as cant_rec,depositos.nombre,id_fila from compras.recibidos left join general.depositos using (id_deposito) where ent_rec=1) as rec using(id_fila)
 		left join (select recibidos.cantidad as cant_ent,depositos.nombre,id_fila from compras.recibidos left join general.depositos using (id_deposito) where ent_rec=0) as ent using(id_fila)
 	    where id_fila=$id_fila";
        $datos_rec_ent=sql($query,"<br>Error al traer datos de fila para mail<br>") or fin_pagina();
  	 //traemos el nro de reservas hechas para esta fila
  	    $query="select id_proveedor,cantidad_total_reservada,id_reservado,cantidad_reservada,fecha_reserva
               from reservados join detalle_reserva using(id_reservado)
               where id_fila=$id_fila and id_producto=$id_prod and id_deposito=$id_dep
               order by cantidad_total_reservada DESC";
       $datos_reserva=sql($query,"<br>Error al traer datos proveedor cantidad (volver a reservar)<br>") or fin_pagina();

  	 //si el nro de recepciones es mayor o igual que el de entregas, y hay mas recepciones
  	 //	que reservas hechas, entonces debemos volver a reservar el producto cuyo codigo de barras
  	 //esta siendo borrado.
  	 $cant_rec=($datos_rec_ent->fields["cant_rec"])?$datos_rec_ent->fields["cant_rec"]:0;
  	 $cant_ent=($datos_rec_ent->fields["cant_ent"])?$datos_rec_ent->fields["cant_ent"]:0;
  	 $cant_reserv=($datos_rec_ent->fields["cantidad_total_reservada"])?$datos_rec_ent->fields["cantidad_total_reservada"]:0;
  	 if($cant_rec>=$cant_ent && $cant_reserv<$cant_rec)
  	       $volver_reservar=1;
  	 else
  	       $volver_reservar=0;
  	}


   if($volver_reservar)
   {/******************************
    Volvemos a reservar el producto
    *******************************/
    if($desde_pagina=="ord_compra_fin")
    {//averiguamos cual fue el primer proveedor para el que se le hizo la reserva inicialmente,
     //para volver a reservar el producto, bajo ese proveedor
     $query="select id_proveedor from proveedor_cantidad where id_fila=$id_fila";
     $prov=sql($query,"<br><br>Error al seleccionar proveedor cantidad  (volver a reservar)<br><br>") or fin_pagina();
     $id_prov=$prov->fields["id_proveedor"];
    }
    elseif($desde_pagina=="lic_ord_compra_fin")
     $id_prov=$_POST["id_proveedor"];
    //obtenemos el id_reservado, para este producto
    $query="select id_reservado from reservados
            where id_producto=$id_prod and id_proveedor=$id_prov and id_deposito=$id_dep";
    $reser=sql($query,"<br>Error al traer el id de reservado (volver a reservar)") or fin_pagina();
    $id_reservado=$reser->fields["id_reservado"];

    $sql="update reservados set
 	           cantidad_total_reservada=cantidad_total_reservada+1
	            where
	            id_reservado=$id_reservado";
    sql($sql,"<br>Error al reservar (volver_a_resevar)<BR>") or fin_pagina();

	$query="select id_detalle_reserva from detalle_reserva where id_fila=$id_fila";
	$det_res=sql($query,"<br>Error al traer detalle de la reserva (volver_a_reservar)");

	//traemos el id del tipo de reserva para OC, para poder insertarlo
    $query="select id_tipo_reserva from tipo_reserva where nombre_tipo ilike '%Reserva de productos para OC%'";
	$tipo_reserv=sql($query,"<br>Error al traer el id del tipo de reserva(insertar_recibidos)<br>") or fin_pagina();
	$tipo_reserva=$tipo_reserv->fields["id_tipo_reserva"];


	//si no hay ningun detalle de reserva, lo insertamos, sino lo acualizamos
	if($det_res->fields["id_detalle_reserva"]=="")
	{
	   //insertamos el detalle de reserva
	   $query="insert into detalle_reserva (id_reservado,id_fila,cantidad_reservada,fecha_reserva,usuario_reserva,id_tipo_reserva)
	           values ($id_reservado,$id_fila,1,'$fecha_modif','".$_ses_user['name']."',$tipo_reserva)";
	}
	else
	{    $query="update detalle_reserva set cantidad_reservada=cantidad_reservada+1,id_tipo_reserva=$tipo_reserva
	            where id_fila=$id_fila and id_reservado=$id_reservado";
	}
	sql($query,"<br>Error al insertar/actualizar detalle de reserva(volver_a_reservar)<bR>") or fin_pagina();

	     //registramos en el historial el descuento de stock
         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
         $id_control_stock=sql($query,"<br>Error al traer la secuencia de control de stock (volver_a_reservar)<br>") or fin_pagina();

         $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                 values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','OC Nº $nro_orden','Producto reservado por eliminación de entrega del producto con Código de Barras $code, en la OC Nº $nro_orden','res_up')";
	     sql($query,"<br>Error al insertar en control_stock(volver_a_reservar)<br>") or fin_pagina();

	     $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
	             values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",1)";
	     sql($query,"<br>Error al insertar en descuento(volver_a_reservar)<br>") or fin_pagina();

	     $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	             values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Producto reservado por eliminación de entrega del producto con Código de Barras $code, en la OC Nº $nro_orden')";
	     sql($query,"<br>Error al insertar en log_stock(volver_a_reservar)<br>") or fin_pagina();
   }//de if($volver_reservar)
  }//de if(($comp==1) ||($comp==0))
    else die("else");
	/****************************************
	Descontamos el producto de los recibidos
	*****************************************/
	$select="select id_recibido from recibidos where id_fila=$id_fila and ent_rec=0";
	$id_recib=sql($select,"<br>Error al traer id de recibido(volver_a_reservar)<br>") or fin_pagina();
	$id_recibido=$id_recib->fields["id_recibido"];
	$query="update recibidos set cantidad=cantidad-1 where id_recibido=$id_recibido";
	sql($query,"<br>Error al actualizar recibidos (volver_a_reservar)<br>") or fin_pagina();
	//con -1 indicamos que en lugar de una entrega, el log hace referencia a una eliminación de
	//una entrega previamente realizada
	$query="insert into log_recibido (id_recibido,usuario,fecha,cant)
	         values($id_recibido,'".$_ses_user["name"]."','$fecha_modif',-1)";
	sql($query,"<br>Error al insertar log<br>$query") or fin_pagina();

	if($desde_pagina=="ord_compra_fin")
	{$query="update proveedor_cantidad set cant_seleccionada=cant_seleccionada+1 where id_fila=$id_fila and id_proveedor=$id_prov";
     sql($query,"<br>Error al actualizar proveedor_cantidad (volver_a_reservar)<br>") or fin_pagina();
	}
///    die("trans");
 $db->CompleteTrans();
}//de function volver_a_reservar


/**********************************************************
 Funcion que agrega los productos entregados (que se
 descuentan de la reserva del stock donde se recibio),
 al stock de Produccion
***********************************************************/
function a_stock_produccion($nro_orden,$id_licitacion,$id_producto,$id_proveedor,$cant,$id_fila)
{global $db,$_ses_user;

 $db->StartTrans();

 $fecha_modif=date("Y-m-d H:i:s",mktime());
 $comentario="Incremento generado al agregar los productos a Stock de Producción, mediante la Orden de Compra Nº $nro_orden";


 //traemos el id del deposito de Produccion
 $query="select id_deposito from depositos where nombre='Produccion'";
 $dep_prod=sql($query,"<br>Error al traer el id del deposito de Produccion<br>") or fin_pagina();
 $id_deposito=$dep_prod->fields["id_deposito"];

 //revisamos si el producto-proveedor ya tiene una entrada para esta licitacion y esta OC, dentro de la tabla en_produccion
 $query="select id_en_produccion from en_produccion
      where id_licitacion=$id_licitacion and nro_orden=$nro_orden and id_producto=$id_producto and id_proveedor=$id_proveedor";
 $actualizar_entrada=sql($query,"<br>Error al revisar si existe entrada para el producto<br>") or fin_pagina();

 //si existe entrada de antes (se entrego una parte antes y ahora el resto, actualizamos dicha entrada
 //die("AAA".$actualizar_entrada->fields["id_en_produccion"]);
 if($actualizar_entrada->fields["id_en_produccion"]!="")
 {
  $id_en_produccion=$actualizar_entrada->fields["id_en_produccion"];

  //como existe entrada en la tabla en_produccion, es claro que existe tambien la correspondiente entrada en stock
  $query="update stock set cant_disp=cant_disp+$cant, comentario='$comentario'
	      where id_producto=$id_producto AND id_deposito=$id_deposito AND id_proveedor=$id_proveedor";
  sql($query,"<br>Error al actualizar el stock de produccion<br>") or fin_pagina();

  //actualizamos la cantidad en la correspondiente entrada en la tabla en_produccion
  $query="update en_produccion set cantidad=cantidad+$cant
          where id_en_produccion=$id_en_produccion";
  sql($query,"<br>Error al actualizar la tabla en produccion<br>") or fin_pagina();

 }//de if($actualizar_entrada->["id_en_produccion"]!="")
 else//sino, creamos una nueva entrada, para la tabla en_produccion
 {
  //insertamos precio si no existe con este proveedor
  $sql="select id_producto from precios where id_producto=$id_producto and id_proveedor=$id_proveedor ";
  $result=sql($sql,"<br>Error al buscar el precio para stock de produccion<br>") or fin_pagina();
  $cant_precios=$result->RecordCount();
  if($cant_precios==0)
  {
  	//traemos el precio de la fila
  	$query="select precio_unitario from fila where id_fila=$id_fila";
  	$precio=sql($query,"<br>Error al traer el precio de la fila<br>") or fin_pagina();

	insertar_precio($id_producto,$id_proveedor,$precio->fields["precio_unitario"],0);
  }

  //revisamos si esta la entrada para ese producto, proveedor, deposito, en el stock.
  $query="select count(*)as cuenta from stock
          where id_producto=$id_producto and id_deposito=$id_deposito and id_proveedor=$id_proveedor";
  $esta=sql($query,"<br>Error al revisar si existe la entrada en la tabla stock<br>") or fin_pagina();

  if($esta->fields["cuenta"]==0)
  {//insertamos la nueva entrada en la tabla stock, si no existe
   $query="insert into stock(id_producto,id_deposito,id_proveedor,cant_disp,comentario,last_user,last_modif)
       	values($id_producto,$id_deposito,$id_proveedor,$cant,'$comentario','".$_ses_user['login']."','$fecha_modif')";
   sql($query,"<br>Error al insertar el stock de produccion<br>") or fin_pagina();
  }
  else
  {
   //como existe entrada en la tabla stock, la actualizamos con la cantidad correspondiente
   $query="update stock set cant_disp=cant_disp+$cant, comentario='$comentario'
	       where id_producto=$id_producto  AND id_deposito=$id_deposito AND id_proveedor=$id_proveedor";
   sql($query,"<br>Error al actualizar el stock de produccion<br>") or fin_pagina();
  }

  //creamos la entrada necesaria en la tabla en_produccion
  $query="select nextval('en_produccion_id_en_produccion_seq') as id_en_produccion";
  $id_en_prod=sql($query,"<br>Error al traer la secuencia de en_produccion<br>") or fin_pagina();
  $id_en_produccion=$id_en_prod->fields["id_en_produccion"];

  $query="insert into en_produccion(id_en_produccion,nro_orden,id_licitacion,id_deposito,id_producto,id_proveedor,cantidad)
          values($id_en_produccion,$nro_orden,$id_licitacion,$id_deposito,$id_producto,$id_proveedor,$cant)";

  sql($query,"<br>Error al insetar tabla en Produccion<br>") or fin_pagina();

 }//del else de if($actualizar_entrada->["id_en_produccion"]!="")

 //finalmente, registramos lo agregado a stock de produccion, en las tablas correspondientes
 $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
 $id_control_stock=sql($query,"<br>Error al traer la secuencia de control de stock<br>") or fin_pagina();


 $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
       values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','".$_ses_user["name"]."','$comentario','oc')";
 sql($query,"<br>Error al insertar en control_stock<br>") or fin_pagina();

 $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc,id_en_produccion)
       values($id_deposito,$id_producto,$id_proveedor,".$id_control_stock->fields['id_control_stock'].",$cant,$id_en_produccion)";
 sql($query,"<br>Error al insertar en descuento<br>") or fin_pagina();

 $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
       values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','$comentario')";
 sql($query,"<br>Error al insertar en log_stock<br>") or fin_pagina();


 $db->CompleteTrans();
}//de function a_stock_produccion($nro_orden,$id_licitacion,$id_producto,$id_proveedor,$cant)


/*********************************************************
Funcion que agrega a mercaderia en transito todos los
productos de la OC, cuando autorizan la orden
(salvo algunas excepciones: si esta asociada a honorarios
o si no esta asociada a nada)
**********************************************************/
/*function pasar_a_merc_trans($nro_orden)
{global $db,$_ses_user,$select_proveedor,$es_stock;

 $db->StartTrans();
 //agregamos cada producto por separado a Mercadería en Tránsito
 //y luego completamos la informacion correspondiente en la tabla
 //mercaderia_transito
 $items_merc_trans=get_items($nro_orden);
 $id_prov=$select_proveedor;
 //seleccionamos el id del deposito RMA
 $query="select id_deposito from depositos where nombre='Mercadería en Tránsito'";
 $dep_merc_trans=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer id del deposito de Mercadería en Tránsito");
 $id_dep=$dep_merc_trans->fields['id_deposito'];

     $obs="Modificación automática para Orden de Compra Nº $nro_orden";
      for($i=0;$i<$items_merc_trans['cantidad'];$i++)
      {
       $id_prod=$items_merc_trans[$i]["id_producto"];

       $prov_cant=$items_merc_trans[$i]["proveedores"];
       $tam_p_c=sizeof($items_merc_trans[$i]["proveedores"]);

       //si el proveedor no es stock entonces solo hace una vez este for,
       //porque aqui no entra en juego la reservacion de productos, por lo que
       //no se utiliza la tabla proveedor_cantidad
       if(!$es_stock)
        $tam_p_c=1;
       for($k=0;$k<$tam_p_c;$k++)
       {
    	//si el proveedor es stock entonces utilizamos la informacion de la tabla
    	//proveedor_cantidad para obtener el id de proveedor y la cantidad de la reserva
    	//que se hizo de este producto ($id_prod)
        if($es_stock)
        {
          $id_prov=$items_merc_trans[$i]["proveedores"][$k]["id_proveedor"];
          $cantidad=$items_merc_trans[$i]["proveedores"][$k]["cantidad"];
        }
        else
        {//sino usamos la cantidad de la fila y el proveedor de la OC
          //para pasar los productos a mercaderia de transito
          $cantidad=$items_merc_trans[$i]["cantidad"];
          //y actualizamos el precio del producto para ese proveedor
          $precio=$items_merc_trans[$i]["precio_unitario"];
          //primero busco si tiene precio ese producto
          //con ese proveedor
	      $sql=" select id_producto from precios ";
	      $sql.="where id_producto=$id_prod ";
	      $sql.="and id_proveedor=$id_prov ";
	      $result=$db->execute($sql) or die($db->ErrorMsg()."<br> Error al revisar los precios (agregar_RMA) <br>$sql");
	      $cant_precios=$result->recordcount();
	      if($cant_precios==0)
	      {
	       insertar_precio($id_prod,$id_prov,$precio);
	      }
        }//del else de if($es_stock)
        if($cantidad!="" && $cantidad>0)
        {
       	 //revisamos si esta la entrada para ese producto, proveedor, deposito, en el stock.
       	 $query="select count(*)as cuenta from stock where id_producto=$id_prod and id_deposito=$id_dep and id_proveedor=$id_prov";
       	 $esta=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al seleccionar el stock (pasar_a_merc_trans)");

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

	      $db->execute($sql) or die($db->ErrorMsg()."<br>Error al insertar en stock (pasar_a_merc_trans)<br>$sql");

	      //registramos en el historial el incremento de stock
          $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
          $id_control_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock (agregar_RMA)");

          $fecha_modif=date("Y-m-d H:i:s",mktime());
          $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','Orden de Compra Nº $nro_orden','Incremento generado por la Orden de Compra Nº $nro_orden','is')";
          $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock (pasar_a_merc_trans)");

          //revisamos si ya no tiene una entrada para este prodcuto para esta OC
          //Esto puede darse porque se rechazo la OC y ahora se vuelve a autorizar
          $query="select id_mercaderia_transito from stock.descuento join stock.mercaderia_transito using (id_mercaderia_transito) where nro_orden=$nro_orden and descuento.id_deposito=$id_dep and descuento.id_producto=$id_prod and descuento.id_proveedor=$id_prov";
          $hay_merca=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al buscar la mercaderia en transito (pasar_a_merc_trans)");
          $id_merc_trans=$hay_merca->fields['id_mercaderia_transito'];
          //si no existe esa entrada, la insertamos
          if($id_merc_trans=="")
          {//Insertamos en la tabla mercaderia_transito los datos correspondientes
	       //a la orden de compra y demas datos necesarios
	       $query="select nextval('mercaderia_transito_id_mercaderia_transito_seq')as id_merc_trans";
	       $id_merc=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer secuencia de merc_trans (pasar_a_merc_trans)");
	       $id_merc_trans=$id_merc->fields['id_merc_trans'];
	       $query="insert into mercaderia_transito(id_mercaderia_transito,id_deposito,id_producto,id_proveedor,nro_orden,cantidad,fecha_inicio)
	               values ($id_merc_trans,$id_dep,$id_prod,$id_prov,$nro_orden,$cantidad,'$fecha_modif')";
           $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar info de mercaderia en transito(pasar_a_merc_trans)");
          }
          //sino, actualizamos la que ya existe
          else
          {$query="update mercaderia_transito set cantidad=cantidad+$cantidad,fecha_inicio='$fecha_modif'
                   where nro_orden=$nro_orden and id_deposito=$id_dep and id_producto=$id_prod and id_proveedor=$id_prov";
           $db->Execute($query) or die($db->ErrorMsg()."<br>Error al actualizar info de mercaderia en transito(pasar_a_merc_trans)");
          }

	      $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc,id_mercaderia_transito)
	            values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cantidad,$id_merc_trans)";
	      $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento (pasar_a_merc_trans)");

	      $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	            values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Incremento por Orden de Compra Nº $nro_orden')";
	      $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock (pasar_a_merc_trans)");



        }//de if($cantidad!="" && $cantidad>0)
       }//de for($k=0;$k<$tam_p_c;$k++)
      }//de for($i=0;$i<$items_rma['cantidad'];$i++)

 $db->CompleteTrans();
}//de function pasar_a_merc_trans($nro_orden)
*/
/*********************************************************
Funcion que descuenta de mercaderia en transito todos los
productos de la OC que se van recibiendo
(salvo algunas excepciones: si esta asociada a honorarios
o si no esta asociada a nada)
**********************************************************/
/*function descontar_merc_trans($nro_orden,$items,$id_proveedor)
{global $db,$msg,$_ses_user,$es_stock;
  //descontamos de Mercaderia en transito los items de la orden de compra

  $db->StartTrans();
  //controlamos la fecha de autorizacion de la OC. Esto para compatibilizar
  //las ordenes autorizadas antes del cambio de merc_trans. Asi, no dará el error
  //sino encunetra los productos en transito, cuando la fecha de autorizada
  //sea antes de haber subido lo de merc_trans. En otro caso, si.

  //buscamos en el log, la fecha de autorizacion
  $query="select fecha from log_ordenes where nro_orden=$nro_orden and tipo_log='de autorizacion'";
  $f=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer fecha de autorizacion (descontar_merc_trans)");
  $fecha_auto=split(" ",$f->fields['fecha']);
  $fecha_limite=date("2004-08-18");
  $comp=compara_fechas($fecha_auto[0],$fecha_limite);
  //si la fecha de autorizacion es mayor(1) o igual(0), descontamos de merc_trans
  if(($comp==1) ||($comp==0))
  {

   	 //traemos el id del deposito Mercaderia en Transito
     $query="select id_deposito from depositos where nombre='Mercadería en Tránsito'";
     $id_merc_trans=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el id del deposito de Mercaderia");
     $id_dep=$id_merc_trans->fields['id_deposito'];

     for($r=0;$r<$items['cantidad'];$r++)
     { //cantidad total recibida para el deposito Nº $r
      $cantidad=$items[$r]['cantidad'];
      if($cantidad!="" && $cantidad>0)
      {$h=0;
       //traemos  la info de los productos de la OC, junto con las duplas
       //proveedor-cantidad, para ser usadas como proveedor y cantidad a descontar
       //de mercaderia en transito. (Esto es asi para que el manejo de proveedores
       //en los stocks, sea transparente al usuario)
   	   $items_fila=get_items($nro_orden);
       while($items[$r]["id_fila"]!=$items_fila[$h]["id_fila"])
        $h++;
       $prov_cant=$items_fila[$h]['proveedores'];
       $tam_prov_cant=sizeof($items_fila[$h]['proveedores']);

      if(!$es_stock) //el proveedor es un stock
       $tam_prov_cant=1;

      for($j=0;$j<$tam_prov_cant;$j++)
  	   {
  	   	$id_prod=$items_fila[$h]['id_producto'];
  	   	if($es_stock)//el proveedor es un stock
  	   	{//entonces usamos la info de proveedores_cantiadad
         $id_prov=$items_fila[$h]['proveedores'][$j]['id_proveedor'];
         $cant_res=$items_fila[$h]['proveedores'][$j]['cantidad'];
  	   	}
  	   	else //el proveedor no es un stock
  	   	{//sino usamos el proveedor de la OC y la cantidad de la fila
         $id_prov=$id_proveedor;
  	   	 $cant_res=$items_fila[$h]['cantidad'];
  	   	}

        //si la cantidad a descontar ($cantidad) es mayor que la
        //reservada para ese proveedor ($cant_res), descontamos la cantidad
        //reservada y seguimos con el proximo proveedor
        if($cant_res<$cantidad)
        { $cant_descontar=$cant_res;
        }
       	//sino, logramos descontar toda la cantidad especificada ($cantidad)
       	//por lo que descontamos $cantidad
       	else
       	{ $cant_descontar=$cantidad;
       	}

        $obs="Recepción de productos en Orden de Compra Nº $nro_orden";

        //traemos la cantidad actual para ese producto, en ese proveedor
        //en ese deposito
        $query="select cant_disp from stock where id_deposito=$id_dep and id_proveedor=$id_prov and id_producto=$id_prod";
        $result_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el stock actual del producto 2 $query");

        //si hay cantidad disponible, lo descontamos
        if($cant_descontar>0 && $cant_descontar<=$result_stock->fields['cant_disp'])
        {$sql="update stock set ";
	     $sql.="cant_disp=cant_disp-$cant_descontar,";
	     $sql.=" comentario='$obs' ";
	     $sql.=" where ";
	     $sql.="id_producto=$id_prod ";
	     $sql.=" AND id_deposito=$id_dep ";
	     $sql.=" AND id_proveedor=$id_prov";
	     $db->execute($sql) or die($db->ErrorMsg().$sql);

 	     //registramos en el historial el descuento de stock
         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
         $id_control_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock");

         $fecha_modif=date("Y-m-d H:i:s",mktime());
         $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','OC Nº $nro_orden','Descuento generado por la Orden de Compra Nº $nro_orden.','a')";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");

         //seleccionamos el id de mercaderia en transito para asociar a descuento
	     $query="select id_mercaderia_transito from mercaderia_transito where id_producto=$id_prod and id_proveedor=$id_prov and id_deposito=$id_dep and nro_orden=$nro_orden";
	     $id_merca=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer id de merc_trans");
	     $id_merc_trans=$id_merca->fields['id_mercaderia_transito'];

         $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc,id_mercaderia_transito)
	            values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cant_descontar,$id_merc_trans)";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento $query");

	     $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	            values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Descuento por OC Nº $nro_orden')";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");

	     //actualizamos la cantidad en la entrada de la tabla Mercaderia en Transito
	     $query="update mercaderia_transito set cantidad=cantidad-$cant_descontar where id_mercaderia_transito=$id_merc_trans";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al actualizar Mercaderia en Transito");

	     //actualizamos la entrada en proveedor_cantidad para saber la proxima vez
        //la cantidad que aun queda reservada para este proveedor ($id_prov)
        $query="update proveedor_cantidad set cant_seleccionada=cant_seleccionada-$cant_descontar
                where id_fila=".$items[$r]['id_fila']." and id_proveedor=$id_prov";
        sql($query) or fin_pagina();

	     //actualizamos la cantidad que aun queda por descontar
	     $cantidad-=$cant_descontar;

        }//de if($cant_descontar<=$result_stock->fields['cant_disp'])


       }//de for($r=0;$r<$items['cantidad'];$r++)

       if($cantidad!=0)
       {
       	 die("La cantidad de mercaderia en transito no coincide con la que se intenta descontar");
       }
      }//de if($cantidad==0)
     }//de for($r=0;$r<$items['cantidad'];$r++)
  }//de if($fecha_auto>=....
  $db->CompleteTrans();
}//de function descontar_merc_trans($nro_orden,$items,$id_proveedor)
*/

/*************************************************
Funcion que se utiliza si la orden (excepto las de
asociacion con honorarios, o las de ninguna asociacion)
se rechaza o se anula, para eliminar los productos
que estaban en mercaderia en transito
-El cuarto parametro indica si se anulo o
 se rechazo la OC, mediante un string (Esto se
 necesita para guardar en el log del descuento
 de mercaderia en transito)
**************************************************/
/*function volver_atras_merc_trans($nro_orden,$items,$id_prov,$rech_an)
{global $db,$msg,$_ses_user,$es_stock;
  //descontamos de Mercaderia en transito los items de la orden de compra

  $db->StartTrans();
  //controlamos la fecha de autorizacion de la OC. Esto para compatibilizar
  //las ordenes autorizadas antes del cambio de merc_trans. Asi, no dará el error
  //sino encunetra los productos en transito, cuando la fecha de autorizada
  //sea antes de haber subido lo de merc_trans. En otro caso, si.

  //buscamos en el log, la fecha de autorizacion
  $query="select fecha from log_ordenes where nro_orden=$nro_orden and tipo_log='de autorizacion'";
  $f=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer fecha de autorizacion (descontar_merc_trans)");
  $fecha_auto=split(" ",$f->fields['fecha']);
  $fecha_limite=date("2004-08-18");
  $comp=compara_fechas($fecha_auto[0],$fecha_limite);
  //si la fecha de autorizacion es mayor(1) o igual(0), descontamos de merc_trans
  if(($comp==1) ||($comp==0))
  {

   	 //traemos el id del deposito Mercaderia en Transito
     $query="select id_deposito from depositos where nombre='Mercadería en Tránsito'";
     $id_merc_trans=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el id del deposito de Mercaderia");
     $id_dep=$id_merc_trans->fields['id_deposito'];

     for($r=0;$r<$items['cantidad'];$r++)
     { //cantidad total recibida para el deposito Nº $r
      $cantidad=$items[$r]['cantidad'];
      if($cantidad!="" && $cantidad>0)
      {$h=0;
       //traemos  la info de los productos de la OC, junto con las duplas
       //proveedor-cantidad, para ser usadas como proveedor y cantidad a descontar
       //de mercaderia en transito. (Esto es asi para que el manejo de proveedores
       //en los stocks, sea transparente al usuario)
   	   $items_fila=get_items($nro_orden);
       while($items[$r]["id_fila"]!=$items_fila[$h]["id_fila"])
        $h++;
       $prov_cant=$items_fila[$h]['proveedores'];
       $tam_prov_cant=sizeof($items_fila[$h]['proveedores']);

      if(!$es_stock) //el proveedor es un stock
       $tam_prov_cant=1;

      for($j=0;$j<$tam_prov_cant;$j++)
  	   {
  	   	$id_prod=$items_fila[$h]['id_producto'];
  	   	if($es_stock)//el proveedor es un stock
  	   	{//entonces usamos la info de proveedores_cantiadad
         $id_prov=$items_fila[$h]['proveedores'][$j]['id_proveedor'];
         $cant_res=$items_fila[$h]['proveedores'][$j]['cantidad'];
  	   	}
  	   	else //el proveedor no es un stock
  	   	{//sino usamos el proveedor de la OC y la cantidad de la fila
         $cant_res=$items_fila[$h]['cantidad'];
  	   	}

        //si la cantidad a descontar ($cantidad) es mayor que la
        //reservada para ese proveedor ($cant_res), descontamos la cantidad
        //reservada y seguimos con el proximo proveedor
        if($cant_res<$cantidad)
        { $cant_descontar=$cant_res;
        }
       	//sino, logramos descontar toda la cantidad especificada ($cantidad)
       	//por lo que descontamos $cantidad
       	else
       	{ $cant_descontar=$cantidad;
       	}

        $obs="Recepción de productos en Orden de Compra Nº $nro_orden";

        //traemos la cantidad actual para ese producto, en ese proveedor
        //en ese deposito
        $query="select cant_disp from stock where id_deposito=$id_dep and id_proveedor=$id_prov and id_producto=$id_prod";
        $result_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer el stock actual del producto 3 $query");
        //si hay cantidad disponible, lo descontamos
        if($cant_descontar>0 && $cant_descontar<=$result_stock->fields['cant_disp'])
        {$sql="update stock set ";
	     $sql.="cant_disp=cant_disp-$cant_descontar,";
	     $sql.=" comentario='$obs' ";
	     $sql.=" where ";
	     $sql.="id_producto=$id_prod ";
	     $sql.=" AND id_deposito=$id_dep ";
	     $sql.=" AND id_proveedor=$id_prov";
	     $db->execute($sql) or die($db->ErrorMsg().$sql);

 	     //registramos en el historial el descuento de stock
         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
         $id_control_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock");

         $fecha_modif=date("Y-m-d H:i:s",mktime());
         $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','OC Nº $nro_orden','Descuento generado por $rech_an de la Orden de Compra Nº $nro_orden.','a')";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");

 	     //seleccionamos el id de mercaderia en transito para asociar a descuento
	     $query="select id_mercaderia_transito from mercaderia_transito where id_producto=$id_prod and id_proveedor=$id_prov and id_deposito=$id_dep and nro_orden=$nro_orden";
	     $id_merca=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer id de merc_trans");
	     $id_merc_trans=$id_merca->fields['id_mercaderia_transito'];


         $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc,id_mercaderia_transito)
	            values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cant_descontar,$id_merc_trans)";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento");

	     $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	            values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Descuento por $rech_an de la OC Nº $nro_orden')";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");

	     //actualizamos la cantidad en la entrada de la tabla Mercaderia en Transito
	     $query="update mercaderia_transito set cantidad=cantidad-$cant_descontar where id_mercaderia_transito=$id_merc_trans";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al actualizar Mercaderia en Transito");

	     //actualizamos la cantidad que aun queda por descontar
	     $cantidad-=$cant_descontar;
         //actualizamos la entrada en proveedor_cantidad para saber la proxima vez
         //la cantidad que aun queda reservada para este proveedor ($id_prov)
         $query="update proveedor_cantidad set cant_seleccionada=cant_seleccionada-$cant_descontar
                 where id_fila=".$items[$r]['id_fila']." and id_proveedor=$id_prov";
         sql($query) or fin_pagina();

        }//de if($cant_descontar<=$result_stock->fields['cant_disp'])


       }//de for($r=0;$r<$items['cantidad'];$r++)

       if($cantidad!=0)
       {
       	 die("La cantidad de mercaderia en transito no coincide con la que se intenta descontar");
       }
      }//de if($cantidad==0)
     }//de for($r=0;$r<$items['cantidad'];$r++)
  }//de if($fecha_auto>=....
  $db->CompleteTrans();
}//de function volver_atras_merc_trans($nro_orden,$items,$id_prov,$rech_an)
*/

/*********************************************************
Funcion que descuenta los productos que se van entregando,
cuando ingresan el codigo de barras de los producto que
estan entregando. Solo se usa para OC que tienen proveedor
a un Stock.

@nro_orden     el Nº OC del producto a descontar del stock
@id_producto   el producto a descontar del stock
@id_deposito   el stock desde donde se descontara el stock
@cantidad      la cantidad a descontar
@id_fila       la fila en la OC del producto a descontar
**********************************************************/
function descontar_entregados($nro_orden,$id_producto,$id_deposito,$cantidad,$id_fila)
{
 global $db,$msg,$_ses_user,$es_stock,$link_cb,$error_cb;

  $db->StartTrans();
  //controlamos la fecha de autorizacion de la OC. Esto para compatibilizar
  //las ordenes autorizadas antes de la eliminacion de merc_trans. Asi, no intentara
  //descontar erroneamente del stock, cuando la fecha de autorizada
  //sea antes de haber subido lo de la eliminacion de merc_trans. En otro caso, si.

  //buscamos en el log, la fecha de autorizacion
  $query="select fecha from log_ordenes where nro_orden=$nro_orden and tipo_log='de autorizacion'";
  $f=sql($query,"<br>Error al traer fecha de autorizacion (descontar_merc_trans)<br>") or fin_pagina();
  $fecha_auto=split(" ",$f->fields['fecha']);
  $fecha_limite=date("2005-01-10");
  $comp=compara_fechas($fecha_auto[0],$fecha_limite);
  //si la fecha de autorizacion es mayor(1) o igual(0), descontamos del stock
  //sino, significa que cuando se autorizo la OC
  if(($comp==1) ||($comp==0))
  {
      if($cantidad!="" && $cantidad>0)
      {
      	$h=0;
        //obtenemos la informacion de la reserva para la fila
        $query="select id_en_stock,cantidad_reservada,id_prod_esp,id_deposito
                from detalle_reserva join en_stock using(id_en_stock)
                where id_fila=$id_fila";
        $idres=sql($query,"<br>Error al traer id de reservado (descontar_entregados)<br>") or fin_pagina();

        //si la cantidad a descontar de las reservas es mayor a la actualmente reservada, damos el error
        if($cant_reserv_actual>$cantidad)
          die("La cantidad que se intenta descontar es mayor a la actualmente reservada");
        else
        {
	        $cant_reserv_actual=$idres->fields['cantidad_reservada'];
	        $id_prod_esp=$idres->fields['id_prod_esp'];
	        $id_deposito=$idres->fields['id_deposito'];
	        $comentario="Utilización de los productos reservados por la Orden de Compra Nº $nro_orden";

	        //traemos el id del tipo de movimiento: "Se utilizaron los productos reservados para OC o para Movimiento de material"
	        $query="select id_tipo_movimiento from tipo_movimiento where nombre='Se utilizaron los productos reservados para OC o para Movimiento de material'";
	        $tipo_mov=sql($query,"<br>Error al traer el id del tipo de movimiento<br>") or fin_pagina();
            $id_tipo_movimiento=$tipo_mov->fields["id_tipo_movimiento"];

	        descontar_reserva($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_fila);
        }//de if($cant_reserv_actual>$cantidad)
      }//de if($cantidad==0)
      else
       die("La cantidad es cero!!");

  }//de if(($comp==1) ||($comp==0))

  $db->CompleteTrans();
}//de function descontar_entregados($nro_orden,$id_producto,$id_deposito,$cantidad,$id_fila)


/*********************************************************
Funcion que descuenta los productos que se van entregando,
cuando ingresan el codigo de barras de los producto que
estan entregando, pero para OC asociadas a Licitacion,
Presupuesto, RMA o Serv Tec, con proveedor que no es uno
de los stocks. La funcion devuelve la cantidad que no se
desconto de las reservas del stock (porque se entrego mas
de lo que se habia recibido)

@nro_orden     el Nº OC del producto a descontar del stock
@id_producto   el producto a descontar del stock
@id_deposito   el stock desde donde se descontara el stock
@cantidad      la cantidad a descontar
@id_fila       la fila en la OC del producto a descontar
**********************************************************/
function descontar_entregados_especial($nro_orden,$id_producto,$id_deposito,$cantidad,$id_fila)
{global $db,$msg,$_ses_user,$es_stock,$link_cb,$error_cb;

  $db->StartTrans();

  $fecha_modif=date("Y-m-d H:i:s",mktime());
  //traemos de la base de datos las reservas hechas para esta fila, si es que hay.

  $comp=1;
  if(($comp==1) ||($comp==0))
  {
      if($cantidad!="" && $cantidad>0)
      {$h=0;
       $cantidad_mail=$cantidad;
       //traemos los datos de los proveedores-cantidad del producto seleccionado,
       //en el deposito seleccionado, desde la tabla reservados,
       //para poder hacer el descuento correspondiente de los productos reservados
       $query="select id_proveedor,cantidad_total_reservada,id_reservado,cantidad_reservada,fecha_reserva
               from reservados join detalle_reserva using(id_reservado)
               where id_fila=$id_fila and id_producto=$id_producto and id_deposito=$id_deposito
               order by cantidad_total_reservada DESC";
       $prov_cantidad=sql($query,"<br>Error al traer datos proveedor cantidad(comun)<br>") or fin_pagina();

      //descontamos todo lo que se pueda de la tabla reservado.
      //Lo que no se pueda descontar de la tabla reservado, porque se está entregando mas de
      //lo que se tiene reservado para esta OC, se evita descontar de la tabla reservado,
      //pero se envia un mail
      //indicando el hecho, y se lo registra en la tabla log_entrega_especial
      while($cantidad>0 && !$prov_cantidad->EOF)
  	   {//die("I´m in");
  	   	$id_prov=$prov_cantidad->fields["id_proveedor"];
        $cant_res=$prov_cantidad->fields["cantidad_total_reservada"];
  	   	$id_reservado=$prov_cantidad->fields['id_reservado'];
  	   	$fecha_reserva=$prov_cantidad->fields['fecha_reserva'];

        //si la cantidad a descontar ($cantidad) es mayor que la
        //reservada para ese proveedor ($cant_res), descontamos la cantidad
        //reservada y seguimos con el proximo proveedor
        if($cant_res<$cantidad)
        { $cant_descontar=$cant_res;
        }
       	//sino, logramos descontar toda la cantidad especificada ($cantidad)
       	//por lo que descontamos $cantidad
       	else
       	{ $cant_descontar=$cantidad;
       	}

        if($id_producto && $cant_descontar!="" && $cant_descontar>0)
        {
         //descontamos la cantidad de reservados
       	 $sql="update reservados set
	           cantidad_total_reservada=cantidad_total_reservada-$cant_descontar
	           where id_reservado=$id_reservado";
       	 sql($sql) or fin_pagina();

       	 /*//seleccionamos la cantidad de detalle reserva que tiene la info de esa reserva
       	 $query="select cantidad_reservada from detalle_reserva where id_fila=$id_fila";
       	 $det_res=sql($query,"<br>Error en traer cant de detalle de reserva<br>") or fin_pagina();
       	 */
       	 //si la cantidad que hay, menos la que se va a descontar, es 0, entonces borramos
       	 //ese detalle porque ya no nos sirve mas
       	 if(($prov_cantidad->fields["cantidad_reservada"]-$cant_descontar)==0)
       	 {$query="delete from detalle_reserva where id_fila=$id_fila";
       	 }
       	 else
       	 {  //sino descontamos la cantidad tambien en detalle de reserva
       	   $query="update detalle_reserva set
       	         cantidad_reservada=cantidad_reservada-$cant_descontar
       	         where id_fila=$id_fila";
       	 }
       	 sql($query,"<br>Error al cambiar el detalle_reserva--<br>") or fin_pagina();
       	 //$db->Execute($query) or die($db->ErrorMsg()."<br>Error al descontar de detalle_reserva (descontar_entregados)");

       	 //vemos el tipo de la OC
         $query="select id_licitacion,es_presupuesto,nrocaso,orden_prod from orden_de_compra
                 where nro_orden=$nro_orden";
         $tipo_oc=sql($query,"<br>Error al consultar el tipo de la OC(insertar_recibidos)<br>") or fin_pagina();

         if($tipo_oc->fields["id_licitacion"]!="" && $tipo_oc->fields["es_presupuesto"]!=1)
         {$tipo_reserva="la Licitación ".$tipo_oc->fields["id_licitacion"];
         }
         elseif($tipo_oc->fields["id_licitacion"]!="" && $tipo_oc->fields["es_presupuesto"]==1)
         {$tipo_reserva="el Presupuesto ".$tipo_oc->fields["id_licitacion"];
         }
         elseif($tipo_oc->fields["nrocaso"]!="")
         {$tipo_reserva="el C.A.S. de Servicio Técnico Nº ".$tipo_oc->fields["nrocaso"];
         }
         elseif($tipo_oc->fields["orden_prod"]!="")
         {$tipo_reserva="el RMA con Orden de Producción Nº ".$tipo_oc->fields["orden_prod"];
         }
         else
         {//este error no deberia aparecer nunca.
          //Si aparece, es posible que se haya intentado descontar para una OC asociada a
          //Honorario de Serv Tec, o asociada a "OTRO". ESTO NO PUEDE SER ASI. SI PASA, ESTA MAL!!!
          die("Error Inesperado: El tipo de reserva esta vacio. Avise a la Division software");
         }
       	 $obs="Productos entregados para $tipo_reserva mediante la OC Nº $nro_orden";

 	     //registramos en el historial el descuento de stock
         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
         $id_control_stock=sql($query) or fin_pagina();

         $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','OC Nº $nro_orden','$obs','oc_ent')";
	     sql($query) or fin_pagina();

         $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
	            values($id_deposito,$id_producto,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cant_descontar)";
	     sql($query) or fin_pagina();

	     $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	            values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','$obs')";
	     sql($query) or fin_pagina();

	    }//de if($id_producto && $cant_descontar!="" && $cant_descontar>0)

	     //actualizamos la cantidad que aun queda por descontar
	     $cantidad-=$cant_descontar;

         $prov_cantidad->MoveNext();
       }//de while($cantidad>0 && !$prov_cantidad->EOF)

       if($cantidad!=0)
       {
       	//traemos los datos de la fila en cuestion para el cuerpo del mail
        $query="select descripcion_prod,desc_adic,precio_unitario,fila.cantidad,simbolo,
                cant_rec,cant_ent,rec.nombre,internacional
 		from compras.fila join compras.orden_de_compra using (nro_orden) join licitaciones.moneda using(id_moneda)
 		left join (select recibidos.cantidad as cant_rec,depositos.nombre,id_fila from compras.recibidos left join general.depositos using (id_deposito) where ent_rec=1) as rec using(id_fila)
 		left join (select recibidos.cantidad as cant_ent,depositos.nombre,id_fila from compras.recibidos left join general.depositos using (id_deposito) where ent_rec=0) as ent using(id_fila)
 	    where id_fila=$id_fila";
        $datos_fila=sql($query,"<br>Error al traer datos de fila para mail<br>") or fin_pagina();

        $prod_desc=$datos_fila->fields["cantidad"]." - ".$datos_fila->fields["descripcion_prod"]." ";
        $prod_desc.=$datos_fila->fields["desc_adic"]." - ";
        $prod_desc.=$datos_fila->fields["simbolo"]." ".formato_money($datos_fila->fields["precio_unitario"])." - ";
        $prod_desc.=$datos_fila->fields["simbolo"]." ".formato_money($datos_fila->fields["precio_unitario"]*$datos_fila->fields["cantidad"]);

        $internacional=$datos_fila->fields["internacional"];
        $stock_rec=$datos_fila->fields["nombre"];
        $cant_rec=($datos_fila->fields["cant_rec"])?$datos_fila->fields["cant_rec"]:0;
        $cant_ent=($datos_fila->fields["cant_ent"])?$datos_fila->fields["cant_ent"]-$cantidad_mail:0;

        if($cant_rec>$cant_ent)
         $cant_rec-=$cant_ent;
        else
         $cant_rec=0;

        //si la cantidad de recepciones de esta fila es es mayor
        //o igual que la cantidad de entregas
        //echo "cant_rec $cant_rec - cant_ent $cant_ent - cantidad $cantidad_mail<br>";
        if($cant_rec<$cant_ent+$cantidad_mail)
        {
       	//si la cantidad es diferente de cero significa que no se pudo descontar de los productos
        //reservados para esa OC, todo lo que se esta entregando. Esto pasa porque se permite
        //entregar productos aunque aun no se hayan recibido los que se compraron con esta OC.
        //En este caso,  se guarda el registro en la tabla log_entrega_especial, y se envia un
        //mail avisando del tema, a Juan Manuel y a Segio Valentino. Cuando se reciban los productos
        //que faltan, no se agregaran al stock como reservados
        $query="insert into log_entrega_especial (id_fila,usuario,fecha,cant_entregada,descripcion)
                values($id_fila,'".$_ses_user["name"]."','$fecha_modif',$cantidad,'Se entregaron mas productos de los que se han recibido hasta el momento')";
        sql($query) or fin_pagina();

        /***********************************************
        hacemos el descuento del stock disponible
        ************************************************/
        /*N0 BORRAR ESTE COMENTARIO POR AHORA (02/02/05)

        //traemos los datos de los proveedores-cantidad del producto seleccionado,
        //en el deposito seleccionado, desde la tabla stock,
        //para poder hacer el descuento correspondiente de los productos reservados
        $query="select id_proveedor,cant_disp
                from stock
                where id_producto=$id_producto and id_deposito=$id_deposito
                order by cant_disp DESC";
        $prov_cantidad=sql($query,"<br>Error al traer datos proveedor cantidad(en entrega especial)<br>") or fin_pagina();

        $cantidad_mail=$cantidad;

       //descontamos todo lo que se pueda de la tabla reservado.
       //Lo que no se pueda descontar de la tabla reservado, porque se está entregando mas de
       //lo que se tiene reservado para esta OC, se evita descontar, pero se envia un mail
       //indicando el hecho, y se lo registra en la tabla log_entrega_especial
       while($cantidad>0 && !$prov_cantidad->EOF)
  	    {
  	    	$id_prov=$prov_cantidad->fields["id_proveedor"];
         $cant_res=$prov_cantidad->fields["cant_disp"];

         //si la cantidad a descontar ($cantidad) es mayor que la
         //disponible para ese proveedor ($cant_res), descontamos la cantidad
         //reservada y seguimos con el proximo proveedor
         if($cant_res<$cantidad)
         { $cant_descontar=$cant_res;
         }
       	 //sino, logramos descontar toda la cantidad especificada ($cantidad)
         //por lo que descontamos $cantidad
         else
         { $cant_descontar=$cantidad;
         }

         if($id_producto && $cant_descontar!="" && $cant_descontar>0)
         {
          //descontamos la cantidad del stock disponible
       	  $sql="update stock set
	            cant_disp=cant_disp-$cant_descontar
	            where id_producto=$id_producto and id_deposito=$id_deposito and id_proveedor=$id_prov";
       	  $db->execute($sql) or die($db->ErrorMsg()."<br>Error al descontar de stock (descontar_entregados)<br>$sql");

       	  //vemos el tipo de la OC
          $query="select id_licitacion,es_presupuesto,nrocaso,orden_prod from orden_de_compra
                  where nro_orden=$nro_orden";
          $tipo_oc=sql($query,"<br>Error al consultar el tipo de la OC(insertar_recibidos)<br>") or fin_pagina();

          if($tipo_oc->fields["id_licitacion"]!="" && $tipo_oc->fields["es_presupuesto"]!=1)
          {$tipo_reserva="la Licitación ".$tipo_oc->fields["id_licitacion"];
          }
          elseif($tipo_oc->fields["id_licitacion"]!="" && $tipo_oc->fields["es_presupuesto"]==1)
          {$tipo_reserva="el Presupuesto ".$tipo_oc->fields["id_licitacion"];
          }
          elseif($tipo_oc->fields["nrocaso"]!="")
          {$tipo_reserva="el C.A.S. de Servicio Técnico Nº ".$tipo_oc->fields["nrocaso"];
          }
          elseif($tipo_oc->fields["orden_prod"]!="")
          {$tipo_reserva="el RMA con Orden de Producción Nº ".$tipo_oc->fields["orden_prod"];
          }
          else
          {//este error no deberia aparecer nunca.
           //Si aparece, es posible que se haya intentado descontar para una OC asociada a
           //Honorario de Serv Tec, o asociada a "OTRO". ESTO NO PUEDE SER ASI. SI PASA, ESTA MAL!!!
           die("Error Inesperado: El tipo de reserva esta vacio. Avise a la Division software");
          }
       	  $obs="Productos entregados (antes de haber recibido los comprados en la OC), para $tipo_reserva mediante la OC Nº $nro_orden";

 	      //registramos en el historial el descuento de stock
          $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
          $id_control_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock");

          $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                  values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','OC Nº $nro_orden','$obs','oc_ent')";
	      $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");

          $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
	              values($id_deposito,$id_producto,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cant_descontar)";
	      $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento $query");

	      $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	              values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','$obs')";
	      $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");

	     }//de if($id_producto && $cant_descontar!="" && $cant_descontar>0)

	     //actualizamos la cantidad que aun queda por descontar
	     $cantidad-=$cant_descontar;

         $prov_cantidad->MoveNext();
        }//de while($cantidad>0 && !$prov_cantidad->EOF)

        //si cantidad es distinto de cero significa que no habia sufieciente productos
        //disponibles para reservar, entonces damos error
        if($cantidad!=0)
         die("No hay suficientes productos disponibles en el stock elegido. No se puede registrar la entrega deseada.");
        */
        /**************************************
        armamos el mail para avisar el hecho
        ***************************************/

        if($internacional)
         $texto_int="Internacional";
        else
         $texto_int="";

         //$para="juanmanuel@coradir.com.ar,valentino@coradir.com.ar";
         $para="juanmanuel@coradir.com.ar";
         $asunto="OC $texto_int Nº $nro_orden: Se entregaron más productos de los que se han recibido.";
         $texto="Para la Orden de Compra $texto_int Nº $nro_orden, habia/n $cant_rec producto/s recibido/s en el Stock $stock_rec.\n\n";
         $texto.="Sin embargo, se ha/n entregado $cantidad_mail producto/s.\n\n";
         $texto.="La fila afectada es:\n";
         $texto.="Cantidad - Descripción - Precio unitario - Precio Total\n";
         $texto.="-------------------------------------------------------\n";
         $texto.="$prod_desc";
         $texto.="\n-------------------------------------------------------\n";
         $texto.="\n\nUsuario que realizó esta operación: ".$_ses_user["name"];
         $texto.="\nFecha: ".Fecha($fecha_modif);
         enviar_mail($para,$asunto,$texto,'','','','');
         //echo "SE ENVIARIA ESTE MAIL:<br> $texto";
        }//de if($cant_rec>=$cant_ent+$cantidad)
       }//de if($cantidad!=0)
      }//de if($cantidad!="" && $cantidad>0)
      else
       die("La cantidad es cero!!");

  }//de if(($comp==1) ||($comp==0))

  $db->CompleteTrans();
  return $cantidad;
}//de function descontar_entregados_especial($nro_orden,$id_producto,$id_deposito,$cantidad,$id_fila)



/*********************************************************
Funcion que reserva los productos seleccionados de un
proveedor de tipo stock, ingresando las entradas
correspondientes en la tabla reservados y detalle_reserva,
ligados a la entrada dentro de la tabla stock
(los productos siguen perteneciendo al stock del cual se
 reservaron los productos en cuestion, hasta que se
 eliminen de la reserva)
**********************************************************/
/*function reservar_stock($nro_orden,$id_dep)
{global $db,$_ses_user,$select_proveedor,$msg;

 $db->StartTrans();
 //por cada producto seleccionado desde el proveedor de tipo stock
 //ingresamos la cantidad dada, en la parte de reservados de ese
 //producto, en ese stock, bajo ese deposito (todo esto contenido)
 //en diferentes variables
 $items_stock=get_items($nro_orden);

      for($i=0;$i<$items_stock['cantidad'];$i++)
      {
       $id_prod=$items_stock[$i]["id_producto"];
       $id_fila=$items_stock[$i]["id_fila"];
       $tam_provc=sizeof($items_stock[$i]['proveedores']);

       for($h=0;$h<$tam_provc;$h++)
       {
        $id_prov=$items_stock[$i]['proveedores'][$h]['id_proveedor'];
        $cantidad=$items_stock[$i]['proveedores'][$h]["cantidad"];

        //controlamos si la fila ya hizo su reserva
        //si la hizo, no se reserva otra vez
        $query="select id_fila,cantidad_reservada from detalle_reserva join reservados using(id_reservado)
                where id_producto=$id_prod and id_proveedor=$id_prov and id_deposito=$id_dep and id_fila=$id_fila";
        $ff=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al chequear la fila en detalle_reserva (reservar_stock)");
        if($ff->fields['id_fila']=="")
        {
         $fecha_res=date("Y-m-d H:i:s",mktime());
         if($id_prod && $cantidad!="" && $cantidad>0)
         {
           //revisamos si esta la entrada en la tabla reservados
           //para ese producto, proveedor, deposito
           $query="select id_reservado from reservados where id_deposito=$id_dep and id_producto=$id_prod and id_proveedor=$id_prov";
           $esta=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al seleccionar el stock (reservar_stock)");

       	   //si no esta, insertamos la nueva entrada en la tabla reservados
       	   if($esta->fields['id_reservado']=="")
       	   {
       	    $query="select nextval('reservados_id_reservado_seq') as id_reservado";
       	    $idres=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer secuencia de reservado (reservar_stock)");
       	    $id_reservado=$idres->fields['id_reservado'];
       	    $sql="insert into reservados(id_reservado,id_producto,id_deposito,id_proveedor,cantidad_total_reservada)
       	          values($id_reservado,$id_prod,$id_dep,$id_prov,$cantidad)";

       	   }//si ya existia una entrada en reservados, se actualiza la cantidad
       	   else
       	   {$id_reservado=$esta->fields['id_reservado'];
       	    $sql="update reservados set
	            cantidad_total_reservada=cantidad_total_reservada+$cantidad
	            where
	            id_reservado=$id_reservado";

       	   }

	       $db->execute($sql) or die($db->ErrorMsg()."<br>Error al agregar productos en reservados (reservar_stock)<br>$sql");

	       //traemos el id del tipo de reserva para OC, para poder insertarlo
           $query="select id_tipo_reserva from tipo_reserva where nombre_tipo ilike '%Reserva de productos para OC%'";
	       $tipo_reserv=sql($query,"<br>Error al traer el id del tipo de reserva(insertar_recibidos)<br>") or fin_pagina();
	       $tipo_reserva=$tipo_reserv->fields["id_tipo_reserva"];

	       //registramos la entrada de productos reservados, con la info
	       //correspondiente
	       $query="insert into detalle_reserva (id_reservado,id_fila,cantidad_reservada,fecha_reserva,usuario_reserva,id_tipo_reserva)
	             values ($id_reservado,$id_fila,$cantidad,'$fecha_res','".$_ses_user['name']."',$tipo_reserva)";
	       //echo "<br>$query<br>ID_porv $id_prov - cant $cantidad<br><br>";
	       $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar detalle de reserva (reservar_stock) <br>$query");

          }//de if($cantidad!="" && $cantidad>0)
        }//de if($ff->fields['id_fila']=="")
        else
        {//la fila ya hizo su reserva, entonces controlamos que no
         //hayan cambiado la cantidad de la fila
         if($ff->fields['cantidad_reservada']!=$cantidad)
         {echo "ff ".$ff->fields['cantidad_reservada']." cant $cantidad <br>";
          die("No se puede cambiar la cantidad de la fila cuando el proveedor es un stock");
         	//si las cantidades son distintas, controlamos que haya stock
          //disponible, para reservar la nueva cantidad
          $query="select cant_disp from stock where id_deposito=$id_dep and
                  id_producto=$id_prod and id_proveedor=$id_prov";
          $cant_disp=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer cantidad en stock (reservar_stock)");

          //si la nueva cantidad a reservar es mayor que la que hay en
          //stock + la que habia sido reservada previamente
          if(($cant_disp->fields['cant_disp']+$ff->fields['cantidad_reservada'])<$cantidad)
          {$msg="<b>La cantidad a reservar en uno de los productos es superior a la disponible en stock.<br>No se pudo actualizar la orden de compra</b>";
           return 0;
          }
          else
          {//sino, modificamos los reservados
           $query="update reservados set
                   cantidad_total_reservada=(cantidad_total_reservada - ".$ff->fields['cantidad_reservada'].")+ $cantidad
                   where id_deposito=$id_dep and id_producto=$id_prod and id_proveedor=$id_prov";
           $db->Execute($query) or die($db->ErrorMsg()."<br>Error al actualizar reservados con nuevas cantidades (reservar_stock)");
           //modificamos el detalle_reserva
           $query="update detalle_reserva set cantidad_reservada=$cantidad where id_fila=$id_fila";
           $db->Execute($query) or die($db->ErrorMsg()."<br>Error al actualizar detalle_reserva con nuevas cantidades (reservar_stock)");

           //y modificamos el stock y todo lo que corresponde con el stock
           $obs="Actualización de reserva de productos por OC Nº $nro_orden";

           $sql="update stock set ";
	       $sql.="cant_disp=(cant_disp+ ".$ff->fields['cantidad_reservada'].")-$cantidad,";
	       $sql.=" comentario='$obs' ";
	       $sql.=" where ";
	       $sql.="id_producto=$id_prod ";
	       $sql.=" AND id_deposito=$id_dep ";
	       $sql.=" AND id_proveedor=$id_prov";
	       $db->execute($sql) or die($db->ErrorMsg()."<br>Error al actualizar el stock (reserva_stock)");
	       $cant_actualizar_logs+=$cantidad;
          }//del else de if($cant_disp->fields['cant_disp']<$cantidad)

          if($cant_actualizar_logs>0)
          {
           //registramos en el historial el descuento de stock
           $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
           $id_control_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock");

           $fecha_modif=date("Y-m-d H:i:s",mktime());
           $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                   values(".$id_control_stock->fields['id_control_stock'].",'$fecha_modif','OC Nº $nro_orden','Actualización de productos reservados por la Orden de Compra Nº $nro_orden','res_ac')";
	       $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");

	       $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
	               values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",actualizacion de la cantidad correcta (diferencia entre cantidad previa y actual)";
	       $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento");

	       $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	               values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_modif','Actualización de productos reservados por OC Nº $nro_orden')";
	       $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");
          }//de if($cant_actualizar_logs>0)
         }//de if($ff->fields['cant_fila']!=$cantidad)
        }//del else if($ff->fields['id_fila']=="")
       }//de for($h=0;$h<$tam_provc;$h++)
      }//de for($i=0;$i<$items_rma['cantidad'];$i++)

 $db->CompleteTrans();
   return 1;
}//de function reservar_stock($nro_orden,$id_dep)*/


/*********************************************************
Funcion que descuenta las reservas de los productos
seleccionados de un proveedor de tipo stock, descontando
las entradas correspondientes en la tabla reservados y
detalle_reserva, ligados a la entrada dentro de
la tabla stock.
(Es aca cuando los productos dejan de pertenecer
 efectivamente al stock al que estan ligados)
**********************************************************/
/*function descontar_reservados($nro_orden,$id_dep,$anular=0)
{global $db,$_ses_user,$select_proveedor;

 $db->StartTrans();
 //por cada producto seleccionado desde el proveedor de tipo stock
 //ingresamos la cantidad dada, en la parte de reservados de ese
 //producto, en ese stock, bajo ese deposito (todo esto contenido)
 //en diferentes variables
 $items_stock=get_items($nro_orden);

      for($i=0;$i<$items_stock['cantidad'];$i++)
      {
       $id_prod=$items_stock[$i]["id_producto"];
       $id_fila=$items_stock[$i]["id_fila"];
       $fecha_res=date("Y-m-d H:i:s",mktime());
       $tam_prov_c=sizeof($items_stock[$i]['proveedores']);
       for($f=0;$f<$tam_prov_c;$f++)
       {
        $id_prov=$items_stock[$i]['proveedores'][$f]['id_proveedor'];
        $cantidad=$items_stock[$i]['proveedores'][$f]["cantidad"];


        //obtenemos el correspondiente id de la entrada
        //en la tabla reservados
        $query="select id_reservado from reservados where
                id_deposito=$id_dep and id_producto=$id_prod and id_proveedor=$id_prov";
        $idres=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer id de reservado (descontar_reservado)");
        $id_reservado=$idres->fields['id_reservado'];
        if($id_prod && $cantidad!="" && $cantidad>0)
        {
         //descontamos la cantidad de reservados
       	 $sql="update reservados set
	           cantidad_total_reservada=cantidad_total_reservada-$cantidad
	           where id_reservado=$id_reservado";
       	 $db->execute($sql) or die($db->ErrorMsg()."<br>Error al descontar de reservados (reservar_stock)<br>$sql");

       	 //eliminamos la entrada del detalle de la reserva porque
       	 //ya no es necesaria
       	 $query="delete from detalle_reserva where id_fila=$id_fila";
       	 $db->Execute($query) or die($db->ErrorMsg()."<br>Error al descontar de detalle_reserva (reservar_stock)");

       	 //registramos en el log del producto,
       	 //el descuento del producto reservado

       	 //registramos en el historial el descuento de stock
         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
         $id_control_stock=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer la secuencia de control de stock");

         if($anular)
         {$comentario_stock="Cancelacion de reserva de productos por anulación de la Orden de Compra Nº $nro_orden";
          $com_cs="res_cl";
          //si se anulo, entonces los productos reservados tienen que volver al stock,
          //porque al cancelar la reservacion, los productos vuelven a estar disponibles
           $sql="update stock set ";
	       $sql.="cant_disp=cant_disp+$cantidad";
	       $sql.=" where ";
	       $sql.="id_producto=$id_prod ";
	       $sql.=" AND id_deposito=$id_dep ";
	       $sql.=" AND id_proveedor=$id_prov";
	       $db->execute($sql) or die($db->ErrorMsg()."<br>Error al actualizar el stock (descontar_reservados)");
         }
         else
         {$comentario_stock="Utilización de los productos reservados por la Orden de Compra Nº $nro_orden";
          $com_cs="res_dn";
         }
         $query="insert into control_stock(id_control_stock,fecha_modif,usuario,comentario,estado)
                values(".$id_control_stock->fields['id_control_stock'].",'$fecha_res','OC Nº $nro_orden','$comentario_stock','$com_cs')";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en control_stock");

	     $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
	            values($id_dep,$id_prod,$id_prov,".$id_control_stock->fields['id_control_stock'].",$cantidad)";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en descuento");

	     $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
	            values (".$id_control_stock->fields['id_control_stock'].",'".$_ses_user['name']."','$fecha_res','$comentario_stock')";
	     $db->Execute($query) or die($db->ErrorMsg()."<br>Error al insertar en log_stock");

        }//de if($id_prod && $cantidad!="" && $cantidad>0)
	   }//de for($f=0;$f<$tam_prov_c;$f++)
      }//de for($i=0;$i<$items_rma['cantidad'];$i++)

 $db->CompleteTrans();
}//de function descontar_reservados($nro_orden,$id_dep,$anular=0)	*/


/*********************************************************
Función que retorna un arreglo con la cantidad de productos
recibidos, y entregados para cada fila de la OC pasada como
parametro.

El formato del arreglo es id_fila en la primera dimension.
Cada id_fila contiene un arreglo con dos elementos:
   #recibidos  #entregados
**********************************************************/
function cant_rec_ent_por_fila($nro_orden)
{global $db;

  /*$query="select cant_rec,cant_ent,id_fila
 		from compras.fila join compras.orden_de_compra using (nro_orden)
 		left join (select recibido_entregado.cantidad as cant_rec,id_fila from compras.recibido_entregado where ent_rec=1) as rec using(id_fila)
 		left join (select recibido_entregado.cantidad as cant_ent,id_fila from compras.recibido_entregado where ent_rec=0) as ent using(id_fila)
         ";*/
  $query="select cant_rec,id_fila
 		from compras.fila join compras.orden_de_compra using (nro_orden)
 		left join (select recibido_entregado.cantidad as cant_rec,id_fila from compras.recibido_entregado where ent_rec=1) as rec using(id_fila)
 		where nro_orden=$nro_orden
         ";
  $datos=sql($query,"<br>Error al traer datos de recibidos o entregados (cant_rec_ent_por_fila)<br>") or fin_pagina();
  $filas_ent_rec=array();
  while (!$datos->EOF)
  {
   $id_fila=$datos->fields["id_fila"];
   $filas_ent_rec[$id_fila]=array();
   $filas_ent_rec[$id_fila]["recibidos"]=$datos->fields["cant_rec"];
   //$filas_ent_rec[$id_fila]["entregados"]=$datos->fields["cant_ent"];
   $filas_ent_rec[$id_fila]["entregados"]=0;

   $datos->MoveNext();
  }//de while(!$datos->EOF)

  return $filas_ent_rec;
}//de function cant_rec_ent_por_fila($nro_orden)

/*********************************************************
Funcion que chequea si hay cambios de productos para ese
$id_fila, y en caso de existir, devuelve el ultimo id de
producto que se cargo para reemplazar el de la fila.
*********************************************************/
function ultimo_cambio_producto($id_fila)
{
 $query="select id_producto from cambios_producto where id_fila=$id_fila order by fecha_cambio DESC";
 $reemplazo=sql($query,"<br>Error al traer el id del producto<br>") or fin_pagina();

 if($reemplazo->fields["id_producto"]!="")
  return $reemplazo->fields["id_producto"];
 else //indicamos que no han habido reemplazos
  return -1;
}


/*********************************************************
Función que retorna un arreglo con la cantidad de productos
recibidos, y entregados para cada fila de la OC pasada como
parametro.

El formato del arreglo es id_fila en la primera dimension.
Cada id_fila contiene un arreglo con dos elementos:
   #recibidos  #entregados
**********************************************************/
function filas_con_cambios_prod($nro_orden)
{global $db;

  $query="select cambios_producto.id_producto,desc_gral,id_fila
          from cambios_producto join productos using(id_producto) join fila using(id_fila)
          where nro_orden=$nro_orden";
  $datos=sql($query,"<br>Error al traer datos de cambios de productos (filas_con_cambios_prod)<br>") or fin_pagina();
  $filas_cambios_prod=array();
  while (!$datos->EOF)
  {
   $id_fila=$datos->fields["id_fila"];
   $filas_cambios_prod[$id_fila]=array();
   $filas_cambios_prod[$id_fila]["id_producto"]=$datos->fields["id_producto"];
   $filas_cambios_prod[$id_fila]["desc_gral"]=$datos->fields["desc_gral"];

   $datos->MoveNext();
  }//de while(!$datos->EOF)

  return $filas_cambios_prod;
}//de function filas_con_cambios_prod($nro_orden)

/**********************************************************************************
Funcion que Genera todas las columnas referidas a la OC internacional, para el
listado de productos de la misma

@index		tiene el numero que se le concatena al nombre de cada input
@valores	trae los values que se deben asignar a cada campo. Puede ser vacio
***********************************************************************************/
function generar_datos_fila_oc_internacional($index,$valores)
{
 ?>
   <td align="center">
    <input type="text" name="proporcional_flete_<?=$index?>" readonly style='text-align:right' value="<?=number_format($valores["proporcional_flete"],2,'.','')?>" size="8" onchange="calcular_monto_total(0,total_proporcional_flete);">
   </td>
   <td align="center">
    <input type="text" name="base_imponible_cif_<?=$index?>" readonly style='text-align:right' value="<?=number_format($valores["base_imponible_cif"],2,'.','')?>" size="8" onchange="calcular_monto_total(1,total_base_imponible_cif);">
   </td>
   <td align="center">
    <input type="text" name="derechos_<?=$index?>" readonly style='text-align:right' value="<?=number_format($valores["derechos"],2,'.','')?>" size="8" onchange="calcular_monto_total(2,total_derechos);">
   </td>
   <td align="center">
    <input type="text" name="iva_<?=$index?>" readonly style='text-align:right' value="<?=number_format($valores["iva"],2,'.','')?>" size="8" onchange="calcular_monto_total(3,total_iva);">
   </td>
   <td align="center">
    <input type="text" name="ib_<?=$index?>" readonly style='text-align:right' value="<?=number_format($valores["ib"],2,'.','')?>" size="8" onchange="calcular_monto_total(4,total_ib);">
   </td>
 <?
}//de function generar_datos_fila_oc_internacional()


/***********************************************************************************
Funcion que genera el select de los datos de POSAD, que van en las filas.

@index		tiene el numero que se le concatena al nombre de del select
@id_posad	trae el id del posad seleccionado, para mostrarlo seleccionado para la
			fila. En caso de no venir nigun id, muestra la palabra "Selccione..."
************************************************************************************/
function generar_posad($index,$id_posad="")
{global $permiso;
 //traemos todos los datos de la tabla posad
 $query="select id_posad,descripcion,codigo_ncm from posad order by descripcion";
 $posad=sql($query,"<br>Error al traer los datos de posad<br>") or fin_pagina();

 //generamos el select con los datos, y los hiddens respectivos, para poder hacer luego los calculos
 //de cada monto referido a las OC internacionales, para la fila.
 ?>
 <table>
  <tr>
   <td>
 	<select name="select_posad_<?=$index?>" <?=$permiso?> onchange="set_montos_fila_oc_internacional()">
     <option value="-1" selected>Seleccione...</option>
     <?
	 while (!$posad->EOF)
	 {?>
	  <option value="<?=$posad->fields["id_posad"]?>" <?if($posad->fields["id_posad"]==$id_posad) echo "selected"?>>
	   <?=$posad->fields["codigo_ncm"]?>
	  </option>
	  <?
	  $posad->MoveNext();
	 }//de while(!$posad->EOF)

	 ?>
	 </select>
	</td>
	<td>
 	 <input type="button" name="detalle_posad_<?=$index?>" title="Seleccionar Posición Aduanera" value="+" <?=$permiso?> onclick="window.open('listado_posad.php?indice_select=<?=$index?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=200,top=120,width=700,height=300')">
 	</td>
   </tr>
  </table>
 <?
}//de function generar_posad($index,$id_posad="")


/*********************************************************
 Funcion que modifica los items devueltos por
 la funcion get_items que se le pasa como parametro,
 sacandole de la segunda dimension los campos
 indicados en el segundo parametro.
 Devuelve el arreglo modificado, sin los valores que
 se solicito sacar.

 Parametros:
 @items         el arreglo original, de dos dimensiones,
                del que se le sacaran los campos
                especificados en $sacar
 @sacar         un arreglo que tiene guardados los nombres
                de los campos que se van a eliminar de $items
 @items_retorno retorna el arreglo $items, sin los campos
                especificados en $sacar, en la segunda
                dimension de $items
**********************************************************/
function prepare_items($items,$sacar)
{
 //revisamos si las claves pasadas en $sacar, son parte de la
 //segunda dimension de $items
 /*foreach ($sacar as $clave) {
  if(!array_key_exists($clave,$items[0]))
   die("Error Interno: La clave $clave a sacar de items no existe (prepare_items)");
 }*/
 //traemos todas las claves de la segunda dimension del arreglo
 $claves_items=array_keys($items[0]);
 $cant_claves=sizeof($claves_items);
 //arreglo de retorno
 $items_retorno=array();
 //recorremos todas las claves, y pasamos al arreglo de retorno solo
 //aquellas claves que no estan en $sacar
 for($j=0;$j<$cant_claves;$j++)
 {//si no esta en el arreglo de claves a sacar entonces pasamos ese campo
  //al arreglo de retorno (sino, no se lo pasa y asi eliminamos los campos
  //especificados en $sacar)
  if(!in_array($claves_items[$j],$sacar))
  {//pasamos cada arreglo en $items[$i], al correspondiente $items_retorno[$i]
   for($i=0; $i < $items["cantidad"];$i++)
   	$items_retorno[$i][$claves_items[$j]]=$items[$i][$claves_items[$j]];
  }//de if(!in_array($claves_items[$j],$sacar))
 }//de 	for($j=0;$j<$cant_claves;$j++)
 $items_retorno["cantidad"]=$items["cantidad"];
 return $items_retorno;
}//de function prepare_items($items,$sacar)


function duplicar_plantilla_default($id_plantilla,$nro_orden)
{global $db;
 $db->StartTrans();
 //buscamos los datos de la plantilla, para reproducirlos si es necesario
 $query="select plantilla_pagos.descripcion,forma_de_pago.id_forma,forma_de_pago.dias,forma_de_pago.id_tipo_pago from plantilla_pagos join pago_plantilla using(id_plantilla_pagos) join forma_de_pago using(id_forma) where id_plantilla_pagos=$id_plantilla";
 $muestra=sql($query,"<br>Error en seleccion del campo 'mostrar' en la plantilla<br>") or fin_pagina();

  //creamos la plantilla duplicada
  $query="insert into plantilla_pagos (descripcion,mostrar) values('".$muestra->fields['descripcion']."',0)";
  sql($query,"<br>Error en la duplicacion de la plantilla (funcion duplicar_plantilla_default)<br>") or fin_pagina();

  //seleccionamos el id de la plantilla recien insertada
  $query="select max(id_plantilla_pagos) as maxid from plantilla_pagos";
  $nuevo_id=sql($query,"<br>seleccion del maximo en la plantilla (funcion duplicar_plantilla_default)<br>") or fin_pagina();
  $nueva_plantilla=$nuevo_id->fields['maxid'];

  //insertamos las nuevas formas de pago
  while(!$muestra->EOF)
  {//insertamos en la tabla forma de pago
   $query="insert into forma_de_pago (dias,id_tipo_pago) values(".$muestra->fields['dias'].",".$muestra->fields['id_tipo_pago'].")";
   sql($query,"<br>Error en la insersion de forma de pago nueva (funcion duplicar_plantilla_default)") or fin_pagina();
   //traemos el id de la forma de pago recien insertada
   $query="select max (id_forma)as maxid from forma_de_pago";
   $id_forma=sql($query,"<br>Error en la seleccion del maximo en la forma de pago (funcion duplicar_plantilla_default)<br>") or fin_pagina();
   //relacionamos la plantilla con la forma de pago
   $query="insert into pago_plantilla (id_forma,id_plantilla_pagos) values(".$id_forma->fields['maxid'].",$nueva_plantilla)";
   sql($query,"<br>insersion de pago_plantilla nueva (funcion duplicar_plantilla_default)<br>") or fin_pagina();

   $muestra->MoveNext();
  }

 if($nro_orden!=0)
 {//borra todas las ordenes pagos que se relacionan con la orden de compra
  $query="select id_pago from orden_de_compra join pago_orden using (nro_orden) where nro_orden=$nro_orden";
  $ordenes_pagos=sql($query,"<br>Error en la seleccion de ordenes de pagos (funcion duplicar_plantilla_default)<br>") or fin_pagina();
  $query="delete from pago_orden where nro_orden=$nro_orden";
  sql($query,"<br>Error eliminando de pago de orden (funcion duplicar_plantilla_default)<br>") or fin_pagina();
  while(!$ordenes_pagos->EOF)
  {
   $query="delete from ordenes_pagos where id_pago=".$ordenes_pagos->fields['id_pago'];
   sql($query) or fin_pagina();

   $ordenes_pagos->MoveNext();
  }
 }
 $db->CompleteTrans();
 return $nueva_plantilla;
}//de function duplicar_plantilla_default

/* firma_coradir() BY GACZ - version solo texto    */
/*function firma_coradir_mail($confiden=true)
{
if ($confiden)
{
$confiden="
NOTA DE CONFIDENCIALIDAD\n
Este mensaje (y sus anexos) es confidencial, esta dirigido exclusivamente a <br>
las personas direccionadas en el mail, puede contener información de <br>
propiedad exclusiva de Coradir S.R.L. y/o amparada por el secreto profesional. <br>
El acceso no autorizado, uso, reproducción, o divulgación esta prohibido. <br>
Coradir S.R.L. no asumirá responsabilidad ni obligación legal alguna por <br>
cualquier información incorrecta o alterada contenida en este mensaje. <br>
Si usted ha recibido este mensaje por error, le rogamos tenga la amabilidad <br>
de destruirlo inmediatamente junto con todas las copias del mismo, notificando<br>
al remitente. No deberá utilizar, revelar, distribuir, imprimir o copiar <br>
este mensaje ni ninguna de sus partes si usted no es el destinatario. <br>
Muchas gracias.<br>";

$confiden="<br>NOTA DE CONFIDENCIALIDAD<br>";
$confiden.="Este mensaje (y sus anexos) es confidencial, esta dirigido exclusivamente a las personas direccionadas en el mail, puede contener información de propiedad exclusiva de Coradir S.R.L. y/o amparada por el secreto profesional. ";
$confiden.="El acceso no autorizado, uso, reproducción, o divulgación esta prohibido. ";
$confiden.="Coradir S.R.L. no asumirá responsabilidad ni obligación legal alguna por cualquier información incorrecta o alterada contenida en este mensaje. ";
$confiden.="Si usted ha recibido este mensaje por error, le rogamos tenga la amabilidad de destruirlo inmediatamente junto con todas las copias del mismo, notificando al remitente. ";
$confiden.="No deberá utilizar, revelar, distribuir, imprimir o copiar este mensaje ni ninguna de sus partes si usted no es el destinatario.<br>";
$confiden.="Muchas gracias.<br>";
}
else
    $confiden="";

$firma="\n"."CORADIR S.R.L. <br>";
$firma.="\n"."San Luis: Tel/Fax: (02652)431134 y rotativas <br>";
$firma.="\n"."Dirección: San Martín 454 (B5700BQJ) <br>";
$firma.="\n"."Bs.As.: Tel/Fax: (011)5236-0314 y rotativas <br>";
$firma.="\n"."Dirección: Tacuarí 447 - (C1071AAI)<br>";
$firma.="\n"."e-mail: info@coradir.com.ar<br>";
$firma.="\n"."página: www.coradir.com.ar<br>";

return "\n".$firma."\n".$confiden;

}*/

function mail_stock($para,$asunto,$nro_orden)
{

   $mensaje=detalle_orden ($nro_orden);
   $mail_header="";
   $mail_header .= "MIME-Version: 1.0";
   $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <>";
   $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
   $mail_header .="\nTo:$para";
   //$mail_header .="\nBcc: corapi@coradir.com.ar";
   //$mail_header .="\nBcc: juanmanuel@coradir.com.ar";
   //$mail_header .="\nBcc: carlos@coradir.com.ar";
   $mail_header .= "\nContent-Type: text/plain";
   $mail_header .= "\nContent-Transfer-Encoding: 8bit";
   $mail_header .= "\n\n" . $mensaje."\n";
   $mail_header .= "\n\n" . firma_coradir()."\n";

mail("",$asunto,"",$mail_header);

} //fin mail_stock

//funcion que muestra la tabla de resumen de pagos para el excel
//para las ordenes con estado pagadas o parcialmente
//pagadas, tambien se sacaron algunos datos
function resumen_excel($nro_orden,$simbolos)
{
	global $db;

	$sql="select * from pago_orden join ordenes_pagos using (id_pago)
	      where pago_orden.nro_orden=$nro_orden
	      order by id_pago";
	$resultado=sql($query) or fin_pagina();
	$filas_encontradas=$resultado->RecordCount();

	//traemos los datos de las notas de credito asociadas (si es que hay)
	$query="select id_nota_credito,nota_credito.monto,nota_credito.observaciones,
	        oc.valor_dolar,moneda.id_moneda,moneda.simbolo
	        from
	        (select * from n_credito_orden where nro_orden=$nro_orden) as oc
	         join nota_credito using (id_nota_credito)
	         join moneda using (id_moneda)";
	$notas_credito=sql($query) or fin_pagina();
	//echo "consulta que hace cuando entra en la funcion    ".$query."<br>";
	//echo "nota de credito que trae    ".$notas_credito['id_nota_credito']."<br>";

	if ($simbolos) $simbolo="u\$s";
	else $simbolo="$";

	$total_a_pagar=0;
	$ordenes_atadas=PM_ordenes($nro_orden);
	$cant_ordenes=sizeof($ordenes_atadas);

	$tam=sizeof($ordenes_atadas);
	  for($i=0;$i<$tam;$i++)
	  {$m_orden=monto_a_pagar($ordenes_atadas[$i]);
	                      $total_a_pagar+=$m_orden;
	                       }//del for

	//if($cant_ordenes>1) {
	//$total_a_pagar=ordenes_pago_multiple($ordenes_atadas,$simbolo,"100%",1,1);
	//echo $total_a_pagar;
	//}
	?>
	<table width="100%" align="center" align="Center" border="1" cellspacing="0"  bordercolor="#000000">

	<? while(!$notas_credito->EOF) { ?>
	    <tr>
	     <td align="center" width="25%">Nota Crédito</td>
	     <td align="center" width="25%"><?=$notas_credito->fields['id_nota_credito']?></td>
	     <td align="center" width="25%"><?=$notas_credito->fields['simbolo']?> -<?=$notas_credito->fields['monto']?></td>
	     <td align="center" width="25%">&nbsp;</td>
	    </tr>
	<? $notas_credito->MoveNext();
	}

	$total_monto=0;
	$total_monto_pagado=0;
	$cantidad_pagos=0;
	for($i=0;$i<$filas_encontradas;$i++){
	//realizo el resumen con los pagos
	$usuario=$resultado->fields['usuario'];
	$fecha=fecha($resultado->fields['fecha']);
	if ($resultado->fields['númeroch'] || $resultado->fields['iddébito']||$resultado->fields['id_ingreso_egreso'])
	  { $cantidad_pagos++;//contador para ver cuantos pagos hizo la persona
	    $monto=$resultado->fields['monto'];
	    $total_monto+=$monto;
	    $monto=number_format($resultado->fields['monto'],"2",".","");
	    echo "<tr>";
	      if (!($moneda)) {
	         if ($resultado->fields["númeroch"]){
	                                     $nro_cheque=$resultado->fields['númeroch'];
	                                     echo "<td width='25%'>Cheque       </td>";
	                                     echo "<td align='center' width='25%'> $nro_cheque </td>";
	                                     echo "<td align='center' width='25%'> $simbolo $monto      </td>";
	                                     echo "<td align='center' width='25%'> $fecha       </td>";
	                                     }
	      if ($resultado->fields["id_ingreso_egreso"]){
	                                     $nro_ingreso=$resultado->fields['id_ingreso_egreso'];
	                                     echo "<td width='25%'>Efectivo       </td>";
	                                     echo "<td align='center' width='25%'> $nro_ingreso </td>";
	                                     echo "<td align='center' width='25%'> $simbolo $monto      </td>";
	                                     echo "<td align='center' width='25%'> $fecha       </td>";
	                                     }
	      if ($resultado->fields["iddébito"])  {
	                                     $nro_debito=$resultado->fields['iddébito'];
	                                     echo "<td width='25%'>Transferencia       </td>";
	                                     echo "<td align='center' width='25%'> $nro_debito </td>";
	                                     echo "<td align='center' width='25%'> $simbolo $monto      </td>";
	                                     echo "<td align='center' width='25%'> $fecha       </td>";
	                                      }
	         }
	      else {
	         $valor_dolar=$resultado->fields["valor_dolar"];
	         $monto_pagado=$monto*$valor_dolar;
	         $valor_dolar=number_format($valor_dolar,"2",".","");
	         $total_monto_pagado+=$monto_pagado;
	         $monto_pagado=number_format($monto_pagado,"2",".","");
	         if ($resultado->fields["númeroch"]){
	                                     $nro_cheque=$resultado->fields['númeroch'];
	                                     echo "<td width='25%'>Cheque       </td>";
	                                     echo "<td align='center' width='25%'> $nro_cheque </td>";
	                                     echo "<td align='center' width='10%'> $simbolo $monto      </td>";
	                                     echo "<td align='center' width='10%'> $valor_dolar </td>";
	                                     echo "<td align='center' width='10%'> \$ $monto_pagado </td>";
	                                     echo "<td align='center' width='20%'> $fecha       </td>";
	                                     }
	        if ($resultado->fields["id_ingreso_egreso"]){
	                                     $nro_ingreso=$resultado->fields['id_ingreso_egreso'];
	                                     echo "<td width='25%'>Efectivo       </td>";
	                                     echo "<td align='center' width='25%'> $nro_ingreso </td>";
	                                     echo "<td align='center' width='10%'> $simbolo $monto       </td>";
	                                     echo "<td align='center' width='10%'> $valor_dolar </td>";
	                                     echo "<td align='center' width='10%'> \$ $monto_pagado </td>";
	                                     echo "<td align='center' width='20%'> $fecha       </td>";
	                                     }
	        if ($resultado->fields["iddébito"])  {
	                                     $nro_debito=$resultado->fields['iddébito'];
	                                     echo "<td width='25%'>Transferencia       </td>";
	                                     echo "<td align='center' width='25%'> $nro_debito </td>";
	                                     echo "<td align='center' width='10%'> $simbolo $monto      </td>";
	                                     echo "<td align='center' width='10%'> $valor_dolar </td>";
	                                     echo "<td align='center' width='10%'> $simbolo $monto_pagado </td>";
	                                      echo "<td align='center' width='20%'> $fecha       </td>";
	                                       }
	         } //del else
	    echo "</tr>";
	}//del if grandote donde estan todos los |||||
	$resultado->MoveNext();
	}//fin del for

	$total_monto=number_format($total_monto,"2",".","");
	$total_monto_pagado=number_format($total_monto_pagado,"2",".","");
	if ($cantidad_pagos!=0){
	                 echo "<tr>";
	                 echo "<td width='25%'>Pagos</td>";
	                 echo "<td align='center' width='25%'>". $cantidad_pagos ."</td>";
	                 echo "<td align='center' width='25%'> $simbolo $total_monto </td>";

	 if (!($moneda)) echo "<td width='25%'>&nbsp; </td>";
	 else {          echo "<td align='center' width='25%'> \$ $total_monto_pagado</td>";
	                 echo "<td width='25%'>&nbsp; </td>";
	          }
	                 echo "</tr>";
	}
	?>
	</tr>
	</table>
	<?
}//de function resumen_excel($nro_orden,$simbolos)

//==================================================================
//				funcion para mostrar el limite de credito
//==================================================================
function credito_proveedor($id_proveedor)
{
 global $db;
 global $pago_estandar;

//selecciono valor dolar
  $sql_dolar="select valor from general.dolar_general";
  $res_dolar=sql($sql_dolar,"dolar") or fin_pagina();
  $valor_dolar=$res_dolar->fields['valor'];
  $dolar=number_format($valor_dolar,'2','.','');
  $title="dolar=$dolar";
  $hoy = date("Y-m-d H:i:s",mktime());

//CHEQUES PENDIENTES
  $sql="select sum (importech) as suma from cheques where idprov=$id_proveedor and fechadébch is null";
  //and '$hoy' < fechavtoch
  $result=sql($sql,"Error calculando el credito disponible") or fin_pagina();

  $suma = 0;
  $chq_pendiente=0;
  if ($result -> RecordCount() > 0) {
       $suma=$result->fields["suma"];
       $chq_pendiente= $result->fields["suma"] / $valor_dolar;
  }

//CREDITO PROVEEDOR
   $sql="Select credito_proveedor.*,moneda.simbolo,plantilla_pagos.descripcion,id_plantilla_pagos from credito_proveedor join moneda using(id_moneda) join plantilla_pagos using(id_plantilla_pagos) where id_proveedor=$id_proveedor";
   $result_credito = sql($sql,"No se pudo cargar el credito del proveedor".$sql) or fin_pagina();
   $limite_credito = $result_credito->fields["limite"];
   $pago_estandar = $result_credito->fields["descripcion"];
   $id_plantilla_pago = $result->fields["id_plantilla_pagos"];
   $id_mon_credito=$result_credito->fields["id_moneda"];
   $simbolo_mon_credito=$result_credito->fields["simbolo"];

//ORDENES ENVIADAS

   $sql="select id_moneda,sum(precio_unitario*cantidad)
         from compras.fila join compras.orden_de_compra using(nro_orden)
         where compras.orden_de_compra.estado='e' and
         compras.orden_de_compra.id_proveedor=$id_proveedor
         group by compras.orden_de_compra.id_moneda";
   $result=sql($sql,"Error calculando el monto de ordenes enviadas") or fin_pagina();
   if ($result->RecordCount()==0)
      $monto_ordenes_enviadas=0;
   else
    {
    	while(!$result->EOF) {
  		$moneda = $result->fields["id_moneda"];
  		$monto = number_format($result->fields["sum"],2,'.','');
  		switch ($moneda) {
  			case 1:{
  				$monto_pesos= $result->fields["sum"];
  				$monto_pesos_a_dol= $result->fields["sum"] /  $valor_dolar;

  			}
  			break;
  			case 2:$monto_dol=$result->fields["sum"];
  			break;
  		}
  		$result->MoveNext();
    	}
    }

    $monto_ordenes_enviadas=$monto_pesos_a_dol + $monto_dol;

    if ($result_credito -> RecordCount() >0) {

 	?>
     <table align="center">
	   <tr >
	     <td align='right'> Limite de Crédito Disponible: </td>
	     <td>
	    <? //el limite de credito disponible se muestra en dolares
         if ($id_mon_credito== 2) {
    	 $limite= $limite_credito -  $chq_pendiente - $monto_ordenes_enviadas;
	     }
	     else {
  	  	 $limite= ($limite_credito / $valor_dolar) - $chq_pendiente - $monto_ordenes_enviadas;
	     }
  	  	 if ($limite < 0) $rojo="style='color:red'";
  	  	 //if ($limite_credito==0) echo "<strong>Sin limite</strong>";
         //else {
  		    echo "<strong $rojo> U\$S ".number_format($limite,2,'.','')."</strong>";
        // }

  	    ?>
	   </td>

	  </tr>
	   <tr align="center">
	     <td > Crédito:
  	    <?=$simbolo_mon_credito." ".number_format($limite_credito,2,'.','') ?></td>
  	      <td > Cheques Pendientes:
	      <? echo "$ ". number_format($suma,'2','.','')?></td>
	   </tr>
	   <tr>
	     <td title='<?=$title?>'> Monto en ordenes enviadas para este proveedor:</td>
	     <td> <? if ($monto_ordenes_enviadas==0) echo"<strong>Ninguno</strong>";
                 else {
    	         echo "  $ ".number_format($monto_pesos,2,'.','');
  	             echo "    U\$S ".number_format($monto_dol,2,'.','');
                  }?>
         </td>
	   </tr>
	   <tr>
	   <td colspan="2" align="center"> <? echo "Forma de pago estándar <strong>'$pago_estandar'</strong></center>"?></td>
	   </tr>
     </table>
 	 <?
  }
  	echo "<input type='hidden' name='id_plantilla_pago_js' value='$id_plantilla_pago'>";
}//de function credito_proveedor($id_proveedor)

//guarda los datos anteriores a un cambio en forma de pago para mantener un log
//en el esquema compras_adicional
//Se invoca desde el archivo ord_pagos cuando se presiona el boton aceptar o se sepraran ordenes de pago multiple
function guardar_forma_pago_ant($tipo_log)
{
	global $nro_orden,$_ses_user;

	//$print_r($nro);die();
	//ordenes asociadas al pago multiple
	$orden_pm=PM_ordenes($nro_orden[0]);
	$cant_orden_pm=sizeof($orden_pm);

	for ($i=0;$i<($cant_orden_pm);$i++) {
	$sql="SELECT nextval('compras_adicional.log_pagos_oc_id_log_pago_seq') as id";
	$res=sql($sql) or fin_pagina();
	$id_log=$res->fields['id'] or fin_pagina();

	//pagos
	$sql_log="";
	$valores=descomprimir_variable($_POST['datos_forma']);

	$fecha=date("Y-m-d H:i:s",mktime());
	$usuario=$_ses_user['name'];
	$titulo=$valores['titulo'];
	$cant_pagos=$valores['cant'];
	$sql_log=" insert into log_pagos_oc (id_log_pago,nro_orden,usuario,fecha,tipo_log,titulo_forma_pago,mostrar,cant_pagos) values
	       ($id_log,".$nro_orden[$i].",'$usuario','$fecha','$tipo_log','$titulo',".$valores['mostrar'].",$cant_pagos)";
	sql($sql_log) or fin_pagina();

	//datos de la orden de compra
	if ($_POST['id_moneda']==1) $simbolo='$';
	else $simbolo='U\$S';
	if ($_POST['id_licitacion']) $id_lic=$_POST['id_licitacion'];
	else $id_lic='NULL';
	if ($cant_orden_pm > 1)
	  $boton_separar=1; //indica que tengo que mostrar el boton separar
	  else $boton_separar=0;
	if ($valores['monto']) $monto=$valores['monto'];
	   else $monto='NULL';
	$sql="insert into datos_orden (id_log_pago,proveedor,fecha_entrega,cliente,nro_lic,simbolo_orden,boton_separar_orden,monto)
	       values ($id_log,'".$_POST['proveedor']."','".fecha_db($_POST['fecha'])."','".$_POST['cliente']."',".$id_lic.",'$simbolo',$boton_separar,".$monto.")";
	sql($sql) or fin_pagina();

	$sql_det="";
	$valores_pagos=descomprimir_variable($_POST['datos_pagos']);
	$cant_pagos=count($valores_pagos);

	for ($j=1;$j<=$cant_pagos; $j++) {

	if ($valores_pagos[$j]['monto'])
	 $monto_pagos=$valores_pagos[$j]['monto'];
	 else $monto_pagos='NULL';
	 if ($valores_pagos[$j]['dolar']) $dolar=$valores_pagos[$j]['dolar'];
	 else $dolar='NULL';
	  $sql_det[]=" insert into detalle_pagos (id_log_pago,id_tipo_pago,cant_dias,monto,valor_dolar,simbolo,pagada) values
	    ($id_log,".$valores_pagos[$j]['tipo'].",".$valores_pagos[$j]['dias'].",".$monto_pagos.",".$dolar.",'".$valores_pagos[$j]['simbolo']."',".$valores_pagos[$j]['pagada']." ) ";
	}
	sql($sql_det) or fin_pagina();


	//notas de credito
	if ($_POST['datos_nc'])
	   	$valores_nc=descomprimir_variable($_POST['datos_nc']);
	$cant_nc=count($valores_nc);
	$sql_notas="";
	for ($j=0;$j<$cant_nc;$j++)  {
	  $sql_notas[]="insert into detalle_notas_credito
	  (id_log_pago,nro_nc,monto,valor_dolar,observaciones,simbolo,chk)
	  values  ($id_log,'".$valores_nc[$j]['id_nota']."',".$valores_nc[$j]['monto'].",".$valores_nc[$j]['dolar'].",'".$valores_nc[$j]['obs']."','".$valores_nc[$j]['simbolo']."',".$valores_nc[$j]['chk'].")";
	}
	if ($sql_notas!="") sql($sql_notas) or fin_pagina();


	 //guardo las ordenes asociadas al pago múltiple
	   $sql_orden_pm="";
	   $total_pagar=0;
	   $orden_pm=PM_ordenes($nro_orden[0]);
	   $cant_orden_pm=sizeof($orden_pm);
	   if ($cant_orden_pm >1) {
	   for ($k=0;$k<$cant_orden_pm;$k++){
	   	   $pm=$orden_pm[$k];
	       $m=monto_a_pagar($pm);
	       $sql_orden_pm[]=" insert into detalle_orden_pm (id_log_pago,nro_orden,monto) values ($id_log,$pm,$m)";
	   }
	   }

	 if ($sql_orden_pm!="") sql($sql_orden_pm) or fin_pagina();
	 }
}//de function guardar_forma_pago_ant($tipo_log)


/********************************************************************/
//guarda los datos anteriores a un cambio en forma de pago para mantener un log
//en el esquema compras_adicional
//Se invoca desde el archivo proc_compras cuando se presiona el boton autorizar y el montoce de los pagos estaba nulo
//cuando se autoriza una orden y el monto es nulo se completa la forma de pago entonces guardo el log
function guardar_fp_ant($tipo_log,$nro_orden,$dolar="")
{
	global $db,$_ses_user;

	$db->StartTrans();

	//ordenes asociadas al pago multiple
	$orden_pm=PM_ordenes($nro_orden);
	$cant_orden_pm=sizeof($orden_pm);

	$sql="select dias,descripcion ,mostrar,id_licitacion,
	simbolo ,razon_social,id_proveedor,
	fecha_entrega,cliente,id_tipo_pago,monto
	from
	compras.forma_de_pago join compras.pago_plantilla using (id_forma)
	join compras.plantilla_pagos using(id_plantilla_pagos)
	join compras.orden_de_compra using (id_plantilla_pagos)
	join licitaciones.moneda using (id_moneda)
	join general.proveedor using (id_proveedor)
	left join compras.ordenes_pagos using (id_forma)
	where nro_orden=".$nro_orden." order by id_forma";

	$datos_orden=sql($sql,"<br>Error al traer los datos pagos de la OC (guardar_fp_ant)<br>") or fin_pagina();
	$cant_pagos=$datos_orden->RecordCount();
	$id_proveedor=$datos_orden->fields['id_proveedor'];


	$sql="SELECT nextval('compras_adicional.log_pagos_oc_id_log_pago_seq') as id";
	$res=sql($sql,"<br>Error al traer la secuencia de pagos (guardar_fp_ant)<br>") or fin_pagina();
	$id_log=$res->fields['id'] or fin_pagina();

	//pagos
	$sql_log="";

	$fecha=date("Y-m-d H:i:s",mktime());
	$usuario=$_ses_user['name'];
	$titulo=$datos_orden->fields['descripcion'];
	$mostrar=$datos_orden->fields['mostrar'];
	$sql_log=" insert into log_pagos_oc (id_log_pago,nro_orden,usuario,fecha,tipo_log,titulo_forma_pago,mostrar,cant_pagos) values
	       ($id_log,".$nro_orden.",'$usuario','$fecha','$tipo_log','$titulo',".$mostrar.",$cant_pagos)";
	sql($sql_log,"<br>Error al insertar en log de pagos de oc (guardar_fp_ant)<br>") or fin_pagina();

	//datos de la orden de compra
	$simbolo=$datos_orden->fields['simbolo'];

	if ($datos_orden->fields['id_licitacion']) $id_lic=$datos_orden->fields['id_licitacion'];
	else $id_lic='NULL';

	if ($cant_orden_pm > 1)
	  $boton_separar=1; //indica que tengo que mostrar el boton separar
	  else $boton_separar=0;

	$monto='NULL';
	$monto=monto_a_pagar($nro_orden);

	$sql="insert into datos_orden (id_log_pago,proveedor,fecha_entrega,cliente,nro_lic,simbolo_orden,boton_separar_orden,monto)
	       values ($id_log,'".$datos_orden->fields['razon_social']."','".$datos_orden->fields['fecha_entrega']."','".$datos_orden->fields['cliente']."',".$id_lic.",'$simbolo',$boton_separar,".$monto.")";
	sql($sql,"<br>Error al insertar los datos de la OC (guardar_fp_ant)<br>") or fin_pagina();

	$j=0;
	while (!$datos_orden->EOF) {
	$monto='NULL';
	if (is_array($dolar) && $dolar[$j] !="" )
	 $d=$dolar[$j];
	else $d='NULL';

		$sql_det[]="insert into detalle_pagos (id_log_pago,id_tipo_pago,cant_dias,monto,valor_dolar,simbolo,pagada) values
	    ($id_log,".$datos_orden->fields['id_tipo_pago'].",".$datos_orden->fields['dias'].",".$monto.",".$d.",'$simbolo',0)";
	$j++;
	$datos_orden->MoveNext();
	}
	sql($sql_det,"<br>Error al insertar el detalle de los pagos de la OC (guardar_fp_ant)<br>") or fin_pagina();

	//recupero las notas de credito

	$query="select id_nota_credito,nota_credito.monto,nota_credito.estado,
	        n_credito_orden.nro_orden,n_credito_orden.valor_dolar,nota_credito.observaciones,simbolo
	        from general.nota_credito join licitaciones.moneda using (id_moneda)
	        left join compras.n_credito_orden using(id_nota_credito)
	        where id_proveedor=$id_proveedor and ((estado=1 and (nro_orden=$nro_orden) or estado=0))";
	$notas_credito=sql($query,"<br>Error al traer los datos de las notas de creditos<br>") or fin_pagina();
	while (!$notas_credito->EOF) {
	if ($notas_credito->fields['nro_orden'] == 'null' || $notas_credito->fields['nro_orden'] == "" )
		$chk=1;
		else $chk=0;
	if ($notas_credito->fields['valor_dolar'])
	    $dolar=$notas_credito->fields['valor_dolar'];
	    else $dolar='NULL';
	$sql_notas[]="insert into detalle_notas_credito
	      (id_log_pago,nro_nc,monto,valor_dolar,observaciones,simbolo,chk)
	 values  ($id_log,'".$notas_credito->fields['id_nota_credito']."',".$notas_credito->fields['monto'].",".$dolar.",'".$notas_credito->fields['observacion']."','".$notas_credito->fields['simbolo']."',$chk)";

	$notas_credito->MoveNext();
	}
	if ($sql_notas!="")
	 sql($sql_notas,"<br>Error al insertar el detalle de las notas de credito<br>") or fin_pagina();

	$db->CompleteTrans();
}//de function guardar_fp_ant($tipo_log,$nro_orden,$dolar="")

function mail_entregar_sin_cb ($filas,$internacional=0)
{
 global $_ses_user;
 $list='(';
 for ($i=0; $i< count($filas);$i++)
 {
   $ind=array_search($filas[$i],$filas);
 	$list.=$filas[$i].',';
 }
 $list=substr_replace($list,')',(strrpos($list,',')));

 $sql="select descripcion_prod,nro_orden,desc_adic from compras.fila where id_fila in $list";
 $res=sql($sql) or fin_pagina();
 $nro_orden=$res->fields['nro_orden'];

 if($internacional)
  $text_int="Internacional";
 else
  $text_int="";

 $contenido= "Se autorizo la entrega de productos sin código de barra para la Orden de Compra $text_int Nº ".$nro_orden.".\n Los productos que se autorizaron a entregar sin Códigos de Barra son:\n";

 while (!$res->EOF)
 {
   $contenido.="- ".$res->fields['descripcion_prod']." ".$res->fields['desc_adic']."\n";
   $res->MoveNext();
 }

 $contenido.=" \n Fecha de autorización: ".date("d/m/Y")."
 Usuario que realizó la operación: ".$_ses_user['name'];
 $contenido.="\n \n";
 $contenido.="DETALLE DE LA ORDEN DE COMPRA \n";
 $contenido.=detalle_orden($nro_orden);


 $para='juanmanuel@coradir.com.ar';

 $asunto="Autorización de entrega de productos sin Códigos de Barra, para la OC $text_int Nº $nro_orden";

 enviar_mail($para,$asunto,$contenido,'','','',0);
}//de function mail_entregar_sin_cb ($filas)


function anular_oc($nro_orden,$internacional=0)
{
  global $es_stock,$select_proveedor,$db,$_ses_user,$nrocaso,$ordprod,$modo;
 $db->StartTrans();

 $q="update orden_de_compra set estado='n' where nro_orden=$nro_orden";

  //(ESTO SOLO SE HACE SI EL PROVEEDOR NO ES STOCK)
  if(!$es_stock)
  {
	//desafectamos las notas de credito que estaban relacionadas con la orden de compra
	//y las volvemos a estado pendientes
	//seleccionamos las notas de credito que se relacionan con las ordenes de compras
    $query="select id_nota_credito from (select * from n_credito_orden where nro_orden=$nro_orden) as oc join nota_credito using(id_nota_credito) join moneda using(id_moneda)";
    $notas_credito=sql($query,"<br>Error al traer las notas de credito relacionadas con la orden de compra $nro_orden<br>") or fin_pagina();

    while(!$notas_credito->EOF)
    {//Volvemos las notas de creditos que estan relacionadas con las ordenes de compra a
     //estado pendientes
     $query="update nota_credito set estado=0 where id_nota_credito=".$notas_credito->fields['id_nota_credito'];
     sql($query,"<br>Error al actualizar estado de nota de credito a pendientes.Nota de credito".$notas_credito->fields['id_nota_credito']."<br>") or fin_pagina();

     //borramos todas las entrada que relacionan ordenes de compra con cada nota de credio
     $query="delete from n_credito_orden where id_nota_credito=".$notas_credito->fields['id_nota_credito'];
     sql($query,"<br>Error al borrar la entrada de  nota de credito y orden de compra. Nota de credito".$notas_credito->fields['id_nota_credito']."<br>") or fin_pagina();

     $notas_credito->MoveNext();
    }//de while(!$notas_credito->EOF)
  }//de if(!$es_stock)
  else//el proveedor es stock
  {
  	//traemos el id del deposito seleccionado como stock
  	//primero obtenemos el nombre del proveedor seleccionado
    $query="select razon_social from proveedor where id_proveedor=$select_proveedor";
    $id_proveedor=sql($query,"<br>Error al traer el nombre del proveedor.<br>") or fin_pagina();
    switch($id_proveedor->fields['razon_social'])
    {case "Stock San Luis":$dep="San Luis";break;
     case "Stock Buenos Aires":$dep="Buenos Aires";break;
     case "Stock ANECTIS":$dep="ANECTIS";break;
     case "Stock SICSA":$dep="SICSA";break;
     case "Stock New Tree":$dep="New Tree";break;
     case "Stock Virtual":$dep="Virtual";break;
     case "Stock Serv. Tec. Bs. As.":$dep="Serv. Tec. Bs. As.";break;
    }

    //se selecciona el id del deposito
    $query="select id_deposito from depositos where nombre='$dep'";
    $id_dep=sql($query,"<br>Error al traer id del deposito<br>") or fin_pagina();
    //cancelamos las reservas hechas por la OC
  	descontar_reservados($nro_orden,$id_dep->fields['id_deposito'],1);
  }//del else de if(!$es_stock)


  sql($q,"<br>Error al anular la OC<br>")  or fin_pagina();

  //eliminamos las entradas generadas en RMA por la OC, si es el caso
  //borrar_rma_x_anular_oc($nro_orden);

  //agreagamos el registro de anulacion de la OC, en el log
  $f1=date("Y-m-d H:i:s",mktime());
  $comentario_anular=$_POST['comentario_anular'];
  $q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha,otros) values ($nro_orden,'de anulacion','".$_ses_user["login"]."','$f1','$comentario_anular')";
  sql($q,"<br>Error al insertar el log de la OC (anulación)<br>") or fin_pagina();

  $msg="Su Orden Nº $nro_orden se actualizo exitosamente";


/***************************************************************
Mercaderia en Tránsito
****************************************************************/
        //se deben descontar de stock los productos de la OC que esten en
	    //mercaderia en transito (Esto es cuando la OC esta autorizada)
	  /*  if($estado=="a" || $estado=="e")
	    {//descontamos de mercaderia en transito los productos
         //solo si no esta asociada con honorario de s tecnico o  es asociada
         //con otro
         $query_as="select flag_honorario,id_licitacion,orden_prod,flag_stock,nrocaso from orden_de_compra where nro_orden=$nro_orden";
         $asociada=$db->Execute($query_as) or die($db->ErrorMsg."<br>Error al traer asociaciones de OC");
         if($asociada->fields['flag_honorario']!=1 && ($asociada->fields['id_licitacion']!="" || $asociada->fields['orden_prod']!="" || $asociada->fields['flag_stock']==1 || $asociada->fields['nrocaso']!=""))
         {
	      $items_merc_trans=get_items($nro_orden);
	      volver_atras_merc_trans($nro_orden,$items_merc_trans,$_POST['id_proveedor_a'],"Anulación");
         }
	    }*/
/***************************************************************
Mercaderia en Tránsito
****************************************************************/
 //si el modo es Orden de Servicio Tecnico no se envia el mail avisando que se anulo la Orden
  if($modo!="oc_serv_tec")
  {
    if($internacional)
     $texto_int="Internacional";
    else
     $texto_int="";

    //armamos el mail para enviar avisando que se anuló la 	OC
	    $asunto="CORADIR:Orden de compra $texto_int Nº $nro_orden ANULADA";
		$texto="La orden de Compra $texto_int Nº:$nro_orden ha sido ANULADA\n\n".detalle_orden($nro_orden,0);
		$texto.="Usuario: ".$_ses_user["name"]."\n" ;
		$texto.="Fecha y hora: ".fecha($f1)." ".Hora($f1)."\n" ;

	//envio mails a las personas que se les envio el mail sobre esta orden
	$sql="select para from ord_compra_mails where nro_orden=$nro_orden";
    $resultado_mail=sql($sql,"<br>Error al traer los destinatarios de anulación<br>") or fin_pagina();

    $db->CompleteTrans();

    enviar_mail($resultado_mail->fields['para'],$asunto,$texto,"","","",0);
  }//de if($modo=="oc_serv_tec")

}//de function anular_oc($nro_orden)


///////////////////////////////PONER FUNCION PARA ENVIAR MAIL CUANDO RECIBO PRODUTOS////////////////////////////
function mail_recibe_productos($nro_orden,$items) {
 global $_ses_user;
 $retorno_funcion=busca_segundo_nivel($items,"cantidad",0,">");
 if (count($retorno_funcion)>0) {
 	  $sql="select mail as mail_autor,responsable,logs.user_login,nro_orden,id_licitacion,o.es_presupuesto,o.flag_stock,o.nrocaso,o.orden_prod,o.flag_honorario,respon.mail_res,
            o.internacional,lider,patrocinador,m_l.mail_lider as mail_lider,m_p.mail_patro as mail_patrocinador
            from compras.orden_de_compra as o
            left join licitaciones.licitacion using (id_licitacion)
            left join (select user_login,orden.nro_orden from compras.orden_de_compra as orden
            left join compras.log_ordenes  as log on (orden.nro_orden=log.nro_orden and orden.fecha=log.fecha)
            ) as logs using (nro_orden)
            left join licitaciones.entrega_estimada using (id_licitacion)
            left join sistema.usuarios on (user_login=login)
            left join (select mail as mail_res, usuarios.nombre||' '||usuarios.apellido as responsable from sistema.usuarios) as respon  using(responsable)
            left join (select mail as mail_lider,id_usuario from sistema.usuarios) as m_l on (m_l.id_usuario=lider)
            left join (select mail as mail_patro,id_usuario from sistema.usuarios) as m_p on (m_p.id_usuario=patrocinador)
            where nro_orden=$nro_orden";
     $resul_sql=sql($sql,"No se pudo ejecutar la consulta $sql en la funcion mail_recibe_productos") or fin_pagina();
     $internacional=$resul_sql->fields["internacional"];
     
     if($internacional)  {
      $text_int="Internacional";
      $text_int_mayusc="INTERNACIONAL";
     }
     else {
      $text_int="";
      $text_int_mayusc="";
     }
     $para=$resul_sql->fields['mail_autor'];
     $asunto="Recepción de Productos Orden de Compra $text_int Nº $nro_orden. Falta la confirmación de estas recepciones.";
     $mensaje="RECEPCIÓN DE PRODUCTOS DE LA ORDEN DE COMPRA $text_int_mayusc Nº $nro_orden.\n";
     $mensaje.="ES NECESARIO CONFIRMAR ESTAS RECEPCIONES PARA QUE LOS PRODUCTOS INGRESEN AL STOCK\n\n";
     $mensaje.="Productos:";
     $mensaje.="\n--------------------------------------------------------------------------------------------------------------";
     $mensaje.="\nCant.    Cant. ";
     $mensaje.="\nPedida   Recibida   Descripción                                 Depósito                             ";
     $mensaje.="\n--------------------------------------------------------------------------------------------------------------";
     $indice=0;
     while ($indice<count($retorno_funcion)) {
            $id_fila=$items[$retorno_funcion[$indice]]['id_fila'];
            $cantidad=$items[$retorno_funcion[$indice]]['cantidad'];
            $id_deposito=$items[$retorno_funcion[$indice]]['id_deposito'];
            $sql="select nombre,tipo from general.depositos where id_deposito=$id_deposito";
            $resul_deposito=sql($sql,"No se pudo realizar la consulta del deposito") or fin_pagina();
           	$sql="select cantidad,descripcion_prod,desc_adic from compras.fila where id_fila=$id_fila";
            $resul_filas=sql($sql,"No se pudo realizar la consulta de las filas") or fin_pagina();
            $mensaje.="\n  ".$resul_filas->fields['cantidad']."     $cantidad              ".$resul_filas->fields['descripcion_prod']."  ".$resul_filas->fields['desc_adic']."                                  ".$resul_deposito->fields['nombre'];
           	$indice++;
     }
     $mensaje.="\n--------------------------------------------------------------------------------------------------------------";

     $user=$_ses_user['name'];
     $fecha_hoy=date("d/m/Y",mktime());
     $mensaje.="\nLos productos los recibió $user, el día $fecha_hoy.";
     $mensaje.="\n\n---------------------------------------------------------------";
     $mensaje.="\n".detalle_orden($nro_orden);
     
    // print_r("Id Licitacion".$resul_sql->fields['id_licitacion']);
     //print_r("es_presupuesto".$resul_sql->fields['es_presupuesto']);
     if ($resul_sql->fields['id_licitacion']!='' && $resul_sql->fields['es_presupuesto']==1)
        {
         $mail=array();
         $indice=0;
         $mail[$indice]=$para;
         if ($resul_sql->fields['mail_res']!="") {
         	 $mail[$indice]=$resul_sql->fields['mail_res'];
             $indice++;
            }
         if ($resul_sql->fields['mail_lider']!="") {
         	 $mail[$indice]=$resul_sql->fields['mail_lider'];
             $indice++;
            }
         if ($resul_sql->fields['mail_patrocinador']!="") {
         	 $mail[$indice]=$resul_sql->fields['mail_patrocinador'];
             $indice++;
            }
         
         $para=elimina_repetidos($mail,0);
        }//HASTA ACA ES PARA CUANDO ES PRESUPUESTO
        elseif ($resul_sql->fields['id_licitacion']!='') {
     	     $mail=array();
             $indice=0;
             $mail[$indice]=$para;
             $indice++;
             if ($resul_sql->fields['mail_res']!="") {
             	 $mail[$indice]=$resul_sql->fields['mail_res'];
                 $indice++;
                }
             if ($resul_sql->fields['mail_lider']!="") {
             	 $mail[$indice]=$resul_sql->fields['mail_lider'];
                 $indice++;
                }
             if ($resul_sql->fields['mail_patrocinador']!="") {
             	 $mail[$indice]=$resul_sql->fields['mail_patrocinador'];
                 $indice++;
                }
            $para=elimina_repetidos($mail,0);
            }
     if ($resul_sql->fields['flag_honorario']==1) {
     	$para="";
        }
     if ($resul_sql->fields['flag_stock']==1) {
     	 $mail[0]=$para;
         $mail[1]="juanmanuel@coradir.com.ar";
         $mail[2]="vetrano@coradir.com.ar";
         $mail[3]="adrian@coradir.com.ar";
         $mail[4]="sebastian@coradir.com.ar";
         $para=elimina_repetidos($mail,0);
        }
  if ($para!="") {
    	$para=str_replace("corapi@coradir.com.ar","",$para);
    	$para=$para.",valentino@coradir.com.ar";
    	enviar_mail($para,$asunto,$mensaje,"","","",0);
    }
  }
}//de la funcion que envia mail cuando se reciben productos en una orden de compra


/*********************************************************************************************
Funcion que elimina el RMA que la OC pasada como parametro, haya generado, cuando se autorizo.
**********************************************************************************************/
function borrar_rma_x_anular_oc($nro_orden){
	
 global $db;
 $query="select id_info_rma,id_deposito,id_producto,id_proveedor,cantidad
         from info_rma
         where nro_ordenc=$nro_orden";
 $datos_rma=sql($query,"<br>Error al traer datos de RMA a borrar<br>") or fin_pagina();

 while (!$datos_rma->EOF)
 {
 	$id_info_rma=$datos_rma->fields["id_info_rma"];
 	$id_producto=$datos_rma->fields["id_producto"];
 	$id_proveedor=$datos_rma->fields["id_proveedor"];
 	$id_deposito=$datos_rma->fields["id_deposito"];
 	$cantidad=$datos_rma->fields["cantidad"];

   //borramos los datos de las tablas relacionadas directamente con info_rma
   //log_cambio_prod
   $query="delete from log_cambio_prod where id_info_rma=$id_info_rma";
   sql($query,"<br>Error al eliminar de log de cambio de producto<br>") or fin_pagina();
   //log_ubicacion
   $query="delete from log_ubicacion where id_info_rma=$id_info_rma";
   sql($query,"<br>Error al eliminar del log de ubicacion<br>") or fin_pagina();
   //archivos_subidos
   $query="delete from archivos_subidos where id_info_rma=$id_info_rma";
   sql($query,"<br>Error al eliminar de los archivos subidos<br>") or fin_pagina();

   //borramos de la tabla info_rma la entrada correspondiente
   $query="delete from info_rma where id_info_rma=$id_info_rma";
   sql($query,"<br>Error al eliminar la informacion de RMA<br>") or fin_pagina();

   //descontamos la cantidad que se habia insertado en la tabla stock y generamos el correspondiente descuento en la tabla
   //stock
   $query="update stock set cant_disp=cant_disp-$cantidad
    where id_deposito=$id_deposito and id_producto=$id_producto and id_proveedor=$id_proveedor";

   //control_stock, descuento y log_stock
   //obtenemos el id_control_stock para poder  eliminar las entradas en estas 3 tablas
   $query="select id_control_stock from descuento where id_info_rma=$id_info_rma";
   $control_stock=sql($query,"<br>Error al traer el id de control de stock<br>") or fin_pagina();
   $id_control_stock=$control_stock->fields["id_control_stock"];

   //log_stock
   $query="delete from log_stock where id_control_stock=$id_control_stock";
   sql($query,"<br>Error al eliminar del log de stock<br>") or fin_pagina();
   //descuento
   $query="delete from descuento where id_control_stock=$id_control_stock";
   sql($query,"<br>Error al eliminar de los descuentos de stock<br>") or fin_pagina();
   //control_stock
   $query="delete from control_stock where id_control_stock=$id_control_stock";
   sql($query,"<br>Error al eliminar del control de stock<br>") or fin_pagina();

  $datos_rma->MoveNext();
 }//de while(!$datos_rma->EOF)

 $db->CompleteTrans();

}//de function borrar_rma_x_anular_oc($nro_orden)



function autorizar_oc($nro_orden,$modo,$select_proveedor,$es_stock=0,$internacional=0)
{
	global $db,$msg,$_ses_user;
	//variables globales propias de la pagina ord_compra.
	global $accion,$destino_para_autorizar,$montos_nc,$select_moneda,$valor_dolar;

	$db->StartTrans();
    $msg="";
    $f1=date("Y-m-d H:i:s",mktime());
   if($modo!="oc_serv_tec")
   {
    $cons_check="select chequeado_avisar from compras.orden_de_compra where nro_orden=$nro_orden";
    $res_check=sql($cons_check,"<br>Error al traer valor check<br>") or fin_pagina();
    $avisar=$res_check->fields['chequeado_avisar'];
    if ($avisar==1)
    {
     //llamar a la funcion enviar_mail a los mails que se chequeron en la orden de compra
     //para avisarles que tienen que retirar la orden por coradir
     $usuarios="select id_usuario, mail from sistema.usuarios where avisar_oc=1";
     $res_usuarios=sql($usuarios,"<br>Error al traer usuarios<br>") or fin_pagina();
     $res_usuarios->Move(0);

     if($internacional)
      $texto_int="Internacional";
     else
      $texto_int="";
     $asunto="Retirar por Coradir Orden de Compra $texto_int Nº: ".$nro_orden;
     while (!$res_usuarios->EOF)
     {
        if ($mail_.$res_usuarios->fields['id_usuario'])
        {
         	$mail=$res_usuarios->fields['mail'];
         	if ($mail)
     	      $para.="$mail, ";
        }//de if ($mail_.$res_usuarios->fields['id_usuario'])
      $res_usuarios->MoveNext();
     }//de while (!$res_usuarios->EOF)
     $para=substr($para, 0 ,strlen($para)-2);

     $contenido=$texto_avisar_oc;
     enviar_mail($para, $asunto, $contenido, $adjunto, $path, $tipo, $adj);
    }//de if ($avisar==1)
   }//de if($modo!="oc_serv_tec")
/***************************************************************
Mercaderia en Tránsito
****************************************************************/
    //Si la orden es asociada a licitacion, presupuesto, C.A.S.,
    //     Stock o RMA de Produccion
    //se agregan los productos de la OC a mercaderia en transito.
    //(Estos saldran de mercaderia en transito cuando se especifique
    //recibidos en el boton terminar)
    /*if($licitacion || $caso || ($orden_prod && $orden_prod!="NULL") || $flag_stock)
     pasar_a_merc_trans($nro_orden);*/
/***************************************************************
Mercaderia en Tránsito
****************************************************************/
    //si el proveedor es un stock
    if($es_stock)
    {$estado='g';

      //primero obtenemos el nombre del proveedor seleccionado
      $query="select razon_social from proveedor where id_proveedor=$select_proveedor";
      $id_proveedor=sql($query,"<br>Error al traer el nombre del proveedor.<br>") or fin_pagina();
      switch($id_proveedor->fields['razon_social'])
      {case "Stock San Luis":$dep="San Luis";break;
       case "Stock Buenos Aires":$dep="Buenos Aires";break;
       case "Stock ANECTIS":$dep="ANECTIS";break;
       case "Stock SICSA":$dep="SICSA";break;
       case "Stock New Tree":$dep="New Tree";break;
       case "Stock Virtual":$dep="Virtual";break;
       case "Stock Serv. Tec. Bs. As.":$dep="Serv. Tec. Bs. As.";break;
      }

      //se selecciona el id del deposito
      $query="select id_deposito from depositos where nombre='$dep'";
      $id_dep=sql($query,"<br>Error al traer id del deposito<br>") or fin_pagina();

/***************************************************************
Mercaderia en Tránsito
****************************************************************/
      //descontamos del stock seleccionado como proveedor
      //descontar_stock($id_dep->fields['id_deposito']);
      //eliminamos los productos reservados del stock
      //(descontamos los productos en la tabla reservados)
      //descontar_reservados($nro_orden,$id_dep->fields['id_deposito']);
/***************************************************************
Mercaderia en Tránsito
****************************************************************/
    //no se envia el mail avisando el movimiento de stock si es una Orden de Servicio Tecnico
     if($modo!="oc_serv_tec")
     {
      /*enviar mail con movimientos de stock */
      if ($id_proveedor->fields['razon_social'] =="Stock San Luis")
         $para='cristian@pcpower.com.ar';
       else
       {  $para='aranguren@coradir.com.ar,dalsanto@coradir.com.ar,valentino@coradir.com.ar,benitez@coradir.com.ar';

       }
       if ($id_proveedor->fields['razon_social'] =="Stock New Tree")
        $para.=',cinthia@newtree.com.ar,nestor@newtree.com.ar';

       if($internacional)
         $texto_int="Internacional";
        else
         $texto_int="";
       $asunto="OC_$nro_orden Movimiento de stock a través de Orden de Compra $texto_int";
       if($msg=="")
        mail_stock($para,$asunto,$nro_orden);
     }//de if($modo!="oc_serv_tec")
    }//de if($es_stock)
    else
     $estado='a';

    if(($estado=='g' && $msg=="") || $estado=='a')
    { $q="update orden_de_compra set ".
		"estado='$estado' where nro_orden=$nro_orden";
	 if (sql($q,"<br>Error al actualizar el estado de la OC<br>") or fin_pagina())
	 {
		$msg="<b> La Orden Nº $nro_orden se actualizó  exitosamente";

		$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'de autorizacion','".$_ses_user['login']."','$f1')";
		sql($q,"<br>Error al insertar el log de autorización<br>") or fin_pagina();
		//include("ord_compra_pdf.php");

	 }
	 else
	 {
		$msg="<b><font color='red'> No se pudo actualizar la orden Nº $nro_orden </font>";
	 }
    }//de if(($estado='g' && $msg=="") || $estado=='a')

	//if($destino_para_autorizar=="ord_compra_listar.php" && $montos_default=="si")
if (($destino_para_autorizar=="ord_compra_listar.php") && ($accion=="dividir")&&(!$es_stock))
			{
			   	$query="select count(pago_plantilla.id_forma) as cant_pagos from orden_de_compra join plantilla_pagos using(id_plantilla_pagos) left join pago_plantilla using (id_plantilla_pagos) where nro_orden=$nro_orden";
				$count_pagos=sql($query,"<br>Error al traer la cantidad de pagos<br>") or fin_pagina();
				$nro_pagos=$count_pagos->fields['cant_pagos'];
				$monto_pago=monto_a_pagar($nro_orden);
				//tomamos en cuenta las notas de credito para la division del total
				$monto_pago-=$montos_nc;

				$una_cuota=$monto_pago/$nro_pagos;
				//echo "monto total $monto_pago, nro_pagos $nro_pagos, cuota $cuota";
				$cuotas=array($nro_pagos);
				$dolar=array($nro_pagos);
				for($i=0;$i<$nro_pagos;$i++)
				{
				   if ($i==($nro_pagos-1)) {
										   $monto_pago=number_format($monto_pago,"2",".","");
										   $una_cuota=number_format($una_cuota,"2",".","");
										   $cuotas[$i]=$una_cuota+($monto_pago-($una_cuota*$nro_pagos));
										   }
										   else{
											  $cuotas[$i]=$una_cuota;
												 }
				 $dolar[$i]=$valor_dolar;
				}//de for($i=0;$i<$nro_pagos;$i++)
 				$q="select id_moneda from moneda where nombre='Dólares'";
                $moneda=sql($q,"<br>Error al traer el id de la moneda<br>") or fin_pagina();
 				if($select_moneda==$moneda->fields['id_moneda'])
 				{
 				 insertar_ordenes_pagos($nro_orden,$cuotas,$dolar);
 				 guardar_fp_ant('Al autorizar',$nro_orden,$dolar); //guarda datos para mantener un log
 				}
 				else
 				{
 				 insertar_ordenes_pagos($nro_orden,$cuotas);
 				 guardar_fp_ant('Al autorizar',$nro_orden); //guarda datos para mantener un log
 				}
			   }//de if (($destino_para_autorizar=="ord_compra_listar.php") && ($accion=="dividir")&&(!$es_stock))
			   //sino, esto se hace en la pagina de ord_compra_pagar



	//si la orden esta asociada a un CAS o a un RMA de Produccion,
	//los productos de la OC se agregan al Stock de RMA y se actualiza la tabla info_rma
/*	if( ($caso || $orden_prod) && $generar_reclamo_parte && $gen_reclamo!=2)
	{
     //controlamos que si las variables $caso y $orden_prod son vacios,
     //le asignamos NULL para que no de error el query
	 if($caso=="")
	  $caso="NULL";
	 if($orden_prod=="")
	  $orden_prod="NULL";
	 agregar_RMA($nro_orden,$caso,$orden_prod);
	}*/

	//El mail de OC autorizada se envia solo si no se trata de una Orden de Servicio Tecnico
	if($modo!="oc_serv_tec")
	{
	 // envio mail cuando Corapi autoriza la orden a la persona que realizo la orden
	 //echo "Login de usuario:".$_ses_user_login."<br>";
	 if($internacional)
	  $texto_int="Internacional";
	 else
	  $texto_int="";
	 if (($_ses_user['login']=='corapi') || ($_ses_user['login']=='juanmanuel'))
	 { $sql="select mail from usuarios join log_ordenes on log_ordenes.user_login=usuarios.login where log_ordenes.nro_orden=$nro_orden and log_ordenes.tipo_log='de creacion'";
	   $result_auto=sql($sql,"<br>Error al traer los mails de usuarios para aviso de autorización<br>") or fin_pagina();

	   enviar_mail($result_auto->fields['mail'],"Orden de Compra $texto_int Nro: $nro_orden Autorizada","La Orden de Compra $texto_int Nro $nro_orden ha sido autorizada. Esta orden puede ser enviada",'','','',0);
	 }//de if (($_ses_user_login=='corapi') || ($_ses_user_login=='juanmanuel'))
	}//if($modo!="oc_serv_tec")

	//Si estamos en modo Orden de Servicio Tecnico, descontamos las reservas del stock Serv Tec Bs As
	if($modo=="oc_serv_tec")
	{
	  //traemos todas las filas
	  $query="select id_fila,cantidad,id_producto from fila where nro_orden=$nro_orden";
	  $filas_serv_tec=sql($query,"<br>Error al traer las filas de la Orde de Servicio Tecnico<br>") or fin_pagina();
	  $id_deposito_st=$id_dep->fields["id_deposito"];
	  while (!$filas_serv_tec->EOF)
	  {
	   $cant_st=$filas_serv_tec->fields["cantidad"];
	   $id_producto_st=$filas_serv_tec->fields["id_producto"];
	   $id_fila_st=$filas_serv_tec->fields["id_fila"];
	   descontar_entregados($nro_orden,$id_producto_st,$id_deposito_st,$cant_st,$id_fila_st);
	   $filas_serv_tec->MoveNext();
	  }//de while(!$filas_serv_tec->EOF)
	}//de if($modo=="oc_serv_tec")

  $db->CompleteTrans();
}//de function autorizar_oc($nro_orden,$modo,$select_proveedor,$es_stock=0,$internacional=0)


/*******************************************************************
Funcion que genera los campos necesarios para especificar la
recepcion de productos. Solo se utiliza cuando el proveedor NO ES
un Stock.
@nro_orden	   	 El Nº de OC del cual se deben mostrar los datos
@version_rapida	 Si esta en 1, se muestra una version mas rapida
				 de las recepciones, sin log de recepcion u otras
				 cosas que hacen mas lenta la carga de esta pagina
********************************************************************/
function generar_form_recepcion($nro_orden,$version_rapida=0)
{
	global $db,$mostrar_boton_no_recibir,$filas_rec_ent,$filas_cambios_prod,
	       $datos_depositos,$fecha_recepcion,$fecha_subida_gestion3;

	/*$query="select  	filas.id_fila,filas.id_prod_esp,filas.id_producto,filas.descripcion_prod,
		                filas.desc_adic,filas.cantidad_fila,filas.precio_unitario,filas.es_agregado,moneda.simbolo,
		                rec_ent.id_recibido,rec_ent.id_deposito,depositos.nombre as deposito_nombre,rec_ent.observaciones,
		                case when rec_ent.cantidad is null then 0
			                else rec_ent.cantidad
		                end as cantidad_recibida
    		from (
	    		      select id_fila,id_prod_esp,id_producto,descripcion_prod,desc_adic,cantidad as cantidad_fila,
	    		             precio_unitario,es_agregado,nro_orden
	       			  from compras.fila
	                  where (es_agregado isnull or es_agregado<>1) and nro_orden=$nro_orden
                 ) as filas
            left join
	             (    select id_recibido,id_fila,id_deposito,cantidad,observaciones
	                  from compras.recibido_entregado
	                  where ent_rec=1
                 )as rec_ent using(id_fila)
            left join general.depositos using(id_deposito)
            join compras.orden_de_compra using (nro_orden)
            join licitaciones.moneda using (id_moneda)
            where id_fila in (select id_fila from compras.fila where nro_orden=$nro_orden)
            order by id_producto";*/

	$query="select  	filas.id_fila,filas.id_prod_esp,filas.id_producto,filas.descripcion_prod,
		                filas.desc_adic,filas.cantidad_fila,filas.precio_unitario,filas.es_agregado,moneda.simbolo,
		                filas.id_recibido,filas.id_deposito,depositos.nombre as deposito_nombre,filas.observaciones,
		                case when filas.cantidad is null then 0
			                else filas.cantidad
		                end as cantidad_recibida
    		from (
	    		      select id_fila,id_prod_esp,id_producto,descripcion_prod,desc_adic,fila.cantidad as cantidad_fila,
	    		             precio_unitario,es_agregado,nro_orden,
				     id_recibido,id_deposito,recibido_entregado.cantidad,observaciones
	       			  from compras.fila left join compras.recibido_entregado using(id_fila)
	                  where (es_agregado isnull or es_agregado<>1) and (ent_rec isnull or ent_rec=1) and nro_orden=$nro_orden
                 ) as filas
            left join general.depositos using(id_deposito)
            join compras.orden_de_compra using (nro_orden)
            join licitaciones.moneda using (id_moneda)
            order by id_producto";

	$datos_recibidos=sql($query,"<br>Error al traer los datos de la recepción<br>") or fin_pagina();

	?>

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
		    <?
		    $desc_adic=ereg_replace("\r\n"," ",cortar($datos_recibidos->fields["desc_adic"],50));
		    ?>
		    aux_msg="--------------------------------------------------------------------------------\n"+
		            "Para la fila:\n   <?=$datos_recibidos->fields["descripcion_prod"]?> <?=$desc_adic?>"+
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
			      msg+=aux_msg+"	*Debe especificar una cantidad\n";
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
	  $precio_unitario=$datos_recibidos->fields['precio_unitario'];
	  $simbolo_moneda=$datos_recibidos->fields['simbolo'];

	  if($cantidad_comprada<=$cantidad_recibida)
	    $todo_recibido=1;
 	  else
        $todo_recibido=0;
	  ?>
	 <table width="100%" align="center" class="bordes">
	  <tr id="sub_tabla">
        <td width="55%">
         <table width="100%">
          <tr>
           <td width="1%">
	         <?
	         //si no hay nada recibido y tiene permiso, mostramos el checkbox para que el usuario pueda indicar que la fila
	         //no se va a recibir (esto pone el campo es_agregado de la tabla fila en 1)
	         if(!$version_rapida && ($cantidad_recibida=="" || $cantidad_recibida==0) && permisos_check("inicio","permiso_no_recibir_fila_oc"))
	         {
	         	$mostrar_boton_no_recibir=1;
	         	?>
	          <input type="checkbox" name="no_recibir_<?=$id_fila?>" value="1" class="estilos_check">
	          <?
	         }//de if($total_recibido=="" || $total_recibido==0)
	         ?>
	         </td>
	         <td width="100%">
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
	          <input type="hidden" name="nombre_prod_<?=$id_fila?>" value="<?=$nombre_producto_fila?>">
	         </b>
	        </td>
	       </tr>
	      </table>
        </td>
        <td width="15%" title="Precio Total de la Fila: <?=$simbolo_moneda?> <?=number_format($precio_unitario*$cantidad_comprada,2,'.','')?>">
         <b>Precio <font style="color: blue;"><?=$simbolo_moneda?> <?=number_format($precio_unitario,2,'.','')?></b>
        </td>
        <td width="15%">
         <b>Comprado</b> <input name="comprado_<?=$id_fila?>" type="text" style="text-align:right; border:none;background-color: #DFF4FF;font-weight: bold;color: blue;" readonly value="<?=$cantidad_comprada?>" size="6" >
        </td>
        <td width="15%">
         <b>Recibido</b> <input name="cantidad_rec_<?=$id_fila?>" type="text" style="text-align:right; border:none;background-color: #DFF4FF;font-weight: bold;color: blue;" value="<?=$cantidad_recibida?>" readonly size="6" >
        </td>
	  </tr>
	  <tr>
	   <td colspan="4">
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
	           if(!$version_rapida && permisos_check("inicio","permiso_cambiar_producto_fila"))
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

	        if(!$version_rapida && permisos_check("inicio","permiso_desrecibir_desentregar_fila") && ($filas_rec_ent[$id_fila]["entregados"]>0 || $filas_rec_ent[$id_fila]["recibidos"]>0))
	        {
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
	      <td colspan="4">
	       <input type="hidden" name="id_recibido_<?=$id_fila?>" value="<?=$datos_recibidos->fields['id_recibido']?>">
	       <?
	       if(!$version_rapida && $datos_recibidos->fields['id_recibido'])
	       {
		       //traemos el log de las recepciones del producto de la fila
		       $query="select id_log_recibido,id_prod_esp,log_rec_ent.usuario,fecha,cant,desde_stock,
		                      recepcion_confirmada,producto_especifico.descripcion
		             from log_rec_ent left join producto_especifico using(id_prod_esp)
		             where id_recibido=".$datos_recibidos->fields['id_recibido'];
		       $log_recibidos=sql($query,"<br>Error al traer el registro de las recepciones de productos<br>") or fin_pagina();


		       if($log_recibidos->RecordCount()>0)
		       {
		       	if(permisos_check("inicio","permiso_confirmar_recepcion_OC"))
		       	{
		       	  $permiso_confirmar=1;
		       	}
		       	else
		       	{
		       	  $permiso_confirmar=0;
		       	}

		       	?>
			       <table width="95%" align="center" class="bordes">
			        <tr id="mo">
			         <td colspan='5'>
			          Registro de Recepciones del Producto en el Stock <?=$datos_recibidos->fields["deposito_nombre"]?>
			         </td>
			        </tr>
			        <tr id="mo_sf6">
			         <td width="1%">
			          &nbsp;
			         </td>
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
			         $hay_sin_confirmar=0;
				     while (!$log_recibidos->EOF)
				     {?>
				       <tr id="ma_mg">
				        <td align="left">
				          <?
				          if ($log_recibidos->fields["recepcion_confirmada"]==0)
				          {?>
				          	<img src="../../imagenes/alerta.gif" title="Recepción sin confirmar">
				          <?
				          }
				          else
				           echo "&nbsp;";
				         ?>
				         </td>
				         <td align="left">
				         <?
				          if($permiso_confirmar && $log_recibidos->fields["recepcion_confirmada"]==0)
				          {
				          	$hay_sin_confirmar=1;
				          ?>
	                            <input type="checkbox" name="confirm_log_<?=$log_recibidos->fields["id_log_recibido"]?>" class="estilos_check" value="1">
				          <?
				          }//de if($permiso_confirmar)
				          else
				          {?>

				          <?
				          }

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

				     if($permiso_confirmar && $hay_sin_confirmar)
  			         {
				     ?>
				      <tr>
			           <td colspan="6" align="center">
				         <input type="submit" name="confirmar_recepcion" value="Confirmar Recepción" class="little_boton">
				         <input type="submit" name="rechazar_recepcion" value="Rechazar Recepción" class="little_boton">
				       </td>
			          </tr>
				     <?
			         }//de if($permiso_confirmar)
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
	      <td colspan="4">
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
		   <td colspan="4">
		    <hr>
		    <table width="100%" align="center">
		     <tr>
		      <td width="25%">
			    <b>Recibir en</b>
			    <select name="deposito_<?=$id_fila?>">
			     <?
                 //si tenemos un id de deposito significa que ya se ha recibido algo, por lo tanto
	             //solo se puede recibir en ese deposito, y no en el resto
	             if($datos_recibidos->fields["id_deposito"]!="" && $cantidad_recibida>0)
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
	   	   <td colspan="4">
	   	    <input type="hidden" name="deposito_<?=$id_fila?>" value="<?=$datos_recibidos->fields["id_deposito"]?>">
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
{global $db,$filas_cambios_prod,$title_gestion3;

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

//guarda las recepciones ingresadas (pero sin confirmarlas)
function guardar_recepciones_oc($nro_orden,$items)
{
	global $db;

	$db->StartTrans();

	//insertamos el registro de los productos recibidos, si el proveedor no es stock
  	$tam_items=sizeof($items);
  	//insertamos solo las recepciones de productos y no las reservas de stock. Esto se hara cuando el Lider confirme las recepciones
  	for($i=0;$i<$tam_items;$i++)
    {
	    $cant=$items[$i]['cantidad'];
	    if($cant=="")
	    	$cant=0;
		if($items[$i]['id_prod_recibido'])
		{   $id_fila=$items[$i]['id_fila'];
		    $id_deposito=$items[$i]['id_deposito'];
		    $id_prod_recibido=$items[$i]['id_prod_recibido'];
		    $id_recibido=$items[$i]['id_recibido'];
		    $observaciones=$items[$i]['observaciones'];

		    insertar_recibidos($nro_orden,$id_fila,$cant,$id_deposito,$id_prod_recibido,$id_recibido,$observaciones,1);
		}//de if($items[$i]['id_prod_recibido'])

  	 }//de for($i=0;$i<$tam_items;$i++)

	$db->CompleteTrans();
}//de function guardar_recepciones_oc($nro_orden,$items)

?>