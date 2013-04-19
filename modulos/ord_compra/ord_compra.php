<?
/*
Autor: GACZ - Marco - Fernando
----------------------------------------
Nueva Versión
 Autor: MAC
 Fecha: 16/05/2005
----------------------------------------

MODIFICADA POR
$Author: fernando $
$Revision: 1.381 $
$Date: 2007/03/01 20:29:38 $

*/

/******************************************************************************************************************************
 ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION
*******************************************************************************************************************************
 POR FAVOR INTENTEN MANTENER EL ORDEN QUE EL ARCHIVO ESTA INTENTANDO MANTENER (UN POCO DE ORDEN ES MEJOR QUE NADA DE ORDEN)
       Nota: Si se agrega una seccion nueva (indicada por '-' y letra mayuscula) o una subseccion nueva (indicada por '*'),
             agregarla con el comentario como el resto de las mismas, y ponerlas en este pseudo-indice.

 LAS SECCIONES EXISTENTES HASTA AHORA SON:
 -ACTIVIDADES RELACIONADAS CON ASOCIACION DE LA OC
   *Asociadas a PRESUPUESTOS DE LICITACIONES AUTOR: GACZ
   *Asociado a Licitacion (o presupuesto)
   *Asociado al servicio tecnico
   *Asociada a los honorarios de los servicios tecnicos
   *Asociada a los honorarios de los servicios tecnicos
   *Asociada a Stock
 -RECOPILACION DE DATOS DE LA OC Y SETEO DE VARIABLES PARA MOSTRAR LOS DATOS DE LA MISMA
 -SETEO DE PERMISOS PARA LOS BOTONES Y DEMAS FUNCIONALIDADES DE LA PAGINA
 -DATOS DE LA OC Y MAS:
   *Traemos y mostramos el Log de la OC
   *Decisión de a qué esta asociada la OC
   *Mostramos los datos propios de la OC
     **Mostramos campos internos de Coradir
     **Generamos el select con los proveedores para elegir
       aquel que se usara para generar el RMA.
     **Mostramos a quien se envio la OC si el estado es mayor
       que autorizada (enviada, p. pagada o t. pagada)
 -LISTA DE PRODUCTOS DE LA OC:
   *Generacion de Filas: HONORARIO DE SERVICIO TECNICO
   *Generacion de Filas: CASOS
   *Generacion de Filas: LICITACION Y PRESUPUESTO
   *Generacion de Filas: CUANDO SE ABRE UNA ORDEN DESDE EL LISTADO
   *Generacion de Filas: CUANDO SE HACE UNA ORDEN DESDE SEGUIMIENTO PRODUCCION PARA UN PRESUPUESTO DE PROVEEDOR DETERMINADO
   *Generacion de Filas: CUANDO LA ORDEN ES NUEVA Y/O SE RECARGA LA PAGINA
 -CONTROL INTERNO PROVEEDORES
 -BOTONERA
 -FIN DE PAGINA
/******************************************************************************************************************************
 ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION
*******************************************************************************************************************************/

require_once("../../config.php");
require_once("fns.php");

//El $modo indica que tipo de Orden se esta cargando: Orden de Compra, Orden de Servicio Tecnico, Orden de Pagos
//(La OC Internacional se conoce por la variable $internacional)
$modo=$parametros["modo"] or $modo=$_POST["modo"];

extract($_POST,EXTR_SKIP);

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

$gastos_servicio_tecnico=$_POST["gastos_servicio_tecnico"] or $gastos_servicio_tecnico=$parametros["gastos_servicio_tecnico"];
if(!$nro_orden)
 $nro_orden=-1;

//vemos quien es el usuario para saber si desplegar el detalle de la forma
//de pago automaticamente o no. Si es Corapi, aparece desplegado de entrada, sino no.
if($_ses_user['login']=="corapi")
	$usuario_corapi=1;
else
    $usuario_corapi=0;

//borra la variables de sesion una vez que se asocio la OC a donde se deseaba (licitaciones,CAS,etc)
if($_ses_global_backto)
{
	//extract($_ses_global_extra,EXTR_SKIP);
 	phpss_svars_set("_ses_global_backto", "");
 	phpss_svars_set("_ses_global_extra", array());
}//de if ($_ses_global_backto)

if($_ses_global_back) //borra varialbes en caso que venga de orden de produccion
{
	phpss_svars_set("_ses_global_back", "");
	phpss_svars_set("_ses_global_nro_orden_asociada", "");
	phpss_svars_set("_ses_global_pag", "");
}//de if($_ses_global_back)

/*************************************************************************************************************
 SECCION: ACTIVIDADES RELACIONADAS CON ASOCIACION DE LA OC
**************************************************************************************************************/

//Asociadas a PRESUPUESTOS DE LICITACIONES AUTOR: GACZ
if ($id_renglon_prop)
{
	/* */
 	$q ="SELECT desc_orig,desc_adic,unitario,id_producto_presupuesto,t1.cantidad,t1.id_producto,
 				case when t2.cantidad_oc is null then 0 else t2.cantidad_oc end as cantidad_oc,
 				cantidad_comprar
 		FROM ";

	 	//productos de los que se deberia hacer OC agrupados para sumar cantidades
	 	$q.="(SELECT pp.id_producto,pp2.monto_unitario as unitario,pp.desc_orig,pp.desc_adic,pp2.id_proveedor,
	 			sum(rp.cantidad*pp.cantidad) as cantidad
	 		  FROM
	 		   licitaciones.renglon_presupuesto_new rp
	 	       join licitaciones.producto_presupuesto_new pp on rp.id_renglon_prop=pp.id_renglon_prop AND rp.id_renglon_prop in ($id_renglon_prop)
	 	       join licitaciones.producto_proveedor_new pp2 on pp.id_producto_presupuesto=pp2.id_producto_presupuesto and pp2.id_proveedor=$select_proveedor
	 	      where activo=1
			  group by pp.id_producto,unitario,pp.desc_orig,pp.desc_adic,pp2.id_proveedor
	 	     ) t1 ";

 	$q.="join ";

	 	//productos de los que se deberia hacer OC solo IDs necesarios
	 	//que no se pudieron recuperar en la consulta anterior
	 	$q.="(SELECT pp.id_producto,pp.id_producto_presupuesto,pp2.cantidad as cantidad_comprar
	 		  FROM
	 			licitaciones.renglon_presupuesto_new rp
	 			join licitaciones.producto_presupuesto_new pp on rp.id_renglon_prop=pp.id_renglon_prop AND rp.id_renglon_prop in ($id_renglon_prop)
	 			join licitaciones.producto_proveedor_new pp2 on pp.id_producto_presupuesto=pp2.id_producto_presupuesto and pp2.id_proveedor=$select_proveedor
	 			where activo=1
	 		) t3 using(id_producto) ";

 	$q.="left join ";

	 	//productos de los que se hizo OC agrupados para descontar las unidades
	 	//de diferentes OC y del mismo producto
	 	$q.="(SELECT oc_pp.id_producto_presupuesto,f.id_producto,f.descripcion_prod,sum(oc_pp.cantidad_oc) as cantidad_oc
	 		  FROM compras.oc_pp
	 			join licitaciones.producto_presupuesto_new pp using(id_producto_presupuesto)
	 			join compras.orden_de_compra oc using(nro_orden)
	 			join compras.fila f on f.id_producto=pp.id_producto AND f.nro_orden=oc.nro_orden
	 		  where oc.estado!='n'
	 		  group by oc_pp.id_producto_presupuesto,f.id_producto,f.descripcion_prod
	 		) t2 using(id_producto_presupuesto) ";

 	$q.="order by t1.id_producto ";

	$prod_seg=sql($q,"<br>Error al consultar productos de presupuesto<br>") or fin_pagina();

 	/*//VERIFICO QUE EXISTA EN STOCK SI EL PROVEEDOR ES STOCK
  $q ="select d.id_deposito,proveedor.nombre
	   from general.proveedor
       join general.depositos d on d.nombre=substring(razon_social from 'Stock (.*)')
       where proveedor.id_proveedor=$select_proveedor";
  $deposito=sql($q,"<br>Error al verificar la existencia del proveedor tipo Stock") or fin_pagina();
  //si es un proveedor STOCK
  if ($deposito->recordcount())
  {
  	    DIE("<b>ATENCION: No se pueden hacer mas presupuestos de licitación con proveedores de tipo Stock. Por favor elija un proveedor que no sea Stock.<b>");
		$id_deposito=$deposito->fields['id_deposito'];
		$nbre_deposito=$deposito->fields['nombre'];
  }
	else*/
		$id_deposito='';
  $aprod_seg=array();
  $i=0;
  
  while (!$prod_seg->EOF)
  {
  	$id_producto=$prod_seg->fields['id_producto'];
  	$desc_adic=$prod_seg->fields['desc_adic'];
  	$desc_orig=$prod_seg->fields['desc_orig'];
  	$desc=$desc_orig." ".$desc_adic;
  	$cantidad=$prod_seg->fields['cantidad'];//cantidad total de productos a comprar para TODOS los proveedores
  	$cantidad_oc=0;
  	//EL CONTROL de cuanto se puede comprar por proveedor se hace en Seguimiento de presupuesto
  	$cantidad_comprar=0;//indica la cantidad de productos que falta comprar por PROVEEDOR
  	$unitario=$prod_seg->fields['unitario'];
  	$id_producto_presupuesto="";//contiene los ids de los productos_presupuesto
  	$cantidades_pres="";//contiene las cantidades a comprar para cada id_producto_presupuesto en la variable de arriba
  	$add_coma="";
  	$link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$id_producto));//historial de comentarios
  	//Busco los id_producto_presupuesto para los
  	//productos repetidos y resto las cantidades
  	do
  	{
  		$id_producto_presupuesto.=$add_coma.$prod_seg->fields['id_producto_presupuesto'];
  		$cantidades_pres.=$add_coma.$prod_seg->fields['cantidad_comprar'];
  		$cantidad_oc+=$prod_seg->fields['cantidad_oc'];
  		$add_coma=",";
  		$cantidad_comprar+=$prod_seg->fields['cantidad_comprar'];
  		$prod_seg->MoveNext();
  	}
  	while (!$prod_seg->EOF && $prod_seg->fields['id_producto']==$id_producto);
  	//si no se compro TODO orden de compra
  	if (($cantidad-=$cantidad_oc)>0)
  	{
  		$aprod_seg[$i]['id_producto']=$id_producto;
  		$aprod_seg[$i]['desc_orig']=$desc_orig;
  		$aprod_seg[$i]['desc_adic']=$desc_adic;
  		$aprod_seg[$i]['desc']=$desc;
  		$aprod_seg[$i]['cantidad']=$cantidad;
  		$aprod_seg[$i]['cantidad_oc']=$cantidad_oc;
  		//si la cantidad a comprar es menor que la CANTIDAD QUE FALTA COMPRAR => compro cantidad_comprar, sino solo lo que falta
  		$aprod_seg[$i]['cantidad_comprar']=$cantidad>=$cantidad_comprar?$cantidad_comprar:$cantidad;
  		$aprod_seg[$i]['unitario']=$unitario;
  		$aprod_seg[$i]['id_producto_presupuesto']=$id_producto_presupuesto;
  		$aprod_seg[$i]['cantidades_pres']=$cantidades_pres;
  		$aprod_seg[$i]['link_historial']=$link;
  		if ($id_deposito)
  		{
  			if (($prov_cant=stock_seleccionar_reserva($id_producto,$id_deposito,$aprod_seg[$i]['cantidad_comprar']))==0)
  			{
  				$msg="No hay Stock Suficiente<br> Deposito: '$nbre_deposito' <br> Producto: '$desc' <br> Cantidad: $cantidad";
  				echo("<center><font size=+1 color=red>$msg</font></center>");
                fin_pagina();
  			}
  			else
				 $aprod_seg[$i]['prov_cant']=$prov_cant;
  		}
  		$i++;
  	}//de if (($cantidad-=$cantidad_oc)>0)
  }//de while (!$prod_seg->EOF)
  $aprod_seg['cantidad']=$i;
}//de if ($id_renglon_prop)
/*************************************************************************************************************/

//Asociado a Licitacion (o presupuesto)
if ($licitacion)
{
   //si se asocia con una licitacion mediante un Seguimiento de Produccion
   //no se recuperan los productos del renglon original
   if ($licitacion && !$id_renglon_prop)
   {
   	//Traemos los productos de la licitacion
	//CONSULTA CON PRODUCTOS IDENTICOS AGRUPADOS PERO SEPARADOS SI DIFIERE EL PRECIO
	$campos="prod.id_producto,prod.desc_gral,p2.cantidad,p2.precio_licitacion as precio";
	$query="SELECT $campos from
	 (SELECT producto.id_producto,sum(producto.cantidad) as cantidad,(sum(producto.precio_licitacion)/count(producto.cantidad)) as precio_licitacion
	  from licitaciones.producto
	  where producto.id_renglon in (SELECT renglon.id_renglon from licitaciones.renglon where id_licitacion=$licitacion)
	  group by producto.id_producto,producto.precio_licitacion
	 ) p2
	  join general.productos prod using(id_producto)";

 	$productos= sql($query,"<br>Error al consultar productos de la OC (general)<br>") or fin_pagina();
   }//de if ($licitacion && !$id_renglon_prop)

 //elijo la entidad de la licitacion
 $sql="select id_entidad,nombre
 	   from licitaciones.licitacion
       join licitaciones.entidad using(id_entidad)
       where licitacion.id_licitacion=$licitacion";
 $result=sql($sql,"<br>Error al traer la entidad de la licitacion<br>") or fin_pagina();
 $cliente=$result->fields["nombre"];
 $id_entidad=$result->fields["id_entidad"];

}//de if ($licitacion)
/*************************************************************************************************************/

//Asociado al servicio tecnico
if ($caso && $pagina)
{
 $sql="select casos_cdr.idcaso,entidad.nombre,entidad.id_entidad
      from casos.casos_cdr
      join casos.dependencias using (id_dependencia)
      join licitaciones.entidad using (id_entidad)
      where casos_cdr.nrocaso=$caso";
 $resultado=sql($sql,"<br>Error al consultar datos del caso asociado<br>") or fin_pagina();
 $idcaso=$resultado->fields["idcaso"];

 $id_entidad=$resultado->fields["id_entidad"];
 $cliente=$resultado->fields["nombre"];

 $fecha_entrega=date("d/m/Y",mktime());

}//de if ($caso && $pagina)
/*************************************************************************************************************/

//Asociada a los honorarios de los servicios tecnicos
if ($idate && $pagina)
{
 //consulta para traer los honorarios de los CAS
 $sql="Select casos_cdr.*,cas_ate.id_proveedor
 	   from casos.cas_ate
       left join  casos.casos_cdr using (idate)
       where casos_cdr.idate=$idate and (casos_cdr.pagado=1 and (casos_cdr.pagado_orden=0 or casos_cdr.pagado_orden is NULL))
       order by casos_cdr.nrocaso ASC
      ";


  $casos=sql($sql,"<br>Error al traer datos de Honorarios de Casos<br>") or fin_pagina();
  //print_r($casos->fields);
  /*selecciono los datos que van a elegirse automaticamente*/
  $select_proveedor=$casos->fields["id_proveedor"];

  $fecha_entrega=date("d/m/Y",mktime());
  $select_moneda=1;
  //entidad = Coradir SA
  $sql="select entidad.id_entidad,entidad.nombre from licitaciones.entidad where entidad.id_entidad=441";
  $resultado=sql($sql,"<br>Error al traer la entidad Coradir para Honorario de Serv Tec<br>") or fin_pagina();
  $id_entidad=$resultado->fields["id_entidad"];
  $cliente=$resultado->fields["nombre"];

 //traigo el producto que va a pagar
  $sql="Select * from general.productos where productos.desc_gral='Gastos de Servicio Tecnico'";
  $productos=sql($sql,"<br>Error al traer el producto Gastos de Servicio Tecnico<br>") or fin_pagina();

}//de if ($idate && $pagina)
/*************************************************************************************************************/

//Asociada a Stock
if($flag_stock)
{
  //entidad = Coradir SRL
  $sql="select entidad.id_entidad,entidad.nombre from licitaciones.entidad where entidad.id_entidad=441";
  $resultado=sql($sql,"<br>Error al traer la entidad Coradir para Stock<br>") or fin_pagina();
  $id_entidad=$resultado->fields["id_entidad"];
  $cliente=$resultado->fields["nombre"];

}//de if ($flag_stock)
/*************************************************************************************************************/

//Asociada a un RMA de produccion
/*if ($orden_prod)
{
   $sql="select entidad.id_entidad,entidad.nombre
         from orden_de_produccion
         join entidad using(id_entidad)
         join licitacion using(id_licitacion)
         where nro_orden=$orden_prod";
  $resultado=sql($sql,"<br>Error al traer la entidad de la Orden de Produccion de RMA<br>") or fin_pagina();
  $id_entidad=$resultado->fields["id_entidad"];
  $cliente=$resultado->fields["nombre"];

}//de if ($orden_prod)
*/
/*************************************************************************************************************
 FIN DE SECCION: ACTIVIDADES RELACIONADAS CON ASOCIACION DE LA OC
**************************************************************************************************************/

$items=0;

