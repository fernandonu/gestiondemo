<?
/*
Autor: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.13 $
$Date: 2007/01/05 14:32:30 $
*/
require_once("../../config.php");
require_once("fns.php");
require_once("../../lib/lib.php");

//print_r($_POST);
/******PARAMETROS DE ENTRADA DE LA PAGINA***********
@boton indica que tipo de accion se debe hacer
****************************************************/

extract($_POST,EXTR_SKIP);
if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

$f1=date("Y/m/j H:i:s");

///////BROGGI//////////////////////
if ($_POST['cambio_entidad']=="si_cambio")
   {$fecha=date("Y-m-d H:m:s");
    $usuario=$_ses_user['id'];
    $sql="select * from usuarios_clientes where id_usuario=$usuario and id_entidad=$id_cliente";
    $resul_select=sql($sql,"No se pudo consulta si ya existia esa entrada usuario-cliente") or fin_pagina();
    if($resul_select->RecordCount()>0)
      {$nuevo_peso=$resul_select->fields['peso_uso']+1;
       $sql="update usuarios_clientes set peso_uso=$nuevo_peso,fecha_ultimo_uso='$fecha' where id_usuario=$usuario and id_entidad=$id_cliente";
       $reul_update=sql($sql,"No se pudo realizar el update en la tabla usuarios_clientes") or fin_pagina();
      }
    else {$sql="insert into usuarios_clientes (id_usuario,id_entidad,fecha_ultimo_uso,peso_uso,empezo_uso_en)
                values ($usuario,$id_cliente,'$fecha',1,1)";
          $result_insert=sql($sql,"No se pudo realizar el insert en la tabla usuarios_clientes") or fin_pagina();
         }

   }//de que se cambio la entidad

// aca guardo los comentarios con las funcion nuevo_comentario
if ($_POST["guardar_comunicacion"]=="Guardar Comunicación Proveedor") {
	$comentario = $_POST["comentario_nuevo"] or $comentario=$parametros['comentario_nuevo'];
	$sql=nuevo_comentario($nro_orden,"ORDEN_PAGO",$comentario);
	sql($sql, "Error al insertar nueva comunicacion con el cliente para la orden de pago") or fin_pagina();
	$msg="<b>Los comentarios de la Comunicación con el Proveedor de la orden Nº $nro_orden han sido guardados</b>";
}

if ($guardar_coment=="Guardar Comentario")
{
 $q="update orden_de_compra set notas='$notas' where nro_orden=$nro_orden";
 if (sql($q) && $db->Affected_Rows() )
 	$msg="<b>Los comentarios de la orden Nº $nro_orden han sido guardados</b>";
 else
   $msg="<b>No se pudo guardar los comentarios de la orden Nº $nro_orden</b>";
}


if($borrar_fila_especial!="")
{
 include_once("../ord_compra/fns_especial.php");
 $msg="";
 //obtenemos el id de fila a borrar (el value es: Del_<id_fila>)
 $id_fila=$borrar_fila_especial;

 //eliminamos la fila que se eligió
 eliminar_fila_autorizada($id_fila,$nro_orden,$id_proveedor_a);
 $destino_para_autorizar="ord_pago_listar.php";
}//de if($borrar_fila_especial!="")


if($desrecibir_desentregar_fila=="Des-Recibir" || $desrecibir_desentregar_fila=="Des-Entregar")
{
 include_once("../ord_compra/fns_especial.php");
 $db->StartTrans();
 $msg="";
 $id_fila=$_POST["fila_desentregar"];
 //llamamos a la funcion que va a desentregar la fila
 des_recibir_fila($id_fila);

 $db->CompleteTrans();
  $destino_para_autorizar="ord_pago_listar.php";
}//de if($desrecibir_desentregar_fila=="Des-recibir")


if($guardar_cambios_fila=="Guardar Modificaciones a Filas")
{include_once("../ord_compra/fns_especial.php");
 //la funcion actualiza los cambios hechos en las filas
 cambiar_fila_especial($items);
 $destino_para_autorizar="ord_pago_listar.php";
}

// para probar la funcion de autogenerar_remito_interno
if ($boton=="Generar Remito Interno"){
	$q="select codigo_barra, tiene_remito from general.log_codigos_barra
        where nro_orden=$nro_orden and tiene_remito=0";
	$res=sql($q, "Error al traer los logs de los codigos de barra") or fin_pagina();
	$cant=$res->RecordCount();
	$q2="select id_fila, id_producto, cant_scb_sr from compras.fila
         where nro_orden=$nro_orden";
	$res2=sql($q2, "Error al traer datos de la oc") or fin_pagina();
	$cant2=$res2->RecordCount();
	for ($j=0;$j<$cant2;$j++) {
		$cant_scb_sr+=$res2->fields['cant_scb_sr'];
		$res2->MoveNext();
	}
	if ($cant || $cant_scb_sr>=1){
	   $nro_remito_interno=autogenerar_remito_interno($nro_orden);
	   $msg="<b> Se generó el remito interno Nº $nro_remito_interno para la orden Nº $nro_orden </b>";
	   //$destino_para_autorizar="ord_pago_listar.php";
	   $destino_mostrar_remito="../remito_interno/remito_int_nuevo.php";
	   $gen_remito_int=1;
	   }
	else {
	   $msg="<b> No se realizaron entregas para la orden Nº $nro_orden <br> No se puede generar el remito interno </b>";
	   $destino_para_autorizar="ord_pago_listar.php";
	   //$destino_mostrar_remito="../remito_interno/remito_int_nuevo.php";
	   //$gen_remito_int=1;
	  }
	}

//control de proveedor de Graciela
if ($cambio_estado_proveedor!="")
{
 switch($estado_proveedor)
 {
  case "Nada":$estado = 0;break;
  case "Atrasada por el Proveedor":$estado = 1;break;
  case "Llamar mas tarde":$estado = 2;break;
  case "Atrasada por CORADIR":$estado = 3;break;
 }

  $sql="update orden_de_compra set estado_proveedor=$estado where nro_orden=$nro_orden";
  sql($sql) or fin_pagina();

  $msg="<b>Se cambió el estado del proveedor para la OC $nro_orden</b>";
}

