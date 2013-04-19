<?
/*
Autor: GACZ - Marco - Fernando

MODIFICADA POR
$Author: mari $
$Revision: 1.18 $
$Date: 2007/01/05 14:31:36 $

*/

/******************************************************************************************************************************
 ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION - ATENCION
*******************************************************************************************************************************
 POR FAVOR INTENTEN MANTENER EL ORDEN QUE EL ARCHIVO ESTA INTENTANDO MANTENER (UN POCO DE ORDEN ES MEJOR QUE NADA DE ORDEN)
       Nota: Si se agrega una seccion nueva (indicada por '-' y letra mayuscula) o una subseccion nueva (indicada por '*'),
             agregarla con el comentario como el resto de las mismas, y ponerlas en este pseudo-indice.

 LAS SECCIONES EXISTENTES HASTA AHORA SON:
 -CONSULTA GENERAL DE PRODUCTOS DE LA OC
 -PRESUPUESTOS DE LICITACIONES
 -ACTIVIDADES RELACIONADAS CON ASOCIACION DE LA OC
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
   *Generacuib de Filas: CUANDO SE ABRE UNA ORDEN DESDE EL LISTADO
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

extract($_POST,EXTR_SKIP);
//print_r($parametros);
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
  SECCION: CONSULTA GENERAL DE PRODUCTOS DE LA OC
**************************************************************************************************************/
//CONSULTA CON PRODUCTOS IDENTICOS AGRUPADOS PERO SEPARADOS SI DIFIERE EL PRECIO
$campos="prod.id_producto,prod.desc_gral,p2.cantidad,p2.precio_licitacion as precio";
$q="SELECT $campos from ".
"precios p join ".
"(SELECT id_producto,id_proveedor,sum(cantidad) as cantidad,(sum(precio_licitacion)/count(cantidad)) as precio_licitacion ".
"from producto where id_renglon in ".
"(SELECT id_renglon from renglon where id_licitacion=$licitacion)".
"group by id_producto,id_proveedor,precio_licitacion) p2 ".
"on p.id_producto=p2.id_producto AND p.id_proveedor=p2.id_proveedor ".
"join productos prod on prod.id_producto=p2.id_producto ";
/*************************************************************************************************************
  FIN DE SECCION: CONSULTA GENERAL DE PRODUCTOS DE LA OC
**************************************************************************************************************/

/*************************************************************************************************************
  SECCION: PRESUPUESTOS DE LICITACIONES
  AUTOR: GACZ
**************************************************************************************************************/
if ($id_renglon_prop)
{
	/* */
 	$q ="SELECT desc_orig,desc_adic,unitario,id_producto_presupuesto,t1.cantidad,t1.id_producto,case when t2.cantidad_oc is null then 0 else t2.cantidad_oc end as cantidad_oc,cantidad_comprar FROM ";

 	//productos de los que se deberia hacer OC agrupados para sumar cantidades
 	$q.="(SELECT id_producto,monto_unitario as unitario,desc_orig,desc_adic,id_proveedor,sum(rp.cantidad*pp.cantidad) as cantidad ";
 	$q.="FROM ";
 	$q.="renglon_presupuesto_new rp ";
 	$q.="join producto_presupuesto_new pp on rp.id_renglon_prop=pp.id_renglon_prop AND rp.id_renglon_prop in ($id_renglon_prop) ";
 	$q.="join producto_proveedor_new pp2 on pp.id_producto_presupuesto=pp2.id_producto_presupuesto and pp2.id_proveedor=$select_proveedor ";
 	$q.="where activo=1 ";
	$q.="group by id_producto,unitario,desc_orig,desc_adic,id_proveedor ";
 	$q.=") t1 ";

 	$q.="join ";

 	//productos de los que se deberia hacer OC solo IDs necesarios
 	//que no se pudieron recuperar en la consulta anterior
 	$q.="(SELECT id_producto,pp.id_producto_presupuesto,pp2.cantidad as cantidad_comprar ";
 	$q.="FROM ";
 	$q.="renglon_presupuesto_new rp ";
 	$q.="join producto_presupuesto_new pp on rp.id_renglon_prop=pp.id_renglon_prop AND rp.id_renglon_prop in ($id_renglon_prop) ";
 	$q.="join producto_proveedor_new pp2 on pp.id_producto_presupuesto=pp2.id_producto_presupuesto and pp2.id_proveedor=$select_proveedor ";
 	$q.="where activo=1 ";
 	$q.=") t3 using(id_producto) ";

 	$q.="left join ";

 	//productos de los que se hizo OC agrupados para descontar las unidades
 	//de diferentes OC y del mismo producto
 	$q.="(SELECT oc_pp.id_producto_presupuesto,f.id_producto,f.descripcion_prod,sum(f.cantidad) as cantidad_oc ";
 	$q.="FROM oc_pp ";
 	$q.="join producto_presupuesto_new pp using(id_producto_presupuesto) ";
 	$q.="join orden_de_compra oc using(nro_orden) ";
 	$q.="join fila f on f.id_producto=pp.id_producto AND f.nro_orden=oc.nro_orden ";
 	$q.="where oc.estado!='n' ";
 	$q.="group by oc_pp.id_producto_presupuesto,f.id_producto,f.descripcion_prod ";
 	$q.=") t2 using(id_producto_presupuesto) ";

 	$q.="order by t1.id_producto ";

	$prod_seg=sql($q,"<br>Error al consultar productos de presupuesto<br>") or fin_pagina();

 	//VERIFICO QUE EXISTA EN STOCK SI EL PROVEEDOR ES STOCK
  $q ="select * ";
  $q.="from proveedor ";
  $q.="join depositos d on d.nombre=substring(razon_social from 'Stock (.*)') ";//para traer el id_deposito
  $q.="where id_proveedor=$select_proveedor";
  $deposito=sql($q,"<br>Error al verificar la existencia del proveedor tipo Stock") or fin_pagina();
  //si es un proveedor STOCK
  if ($deposito->recordcount())
  {
		$id_deposito=$deposito->fields['id_deposito'];
		$nbre_deposito=$deposito->fields['nombre'];
  }
	else
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
  	$id_producto_presupuesto="";
  	$add_coma="";
  	$link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$id_producto));//historial de comentarios
  	//Busco los id_producto_presupuesto para los
  	//productos repetidos y resto las cantidades
  	do
  	{
  		$id_producto_presupuesto.=$add_coma.$prod_seg->fields['id_producto_presupuesto'];
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
  		$aprod_seg[$i]['link_historial']=$link;
  		if ($id_deposito)
  		{
  			if (($prov_cant=stock_seleccionar_reserva($id_producto,$id_deposito,$cantidad))==0)
  			{
  				$msg="No hay Stock Suficiente<br> Deposito: '$nbre_deposito' <br> Producto: '$desc'";
  				die("<center><font size=+1 color=red>$msg</font></center>");
  			}
  			else
				 $aprod_seg[$i]['prov_cant']=$prov_cant;
  		}
  		$i++;
  	}//de if (($cantidad-=$cantidad_oc)>0)
  }//de while (!$prod_seg->EOF)
  $aprod_seg['cantidad']=$i;
}//de if ($id_renglon_prop)

/*************************************************************************************************************
  FIN DE SECCION: PRESUPUESTOS DE LICITACIONES
**************************************************************************************************************/