/*************************************************************************************************************
 SECCION: RECOPILACION DE DATOS DE LA OC Y SETEO DE VARIABLES PARA MOSTRAR LOS DATOS DE LA MISMA
**************************************************************************************************************/
if ($nro_orden && $nro_orden!=-1)//la OC esta cargada ya en la BD (proviene desde el listado de OC, o similar)
{
		$q="select * from compras.fila where fila.nro_orden=$nro_orden";
		$filas=sql($q,"<br>Error al traer los datos de las filas de la OC<br>") or fin_pagina();

		$q="select orden_de_compra.* from compras.orden_de_compra where orden_de_compra.nro_orden=$nro_orden";
		$orden=sql($q,"<br>Error al traer los datos propios de la OC<br>") or fin_pagina();

		//si la OC esta asociada a Licitacion o Presupuesto, traemos el color y estado de la Licitacion o Presupuesto
		if($orden->fields['id_licitacion']!="")
		{
		 $query="select estado.nombre,estado.color from licitaciones.estado join licitaciones.licitacion using (id_estado)
		 		where licitacion.id_licitacion=".$orden->fields['id_licitacion'];
		 $color_estado=sql($query,"<br>Error al traer el color del estado de la Licitación<br>") or fin_pagina();
		 $estado_lic_color=$color_estado->fields["color"];
		 $estado_lic_nombre=$color_estado->fields["nombre"];
		}
		else
		{$estado_lic_color="";
		 $estado_lic_nombre="";
        }
		//tengo en dos variables lo que trajo de orden de compra
		//el chequeado_avisar y la descripcion
        $chequeado_avisar=$orden->fields['chequeado_avisar'];
        $descripcion_avisar=$orden->fields['descripcion_avisar'];
        $estado_avisar=$orden->fields['estado'];

        //si no estamos en una Orden de Servicio Tecnico
        if($modo!="oc_serv_tec")
        {
		 //traemos los datos de las notas de credito asociadas (si es que hay)
		 $query="select id_nota_credito,nota_credito.monto,nota_credito.observaciones,oc.valor_dolar,id_moneda,simbolo
		 from (select n_credito_orden.id_nota_credito,n_credito_orden.nro_orden,
		 	   	 	  n_credito_orden.valor_dolar,n_credito_orden.usuario,n_credito_orden.fecha
		 	   from compras.n_credito_orden
		 	   where n_credito_orden.nro_orden=$nro_orden) as oc
         join general.nota_credito using(id_nota_credito)
		 join licitaciones.moneda using(id_moneda)";
         $notas_credito=sql($query,"<br>Error al traer las notas de credito relacionadas con la orden de compra Nº $nro_orden<br>") or fin_pagina();
        }

        if (!$select_proveedor && !$select_provee)
        {
	      $select_proveedor=$orden->fields['id_proveedor'];
	      $select_provee='t';
        }
        if (!$fecha_entrega)
        {
          $fecha_entrega=date("j/m/Y",strtotime($orden->fields['fecha_entrega']));
 	    }
 	    if($cuenta_corriente=="")
 	    {
 	     $cuenta_corriente=$orden->fields['cuenta_corriente'];
 	    }

        if(!$fecha_facturacion)
        {
         $fecha_facturacion=fecha($orden->fields['fecha_facturacion']);
        }

 	    //si la OC cargada desde el listado es internacional, traemos los datos de la parte internacional de esta OC
 	    if($orden->fields['internacional'])
 	    {
 	     $query="select int.id_oc_internacional,int.id_despachante,int.fob,int.tipo_flete,int.monto_flete,int.honorarios_gastos,
                 int.direccion_proveedor,int.banco_proveedor,int.dir_banco_proveedor,int.swift_proveedor,
                 int.nombre_despachante,int.mail_despachante,int.telefono_despachante
 	             from compras.datos_oc_internacional as int where int.nro_orden=$nro_orden";
 	     $datos_internacional=sql($query,"<br>Error al traer los datos internacionales de la OC<br>") or fin_pagina();
 	     $internacional=1;

 	     if(!$id_oc_internacional)
 	      $id_oc_internacional=$datos_internacional->fields["id_oc_internacional"];
 	     if(!$id_despachante)
 	      $id_despachante=$datos_internacional->fields["id_despachante"];
 	     if(!$fob)
 	      $fob=$datos_internacional->fields["fob"];
 	     if(!$tipo_flete)
 	      $tipo_flete=$datos_internacional->fields["tipo_flete"];
 	     if(!$monto_flete)
 	      $monto_flete=$datos_internacional->fields["monto_flete"];
 	     if(!$honorarios_gastos)
 	      $honorarios_gastos=$datos_internacional->fields["honorarios_gastos"];
 	     if(!$nombre_proveedor)
 	      $nombre_proveedor=$datos_internacional->fields["nombre_proveedor"];
 	     if(!$direccion_proveedor)
 	      $direccion_proveedor=$datos_internacional->fields["direccion_proveedor"];
 	     if(!$banco_proveedor)
 	      $banco_proveedor=$datos_internacional->fields["banco_proveedor"];
 	     if(!$dir_banco_proveedor)
 	      $dir_banco_proveedor=$datos_internacional->fields["dir_banco_proveedor"];
 	     if(!$swift_proveedor)
 	      $swift_proveedor=$datos_internacional->fields["swift_proveedor"];
 	     if(!$nombre_despachante)
 	      $nombre_despachante=$datos_internacional->fields["nombre_despachante"];
 	     if(!$mail_despachante)
 	      $mail_despachante=$datos_internacional->fields["mail_despachante"];
 	     if(!$telefono_despachante)
 	      $telefono_despachante=$datos_internacional->fields["telefono_despachante"];
 	    }//de if($orden->fields['internacional'])
		elseif(!$internacional)
		 $internacional=0;

        if (!$cliente)
	     $cliente=$orden->fields['cliente'];
	    if (!$select_dest_oc)
	     $select_dest_oc=$orden->fields['id_destino_oc'];
        if (!$entrega)
	     $entrega=$orden->fields['lugar_entrega'];
        if (!$select_pago)
	     $select_pago=$orden->fields['id_plantilla_pagos'];
        if (!$licitacion)
         $licitacion=$orden->fields['id_licitacion'];
        if (!$select_contacto)
	     $select_contacto=($orden->fields['id_contacto'])?$orden->fields['id_contacto']:-2;
        if (!$notas)
	     $notas=$orden->fields['notas'];
        if (!$notas_internas)
	     $notas_internas=$orden->fields['notas_internas'];
        if (!$select_moneda)
         $select_moneda=$orden->fields['id_moneda'];
        if(!$valor_dolar)
         $valor_dolar=$orden->fields['valor_dolar'];
        if(!$flag_stock)
        { $flag_stock=$orden->fields['flag_stock'];
        }
        if(!$presupuesto)
         $presupuesto=$orden->fields['es_presupuesto'];
        if(!$generar_reclamo_parte)
         $generar_reclamo_parte=$orden->fields['reclamo_activado'];
        if(!$proveedor_reclamo)
         $proveedor_reclamo=$orden->fields['reclamo_proveedor'];
        if(!$orden_prod)
        {$orden_prod=$orden->fields['orden_prod'];
        }
        if(!$seguimientos)
         $seguimientos=$orden->fields['id_entrega_estimada'];
        if(!$transporte_agregado)
         $transporte_agregado=$orden->fields['transporte_agregado'];
        //flag de honorarios
        $flag_honorario=$orden->fields["flag_honorario"];
        if(!$caso && $pagina!="asociar")
        {
         $caso=$orden->fields['nrocaso'];
        }
        if ($orden->fields['estado']=='a' || $orden->fields['estado']=='e')
         $can_finish=1;
        $estado=$orden->fields['estado'];
        if(!$fecha_creacion)
        {$fecha_creacion1=split(" ",$orden->fields['fecha']);
         $fecha_creacion=$fecha_creacion1[0];
        }
        elseif($estado=="")
         $fecha_creacion=date("Y-m-d",mktime());
}//de if ($nro_orden && $nro_orden!=-1)
else //la OC es nueva (aun no se guardo en la BD)
{
	$q="select last_value from orden_de_compra_nro_orden_seq";
	$o=sql($q,"<br>Error al tarer el ultimo valor usado como Nro de Orden de Compra<br>") or die($db->ErrorMsg()."<br> $q");
	$nro_orden_n=$o->fields['last_value']+1;

}//del else de if ($nro_orden && $nro_orden!=-1)


//si no es una Orden de Servicio Tecnico, traemos el listado de proveedores o el proveedor cargado, segun el estado
if($modo!="oc_serv_tec")
{
  //esto solo se hace cuando el estado de la orden es mayor o igual
  // que autorizada
  if($estado=="" || $estado=="p" || $estado=="r" || $estado=="u")
  {
   $q="select proveedor.id_proveedor,proveedor.razon_social,proveedor.cuenta_corriente,proveedor.clasificado
       from general.proveedor ";
   if ($select_provee && $select_provee!='t')
  	$q.=" where proveedor.filtro ilike '%".$select_provee."%' ";
   /*SI SE AÑADE EL FILTRO OTRA VEZ DESCOMENTAR
   elseif (!$select_provee)
     	$q.=" where filtro ilike '%c%' ";
    */
    $q.="where proveedor.activo='t' and proveedor.razon_social not ilike 'Stock%'";
    $q.="order by proveedor.razon_social";
   }//de if($estado=="" || $estado=="p" || $estado=="r" || $estado=="u")
   else
    $q="select proveedor.id_proveedor,proveedor.razon_social,proveedor.cuenta_corriente,proveedor.clasificado
    	from general.proveedor
        where proveedor.id_proveedor=$select_proveedor";

   $proveedores=sql($q,"<br>Error al traer los proveedores para la OC<br>") or fin_pagina();

   $q="select contactos.id_contacto,contactos.nombre
   	   from general.contactos
   	   where contactos.id_proveedor=$select_proveedor";
   if ($select_proveedor && $select_proveedor!=-1)
	$contactos=sql($q,"<br>Error al traer los contactos del proveedor elegido<br>") or fin_pagina();
   else
	$select_contacto=-1;
}//de if($modo!="oc_serv_tec")
elseif ($modo=="oc_serv_tec")//Si es una Orden de Servicio Tecnico, traemos el id del stock de Serv Tec
{
    $query="select proveedor.id_proveedor from general.proveedor where proveedor.razon_social='Stock Serv. Tec. Bs. As.'";
    $proveedores=sql($query,"<br>Error al traer<br>") or fin_pagina();

    $select_proveedor=$proveedores->fields["id_proveedor"];
}

//Esto se hace solo para las Ordenes que tienen forma de pago (no es el caso de Ordenes de Servicio Tecnico)
if ($modo!="oc_serv_tec")
{
 $sql="select sum(ordenes_pagos.monto) as montos_pagos
 	   from compras.pago_orden join compras.ordenes_pagos using (id_pago)
 	   where pago_orden.nro_orden=$nro_orden";
 $resultado=sql($sql,"<br>Error al traer montos_pagos de la OC<br>") or fin_pagina();

 $montos_pagos=$resultado->fields["montos_pagos"];
 if ($montos_pagos=="")
  $montos_pagos=0;
}
/*************************************************************************************************************
 FIN DE SECCION: RECOPILACION DE DATOS DE LA OC Y SETEO DE VARIABLES PARA MOSTRAR LOS DATOS DE LA MISMA
**************************************************************************************************************/

/*************************************************************************************************************
 SECCION: SETEO DE PERMISOS PARA LOS BOTONES Y DEMAS FUNCIONALIDADES DE LA PAGINA
**************************************************************************************************************/
//Si la OC esta en estado es enviada, y se ha recibido al menos un
//producto, deshabilitamos el boton de rechazar, y el de anular
if($estado=="e")
{$query="select sum(recibido_entregado.cantidad) as suma
		 from compras.fila join compras.recibido_entregado using(id_fila) where fila.nro_orden=$nro_orden";
 $items_recibidos=sql($query) or fin_pagina();

 if($items_recibidos->fields['suma']!="" && $items_recibidos->fields['suma']>0)
  $disabled_recib="disabled";
 else
  $disabled_recib="";
}//de if($estado=="e")

//dependiendo del estado de la OC, damos los permisos a los botones
switch ($estado)
{

 //SI EL ESTADO NO EXISTE => TIENE PERMISO
 case 't'://terminada
            $permisos_b['pagar']="";
 case 'e'://enviada
		 	$permisos_b['terminar']=" ";
		 	$permisos_b['eliminar']=" disabled ";
		 	$permisos_b['agregar']=" disabled ";
		 	$permisos_b['guardar']=" disabled ";
		 	$permisos_b['anular']=" ";
		 	$permisos_b['por_autorizar']=" disabled ";
		 	$permisos_b['autorizar']="disabled";
		break;
 case 'a'://autorizada
		 	$permisos_b['terminar']=" disabled ";
		 	$permisos_b['eliminar']=" disabled ";
		 	$permisos_b['agregar']=" disabled ";
		 	$permisos_b['guardar']=" disabled ";
		 	$permisos_b['anular']=" ";
		 	$permisos_b['por_autorizar']=" disabled ";
		 	$permisos_b['pagar']=" disabled";
			$permisos_b['autorizar']="disabled";

		break;
 case 'n'://anulada
			$permisos_b['eliminar']=" disabled ";
			$permisos_b['agregar']=" disabled ";
			$permisos_b['guardar']=" disabled ";
			$permisos_b['anular']=" disabled ";
			$permisos_b['por_autorizar']=" disabled ";
			$permisos_b['terminar']=" disabled ";
			$permisos_b['pagar']=" disabled ";
			$permisos_b['rechazar']=" disabled ";
			$permisos_b['autorizar']="autorizar";
			$anulada="disabled";
	 	break;
 case 'u'://por autorizar
		 	$permisos_b['eliminar']=" ";
		 	$permisos_b['agregar']=" ";
		 	$permisos_b['guardar']=" ";
		 	$permisos_b['anular']="  ";
		 	$permisos_b['por_autorizar']=" disabled ";
		 	$permisos_b['terminar']=" disabled ";
		 	$permisos_b['pagar']=" disabled ";
	 	break;
 case 'r'://rechazada
 case 'p'://pendiente
		 	$permisos_b['eliminar']=" ";
		 	$permisos_b['agregar']=" ";
		 	$permisos_b['guardar']=" ";
		 	$permisos_b['anular']=" ";
		 	$permisos_b['por_autorizar']=" ";
		 	$permisos_b['terminar']=" disabled ";
		 	$permisos_b['pagar']=" disabled ";
	 	break;
 case 'd'://pagada parcial
             $permisos_b['autorizar']="disabled ";
             $permisos_b['eliminar']="disabled ";
             $permisos_b['agregar']="disabled ";
             $permisos_b['guardar']="disabled ";
             $permisos_b['anular']=" disabled ";
             $permisos_b['por_autorizar']=" disabled ";
             $permisos_b['terminar']="";
             $permisos_b['pagar']=" ";
			 $permisos_b['rechazar']=" disabled ";
             $permisos_b['autorizar']="disabled";
        break;
 case 'g'://paga totalmente
             $permisos_b['autorizar']="";
             $permisos_b['eliminar']="disabled ";
             $permisos_b['agregar']="disabled ";
             $permisos_b['guardar']="disabled ";
             $permisos_b['anular']=" disabled ";
             $permisos_b['por_autorizar']=" disabled ";
             $permisos_b['terminar']="";
             $permisos_b['pagar']="";
			 $permisos_b['rechazar']=" disabled ";
             $permisos_b['autorizar']="disabled";
        break;
 default://nueva orden
            $permisos_b['eliminar']=" ";
             $permisos_b['agregar']=" ";
             $permisos_b['guardar']=" ";
             $permisos_b['anular']=" disabled ";
             $permisos_b['por_autorizar']=" disabled ";
             $permisos_b['terminar']=" disabled ";
             $permisos_b['pagar']=" disabled ";
        break;
}//de switch ($estado)

//se controla si los usuarios tienen permiso para editar o crear nueva orden de compra
if(!permisos_check("inicio","permiso_editar_crear_ordenc"))
{ $permiso="disabled";
  $permiso_read="readonly";
  $permisos_b['guardar']="disabled";
  $permisos_b['autorizar']="disabled";
  $permisos_b['eliminar']="disabled ";
  $permisos_b['agregar']="disabled ";
  $permisos_b['anular']=" disabled ";
  $permisos_b['por_autorizar']=" disabled ";
  $permisos_b['pagar']="disabled";
  $permisos_b['rechazar']=" disabled ";
  $permisos_b['autorizar']="disabled";
  $permiso_habilitar="disabled";
  $permiso_forma_pago="disabled";
}//de if(!permisos_check("inicio","permiso_editar_crear_ordenc"))

/*************************************************************************************************************
 FIN DE SECCION: SETEO DE PERMISOS PARA LOS BOTONES Y DEMAS FUNCIONALIDADES DE LA PAGINA
**************************************************************************************************************/

/*************************************************************************************************************
 SECCION: DATOS DE LA OC Y MAS
**************************************************************************************************************/

echo $html_header;
?>
	<style type="text/css">
	<!--
	.alerta {
		font-family: "Courier New", Courier, mono;
		color: 'red';
		text-decoration: blink;
		font-size: medium;
	}
	a {
		cursor: hand;
	}
	-->
	</style>

<script language="JavaScript" src="funciones.js"></script>
<script language="JavaScript" src="funciones_de_ord_compra.js"></script>
<?
/***********************************************
 Generacion de variables JavaScript para POSAD
************************************************/
if($internacional)
{
//traemos los datos de la tabla POSAD
$query="select posad.id_posad,posad.descripcion,posad.codigo_ncm,posad.derechos,posad.estadistica,posad.iva_ganancias
        from compras.posad where posad.estado_posad=1 order by posad.descripcion";
$posad_js=sql($query,"<br>Error al traer los datos de posad<br>") or fin_pagina();
?>
<script>
var cantidad_posad=0;
<?
//generamos las variables (arreglos) JavaScript con los datos de cada POSAD, necesarios para realizar los calculos de los montos
//de los datos propios de una OC internacional
$codigo_select_posad="";
while (!$posad_js->EOF)
{
 $id_posad_js=$posad_js->fields["id_posad"];

 //sacamos los enters de la descripcion, y eliminamos las comillas dobles o simples
 $desc_posad_js=$posad_js->fields["descripcion"];
 $desc_posad_js=ereg_replace("\r\n","<br>",$desc_posad_js);
 $desc_posad_js=ereg_replace("\n","<br>",$desc_posad_js);
 $desc_posad_js=ereg_replace("\""," ",$desc_posad_js);
 $desc_posad_js=ereg_replace("'"," ",$desc_posad_js);

 ?>
 cantidad_posad++;
 var posad_<?=$id_posad_js?>=new Array();
 posad_<?=$id_posad_js?>["descripcion"]=  "<?if($desc_posad_js!="")echo $desc_posad_js;else echo "null"?>";
 posad_<?=$id_posad_js?>["codigo_ncm"]=   <?if($posad_js->fields["codigo_ncm"]!="")echo $posad_js->fields["codigo_ncm"];else echo "null"?>;
 posad_<?=$id_posad_js?>["derechos"]=     <?if($posad_js->fields["derechos"]!="")echo $posad_js->fields["derechos"];else echo "null"?>;
 posad_<?=$id_posad_js?>["estadistica"]=  <?if($posad_js->fields["estadistica"]!="")echo $posad_js->fields["estadistica"];else echo "null"?>;
 posad_<?=$id_posad_js?>["iva_ganancias"]=<?if($posad_js->fields["iva_ganancias"]!="")echo $posad_js->fields["iva_ganancias"];else echo "null"?>;

 <?
 $posad_js->MoveNext();
}//de while(!$posad_js->EOF)
?>
</script>
<script language="JavaScript" src="funciones_oc_internacional.js"></script>
<?
/***********************************************
 Fin de Generacion de variables JavaScript
 para POSAD
************************************************/
}//de if($internacional)
?>