//de este modo puede guardar si es necesario antes de autorizar o terminar
if ($boton_guardar=="Guardar" || $guardar > 0)
{
 $db->StartTrans();
	if ($nro_orden==-1)
	{
		//si la plantilla es default entonces hacemos una copia de esa plantilla
	    //para que se pueda actualizar si es necesario, en el futuro
	    //(ESTO SOLO SE HACE SI EL PROVEEDOR NO ES STOCK)
	    if(!$es_stock)
	    {
	       $query="select mostrar from plantilla_pagos where id_plantilla_pagos=$select_pago";
	       $muestra_plantilla=sql($query) or fin_pagina();

	       if($muestra_plantilla->fields['mostrar']==1)
	       $select_pago=duplicar_plantilla_default($select_pago,0);
	    }
	    else
	    {//si es stock el proveedor, la forma de pago es null
	     $select_pago="null";
	    }

		$campos="id_contacto,fecha,fecha_entrega,lugar_entrega,cliente,id_plantilla_pagos,
		         id_proveedor,notas,id_moneda,estado,valor_dolar,reclamo_activado,flag_stock,
		         id_entidad,notas_internas,es_presupuesto";
		//saque de la consulta el destino oc ,id_destino_oc";
		$fecha=date("Y-m-j H:i:s");
		if ($select_contacto==-2 || $select_contacto==-1)
			$select_contacto='NULL';
		$fecha_entrega=Fecha_db($fecha_entrega);
		if($valor_dolar=="")
		 $valor_dolar=0;
		if($generar_reclamo_parte==1)
		 $generar_reclamo_parte=1;
		else
		 $generar_reclamo_parte=0;
		if($select_moneda=="-1" || !$select_moneda)
		  $select_moneda=1;

		  //recupero la entidad CORADIR SA
		  $q="select id_entidad,nombre from entidad where nombre ilike '%coradir%' order by id_entidad,id_distrito";
		  $r=sql($q) or fin_pagina();
		  $id_ent=$r->fields['id_entidad'] or $id_ent='NULL';
		  $cliente=$r->fields['nombre'];
				//Entidad CORADIR
//		  	$id_ent=441;
//		  	$cliente="Coradir S.A.";

		if($presupuesto=="" ||$presupuesto==0)
          $es_presupuesto=0;
        elseif($presupuesto==1)
         $es_presupuesto=1;
		//saco el destino oc
		//$valores="$select_contacto,'$fecha','$fecha_entrega','$entrega','$cliente',$select_pago,$select_proveedor,'$notas',$select_moneda,'p',$valor_dolar,$generar_reclamo_parte,$flag_stock,$id_ent,'$notas_internas',$es_presupuesto,$select_dest_oc";
		$valores="$select_contacto,'$fecha','$fecha_entrega','$entrega','$cliente',$select_pago,$select_proveedor,'$notas',$select_moneda,'p',$valor_dolar,$generar_reclamo_parte,$flag_stock,$id_ent,'$notas_internas',$es_presupuesto";
		if ($avisar)
		{
		 $campos.=",chequeado_avisar";
		 $valores.=",1";
		 $campos.=",descripcion_avisar";
		 $valores.=",'$texto_avisar_oc'";
		}
		else
		{
		 $campos.=",chequeado_avisar";
		 $valores.=",0";

		}
		if ($licitacion)
		{
		 $campos.=",id_licitacion";
		 $valores.=",$licitacion";
		 // este campo guarda el id estrega estimada del seguimietno de prod elegido para la orden
		 // asociada con una licitacion
		 if (($seguimientos!="" && $seguimientos!=-1) || $id_renglon_prop)
		 {
		  //si la OC provino desde un presupuesto de seguimiento de produccion, entonces
		  //el seguimiento que guardamos para relacionarlo con la OC, es aquel que contenga
		  //al id de renglon prop correspondiente. Es decir guaradmos el id_entrega_estimada
		  //que contiene al presupuesto que genero esta OC.
		  if($id_renglon_prop)
		  {$array_renglon_prep=split(",",$id_renglon_prop);
           $id_renglon=$array_renglon_prep[0];

           $sql="select id_entrega_estimada from renglon_presupuesto_new
           join licitacion_presupuesto_new using(id_licitacion_prop)
           join entrega_estimada using(id_entrega_estimada)
           join subido_lic_oc using(id_entrega_estimada)
           where renglon_presupuesto_new.id_renglon_prop=$id_renglon";
           $res=sql($sql) or fin_pagina();
           $seguimientos=$res->fields["id_entrega_estimada"];
		  }//de if($id_renglon_prop)
		  $campos.=",id_entrega_estimada";
		  $valores.=",$seguimientos";
		 }
		}
		if($caso)
		{$campos.=",nrocaso";
		 $valores.=",$caso";
		}
		if($proveedor_reclamo)
		{$campos.=",reclamo_proveedor";
		 $valores.=",$proveedor_reclamo";
		}
		if ($orden_prod) //si esta asociado a RMA de producion
		{$campos.=",orden_prod";
		 $valores.=",$orden_prod";
		}
		if ($gastos_servicio_tecnico)
		{$campos.=",flag_honorario";
		 $valores.=",1";
		}
		$campos.=",ord_pago";
		$valores.=",'si'";
		$q="insert into orden_de_compra ($campos) values ($valores)";
		sql($q) or fin_pagina();

		$q="select max(nro_orden) as nro_orden from orden_de_compra where fecha='$fecha'";
		$o=sql($q) or fin_pagina();
		$nro_orden=$o->fields['nro_orden'];
		$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'de creacion','".$_ses_user['login']."','$f1')";
		sql($q,"$q") or fin_pagina();

		$usuarios="select id_usuario from sistema.usuarios where avisar_oc=1";
        $res_usuarios=sql($usuarios) or fin_pagina();
        $res_usuarios->Move(0);
        $campos_avisar_oc="id_usuario, nro_orden";
        while (!$res_usuarios->EOF) {
           if ($_POST["mail_".$res_usuarios->fields['id_usuario']]) {
             $usuario=$res_usuarios->fields['id_usuario'];
             $consulta="insert into avisar_oc ($campos_avisar_oc) values ($usuario, $nro_orden)";
             sql($consulta) or fin_pagina();
           }
        $res_usuarios->MoveNext();
        }

		//si el proveedor es un stock
		if($es_stock)
		{
		 //descontamos los productos del stock seleccionado
	     //para reservarlos para esta OC

	     //primero obtenemos el nombre del proveedor seleccionado
         $query="select razon_social from proveedor where id_proveedor=$select_proveedor";
         $id_proveedor=sql($query) or fin_pagina();
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
         $id_dep=sql($query) or fin_pagina();

          //descontamos del stock seleccionado como proveedor
          $problems=descontar_stock($id_dep->fields['id_deposito']);
          if($problems==0)
	      {die("La Orden de Compra no se puede guardar: <br>Las cantidades a reservar del stock son mayores que las actualmente disponibles");
	      }
	     $reservarlo=1;
		}//de if($es_stock)

	}
	else
	{   //traemos la plantilla de pago que habia sido guardada antes
	    //(ESTO SOLO SE HACE SI EL PROVEEDOR NO ES STOCK)
	    if(!$es_stock)
	    {
	     $query="select id_plantilla_pagos from orden_de_compra where nro_orden=$nro_orden";
	     $plantilla=sql($query) or fin_pagina();

	     //si la plantilla es default entonces hacemos una copia de esa plantilla
	     //para que se pueda actualizar si es necesario, en el futuro
	     $query="select mostrar from plantilla_pagos where id_plantilla_pagos=$select_pago";
	     $muestra_plantilla=sql($query) or fin_pagina();

	     if($muestra_plantilla->fields['mostrar']==1)
	     {$select_pago=duplicar_plantilla_default($select_pago,$nro_orden);
	      $accion="dividir";
	     }

	    if($select_pago!=$plantilla->fields['id_plantilla_pagos'])
		{
		 //seleccionamos las ordenes de pagos vieja si es que hay y las borramos
         $query="select id_pago from ordenes_pagos join pago_orden using(id_pago) join orden_de_compra using (nro_orden) where nro_orden=$nro_orden";
         $res_ord_pago=sql($query) or fin_pagina();

         //borramos las entrada de pago_orden viejas si es que hay
         $query="delete from pago_orden where nro_orden=$nro_orden";
         $a=sql($query) or fin_pagina();

         while(!$res_ord_pago->EOF)
         {$query="delete from ordenes_pagos where id_pago=".$res_ord_pago->fields['id_pago'];
          sql($query) or fin_pagina();
          $res_ord_pago->MoveNext();
         }
		}//del if
	   }//de if(!$es_stock)
	   else//el proveedor es un stock
	   {//descontamos los productos del stock seleccionado
	    //para reservarlos para esta OC

	    //primero obtenemos el nombre del proveedor seleccionado
        $query="select razon_social from proveedor where id_proveedor=$select_proveedor";
        $id_proveedor=sql($query) or fin_pagina();
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
        $id_dep=sql($query) or fin_pagina();

        //descontamos del stock seleccionado como proveedor
        descontar_stock($id_dep->fields['id_deposito']);

	    $reservarlo=1;
	   }


		if ($select_contacto==-2 || $select_contacto==-1)
			$select_contacto='NULL';
        if($valor_dolar=="")
		 $valor_dolar=0;
		if($generar_reclamo_parte==1)
		 $generar_reclamo_parte=1;
		else
		 $generar_reclamo_parte=0;
		//si el proveedor seleccionado es un stock, la parte de pago no se utiliza
		//entonces asignamos un numero arbitrario para la plantilla de pagos
		//Pero no tiene aplicacion en este caso
		if($es_stock)
		 $select_pago=1;
		if($select_moneda=="-1"  || !$select_moneda)
		  $select_moneda=1;
		if(!$proveedor_reclamo)
		 $proveedor_reclamo="null";

        if (!$orden_prod)
           $orden_prod="NULL";

        if($presupuesto=="" ||$presupuesto==0)
          $es_presupuesto=0;
        elseif($presupuesto==1)
         $es_presupuesto=1;

		$q="update orden_de_compra set ".
		"fecha_entrega='".Fecha_db($fecha_entrega)."',".
		"lugar_entrega='".$entrega."',".
		"id_plantilla_pagos=$select_pago,".
		"id_proveedor=$select_proveedor,".
		"notas='$notas',".
		"notas_internas='$notas_internas',".
		"id_moneda=$select_moneda,".
		"id_contacto=$select_contacto ,".
		"valor_dolar=$valor_dolar,".
		"nrocaso='$caso',".
		"reclamo_proveedor=$proveedor_reclamo,".
		"reclamo_activado=$generar_reclamo_parte,".
		"flag_stock=$flag_stock,".
		"orden_prod=$orden_prod,".
		"es_presupuesto=$es_presupuesto,";
		/*if(permisos_check("inicio","control_compras_proveedores"))
		{
	     "estado_proveedor=$estado_proveedor,";
		}
		*/
		//saque el destino oc
		//"id_destino_oc=$select_dest_oc,";

		if ($avisar)
		   {
		$q.="chequeado_avisar= 1,".
		    "descripcion_avisar='$texto_avisar_oc'";
		   }
		else
		 $q.="chequeado_avisar= 0";


 		if ($licitacion){
 			$q.=",id_licitacion=$licitacion ";
 			// id_entrega_estimada dl seguimiento para la oc con esa licitacion
 			if (($seguimientos!="" && $seguimientos!=-1) && !$id_renglon_prop)
 			  $q.=",id_entrega_estimada=$seguimientos";
 		}
 		else
 			$q.=",id_licitacion=null ";

		$q.="where nro_orden=$nro_orden";
		sql($q) or fin_pagina();


		$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'de modificación','".$_ses_user['login']."','$f1')";
		sql($q) or fin_pagina();

		if($_POST['pagina_asoc']=="asociar")
		{$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'de asociacion','".$_ses_user['login']."','$f1')";
		 sql($q) or fin_pagina();
		}

 $actualizar_avisar_oc="delete from avisar_oc where nro_orden=$nro_orden ";
 sql($actualizar_avisar_oc) or fin_pagina();

 $usuarios="select id_usuario from sistema.usuarios where avisar_oc=1";
 $res_usuarios=sql($usuarios) or fin_pagina();
 $res_usuarios->Move(0);
 $campos_avisar_oc="id_usuario, nro_orden";

 $mails=PostvartoArray('mail_'); //crea un arreglo con los checkbox chequeados
 $tam_mails=sizeof($mails);

 if ($mails){  //para ver si hay check seleccionados
  foreach($mails as $key => $value){
   $consulta="insert into avisar_oc ($campos_avisar_oc) values ($value, $nro_orden)";
   sql($consulta) or fin_pagina();
 }

 }

	}

	//organiza los datos provenientes de la pagina anterior
	//echo "POST:";print_r($_POST); echo "<br>";
	$items=get_items();