/*************************************************************************************************************
 SECCION: ACTIVIDADES RELACIONADAS CON ASOCIACION DE LA OC
**************************************************************************************************************/
//asociado a licitacion (o presupuesto)
if ($licitacion)
{
 //si se asocia con una licitacion mediante un Seguimiento de Produccion
 //no se recuperan los productos del renglon original
 if ($licitacion && !$id_renglon_prop)
 	$productos= sql($q,"<br>Error al consultar productos de la OC (general)<br>") or fin_pagina();

 //elijo la entidad de la licitacion
 $sql="select id_entidad,nombre from  licitacion
       join entidad using(id_entidad)
       where id_licitacion=$licitacion";
 $result=sql($sql,"<br>Error al traer la entidad de la licitacion<br>") or fin_pagina();
 $cliente=$result->fields["nombre"];
 $id_entidad=$result->fields["id_entidad"];

}

//asociado al servicio tecnico
if ($caso && $pagina)
{
 $sql="select casos_cdr.idcaso,entidad.nombre,entidad.id_entidad
      from casos.casos_cdr
      join dependencias using (id_dependencia)
      join entidad using (id_entidad)
      where casos_cdr.nrocaso=$caso";
 $resultado=sql($sql,"<br>Error al consultar datos del caso asociado<br>") or fin_pagina();
 $idcaso=$resultado->fields["idcaso"];

 $id_entidad=$resultado->fields["id_entidad"];
 $cliente=$resultado->fields["nombre"];

 $sql="select * from repuestos_casos
          left join productos using (id_producto)
          where idcaso=$idcaso and id_producto not in
          (
          select id_producto from fila join orden_de_compra using(nro_orden)
                 where estado<>'n' and nrocaso=$caso
          )
      ";
 $productos=sql($sql,"<br>Error al consultar por los repuestos del caso asociado<br>") or fin_pagina();
 $fecha_entrega=date("d/m/Y",mktime(0,0,0));
 //cliente es el CAS

}//de if ($caso && $pagina)

//asociada a los honorarios de los servicios tecnicos
if ($idate && $pagina)
{
 //consulta para traer los honorarios de los CAS
 //pagado=1 indica que la el caso esta listo para pagar
 //casos_cdr.pagado_orden=0 or casos_cdr.pagado_orden is NULL indica que no se ha pagado la orden de pago
 $sql="Select casos_cdr.*,cas_ate.id_proveedor,obspago  from cas_ate
       left join  casos_cdr using (idate)
       where idate=$idate and (casos_cdr.pagado=1 and (casos_cdr.pagado_orden=0 or casos_cdr.pagado_orden is NULL) and fila isnull)
       order by casos_cdr.nrocaso asc ";
  $casos=sql($sql,"<br>Error al traer datos de Honorarios de Casos<br>") or fin_pagina();
  //print_r($casos->fields);
  /*selecciono los datos que van a elegirse automaticamente*/
  $select_proveedor=$casos->fields["id_proveedor"];
  $fecha_entrega=date("d/m/Y",mktime(0,0,0));
  $select_moneda=1;
  //entidad = Coradir SRL
  $sql="select * from entidad where id_entidad=441";
  $resultado=sql($sql,"<br>Error al traer la entidad Coradir para Honorario de Serv Tec<br>") or fin_pagina();
  $id_entidad=$resultado->fields["id_entidad"];
  $cliente=$resultado->fields["nombre"];

 //traigo el producto que va a pagar
  $sql="Select * from productos where desc_gral='Gastos de Servicio Tecnico'";
  $productos=sql($sql,"<br>Error al traer el producto Gastos de Servicio Tecnico<br>") or fin_pagina();

   //busca el lugar de entrega de la ultima orden de pago asociada al organismo idate
  $sql_entrega="select distinct(nro_orden) as  num,orden_de_compra.lugar_entrega,orden_de_compra.fecha_entrega
                from casos.casos_cdr
                join compras.fila on fila.id_fila=casos_cdr.fila
                join compras.orden_de_compra using (nro_orden)
                where fila is not null and pagado_orden=1 and idate=$idate and flag_honorario=1 and ord_pago='si' order by fecha_entrega desc";
  $res_entrega=sql( $sql_entrega," $sql_entrega") or fin_pagina;
  $entrega=$res_entrega->fields['lugar_entrega'];

}//de if ($idate && $pagina)

//asociada a Stock
if($flag_stock)
{
  //entidad = Coradir SRL
  $sql="select * from entidad where id_entidad=441";
  $resultado=sql($sql,"<br>Error al traer la entidad Coradir para Stock<br>") or fin_pagina();
  $id_entidad=$resultado->fields["id_entidad"];
  $cliente=$resultado->fields["nombre"];

}//de if ($flag_stock)

//asociada a un RMA de produccion
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
		$q="select * from fila where nro_orden=$nro_orden";
		$filas=sql($q,"<br>Error al traer los datos de las filas de la OC<br>") or fin_pagina();

		$q="select orden_de_compra.* from orden_de_compra where nro_orden=$nro_orden";
		$orden=sql($q,"<br>Error al traer los datos propios de la OC<br>") or fin_pagina();

		//si la OC esta asociada a Licitacion o Presupuesto, traemos el color y estado de la Licitacion o Presupuesto
		if($orden->fields['id_licitacion']!="")
		{
		 $query="select nombre,color from licitaciones.estado join licitaciones.licitacion using (id_estado) where id_licitacion=".$orden->fields['id_licitacion'];
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

		//traemos los datos de las notas de credito asociadas (si es que hay)
		$query="select id_nota_credito,nota_credito.monto,nota_credito.observaciones,oc.valor_dolar,id_moneda,simbolo from (select * from n_credito_orden where nro_orden=$nro_orden) as oc join nota_credito using(id_nota_credito) join moneda using(id_moneda)";
        $notas_credito=sql($query,"<br>Error al traer las notas de credito relacionadas con el pago Nº $nro_orden<br>") or fin_pagina();

        if (!$select_proveedor && !$select_provee)
        {
	      $select_proveedor=$orden->fields['id_proveedor'];
	      $select_provee='t';
        }
        if (!$fecha_entrega)
        {
          $fecha_entrega=date("j/m/Y",strtotime($orden->fields['fecha_entrega']));
 	    }

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
		/******Agrego Broggi********/
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
	$o=sql($q,"<br>Error al traer el ultimo valor usado como Nro el pago <br>") or die($db->ErrorMsg()."<br> $q");
	$nro_orden_n=$o->fields['last_value']+1;

}//del else de if ($nro_orden && $nro_orden!=-1)


//esto solo se hace cuando el estado de la orden es mayor o igual
// que autorizada
if($estado=="" || $estado=="p" || $estado=="r" || $estado=="u")
{
 $q="select * from proveedor ";
 if ($select_provee && $select_provee!='t')
	$q.=" where filtro ilike '%".$select_provee."%' AND razon_social not ilike 'stock%' ";
 /*SI SE AÑADE EL FILTRO OTRA VEZ DESCOMENTAR
 elseif (!$select_provee)
   	$q.=" where filtro ilike '%c%' ";
 */
 $q.="where activo AND razon_social not ilike 'stock%' ";
 $q.="order by razon_social";
}//de if($estado=="" || $estado=="p" || $estado=="r" || $estado=="u")
else
 $q="select * from proveedor where id_proveedor=$select_proveedor";

$proveedores=sql($q,"<br>Error al traer los proveedores para la OC<br>") or fin_pagina();


 $q="select * from contactos where id_proveedor=$select_proveedor";

if ($select_proveedor && $select_proveedor!=-1)
	$contactos=sql($q,"<br>Error al traer los contactos del proveedor elegido<br>") or fin_pagina();
else
	$select_contacto=-1;