<script>
var titulo_pagina;
var links_stock=new Array();
links_stock["san luis"]="<?=encode_link($html_root.'/modulos/stock/stock_san_luis.php',array('onclick_cargar'=>"window.opener.cargar_stock()",'cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["buenos aires"]="<?=encode_link($html_root.'/modulos/stock/stock_buenos_aires.php',array('onclick_cargar'=>"window.opener.cargar_stock()",'cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["new tree"]="<?=encode_link($html_root.'/modulos/stock/stock_new_tree.php',array('onclick_cargar'=>"window.opener.cargar_stock()",'cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["anectis"]="<?=encode_link($html_root.'/modulos/stock/stock_anectis.php',array('onclick_cargar'=>"window.opener.cargar_stock()",'cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["sicsa"]="<?=encode_link($html_root.'/modulos/stock/stock_sicsa.php',array('onclick_cargar'=>"window.opener.cargar_stock()",'cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["st_ba"]="<?=encode_link($html_root.'/modulos/stock/stock_st_ba.php',array('onclick_cargar'=>"window.opener.cargar_stock()",'cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["no_stock"]="<?=encode_link('../productos/listado_productos.php',array('pagina_viene'=>'ord_compra.php','onclick_cargar'=>"window.opener.cargar()",'cambiar'=>0)) ?>";
</script>
<script src="../../lib/popcalendar.js"></script>
<script src="../../lib/checkform.js"></script>
<script src="../../lib/NumberFormat150.js"></script>

<script LANGUAGE=VBScript TYPE="text/vbscript">
Function makeMsgBox(title,mess,icon,buts,defbut,mods)
   butVal = buts + (icon*16) + (defbut*256) + (mods*4096)
   makeMsgBox = MsgBox(mess,butVal,title)
End Function

Function makeInputBox(title,pr,def)
   makeInputBox = InputBox(pr,title,def)
End Function
</script>

<?
$link=encode_link("../licitaciones/licitaciones_view.php",array("backto"=>$_SERVER['SCRIPT_NAME'],"_ses_global_extra"=>array()));
?>

<div style="overflow:auto;width:100%;position:relative" id="div_formulario">

<form name="form1" method="post" action="proc_compra.php" >
<!--Hiddens del modo de la Orden -->
<input type="hidden" name="modo" value="<?=$modo?>">

<input type="hidden" name="codigo_select_posad" value="">
<input type="hidden" name="destino_para_autorizar" value="ord_compra_listar.php">
<input type="hidden" name="montos_default" value="si">
<input type="hidden" name="estado" value="<?=$estado?>">
<input type="hidden" name="presupuesto" value="<?=$presupuesto?>">
<input type="hidden" name="caso" value="<?=$caso?>">
<input type="hidden" name="pagina_asoc" value="<?=$pagina?>">
<input type="hidden" name="pago_especial" value="<?=$orden->fields['habilitar_pago_especial']?>">
<input type="hidden" name="montos_pagos" value="<?=$montos_pagos?>">
<input type="hidden" name="flag_stock" value=<?if($flag_stock)echo $flag_stock;else echo 0;?>>
<input type="hidden" name="accion" value="nada">
<input type="hidden" name="fecha_creacion" value="<?=$fecha_creacion?>">
<!--estos hidden son para guardar los valores que vienen por parametro para saber
a que esta asociada una orden de compra -->
<input type="hidden" name="es_lic" value="<?if ($licitacion) echo $licitacion; else echo 0;?>">
<input type="hidden" name="es_pres" value="<?if ($presupuesto) echo $presupuesto; else echo 0;?>">
<input type="hidden" name="es_st" value="<?if($caso) echo $caso; else echo 0;?>">
<input type="hidden" name="es_cas" value="<?if ($flag_honorario) echo $flag_honorario; else echo 0;?>">
<input type="hidden" name="es_rma" value="<?if ($orden_prod) echo $orden_prod; else echo 0;?>">
<!--////////////////////BROGGI - esto es para cuando elige un cliente ////////////////-->
<!--////////////////////y guarda la orden es para ver cual es la entidad mas usada por cada usario ////////////////-->
<input name="cambio_entidad" type="hidden" value="no_cambio">
<!--///////////////////////////////////-->

<!--Hidden que se usa para decirle a la funcion javascript cargar()
    y la funcion cargar_stock(), si debe controlar la duplicacion
    de productos o no
-->
<?
if($licitacion || $nrocaso || $flag_stock==1 ||$orden_prod)
 $controlar_siempre="si";
else
 $controlar_siempre="no";
?>
<input type="hidden" name="controlar_siempre" value="<?=$controlar_siempre?>">

<input type="hidden" name="gastos_servicio_tecnico" value="<?=$gastos_servicio_tecnico?>">

<?/*<input type="hidden" name="es_stock" value="<?if ($disabled_por_stock=="disabled") echo 1; else echo 0;?>">*/?>
<input type="hidden" name="era_stock" value="<?if ($era_stock!="") echo $era_stock; else echo $es_stock;?>">
<!-- hidden orden_prod para mantener el numero de la orden de produccion cuando la orden es asociada a RMA DE PRODUCCION-->
<input type="hidden" name="orden_prod" value="<?if ($orden_prod) echo $orden_prod; else echo 0;?>">

<?
/***********************************************
 Traemos y mostramos el Log de la OC
************************************************/
//left join por si alguna vez se elimina el usuario
$q="select log_ordenes.id_log,log_ordenes.fecha,log_ordenes.tipo_log,log_ordenes.otros,
		   usuarios.nombre ||' '||usuarios.apellido as usuario
	from compras.log_ordenes LEFT join sistema.usuarios on log_ordenes.user_login=usuarios.login
	where log_ordenes.nro_orden=";
$q.=($nro_orden)?$nro_orden:-1;
$q.=" order by log_ordenes.fecha desc";
$log=sql($q,"<br>Error al consultar el log de la OC<br>") or fin_pagina();
?>
<div align="right">
	<input name="mostrar_ocultar_log" type="checkbox" value="1" onclick="if(!this.checked)
																	  document.all.tabla_logs.style.display='none'
																	 else
																	  document.all.tabla_logs.style.display='block'
																	  "> Mostrar Logs
</div>
<!-- tabla de Log de la OC -->
<div style="display:'none';width:98%;overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;'?> " id="tabla_logs" >
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?
$anulada_mostrar=0;
$rechazo_mostrar=0;
do
{

 if ($log->fields['tipo_log']=="de rechazo")
  $coment_rechazo=$log->fields['otros'];

 if($log->fields['tipo_log']=="de creacion")
 {
 	$usuario_creacion=$log->fields['usuario'];
 }
//$log->fields['fecha']?strtotime($log->fields['fecha']):''
 ?>
<tr title="<?=$coment_rechazo?>">
      <td height="20" nowrap>Fecha <?=(($log->fields['tipo_log'])?$log->fields['tipo_log'].": ":"creacion: ").($log->fields['fecha']?date("j/m/Y H:i:s",strtotime($log->fields['fecha'])):date("j/m/Y H:i:s"))?> </td>
      <td nowrap > Usuario : <?=(($log->fields['usuario'])?$log->fields['usuario']:$_ses_user['name']);?> </td>
</tr>
<?
 //controlo si la orden ha sido anulada
 if ($log->fields['tipo_log']=="de anulacion")
  {$anulada_mostrar=1;
   $nombre_an=$log->fields['usuario'];
   $justificacion=$log->fields['otros'];
  }
  //controlo si la orden ha sido rechazada
 if ($rechazo_mostrar==0 && $log->fields['tipo_log']=="de rechazo")
  {$rechazo_mostrar=1;
   $nombre_rec=$log->fields['usuario'];
   $justificacion_rec=$log->fields['otros'];
  }
 $log->MoveNext();
}
while (!$log->EOF);
?>
</table>
</div>
<?
/***********************************************
 Fin de muestra del LOG de la OC
************************************************/
?>
<hr>
<?
/***********************************************
 Decisión de a qué esta asociada la OC
************************************************/
$desde_presupuesto=0;

if ($licitacion && !$presupuesto)
{
  $link = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$licitacion));

  //revisamos si la OC asociada a licitacion provino desde un
  //presupuesto de seguimiento de produccion (que no tiene nada
  //que ver con la variable $presupuesto, que refiere a presupuestos
  //del tipo de licitaciones), o si la OC provino de asociar
  //con licitacion cuando se hace nueva OC.
  $query="select distinct(oc_pp.nro_orden) from compras.oc_pp where oc_pp.nro_orden=$nro_orden";
  $desde_pres=sql($query) or fin_pagina();
  if($id_renglon_prop || $desde_pres->fields["nro_orden"]!="")
   $desde_presupuesto=1;
?>
  <table align='center'>
   <tr>
    <td width="60%" align="right">
     &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' ><b>Orden asociada a la Licitación</font>
    </td>
    <td bgcolor="<?=$estado_lic_color?>" title="Estado de la Licitación: <?=$estado_lic_nombre?>" width="1%">
    <?
     $frente="#000000";
     $reemplazo="#ffffff";
     $color_link=contraste($estado_lic_color, $frente, $reemplazo);?>
     <font size=3 color='red' >
      <b><a href="<?=$link?>" style="font-size='16'; color='<?=$color_link?>';" target="_blank"><U><?=$licitacion?></U></A>
     </font>
    </td>
    <td>
     &nbsp;
    </td>
    <?
    if ($licitacion && !$id_renglon_prop)
    {
     // traer los seguimientos de produccion de la licitacion con la q asocie la orden d compra
     $q_seg="select entrega_estimada.id_entrega_estimada, entrega_estimada.nro,entrega_estimada.finalizada,
         subido_lic_oc.nro_orden, subido_lic_oc.vence_oc
         from licitaciones.entrega_estimada
         left join licitaciones.subido_lic_oc using (id_entrega_estimada)
         where entrega_estimada.id_licitacion=$licitacion
         order by entrega_estimada.nro";
    $res_q_seg=sql($q_seg, "Error al traer los seguimientos de produccion para la licitacion $licitacion") or fin_pagina();
    $cant_seg=$res_q_seg->RecordCount();
    if($cant_seg>0)
    {
   	?>
      <td id=tabla_seguimiento width="30%">
         <b>Nº de Seguimiento </b>
         <select name="seguimientos" <?=$permiso?>>
         <?
          $contador_finalizadas=0;
          while (!$res_q_seg->EOF)
          {
            if($res_q_seg->fields['finalizada']==1)
             $contador_finalizadas++;
            $res_q_seg->MoveNext();
          }//de while(!$res_q_seg->EOF)
          if($contador_finalizadas==$cant_seg)
           $todas_finalizadas=1;
          else
           $todas_finalizadas=0;
          $res_q_seg->Move(0);
          for ($i=0;$i<$cant_seg;$i++)
          {
           $nro_seg=$res_q_seg->fields['nro']."/".$res_q_seg->fields['nro_orden'];
           if($todas_finalizadas||$res_q_seg->fields['finalizada']==0 || $res_q_seg->fields['id_entrega_estimada']==$seguimientos)
           {
           ?>
            <option value="<?=$res_q_seg->fields['id_entrega_estimada']?>"
              <?if ($res_q_seg->fields['id_entrega_estimada']==$seguimientos) echo "selected"?>
            >
             <?=$nro_seg?>
            </option>
           <?
           }//de if($res_q_seg->fields['finalizada']==0 || $res_q_seg->fields['id_entrega_estimada']==$seguimientos)
          $res_q_seg->MoveNext();
         } // del for ($i=0;$i<$cant_seg;$i++)
         if($todas_finalizadas)
         {
         ?>
           <option value=-1
            <?if (-1==$seguimientos || ($seguimientos==""&&$estado!="")) echo "selected"?>
           >
            No asociada
           </option>
         <?
         }
         ?>
        </select>
        <script>
         if(typeof(document.all.seguimientos)!="undefined")
         {
          if(document.all.seguimientos.options.length==1)
           document.all.tabla_seguimiento.style.visibility='hidden';
         }
        </script>

      </td>
    <?
    }//de if($cant_seg>0)
  } // del if ($licitacion)
 ?>
   </tr>
  </table>
  <?
  $tipo_oc="Licitación";
  $tipo_asociacion_oc="Asociada a Licitación
       <a href=\"$link\" style=\"color='$color_link';\" target='_blank'>
       <U><font style=\"background-color:'$estado_lic_color'\" title='Estado de la Licitación: $estado_lic_nombre'>$licitacion</font></U></a>";
}//de if ($licitacion && !$presupuesto)
elseif ($presupuesto)
{
  $link = encode_link("../presupuestos/presupuestos_view.php",array("cmd1"=>"detalle","ID"=>$licitacion));
?>
  <table align='center'>
   <tr>
    <td>
     &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' ><b>Orden asociada al Presupuesto</font>
    </td>
    <td bgcolor="<?=$estado_lic_color?>" title="Estado de la Licitación: <?=$estado_lic_nombre?>">
    <?
     $frente="#000000";
     $reemplazo="#ffffff";
     $color_link=contraste($estado_lic_color, $frente, $reemplazo);?>
     <font size=3 color='red' >
      <b><a href="<?=$link?>" style="font-size='16'; color='<?=$color_link?>';" target="_blank"><U><?=$licitacion?></U></A>
     </font>
    </td>
   </tr>
  </table>
  <?
  $tipo_oc="Presupuesto";
 $tipo_asociacion_oc="Asociada a Presupuesto
    <a href=\"$link\" style=\"color='$color_link';\" target='_blank'>
    <U><font style=\"background-color:'$estado_lic_color'\" title='Estado de la Licitación: $estado_lic_nombre'>$licitacion</U></a>";
}//de elseif ($presupuesto)
elseif ($caso)
{
	if (!$idcaso)
	{
	  $sql="select casos_cdr.idcaso,entidad.id_entidad
      from casos.casos_cdr
      join casos.dependencias using (id_dependencia)
      join licitaciones.entidad using (id_entidad)
      where casos_cdr.nrocaso=$caso";
	  $resultado_caso=sql($sql,"<br>Error al traer los datos del caso asociado a la OC<br>") or fin_pagina();
	  $idcaso=$resultado_caso->fields["idcaso"];

	  $id_entidad=$resultado_caso->fields["id_entidad"];
	}//de if (!$idcaso)
	$link = encode_link("../casos/caso_estados.php",array("id"=>$idcaso,"id_entidad"=>$id_entidad));
?>
  <b><div align='center'>
  &nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >Orden Asociada al Número de Caso <a href="<?=$link?>" style="font-size='16'; color='red';" target="_blank" title="Ver el caso de Servicio Técnico."><U><?=$caso?></U></A> de Servicio Técnico</font></div>
  </b>
<?
  $tipo_oc="Servicio Técnico";
  $tipo_asociacion_oc="Asociada a Caso <a href=\"$link\" target='_blank' title='Ver el caso de Servicio Técnico.'><U>$caso</U></A>";
}//de elseif ($caso)
elseif ($flag_stock)
{
?>
  <div align='center'> <b>&nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >Orden de Compra Asociada al Stock de Coradir</font></b></div>
<?
  $tipo_oc="Stock";
  $tipo_asociacion_oc="Asociada a Stock";
}//de elseif ($flag_stock)
elseif ($orden_prod)
{
	$link = encode_link("../ordprod/ordenes_nueva.php",array("nro_orden"=>$orden_prod,"modo"=>"modificar"));

	//traemos el ID de licitacion asociada a la orden de produccion
	$query="select orden_de_produccion.id_licitacion from ordenes.orden_de_produccion
		    where orden_de_produccion.nro_orden=$orden_prod";
	$lic_ord_prod=sql($query,"<br>Error al traer Lic de ORD PROD") or fin_pagina();

	$lic_op=$lic_ord_prod->fields["id_licitacion"];
	$link_lic = encode_link("../licitaciones/licitaciones_view.php",array("cmd1"=>"detalle","ID"=>$lic_op));
?>
   <div align='center'>
     <b>&nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >Orden de Compra Asociada a RMA de OP Nº <a href="<?=$link?>" style="font-size='16'; color='red';" target="_blank" title="Ver orden de Producción."><U><?=$orden_prod?></U></A> <?if($lic_op){?>, con Licitación Nº <a href="<?=$link_lic?>" style="font-size='16'; color='red';" target="_blank" title="Ver Licitación Asociada a la Orden de Producción."><U><?=$lic_op?></U></A><?}?>
      </font>
     </b>
   </div>
<?
   $tipo_oc="RMA de Producción";
   $tipo_asociacion_oc="Asociada a RMA de Producción <a href=\"$link\" target='_blank' title='Ver orden de Producción.'><U>$orden_prod</U></A>";
}//de elseif ($orden_prod)
elseif ($flag_honorario || $gastos_servicio_tecnico)
{
?>
  <div align='center'>
   <b>&nbsp;&nbsp;&nbsp;&nbsp;<font size=3 color='red' >Orden de Compra Asociada a los Honorarios de los C.A.S. </font></b>
  </div>
  <?
  $tipo_oc="Honorarios de Servicios Técnicos";
  $tipo_asociacion_oc="Asociada a Honorarios de Servicios Técnicos";
}//de elseif ($flag_honorario || $gastos_servicio_tecnico)
elseif(!$internacional)
{
?>
  <div align='center'> <b>&nbsp;&nbsp;&nbsp;&nbsp;<b><font size=3 color='red' >Esta Orden no está asociada a <u>nada</u><br>(Verifique que la Orden de Compra esté <u>correctamente realizada</u>)</font></b></div>
  <?
  $tipo_oc="Otro";
  $tipo_asociacion_oc="No Asociada";
}//del else de las asociaciones

if ($internacional)//si la OC es una OC Internacional, mostramos el cartel correspondiente
{?>
 <div align='center'> <b>&nbsp;&nbsp;&nbsp;&nbsp;<b><font size=3 color='#00C021' >Orden de Compra Internacional</font></b></div>
  <?
  if($tipo_oc!="")
  {
   $tipo_oc.=" - ";
   $tipo_asociacion_oc.=" - ";
  }
  $tipo_oc.="OC Internacional";
  $tipo_asociacion_oc.="<font color='#00C021'>OC Internacional</font>";
}//de elseif ($internacional)


/***********************************************
 Fin de Decisión de a qué esta asociada la OC
************************************************/


/***********************************************
 Mostramos los datos propios de la OC
************************************************/
if ($nro_orden_n)
  $estado_nombre="Nueva Orden";
elseif ($nro_orden!=-1)
{
 switch ($orden->fields['estado'])
 {
  case 'P':
  case 'p': $estado_nombre="PENDIENTE"; break;
  case 'A':
  case 'a': $estado_nombre="AUTORIZADA";
            $permiso="disabled";
            $permiso_read="readonly";
	      	break;
  case 't':
  case 'T': $estado_nombre="TERMINADA";
	        $permiso="disabled";
	        $permiso_read="readonly";
	      	$can_finish=true;
	      	break;
  case 'r': $estado_nombre="RECHAZADA";
	        break;
  case 'n': $estado_nombre="ANULADA";
	        $permiso="disabled";
	        $permiso_read="readonly";
	      	$can_finish=false;
			break;
  case 'u': $estado_nombre="POR AUTORIZAR";
            break;
  case 'd': $estado_nombre="PARCIALMENTE PAGADA";
	        $permiso="disabled";
	        $permiso_read="readonly";
	      	$can_finish=true;
			break;
  case 'e':
  case 'E': $estado_nombre="ENVIADA";
	        $permiso="disabled";
	        $permiso_read="readonly";
	      	break;
  case 'g':
  case 'G': $estado_nombre="TOTALMENTE PAGADA";
            if($modo=="oc_serv_tec")//Si es una Orden de Servicio Tecnico, el estado g se llama "Finalizada"
             $estado_nombre="FINALIZADA";
	        $permiso="disabled";
	        $permiso_read="readonly";
	      	break;
  default: $estado_nombre="DESCONOCIDO";
 }//de switch ($orden->fields['estado'])
}//de elseif ($nro_orden!=-1)
else
 $estado_nombre="NoEnTrO";
?>
<input type="hidden" name="orden_compra_id" value="<?=$nro_orden?>">
<input type="hidden" name="internacional" value="<?=$internacional?>">
<input type="hidden" name="id_oc_internacional" value="<?=$id_oc_internacional?>">
  <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' id="tabla_info" class="bordes">
    <tr id="mo">
     <td colspan="3">
      <?
      //de acuerdo al modo de la OC, mostramos el cartel correspondiente
      switch ($modo) {
      	case "oc_serv_tec": $titulo_pagina="Orden de Servicio Técnico";
      						break;
      	default:			$titulo_pagina="Orden de Compra";
      	             		break;
      }
      ?>
      <font size="3"><b><?=$titulo_pagina?> Nº </b>
      <?=($nro_orden && $nro_orden!=-1)?$nro_orden:$nro_orden_n?></font>
     </td>
    </tr>
    <tr>
      <td>
       <table width="95%" align="center">
        <tr>
         <td width="15%" id="td_fecha_entrega">
          <b>Fecha entrega </b>
         </td>
         <td width="15%">
          <input name="fecha_entrega" type="text" id="fecha_entrega" size="10"   <?=$permiso?> value="<?=$fecha_entrega?>" readonly> <?if($estado!='a'&&$estado!='e'&&$estado!='d'&&$estado!='g')echo link_calendario("fecha_entrega")?>
         </td>
         <td width="40%" align="center">
          <font size="2" color="Blue"><b>Tipo: <?=$tipo_oc?></b></font>
         </td>
         <td  colspan="2" align="right">
          <b>Estado: <?=$estado_nombre?></b>
         </td>
        </tr>
       </table>
      </td>
     </tr>

<? if($anulada_mostrar==1)
{
?>
     <tr>
      <td align="center">
       <table align="center">
        <td width="40%">
         <b>Anulada por</b>&nbsp;&nbsp;<?=$nombre_an?>
        </td>
        <td align="right">
         <table>
          <tr>
           <td width="10%">
            <b>Comentario
           </td>
           <td>
            <textarea readonly style="width:90%" rows="2"><?=$justificacion;?></textarea>
           </td>
          </tr>
         </table>
        </td>
       </tr>
      </table>
     </td>
    </tr>
<?
}
if($rechazo_mostrar==1 && ($estado=="r" || $estado=='u'))
{
?>
     <tr>
      <td align="center">
       <table align="center">
        <td width="40%">
         <b>Último Rechazo por</b>&nbsp;&nbsp;<?=$nombre_rec?>
        </td>
        <td align="left">
         <table>
          <tr>
           <td width="20%">
            <b>Comentario<br>último rechazo
           </td>
           <td>
            <textarea readonly cols="65" rows="2"><?=$justificacion_rec;?></textarea>
           </td>
          </tr>
         </table>
        </td>
       </tr>
      </table>
     </td>
    </tr>
<?
}//de if($rechazo_mostrar==1 && ($estado=="r" || $estado=='u'))

//Si la OC no es modo Servicio Tecnico, se muestra la tabla de proveedores
if($modo!="oc_serv_tec")
{
?>
    <tr>
      <td align="center">

      <?$es_stock=0;
        $cuenta_corriente_habilitada=0;
      ?>
      <input type="hidden" name="borrar_filas_stock" value="0">
      <table width="98%" class="bordes"><!--Tabla del proveedor-->
       <tr align="center" id="sub_tabla">
         <td colspan="4">Proveedor</td>
       </tr>
       <tr>
        <td width="15%"><STRONG>Nombre</STRONG> </td>
        <td width="55%">
          <!--Hidden para saber el id del proveedor aunque este deshabilitado
           el select_proveedor-->
          <input type="hidden" name="id_proveedor_a" value="<?=$select_proveedor?>">
          <?if($select_proveedor)
          {
          ?>
            <input type="text" name="clasificacion_proveedor" size="1" readonly value="" style="text-align:center;font-weight:bold;" title="Calificación actual del Proveedor">
          <?
          }
          ?>
          <select name="select_proveedor" style="width:300px"
           onKeypress="buscar_op_submit(this);"
           onblur="borrar_buffer();"
           onclick="if(puntero>0)onchange_proveedor(this);else {borrar_buffer();}"
           onchange="onchange_proveedor(this);" <?=$permiso?>
          >
           <option value="-1">Seleccione un proveedor</option>
           <?
           $disabled_por_stock="";
           if($_POST["select_proveedor"])
            $cuenta_corriente="";
           while (!$proveedores->EOF)
           {//si la OC esta asociada a Stock, filtramos los proveedores, para no mostrar
            //aquellos que sean proveedores de tipo Stock. Sino, mostramos todos los proveedores.
            if(!(($flag_stock || $internacional) && substr_count($proveedores->fields['razon_social'],"Stock")>0) || ($estado!="" && $estado!="p" && $estado!="r" && $estado!="u"))
            {
           ?>
             <option value="<?=$proveedores->fields['id_proveedor']?>"
             <?if ($proveedores->fields['id_proveedor']==$select_proveedor)
               {echo " selected";
                $clasif=($proveedores->fields['clasificado'])?$proveedores->fields['clasificado']:"-";
                switch ($clasif)
                {
                	case "A":$color_clasif="#00CC00";break;
                	case "B":$color_clasif="#99FF00";break;
                	case "C":$color_clasif="#FFFF66";break;
                	case "E":$color_clasif="#ff0000";break;
                	case "D":$color_clasif="#FF9900";break;
                	default:$color_clasif="#FFFFFF";break;
                }//de switch ($clasif)

                if(substr_count($proveedores->fields['razon_social'],"Stock")>0)
                {  $disabled_por_stock="disabled";
                   $es_stock=1;
                }

                if($proveedores->fields['cuenta_corriente']==1)
                  $cuenta_corriente_habilitada=1;
               }//de if ($proveedores->fields['id_proveedor']==$select_proveedor)
               ?>
             >
              <?=$proveedores->fields['razon_social']?>
             </option>
             <?
            }//de if(!($flag_stock && substr_count($proveedores->fields['razon_social'],"Stock")....
	        $proveedores->MoveNext();
           }//de while (!$proveedores->EOF)
           ?>
          </select>
          <!--setea el color de clasificacion del proveedor-->
          <script>
           if(typeof(document.all.clasificacion_proveedor)!="undefined")
           {document.all.clasificacion_proveedor.style.backgroundColor='<?=$color_clasif?>';
            document.all.clasificacion_proveedor.value='<?=$clasif?>';
           }
          </script>
          <?
          if ($estado!="")
          {?>
           &nbsp;&nbsp;<input type='button' name='historial' value='H' onclick="window.open('<?=encode_link("../productos/clasif_prove.php",array())?>&proveedor='+document.all.select_proveedor.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=50,top=30,width=700,height=400')">
        <?}

          //si el proveedor se eligio de nuevo y tiene la cuenta corriente habilitada ,
          //o no se eligio el proveedor sino que ya estaba elegido de antes, y se usa la OC (sacado de la tabla orden_de_compra)
          //mostramos el check checkeado
          if($cuenta_corriente || (($_POST["select_proveedor"]||$id_renglon_prop) && $cuenta_corriente_habilitada))
          {
            $cuenta_corriente_checked="checked";
          }

          if($cuenta_corriente_habilitada)
          {
          ?>
           <input type="checkbox" name="cuenta_corriente" value="1" <?=$cuenta_corriente_checked?> onclick="mostrar_fecha_facturacion(this)" <?=$permiso?>> Cuenta Corriente
          <?
          }
          ?>
          <input type="hidden" name="es_stock" value="<?=$es_stock?>">
		</td>
		<td width="10%" align="right"><strong>Contacto</strong></td>
        <td width="20%">
         <select name="select_contacto" <?=$permiso?> >
          <option value="-1">Seleccione el contacto</option>
          <?
			//si esta definido el objeto contactos
			if ($contactos)
			{
				while (!$contactos->EOF)
				{
                 ?>
	             <option value="<?=$contactos->fields['id_contacto']?>" <? if ($contactos->fields['id_contacto']==$select_contacto) echo " selected "; ?>><?=$contactos->fields['nombre']?></option>
                 <?
				 $contactos->MoveNext();
				}//de while (!$contactos->EOF)
			}//de if ($contactos)
?>
           <option value="-2" <? if ($select_contacto==-2) echo " selected ";?>>Ninguno</option>
		 </select>
       </td>
      </tr>
      <tr id="tr_fecha_facturacion" style="display:<?if($cuenta_corriente_checked)echo "block"; else echo "none"?>">
       <td width="15%" id="td_fecha_facturacion">
        <b>Fecha facturación </b>
       </td>
       <td width="45%">
        <input name="fecha_facturacion" type="text" size="10"  <?=$permiso?> value="<?=$fecha_facturacion?>" readonly> <?if($estado!='a'&&$estado!='e'&&$estado!='d'&&$estado!='g')echo link_calendario("fecha_facturacion")?>
       </td>
      </tr>
      <?
      //Si la OC es internacional, mostramos los datos restantes del proveedor, que interesan para este tipo de OC
      if($internacional)
      {
       if($_POST["select_proveedor"])
       {
       	$direccion_proveedor=$banco_proveedor=$dir_banco_proveedor=$swift_proveedor="";
       }
       ?>
       <tr>
        <td colspan="4">
         <table width="100%">
          <tr>
           <td width="19%">
            <b>Dirección</b>
           </td>
           <td width="41%" colspan="5">
            <input type="text" name="direccion_proveedor" value="<?=$direccion_proveedor?>" size="80" <?=$permiso?>>
           </td>
          </tr>
          <tr>
           <td>
            <b>Banco</b>
           </td>
           <td>
            <input type="text" name="banco_proveedor" value="<?=$banco_proveedor?>" size="42" <?=$permiso?>>
           </td>
           <td width="20%" align="right">
            <b>Dirección Banco</b>
           </td>
           <td width="30%">
            <input type="text" name="dir_banco_proveedor" value="<?=$dir_banco_proveedor?>" size="30" <?=$permiso?>>
           </td>
           <td width="5%" align="right">
            <b>Swift</b>
           </td>
           <td width="15%">
            <input type="text" name="swift_proveedor" value="<?=$swift_proveedor?>" size="10" <?=$permiso?>>
           </td>
          </tr>
         </table>
        </td>
       </tr>
       <?
      }//de if($internacional)
      ?>
      <tr>
       <td colspan="4">
        <?if ($select_proveedor!='')
          {echo "<hr>";
           echo credito_proveedor($select_proveedor);
          }
        ?>
       </TD>
      </tr>
     </table><!--Fin de Tabla del proveedor-->
    </td>
   </tr>
   <?
   if($internacional)//si es OC internacional, mostramos la tabla con los datos del despachantes
   {
    $onclick_cargar_despachante="
       window.opener.document.all.id_despachante.value=document.all.elegir_uno.value
       window.opener.document.all.nombre_despachante.value=document.all.nombre.value
       window.opener.document.all.mail_despachante.value=document.all.mail.value
       window.opener.document.all.telefono_despachante.value=document.all.telefono.value
      ";
    $link_despachante=encode_link("seleccion_datos_terceros.php",array("modo"=>"despachantes","onclick_cargar"=>$onclick_cargar_despachante))?>
    <tr>
     <td align="center">
      <input type="hidden" name="id_despachante" value="<?=$id_despachante?>">
      <table width="98%" class="bordes"><!--Tabla de Despachante-->
        <tr align="center" id="sub_tabla">
         <td colspan="2">Despachante</td>
        </tr>
        <tr>
         <td width="15%">
          <b>Nombre</b>
         </td>
         <td>
          <input type="text" name="nombre_despachante" readonly value="<?=$nombre_despachante?>" size="80" <?=$permiso?>>
          &nbsp;
          <input type="button" name="seleccion_despachante" readonly value="Elegir Despachante" style="width:140" onclick="window.open('<?=$link_despachante?>')" <?=$permiso?>>
         </td>
        </tr>
        <tr>
         <td>
          <b>E-mail</b>
         </td>
         <td>
          <input type="text" name="mail_despachante" readonly value="<?=$mail_despachante?>" size="80" <?=$permiso?>>
         </td>
        </tr>
        <tr>
         <td>
          <b>Teléfono</b>
         </td>
         <td>
          <input type="text" name="telefono_despachante" readonly value="<?=$telefono_despachante?>" size="80" <?=$permiso?>>
         </td>
        </tr>
      </table>
     </td>
    </tr>
    <tr>
     <td align="center">
      <table width="98%" class="bordes"><!--Tabla de Condiciones Generales-->
        <tr align="center" id="sub_tabla">
         <td colspan="2">Condiciones Generales</td>
        </tr>
        <tr>
         <td width="15%">
          <b>F.O.B./C.I.F.</b>
         </td>
         <td>
          <?
          if($fob==1 || $fob=="")
          {$check_fob="checked";
           $check_cif="";
          }
          else
          {$check_fob="";
           $check_cif="checked";
          }

          ?>
          <input type="radio" name="fob" value="1" <?=$check_fob?> <?=$permiso?>> F.O.B. &nbsp;&nbsp;
          <input type="radio" name="fob" value="-1" <?=$check_cif?> <?=$permiso?>> C.I.F.
         </td>
        </tr>
        <tr>
         <td width="15%">
          <b>Tipo de flete</b>
         </td>
         <td>
          <input type="text" name="tipo_flete" value="<?=$tipo_flete?>" size="80" <?=$permiso?>>
         </td>
        </tr>
        <tr>
         <td colspan="2">
          <table width="100%">
           <tr>
            <td width="15%">
             <b>Monto Flete/Seguro</b>
            </td>
            <td width="20%">
             <b>U$S</b> <input type="text" name="monto_flete" value="<?=($monto_flete)?number_format($monto_flete,2,'.',''):''?>" size="10" <?=$permiso?> onchange="if(control_numero(monto_flete,'Monto Flete/Seguro')==0)set_montos_fila_oc_internacional();">
           </td>
           <td width="22%" align="right">
            <b>Honorarios y Gastos&nbsp;&nbsp;</b>
           </td>
           <td>
            <b>U$S</b> <input type="text" name="honorarios_gastos" value="<?=($honorarios_gastos)?number_format($honorarios_gastos,2,'.',''):''?>" size="10" <?=$permiso?> onchange="if(control_numero(honorarios_gastos,'Honorarios y Gastos')==0)document.all.total_honorarios_final.value=this.value;else document.all.total_honorarios_final.value='';">
           </td>
          </tr>
         </table>
        </td>
       </tr>
      </table>
     </td>
    </tr>

   <?
   }//de if($internacional)
}//de if($modo!="oc_serv_tec")
elseif($modo=="oc_serv_tec")
{
 $es_stock=1;//Indicamos que el proveedor es un Stock (al ser Orden de Servicio Tecnico, el proveedor es el Stock Serv Tec
 ?>
  <input type="hidden" name="es_stock" value="<?=$es_stock?>">
  <input type="hidden" name="borrar_filas_stock" value="0">
  <input type="hidden" name="id_proveedor_a" value="<?=$select_proveedor?>">
  <input type="hidden" name="nombre_proveedor" value="Stock Serv. Tec. Bs. As.">
  <input type="hidden" name="select_contacto" value="-2">
 <?
}//de elseif($modo!="oc_serv_tec")

//Si la Orden es de Servicio Tecnico, no mostramos nada referido a la Forma de Pago, porque no se aplica en este caso
if($modo!="oc_serv_tec")
{
   ?>
   <tr>
    <td align="center">
     <table width="98%" class="bordes"><!--Tabla de Forma de Pago-->
       <tr align="center" id="sub_tabla">
        <td colspan="5">Forma de Pago</td>
       </tr>
       <tr>
        <?$title_pago="Desplegar la forma de pago para esta orden de compra";?>
        <td width="17%" id="td_pago" style="cursor:hand" title="<?=$title_pago?>"
         <?
         if($estado!="")
         {?>
          onclick="desplegar_forma_pago();"
          <?
         }
         ?>
        >
         <b><u>Forma de Pago</u> </b>&nbsp;
        </td>
        <td width="55%" align="left">
         <select name="select_pago" id="select_pago" <?=$permiso?> <?=$disabled_por_stock?> onchange="if(this.text!='Contado')
                                                                                                    {document.all.tabla_info.style.backgroundColor='<?=$bgcolor_out?>';
                                                                                                     document.all.productos.style.backgroundColor='<?=$bgcolor_out?>';
                                                                                                     document.all.fila_contado1.style.visibility='hidden';
                                                                                                     document.all.fila_contado2.style.visibility='hidden';
                                                                                                    }
                                                                                                    if(	document.all.select_proveedor.value !=-1
                                                                                                    	&& document.all.id_plantilla_pago_js.value != ''
                                                                                                    	&& this.value != -1
                                                                                                       	&& document.all.id_plantilla_pago_js.value != this.value)
                                                                                                       document.all.warning_forma_pago.style.display='block';
                                                                                                       else
                                                                                                       document.all.warning_forma_pago.style.display='none';
                                                                                                    "
         >
          <option value="-1" selected>Seleccione una forma de pago</option>
          <?
          if($nro_orden && $nro_orden!=-1 && !$es_stock && $select_pago)
          {
             $query_pago="select orden_de_compra.id_plantilla_pagos,plantilla_pagos.descripcion
             			  from compras.orden_de_compra join compras.plantilla_pagos using (id_plantilla_pagos)
             			  where orden_de_compra.nro_orden=$nro_orden";
          	 $res_pago=sql($query_pago,"<br>Error al traer la plantilla de pagos<br>") or fin_pagina();
          	 $select_pago=$res_pago->fields['id_plantilla_pagos'];
	         $descripcion_pago=$res_pago->fields['descripcion'];
    	     $add_query="or plantilla_pagos.id_plantilla_pagos=$select_pago";
          }//de if($nro_orden && $nro_orden!=-1 && !$es_stock && $select_pago)
          else
           $add_query="";
          if($estado=="" || $estado=="p" || $estado=="r" || $estado=="u" || $estado=="n")
           $q="select plantilla_pagos.id_plantilla_pagos,plantilla_pagos.descripcion,plantilla_pagos.mostrar
           	  from compras.plantilla_pagos
           	  where plantilla_pagos.mostrar=1 $add_query order by plantilla_pagos.descripcion ";
          else
           $q="select plantilla_pagos.id_plantilla_pagos,plantilla_pagos.descripcion,plantilla_pagos.mostrar
               from compras.plantilla_pagos
               where plantilla_pagos.id_plantilla_pagos=$select_pago ";

          $pagos=sql($q,"<br>Error al consultar por la forma de pagos de la OC<br>") or fin_pagina();
          $style_contado="";
          $cartel_contado="&nbsp;";
          while (!$pagos->EOF)
          {
           if($pagos->fields['descripcion']=="Contado" && !$es_stock && $pagos->fields['id_plantilla_pagos']==$select_pago)
           {$style_contado="document.all.tabla_info.style.backgroundColor='#f0d1d2';
                         document.all.productos.style.backgroundColor='#f0d1d2';";
            $cartel_contado="<font size=2  color='red'><b>PAGO CONTADO</b></font>";
           }
           ?>
           <option value="<?=$pagos->fields['id_plantilla_pagos']?>"
             <?
              if($pagos->fields['id_plantilla_pagos']==$select_pago || ($internacional==1 && $select_pago=="" && $pagos->fields['descripcion']=="Pagos de Orden de Compra Internacional"))
                echo ' selected';

             ?>
           >
            <?=$pagos->fields['descripcion']?>
           </option>
           <?
           $pagos->MoveNext();
          }//de while (!$pagos->EOF)
          ?>
         </select>

         <input type="button" name="nueva_forma" value="Forma de Pago" <?=$disabled_por_stock?> <?=$permiso_forma_pago?> <?if($orden->fields['fecha']<"2004-02-02 00:00:00" && $estado!="e"&& $estado!="") echo " disabled ";elseif(($select_moneda==0 || $select_moneda==-1)&& !$internacional)echo "disabled title='Primero debe seleccionar la moneda'";else echo "title='Permite agregar una nueva forma de pago o editar la que está seleccionada'";?> onclick="window.open('<?=encode_link('ord_pagos.php',array('pago_especial'=>$orden->fields['habilitar_pago_especial'],'reload'=>1,"presupuesto"=>$presupuesto))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=0,top=0,width=800,height=500')">&nbsp;
         <?
         if(permisos_check("inicio","permiso_boton_despagar_oc"))
         {if($estado=="d" || $estado=="g")
          {
          ?>
           <input type="submit" name="despagar" value="D$" onclick="return confirm('Al despagar una Orden de Compra se borrarán egresos de caja o se anularán cheques cargados, que hayan sido usados para pagar esa Orden de Compra\n¿Está seguro que desea despagar la Orden de Compra Nº <?=$nro_orden?>?')">&nbsp;
          <?
          }//de if($estado=="d" || $estado=="g")
         }//de if(permisos_check("inicio","permiso_boton_despagar_oc"))
         ?>
        </td>
        <td width="1%" align="right">
         <strong>Moneda </strong>
        </td>
        <td width="1%">
         <select  name="select_moneda" <?echo $permiso; if($orden->fields['fecha']>"2004-02-02 00:00:00" || $estado=="")
                                                     {?>
        											  onchange="
                                                      <?
                                                      if($disabled_por_stock!="disabled")
                                                      {?>
                                                        if(this.options[this.options.selectedIndex].value!=-1)
                                                        {document.all.nueva_forma.disabled=0;
                                                         document.all.nueva_forma.title='Permite agregar una nueva forma de pago o editar la que está seleccionada';
                                                        }
                                                       <?
                                                       }
                                                       ?>
                                                       if(this.options[this.options.selectedIndex].text=='Dólares')
                                                       {document.all.valor_moneda.style.visibility='visible';
                                                       }
                                                       else
                                                       {document.all.valor_moneda.style.visibility='hidden';
                                                       }"
        											 <?
                                                     }//de if($orden->fields['fecha']>"2004-02-02 00:00:00" || $estado=="")
        											 ?>
         >
          <option value="-1">Seleccione el tipo</option>
          <?
          $q="select moneda.id_moneda,moneda.nombre,moneda.simbolo,moneda.observaciones from licitaciones.moneda";
          $moneda=sql($q,"<br>Error al traer los datos de las monedas<br>") or fin_pagina();
          while (!$moneda->EOF)
          {?>
           <option value="<?=$moneda->fields['id_moneda']?>" <? if ($moneda->fields['id_moneda']==$select_moneda ||($select_moneda==""&&$internacional&&$moneda->fields['simbolo']=="U\$S"))
                                                                {
                                                                  echo " selected"; $tipo_moneda=$moneda->fields['nombre'];
                                                                }
                                                             ?>
           >
            <?=$moneda->fields['nombre']?>
           </option>
           <?
	       $moneda->MoveNext();
          } //del while que selecciona la moneda
          ?>
         </select>
        </td>
        <?
        if($tipo_moneda=="Dólares")
          $visibility="visibility:visible;";
        else
         $visibility="visibility:hidden;";
        ?>
        <td width="20%" style="<?=$visibility?>" id="valor_moneda" align="right">
         <b>&nbsp;Valor Dolar </b><input type="text" name="valor_dolar" <?=$permiso?> value="<?=number_format($valor_dolar,3,'.','')?>" size="7">
        </td>
       </tr>
       <?
       //agregamos (si hay) las ordenes que es tan asociadas por pago
       //multiple, a la orden que se esta viendo
       $string_ordenes="";
       $ordenes_atadas=PM_ordenes($nro_orden);
       //lo pasamos a un string, para poder generarla facilmente con JavaScript
       $tam=sizeof($ordenes_atadas);
       if($tam>1)
       {$string_ordenes="La Orden de Compra forma parte de un <b>Pago Múltiple</b> junto a las ordenes: ";
        for($i=0;$i<$tam;$i++)
        {$string_ordenes.=" <b>".$ordenes_atadas[$i]."</b>";
        }//de for($i=0;$i<$tam;$i++)
        ?>
        <tr>
         <td colspan=5 bgcolor="#800000" align="center">
          <font color="white"><?=$string_ordenes?></font>
         </td>
        </tr>
        <?
       }//de if($tam>1)
       ?>
      <tr>
   	   <td colspan="5" style="color:'red';" align="center">
        <div id='warning_forma_pago' style="display:<?if (($descripcion_pago == $pago_estandar) || ($select_pago == -1) || ($pago_estandar=='')) echo 'none'; else echo 'block';?>">
         <STRONG>La forma de pago seleccionada es distinta de la estándar</STRONG>
	    </div>
	   </td>
      </tr>
      <tr>
       <td colspan=5>
        <table id='table_pago' style="visibility:hidden" border="1" width="100%">
         <?
          /*************************************************
           guardamos en hiddens los datos a mostrar, para
           poder desplegar la forma de pago (solo si la orden
           ya fue guardad al menos una vez)
          **************************************************/
          if($estado!="")
          {//taremos los datos de la forma de pago de la orden cargada

           $query="select plantilla_pagos.descripcion as nombre,forma_de_pago.dias,tipo_pago.descripcion,x.monto,x.valor_dolar
                   from compras.orden_de_compra join compras.plantilla_pagos using (id_plantilla_pagos)
                   join compras.pago_plantilla using (id_plantilla_pagos) join compras.forma_de_pago using(id_forma)
                   join compras.tipo_pago using(id_tipo_pago)
                   left join (select distinct ordenes_pagos.id_forma,ordenes_pagos.monto,ordenes_pagos.valor_dolar
                   			  from compras.orden_de_compra
                              join compras.pago_orden using (nro_orden) join compras.ordenes_pagos using(id_pago)
                   			  where orden_de_compra.nro_orden=$nro_orden) as x using (id_forma)
                   where orden_de_compra.nro_orden=$nro_orden";
           $forma_de_pago_info=sql($query,"<br>Error al traer datos de la forma de pago de la OC<br>") or fin_pagina();
           ?>
           <input type="hidden" name="nombre_forma" value="<?=$forma_de_pago_info->fields['nombre']?>">
           <input type="hidden" name="cant_pagos" value="<?=$forma_de_pago_info->RecordCount()?>">
           <input type="hidden" name="mostrar_dolar" value="<?if($tipo_moneda=="Dólares")echo "1";else echo "0"?>">
           <?
           //generamos los hiddens de la forma de pago de acuerdo a la cantidad
           $x=0;
           while(!$forma_de_pago_info->EOF)
           {?>
            <input type="hidden" name="pago_<?=$x?>_tipo" value="<?=$forma_de_pago_info->fields['descripcion']?>">
            <input type="hidden" name="pago_<?=$x?>_dias" value="<?=$forma_de_pago_info->fields['dias']?>">
            <input type="hidden" name="pago_<?=$x?>_monto" value="<?=formato_money($forma_de_pago_info->fields['monto'])?>">
            <input type="hidden" name="pago_<?=$x?>_dolar" value="<?=number_format($forma_de_pago_info->fields['valor_dolar'],3,'.','')?>">
            <?
            $x++;
            $forma_de_pago_info->MoveNext();
           }//de while(!$forma_de_pago_info->EOF)
           //generamos (si hay) los hiddens para las notas de credito asociadas
           //a la orden de compra
           $cant_nc=$notas_credito->RecordCount();
           ?>
           <input type="hidden" name="cant_nc" value="<?=$cant_nc?>">
           <?
           $montos_nc=0;
           if($cant_nc>0)
           {$i=0;
            while(!$notas_credito->EOF)
            {?>
             <input type="hidden" name="notac_nro_<?=$i?>" value="<?=$notas_credito->fields['id_nota_credito']?>">
             <input type="hidden" name="notac_moneda_<?=$i?>" value="<?=$notas_credito->fields['simbolo']?>">
             <input type="hidden" name="notac_monto_<?=$i?>" value="<?=$notas_credito->fields['monto']?>">
             <input type="hidden" name="notac_obs_<?=$i?>" value="<?if($notas_credito->fields['observaciones'])echo $notas_credito->fields['observaciones'];else echo "&nbsp;"?>">
             <?
             $monto_local=$notas_credito->fields['monto'];
             //Calculo del total para pagar usando notas de credito
             //si la orden es en pesos y la nota de credito en dolares,
             //se multiplica la nota de credito por el valor dolar
             if($tipo_moneda!="Dólares" && $notas_credito->fields['simbolo']=="U\$S")
             {
              $monto_local*=$notas_credito->fields['valor_dolar'];
             ?>
              <input type="hidden" name="notac_valor_dolar_<?=$i?>" value="<?=number_format($notas_credito->fields['valor_dolar'],3,'.','')?>">
             <?
             }//de if($tipo_moneda!="Dólares" && $notas_credito->fields['simbolo']=="U\$S")
             //si la orden es en dolares y la nota de credito en pesos,
             //se divide la nota de credito por el valor dolar
             elseif($tipo_moneda=="Dólares" && $notas_credito->fields['simbolo']=="$")
             { $monto_local/=$notas_credito->fields['valor_dolar'];
             ?>
              <input type="hidden" name="notac_valor_dolar_<?=$i?>" value="<?=number_format($notas_credito->fields['valor_dolar'],3,'.','')?>">
             <?
       	     }//de elseif($tipo_moneda=="Dólares" && $notas_credito->fields['simbolo']=="$")
             else
             {?>
              <input type="hidden" name="notac_valor_dolar_<?=$i?>" value="-1">
             <?
             }
             $montos_nc+=$monto_local; //acumulamos el total de las notas de credito de la orden
             $notas_credito->MoveNext();
             $i++;
            }//del while
           }//de if($cant_nc>0)
          }//de if($estado!="")
          /**************************************************/
          ?>
          </table>
          <input type="hidden" name="montos_nc" value="<?=$montos_nc?>">
         </td>
        </tr>
       </table><!--Fin de Tabla de Forma de Pago-->
      </td>
     </tr>
<?
}//de if($modo!="oc_serv_tec")
elseif ($modo=="oc_serv_tec")//Si la Orden es de Serv Tec
{
  //Le ponemos una forma de pago por default (contado)
  $query="select plantilla_pagos.id_plantilla_pagos from compras.plantilla_pagos where plantilla_pagos.descripcion='Contado'";
  $pago_contado=sql($query,"<br>Error al traer el id de la forma de pago contado<br>") or fin_pagina();
  $select_pago=$pago_contado->fields["id_plantilla_pagos"];

  //traemos el id de la moneda dolar, para ponerlo como default en la Orden de Servicio Tecnico
  $query="select moneda.id_moneda from licitaciones.moneda where simbolo='U\$S'";
  $moneda_dolar=sql($query,"<br>Error al traer el id de la moneda dolar<br>") or fin_pagina();
  $select_moneda=$moneda_dolar->fields["id_moneda"];
  ?>
  <input type="hidden" name="select_pago" value="<?=$select_pago?>">
  <input type="hidden" name="select_moneda" value="<?=$select_moneda?>">
  <input type="hidden" name="valor_dolar" value="1">
  <?
}//de elseif ($modo=="oc_serv_tec")

//Si la OC no es de Servicio Tecnico, mostramos la tabla de cliente
if ($modo!="oc_serv_tec")
{
?>

     <tr>
      <td nowrap align="center">
       <table width="98%" class="bordes"><!--Tabla de Cliente-->
        <tr align="center" id="sub_tabla">
         <td colspan="3">Cliente</td>
        </tr>
        <tr>
         <td width="50%">
          <a title="Haga click para ver elegir/editar el cliente"
           <?
           if ($permiso=="")
	       {
            ?>
            onclick="
             if (wcliente==0 || wcliente.closed)
	          wcliente=window.open('<?=encode_link('elegir_cliente.php',array('onclickaceptar'=>"window.opener.cargar_cliente();window.close()",'onclikaceptar2'=>"window.opener.cargar_cliente_mas_usados();window.close()",'onclicksalir'=>'window.close()'))?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1');
             else
	         if (!wcliente.closed)
	 	      wcliente.focus();
            "
            <?
           }//de if ($permiso=="")
           ?>
          >
           <b><u>Cliente</u></b> (<---Haga click en la palabra para editar/elegir el cliente)
          </a>
         </td>
         <td width="50%">
          <b>Lugar y Forma de Entrega</b>
         </td>
        </tr>
        <tr>
         <td align="center">
           <?
           $max_800_600=50;
           $max_1024_768=67;
           $max_otro=90;
	       if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	        $longitud_fila=$max_800_600;
	       elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	        $longitud_fila=$max_1024_768;
	       else//si es una resolucion mayor a 1024
	        $longitud_fila=$max_otro;
	       if($cliente=="")
	        $cliente="Haga click en la palabra cliente para ver la lista";
           //$cliente=ajustar_lineas_texto($cliente,$longitud_fila);
	       ?>
           <textarea name="cliente" style="width:95%" rows="<?=row_count($cliente,$longitud_fila)?>" readonly wrap="VIRTUAL"  <?=$permiso?>><?=$cliente?></textarea>
           <input name="id_entidad" type="hidden" value="<?=$id_entidad?>">
         </td>
         <td align="center">
          <?if(!$entrega && $estado=="")
             $entrega="Se lleva a Coradir BsAs Patagones 2538 - Parque Patricios Coordinar la entrega con Graciela Tedeschi 011-5354-0300";

           $entrega=ajustar_lineas_texto($entrega,$longitud_fila);
           ?>
           <textarea name="entrega" style="width:95%" rows="<?=row_count($entrega,$longitud_fila)?>" wrap="VIRTUAL" id="entrega" <?=$permiso?>><?=$entrega?></textarea>
         </td>
        </tr>
       </table>
     </td>
    </tr>
  <?
  if($licitacion && !$presupuesto && !$desde_presupuesto &&($estado=="p" || $estado=="u" || $estado=="" || $estado=="r"))
  {?>
   <tr>
    <td colspan="3">
     <font color="red" size="5">
      <b>
       Atención: Esta Orden de Compra asociada a licitación, no fue hecha a través de un presupuesto.
       Por favor, extreme controles antes de autorizar la orden.
      </b>
     </font>
    </td>
   </tr>
   <?
  }//de if($licitacion && !$presupuesto && !$desde_presupuesto &&($estado=="p" || $estado=="u" || $estado=="" || $estado=="r"))
}//de if ($modo!="oc_serv_tec")
elseif ($modo=="oc_serv_tec")//Si la OC es de Servicio Tecnico, ponemos como cliente a Coradir, por default
{
 $query="select entidad.id_entidad from licitaciones.entidad where entidad.nombre='Coradir S.A.'";
 $entidad_id=sql($query,"<br>Error al traer el id de la entidad Coradir<br>") or fin_pagina();
 $id_entidad=$entidad_id->fields["id_entidad"];
?>
 <input type="hidden" name="id_entidad" value="<?=$id_entidad?>">
 <input type="hidden" name="cliente" value="Coradir S.A.">
<?
}//de elseif ($modo=="oc_serv_tec")

//Si la OC no es de Servicio Tecnico, mostramos la tabla de comentarios
if ($modo!="oc_serv_tec")
{
?>
  <tr>
   <td>
    <table width="98%" class="bordes" align="center"><!--Tabla de Comentarios-->
     <tr align="center" id="sub_tabla">
      <td colspan="3">Comentarios</td>
     </tr>
     <tr>
      <td colspan="3" align="center">
       <?
       $max_800_600=110;
       $max_1024_768=140;
       $max_otro=189;
	   if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	    $longitud_fila=$max_800_600;
	   elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	    $longitud_fila=$max_1024_768;
	   else//si es una resolucion mayor a 1024*768
	    $longitud_fila=$max_otro;
       $notas=ajustar_lineas_texto($notas,$longitud_fila);
	       ?>
       <textarea name="notas" style="width:95%;" rows="<?=row_count($notas,$longitud_fila)?>" wrap="VIRTUAL"><?=$notas?></textarea>
       <br>
       <?
       if ($estado!="p" && $estado!="u" && $estado!="")
       {
       ?>
        <input name="guardar_coment" type="submit"  value="Guardar Comentario" <?=$anulada?>>
       <?
       }//de if ($estado!="p" && $estado!="u" && $estado!="")
       ?>
      </td>
     </tr>
    </table>
   </td>
  </tr>
  <?
}//de if ($modo!="oc_serv_tec")

  //Los comentarios de comunicacion con el proveedor, no se muestran en las Ordenes de Servicio Tecnico
  if ($modo!="oc_serv_tec")
  {
   if ($estado!="")
   {?>
    <tr>
     <? /*** agrego al tabla para los comentarios usando la funcion gestiones_comentarios ***/
     $sql = "SELECT gestiones_comentarios.id_comentario
	         FROM general.gestiones_comentarios WHERE gestiones_comentarios.id_gestion=$nro_orden
	         AND gestiones_comentarios.tipo='ORDEN_COMPRA'";
	 $resu=sql($sql, "Error al traer los comentarios para la Orden de Compra") or fin_pagina();
     ?>
	 <td>
	  <table width="98%" class="bordes" align="center"><!--Tabla de Comunicacion con el proveedor-->
       <tr align="center" id="sub_tabla">
        <td colspan="3">
         <table width="100%">
          <tr>
           <td width="1%">
            <img src='../../imagenes/drop2.gif' border=0 style='cursor: hand;'
	         onClick='if (this.src.indexOf("drop2.gif")!=-1)
                      {
	                   this.src="../../imagenes/dropdown2.gif";
		               div_comentario.style.overflow="visible";
	                  }
	                  else
	                  {
		               this.src="../../imagenes/drop2.gif";
		               div_comentario.style.overflow="hidden";
	                  }'
	        >
           </td>
           <td align="center">
	        <?
	        if ($resu->recordcount()>=1)
	        {?>
	         &nbsp;<b><blink>Comunicación con el Proveedor</blink></b>
	         <?
	        }
	        else
	        {?>
	         &nbsp;<b>Comunicación con el Proveedor</b>
	         <?
	        }
	        ?>
          </td>
         </tr>
        </table>
       </td>
      </tr>

	  <tr>
	   <td>
	    <div id='div_comentario' style='border-width: 0;overflow: hidden;height: 1'>
	     <?gestiones_comentarios($nro_orden,"ORDEN_COMPRA",1);?>
	     <br>
	     <center>
	      <input type='submit' name='guardar_comunicacion' <?=$anulada?> value='Guardar Comunicación Proveedor'>
	     </center>
	    </div>
       </td>
      </tr>
     </table>
	</td>
   </tr>
   <?
   }//de if ($estado!="")
  }//de if ($modo!="oc_serv_tec")

   /*******************************************************
    Mostramos campos internos de Coradir
   ********************************************************/
//Si la OC no es de Servicio Tecnico, mostramos la tabla de Seguimiento Interno del material en Coradir
if ($modo!="oc_serv_tec")
{

   if ($chequeado_avisar)
   {
 	$mostrar_tabla="block";
     $checked_avisar="checked";
   }
   else
   {
     $mostrar_tabla="none";
     $checked_avisar="";
   }
   if ($estado_avisar!='p' && $estado_avisar!='r' && $estado_avisar!='u' && $estado_avisar!='')
   {
    $textarea="readonly";
    $checkbox="disabled";
   }
   else
   {
    $textarea="";
    $checkbox="";
   }
   ?>
   <tr>
    <td>
     <table width="98%" class="bordes" align="center"><!--Tabla de Seguimiento Interno Coradir-->
      <tr align="center" id="sub_tabla">
       <td colspan="3">Seguimiento Interno del material en Coradir</td>
      </tr>
      <tr>
       <td widht=100% align="right">
        <div align="center">
          <table width="100%">
           <tr align="left">
            <td align="center">
             <b>Para seguimiento interno del material en Coradir</b>
            <td>
           </tr>
           <tr>
            <td align="center" width="100%">
             <?
             $max_800_600=110;
             $max_1024_768=155;
             $max_otro=190;
	         if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	          $longitud_fila=$max_800_600;
	         elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	          $longitud_fila=$max_1024_768;
	         else//si es una resolucion mayor a 1024
	          $longitud_fila=$max_otro;
	         $notas_internas=ajustar_lineas_texto($notas_internas,$longitud_fila);
	         ?>
             <textarea name="notas_internas" <?=$permiso?> style="width:95%" rows="<?=row_count($notas_internas,$longitud_fila)?>" wrap="VIRTUAL" onkeypress="more_rows(this,5)" ><?=$notas_internas?></textarea>
            </td>
           </tr>
          </table>
         </div>
        </td>
       </tr>
      </table>
     <?
     /*******************************************************
      Fin de: Mostramos campos internos de Coradir
     ********************************************************/
     ?>
    </td>
   </tr>
   <?
}//de if ($modo!="oc_serv_tec")


   /*******************************************************
    Generamos el select con los proveedores para elegir
    aquel que se usara para generar el RMA.
   ********************************************************/
   if($caso || $orden_prod)
   {
    if($estado=='' || $estado=='p' || $estado=='u')
       $seleccione_rma="Seleccione el Proveedor de los productos que generan el RMA";
    else
       $seleccione_rma="Proveedor Seleccionado para RMA";
    ?>
    <tr>
     <td>
      <br>
      <table width="98%" class="bordes" align="center"><!--Tabla de Proveedor para RMA-->
       <tr id="sub_tabla">
        <td colspan="2"><?=$seleccione_rma?></td>
       </tr>
       <tr>
        <td align="center">
         <input type="hidden" name="generar_reclamo_parte" value=1 >
         <b><?=$seleccione_rma?></b>
         <select name="proveedor_reclamo" style="width:300px" onKeypress= "buscar_op(this)" onblur="borrar_buffer()" onclick= "borrar_buffer()" <?=$permiso?>>
          <option value=-1>Seleccione un proveedor</option>
          <?
          if($modo!="oc_serv_tec")
          {if($estado=='a' || $estado=='e' || $estado=='d'|| $estado=='g')
           {$query="select proveedor.razon_social,proveedor.id_proveedor from general.proveedor where proveedor.id_proveedor=$proveedor_reclamo";
            $proveedores=sql($query,"<br>Error al traer proveedor de reclamo<br>") or fin_pagina();
           }
   		  }//de if($modo!="oc_serv_tec")
   		  else if($modo=="oc_serv_tec")
   		  {
   		   $query="select proveedor.razon_social,proveedor.id_proveedor from general.proveedor where proveedor.razon_social not ilike '%Stock%'order by proveedor.razon_social";
            $proveedores=sql($query,"<br>Error al traer los datos del proveedor de RMA<br>") or fin_pagina();
   		  }
          $proveedores->Move(0);
          while (!$proveedores->EOF)
          {
           ?>
           <option value="<?=$proveedores->fields['id_proveedor']?>" <?if($proveedor_reclamo==$proveedores->fields['id_proveedor']) echo " selected ";?> ><?=$proveedores->fields['razon_social']?></option>
           <?
           $proveedores->MoveNext();
          }
          ?>
         </select>
         <input type="hidden" name="gen_reclamo" value="<?=$generar_reclamo_parte?>">
        </td>
       </tr>
      </table>
     <td>
    </tr>
      <?
   }//de if($caso || $orden_prod)
   /*******************************************************
    Fin de: Generamos el select con los proveedores para
    elegir aquel que se usara para generar el RMA.
   ********************************************************/

   /*******************************************************
    Mostramos a quien se envio la OC si el estado es mayor
    que autorizada (enviada, p. pagada o t. pagada)
   ********************************************************/
   //el dato de a quien se envio la OC no se aplica en las Ordenes de Servicio tecnico, porque no tienen ese estado
   if ($modo!="oc_serv_tec")
   {if ($estado=='g' || $estado=='e' || $estado=='d')
    {
     $consulta_mail="select ord_compra_mails.para,ord_compra_mails.fecha_envio, ord_compra_mails.user_name
     				from compras.ord_compra_mails
     				where ord_compra_mails.nro_orden=$nro_orden order by ord_compra_mails.fecha_envio desc";
     $res_mail=sql($consulta_mail,"<br>Error en la consulta de los mails de orden de compra<br>") or fin_pagina();
     ?>
     <tr>
      <td>
       <table width="98%" class="bordes" align="center"><!--Tabla de Seguimiento Interno Coradir-->
        <tr align="center" id="sub_tabla">
         <td>Orden de Compra enviada a</td>
        </tr>
        <tr>
         <td>
          <table align="center">
           <?
           //mostramos una fila por cada entrada en la tabla para esta orden, con limite de 5.
           $cont_mail=0;$primera_fila=1;
           while(!$res_mail->EOF && $cont_mail<5)
           {
            $para_mail=$res_mail->fields['para'];
            $fecha_envio=$res_mail->fields['fecha_envio'];
            $user_name_mail=$res_mail->fields['user_name'];
            if($cont_mail%2==0)
            {?>
            <tr>
            <?
            }
            ?>
             <td width="50%">
              <table border="1" align="center" >
               <tr>
                <td width="65%"><b>E-mail</b></td>
                <td width="35%"><b>Fecha y Usuario</b></td>
               </tr>
               <tr>
                <td width="65%">
                 <textarea cols="35" rows=3 name=mails readonly><?=$para_mail?></textarea>
                </td>
                <td width="35%" align="center">
                 <table>
                  <tr>
                   <td align="center">
                    <b><?=fecha($fecha_envio)?></b>
                   </td>
                  </tr>
                  <tr>
                   <td align="center">
                    <b><?=$user_name_mail?></b>
                   </td>
                  </tr>
                 </table>
                </td>
               </tr>
              </table>
             </td>
            <?
            if($cont_mail%2!=0)
            {?>
            </tr>
            <?
            }
            $cont_mail++;
            $res_mail->MoveNext();
           }//de while(!$res_mail->EOF && $cont_mail<5)
           ?>
          </table>
         </td>
        </tr>
       </table>
      </td>
     </tr>
    <?
    }//de if ($estado=='g' || $estado=='e' || $estado=='d')
   }//de if ($modo!="oc_serv_tec")
  /*******************************************************
   Fin de: Mostramos a quien se envio la OC si el estado
   es mayor que autorizada (enviada, p. pagada o t. pagada)
  ********************************************************/

  if($licitacion!=0 && $licitacion!="")
  {?>
    <input name="licitacion" type="hidden" size="6" value="<?=$licitacion?>" >
  <?
  }//de if($licitacion!=0 && $licitacion!="")
 /*******************************************************
  Fin de Mostrar los datos propios de la OC
 ********************************************************/
  ?>
  </table>
  <br>
  <?
/*************************************************************************************************************
 FIN DE SECCION: DATOS DE LA OC Y MAS
**************************************************************************************************************/

/*************************************************************************************************************
   SECCION: LISTA DE PRODUCTOS DE LA OC
**************************************************************************************************************/
//fijamos la cantidad maxima de caracteres por linea que deben tener los textarea de las filas
$max_800_600=50;
$max_1024_768=80;
$max_otro=100;

//si la OC es internacional, hacemos mas ancho de lo comun la tabla, para que entren todos los datos adicionales
if($internacional)
 $width_tabla_productos="145%";
else
 $width_tabla_productos="100%";
?>
<br>
<table width="<?=$width_tabla_productos?>" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor="<?=$bgcolor_out?>" class="bordes">
 <tr id="mo">
  <td colspan="3">
   <font size="3"><b>Productos de la <?=$titulo_pagina?></b></font>
  </td>
 </tr>
 <tr>
  <td>
  <table width="100%">
   <tr>
    <td width="20%" valign="bottom">
     <?
     //si tiene permiso, mostramos check para habilitar los cambios a la fila para las OC
     //con estado es 'a', 'e', 'd' o 'g'.
     if(($modo!="oc_serv_tec") && ($estado=='a' || $estado=='e' || $estado=='d' || $estado=='g') && permisos_check("inicio","permiso_borrar_fila_especial"))
     {?>
	  <input type="checkbox" name="check_habilitar_cambios" value="1" onclick="habilitar_cambios_especiales()"> Modificar Filas
      <?
     }
     ?>
    </td>
    <td id='fila_contado1' width="50%" valign="bottom">
     <?
     if($style_contado!="")
     {
      echo "$cartel_contado&nbsp;&nbsp;&nbsp;$cartel_contado&nbsp;&nbsp;&nbsp;$cartel_contado";
     }//de if($style_contado!="")
     else
      echo "&nbsp;";
     ?>
    </td>
    <td width="25%">
     <table width="100%" border="1">
      <tr>
       <td align="center">
        <b>Recuerde no utilizar separador<br>de miles al insertar los montos</b>
       </td>
      </tr>
     </table>
    </td>
   </tr>
  </table>
  </td>
 </tr>
 <tr>
  <td>
   <table id="productos" width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 bgcolor="<?=$bgcolor_out?>" >
    <tr id="sub_tabla">
      <td nowrap width="4%" align="center" >
      <input type=checkbox name="elegir_todos" <?=$permiso?> title="Seleccionar todas las filas" onclick="seleccionar_todos(this,document.form1.chk)">
      </td>
      <td nowrap width="65%" align="center">Producto</td>
      <?
      if($internacional)
      {?>
       <td nowrap align="center">POSAD</td>
       <?
      }
      ?>
      <td nowrap width="5%" align="center">Cantidad</td>
      <td nowrap width="10%" align="center">Precio Unitario</td>
      <td nowrap width="25%" align="center">Subtotal Final</td>
      <?
      //si la OC es internacional, mostramos los encabezados de columnas necesarios para los datos de las OC adicionales
      if($internacional)
      {?>
       <td nowrap width="20%" align="center">Proporcional Flete</td>
       <td nowrap width="20%" align="center">Base Imponible C.I.F.</td>
       <td nowrap width="20%" align="center">Derechos</td>
       <td nowrap width="20%" align="center">I.V.A.</td>
       <td nowrap width="10%" align="center">I.B.</td>
       <?
      }//de if($internacional)
      ?>
    </tr>
  <?
  $total=0;
  //si no cambio el filtro proveedor (si no se refresco la pagina)
  if(!$refresh)
  {
   /***************************************************************
    Generacion de Filas: HONORARIO DE SERVICIO TECNICO
   ****************************************************************/
   if ($idate && $casos)
   {
     while (!$casos->EOF)
     {
      $link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$productos->fields['id_producto']));
         ?>
        <tr>
          <td align="center">
           <input name="chk" type="checkbox" id="chk" value="1">
          </td>
          <input type="hidden" name="idp_<?=$items?>" value="<?=$productos->fields['id_producto']?>">
          <input type="hidden" name="nro_caso_<?=$items?>" value="<?=$casos->fields['idcaso']?>">
          <td>
           <?
            $producto_hcaso="Honorario C.A.S Correspondiente al Caso nro: ".$casos->fields['nrocaso'];
            $desc_adic=$_POST['h_desc_'.$items];
           ?>
           <input type='hidden' value='<?=$desc_adic?>' name='h_desc_<?=$items?>'>
           <input type='hidden' value='<?=$producto_hcaso?>' name='desc_orig_<?=$items?>'>
           <?
	       if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	        $longitud_fila=$max_800_600;
	       elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	        $longitud_fila=$max_1024_768;
	       else//si es una resolucion mayor a 1024
	        $longitud_fila=$max_otro;
	       //$texto_desc=ajustar_lineas_texto($producto_hcaso." ".$desc_adic,$longitud_fila);
	       $texto_desc=ajustar_lineas_texto($producto_hcaso." ".$desc_adic,$longitud_fila);
	       ?>
           <textarea name="desc_<?=$items?>" style="width:90%" rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$items?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$items?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
          </td>
          <td align="center">
           <input name="cant_<?=$items?>" type="text" size="6" style='text-align:right' value="1" onchange="calcular(this)" >
          </td>
          <td align="center">
           <input name="unitario_<?=$items?>" type="text" size="10" style='text-align:right' value="<?=number_format($casos->fields['costofin'],2,".","")?>" onchange="this.value=this.value.replace(',','.');calcular(this)">
           <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
          </td>
          <td align="center">
           <input name="subtotal_<?=$items?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($casos->fields['costofin'],2,".","")?>">
          </td>
        </tr>
       <?
       $items++;
       $total+=$casos->fields['costofin'];
       $casos->movenext();
     }//de while (!$casos->EOF)
   }//de if ($idate && $casos)

  /***************************************************************
   Fin de Generacion de Filas: HONORARIO DE SERVICIO TECNICO
  ****************************************************************/

  /***************************************************************
    Generacion de Filas: CASOS
  ****************************************************************/
    //Si la Orden es de Servicio Tecnico no trae los productos del caso
    if ($caso && $productos && $modo!="oc_serv_tec")
    {
     while (!$productos->EOF)
     {
      $link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$productos->fields['id_producto']));
      ?>
      <tr>
       <td align="center">
        <input name="chk" type="checkbox" id="chk" value="1">
       </td>
       <input type="hidden" name="idp_<?=$items?>" value="<?=$productos->fields['id_producto']?>">
       <td>
         <? $desc_adic=$_POST['h_desc_'.$items];?>
         <input type='hidden' value='<?=$desc_adic?>' name='h_desc_<?=$items?>'>
         <input type='hidden' value='<?=$productos->fields['desc_gral']?>' name='desc_orig_<?=$items?>'>
         <?
         if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	        $longitud_fila=$max_800_600;
	       elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	        $longitud_fila=$max_1024_768;
	       else//si es una resolucion mayor a 1024
	        $longitud_fila=$max_otro;
	      $texto_desc=ajustar_lineas_texto($productos->fields['desc_gral']." ".$desc_adic,$longitud_fila);
	     ?>
         <textarea name="desc_<?=$items?>" style="width:90%" rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$items?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$items?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
       </td>
       <td align="center">
        <input name="cant_<?=$items?>" type="text" size="6" style='text-align:right' value="<?=$productos->fields['cantidad']?>" onchange="calcular(this)" >
       </td>
       <td align="center">
        <input name="unitario_<?=$items?>" type="text" size="10" style='text-align:right' value="<?=number_format($productos->fields['precio_stock'],2,".","")?>" onchange="this.value=this.value.replace(',','.');calcular(this)">
        <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
       </td>
       <td align="center">
        <input name="subtotal_<?=$items?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($productos->fields['cantidad']*$productos->fields['precio_stock'],2,".","")?>">
       </td>
      </tr>
      <?
      $total+=$productos->fields['cantidad']*$productos->fields['precio_stock'];
      $productos->MoveNext();
      $items++;
     }//de while (!$productos->EOF)
    }//de if ($caso && $productos)
   /***************************************************************
    Fin de Generacion de Filas: CASOS
   ****************************************************************/

   /***************************************************************
    Generacion de Filas: LICITACION Y PRESUPUESTO
   ****************************************************************/
   if ($licitacion && $productos)
   {
    while (!$productos->EOF)
    {
     $link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$productos->fields['id_producto']));
 	 ?>
	 <tr>
	  <td align="center">
	   <input name="chk" type="checkbox" id="chk" value="1">
	  </td>
	  <input type="hidden" name="idp_<?=$items?>" value="<?=$productos->fields['id_producto']?>">
	  <td>
	   <?$desc_adic=$_POST['h_desc_'.$items];?>
	   <input type='hidden' value='<?=$desc_adic?>' name='h_desc_<?=$items?>'>
	   <input type='hidden' value='<?=$productos->fields['desc_gral']?>' name='desc_orig_<?=$items?>'>
       <?
         if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	        $longitud_fila=$max_800_600;
	       elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	        $longitud_fila=$max_1024_768;
	       else//si es una resolucion mayor a 1024
	        $longitud_fila=$max_otro;
	      $texto_desc=ajustar_lineas_texto($productos->fields['desc_gral']." ".$desc_adic,$longitud_fila);
	    ?>
	   <textarea name="desc_<?=$items?>" style="width:90%" rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$items?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$items?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
	  </td>
	  <td align="center">
	   <input name="cant_<?=$items?>" type="text" size="6" style='text-align:right' value="<?=$productos->fields['cantidad']?>" onchange="calcular(this)" >
	  </td>
	  <td align="center">
	   <input name="unitario_<?=$items?>" type="text" size="10" style='text-align:right' value="<?=number_format($productos->fields['precio'],2,".","")?>" onchange="this.value=this.value.replace(',','.');calcular(this)">
	   <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
	  </td>
	  <td align="center">
	   <input name="subtotal_<?=$items?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($productos->fields['cantidad']*$productos->fields['precio'],2,".","")?>">
	  </td>
	 </tr>
	 <?
	 $total+=$productos->fields['cantidad']*$productos->fields['precio'];
	 $productos->MoveNext();
	 $items++;
    }//de while (!$productos->EOF)
   }//de if ($licitacion && $productos)
  /***************************************************************
   Fin de Generacion de Filas: LICITACION Y PRESUPUESTO
  ****************************************************************/

  /***************************************************************
   Generacion de Filas: CUANDO SE ABRE UNA ORDEN DESDE EL LISTADO
  ****************************************************************/
  ?>
  <input type="hidden" name="borrar_fila_especial" value="">
  <?
  if ($nro_orden && $filas)
  {
	//si el estado es mayor que autorizada, mostramos, si es necesario el boton de cambio de productos en la fila
	if($estado=='e' ||$estado=='d' || $estado=='g')
	 $filas_cambios_prod=filas_con_cambios_prod($nro_orden);
    while (!$filas->EOF)
	{
     //historial de comentarios
	 $link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$filas->fields['id_producto']));
	 ?>
	 <tr>
	  <td align="center">
	   <input name="chk" type="checkbox" id="chk" value="1" <?=$permiso?>>
	  </td>
	  <input type='hidden' name='idprov_<?=$items?>' value='<?=$filas->fields['prov_prod']?>' >
	  <input type="hidden" name="idf_<?=$items?>" value="<?=$filas->fields['id_fila']?>">
	  <?
	  if($filas->fields['id_producto'])
	  {?>
	   <input type="hidden" name="idp_<?=$items?>" value="<?=$filas->fields['id_producto']?>" <?/*no sacar este permiso!!! ---->*/ echo $permiso?>>
	  <?
	  }
	  else
	  {
	  ?>
	   <input type="hidden" name="id_p_esp_<?=$items?>" value="<?=$filas->fields['id_prod_esp']?>" <?/*no sacar este permiso!!! ---->*/ echo $permiso?>>
	  <?
	  }
	  //En el caso de que la OC este en estado 'a','e','d' o 'g', y se van a modificar las
	  //filas, con permiso especial, si la fila es un agregado, hacemos el control de la
	  //función control_trans(), para que siga controlando que el monto de esa fila
	  //no supere los $30 o el 10% de la compra
	  if(($estado=='a' || $estado=='e' || $estado=='d' || $estado=='g') && $filas->fields["es_agregado"])
	  {$control_especial_transporte="pos_trans=document.all.items.value-1;
	                                 if(control_trans(1))
	                                  document.all.guardar_cambios_fila.disabled=0;
	                                 else
	                                  document.all.guardar_cambios_fila.disabled=1;";
	   $readonly_especial_modif="readonly";
	  }//de if($filas->fields["es_agregado"])
	  else
	  {$control_especial_transporte="";
	   $readonly_especial_modif="";
	  }
	  /*if($es_stock)
	  {?>
	    <input name="proveedores_cantidad_<?=$items?>" type="hidden" value="<?=$proveedor_cantidad?>">
	   <?
	  }*/
	  ?>
	  <td <?=$color_textarea?>>
	   <?
	   $desc_adic=$_POST['h_desc_'.$items] or $desc_adic=$filas->fields['desc_adic'];
	   ?>
	   <input type='hidden' value="<?=$desc_adic?>" name='h_desc_<?=$items?>'>
	   <input type='hidden' value='<?=$filas->fields['descripcion_prod']?>' name='desc_orig_<?=$items?>'>
	    <?
         if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	        $longitud_fila=$max_800_600;
	       elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	        $longitud_fila=$max_1024_768;
	       else//si es una resolucion mayor a 1024
	        $longitud_fila=$max_otro;
	      $texto_desc=ajustar_lineas_texto($filas->fields['descripcion_prod']." ".$desc_adic,$longitud_fila);
	    ?>
       <textarea name="desc_<?=$items?>" style="width:90%" rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" <?=$permiso?> readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$items?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$items?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
       <?
       //Control que revisa si el producto ya esta en alguno de los stock d coradir. En tal caso muestra el icono respectivo
	   /*if(!$es_stock && ($estado=='p' || $estado=='u'))
	   {
	    if(en_stock_coradir($filas->fields['id_producto']))
	    {
	     ?>
	     <img src='<?="$html_root/imagenes/stock.gif"?>' onclick="window.open('en_stock_coradir.php?posicion=<?=$filas->fields['id_producto']?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')"
	      title='Este producto está disponible en el Stock de Coradir' style="cursor:hand">
       <?
	    }//de if(en_stock_coradir($filas->fields['id_producto']))
	   }//de if(!$es_stock && ($estado=='p' || $estado=='u'))
	   else
	    echo "&nbsp;";*/
	   //Control para saber si se hizo un cambio de producto desde la parte de Entrega y recepcion, para esta fila
	   if($filas_cambios_prod[$filas->fields['id_fila']]["id_producto"]!="")
	   {
	    $link_cambio_prod=encode_link("cambios_productos_fila.php",array("id_fila"=>$filas->fields['id_fila'],"permiso_cambio"=>0));
	    $click_cambios_prod="onclick=\"window.open('$link_cambio_prod','','top=130, left=200, width=700px, height=350px, scrollbars=1, status=1,directories=0')\"";
	    if(permisos_check("inicio","permiso_cambiar_producto_fila"))
	    {
	    ?>
	     <img name="hay_cambios" src="../../imagenes/cambios_producto_fila.gif" title="Hay Cambios de Productos en la parte de Recepción/Entrega" <?=$click_cambios_prod?> style="cursor:hand">
	     <?
	    }//de if(permisos_check("inicio","permiso_cambiar_producto_fila"))
	   }//de if($filas_cambios_prod[$filas->fields['id_fila']]["id_producto"]!="")
	   else
	   {$color_textarea="";
	    $click_cambios_prod="";
	   }
	   ?>
      </td>
      <?
      if($internacional)
      {?>
       <td>
        <?generar_posad($items,$filas->fields["id_posad"]);//generamos el select para elegir el POSAD, y mostramos el elegido para cada fila?>
       </td>
       <?
      //recalculamos todos los montos de OC internacional
       $actualizar_internacional="
   		set_montos_fila_oc_internacional() ";
      }
      else
       $actualizar_internacional="";

      ?>
	  <td align="center">
	   <input name="cant_<?=$items?>" type="text" size="6" style='text-align:right' value="<?=$filas->fields['cantidad']?>" <?=$readonly_especial_modif?> onchange="calcular(this);<?=$actualizar_internacional?>;<?=$control_especial_transporte?>;" <?=$permiso?> <?if($es_stock)echo "readonly"?>>
	  </td>
	  <td align="right">
	   <input name="unitario_<?=$items?>" type="text" size="10" style='text-align:right' value="<?=number_format(($filas->fields['precio_unitario'])?$filas->fields['precio_unitario']:0,2,".","")?>" onchange="this.value=this.value.replace(',','.');calcular(this);<?=$actualizar_internacional?>;<?=$control_especial_transporte?>;" <?=$permiso?>>
	   <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
	  </td>
	  <td>
	   <table width="100%">
	    <tr>
	     <td align="right">
	      <input name="subtotal_<?=$items?>" type="text" size="10" readonly style='text-align:right' value="<?=number_format($filas->fields['cantidad']*$filas->fields['precio_unitario'],2,".","")?>" <?=$permiso_read?>>
	     </td>
          <?
          //mostramos boton (invisible) para borrar filas, solo si tiene permiso
          //y la oc esta en estado 'a', 'e', 'd' o 'g'
          if(($estado=='a' || $estado=='e' || $estado=='d' || $estado=='g') && permisos_check("inicio","permiso_borrar_fila_especial"))
	       {
	       	$desc_adic=ereg_replace("\r\n"," ",cortar($desc_adic,40));
	       	?>
	        <td>
		     <input type="submit" name="borrar_fila_<?=$items?>" value="Del" style="visibility:hidden" class="little_boton" onclick="document.all.borrar_fila_especial.value='<?=$filas->fields['id_fila']?>';return confirm('Se va a borrar la fila con el producto \n\'<?=$filas->fields['descripcion_prod']." ".$desc_adic?>\'\n ¿Está seguro que desea continuar?')">
		    </td>
		   <?
	       }
	       ?>

		 </tr>
		</table>
	  </td>
	  <?
	  //generamos los datos adicionales propios de las OC internacionales
	  if($internacional)
	  {
	   $montos_internacionales["proporcional_flete"]=$filas->fields["proporcional_flete"];
	   $montos_internacionales["base_imponible_cif"]=$filas->fields["base_imponible_cif"];
	   $montos_internacionales["derechos"]=$filas->fields["derechos"];
	   $montos_internacionales["iva"]=$filas->fields["iva"];
	   $montos_internacionales["ib"]=$filas->fields["ib"];
	   generar_datos_fila_oc_internacional($items,$montos_internacionales);
	  }
	  ?>
	 </tr>
	 <?
	 $total+=$filas->fields['cantidad']*$filas->fields['precio_unitario'];
	 $filas->MoveNext();
	 $items++;
	}//de while (!$filas->EOF)
   }//de if ($nro_orden && $filas)
  /***************************************************************
   Fin de Generacion de Filas: CUANDO SE ABRE UNA ORDEN DESDE
                               EL LISTADO
  ****************************************************************/

  /***************************************************************
   Generacion de Filas: CUANDO SE HACE UNA ORDEN DESDE
                        SEGUIMIENTO PRODUCCION PARA UN PRESUPUESTO
                        DE PROVEEDOR DETERMINADO
  ****************************************************************/
  if ($id_renglon_prop && $aprod_seg['cantidad'])
  {
	for ($i=0; $i < $aprod_seg['cantidad']; $i++)
	{
	?>
	<tr>
	 <td align="center">
	  <input name="chk" type="checkbox" id="chk" value="1" <?=$permiso?>>
	 </td>
	 <input type="hidden" name="idp_<?=$items?>" value="<?=$aprod_seg[$i]['id_producto']?>" >
     <?
     if($aprod_seg[$i]['prov_cant'])
	 {
     ?>
	  <input name="proveedores_cantidad_<?=$items?>" type="hidden" value="<?=$aprod_seg[$i]['prov_cant']?>">
     <?
	 }
	 ?>
	 <td>
	  <input type='hidden' value='<?=$aprod_seg[$i]['desc_adic']?>' name='h_desc_<?=$items?>'>
	  <input type='hidden' value='<?=$aprod_seg[$i]['desc_orig']?>' name='desc_orig_<?=$items?>'>
	  <input type='hidden' value='<?=$aprod_seg[$i]['id_producto_presupuesto']?>' name='id_prod_pres_<?=$items?>'>
	  <input type='hidden' value='<?=$aprod_seg[$i]['cantidades_pres']?>' name='hcantidades_pres_<?=$items?>'>
	  <?
	  if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	   $longitud_fila=$max_800_600;
	  elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	   $longitud_fila=$max_1024_768;
	  else//si es una resolucion mayor a 1024
	   $longitud_fila=$max_otro;
	  $texto_desc=ajustar_lineas_texto($aprod_seg[$i]['desc'],$longitud_fila);
	  ?>
   	  <textarea wrap name="desc_<?=$items?>" style="width:90%"  rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" <?=$permiso?> readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$items?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$items?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
	 </td>
	 <!-- la cantidad no se puede modificar, sacar el readonly para que se pueda -->
	 <td align="center">
	  <input name="cant_<?=$items?>" readonly type="text" size="6" style='text-align:right' value="<?=$aprod_seg[$i]['cantidad_comprar']?>" onchange="calcular(this)" <?=$permiso?>>
	 </td>
	 <td align="center">
	  <input name="unitario_<?=$items?>"  type="text" size="10" style='text-align:right' value="<?=number_format(($aprod_seg[$i]['unitario'])?$aprod_seg[$i]['unitario']:0,2,".","")?>" onchange="this.value=this.value.replace(',','.');calcular(this)" <?=$permiso?>>
	  <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
	 </td>
	 <td align="center">
	  <input name="subtotal_<?=$items?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($aprod_seg[$i]['cantidad_comprar']*$aprod_seg[$i]['unitario'],2,".","")?>" <?=$permiso?>>
	 </td>
	</tr>
	<?
	$total+=$aprod_seg[$i]['cantidad_comprar']*$aprod_seg[$i]['unitario'];
	$items++;
   }//de for ($i=0; $i < $aprod_seg['cantidad']; $i++)
  }//de if ($id_renglon_prop && $aprod_seg['cantidad'])
 /***************************************************************
  Fin de Generacion de Filas: CUANDO SE HACE UNA ORDEN DESDE
                       SEGUIMIENTO PRODUCCION PARA UN PRESUPUESTO
                        DE PROVEEDOR DETERMINADO
 ****************************************************************/
 }//de  if(!$refresh)

 /***************************************************************
  Generacion de Filas: CUANDO LA ORDEN ES NUEVA
                       Y/O SE RECARGA LA PAGINA
 ***************************************************************/
 else
 {
   $items=get_items();
   //Si se eligio un proveedor nuevo, que es stock, se deben borrar los items
   //que ya tenia la orden de compra, para que vuelva a elegirlos del stock correspondiente
   if($_POST['borrar_filas_stock']==1 || ($items[0]["proveedores_cantidad"]!="null" && $_POST['es_stock']==1))
    unset($items);
   for($i=0; $i < $items['cantidad']; $i++)
   {
	//historial de comentarios
	$link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$items[$i]['id_producto']));
    ?>
    <tr>
     <td align="center">
      <input name="chk" type="checkbox" id="chk" value="1">
     </td>
     <?
     if ($items[$i]['id_fila'])
	 {
     ?>
      <input type="hidden" name="idf_<?=$i?>" value="<?=$items[$i]['id_fila']?>">
     <?
	 }

	 if($items[$i]['id_producto'])
	 {?>
      <input type="hidden" name="idp_<?=$i?>" value="<?=$items[$i]['id_producto']?>">
     <?
	 }
	 else
	 {
	  ?>
      <input type="hidden" name="id_p_esp_<?=$i?>" value="<?=$items[$i]['id_prod_esp']?>">
     <?
	 }

     //si es de gastos de servicio tecnico
     if ($gastos_servicio_tecnico)
     {
     ?>
      <input type=hidden name="nro_caso_<?=$i?>" value="<?=$_POST["nro_caso_$i"]?>">
     <?
     }//del idate
     ?>
     <td>
      <?$desc_adic=$_POST['h_desc_'.$i];?>
      <input type='hidden' value='<?=$desc_adic?>' name='h_desc_<?=$i?>'>
      <input type='hidden' value='<?=$items[$i]['descripcion_prod']?>' name='desc_orig_<?=$i?>'>
      <?
       if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	    $longitud_fila=$max_800_600;
	   elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	    $longitud_fila=$max_1024_768;
	   else//si es una resolucion mayor a 1024
	    $longitud_fila=$max_otro;
	   $texto_desc=ajustar_lineas_texto($items[$i]['descripcion_prod']." ".$desc_adic,$longitud_fila);
	  ?>
      <textarea name="desc_<?=$i?>" style="width:90%" rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$i?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$i?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
     </td>
     <input type='hidden' value='<?=$items[$i]['prov_prod']?>' name='idprov_<?=$i?>"'>
     <?
     if($internacional)
     {?>
      <td>
       <?generar_posad($i,$items[$i]["id_posad"]);//generamos el select para elegir el POSAD, y mostramos el elegido para cada fila?>
      </td>
      <?
      //recalculamos todos los montos totales de OC internacional
      $actualizar_internacional="set_montos_fila_oc_internacional()";
     }
     else
      $actualizar_internacional="";
      ?>
     <td align="center">
      <input name="cant_<?=$i?>" type="text" size="6" style='text-align:right' value="<?=$items[$i]['cantidad']?>" onchange="calcular(this);<?=$actualizar_internacional?>;" <?if($es_stock)echo "readonly"?>>
     </td>
     <td align="right">
      <input name="unitario_<?=$i?>" type="text" size="10" style='text-align:right' value="<?=number_format($items[$i]['precio_unitario'],2,".","")?>" onchange="calcular(this);<?=$actualizar_internacional?>;">
      <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
     </td>
     <td align="right">
       <input name="subtotal_<?=$i?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($items[$i]['cantidad']*$items[$i]['precio_unitario'],2,".","")?>">
     </td>
    <?
	//generamos los datos adicionales propios de las OC internacionales
	if($internacional)
	{
	   $montos_internacionales["proporcional_flete"]=$items[$i]["proporcional_flete"];
	   $montos_internacionales["base_imponible_cif"]=$items[$i]["base_imponible_cif"];
	   $montos_internacionales["derechos"]=$items[$i]["derechos"];
	   $montos_internacionales["iva"]=$items[$i]["iva"];
	   $montos_internacionales["ib"]=$items[$i]["ib"];
	   generar_datos_fila_oc_internacional($i,$montos_internacionales);
	}
	?>

    </tr>
    <?
	$total+=$items[$i]['cantidad']*$items[$i]['precio_unitario'];
   }//de for($i=0; $i < $items['cantidad']; $i++)
   $items=$i;
  }//del else de if(!$refresh)
  /***************************************************************
   Fin de Generacion de Filas: CUANDO LA ORDEN ES NUEVA
                               Y/O SE RECARGA LA PAGINA
  ***************************************************************/
