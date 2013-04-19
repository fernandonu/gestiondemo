<?
/*
Autor: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.239 $
$Date: 2007/01/05 18:50:41 $
*/
require_once("../../config.php");
require_once("fns.php");
require_once("../stock/funciones.php");
require_once("../../lib/lib.php");

//print_r($_POST);
/******PARAMETROS DE ENTRADA DE LA PAGINA***********
@boton indica que tipo de accion se debe hacer
****************************************************/

extract($_POST,EXTR_SKIP);

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

$f1=date("Y/m/j H:i:s");

if ($_POST['cambio_entidad']=="si_cambio")
{
	$db->StartTrans();
	$fecha=date("Y-m-d H:m:s");
    $usuario=$_ses_user['id'];
    $sql="select * from licitaciones.usuarios_clientes where id_usuario=$usuario and id_entidad=$id_cliente";
    $resul_select=sql($sql,"<br>No se pudo consulta si ya existia esa entrada usuario-cliente<br>") or fin_pagina();
    if($resul_select->RecordCount()>0)
      {$nuevo_peso=$resul_select->fields['peso_uso']+1;
       $sql="update licitaciones.usuarios_clientes set peso_uso=$nuevo_peso,fecha_ultimo_uso='$fecha' where id_usuario=$usuario and id_entidad=$id_cliente";
       $reul_update=sql($sql,"<br.No se pudo realizar el update en la tabla usuarios_clientes<br>") or fin_pagina();
      }
    else {$sql="insert into licitaciones.usuarios_clientes (id_usuario,id_entidad,fecha_ultimo_uso,peso_uso,empezo_uso_en)
                values ($usuario,$id_cliente,'$fecha',1,1)";
          $result_insert=sql($sql,"<br>No se pudo realizar el insert en la tabla usuarios_clientes<br>") or fin_pagina();
         }

   $db->CompleteTrans();
}//de if ($_POST['cambio_entidad']=="si_cambio")

// aca guardo los comentarios con las funcion nuevo_comentario
if ($_POST["guardar_comunicacion"]=="Guardar Comunicación Proveedor")
{
 $db->StartTrans();
	$comentario = $_POST["comentario_nuevo"] or $comentario=$parametros['comentario_nuevo'];
	$sql=nuevo_comentario($nro_orden,"ORDEN_COMPRA",$comentario);
	sql($sql, "<br>Error al insertar nueva comunicacion con el cliente para la orden de compra<br>") or fin_pagina();
	$msg="<b>Los comentarios de la Comunicación con el Proveedor de la orden Nº $nro_orden han sido guardados</b>";
 $db->CompleteTrans();
}

if ($guardar_coment=="Guardar Comentario")
{
 	$db->StartTrans();
	 $q="update orden_de_compra set notas='$notas' where nro_orden=$nro_orden";
	 if ((sql($q,"<br>Error al actualizar los comentarios de la OC<br>") or fin_pagina())&& $db->Affected_Rows() )
	 	$msg="<b>Los comentarios de la orden Nº $nro_orden han sido guardados</b>";
	 else
	   $msg="<b>No se pudo guardar los comentarios de la orden Nº $nro_orden</b>";

	$db->CompleteTrans();
}


if($borrar_fila_especial!="")
{
 include_once("fns_especial.php");
 $db->StartTrans();
 $msg="";
 //obtenemos el id de fila a borrar (el value es: Del_<id_fila>)
 $id_fila=$borrar_fila_especial;

 //eliminamos la fila que se eligió
 eliminar_fila_autorizada($id_fila,$nro_orden,$id_proveedor_a);
 $destino_para_autorizar="ord_compra_listar.php";

 $db->CompleteTrans();
}//de if($borrar_fila_especial!="")

if($guardar_cambios_fila=="Guardar Modificaciones a Filas")
{include_once("fns_especial.php");
 $db->StartTrans();
 //la funcion actualiza los cambios hechos en las filas
 cambiar_fila_especial($items,$internacional);
 $destino_para_autorizar="ord_compra_listar.php";

 $db->CompleteTrans();
}//de if($guardar_cambios_fila=="Guardar Modificaciones a Filas")