$sql="select sum(monto) as montos_pagos from pago_orden join ordenes_pagos using (id_pago) ";
$sql.="where pago_orden.nro_orden=$nro_orden";
$resultado=sql($sql,"<br>Error al traer montos_pagos de la OC<br>") or fin_pagina();

$montos_pagos=$resultado->fields["montos_pagos"];
if ($montos_pagos=="")
 $montos_pagos=0;
/*************************************************************************************************************
 FIN DE SECCION: RECOPILACION DE DATOS DE LA OC Y SETEO DE VARIABLES PARA MOSTRAR LOS DATOS DE LA MISMA
**************************************************************************************************************/

/*************************************************************************************************************
 SECCION: SETEO DE PERMISOS PARA LOS BOTONES Y DEMAS FUNCIONALIDADES DE LA PAGINA
**************************************************************************************************************/
//Si la OC esta en estado es enviada, y se ha recibido al menos un
//producto, deshabilitamos el boton de rechazar, y el de anular

if($estado=="e")
{
	$query="select sum(recibido_entregado.cantidad) as suma from fila join recibido_entregado using(id_fila) where nro_orden=$nro_orden";
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
 case 'm'://enviada //de HST
		 	$permisos_b['terminar']=" ";
		 	$permisos_b['eliminar']=" disabled ";
		 	$permisos_b['agregar']=" disabled ";
		 	$permisos_b['guardar']=" disabled ";
		 	$permisos_b['anular']=" ";
		 	$permisos_b['pagar']=" ";
		 	$permisos_b['por_autorizar']=" disabled ";
		 	$permisos_b['autorizar']="disabled";
  break;
 case 'e'://enviada
		 	$permisos_b['terminar']=" ";
		 	$permisos_b['eliminar']=" disabled ";
		 	$permisos_b['agregar']=" disabled ";
		 	$permisos_b['guardar']=" disabled ";
		 	$permisos_b['anular']=" ";
		 	$permisos_b['por_autorizar']=" disabled ";
		 	$permisos_b['autorizar']="disabled";
            if ($gastos_servicio_tecnico==1 || $flag_honorario==1)
              $permisos_b['pagar']=" disabled";
		break;
 case 'a'://autorizada
		 	$permisos_b['terminar']=" disabled ";
		 	$permisos_b['eliminar']=" disabled ";
		 	$permisos_b['agregar']=" disabled ";
		 	$permisos_b['guardar']=" disabled ";
		 	$permisos_b['anular']=" ";
		 	$permisos_b['por_autorizar']=" disabled ";
		 	$permisos_b['pagar']=" ";
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
			$permisos_b['autorizar']=" disabled";
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
/*if(!permisos_check("inicio","permiso_editar_crear_ordenc"))
{ $permiso="disabled";
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
*/
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

<script language="JavaScript" src="ord_pago.js"></script>
<script>
var links_stock=new Array();
links_stock["san luis"]="<?=encode_link($html_root.'/modulos/stock/stock_san_luis.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["buenos aires"]="<?=encode_link($html_root.'/modulos/stock/stock_buenos_aires.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["new tree"]="<?=encode_link($html_root.'/modulos/stock/stock_new_tree.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["anectis"]="<?=encode_link($html_root.'/modulos/stock/stock_anectis.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["sicsa"]="<?=encode_link($html_root.'/modulos/stock/stock_sicsa.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["st_ba"]="<?=encode_link($html_root.'/modulos/stock/stock_st_ba.php',array('onclickcargar'=>"window.opener.cargar_stock()",'onclicksalir'=>'window.close()','cambiar'=>0,"pagina_oc"=>"1")) ?>";
links_stock["no_stock"]="<?=encode_link('../general/productos2.php',array('onclickcargar'=>"window.opener.cargar()",'onclicksalir'=>'window.close()','cambiar'=>0)) ?>";
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
<form name="form1" method="post" action="ord_pago_proc.php" >
<div style="overflow:auto;width:100%;position:relative" id="div_formulario">
<input type="hidden" name="destino_para_autorizar" value="ord_pago_listar.php">
<input type="hidden" name="montos_default" value="si">
<input type="hidden" name="estado" value="<?=$estado?>">
<input type="hidden" name="presupuesto" value="<?=$presupuesto?>">
<input type="hidden" name="caso" value="<?=$caso?>">
<input type="hidden" name="pagina_asoc" value="<?=$pagina?>">
<input type="hidden" name="pago_especial" value="<?=$orden->fields['habilitar_pago_especial']?>">
<input type="hidden" name="montos_pagos" value="<?=$montos_pagos?>">
<input type="hidden" name="flag_stock" value=<?if($flag_stock)echo $flag_stock;else echo 0;?>>
<input type="hidden" name="accion" value="nada">
<input type="hidden" name="cliente" value="0">
<input type="hidden" name="fecha_creacion" value="<?=$fecha_creacion?>">
<input type='hidden' name='idate' value='<?=$idate?>'>
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
<?
/***********************************************
 Traemos y mostramos el Log de la OC
************************************************/
//left join por si alguna vez se elimina el usuario
$q="select *,nombre ||' '||apellido as usuario from log_ordenes LEFT join usuarios on user_login=login where nro_orden=";
$q.=($nro_orden)?$nro_orden:-1;
$q.=" order by fecha desc";
$log=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");
?>
<div align="right"><input name="mostrar_ocultar_log" class="estilos_check" type="checkbox" value="1" onclick="if(!this.checked) document.all.tabla_logs.style.display='none'; else document.all.tabla_logs.style.display='block'; "/> Mostrar Logs</div>
<div style="display:'none';overflow:auto;<? if ($log->RowCount() > 3) echo 'height:60;' ?> " id="tabla_logs" >
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=#cccccc>
<?
$anulada_mostrar=0;
$rechazo_mostrar=0;
do
{

 if ($log->fields['tipo_log']=="de rechazo")
  $coment_rechazo=$log->fields['otros'];
//$log->fields['fecha']?strtotime($log->fields['fecha']):''
 ?>
<tr title="<?=$coment_rechazo?>">
      <td height="20" nowrap>Fecha <?=(($log->fields['tipo_log'])?$log->fields['tipo_log'].": ":"creacion: ").($log->fields['fecha']?date("j/m/Y H:i:s",strtotime($log->fields['fecha'])):date("j/m/Y H:i:s"))?> </td>
      <td nowrap > Usuario : <?=(($log->fields['usuario'])?$log->fields['usuario']:$_ses_user['name']); ?> </td>
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
/**************************************************************************
 Decisión de a qué esta asociada la OC - SE ELIMINO ESTA PARTE PARA LA DEMO
**************************************************************************/


/***********************************************
 Mostramos los datos propios de la OC
************************************************/
if ($nro_orden_n)
  $estado_nombre="Nuevo Pago";
elseif ($nro_orden!=-1)
{
 switch ($orden->fields['estado'])
 {
  case 'P':
  case 'p': $estado_nombre="PENDIENTE"; break;
  case 'A':
  case 'E':
  case 'e':
  case 'a': $estado_nombre="AUTORIZADA";
            $permiso="disabled";
            break;
  case 'm': $estado_nombre="AUTORIZADA / ENVIADA";
            $permiso="disabled";
	      	break;
  case 't':
  case 'T': $estado_nombre="TERMINADA";
	        $permiso="disabled";
	      	$can_finish=true;
	      	break;
  case 'r': $estado_nombre="RECHAZADA";
	        break;
  case 'n': $estado_nombre="ANULADA";
	        $permiso="disabled";
	      	$can_finish=false;
			break;
  case 'u': $estado_nombre="POR AUTORIZAR";
            break;
  case 'd': $estado_nombre="PARCIALMENTE PAGADA";
	        $permiso="disabled";
	      	$can_finish=true;
			break;
  case 'g':
  case 'G': $estado_nombre="TOTALMENTE PAGADA";
	        $permiso="disabled";
	      	break;
  default: $estado_nombre="DESCONOCIDO";
 }//de switch ($orden->fields['estado'])
}//de elseif ($nro_orden!=-1)
else
 $estado_nombre="NoEnTrO";
?>
<input type="hidden" name="orden_compra_id" value="<?=$nro_orden?>">
 <?

   if ($flag_honorario || $gastos_servicio_tecnico) {?>
  <div align='center'>
     <font size=3 color='red' ><b>Orden de Pago asociada a Honorarios Serv. Técnico</font>
  </div>
  <?

    //si la orden esta asociada a honorarios de servivio tecnicos y
    // esta autorizada no puede pagar hasta que este enviada
  ?>
  <?}?>
  <table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor=<?=$bgcolor_out?> id="tabla_info" class="bordes">
  <tr id="mo">
     <td colspan="3">
      <font size="3"><b>Orden de Pago Nº </b>
      <?=($nro_orden && $nro_orden!=-1)?$nro_orden:$nro_orden_n ?></font>
     </td>
    </tr>
    <tr>
      <td>
       <table width="95%" align="center">
        <tr>
         <td width="15%" id="td_fecha_entrega">
          <b>Fecha </b>
         </td>
         <td width="15%">
          <input name="fecha_entrega" type="text" id="fecha_entrega" size="10"   <?=$permiso ?> value="<?=$fecha_entrega ?>" onchange="check_fecha_legal()">
          <img <?=$permiso ?> src=../../imagenes/cal.gif border=0 align=center style='cursor:hand;' alt='Haga click aqui para seleccionar la fecha'  onClick="javascript:popUpCalendar(td_fecha_entrega, fecha_entrega, 'dd/mm/yyyy');">
         </td>
         <td width="40%" align="center">
          <font size="2" color="Blue">&nbsp; <!--<b>Tipo: <?=$tipo_oc?></b>--></font>
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
}
?>
    <tr>
      <td align="center">
      <input type="hidden" name="borrar_filas_stock" value="0">
      <table width="98%" class="bordes"><!--Tabla del proveedor-->
       <tr align="center" id="sub_tabla">
         <td colspan="4">Proveedor</TD>
       </tr>
       <tr>
        <td width="15%"><STRONG>Nombre</STRONG> </TD>
        <td width="55%">
          <!--Hidden para saber el id del proveedor aunque este deshabilitado
           el select_proveedor-->
          <input type="hidden" name="id_proveedor_a" value="<?=$select_proveedor?>">
          <select name="select_proveedor" style="width:300px"
           onKeypress="buscar_op_submit(this);"
           onblur="borrar_buffer();"
           onclick="if(puntero>0)onchange_proveedor(this);else {borrar_buffer();}"
           onchange="onchange_proveedor(this);" <?=$permiso?>
          >
           <option value="-1">Seleccione un proveedor</option>
           <?
           $disabled_por_stock="";
           while (!$proveedores->EOF)
           {//si la OC esta asociada a Stock, filtramos los proveedores, para no mostrar
            //aquellos que sean proveedores de tipo Stock. Sino, mostramos todos los proveedores.
            if(!($flag_stock && substr_count($proveedores->fields['razon_social'],"Stock")>0) || ($estado!="" && $estado!="p" && $estado!="r" && $estado!="u"))
            {
           ?>
             <option value="<?=$proveedores->fields['id_proveedor']?>"
             <?if ($proveedores->fields['id_proveedor']==$select_proveedor)
               {
               	echo " selected";
                $clasif=$proveedores->fields['clasificado'];
               }?>
             >
              <?=$proveedores->fields['razon_social']?>
             </option>
             <?
            }//de if(!($flag_stock && substr_count($proveedores->fields['razon_social'],"Stock")....
	        $proveedores->MoveNext();
           }//de while (!$proveedores->EOF)
           ?>
          </select>
          <?
          if ($estado!="")
          {?>
           &nbsp;&nbsp;<input type='button' name='historial' value='H' onclick="window.open('<?=encode_link("../productos/clasif_prove.php",array()) ?>&proveedor='+document.all.select_proveedor.value,'','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=50,top=30,width=700,height=400')">
        <?}?>
          <input type="hidden" name="es_stock" value="0">
		</td>
		<td width="10%" align="right"><strong>Contacto</strong></td>
        <td width="20%">
         <select name="select_contacto" <?=$permiso ?> >
          <option value="-1">Seleccione el contacto</option>
          <?
			//si esta definido el objeto contactos
			if ($contactos)
			{
				while (!$contactos->EOF)
				{
                 ?>
	             <option value="<?=$contactos->fields['id_contacto']?>" <? if ($contactos->fields['id_contacto']==$select_contacto) echo " selected ";  ?>><?=$contactos->fields['nombre'] ?></option>
                 <?
				 $contactos->MoveNext();
				}//de while (!$contactos->EOF)
			}//de if ($contactos)
?>
           <option value="-2" <? if ($select_contacto==-2) echo " selected ";  ?>>Ninguno</option>
		 </select>
       </td>
      </tr>
      <tr>
       <td colspan="4"><?if ($select_proveedor!='') echo credito_proveedor($select_proveedor);?></TD>
      </tr>
     </table><!--Fin de Tabla del proveedor-->
    </td>
   </tr>
   <tr>
    <td align="center">
     <table width="98%" class="bordes"><!--Tabla de Forma de Pago-->
       <tr align="center" id="sub_tabla">
        <td colspan="5">Forma de Pago</td>
       </tr>
       <tr>
        <?$title_pago="Desplegar la forma de pago para este pago";?>
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
         <select name="select_pago" id="select_pago" <?=$permiso ?> <?=$disabled_por_stock?> onchange="if(this.text!='Contado')
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
          {$res_pago=$db->Execute($query_pago="select id_plantilla_pagos,descripcion from orden_de_compra join plantilla_pagos using (id_plantilla_pagos) where nro_orden=$nro_orden") or die ($db->ErrorMsg()."select__pagos");
           $select_pago=$res_pago->fields['id_plantilla_pagos'];
           $descripcion_pago=$res_pago->fields['descripcion'];
           $add_query="or id_plantilla_pagos=$select_pago";
          }//de if($nro_orden && $nro_orden!=-1 && !$es_stock && $select_pago)
          else
           $add_query="";
          if($estado=="" || $estado=="p" || $estado=="r" || $estado=="u" || $estado=="n")
           $q="select * from plantilla_pagos where mostrar=1 $add_query order by descripcion ";
          else
           $q="select * from plantilla_pagos where id_plantilla_pagos=$select_pago ";

          $pagos=$db->Execute($q) or die($db->ErrorMsg()."<br> $q");
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
           <option value="<?=$pagos->fields['id_plantilla_pagos']?>" <? if  ($pagos->fields['id_plantilla_pagos']==$select_pago) echo ' selected' ?>>
            <?=$pagos->fields['descripcion']?>
           </option>
           <?
           $pagos->MoveNext();
          }//de while (!$pagos->EOF)
          ?>
         </select>

         <input type="button" name="nueva_forma" value="Forma de Pago" <?=$disabled_por_stock?> <?=$permiso_forma_pago?> <?if($orden->fields['fecha']<"2004-02-02 00:00:00" && $estado!="e"&& $estado!="") echo " disabled ";elseif($select_moneda==0 || $select_moneda==-1)echo "disabled title='Primero debe seleccionar la moneda'";else echo "title='Permite agregar una nueva forma de pago o editar la que está seleccionada'";?> onclick="window.open('<?=encode_link('ord_pago_pagos.php',array('pago_especial'=>$orden->fields['habilitar_pago_especial'],'reload'=>1,"presupuesto"=>$presupuesto))?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=0,top=0,width=800,height=500')">&nbsp;
         <?
         if(permisos_check("inicio","permiso_boton_despagar_oc"))
         {if($estado=="d" || $estado=="g")
          {
          ?>
           <input type="submit" name="despagar" value="D$" onclick="return confirm('Al despagar un Pago se borrarán egresos de caja o se anularán cheques cargados, que hayan sido usados para pagar ese Pago\n¿Está seguro que desea despagar la Orden de Pago Nº <?=$nro_orden?>?')">&nbsp;
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
          $q="select * from moneda";
          $moneda=$db->Execute($q) or die ($db->ErrorMsg()."<br>$q");
          while (!$moneda->EOF)
          {?>
           <option value="<?= $moneda->fields['id_moneda'] ?>" <? if ($moneda->fields['id_moneda']==$select_moneda) {echo " selected"; $tipo_moneda=$moneda->fields['nombre']; } ?>>
            <?= $moneda->fields['nombre'] ?>
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
         <b>&nbsp;Valor Dolar </b><input type="text" name="valor_dolar" <?=$permiso ?> value="<?=number_format($valor_dolar,3,'.','')?>" size="7">
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
       {$string_ordenes="El Pago forma parte de un <b>Pago Múltiple</b> junto a los pagos: ";
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
           //$query="select plantilla_pagos.descripcion as nombre,forma_de_pago.dias,tipo_pago.descripcion,x.monto,x.valor_dolar from compras.orden_de_compra join compras.plantilla_pagos using (id_plantilla_pagos) join compras.pago_plantilla using (id_plantilla_pagos) join compras.forma_de_pago using(id_forma) join compras.tipo_pago using(id_tipo_pago) left join (select * from compras.ordenes_pagos join compras.pago_orden using (id_pago) where nro_orden=$nro_orden)as x using (id_forma) where orden_de_compra.nro_orden=$nro_orden";
           $query="select plantilla_pagos.descripcion as nombre,forma_de_pago.dias,tipo_pago.descripcion,x.monto,x.valor_dolar from compras.orden_de_compra join compras.plantilla_pagos using (id_plantilla_pagos) join compras.pago_plantilla using (id_plantilla_pagos) join compras.forma_de_pago using(id_forma) join compras.tipo_pago using(id_tipo_pago) left join (select distinct id_forma,monto,ordenes_pagos.valor_dolar from compras.orden_de_compra join compras.pago_orden using (nro_orden) join compras.ordenes_pagos using(id_pago) where nro_orden=$nro_orden) as x using (id_forma) where orden_de_compra.nro_orden=$nro_orden";
           $forma_de_pago_info=$db->Execute($query) or die ($db->ErrorMsg()." error en info de forma de pago");
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
  if($licitacion && !$presupuesto && !$desde_presupuesto &&($estado=="p" || $estado=="u" || $estado=="" || $estado=="r"))
  {?>
   <tr>
    <td colspan="3">
     <font color="red" size="5">
      <b>
       Atención: Este Pago asociada a licitación, no fue hecha a través de un presupuesto.
       Por favor, extreme controles antes de autorizar la orden.
      </b>
     </font>
    </td>
   </tr>
   <?
  }//de if($licitacion && !$presupuesto && !$desde_presupuesto &&($estado=="p" || $estado=="u" || $estado=="" || $estado=="r"))

  if ($flag_honorario==1 || $gastos_servicio_tecnico==1) {?>
    <tr>
   <td>
    <table width="98%" class="bordes" align="center"><!--Tabla de Lugar de Entrega-->
     <tr align="center" id="sub_tabla">
      <td colspan="3">Lugar y Forma de Entrega </td>
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
       $entrega=ajustar_lineas_texto($entrega,$longitud_fila);
	       ?>
       <textarea name="entrega" style="width:95%;" rows="<?=row_count($entrega,$longitud_fila)?>" wrap="VIRTUAL"><?=$entrega?></textarea>
      </td>
     </tr>
     <?if ($estado=='p' || $estado=='u' || $estado=='r'  || !$estado) {?>
     <tr>
        <td align="right"><b>Actualizar C.A.S</b>&nbsp;&nbsp;<input type='checkbox' class="estilos_check" name="actualizar_cas" value=1 title="Actualiza el comentario del C.A.S"></td>
     </tr>
     <?}?>
    </table>
   </td>
  </tr>

  <? }?>
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
   if ($estado!="")
   {?>
    <tr>
     <? /*** agrego al tabla para los comentarios usando la funcion gestiones_comentarios ***/
     $sql = "SELECT id_comentario FROM ";
	 $sql .= "gestiones_comentarios WHERE id_gestion=$nro_orden ";
	 $sql .= "AND tipo='ORDEN_COMPRA'";
	 $resu=sql($sql, "Error al traer los comentarios para el Pago") or fin_pagina();
     ?>
	 <td>
	  <table width="98%" class="bordes" align="center"><!--Tabla de Comunicacion con el proveedor-->
       <tr align="center">
        <td colspan="3">
         <table width="100%"  id="sub_tabla">
          <tr>
           <td width="1%">
            <img src='../../imagenes/rigth2.gif' border=0 style='cursor: hand;' alt="Mostrar Comentarios"
	         onClick='if (this.src.indexOf("rigth2.gif")!=-1)
                      {
	                   this.src="../../imagenes/down2.gif";
	                   this.alt="Ocultar Comentarios";
		                div_comentario.style.overflow="visible";
	                  }
	                  else
	                  {
		               this.src="../../imagenes/rigth2.gif";
		               this.alt="Mostrar Comentarios";
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

   /*******************************************************
    Mostramos campos internos de Coradir
   ********************************************************/
//Si la OP esta asociada a Honorarios de servicio tecnico
if ($flag_honorario || $gastos_servicio_tecnico)
{   ?>
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
	         if ($nro_orden==-1 && $casos) {
	         	while (!$casos->EOF) {
	         	  $obspago.="Caso:".substr($casos->fields['nrocaso'],-4)." ".$casos->fields['obspago']."\n";
	         	  $casos->Movenext();
	         	}
	            $notas_internas=ajustar_lineas_texto($obspago,$longitud_fila);
	         } else
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
}

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
          if($estado=='a' || $estado=='e' || $estado=='d'|| $estado=='g')
          {$query="select razon_social,id_proveedor from proveedor where id_proveedor=$proveedor_reclamo";
           $proveedores=$db->Execute($query) or die($db->ErrorMsg()."<br>Error al traer proveedor de reclamo");
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
   if ($estado=='g' || $estado=='e' || $estado=='d')
   {
    $consulta_mail="select para, fecha_envio, user_name from ord_compra_mails where nro_orden=$nro_orden order by fecha_envio desc";
    $res_mail=$db->Execute($consulta_mail) or die($db->ErrorMsg()."<br> $consulta_mail");
    ?>
    <tr>
     <td>
      <table width="98%" class="bordes" align="center"><!--Tabla de Seguimiento Interno Coradir-->
       <tr align="center" id="sub_tabla">
        <td>Pago enviado a</td>
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
?>
<br>
<table width="100%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor="<?=$bgcolor_out?>" class="bordes">
 <tr id="mo" >
  <td colspan="3">
   <font size="3">Items del Pago</font>
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
     if(($estado=='a' || $estado=='e' || $estado=='d' || $estado=='g') && permisos_check("inicio","permiso_borrar_fila_especial"))
     {?>
	  <input  class="estilos_check" type="checkbox" name="check_habilitar_cambios" value="1" onclick="habilitar_cambios_especiales()"> Modificar Filas
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
      <input class="estilos_check" type=checkbox name="elegir_todos" <?=$permiso?> title="Seleccionar todas las filas" onclick="seleccionar_todos(this,document.form1.chk)">
      </td>
      <td nowrap width="65%" align="center">Producto</td>
      <td nowrap width="5%" align="center">Cantidad</td>
      <td nowrap width="10%" align="center">Precio Unitario</td>
      <td nowrap width="20%" align="center">Subtotal Final</td>
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
   	 $casos->MoveFirst();
     while (!$casos->EOF)
     {
      $link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$productos->fields['id_producto']));
         ?>
        <tr>
          <td align="center">
           <input name="chk" type="checkbox" id="chk" value="1" class="estilos_check">
          </td>
          <input type="hidden" name="idp_<?=$items ?>" value="<?=$productos->fields['id_producto']?>">
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
           <textarea name="desc_<?=$items ?>" style="width:90%" rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$items ?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$items ?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
          </td>
          <td align="center">
           <input name="cant_<?=$items?>" type="text" size="6" style='text-align:right' value="1" onchange="calcular(this)" >
          </td>
          <td align="center">
           <input name="unitario_<?=$items?>" type="text" size="10" style='text-align:right' value="<?=number_format($casos->fields['costofin'],2,".","") ?>" onchange="this.value=this.value.replace(',','.');calcular(this)">
           <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
          </td>
          <td align="center">
           <input name="subtotal_<?=$items?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($casos->fields['costofin'],2,".","") ?>">
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
    if ($caso && $productos)
    {
     while (!$productos->EOF)
     {
      $link=encode_link("../licitaciones/historial_comentarios.php",array("id_producto"=>$productos->fields['id_producto']));
      ?>
      <tr>
       <td align="center">
        <input name="chk" type="checkbox" id="chk" value="1" class="estilos_check">
       </td>
       <input type="hidden" name="idp_<?=$items ?>" value="<?=$productos->fields['id_producto']?>">
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
         <textarea name="desc_<?=$items ?>" style="width:90%" rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$items?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$items?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
       </td>
       <td align="center">
        <input name="cant_<?=$items?>" type="text" size="6" style='text-align:right' value="<?=$productos->fields['cantidad'] ?>" onchange="calcular(this)" >
       </td>
       <td align="center">
        <input name="unitario_<?=$items?>" type="text" size="10" style='text-align:right' value="<?=number_format($productos->fields['precio_stock'],2,".","") ?>" onchange="this.value=this.value.replace(',','.');calcular(this)">
        <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
       </td>
       <td align="center">
        <input name="subtotal_<?=$items?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($productos->fields['cantidad']*$productos->fields['precio_stock'],2,".","") ?>">
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
	   <input name="chk" type="checkbox" id="chk" value="1" class="estilos_check">
	  </td>
	  <input type="hidden" name="idp_<?=$items ?>" value="<?=$productos->fields['id_producto']?>">
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
	   <textarea name="desc_<?=$items ?>" style="width:90%" rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$items?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$items?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
	  </td>
	  <td align="center">
	   <input name="cant_<?=$items?>" type="text" size="6" style='text-align:right' value="<?=$productos->fields['cantidad'] ?>" onchange="calcular(this)" >
	  </td>
	  <td align="center">
	   <input name="unitario_<?=$items?>" type="text" size="10" style='text-align:right' value="<?=number_format($productos->fields['precio'],2,".","") ?>" onchange="this.value=this.value.replace(',','.');calcular(this)">
	   <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
	  </td>
	  <td align="center">
	   <input name="subtotal_<?=$items?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($productos->fields['cantidad']*$productos->fields['precio'],2,".","") ?>">
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
	   <input name="chk" type="checkbox" id="chk" value="1" <?=$permiso ?> class="estilos_check">
	  </td>
	  <input type='hidden' name='idprov_<?=$items?>' value='<?=$filas->fields['prov_prod']?>' >
	  <input type="hidden" name="idf_<?=$items?>" value="<?=$filas->fields['id_fila']?>">
	  <input type="hidden" name="idp_<?=$items?>" value="<?=$filas->fields['id_producto']?>" <?/*no sacar este permiso!!! ---->*/ echo $permiso?>>
	  <?
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
	  ?>
	  <td <?=$color_textarea?>>
	   <?
	   $desc_adic=$_POST['h_desc_'.$items] or $desc_adic=$filas->fields['desc_adic'];?>
	   <input type='hidden' value='<?=$desc_adic?>' name='h_desc_<?=$items?>'>
	    <?
         if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	        $longitud_fila=$max_800_600;
	       elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	        $longitud_fila=$max_1024_768;
	       else//si es una resolucion mayor a 1024
	        $longitud_fila=$max_otro;
	      $texto_desc=ajustar_lineas_texto($filas->fields['descripcion_prod']." ".$desc_adic,$longitud_fila);
	    ?>
       <textarea name="desc_orig_<?=$items?>" style="width:100%" rows="<?=row_count($filas->fields['descripcion_prod'],$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" <?=$permiso ?>><?=$texto_desc?></textarea>
      </td>
	  <td align="center">
	   <input name="cant_<?=$items?>" type="text" size="6" style='text-align:right' value="<?=$filas->fields['cantidad'] ?>" <?=$readonly_especial_modif?> onchange="calcular(this);<?=$control_especial_transporte?>;" <?=$permiso ?> <?if($es_stock || $desde_presupuesto)echo "readonly"?>>
	  </td>
	  <td align="center">
	   <input name="unitario_<?=$items?>" type="text" size="10" style='text-align:right' value="<?=number_format(($filas->fields['precio_unitario'])?$filas->fields['precio_unitario']:0,2,".","") ?>" onchange="this.value=this.value.replace(',','.');calcular(this);<?=$control_especial_transporte?>;" <?=$permiso ?>>
	  </td>
	  <td align="center">
	   <input name="subtotal_<?=$items?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($filas->fields['cantidad']*$filas->fields['precio_unitario'],2,".","") ?>" <?=$permiso ?>>
       <?
       //mostramos boton (invisible) para borrar filas, solo si tiene permiso
       //y la oc esta en estado 'a', 'e', 'd' o 'g'
       if(($estado=='a' || $estado=='e' || $estado=='d' || $estado=='g') && permisos_check("inicio","permiso_borrar_fila_especial"))
       {
       	$desc_adic=ereg_replace("\r\n"," ",cortar($desc_adic,40));
       	$desc_prod=ereg_replace("\r\n"," ",cortar($filas->fields['descripcion_prod'],40));
       	?>
	    <input type="submit" name="borrar_fila_<?=$items?>" value="Del" style="visibility:hidden" class="little_boton" onclick="document.all.borrar_fila_especial.value='<?=$filas->fields['id_fila']?>';return confirm('Se va a borrar la fila con el producto \n\'<?=$desc_prod." ".$desc_adic?>\'\n ¿Está seguro que desea continuar?')">
	   <?
       }
       ?>
	  </td>
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
	  <input name="chk" type="checkbox" id="chk" value="1" <?=$permiso ?> class="estilos_check">
	 </td>
	 <input type="hidden" name="idp_<?=$items ?>" value="<?=$aprod_seg[$i]['id_producto']?>" >
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
	  <?
	  if (($_ses_user["res_height"]==600 && $_ses_user["res_width"]==800))
	   $longitud_fila=$max_800_600;
	  elseif($_ses_user["res_height"]==768 && $_ses_user["res_width"]==1024)
	   $longitud_fila=$max_1024_768;
	  else//si es una resolucion mayor a 1024
	   $longitud_fila=$max_otro;
	  $texto_desc=ajustar_lineas_texto($aprod_seg[$i]['desc'],$longitud_fila);
	  ?>
   	  <textarea wrap name="desc_<?=$items ?>" style="width:90%"  rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion" <?=$permiso ?> readonly><?=$texto_desc?></textarea><input type='button' name="desc_adic_<?=$items?>" value="E" title='Agregar descripción adicional del producto' onclick="window.open('desc_adicional.php?posicion=<?=$items?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=450,height=400')" <?=$permiso?>>
	 </td>
	 <!-- la cantidad no se puede modificar, sacar el readonly para que se pueda -->
	 <td align="center">
	  <input name="cant_<?=$items?>" readonly type="text" size="6" style='text-align:right' value="<?=$aprod_seg[$i]['cantidad_comprar'] ?>" onchange="calcular(this)" <?=$permiso ?>>
	 </td>
	 <td align="center">
	  <input name="unitario_<?=$items?>"  type="text" size="10" style='text-align:right' value="<?=number_format(($aprod_seg[$i]['unitario'])?$aprod_seg[$i]['unitario']:0,2,".","") ?>" onchange="this.value=this.value.replace(',','.');calcular(this)" <?=$permiso ?>>
	 </td>
	 <td align="center">
	  <input name="subtotal_<?=$items?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($aprod_seg[$i]['cantidad_comprar']*$aprod_seg[$i]['unitario'],2,".","") ?>" <?=$permiso ?>>
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
      <input name="chk" type="checkbox" id="chk" value="1" class="estilos_check">
     </td>
     <?
     if ($items[$i]['id_fila'])
	 {
     ?>
      <input type="hidden" name="idf_<?=$i?>" value="<?=$items[$i]['id_fila']?>">
     <?
	 }?>
     <input type="hidden" name="idp_<?=$i?>" value="<?=$items[$i]['id_producto']?>">
     <?
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
      <textarea name="desc_orig_<?=$i?>" style="width:90%" rows="<?=row_count($texto_desc,$longitud_fila)?>" wrap="VIRTUAL" id="descripcion"><?=$texto_desc?></textarea>
     </td>
     <input type='hidden' value='<?=$items[$i]['prov_prod']?>' name='idprov_<?=$i?>"'>
     <td align="center">
      <input name="cant_<?=$i?>" type="text" size="6" style='text-align:right' value="<?=$items[$i]['cantidad'] ?>" onchange="calcular(this)" <?if($es_stock)echo "readonly"?>>
     </td>
     <td align="center">
      <input name="unitario_<?=$i?>" type="text" size="10" style='text-align:right' value="<?=number_format($items[$i]['precio_unitario'],2,".","") ?>" onchange="calcular(this)">
      <input type="button" name="H_button" value="H" title="Historial del Precio" onclick="window.open('<?=$link?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=125,top=10,width=650,height=520')">
     </td>
     <td align="center">
       <input name="subtotal_<?=$i?>" type="text" size="12" readonly style='text-align:right' value="<?=number_format($items[$i]['cantidad']*$items[$i]['precio_unitario'],2,".","") ?>">
     </td>
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
  </table>
  <table width="100%">
   <tr>
  <?
  //si el estado es autorizada o enviada o parcialmente pagada
  //mostramos el boton de agregar transporte

  //solo si no se ha presionado previamente,
  //y si el usuario tiene permiso para ver el boton
  $permiso_agregar=permisos_check("inicio","permiso_agregar_transporte");

  if($permiso_agregar && (!$es_stock) && ($estado=='a' ||$estado=='e' || $estado=='d') && $transporte_agregado==0)
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
      <input type="submit" name="guardar_cambios_fila" value="Guardar Modificaciones a Filas" style="width=170;visibility='hidden'" class="little_boton" onclick="return confirm('¿Está seguro que desea guardar los cambios hechos a las filas de este Pago?')">
     <?
     }
     ?>
     <a name="#total"></a>
     <b>Total Final <? if ($orden->fields['id_moneda']==1) echo "<font color='#FF3300'>".'$'."</font>";elseif($orden->fields['id_moneda']==2) echo "<font color='#FF3300'>".'U$S'."</font>"?></b>
     <input type="text" name="total" size="12" style="text-align:right;background-color:'#DDFFDD';font-weight:'bold';" readonly value="<?=number_format($total,2,".",""); ?>" >
    </td>
   </tr>
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
/*************************************************************************************************************
 FIN DE SECCION: CONTROL INTERNO PROVEEDORES
**************************************************************************************************************/
?>
</div>
<?
/*************************************************************************************************************
 SECCION: BOTONERA
**************************************************************************************************************/
?>
<div style="background-color:<?=$bgcolor_out?>;height:75px;position:relative;width:100%" id="div_botonera">
<table width="100%" cellpadding="0" cellspacing="0" id=history class="bordessininferior">
 <tr >
  <td width="37.5%" height="20px">
   Estado: <?=$estado_nombre?>
  </td>
  <td width="25%">
   <?
   if($nro_orden!=-1)
   {?>
    Orden de Pago Nº <?=$nro_orden?>
   <?
   }
   else
   {
   	echo "Nuevo Pago";
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
   <!--<input type="button" name="volver" class="estilos_boton" value="Volver" onclick="document.location.href='<?//= $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:"ord_pago_listar.php"?>'">-->
   <input type="button" name="volver" class="estilos_boton" value="Volver" onclick="document.location.href='ord_pago_listar.php'">
   <?
   if(permisos_check("inicio","permiso_boton_anular_oc_pagadas") && $estado=='g' && ($orden_prod || $caso) && $es_stock)
   {?>
    <input type="submit" class="estilos_boton" name="anular_con_stock" value="Anular OC" onclick="if(confirm('Si anula esta Pago, todas las entregas/recepciones realizadas se volverán para atrás.\n¿Está seguro que desea anularla?'))return true; else return false;">
   <?
   }
   ?>
   <input name="boton_anular" <?/*boton 0*/?> type="button" class="estilos_boton" value="Anular" <?= $permisos_b['anular'] ?> <?=$disabled_recib?>
    onclick="
    //si confirma la anulacion de la orden debe justificar el porque
    if (confirm('¿Está seguro que desea anular la Orden?'))
     window.open('<?=encode_link("comentario_anulacion.php",array("nro_orden"=>$nro_orden,"tipo"=>"anular"))?>','','toolbar=1,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300');"
   >
   &nbsp;
   <input name="boton_eliminar" <?/*boton 1*/?> type="button" class="estilos_boton" value="Eliminar" <? if (!$items) echo " disabled "; else echo $permisos_b['eliminar']; ?>
    onclick="
     if (confirm('¿Está seguro que desea eliminar los items seleccionados ?'))
	  borrar_items()
	"
   >
   <?
   if (($gastos_servicio_tecnico || $flag_honorario ||$caso ||$id_renglon_prop)&&!$es_stock)
    $disabled_agregar="disabled";
   else
    $disabled_agregar="";

   if(permisos_check("inicio","oc_para_autorizar"))
     $disabled_para_autorizar_oc="";
   else
     $disabled_para_autorizar_oc="disabled";
   ?>
   &nbsp;<input name="boton_agregar" <?/*boton 2*/?> type="button" class="estilos_boton" value="Agregar" <?=$permisos_b['agregar'] ?> <?=($desde_presupuesto)?"disabled":""?> <?= ($select_proveedor && $select_proveedor!=-1)?"":"disabled" ?> <?=$disabled_agregar?> onclick="cargar()">
   &nbsp;<input name="boton_guardar" <?/*boton 3*/?> type="submit" class="estilos_boton" value="Guardar" <?=$permisos_b['guardar'] ?> onclick="if (chk_campos()) { alert (msg); return false;}">
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
  /* if (compara_fechas(fecha_db($fecha_entrega),date("Y-m-d"))==0)
    $confirmar = "if (confirm('Este pago tiene fecha de entrega hoy mismo,esto puede generar incovenientes en el area de logistica. Por favor cambie de estado a para autorizar solo en caso de urgencia de lo contrario modifique la fecha de entrega ¿Esta seguro que desea cambiar el estado a para autorizar?')){";
   else */
    $confirmar = "";
  ?>
  &nbsp; <input name="boton_para_autorizar"  type="submit" class="estilos_boton" value="Para Autorizar" <?=$permisos_b['por_autorizar'] ?> <?=$disabled_para_autorizar_oc?> style="width:90px"
          onclick="
           <?=$confirmar;?>
           document.all.guardar.value=1;
           if (chk_campos())
           {alert (msg);
            return false;
           }
           else
        		return true;
           <?=($confirmar!="")?"}else return false;":"";?>
          "
        >
        <input type="hidden" name="contenido" value="">
        &nbsp;<input name="boton_ pagar" <?/*boton 5*/?> type="button" class="estilos_boton" value="Pagar" <?=$disabled_por_stock?> style="width:65px" <?=$permisos_b['pagar'] ?>
               onclick="
                if (wpagar==0 || wpagar.closed)
	             wpagar=window.open('<?=encode_link('ord_pago_pagar.php',array('nro_orden'=>$nro_orden)) ?>','','toolbar=0,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=0,top=0,width=800,height=500');
                else if (!wpagar.closed)
	              wpagar.focus();
               "
	          >
        &nbsp;<input name="boton_recepcion" <?/*boton 6*/?> type="button" class="estilos_boton" style="width:120px" title="Agregue las facturas y archivos que sean necesarios" value="Facturas/Archivos" <?=$permisos_b['terminar']?> onclick="document.location.href='<?=encode_link("ord_pago_recepcion.php",array('nro_orden'=>$nro_orden,'mostrar_dolar'=>$mostrar_dolar));?>'">
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

	$sql="select user_login from log_ordenes where nro_orden=$nro_orden ";
	$sql.=" and tipo_log='de creacion'";
	$result=$db->execute($sql) or die($db->errormsg()."<br>".$sql);
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
    //si la orden no tiene ningun pago realizado, mostramos el
    //boton de habilitar pagos especiales
    if (/*($orden->fields['estado']!='d')&&*/($orden->fields['estado']!='g')&&$orden->fields['habilitar_pago_especial']==0 && (permisos_check("inicio","boton_habilitar_pago_especial")))
    {?>
     <input name="boton_habilitar_pago_especial" <?/*boton 7*/?> type="submit" class="estilos_boton"  value="Habilitar Pago Especial" <?=$disabled_por_stock?> <?=$permiso_habilitar?> <?=$anulada?> style="width:170px">
    <?
    }
    elseif (($orden->fields['estado']!='d')&& ($orden->fields['estado']!='g')&&permisos_check("inicio","boton_habilitar_pago_especial"))
    {?>
     <input name="boton_deshabilitar_pago_especial" <?/*boton 8*/?> type="submit" class="estilos_boton"  value="Deshabilitar Pago Especial"  style="width:170px">
    <?
    }

    if (($orden->fields['estado']=='d')||($orden->fields['estado']=='g')||($orden->fields['estado']=='e')||($orden->fields['estado']=='t'))
    {
     if ($tipo_moneda!='Dólares') $tipo_moneda="";
      $link=encode_link("./ord_pago_resumen_pagos.php",array("nro_orden"=>$nro_orden,"moneda"=>$tipo_moneda,"pagina"=>"ord_compra"));
     ?>
     <input name="boton_resumen_pagos" <?/*boton 9*/?> type="button" class="estilos_boton"  value="Resumen de Pagos"  style=";width:100" <?=$disabled_por_stock?> onclick="window.open('<?=$link;?>','','toolbar=0,location=0,directories=0,status=0, menubar=0,scrollbars=1,left=0,top=0,width=800,height=500');">
    <?
    }//de  if (($orden->fields['estado']=='d')||($orden->fields['estado']=='g')||($orden->fields['estado']=='e')||($orden->fields['estado']=='t'))
    ?>
    <input name="boton_rechazar" <?/*boton 10*/?> type="button" class="estilos_boton"  value="Rechazar" <?=$disabled_rechazar?> <?=$disabled_recib?> style="width:70px"
     onClick="
        //si confirma el rechazo de la orden debe justificar el porque
        if (confirm('¿Está seguro que desea rechazar la Orden?'))
         window.open('<?=encode_link("comentario_anulacion.php",array("nro_orden"=>$nro_orden,"tipo"=>"rechazar"))?>','','toolbar=1,location=0,directories=0,status=1, menubar=0,scrollbars=1,left=125,top=10,width=600,height=300');
      "
    >
    <?
    //chequeamos si el usuario que se ha logueado, tiene permiso
    //para ver el boton de autorizar o no. Si tiene lo mostramos, sino ,no.
    if (!permisos_check("inicio","aut_ord_compra"))
      $autorizar=" disabled ";

    //Control de fecha de entrega y dia actual
  /*  if (compara_fechas(fecha_db($fecha_entrega),date("Y-m-d"))==0)
    {$confirmar = '
        if (confirm("Este pago tiene fecha de entrega hoy mismo, esto puede generar incovenientes en el area de logistica. Por favor cambie de estado a autorizada solo en caso de urgencia, de lo contrario modifique la fecha de entrega. ¿Está seguro que desea autorizar?"))
        {';
    }
    else */
     $confirmar = "";
    ?>
	&nbsp;<input name="boton_autorizar" type="submit" class="estilos_boton"  <?=$permisos_b['autorizar']?> value="Autorizar" <?=$autorizar ?> style="width:70px"
           onclick='
            <?=$confirmar?>
             document.all.guardar.value=1;
             if (chk_campos())
             {
              alert (msg);
              return false;
             }
             else
              return control_pagos();
           '
          >
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
         &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <?
       /********************   Busco seguimiento si existe   ******************/
       //falta
       /********************   Busco entrega si existe   ******************/
		 //falta
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
</div>
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
 <input type="hidden" name="nro_orden" value="<?=($nro_orden && $nro_orden!=-1)?$nro_orden:-1 ?>">
 <input type="hidden" name="id_cliente" value="0">
 <input type="hidden" name="back_page" value="<?= $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:"ord_pago_listar.php"?>" />
 <!--
 <input type="hidden" name="id_entidad" value="<? if ($_POST['id_entidad']) echo $_POST['id_entidad'];elseif ($orden->fields['id_entidad'] !='NULL') echo $orden->fields['id_entidad'];else echo 0; ?>">
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


 function fix_size() {
  //dependiendo del largo del formulario, seteamos el largo del div del formulario
 var largo_form=parseInt((parent.document.getElementById('frame2'))?parent.document.getElementById('frame2').clientHeight:document.body.clientHeight)-parseInt(document.getElementById('div_botonera').style.height);
  document.getElementById('div_formulario').style.height=largo_form+"px";

}
fix_size();
document.body.onresizeend=function (){fix_size()};
</script>
</body>
</html>