?>
  <?
  if($internacional)
  {
   $color_total="#B4E3A8";
   ?>
   <tr>
    <td colspan="4">
     &nbsp;
    </td>
    <td  bgcolor="<?=$color_total?>">
      <b>Totales <? if ($orden->fields['id_moneda']==1) echo "<font color='#FF3300'>".'$'."</font>";elseif($orden->fields['id_moneda']==2) echo "<font color='#FF3300'>".'U$S'."</font>"?></b>
    </td>
    <td bgcolor="<?=$color_total?>">
      <input type="text" name="total" size="12" style="text-align:right;background-color:'#DDFFDD';font-weight:'bold';" readonly value="<?=number_format($total,2,".","");?>" >
    </td>
    <td align="center" bgcolor="<?=$color_total?>">
     <input type="text" name="total_proporcional_flete" style='text-align:right' readonly value="" size="8">
    </td>
    <td align="center" bgcolor="<?=$color_total?>">
     <input type="text" name="total_base_imponible_cif" style='text-align:right' readonly value="" size="8">
    </td>
    <td align="center" bgcolor="<?=$color_total?>">
     <input type="text" name="total_derechos" style='text-align:right' readonly value="" size="8">
    </td>
    <td align="center" bgcolor="<?=$color_total?>">
     <input type="text" name="total_iva" style='text-align:right' readonly value="" size="8">
    </td>
    <td align="center" bgcolor="<?=$color_total?>">
     <input type="text" name="total_ib" style='text-align:right' readonly value="" size="8">
    </td>
   </tr>
   <?
  }?>
  </table>

  <table width="100%">
  <tr>
  <?
  //si el estado es autorizada o enviada o parcialmente pagada
  //mostramos el boton de agregar transporte

  //solo si no se ha presionado previamente,
  //y si el usuario tiene permiso para ver el boton
  $permiso_agregar=permisos_check("inicio","permiso_agregar_transporte");

  if($permiso_agregar && (!$es_stock) && ($estado=='a' ||$estado=='e' || $estado=='d') && $transporte_agregado==0 && !$internacional)
  {?>
   <td width="20%">
    <input type="button" name="agregar_transporte" value="Agregar Conexo" style="width:60%" onclick="agregar_trans()">
    <input type="submit" name="guardar_transporte" value="Guardar" style="visibility:hidden" onclick="return control_trans()">
   </td>
  <?
  }
  else
  {
  echo "<td width='20%'>&nbsp;</td>";
  }
  ?>

   <?
   if($style_contado!="")
   {
    echo "<td id='fila_contado2' width='40%'>";
    echo "$cartel_contado&nbsp;&nbsp;&nbsp$cartel_contado&nbsp;&nbsp;&nbsp;$cartel_contado";
    echo "</td>";
   }
   else
    echo "<td id='fila_contado2' width='40%'>&nbsp;</td>";
   ?>
    <td align="right" width="40%">
     <?//mostramos el boton de guardar los cambios hechos a las filas
     //(solo si la OC esta en estado 'a','e','d' o 'g')
     if(($estado=='a' || $estado=='e' || $estado=='d' || $estado=='g') && permisos_check("inicio","permiso_borrar_fila_especial"))
     {?>
      <input type="submit" name="guardar_cambios_fila" value="Guardar Modificaciones a Filas" style="width=170;visibility='hidden'" class="little_boton" onclick="return confirm('¿Está seguro que desea guardar los cambios hechos a las filas de esta Orden de Compra?')">
     <?
     }
     if(!$internacional)
     {
      ?>
      <b>Total Final <? if ($orden->fields['id_moneda']==1) echo "<font color='#FF3300'>".'$'."</font>";elseif($orden->fields['id_moneda']==2) echo "<font color='#FF3300'>".'U$S'."</font>"?></b>
      <input type="text" name="total" size="12" style="text-align:right;background-color:'#DDFFDD';font-weight:'bold';" readonly value="<?=number_format($total,2,".","");?>" >
      <?
     }//de if(!$internacional)
     ?>
    </td>
   </tr>
 <?if($internacional) //si la OC es internacional mostramos el detalle de los montos de la misma
  {
 ?>
   <tr>
    <td colspan="6">
     <hr>
     <input type="checkbox" name="desplegar_totales" value="1" onclick="if(this.checked==1)detalle_monto_total.style.display='block';else detalle_monto_total.style.display='none';">
     Mostrar Detalle de Monto Global
     <table border="1" class="bordes">
      <tr>
       <td colspan="2">
        <div id="detalle_monto_total" style="display:none">
         <table class="bordes">
          <tr>
           <td>
            <b>Total FOB</b>
           </td>
           <td>
            <input type="text" name="total_fob_final" value="" readonly style="text-align:right;">
           </td>
          </tr>
          <tr>
	       <td>
	        <b>Flete</b>
	       </td>
	       <td>
	        <input type="text" name="total_flete_final" value="" readonly style="text-align:right;">
	       </td>
	      </tr>
	      <tr>
	       <td>
	        <b>Total I.V.A./Ganancias</b>
	       </td>
	       <td>
	        <input type="text" name="total_iva_ganancias_final" value="" readonly style="text-align:right;">
	       </td>
	      </tr>
	      <tr>
	       <td>
	        <b>Total I.B.</b>
	       </td>
	       <td>
	        <input type="text" name="total_ib_final" value="" readonly style="text-align:right;">
	       </td>
	      </tr>
	      <tr>
	       <td>
	        <b>Total Derechos</b>
	       </td>
	       <td>
	        <input type="text" name="total_derechos_final" value="" readonly style="text-align:right;">
	       </td>
	      </tr>
	      <tr>
	       <td>
	        <b>Honorarios y Gastos</b>
	       </td>
	       <td>
	        <input type="text" name="total_honorarios_final" value="" readonly style="text-align:right;">
	       </td>
	      </tr>
	     </table>
	    </div>
	   </td>
	  </tr>
	  <tr>
       <td colspan="2">
        &nbsp;
       </td>
      </tr>
      <tr>
       <td>
        <b>TOTAL GLOBAL</b>
       </td>
       <td align="right">
        <input type="text" name="total_global" value="" readonly style="text-align:right;">
       </td>
      </tr>
     </table>
    </td>
   </tr>
  <?
   }//de if($internacional)
  ?>
  </table>
  </td>
 </tr>