//	echo "Despues de get_items() items:";print_r($items); echo "<br>";

	//elimina los items que no estan en items
	del_items($items,$nro_orden);
	//echo "Despues de del_items()";print_r($items); echo "<br>";

	 //modificamos el arreglo que le pasamos a la funcion replace para que
     //no intente insertar el campo proveedores y proveedores_cantidad
     //(porque eso es lo que intenta hacer si no los sacamos del $items)
	 $sacar=array();
     $sacar[0]="proveedores_cantidad";
    if($es_stock)
	  	$sacar[1]="proveedores";

	  if($items["cantidad"]>0)
      $items_replace=prepare_items($items,$sacar);

  //contendra los id_producto_presupuesto
  $productos_pres=array();//arreglo que se usa dentro de la funcion
	//eliminar los campos inecesarios para poder usar la funcion replace
	prepare_orden($nro_orden,$items_replace);

	//la funcion hace su trabajo
	if (replace("fila",$items_replace,array("id_fila")) == 0)
	{
		//guardamos la descripcion de los primeros 3 productos de la orden
	    //en la tabla de orden_de_compra para que al mostrarlos en el listado
	    //se cargue mas rapido la pagina
		$query="select descripcion_prod, desc_adic from fila where nro_orden=$nro_orden limit 3 offset 0";
		$prod_desc=sql($query) or fin_pagina();
        $descripcion_temp="";
        while(!$prod_desc->EOF)
        {
         $descripcion_temp.=$prod_desc->fields['descripcion_prod']." ".$prod_desc->fields['desc_adic']."\n";
         $prod_desc->MoveNext();
        }
        $query="update orden_de_compra set desc_prod='$descripcion_temp' where nro_orden=$nro_orden";
        if(sql($query) or fin_pagina())
    		$msg="<b> Su Orden Nº $nro_orden se guardo con éxito";
    	else
    	    $msg="<b> 1 - NO SE PUDO GUARDAR LA ORDEN: $query".$db->errormsg();
        $sin_problemas=1;

        if ($gastos_servicio_tecnico)
        {
            $casos=obtener_casos();
            $casos_aux=array();
            for($j=0;$j<sizeof($casos);$j++)
            {
                if ($casos[$j])
                             $casos_aux[]=$casos[$j];
            }
            if (sizeof($casos_aux))
            {
             insertar_fila_en_caso($nro_orden,$casos_aux);
            }

        }//de if ($gastos_servicio_tecnico)
     if ($gastos_servicio_tecnico || $es_cas)  {
        if ($actualizar_cas==1) { //ingresa en c.a.s. el lugar de entrega
              if (!$idate) {
               $sql_idate="select idate from casos.casos_cdr
                           join compras.fila on casos_cdr.fila=fila.id_fila
                           where nro_orden=$nro_orden";
               $res_idate=sql($sql_idate,"$sql_idate") or fin_pagina();
               $idate=$res_idate->fields['idate'];
              }
              if ($idate) {
              	$sql_com=nuevo_comentario($idate,"CASOS",$entrega);
                sql($sql_com,"$sql_com") or fin_pagina();
              }
              else $msg.= "No se actualizó el comentario en C.A.S. ";
        }
     }
	}
	else
	{
 	  $msg="<b> 2 - NO SE PUDO GUARDAR LA ORDEN $query".$db->errormsg();
	}