// para probar la funcion de autogenerar_remito_interno
if ($boton=="Generar Remito Interno")
{
  $db->StartTrans();
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
	   //$destino_para_autorizar="ord_compra_listar.php";
	   $destino_mostrar_remito="../remito_interno/remito_int_nuevo.php";
	   $gen_remito_int=1;
	   }
	else {
	   $msg="<b> No se realizaron entregas para la orden Nº $nro_orden <br> No se puede generar el remito interno </b>";
	   $destino_para_autorizar="ord_compra_listar.php";
	   //$destino_mostrar_remito="../remito_interno/remito_int_nuevo.php";
	   //$gen_remito_int=1;
	  }
	$db->CompleteTrans();
}//de if ($boton=="Generar Remito Interno")

//control de proveedor de Graciela
if ($cambio_estado_proveedor!="")
{

 $db->StartTrans();
 switch($estado_proveedor)
 {
  case "Nada":$estado = 0;break;
  case "Atrasada por el Proveedor":$estado = 1;break;
  case "Llamar mas tarde":$estado = 2;break;
  case "Atrasada por CORADIR":$estado = 3;break;
 }

  $sql="update orden_de_compra set estado_proveedor=$estado where nro_orden=$nro_orden";
  sql($sql,"<br>Error al actualizar el estado del proveedor<br>") or fin_pagina();

  $msg="<b>Se cambió el estado del proveedor para la OC $nro_orden</b>";

  $db->CompleteTrans();
}//de if ($cambio_estado_proveedor!="")

//Si es una Orden de Servicio Tecnico, tomamos el proveedor desde id_proveedor_a
if($modo=="oc_serv_tec")
  $select_proveedor=$id_proveedor_a;