</table>
<?
/*************************************************************************************************************
 FIN DE SECCION: LISTA DE PRODUCTOS DE LA OC
**************************************************************************************************************/

/*************************************************************************************************************
 SECCION: CONTROL INTERNO PROVEEDORES
**************************************************************************************************************/
//Si la OC no es Orden de Servicio Tecnico, no se muestra el control interno de proveedores
if($modo!="oc_serv_tec")
{
 if(permisos_check("inicio","control_compras_proveedores"))
 {
  switch ($orden->fields['estado_proveedor'])
  {
    case 1:$color = "#aaaacc";$texto = "Atrasada por el Proveedor";$select_estado_prov="checked";break;
    case 2:$color = "pink";$texto = "Llamar mas tarde";$select_estado_tarde="checked";break;
    case 3:$color = "yellow";$texto = "Atrasada por CORADIR";$select_estado_coradir="checked";break;
    default:$color = "white";$texto = "Nada";$select_estado_nada="checked";break;
  }//de switch ($orden->fields['estado_proveedor'])
  ?>
  <hr>
  <font color="Blue">Solo para Graciela Tedeschi...</font>
  <input type="hidden" name="cambio_estado_proveedor" value="">
  <table width="95%" align="center">
   <tr>
    <td width="100%" align="center">
     <table>
      <tr>
       <td width="1%">
        &nbsp;<input type="radio" class="estilos_check" name="estado_proveedor" <?=$select_estado_nada?> value="Nada" style="cursor:hand" <?=$anulada?> onclick="if(confirm('¿Está seguro que desea cambiar el estado del proveedor a: \'Nada\'?')){document.all.cambio_estado_proveedor.value=1;document.form1.submit();}">
       </td>
       <td bgcolor="White" width="20%"><b>Nada</b></td>
       <td width="1%">
        &nbsp;<input type="radio" class="estilos_check" name="estado_proveedor" <?=$select_estado_prov?> value="Atrasada por el Proveedor" style="cursor:hand" <?=$anulada?> onclick="if(confirm('¿Está seguro que desea cambiar el estado del proveedor a: \'Atrasada por el Proveedor\'?')){document.all.cambio_estado_proveedor.value=1;document.form1.submit();}">
       </td>
       <td bgcolor="#aaaacc" width="20%"><b>Atrasada por el Proveedor</b></td>
       <td width="1%">
        &nbsp;<input type="radio" class="estilos_check" name="estado_proveedor" <?=$select_estado_coradir?> value="Atrasada por CORADIR" style="cursor:hand" <?=$anulada?> onclick="if(confirm('¿Está seguro que desea cambiar el estado del proveedor a: \'Atrasada por CORADIR\'?')){document.all.cambio_estado_proveedor.value=1;document.form1.submit();}">
       </td>
       <td bgcolor="yellow" width="20%"><b>Atrasada por CORADIR</b></td>
       <td width="1%">
        &nbsp;<input type="radio" class="estilos_check" name="estado_proveedor" <?=$select_estado_tarde?> value="Llamar mas tarde" style="cursor:hand" <?=$anulada?> onclick="if(confirm('¿Está seguro que desea cambiar el estado del proveedor a: \'Llamar más Tarde\'?')){document.all.cambio_estado_proveedor.value=1;document.form1.submit();}">
       </td>
       <td bgcolor="pink" width="20%"><b>Llamar más Tarde</b></td>
      </tr>
     </table>
    </td>
   </tr>
   <tr>
    <td align="center">
     <table>
      <tr>
       <td align="right">
        <b>Estado Actual
       </td>
       <td bgcolor="<?=$color;?>" width="40%">
        <b><?=$texto?></b>
       </td>
      </tr>
     </table>
    </td>
   </tr>
  </table>
  <?
 }//de if(permisos_check("inicio","control_compras_proveedores"))
}//de if($modo!="oc_serv_tec")
/*************************************************************************************************************
 FIN DE SECCION: CONTROL INTERNO PROVEEDORES
**************************************************************************************************************/
?>
</div><!--div_formulario-->
<?
/*************************************************************************************************************
 SECCION: BOTONERA
**************************************************************************************************************/
?>
<div style="background-color:<?=$bgcolor_out?>;height:78px;position:relative" id="div_botonera">
<table width="100%" id=history class="bordessininferior">
 <tr>
  <td width="37.5%">
   Estado: <?=$estado_nombre?>
  </td>
  <td width="25%">
   <?
   if($nro_orden!=-1)
   {?>
    <?=$titulo_pagina?> Nº <?=$nro_orden?>
   <?
   }
   else
   {
   	echo "Nueva $titulo_pagina";
   }?>
  </td>
  <td width="37.5%">
   <?=$tipo_asociacion_oc?>
  </td>
 </tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="1" class="bordessininferior">
 <tr>
  <td width="4%" align="center">&nbsp;</td>
  <!-- El boton aa es solo para anular el enter-->
  <td width="100%" align="center">
   <input type="hidden" name="comentario_anular" value="">
   <input type="hidden" name="h_anular" value="">
   <input type="hidden" name="comentario_rechazar" value="">
   <input type="hidden" name="h_rechazar" value="">
   <input type="submit" name="aa"  value="" onclick="return false;" style="width:0px">
   <?
   //dependiendo del $modo, el boton volver vuelve al listado correpondiente
   switch ($modo) {
   	case "oc_serv_tec":$volver="listado_oc_serv_tec.php";break;
   	default:$volver="ord_compra_listar.php";break;
   }
   ?>
   <input type="button" name="volver" class="estilos_boton" value="Volver" onclick="document.location='<?=$volver?>'">
   <?
   //El boton de anular OC no se muestra si el modo es Ordenes de Servicio Tecnico
   if(permisos_check("inicio","permiso_boton_anular_oc_pagadas") && $estado=='g' && ($orden_prod || $caso) && $es_stock && $modo!="oc_serv_tec")
   {?>
    <input type="submit" class="estilos_boton" name="anular_con_stock" disabled title="NO IMPLEMENTADO" value="Anular OC" onclick="if(confirm('Si anula esta <?=$titulo_pagina?>, todas las entregas/recepciones realizadas se volverán para atrás.\n¿Está seguro que desea anularla?'))return true; else return false;">
   <?
   }
   ?>
   <input name="boton_anular" <?/*boton 0*/?> type="button" class="estilos_boton" value="Anular" <?= $permisos_b['anular']?> <?=$disabled_recib?>
    onclick="
    //si confirma la anulacion de la orden debe justificar el porque
    if (confirm('¿Está seguro que desea anular la Orden?'))
     window.open('<?=encode_link("comentario_anulacion.php",array("nro_orden"=>$nro_orden,"tipo"=>"anular","titulo_pagina"=>$titulo_pagina))?>','','toolbar=1,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300');"
   >
   &nbsp;
   <?
   //Si la OC es internacional, al eliminar filas, debemos recalcular los montos de las filas que queden, si queda alguna
   if($internacional)
   //recalculamos todos los montos totales de OC internacional
      $actualizar_internacional="set_montos_fila_oc_internacional();";
   else
      $actualizar_internacional="";
   ?>
   <input name="boton_eliminar" <?/*boton 1*/?> type="button" class="estilos_boton" value="Eliminar" <? if (!$items) echo " disabled "; else echo $permisos_b['eliminar'];?>
    onclick="
     if (confirm('¿Está seguro que desea eliminar los items seleccionados ?'))
     {
      borrar_items();
      <?=$actualizar_internacional?>
     }
	"
   >
   <?
   //Si la orden es de Servicio Tecnico entonces no se deshabilita el boton de Agregar
   if (($gastos_servicio_tecnico || $flag_honorario)&&!$es_stock)
      $disabled_agregar="disabled";
   else
    $disabled_agregar="";

   if(permisos_check("inicio","oc_para_autorizar"))
     $disabled_para_autorizar_oc="";
   else if(permisos_check("inicio","permiso_boton_para_autorizar_oc_ser_tec")&&$modo=="oc_serv_tec")
    $disabled_para_autorizar_oc="";
   else
     $disabled_para_autorizar_oc="disabled";
   ?>
   &nbsp;<input name="boton_agregar" <?/*boton 2*/?> type="button" class="estilos_boton" value="Agregar" <?=$permisos_b['agregar']?>  <?= ($select_proveedor && $select_proveedor!=-1)?"":"disabled"?> <?=$disabled_agregar?> onclick="nuevo_item(links_stock)">
   &nbsp;<input name="boton_guardar" <?/*boton 3*/?> type="submit" class="estilos_boton" value="Guardar" <?=$permisos_b['guardar']?> onclick="if (chk_campos()) { alert (msg); return false;}">
   <?$link_calif=encode_link("../calidad/califique_proveedor.php",array("proveedor"=>"$select_proveedor","desde"=>"1"));
   ///////////////////////////////////////////////////////////////////////
   $poner_calif=0;
   if($estado=="p")
   {
    $q="select * from general.calificacion_proveedor where fecha is not null and fecha>(current_date - 7)
                                                           and id_proveedor=$select_proveedor";
    $re=sql($q) or fin_pagina("error al buscar en la tabla $q");
    $poner_calif=$re->RecordCount();
   }//de if($estado=="p")
   /////////////////////////////////////////////////////////////////////////

   //Control de fecha para autorizar
   if ($modo !="oc_serv_tec" && compara_fechas(fecha_db($fecha_entrega),date("Y-m-d"))==0)
    $confirmar = "if (confirm('Esta orden de compra tiene fecha de entrega hoy mismo,esto puede generar incovenientes en el area de logistica. Por favor cambie de estado a para autorizar solo en caso de urgencia de lo contrario modifique la fecha de entrega ¿Esta seguro que desea cambiar el estado a para autorizar?')){";
   else
    $confirmar = "";

  ?>
  &nbsp; <input name="boton_para_autorizar" <?/*boton 4*/?> type="submit" class="estilos_boton" value="Para Autorizar" <?=$permisos_b['por_autorizar']?> <?=$disabled_para_autorizar_oc?> style="width:90px"
          <?if($usuario_creacion==$_ses_user["name"]) echo "disabled"?>
  		  onclick="
           <?=$confirmar;?>
           document.all.guardar.value=1;
           if (chk_campos())
           {alert (msg);
            return false;
           }
           else
           {
        	<? if (!$es_stock && $poner_calif==0)
        	    echo"window.open('$link_calif','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=230,top=80,width=500,height=400');";
        	?>
        	return true;
           }
           <?=($confirmar!="")?"}else return false;":"";?>
          "
        >
        <input type="hidden" name="contenido" value="">
        <?
         //Si es Orden de Servicio Tecnico no se muestran los botones de pagar y recepcion/entrega
         if($modo!="oc_serv_tec")
         {
          $link_recepciones=encode_link("ord_compra_recepcion.php",array('nro_orden'=>$nro_orden,'es_stock'=>$es_stock,'mostrar_dolar'=>$mostrar_dolar,"tipo_lic"=>$tipo_lic_text));
        ?>
          &nbsp;<input name="boton_pagar" <?/*boton 5*/?> type="button" class="estilos_boton" value="Pagar" <?=$disabled_por_stock?> style="width:65px" <?=$permisos_b['pagar']?>
               onclick="
                if (wpagar==0 || wpagar.closed)
	             wpagar=window.open('<?=encode_link('ord_compra_pagar.php',array('nro_orden'=>$nro_orden))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=0,top=0,width=900,height=600');
                else if (!wpagar.closed)
	              wpagar.focus();
               "
	          >

            &nbsp;<input name="boton_recepcion" <?/*boton 6*/?> type="button" class="estilos_boton" style="width:120px" title="Recepción de los productos de la Orden de Compra" value="Recepciones" <?=$permisos_b['terminar']?> onclick="document.location.href='<?=$link_recepciones?>'">
<?
         }//de if($modo!="oc_serv_tec")
         ?>
  </td>
  <td width="23%" align="center">&nbsp;</td>
 </tr>