$db->Completetrans();

}

//dep_count es una variable que esta en la pagina de ord_compra_fin
if ($boton=="Guardar Datos" && (isset($dep_count)|| $es_stock))
{
  $db->StartTrans();
    /*********Esto lo puso Broggi********************/
    $com = "update orden_de_compra set notas_internas='$notas_internas' where nro_orden=$nro_orden";
    sql($com) or fin_pagina();
    /**********Hasta aca*****************************/

 //con el parametro en 1 indicamos que estamos recibiendo productos
 //(el proveedor NO es un stock)
 //con el parametro en 0 indicamos que estamos entregando productos
 //(el proveedor SI es un stock)
 if(!$es_stock)
 {$items=get_items_fin(1);
   $estado=$items['estado'];
  unset($items['estado']);
  unset($items['cantidad']);
  $msg="<b> NO SE PUDO ACTUALIZAR LA ORDEN";
 }
 else
  $items=get_items_fin(0);



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
  {$items_nuevos=insertar_recibidos($nro_orden,$items,$id_proveedor);
 // print_r($items);
 // die;

   /*************/
   //se agrega un log en log_recibidos por cada producto, para llevar
   //el seguimiento de quien recibio cada producto

   $user=$_ses_user['name'];
   $fecha_hoy=date("Y-m-d H:i:s",mktime());
   $tam_items_nuevos=sizeof($items_nuevos);
   for($m=0;$m<$tam_items_nuevos;$m++)
   {$cant_produc=$items_nuevos[$m]['cantidad'];
    $id_recibido=$items_nuevos[$m]['id_recibido'];
    if($cant_produc!="" && $cant_produc>0)
    {$query_ins="insert into log_recibido(id_recibido,usuario,fecha,cant)
          values($id_recibido,'$user','$fecha_hoy',$cant_produc)";
     sql($query_ins) or fin_pagina();
    }
   }//del for
  }//de if(!$es_stock)
 /************/
 else
 {
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

     if($_POST["entregar_sin_cb_".$id_f]==1) {
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
         mail_entregar_sin_cb($filas_sin_cb);
 }//del else de if(!$es_stock)

 $flag=0;  //control para ver si seleccionaron todas las facturas
		  //armo arreglo con wury id factura, nro orden
 $query[]="delete from factura_asociadas where nro_orden=$nro_orden";
 if ($factura){
   for ($i=0;$i<$tam;$i++){
		  if ($factura[$i]!='')  {
								   $id_fact=$factura[$i];
								   $query[]="insert into factura_asociadas (id_factura, nro_orden) values ($id_fact,$nro_orden)";
								  }

							   else {
								   $flag=1;
								   }
	 }//del for
   if (!$_POST['orden_ant'])
		  sql($query) or fin_pagina();
 }//del primer if ($fatura)

 else {
	  $flag=0;
	  }

 //$_POST['orden_ant']=! significa que la orden es vieja (tiene guardado el numero de factura y la fecha y la factura no esta cargada en factura_proveedores)
 //si $flag =1 significa que faltan cargar facturas entonces no se puede terminar
 $q="update orden_de_compra set  notas_internas='$notas_internas'";
  if (!$_POST['orden_ant']) $q.=" , cant_factura=$cant_factura";

	$tipo_log="de recepcion";
	$q.=" where nro_orden=$nro_orden";

	//actualizo el estado de la orden
	sql($q) or fin_pagina();

	$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'$tipo_log','".$_ses_user['login']."','$f1')";
	sql($q) or fin_pagina();

//ACTUALIZO EL STOCK !!!!!!!!!!!!!
//actualizo (incrementa)el stock si el estado es
//totalmente pagada

if($flag_stock)
{if($es_stock)
  die("La OC no puede tener un stock proveedor porque esta asociada a stock. Consulte con la Division Software");

	if (($estado_orden=='g')||($estado_orden=='d')||($estado_orden=='e'))
	 {
	 $sql="select prov_prod,id_proveedor,id_producto,id_deposito,r.cantidad,precio_unitario,f.id_fila
	       from ";
	 $sql.="orden_de_compra o join fila f using(nro_orden) join ";
	 $sql.="recibidos r using(id_fila)
	        where o.nro_orden=$nro_orden ";
	 $datos=sql($sql) or fin_pagina();
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


			$obs="Modificación automática Ordenes de Compra";


			//primero busco si tiene precio ese producto
			//con ese proveedor
			$sql=" select id_producto from precios ";
			$sql.="where id_producto=$id_prod ";
			$sql.="and id_proveedor=$id_prov ";
			$result=sql($sql) or fin_pagina();
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
                               }
   		     //me fijo si hay stock
                     $sql="select id_producto from stock";
		     $sql.=" where id_producto=$id_prod ";
		     $sql.=" AND id_proveedor=$id_prov";
		     $sql.=" AND id_deposito=$id_dep";
		     $result_stock=sql($sql) or fin_pagina();
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
					   sql($sql) or fin_pagina();

					   }
					   else
					   {
					   $campos="id_producto,id_proveedor,id_deposito,";
					   $campos.="comentario,last_user,cant_disp";
					   $sql="insert into stock ($campos) values ";

					   $sql.="($id_prod,$id_prov,$id_dep,'$obs','".$_ses_user['login']."',$cantidad)";
					   sql($sql,"$sql") or fin_pagina();
                       }
								//fin de la verificacion del stock
					   }
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
                                           insertar_precio($id_prod,$id_prov,$precio);

					   $campos="id_producto,id_proveedor,id_deposito,";
					   $campos.="comentario,last_user,cant_disp";
					   $sql="insert into stock ($campos) values ";
					   $sql.="($id_prod,$id_prov,$id_dep,'$obs','".$_ses_user['login']."',$cantidad)";
					   sql($sql) or fin_pagina();
					   }




         //INSERTO LOS LOG DEL LA MODIFICACION DEL
         //STOCK MEDIANTE  LA ORDEN DE COMPRA
         $fecha=date("Y-m-d H:i:s");
         $usuario=$_ses_user["name"];
         $query="select nextval('control_stock_id_control_stock_seq') as id_control_stock";
         $resultado=sql($query) or fin_pagina();
         $id_control_stock=$resultado->fields["id_control_stock"];

         $query="insert into control_stock
                (id_control_stock,fecha_modif,usuario,comentario,estado)
                 values($id_control_stock,'$fecha','OC Nº $nro_orden','Ingreso generado por la Orden de Compra Nº $nro_orden','oc')";
         sql($query) or fin_pagina();

         $query="insert into descuento (id_deposito,id_producto,id_proveedor,id_control_stock,cant_desc)
                 values($id_dep,$id_prod,$id_prov,$id_control_stock,$cantidad)";
         sql($query) or fin_pagina();

         $query="insert into log_stock(id_control_stock,usuario,fecha,tipo)
                 values ($id_control_stock,'$usuario','$fecha','Ingreso de Stock')";
         sql($query) or fin_pagina();
		}//del if de $cantidad > 0
         $datos->MoveNext();
	  }//for
	}//del if de totalmente pagada
}//de if($flag_stock)
//FIN DE LA ACTUALIZACION DEL STOCK!!!!!!