//de este modo puede guardar si es necesario antes de autorizar o terminar
if ($boton_guardar=="Guardar" || $guardar > 0)
{
 $db->StartTrans();
	if ($nro_orden==-1)//es una OC nueva
	{
		//si la plantilla es default entonces hacemos una copia de esa plantilla
	    //para que se pueda actualizar si es necesario, en el futuro
	    //(ESTO SOLO SE HACE SI EL PROVEEDOR NO ES STOCK)
	    if(!$es_stock)
	    {$query="select mostrar from plantilla_pagos where id_plantilla_pagos=$select_pago";
	     $muestra_plantilla=sql($query,"<br>Error al averiguar si la plantilla de pagos es default o no<br>") or fin_pagina();

	     if($muestra_plantilla->fields['mostrar']==1)
	      $select_pago=duplicar_plantilla_default($select_pago,0);
	    }
	    else
	    {//si es stock el proveedor, la forma de pago es null
	     $select_pago="null";
	    }

		$campos="id_contacto,fecha,fecha_entrega,lugar_entrega,cliente,id_plantilla_pagos,
		         id_proveedor,notas,id_moneda,estado,valor_dolar,reclamo_activado,flag_stock,
		         id_entidad,notas_internas,es_presupuesto,fecha_facturacion,cuenta_corriente";
		//saque de la consulta el destino oc ,id_destino_oc";
		$fecha=date("Y-m-j H:i:s");
		if ($select_contacto==-2 || $select_contacto==-1)
			$select_contacto='NULL';
		$fecha_entrega=Fecha_db($fecha_entrega);

		if($cuenta_corriente)//si tiene cuenta corriente habilitada, usamos la fecha de facturacion ingresada
		  $fecha_facturacion=Fecha_db($fecha_facturacion);
		else //sino, usamos la fecha de entrega como fecha de facturacion
		{
		  $fecha_facturacion=$fecha_entrega;
		  $cuenta_corriente=0;
		}

		if($valor_dolar=="")
		 $valor_dolar=0;
		if($generar_reclamo_parte==1)
		 $generar_reclamo_parte=1;
		else
		 $generar_reclamo_parte=0;
		if($select_moneda=="-1" || !$select_moneda)
		  $select_moneda=1;
        if ($id_entidad == 0) $id_ent='NULL';
          else $id_ent= $id_entidad;
		if($presupuesto=="" ||$presupuesto==0)
          $es_presupuesto=0;
        elseif($presupuesto==1)
         $es_presupuesto=1;
		//saco el destino oc
		//$valores="$select_contacto,'$fecha','$fecha_entrega','$entrega','$cliente',$select_pago,$select_proveedor,'$notas',$select_moneda,'p',$valor_dolar,$generar_reclamo_parte,$flag_stock,$id_ent,'$notas_internas',$es_presupuesto,$select_dest_oc";
		$valores="$select_contacto,'$fecha','$fecha_entrega','$entrega','$cliente',$select_pago,$select_proveedor,'$notas',$select_moneda,'p',$valor_dolar,$generar_reclamo_parte,$flag_stock,$id_ent,'$notas_internas',$es_presupuesto,'$fecha_facturacion',$cuenta_corriente";
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
		  //al id de renglon prop correspondiente. Es decir guardamos el id_entrega_estimada
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
		if ($gastos_servicio_tecnico) //si esta asociado a RMA de producion
		{$campos.=",flag_honorario";
		 $valores.=",1";
		}
		if($internacional)
		{
		 $campos.=",internacional";
		 $valores.=",1";
		}

		$query="select nextval('orden_de_compra_nro_orden_seq') as nro_orden";
		$oc_nro=sql($query,"<br>Error al traer el nuevo numero de la OC<br>") or fin_pagina();
		$nro_orden=$oc_nro->fields["nro_orden"];

		$campos="nro_orden,".$campos;
		$valores=$nro_orden.", ".$valores;
		$q="insert into orden_de_compra ($campos) values ($valores)";
		sql($q,"<br>Error al insertar la orden de compra<br>") or fin_pagina();

		/*$q="select max(nro_orden) as nro_orden from orden_de_compra where fecha='$fecha'";
		$o=$db->Execute($q) or die($db->ErrorMsg()."<br>".$q);
		$nro_orden=$o->fields['nro_orden'];*/
		$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'de creacion','".$_ses_user['login']."','$f1')";
		sql($q,"<br>Error al insertar el log de la OC<br>") or fin_pagina();

		//insertamos los datos propios de la OC internacional, en caso de que la OC sea internacional
		if($internacional)
		{
		 $query="insert into datos_oc_internacional(nro_orden,id_despachante,fob,tipo_flete,monto_flete,honorarios_gastos,
		         direccion_proveedor,banco_proveedor,dir_banco_proveedor,swift_proveedor,nombre_despachante,
		         mail_despachante,telefono_despachante)
		         values($nro_orden,$id_despachante,$fob,'$tipo_flete',$monto_flete,$honorarios_gastos,'$direccion_proveedor',
		         '$banco_proveedor','$dir_banco_proveedor','$swift_proveedor','$nombre_despachante','$mail_despachante',
		         '$telefono_despachante')
		         ";
		 sql($query,"<br>Error al insertar los datos de la oc internacional<br>") or fin_pagina();

		}//de if($internacional)

		$usuarios="select id_usuario from sistema.usuarios where avisar_oc=1";
        $res_usuarios=sql($usuarios,"<br>Error al traer el id de usuario<br>") or fin_pagina();
        $res_usuarios->Move(0);
        $campos_avisar_oc="id_usuario, nro_orden";
        while (!$res_usuarios->EOF)
        {
           if ($_POST["mail_".$res_usuarios->fields['id_usuario']])
           {
             $usuario=$res_usuarios->fields['id_usuario'];
             $consulta="insert into avisar_oc ($campos_avisar_oc) values ($usuario, $nro_orden)";
             sql($consulta,"<br>Error al insertar los avisos de la OC<br>") or fin_pagina();
           }
         $res_usuarios->MoveNext();
        }

	}//de if ($nro_orden==-1) --->Es decir, una OC nueva
	else//actualizamos la OC previamente guardada
	{   //traemos la plantilla de pago que habia sido guardada antes
	    //(ESTO SOLO SE HACE SI EL PROVEEDOR NO ES STOCK)
	    if(!$es_stock)
	    {
	     $query="select id_plantilla_pagos from orden_de_compra where nro_orden=$nro_orden";
	     $plantilla=sql($query,"<br>Error al traer el id de la plantilla de pago de la OC<br>") or fin_pagina();

	     //si la plantilla es default entonces hacemos una copia de esa plantilla
	     //para que se pueda actualizar si es necesario, en el futuro
	     $query="select mostrar from plantilla_pagos where id_plantilla_pagos=$select_pago";
	     $muestra_plantilla=sql($query,"<br>Error al decidir si la plantilla de pago de la OC es default o no<br>") or fin_pagina();

	     if($muestra_plantilla->fields['mostrar']==1)
	     {$select_pago=duplicar_plantilla_default($select_pago,$nro_orden);
	      $accion="dividir";
	     }

	    if($select_pago!=$plantilla->fields['id_plantilla_pagos'])
		{
		 //seleccionamos las ordenes de pagos vieja si es que hay y las borramos
         $query="select id_pago from ordenes_pagos join pago_orden using(id_pago) join orden_de_compra using (nro_orden) where nro_orden=$nro_orden";
         $res_ord_pago=sql($query,"<br>Error en la seleccion de ordenes de pago<br>") or fin_pagina();

         //borramos las entrada de pago_orden viejas si es que hay
         $query="delete from pago_orden where nro_orden=$nro_orden";
         $a=sql($query,"<br>Error en el borrado de pago de orden<br>") or fin_pagina();

         while(!$res_ord_pago->EOF)
         {$query="delete from ordenes_pagos where id_pago=".$res_ord_pago->fields['id_pago'];
          sql($query,"<br>Error en el borrado de ordenes de pago<br>") or fin_pagina();
          $res_ord_pago->MoveNext();
         }
		}//de if($select_pago!=$plantilla->fields['id_plantilla_pagos'])
	   }//de if(!$es_stock)

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
		if ($id_entidad == 0) $id_ent='NULL';
          else $id_ent= $id_entidad;
        if (!$orden_prod)
           $orden_prod="NULL";

        if($cuenta_corriente)//si tiene cuenta corriente habilitada, usamos la fecha de facturacion ingresada
		  $fecha_facturacion=Fecha_db($fecha_facturacion);
		else //sino, usamos la fecha de entrega como fecha de facturacion
		{
		  $fecha_facturacion=Fecha_db($fecha_entrega);
		  $cuenta_corriente=0;
		}

        if($presupuesto=="" ||$presupuesto==0)
          $es_presupuesto=0;
        elseif($presupuesto==1)
         $es_presupuesto=1;

		$q="update orden_de_compra set ".
		"fecha_entrega='".Fecha_db($fecha_entrega)."',".
		"lugar_entrega='".$entrega."',".
		"cliente='".$cliente."',".
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
		"id_entidad=$id_ent,".
		"es_presupuesto=$es_presupuesto,".
		"fecha_facturacion='$fecha_facturacion',".
		"cuenta_corriente=$cuenta_corriente,";
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
		sql($q,"<br>Error al actualizar la OC<br>") or fin_pagina();

		$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'de modificación','".$_ses_user['login']."','$f1')";
		sql($q,"<br>Error al insertar el log de modificación OC<br>") or fin_pagina();
		if($_POST['pagina_asoc']=="asociar")
		{$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'de asociacion','".$_ses_user['login']."','$f1')";
		 sql($q,"<br>Error al insertar el log de asociación de OC<br>") or die($db->ErrorMsg()."<br>".$q);
		}

		//actualizamos los datos propios de la OC internacional, en caso de que la OC sea internacional
		if($internacional)
		{
		 $query="update datos_oc_internacional set nro_orden=$nro_orden,id_despachante=$id_despachante,fob=$fob,
		         tipo_flete='$tipo_flete',monto_flete=$monto_flete,honorarios_gastos=$honorarios_gastos,
		         direccion_proveedor='$direccion_proveedor',banco_proveedor='$banco_proveedor',
		         dir_banco_proveedor='$dir_banco_proveedor',swift_proveedor='$swift_proveedor',
		         nombre_despachante='$nombre_despachante',mail_despachante='$mail_despachante',
		         telefono_despachante='$telefono_despachante'
                 where id_oc_internacional=$id_oc_internacional";
		 sql($query,"<br>Error al actualizar los datos de la oc internacional<br>") or fin_pagina();

		}//de if($internacional)

		/*
		 $actualizar_avisar_oc="delete from avisar_oc where nro_orden=$nro_orden ";
		 sql($actualizar_avisar_oc,"<br> Error al eliminar los aviso de la OC<br>") or fin_pagina();

		 $usuarios="select id_usuario from sistema.usuarios where avisar_oc=1";
		 $res_usuarios=sql($usuarios,"<br>Error al traer usuarios<br>") or fin_pagina();
		 $campos_avisar_oc="id_usuario, nro_orden";

		 $mails=PostvartoArray('mail_'); //crea un arreglo con los checkbox chequeados
		 $tam_mails=sizeof($mails);

		 if ($mails){  //para ver si hay check seleccionados
		  foreach($mails as $key => $value)
		  {
		   $consulta="insert into avisar_oc ($campos_avisar_oc) values ($value, $nro_orden)";
		   $db->Execute($consulta) or die($db->ErrorMsg()."<br>".$consulta);
		  }
		 }*/

 }//del else de if ($nro_orden==-1)

	//organiza los datos provenientes de la pagina anterior
	$items=get_items();

	//elimina los items que no estan en items
	del_items($items,$nro_orden);

	 //modificamos el arreglo que le pasamos a la funcion replace para que
     //no intente insertar el campo proveedores y proveedores_cantidad
     //(porque eso es lo que intenta hacer si no los sacamos del $items)
	 $sacar=array();
	 $index_sacar=0;
     if($es_stock)
     {
      $sacar[$index_sacar++]="proveedores";
     }
     if(!$internacional)
     {
      $sacar[$index_sacar++]="id_posad";
      $sacar[$index_sacar++]="proporcional_flete";
      $sacar[$index_sacar++]="base_imponible_cif";
      $sacar[$index_sacar++]="derechos";
      $sacar[$index_sacar++]="iva";
      $sacar[$index_sacar++]="ib";
     }

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
		$prod_desc=sql($query,"<br>Error al traer las filas de la orden de compra Nº $nro_orden<br>") or fin_pagina();
        $descripcion_temp="";
        while(!$prod_desc->EOF)
        {
         $descripcion_temp.=$prod_desc->fields['descripcion_prod']." ".$prod_desc->fields['desc_adic']."\n";
         $prod_desc->MoveNext();
        }
        $query="update orden_de_compra set desc_prod='$descripcion_temp' where nro_orden=$nro_orden";
        sql($query,"<br>Error al actualizar la descripcion de productos en la OC<br>") or fin_pagina();

        $msg="<b> Su Orden Nº $nro_orden se guardo con éxito";

    	//Si el proveedor es un stock, entonces se realizan las reservas correspondientes en el stock
    	//(esto es: descontamos de la cantidad disponible y lo agregamos la cantidad reservada del stock para ese producto)
        if($es_stock)
    	{
	    	 //primero obtenemos el nombre del proveedor seleccionado
	         $query="select razon_social from proveedor where id_proveedor=$select_proveedor";
	         $id_proveedor=sql($query,"<br>Error al traer el nombre del proveedor.<br>") or fin_pagina();
	         switch($id_proveedor->fields['razon_social'])
	         {case "Stock San Luis":$dep="San Luis";break;
	          case "Stock Buenos Aires":$dep="Buenos Aires";break;
	          case "Stock ANECTIS":$dep="ANECTIS";break;
	          case "Stock SICSA":$dep="SICSA";break;
	          case "Stock New Tree":$dep="New Tree";break;
	          case "Stock Serv. Tec. Bs. As.":$dep="Serv. Tec. Bs. As.";break;
	         }

	         //se selecciona el id del deposito
	         $query="select id_deposito from depositos where nombre='$dep'";
	         $id_dep=sql($query,"<br>Error al traer id del deposito<br>") or fin_pagina();
	    	 $id_deposito=$id_dep->fields["id_deposito"];

	    	 if($id_deposito=="")
	    	  die("<br>Error: no se pudo determinar el deposito. Contacte a la division software<br>");

		     //traemos el id de tipo de movimiento: "Reserva de productos para OC o para Movimiento de material"
		     $query="select id_tipo_movimiento from tipo_movimiento where nombre='Reserva de productos para OC o para Movimiento de material'";
		     $id_movq=sql($query,"<br>Error al traer el id del tipo de movimiento reserva<br>") or fin_pagina();
		     $id_tipo_movimiento=$id_movq->fields["id_tipo_movimiento"];
		     if($id_tipo_movimiento=="")
		      die("<br>Error: no se pudo determinar el tipo de movimiento de stock. Contacte a la division software<br>");

		     //traemos el id del tipo de reserva: "Reserva de productos para OC"
		     $query="select id_tipo_reserva from tipo_reserva where nombre_tipo='Reserva de productos para OC'";
		     $id_res_t=sql($query,"<br>Error al traer el tipo de reserva de <br>") or fin_pagina();
		     $id_tipo_reserva=$id_res_t->fields["id_tipo_reserva"];
		     if($id_tipo_reserva=="")
		      die("<br>Error: no se pudo determinar el tipo de reserva de stock. Contacte a la division software<br>");

		     $items_reserva=get_items($nro_orden);
		     for($h=0;$h<$items_reserva["cantidad"];$h++)
		     {
		      $id_prod_esp=$items_reserva[$h]["id_prod_esp"];
		      $id_fila=$items_reserva[$h]["id_fila"];
		      $cantidad=$items_reserva[$h]["cantidad"];
		      $comentario="Reserva de productos generados por la Orden de Compra Nº $nro_orden";

		      //hacemos la reserva para la fila, solo si no tiene hecha ya la reserva correspondiente (fue hecha en una guardada anterior)
		      //para eso consultamos la tabla detalle_reserva para ver si hay una entrada para la fila
		      $query="select cantidad_reservada from detalle_reserva where id_fila=$id_fila";
		      $result_fila=sql($query,"<br>Error al consultar las reserva de la fila Nº $id_fila<br>") or fin_pagina();
		      //si la consulta no devuelve nada se hace la reserva
		      if($result_fila->RecordCount()==0)
		       reservar_stock($id_prod_esp,$cantidad,$id_deposito,$comentario,$id_tipo_movimiento,$id_tipo_reserva,$id_fila);

		     }//de for($h=0;$h<$items_reserva;$h++)

    	}//de if($es_stock)

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


        if ($id_renglon_prop)
        {
          //inserto en oc_pp
          get_ids_fila($productos_pres,$items_replace);
//          print_r($productos_pres);die;
          if (count($productos_pres) > 0) {
             insertar_oc_pp($productos_pres);
             relacionar_presupuesto_oc($nro_orden,$id_renglon_prop);
             }
        }//de if ($id_renglon_prop)

	}//de if (replace("fila",$items_replace,array("id_fila")) == 0)
	else
	{
 	  $msg="<b> 2 - NO SE PUDO GUARDAR LA ORDEN $query".$db->errormsg();
	}