</table>
<?
if ($nro_orden && $nro_orden!=-1)
{
?>
 <table width="100%" class="bordessinsuperior">
  <tr>
   <td width="30%">&nbsp;</td>
   <td width="25%" align="center" nowrap>
   <?
	if (!permisos_check("inicio","rechazar_ord_compra"))
		$rechazar="disabled";

    //este control es para que solo pueda rechazar el que
    //hizo la orden de compra

	$sql="select log_ordenes.user_login from compras.log_ordenes where log_ordenes.nro_orden=$nro_orden
		 and log_ordenes.tipo_log='de creacion'";
	$result=sql($sql,"<br>Error al traer el usuario que creo la orden de compra<br>") or fin_pagina();
	$login=$result->fields["user_login"];
    if($_ses_user["login"]!=$login)
	 $rechazar_usuario_creo="disabled";

    if($permisos_b['rechazar']=="disabled" || $permisos_b['rechazar']==" disabled ")
	 $disabled_rechazar="disabled";
	else
	{
	  if(($rechazar=="disabled")&&($rechazar_usuario_creo=="disabled"))
		$disabled_rechazar="disabled";
	  else
	    $disabled_rechazar="";
    }
    //si la orden no tiene ningun pago realizado, y no es Orden de Servicio Tecnico, mostramos el
    //boton de habilitar pagos especiales
    if ($modo!="oc_serv_tec" && ($orden->fields['estado']!='g')&&$orden->fields['habilitar_pago_especial']==0 && (permisos_check("inicio","boton_habilitar_pago_especial")))
    {?>
     <input name="boton_habilitar_pago_especial" <?/*boton 7*/?> type="submit" class="estilos_boton"  value="Habilitar Pago Especial" <?=$disabled_por_stock?> <?=$permiso_habilitar?> <?=$anulada?> style="width:170px">
    <?
    }
    elseif ($modo!="oc_serv_tec" && ($orden->fields['estado']!='d')&& ($orden->fields['estado']!='g')&&permisos_check("inicio","boton_habilitar_pago_especial"))
    {?>
     <input name="boton_deshabilitar_pago_especial" <?/*boton 8*/?> type="submit" class="estilos_boton"  value="Deshabilitar Pago Especial"  style="width:170px">
    <?
    }

    //El boton de Resumen de Pago no se muestra si estamos en modo Servicio Tecnico
    if ($modo!="oc_serv_tec" && ($orden->fields['estado']=='d')||($orden->fields['estado']=='g')||($orden->fields['estado']=='e')||($orden->fields['estado']=='t'))
    {
     if ($tipo_moneda!='Dólares') $tipo_moneda="";
      $link=encode_link("./ord_compra_resumen_pagos.php",array("nro_orden"=>$nro_orden,"moneda"=>$tipo_moneda,"pagina"=>"ord_compra"));
     //si estamos en modo Ordenes de Servicio Tecnico
     if($modo!="oc_serv_tec")
     {?>
      <input name="boton_resumen_pagos" <?/*boton 9*/?> type="button" class="estilos_boton"  value="Resumen de Pagos"  style=";width:100" <?=$disabled_por_stock?> onclick="window.open('<?=$link;?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=0,width=800,height=500');">
    <?
     }
    }//de  if (($orden->fields['estado']=='d')||($orden->fields['estado']=='g')||($orden->fields['estado']=='e')||($orden->fields['estado']=='t'))

    //Si es una Orden de Compra comun o es una Orden de Servicio Tecnico, pero tiene permiso de rechazar, se muestra el boton
    if($modo!="oc_serv_tec" || ($modo=="oc_serv_tec" && $disabled_rechazar==""))
    {
    ?>
     <input name="boton_rechazar" <?/*boton 10*/?> type="button" class="estilos_boton"  value="Rechazar" <?=$disabled_rechazar?> <?=$disabled_recib?> style="width:70px"
      onClick="
        //si confirma el rechazo de la orden debe justificar el porque
        if (confirm('¿Está seguro que desea rechazar la Orden?'))
         window.open('<?=encode_link("comentario_anulacion.php",array("nro_orden"=>$nro_orden,"tipo"=>"rechazar","titulo_pagina"=>$titulo_pagina))?>','','toolbar=1,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300');
       "
     >
    <?
    }//de if($modo!="oc_serv_tec" || ($modo!="oc_serv_tec" && $disabled_rechazar==""))

    //chequeamos si el usuario que se ha logueado, tiene permiso
    //para ver el boton de autorizar o no. Si tiene lo mostramos, sino ,no.
    if (!permisos_check("inicio","aut_ord_compra"))
      $autorizar=" disabled ";

    //Control de fecha de entrega y dia actual
    if ($modo !="oc_serv_tec" && compara_fechas(fecha_db($fecha_entrega),date("Y-m-d"))==0)
    {$confirmar = '
        if (confirm("Esta orden de compra tiene fecha de entrega hoy mismo, esto puede generar incovenientes en el area de logistica. Por favor cambie de estado a autorizada solo en caso de urgencia, de lo contrario modifique la fecha de entrega. ¿Está seguro que desea autorizar?"))
        {';
    }
    else
     $confirmar = "";

    //Si es una Orden de Compra comun o es una Orden de Servicio Tecnico, pero tiene permiso de autorizar, se muestra el boton
    if($modo!="oc_serv_tec" || ($modo=="oc_serv_tec" && $autorizar==""))
    {
    ?>
	&nbsp;<input name="boton_autorizar" <?/*boton 10*/?> type="submit" class="estilos_boton"  <?= $autorizar?> <?=$permisos_b['autorizar']?> value="Autorizar" <?=$permiso?> style="width:70px"
           onclick='
            <?=$confirmar?>
             document.all.guardar.value=1;
             if (chk_campos())
             {
              alert (msg);
              return false;
             }
             else if(document.all.modo.value!="oc_serv_tec")
              return control_pagos();
            <?if($confirmar!="")
               echo "}else return false;";
              else
               echo""
            ?>
           '
          >
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
         &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <?
    }//de if($modo!="oc_serv_tec" || ($modo!="oc_serv_tec" && $autorizar==""))

    //Las consultas de seguimientos y los botones de ver segumiento y materiales, se muestran solo si al OC
    //no esta en modo Servicio Tecnico
    if($modo!="oc_serv_tec")
    {
       /********************   Busco seguimiento si existe   ******************/
       $sql = "select subido_lic_oc.id_entrega_estimada,subido_lic_oc.id_licitacion,subido_lic_oc.nro_orden,
       		   subido_lic_oc.id_subir,entidad.nombre,subido_lic_oc.vence_oc
       		   from licitaciones.subido_lic_oc join licitaciones.licitacion using(id_licitacion)
       		   join licitaciones.entidad using(id_entidad) join compras.orden_de_compra using(id_licitacion)
       		   where orden_de_compra.nro_orden = $nro_orden";
       $result_seguimiento = sql($sql,"<br>Error al traer el seguimiento de produccion asociado a esta OC<br>") or fin_pagina();

       if ($result_seguimiento->RecordCount()>0)
       {
         $sql = "select entrega_estimada.nro
         		from licitaciones.entrega_estimada
         		where entrega_estimada.id_entrega_estimada = ".$result_seguimiento->fields['id_entrega_estimada'];
         $result_nro_seguimiento = sql($sql,"<br>Error al traer el nro de la entrega estimada<br>") or fin_pagina();

         $link=encode_link("../ordprod/ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$result_seguimiento->fields['id_licitacion'], "id_entrega_estimada"=>$result_seguimiento->fields['id_entrega_estimada'], "nro_orden"=>"","nro"=>$result_nro_seguimiento->fields['nro'],"id_subir"=>$result_seguimiento->fields['id_subir'],"nro_orden_cliente"=>$result_seguimiento->fields['nro_orden']));
         ?>
         <input type="button" class="estilos_boton" name="ver_seguimiento" value="Ver seguimiento" style="cursor:hand;width:100" onclick="window.open('<?=$link?>','','')">&nbsp;
         <?
       }//de if ($result_seguimiento->RecordCount()>0)
       /********************   Busco entrega si existe   ******************/

       if ($licitacion!="")
       {
 	    $link_materiales=encode_link("../ordprod/seguimiento_orden_materiales_pm.php",array("id_licitacion"=>$licitacion,"mostrar_pedidos"=>1,"oc"=>$nro_orden));
        ?>
        <input type="button" class="estilos_boton" name="ver_entrega" value="Materiales" style="cursor:hand;width:70" onclick="window.open('<?=$link_materiales?>','','')">
        <?
       }
    }//de if($modo!="oc_serv_tec")
       ?>
   </td>
   <td width="40%">&nbsp;</td>
  </tr>
  <?
  //if(permisos_check("inicio","botones_entrega_produccion"))
  //{
  ?>
     <tr>
      <td align="right" colspan="4">

  </td>
 </tr>
  <?
 //}//de if(permisos_check("inicio","botones_entrega_produccion"))
 ?>
</table>
</div><!--div_botonera-->
<?
/*************************************************************************************************************
 FIN DE SECCION: BOTONERA
**************************************************************************************************************/

/*************************************************************************************************************
 SECCION: FIN DE PAGINA
**************************************************************************************************************/

}//de if ($nro_orden && $nro_orden!=-1)
else
{?>
 <table width="100%" class="bordessinsuperior">
  <tr>
   <td>
    &nbsp;
   </td>
  </tr>
 </table>
<?
}
?>

 <input type="hidden" name="items" value="<?=$items?>">
 <input type="hidden" name="guardar" value="0">
 <input type="hidden" name="refresh" value="0">
 <input type="hidden" name="nro_orden" value="<?=($nro_orden && $nro_orden!=-1)?$nro_orden:-1?>">
 <input type="hidden" name="id_cliente" value="0">
 <!--
 <input type="hidden" name="id_entidad" value="<?if ($_POST['id_entidad']) echo $_POST['id_entidad'];elseif ($orden->fields['id_entidad'] !='NULL') echo $orden->fields['id_entidad'];else echo 0;?>">
 -->
 <?if($_POST['id_entidad'])
    $id_entidad=$_POST['id_entidad'];
   elseif ($orden->fields['id_entidad']!="" && $orden->fields['id_entidad'] !='NULL')
    $id_entidad=$orden->fields['id_entidad'];
 //else $id_entidad=0;
 ?>
 <input type="hidden" name="id_entidad" value="<?=$id_entidad?>">
 <input type="hidden" name="id_licitacion" value="<?=$licitacion?>">
 <?
 //si no hay items, que no se asocie con el rengon de presupuesto
 if($id_renglon_prop && $items)
 {?>
  <input type="hidden" name="id_renglon_prop" value=<?=$id_renglon_prop?>>
 <?
 }//si viene desde Seguimiento de Produccion
?>

</form>

<script>
 <?
 if($style_contado!="")
  echo "$style_contado";
 else
  echo "$style_contado";

 if($usuario_corapi&&$estado!="")
  echo "desplegar_forma_pago();";
 if($permiso)
 {
 ?>
  if(typeof(document.all.seguimientos)!="undefined")
   document.all.seguimientos.disabled=1;
 <?
 }
 ?>

 if(document.all.items.value==0)
  document.all.boton_eliminar.disabled=1;

 <?
 if($internacional)
 {
 ?>
   //recalculamos todos los montos totales de los montos de OC internacional
   calcular_monto_total(0,document.all.total_proporcional_flete);
   calcular_monto_total(1,document.all.total_base_imponible_cif);
   calcular_monto_total(2,document.all.total_derechos);
   calcular_monto_total(3,document.all.total_iva);
   calcular_monto_total(4,document.all.total_ib);

   set_montos_totales();
  <?
 }//de if($internacional)
 ?>


 //de acuerdo al modo de la Orden, es el titulo que mostramos, en la funciones JavaScript
 switch(document.all.modo.value)
 {
   case "oc_serv_tec": titulo_pagina="Orden de Servicio Técnico";break;
   default: titulo_pagina="Orden de Compra";break;
 }

 //dependiendo del largo del formulario, seteamos el largo del div del formulario
 var largo_form=parseInt(document.body.clientHeight)-parseInt(document.all.div_botonera.style.height);

 document.all.div_formulario.style.height=largo_form+"px";
</script>

<?
if($_ses_user["login"]=="marcos" || $_ses_user["login"]=="fernando" || $_ses_user["login"]=="norberto"
   || $_ses_user["login"]=="gonzalo" || $_ses_user["login"]=="mariela")
  echo fin_pagina();
else
 echo "</body></html>";
?>