if ($db->CompleteTrans()) $msg="<b> Su Orden Nº $nro_orden se actualizó exitosamente";

$destino_para_autorizar="ord_pago_listar.php";

//}//de if (0 == replace("recibidos",$items,array("id_recibido")))

//////////////////////////////////////////ACA TENGO QUE MANDAR EL MAIL/////////////////////////////////
//print_r($items);
//die();
mail_recibe_productos($nro_orden,$items);
///////////////////////////////////////////////////////////////////////////////////////////////////////
}
elseif ($boton_ent_rec=="Recepciones/Entregas")
{
$link=encode_link("ord_compra_fin.php",array('nro_orden'=>$nro_orden,'es_stock'=>$es_stock,'mostrar_dolar'=>$mostrar_dolar,"tipo_lic"=>$tipo_lic_text));
header("location: $link");
die;
}
elseif($boton=="Entregar")
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

   $destino_para_autorizar="ord_pago_listar.php";

 if($db->CompleteTrans()) {
  $msg="<b> Su Orden Nº $nro_orden se actualizó exitosamente";
  if (count($filas_sin_cb)>0)
       mail_entregar_sin_cb($filas_sin_cb);

 }
}//de elseif($boton="Entregar")

if ($boton_autorizar=="Autorizar") {
        $db->StartTrans();
        $estado='e';
        $q="update orden_de_compra set estado='$estado' where nro_orden=$nro_orden";
 	    if (sql($q) or fin_pagina()) {
			$msg="<b> La Orden Nº $nro_orden se actualizó  exitosamente";

			$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'de autorizacion','".$_ses_user['login']."','$f1')";
			sql($q) or fin_pagina();
			//armo el mail para mandar
			if ($_ses_user["login"]!="corapi"){
				$para      = "corapi@coradir.com.ar";
				$asunto    = " Se autorizó la orden de pago nro:$nro_orden ";
				$contenido = "La orden de pago nro : $nro_orden, fue autorizada por el usuario ".$_ses_user["name"]."\n";
				enviar_mail($para,$asunto,$contenido,"","","");
			}
		   	
		 }
		 else {
			$msg="<b><font color='red'> No se pudo actualizar la orden Nº $nro_orden </font>";
		 }
    
		 

	//if($destino_para_autorizar=="ord_pago_listar.php" && $montos_default=="si")

    if (($destino_para_autorizar=="ord_pago_listar.php") && ($accion=="dividir")&&(!$es_stock)) {
	   	$query="select count(pago_plantilla.id_forma) as cant_pagos from orden_de_compra join plantilla_pagos using(id_plantilla_pagos) left join pago_plantilla using (id_plantilla_pagos) where nro_orden=$nro_orden";
		$count_pagos=sql($query) or fin_pagina();
		$nro_pagos=$count_pagos->fields['cant_pagos'];
		$monto_pago=monto_a_pagar($nro_orden);
		//tomamos en cuenta las notas de credito para la division del total
	    $monto_pago-=$montos_nc;
		$una_cuota=$monto_pago/$nro_pagos;
		//echo "monto total $monto_pago, nro_pagos $nro_pagos, cuota $cuota";
		$cuotas=array($nro_pagos);
		$dolar=array($nro_pagos);
		for($i=0;$i<$nro_pagos;$i++) {
		    if ($i==($nro_pagos-1)) {
			   $monto_pago=number_format($monto_pago,"2",".","");
			   $una_cuota=number_format($una_cuota,"2",".","");
			   $cuotas[$i]=$una_cuota+($monto_pago-($una_cuota*$nro_pagos));
		   }
		   else{
			  $cuotas[$i]=$una_cuota;
		   }
	 	   $dolar[$i]=$valor_dolar;
	    }//del for
	    		
 		$q="select id_moneda from moneda where nombre='Dólares'";
        $moneda=sql($q) or fin_pagina();
 		if($select_moneda==$moneda->fields['id_moneda']) {
 			 insertar_ordenes_pagos($nro_orden,$cuotas,$dolar);
 			 guardar_fp_ant('Al autorizar',$nro_orden,$dolar); //guarda datos para mantener un log
 		}
 		else {
 			 insertar_ordenes_pagos($nro_orden,$cuotas);
 			 guardar_fp_ant('Al autorizar',$nro_orden); //guarda datos para mantener un log
 		}
     }//del if
   //sino, esto se hace en la pagina de ord_compra_pagar

$db->CompleteTrans();
} //del if $boton_autorizar == "Autorizar"