$db->Completetrans();

}//de if ($boton_guardar=="Guardar" || $guardar > 0)

if ($boton_autorizar=="Autorizar")
{
 $db->StartTrans();
     $msg="";
   autorizar_oc($nro_orden,$modo,$select_proveedor,$es_stock,$internacional);
  $db->CompleteTrans();
}//de if ($boton_autorizar=="Autorizar")

if($guardar_transporte=="Guardar")
{
	$db->StartTrans();
	//traemos los id del producto "Transporte" del proveedor "licitacion"
	//para insertarlo en la OC como una nueva fila
	$query="select id_producto from general.productos
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
}//de if($guardar_transporte=="Guardar")

if ($h_rechazar=="Rechazar")
{   $db->StartTrans();
	$q="update orden_de_compra set estado='r' where nro_orden=$nro_orden";
	sql($q,"<br>Error al actualizar el estado de la OC a rechazada<br>") or fin_pagina();

	$q="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha,otros) values ($nro_orden,'de rechazo','".$_ses_user['login']."','$f1','".$_POST["comentario_rechazar"]."')";
	sql($q,"<br>Error al insertar el log de la OC, cuando se pasa a rechazada<br>") or fin_pagina();

	//(ESTO SOLO SE HACE SI EL PROVEEDOR NO ES STOCK)
    if(!$es_stock)
    {
     //si se rechaza la orden, se deben eliminar las entradas de
	 //ordenes_pagos para esa orden
	 //primero se controla si ya se realizaron pagos, en cuyo caso se
	 //agrega al mail que se envia, el detalle de los pagos ya realizados
	 $query="select id_pago,id_ingreso_egreso,iddébito,idbanco,númeroch,ordenes_pagos.valor_dolar from ordenes_pagos join pago_orden using(id_pago) join orden_de_compra using (nro_orden) where nro_orden=$nro_orden";
	 $pagos_realizados=$db->Execute($query) or die($db->ErrorMsg()."<br>".$query);
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
	 sql($query,"Error al eliminar los pagos de la OC rechazada") or fin_pagina();
	 $pagos_realizados->Move(0);
	 while(!$pagos_realizados->EOF)
	 {
	  $query="delete from ordenes_pagos where id_pago=".$pagos_realizados->fields['id_pago'];
	  sql($query,"<br>Error al eliminar ordenes de pagos para la OC<br>") or die($db->ErrorMsg()."del delete de ordenes_pagos");
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

     if($internacional)
      $texto_int="Internacional";
     else
      $texto_int="";
	 $asunto="La Orden de Compra $texto_int Nº $nro_orden ha sido rechazada";
	 $mail_text="";
	 $mail_text .= "La Orden de Compra $texto_int Nº $nro_orden ha sido rechazada\n";
	 $mail_text .= "\nUsuario que rechazó: ".$_ses_user["name"]."\n";
	 $mail_text .= "\nJustificación del rechazo:\n";
	 $mail_text .= "--------------------------------------------------------------\n";
	 $mail_text .= $_POST["comentario_rechazar"]."\n";
	 $mail_text .= "\n--------------------------------------------------------------\n\n";
	 $mail_text .= detalle_orden($nro_orden);

	 $query="select distinct user_login,mail from log_ordenes
	         join usuarios on(user_login=login)
	         where nro_orden=$nro_orden and user_login<>'".$_ses_user['login']."' and user_login<>'juanmanuel'";
	 $para_users=sql($query,"<BR>Error al traer usuarios de rechazo<br>") or fin_pagina();
	 $para="juanmanuel@coradir.com.ar";
	 while(!$para_users->EOF)
	 {
	  $para.=",".$para_users->fields["mail"];
	  $para_users->MoveNext();
	 }
	 //echo "<br>Para $para<br>";
	 //echo "<br><br>TEXT:<br> $mail_text<br>";
     if($para!="")
      enviar_mail ($para,$asunto,$mail_text,"","","",0);
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

   $db->CompleteTrans();
}//de if ($h_rechazar=="Rechazar")

if ($h_anular=="Anular")
{
 $db->StartTrans();
 anular_oc($nro_orden,$internacional);
 $db->CompleteTrans();
}//de if ($h_anular=="Anular")

if ($boton_para_autorizar=="Para Autorizar")
{
  $db->StartTrans();
  $sql="update orden_de_compra set estado='u' where nro_orden=$nro_orden";
  if (sql($sql,"<br>Error al pasar a para autorizar la OC (CM)<br>") or fin_pagina())
  {
  	    //si es una Orden de Servicio Tecnico, no se envia el mail de Para Autorizar
	   	if($modo=="oc_serv_tec")
	   	 $mandar_mail=0;
	   	else//Si es una OC comun
         $mandar_mail=1;

       	$msg="Su Orden Nº $nro_orden se actualizó exitosamente";
   		$sql="insert into log_ordenes (nro_orden,tipo_log,user_login,fecha) values ($nro_orden,'para autorizar','".$_ses_user['login']."','$f1')";
   		sql($sql,"<br>Error al guardar el log de para autorizar (CM)<br>") or fin_pagina();
  }//de if (sql($sql,"<br>Error al pasar a para autorizar la OC (CM)<br>") or fin_pagina())
  else
	    $msg="<b> NO SE PUDO ACTUALIZAR LA ORDEN";



   if ($mandar_mail)
   {
                 $para="corapi@coradir.com.ar";
                 $mailtext=$_POST['contenido'];
                 if($internacional)
                  $texto_int="Internacional";
                 else
                  $texto_int="";
                 $asunto="Orden de Compra $texto_int Nº:$nro_orden en condición de ser Autorizada";
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

   }//de if ($mandar_mail)

   if (($destino_para_autorizar=="ord_compra_listar.php")&&($accion=="dividir")&&(!$es_stock))
   {            $query="select count(pago_plantilla.id_forma) as cant_pagos from orden_de_compra join plantilla_pagos using(id_plantilla_pagos) left join pago_plantilla using (id_plantilla_pagos) where nro_orden=$nro_orden";
				$count_pagos=sql($query,"<br>Error al buscar la cantidad de pagos de la OC<br>") or fin_pagina();
				$nro_pagos=$count_pagos->fields['cant_pagos'];
				$monto_pago=monto_a_pagar($nro_orden,$internacional);
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
				$moneda=sql($q,"<br>Error al seleccionar el id de dolar<br>") or fin_pagina();
				if($select_moneda==$moneda->fields['id_moneda'])
				 insertar_ordenes_pagos($nro_orden,$cuotas,$dolar);
				else
				 insertar_ordenes_pagos($nro_orden,$cuotas);
	 }//de if (($destino_para_autorizar=="ord_compra_listar.php")&&($accion=="dividir")&&(!$es_stock))

  $db->CompleteTrans();
} //del boton Para autorizar
elseif($boton_habilitar_pago_especial=="Habilitar Pago Especial")
{$db->StartTrans();
 $query="update orden_de_compra set habilitar_pago_especial=1 where nro_orden=$nro_orden";
 if(sql($query,"<br>Error al habilitar el pago especial<br>") or fin_pagina())
 {$msg="Se habilitó el pago especial para la orden Nº $nro_orden";
 }
 else
 {$msg="<b> No se pudo habilitar el pago especial para la orden Nº $nro_orden";
 }
 $db->CompleteTrans();
}//de elseif($boton_habilitar_pago_especial=="Habilitar Pago Especial")
elseif($boton_deshabilitar_pago_especial=="Deshabilitar Pago Especial")
{$db->StartTrans();
 $query="update orden_de_compra set habilitar_pago_especial=0 where nro_orden=$nro_orden";
 if(sql($query,"<br>Error al deshabilitar el pago especial<br>") or fin_pagina())
 {$msg="<b> Se deshabilitó el pago especial para la orden Nº $nro_orden";
 }
 else
 {$msg="<b> No se pudo deshabilitar el pago especial para la orden Nº $nro_orden";
 }
 $db->CompleteTrans();
}//de elseif($boton_deshabilitar_pago_especial=="Deshabilitar Pago Especial")

//Despagar Orden de Compra
if($despagar=="D$")
{
 $db->StartTrans();
 include_once("fns_especial.php");
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
 include_once("fns_especial.php");

 $select_proveedor=$id_proveedor_a;
 des_recibir_oc($nro_orden);

 //anulamos la OC.
 $_POST['comentario_anular']="Anulado por sistema debido a que no se usó la parte para el Servicio Técnico";
 anular_oc($nro_orden);

 $msg="<center><b>La Orden de Compra $nro_orden, se anuló con éxito</b></center>";
 $destino_para_autorizar="ord_compra_listar.php";
 $db->CompleteTrans();
}//de if($anular_con_stock=="Anular OC")


if ($gen_remito_int)
 $link=encode_link($destino_mostrar_remito,array('id_remito'=>$nro_remito_interno,'pagina_viene'=>'ord_compra_recepcion'));
elseif($modo=="oc_serv_tec")
 $link=encode_link("listado_oc_serv_tec.php",array('nro_orden'=>$nro_orden,'msg'=>$msg,'pagina_viene'=>'ord_compra'));
else
 $link=encode_link($destino_para_autorizar,array('nro_orden'=>$nro_orden,'msg'=>$msg,'pagina_viene'=>'ord_compra'));

header("location: $link");
?>