if($guardar_transporte=="Guardar")
{
	$db->StartTrans();
	//traemos los id del producto "Transporte" del proveedor "licitacion"
	//para insertarlo en la OC como una nueva fila
	$query="select id_producto from productos
	        where desc_gral='Conexo:'";
	$datos_trans=sql($query) or fin_pagina();
	$id_producto=$datos_trans->fields["id_producto"];

	$items_trans=get_items();

	//tomamos los datos del unico producto que va a devolver get_items(el resto no viene por estar disabled)
    $desc_prod=$items_trans[0]["descripcion_prod"];
	$desc_adic=$items_trans[0]["desc_adic"];
	$cant=$items_trans[0]["cantidad"];
	$precio=$items_trans[0]["precio_unitario"];

	$query="select nextval('fila_id_fila_seq') as id_fila";
	$id=sql($query) or fin_pagina();
	$id_fila=$id->fields['id_fila'];
	//insertamos la fila en la OC con los datos obtenidos
	$insert="insert into fila(id_fila,descripcion_prod,desc_adic,cantidad,precio_unitario,nro_orden,id_producto,es_agregado)
	          values($id_fila,'$desc_prod','$desc_adic',$cant,$precio,$nro_orden,$id_producto,1)";
	sql($insert) or fin_pagina();

	//seleccionamos los pagos de la OC para saber cual vamos a modificar
	$query="select id_pago,id_ingreso_egreso,iddébito,númeroch,monto
	        from pago_orden join ordenes_pagos using(id_pago) where nro_orden=$nro_orden";
	$pagos=sql($query) or fin_pagina();
	//recorremos los pagos hasta encontrar el primero disponible
	//el cual vamos a modificar agregandole al monto, el valor del conexo insertado
	$modif_hecha=0;
	while(!$pagos->EOF)
	{
	 if($modif_hecha==0 && ($pagos->fields['id_ingreso_egreso']=="" && $pagos->fields['iddébito']=="" && $pagos->fields['númeroch']==""))
	 {
	  //modificamos el monto del primer pago de la OC que aun no se pagó
	  // para que tome el nuevo producto como parte del monto total a pagar
	  $query="update ordenes_pagos set monto=monto+$precio where id_pago=".$pagos->fields['id_pago'];
	  sql($query) or fin_pagina();
      $modif_hecha=1;
	 }
     $pagos->MoveNext();
	}//de while(!$pagos->EOF)

	//guardamos en la tabla orden_de_compra que ya se agrego una vez el transporte
	$update="update orden_de_compra set transporte_agregado=1 where nro_orden=$nro_orden";
	sql($update) or fin_pagina();

	//insertamos el log registrando el ingreso del transporte para la OC
	$fecha_hoy=date("Y-m-d H:i:s",mktime());
	$query="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha)
	    values ($nro_orden,'de adición del Transporte','".$_ses_user['login']."','$fecha_hoy')";
	sql($query) or fin_pagina();

	$msg="Se agregó con éxito el Transporte para la Orden de Compra Nº $nro_orden";
	$db->CompleteTrans();
}

if ($h_rechazar=="Rechazar")
{   $db->StartTrans();
		$q="update orden_de_compra set ".
		"estado='r' where nro_orden=$nro_orden";
	if (sql($q) or fin_pagina())
	{
		$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha,otros) values ($nro_orden,'de rechazo','".$_ses_user['login']."','$f1','".$_POST["comentario_rechazar"]."')";
		sql($q) or fin_pagina();

		//(ESTO SOLO SE HACE SI EL PROVEEDOR NO ES STOCK)
	    if(!$es_stock)
	    {
	     //si se rechaza la orden, se deben eliminar las entradas de
		 //ordenes_pagos para esa orden
		 //primero se controla si ya se realizaron pagos, en cuyo caso se
		 //agrega al mail que se envia, el detalle de los pagos ya realizados
		 $query="select id_pago,id_ingreso_egreso,iddébito,idbanco,númeroch,ordenes_pagos.valor_dolar from ordenes_pagos join pago_orden using(id_pago) join orden_de_compra using (nro_orden) where nro_orden=$nro_orden";
		 $pagos_realizados=sql($query) or fin_pagina();
/*
		 $cheque_array=array();
		 $efectivo_array=array();
		 $debito_array=array();
		 while(!$pagos_realizados->EOF)
		 {if($pagos_realizados->fields['id_ingreso_egreso'])
		  {
		   $efectivo_array[sizeof($efectivo_array)]=$pagos_realizados->fields['id_ingreso_egreso'];
		  }
		  if($pagos_realizados->fields['iddébito'])
		  {
		   $debito_array[sizeof($debito_array)]=$pagos_realizados->fields['iddébito'];
		  }
		  if($pagos_realizados->fields['idbanco'] && $pagos_realizados->fields['númeroch'])
		  {$a=sizeof($cheque_array);
		   $cheque_array[$a]['idbanco']=$pagos_realizados->fields['idbanco'];
		   $cheque_array[$a]['numeroch']=$pagos_realizados->fields['númeroch'];
		  }
          $pagos_realizados->MoveNext();
		 }

		 //generamos el detalle en el mail de los pagos realizados que deberian revisar

		 //cheques
		 $query_cheques="select fechaemich,númeroch,importech,nombrebanco from cheques join tipo_banco using(idbanco)";
		 $where_cheques=" where ";
		 $tam_cheque=sizeof($cheque_array);

		 for($i=0;$i<$tam_cheque;$i++)
		  {if($i!=0)
		   	$where_cheques.="or ";
		   $where_cheques.="idbanco=".$cheque_array[$i]['idbanco']." and númeroch=".$cheque_array[$i]['numeroch']." ";
		  }
		 $query_cheques.=$where_cheques;

		 //debitos
		 $query_debito="select fechadébito,importedéb from débitos ";
		 $where_debito=" where ";
		 $tam_debito=sizeof($debito_array);
		 for($i=0;$i<$tam_debito;$i++)
	     {if($i!=0)
		   $where_debito.="or ";
		  $where_debito.="iddébito=".$debito_array[$i]." ";
   		 }
		 $query_debito.=$where_debito;

         //efectivo
		 $query_efectivo="select fecha_creacion,monto from ingreso_egreso ";
		 $where_efectivo=" where ";
		 $tam_efectivo=sizeof($efectivo_array);
		 for($i=0;$i<$tam_efectivo;$i++)
		  {if($i!=0)
		    	$where_efectivo.="or ";
		   $where_efectivo.="id_ingreso_egreso=".$efectivo_array[$i]." ";
		  }
		  $query_efectivo.=$where_efectivo;

		 //si hubo pagos de uno de los tipos de pago, ejecutamos el query correspondiente
		 //y construimos el detalle para el mail
		 if($tam_cheque>0)
		 {$detalle_cheque=$db->Execute($query_cheques) or die($db->ErrorMsg()."query cheques");
		  $mail_cheque="";
		  while(!$detalle_cheque->EOF)
		  {$mail_cheque.="-El cheque Nro ".$detalle_cheque->fields['númeroch']." del banco ".$detalle_cheque->fields['NombreBanco'].", con fecha de emisión ".fecha($detalle_cheque->fields['fechaemich']).", con un importe de $".formato_money($detalle_cheque->fields['importech'])." se utilizó para realizar parte del pago de esta orden de compras.\n";
		   $detalle_cheque->MoveNext();
		  }
		 }
         if($tam_debito>0)
		 {$detalle_debito=$db->Execute($query_debito) or die($db->ErrorMsg()." query debito");
		  $mail_debito="";
		  while(!$detalle_debito->EOF)
		  {$mail_debito.="-El débito con fecha ".fecha($detalle_debito->fields['fechadébito']).", y un importe de $".formato_money($detalle_debito->fields['importedéb'])." se utilizó para realizar parte del pago de esta orden de compras.\n";
		   $detalle_debito->MoveNext();
		  }
		 }
		 if($tam_efectivo>0)
		 {$detalle_efectivo=$db->Execute($query_efectivo) or die($db->ErrorMsg()." query efectivo");
		  $mail_efectivo="";
		  while(!$detalle_efectivo->EOF)
		  {if($valor_dolar==0)
		    $valor_dolar=1;
		   $mail_efectivo.="-El egreso con fecha ".fecha($detalle_efectivo->fields['fecha_creacion']).", y un importe de $".formato_money($valor_dolar*$detalle_efectivo->fields['monto'])." se utilizó para realizar parte del pago de esta orden de compras.\n";
		   $detalle_efectivo->MoveNext();
		  }
		 }
*/
      	 //se borran las ordenes de pago de la orden de compra que se esta rechazando
 		 $query="delete from pago_orden where nro_orden=$nro_orden";
		 sql($query) or fin_pagina();
		 $pagos_realizados->Move(0);
		 while(!$pagos_realizados->EOF)
		 {
		  $query="delete from ordenes_pagos where id_pago=".$pagos_realizados->fields['id_pago'];
		  sql($query) or fin_pagina();
		  $pagos_realizados->MoveNext();
		 }
/*
         if (($mail_cheque!="")||($mail_debito!="")||($mail_detalle!=""))
               {
		       $mail_detalle="<b>ATENCION: Se han realizado pagos para la orden de compra Nro $nro_orden.</b>";
		       $mail_detalle.=$mail_cheque.$mail_debito.$mail_efectivo;
               }
               else $mail_detalle="";
		 $msg="<b> Su Orden Nº $nro_orden se actualizó exitosamente";*/
		 $asunto="La Orden de compra Nº $nro_orden ha sido rechazada";
		 $mail_text="";
		 $mail_text .= "La Orden de compra Nº $nro_orden ha sido rechazada\n";
		 $mail_text .= "\nUsuario que rechazó: ".$_ses_user["name"]."\n";
		 $mail_text .= "\nJustificación del rechazo:\n";
		 $mail_text .= "--------------------------------------------------------------\n";
		 $mail_text .= $_POST["comentario_rechazar"]."\n";
		 $mail_text .= "\n--------------------------------------------------------------\n\n";
		 $mail_text .= detalle_orden($nro_orden);

		 $query="select distinct user_login,mail from log_ordenes
		         join usuarios on(user_login=login)
		         where nro_orden=$nro_orden and user_login<>'".$_ses_user['login']."' and user_login<>'juanmanuel'";
		 $para_users=sql($query) or die($db->ErrorMsg()."<BR>Error al traer usuarios de rechazo");
		 $para="juanmanuel@coradir.com.ar";
		 while(!$para_users->EOF)
		 {
		  $para.=",".$para_users->fields["mail"];
		  $para_users->MoveNext();
		 }
		 //echo "<br>Para $para<br>";
		 //echo "<br><br>TEXT:<br> $mail_text<br>";
         //if($para!="")
		 // enviar_mail ($para,$asunto,$mail_text,"","","",0);
	    }//de if(!$es_stock)

/***************************************************************
Mercaderia en Tránsito
****************************************************************/
	    //se deben descontar de stock los productos de la OC que esten en
	    //mercaderia en transito (Esto es cuando la OC esta autorizada)
	 /*   if($estado=="a" || $estado=="e")
	    {//descontamos de mercaderia en transito los productos
         //solo si no esta asociada con honorario de s tecnico o  es asociada
         //con otro
         $query_as="select flag_honorario,id_licitacion,orden_prod,flag_stock,nrocaso from orden_de_compra where nro_orden=$nro_orden";
         $asociada=$db->Execute($query_as) or die($db->ErrorMsg."<br>Error al traer asociaciones de OC");
         if($asociada->fields['flag_honorario']!=1 && ($asociada->fields['id_licitacion']!="" || $asociada->fields['orden_prod']!="" || $asociada->fields['flag_stock']==1 || $asociada->fields['nrocaso']!=""))
         {$items_merc_trans=get_items($nro_orden);
	      volver_atras_merc_trans($nro_orden,$items_merc_trans,$_POST['id_proveedor_a'],"Rechazo");
         }
	    }*/
/***************************************************************
Mercaderia en Tránsito
****************************************************************/

	}//de if ($db->Execute($q))
	else
	{
		$msg="<b> NO SE PUDO ACTUALIZAR LA ORDEN";
	}
   $db->CompleteTrans();
}

if ($h_anular=="Anular")
{
 $flag_honorario=$_POST["es_cas"];
 $db->StartTrans();
 anular_oc($nro_orden);
 $db->CompleteTrans();
}//de if ($h_anular=="Anular")

if ($boton_para_autorizar=="Para Autorizar")
//Siempre manda un mail a corapi
{
$db->StartTrans();
//busco el usuario que esta autorizado
switch ($_ses_user['login']) {
case "mascioni": //cambia de estado y manda un mail
                $sql="update orden_de_compra set ".
		"estado='u' where nro_orden=$nro_orden";
		if (sql($sql) or fin_pagina()){
                $mandar_mail=1;
		$msg="Su Orden Nº $nro_orden se actualizó exitosamente";
		$sql="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'para autorizar','".$_ses_user['login']."','$f1')";
		sql($sql) or fin_pagina();

		}
		else
		$msg="NO SE PUDO ACTUALIZAR LA ORDEN";
			   //mando un mail para avisar que cambio de estado la orden
			  /* $sql="select usuario_avisar from ordenes_mail_aviso where usuario_avisa='$_ses_user_login' and tipo='$boton'";
			   $resultado=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
			   $para=$resultado->fields['usuario_avisar'];
			   $mailtext=$_POST['contenido'];
			   $asunto="Orden de Compra Nº:$nro_orden en condición de ser Autorizada";
			   $mail_header="";
			   $mail_header .= "MIME-Version: 1.0";
			   $mail_header .= "\nfrom: Sistema Inteligente de CORADIR <>";
			   $mail_header .="\nTo: $para";
			   $mail_header .= "\nContent-Type: text/plain";
			   $mail_header .= "\nContent-Transfer-Encoding: 8bit";
			   $mail_header .= "\n\n" . $mailtext."\n";
				  $mail_header .= "\n\n" . firma_coradir()."\n";
			  // mail("",$asunto,"",$mail_header);*/
			   break;   //del case fernando
 case "corapi":
  	      $sql="update orden_de_compra set ".
		   "estado='u' where nro_orden=$nro_orden";
	      if (sql($sql) or fin_pagina()){
                               $mandar_mail=1;
		 	       $msg="Su Orden Nº $nro_orden se actualizó exitosamente";

			       $sql="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'para autorizar','".$_ses_user['login']."','$f1')";
			       sql($sql) or fin_pagina();
							  /*$asunto="La Orden de compra Nº $nro_orden a sido autorizada";
							  $mail_header="";
							  $mail_header .= "MIME-Version: 1.0";
							  $mail_header .= "\nfrom: Sistema Inteligente de CORADIR <>";
							  $mail_header .="\nTo: carlos@pcpower.com.ar";
							  $mail_header .= "\nContent-Type: text/plain";
							  $mail_header .= "\nContent-Transfer-Encoding: 8bit";
							  $mail_header .= "\n\nUna orden de compra a sido Autorizada\n";
							  //mail ("",$asunto,"",$mail_header);*/
					}
					else
					 $msg="<b> NO SE PUDO ACTUALIZAR LA ORDEN";

			   break;
 default:
	  //temporal hasta que mansioni vuelva de las vacaciones
	  $sql="update orden_de_compra set ".
          "estado='u' where nro_orden=$nro_orden";
	   if(sql($sql) or fin_pagina())
			   {
                            $mandar_mail=1;
                            $msg="<b>Su Orden Nº $nro_orden se actualizó exitosamente";

				$sql="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'para autorizar','".$_ses_user['login']."','$f1')";
				sql($sql) or fin_pagina();
			   }
			   else
				$msg="<b> NO SE PUDO ACTUALIZAR LA ORDEN";

			  //manda un mail solamente
			 //cualquier usuario que sea distinto de Corapu y de Mascioni
			   //sql="select usuario_avisar from ordenes_mail_aviso where usuario_avisa='$_ses_user_login' and tipo='$boton'";
			/* $sql="select usuario_avisar from ordenes_mail_aviso where usuario_avisa='general' and tipo='$boton'";
			   $resultado=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
			   $para=$resultado->fields['usuario_avisar'];
			   $mailtext=$_POST['contenido'];
			   $asunto="Orden de Compra Nº:$nro_orden en condición de ser Autorizada";
			   $mail_header="";
			   $mail_header .= "MIME-Version: 1.0";
			   $mail_header .= "\nfrom: Sistema Inteligente de CORADIR <>";
			   $mail_header .="\nTo: $para";
			   $mail_header .= "\nContent-Type: text/plain";
			   $mail_header .= "\nContent-Transfer-Encoding: 8bit";
			   $mail_header .= "\n\n" . $mailtext."\n";
			   mail("",$asunto,"",$mail_header);


			   //Si el archivo de destino es listar_ordenes
			   //hacemos la division automatica de los pagos y los insertamos
			   //en las tablas correspondientes
*/
			   break;   //del case fernando

}//del swhitch
if ($mandar_mail){
                 $para="corapi@coradir.com.ar";
                 $mailtext=$_POST['contenido'];
                 $asunto="Orden de Compra Nº:$nro_orden en condición de ser Autorizada";
                 $mail_header="";
                 $mail_header .= "MIME-Version: 1.0";
                 $mail_header .= "\nFrom: Sistema Inteligente de CORADIR <>";
                 $mail_header .= "\nReturn-Path: sistema_inteligente@coradir.com.ar";
                 $mail_header .="\nTo: $para";
                 $mail_header .= "\nContent-Type: text/plain";
                 $mail_header .= "\nContent-Transfer-Encoding: 8bit";
                 $mail_header .= "\n\n" . $mailtext."\n";
                 $mail_header .= "\n\n" . firma_coradir()."\n";
                 //mail("",$asunto,"",$mail_header);

}

if (($destino_para_autorizar=="ord_pago_listar.php")&&($accion=="dividir")&&(!$es_stock))
			   {$query="select count(pago_plantilla.id_forma) as cant_pagos from orden_de_compra join plantilla_pagos using(id_plantilla_pagos) left join pago_plantilla using (id_plantilla_pagos) where nro_orden=$nro_orden";
				$count_pagos=sql($query) or fin_pagina();
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
				 }
				$q="select id_moneda from moneda where nombre='Dólares'";
				$moneda=sql($q) or fin_pagina();
				if($select_moneda==$moneda->fields['id_moneda'])
				 insertar_ordenes_pagos($nro_orden,$cuotas,$dolar);
				else
				 insertar_ordenes_pagos($nro_orden,$cuotas);
			   }
			   //sino, esto se hace en la pagina de ord_compra_pagar




$db->CompleteTrans();
} //del boton Para autorizar
elseif($boton_habilitar_pago_especial=="Habilitar Pago Especial")
{$db->StartTrans();
 $query="update orden_de_compra set habilitar_pago_especial=1 where nro_orden=$nro_orden";
 if(sql($query) or  fin_pagina()) //or die($db->ErrorMsg()."error en update pago_especial");
 {$msg="Se habilitó el pago especial para la orden Nº $nro_orden";
 }
 else
 {$msg="<b> No se pudo habilitar el pago especial para la orden Nº $nro_orden";
 }
 $db->CompleteTrans();
}
elseif($boton_deshabilitar_pago_especial=="Deshabilitar Pago Especial")
{$db->StartTrans();
 $query="update orden_de_compra set habilitar_pago_especial=0 where nro_orden=$nro_orden";
 if(sql($query) or fin_pagina()) //or die($db->ErrorMsg()."error en update pago_especial");
 {$msg="<b> Se deshabilitó el pago especial para la orden Nº $nro_orden";
 }
 else
 {$msg="<b> No se pudo deshabilitar el pago especial para la orden Nº $nro_orden";
 }
 $db->CompleteTrans();
}

//Despagar Orden de Compra
if($despagar=="D$")
{$db->StartTrans();
 include_once("../ord_compra/fns_especial.php");
 if($mostrar_dolar==1)
  $moneda="U\$S";
 else
  $moneda="\$";
 $msg="";
 //La funcion envia un mail
 despagar_oc($nro_orden,$moneda);

 $db->CompleteTrans();
}

if($anular_con_stock=="Anular OC")
{
 $db->StartTrans();
 include_once("../ord_compra/fns_especial.php");

 $select_proveedor=$id_proveedor_a;
 des_recibir_oc($nro_orden);

 //anulamos la OC.
 $_POST['comentario_anular']="Anulado por sistema debido a que no se usó la parte para el Servicio Técnico";
 anular_oc($nro_orden);

 $msg="<center><b>La Orden de Compra $nro_orden, se anuló con éxito</b></center>";
 $destino_para_autorizar="ord_pago_listar.php";
 $db->CompleteTrans();
}//de if($anular_con_stock=="Anular OC")

if ($gen_remito_int)
 $link=encode_link($destino_mostrar_remito,array('id_remito'=>$nro_remito_interno,'pagina_viene'=>'ord_compra_fin'));
else
{
 if (strpos($_POST['back_page'],"ord_compra_listar.php"))
 	$destino_para_autorizar=$_POST['back_page'];
 $link=encode_link($destino_para_autorizar,array('nro_orden'=>$nro_orden,'msg'=>$msg,'pagina_viene'=>'ord_compra'));
}

header("location: $link");
?>