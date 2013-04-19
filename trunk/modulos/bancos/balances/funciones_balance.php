<?php
/*
$Author: fernando $
$Revision: 1.25 $
$Date: 2007/02/01 16:14:52 $
*/

//variables

$cuentas=array();

$cuentas_activo[]=array("nombre"=>"Cuentas a Cobrar","pesos"=>"cuentas_a_cobrar_pesos","dolar"=>"cuentas_a_cobrar_dolares");
$cuentas_activo[]=array("nombre"=>"Bancos","pesos"=>"bancos_pesos","dolar"=>"");
$cuentas_activo[]=array("nombre"=>"Stock","pesos"=>"stock_pesos","dolar"=>"stock_dolares");
$cuentas_activo[]=array("nombre"=>"Adelantos","pesos"=>"adelantos_pesos","dolar"=>"adelantos_dolares");
$cuentas_activo[]=array("nombre"=>"Bienes de Uso","pesos"=>"bienes_de_uso_pesos","dolar"=>"bienes_de_uso_dolares");
$cuentas_activo[]=array("nombre"=>"Caja","pesos"=>"caja_pesos","dolar"=>"caja_dolares");
$cuentas_activo[]=array("nombre"=>"Depositos Pendientes","pesos"=>"depositos_pendientes_pesos","dolar"=>"depositos_pendientes_dolares");
$cuentas_activo[]=array("nombre"=>"Cheques Diferidos","pesos"=>"cheques_diferidos_pendientes_pesos","dolar"=>"cheques_diferidos_pendientes_dolares");
$cuentas_activo[]=array("nombre"=>"Saldo Libre Disponibilidad","pesos"=>"saldo_libre_disponibilidad","dolar"=>"");
$cuentas_activo[]=array("nombre"=>"SUSS","pesos"=>"suss","dolar"=>"");

$cuentas_pasivo[]=array("nombre"=>"Cheques","pesos"=>"cheques_pendientes_pesos","dolar"=>"cheques_pendientes_dolares");
$cuentas_pasivo[]=array("nombre"=>"Deuda Comercial","pesos"=>"deuda_comercial_pesos","dolar"=>"deuda_comercial_dolares");
$cuentas_pasivo[]=array("nombre"=>"Deuda Financiera","pesos"=>"deuda_financiera_pesos","dolar"=>"deuda_financiera_dolares");




//funcion para el historial, me recupera los datos de una fecha determinad y me devuelve un arreglo
//con los datos de la consulta
function obtener_datos($fecha,$h='',$flag=0)
{
   global $mensaje,$bgcolor_aux,$bgcolor_aux_comparacion,$hora,$hora_comparacion;

   if (!$h)  $h=date("G").":00:00";

   $where=" where fecha='$fecha $h'";

   $sql=" select * from balance_historial $where order by fecha limit 1";
   $balance_historial=sql($sql) or fin_pagina();
   $id_balance_historial=$balance_historial->fields["id_balance_historial"];
   //este if lo coloco para que si es muy vieja la fecha pueda traer aunque sea la foto diaria
   if (!$balance_historial->recordcount()){

               $where=" where fecha>='$fecha 00:00:00' and fecha<='$fecha $h'";
               $sql=" select * from balance_historial $where order by fecha DESC";

               $balance_historial=sql($sql) or fin_pagina();
               $fecha_obtuvo=$balance_historial->fields["fecha"];
               $fecha_obtuvo=fecha($fecha_obtuvo);



               $id_balance_historial=$balance_historial->fields["id_balance_historial"];

               if ($id_balance_historial)
               {
                 if ($flag)
                           {
                            //$hora_obtuvo_comparacion=substr($balance_historial->fields["fecha"],10,9);
                            $hora_comparacion=substr($balance_historial->fields["fecha"],10,9);
                            $bgcolor_aux_comparacion=" bgcolor='#FFFF80'";
                            }
                            else
                            {
                            $hora=substr($balance_historial->fields["fecha"],10,9);
                            $bgcolor_aux="bgcolor='#FFFF80'";
                            }

               }
    } // del if que trae el balance historial mas proximo a la fecha que uno eligio


   //aca recupero los datos del historial
   if ($balance_historial->recordcount()){

               $sql=" select detalle_balance_historial.*,tipo_cuenta_balance.nombre as tipo_cuenta
                      from detalle_balance_historial
                      join tipo_cuenta_balance using(id_tipo_cuenta_balance)
                       where id_balance_historial=$id_balance_historial";
              $res_detalle_balance=sql($sql) or fin_pagina();


              //armos arreglo para detalle balance
              $detalle_balance=array();
              for($i=0;$i<$res_detalle_balance->recordcount();$i++){
                    $id_detalle_balance_historial=$res_detalle_balance->fields["id_detalle_balance_historial"];
                    $nombre=$res_detalle_balance->fields["nombre"];
                    $monto=formato_money($res_detalle_balance->fields["monto"]);
                    $id=$res_detalle_balance->fields["id_tipo_cuenta_balance"];
                    $moneda=$res_detalle_balance->fields["moneda"];

                    $detalle_balance[]=array("id_detalle_balance_historial"=>$id_detalle_balance_historial,"nombre"=>$nombre,"id"=>$id,"monto"=>$monto,"moneda"=>$moneda);
                    $res_detalle_balance->movenext();
              }

              $datos=array();


              $datos["valor_dolar"]=$balance_historial->fields["valor_dolar"];

              $datos["cuentas_a_cobrar_dolares"]=$balance_historial->fields["cuentas_a_cobrar_dolares"];
              $datos["cuentas_a_cobrar_pesos"]=$balance_historial->fields["cuentas_a_cobrar_pesos"];

              $datos["bancos_pesos"]=$balance_historial->fields["bancos_pesos"];
              $datos["bancos_dolares"]=$balance_historial->fields["bancos_dolares"];

              $datos["stock_pesos"]=$balance_historial->fields["stock_pesos"];
              $datos["stock_dolares"]=$balance_historial->fields["stock_dolares"];


              $datos["adelantos_pesos"]=$balance_historial->fields["adelantos_pesos"];
              $datos["adelantos_dolares"]=$balance_historial->fields["adelantos_dolares"];

              $datos["caja_pesos"]=$balance_historial->fields["caja_pesos"];
              $datos["caja_dolares"]=$balance_historial->fields["caja_dolares"];

              $datos["depositos_pendientes_pesos"]=$balance_historial->fields["depositos_pendientes_pesos"];
              $datos["depositos_pendientes_dolares"]=$balance_historial->fields["depositos_pendientes_dolares"];

              $datos["cheques_diferidos_pendientes_pesos"]=$balance_historial->fields["cheques_diferidos_pendientes_pesos"];
              $datos["cheques_diferidos_pendientes_dolares"]=$balance_historial->fields["cheques_diferidos_pendientes_dolares"];


              $datos["cheques_pendientes_pesos"]=$balance_historial->fields["cheques_pendientes_pesos"];
              $datos["cheques_pendientes_dolares"]=$balance_historial->fields["cheques_pendientes_dolares"];

              $datos["deuda_comercial_pesos"]=$balance_historial->fields["deuda_comercial_pesos"];
              $datos["deuda_comercial_dolares"]=$balance_historial->fields["deuda_comercial_dolares"];

              $datos["deuda_financiera_pesos"]=$balance_historial->fields["deuda_financiera_pesos"];
              $datos["deuda_financiera_dolares"]=$balance_historial->fields["deuda_financiera_dolares"];

              $datos["bienes_de_uso_pesos"]=$balance_historial->fields["bienes_de_uso_pesos"];
              $datos["bienes_de_uso_dolares"]=$balance_historial->fields["bienes_de_uso_dolares"];


              $datos["saldo_libre_disponibilidad"]=$balance_historial->fields["saldo_libre_disponibilidad"];
              $datos["suss"]=$balance_historial->fields["suss"];              
              $datos["detalle_balance"]=$detalle_balance;
   }
    return $datos;
}// de la functin obtener_datos()



//Tiene que incluirse el config o definirse las variables para que este modulo pueda andar

function total_caja($id_distrito,$id_moneda,$fecha_hasta) {
       	global $_ses_user_login,$db;
        $sql="select cerrada,saldo_total from caja.caja where id_distrito=$id_distrito and id_moneda=$id_moneda and fecha='$fecha_hasta'";
        $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
        $cerrada=$res->fields['cerrada'];

        if ($cerrada==1)  $saldo=$res->fields['saldo_total'];

                    else
                      {
                      $sql="select saldo_total,fecha
                     	     from caja.caja join
                             (select max(id_caja) as id_caja
                                            from caja.caja
                    	                    where cerrada=1 and id_moneda=$id_moneda and id_distrito=$id_distrito ) as c
                    		  using (id_caja)";
                         $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
                    	 $saldo_parcial=$res->fields['saldo_total'];
                    	 $fecha_caja_cerrada=$res->fields['fecha'];


                    	 $sql="select sum(monto) as ing from caja.caja join
                    		  caja.ingreso_egreso using (id_caja)
                              where id_tipo_egreso isnull
                              and  fecha > '$fecha_caja_cerrada' and fecha <= '$fecha_hasta' and id_moneda=$id_moneda and id_distrito=$id_distrito";
                    	 $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
                    	 $ingresos=$res->fields['ing'];

                    	 $sql="select sum(monto) as eg from caja.caja join
                    		  caja.ingreso_egreso using (id_caja)
                              where id_tipo_ingreso isnull
                              and  fecha > '$fecha_caja_cerrada' and fecha <= '$fecha_hasta' and id_moneda=$id_moneda and id_distrito=$id_distrito";
                    	 $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
                    	 $egresos=$res->fields['eg'];
                    	 $saldo=$saldo_parcial + ($ingresos - $egresos);

                    }

        	 return $saldo;
}   //de la funcion

 function proximo_id_detalle(){
      global $db;

      $sql="select nextval('bancos.detalle_balance_historial_id_detalle_balance_historial_seq') as id_detalle_balance_historial";
      $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
      return  $res->fields["id_detalle_balance_historial"];
 }


  //  $id_detalle_balance_historial: es el id del detalle_balance_historial
  //$datos: es un arreglo con los datos generales

 function insertar_items_balance($id_detalle_balance_historial,$datos){
     global $db;


     $descripcion=$datos["descripcion"];
     $monto=$datos["monto"];
     $id_licitacion=$datos["id_licitacion"];
     $id_cobranza=$datos["id_cobranza"];
     $nro_factura=$datos["nro_factura"];
     $nro_orden=$datos["nro_orden"];
     $cantidad=$datos["cantidad"];
     $moneda=$datos["moneda"];

     $sql="select nextval('bancos.items_detalle_balance_id_items_detalle_balance_seq') as id_items_detalle_balance";
     $res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
     $id_items_balance=$res->fields["id_items_detalle_balance"];


     $campos= "id_items_detalle_balance,id_detalle_balance_historial,";
     $campos.="descripcion,monto,cantidad,moneda,id_licitacion,id_cobranza,nro_factura,nro_orden";

     $values="$id_items_balance,$id_detalle_balance_historial,";
     ($descripcion)?$values.="'$descripcion',":$values.="'',";
     ($monto)?$values.="$monto,":$values.="0,";
     ($cantidad)?$values.="$cantidad,":$values.="0,";
     ($moneda)?$values.="'$moneda',":$values.="null,";
     ($id_licitacion)?$values.="$id_licitacion,":$values.="null,";
     ($id_cobranza)?$values.="$id_cobranza,":$values.="null,";
     ($nro_factura)?$values.="$nro_factura,":$values.="null,";
     ($nro_orden)?$values.="$nro_orden":$values.="null";

     $sql="insert into items_detalle_balance ($campos) values ($values)";
     $db->execute($sql) or  die($db->errormsg()." <br> $sql<br>");


 }   //de la function insertar_items_balance





//Devuelve la consulta y los datos de adelantos


function  sql_adelantos($resultado=0){
global $db;

$sql="
 --obtengo los porcentajes correspondientes de los montos pagados

select  nro_orden,total_recibido,monto_oc,(monto_oc  - total_recibido) as total,id_moneda,id_licitacion,
        razon_social,simbolo
from (

select ordenes.nro_orden ,ordenes.id_moneda,ordenes.valor_dolar,ordenes.id_licitacion,
       ((ordenes.total_por_orden*100)/ case when total_pagar.total is null or total_pagar.total=0 then 1 else total_pagar.total end) as porcentaje,
      (
       ((ordenes.total_por_orden*100)/ case when total_pagar.total is null or total_pagar.total=0 then 1 else total_pagar.total end) * total_pagado.total)/100 as monto_oc
  	,recibidos.total_recibido,ordenes.razon_social,ordenes.simbolo


from
(
	select sum(precio_unitario*cantidad)  as total_por_orden,id_plantilla_pagos,oc.id_moneda,oc.nro_orden,oc.valor_dolar,oc.id_licitacion,
    proveedor.razon_social,simbolo
	from compras.orden_de_compra as oc
	join compras.fila using(nro_orden)
    join general.proveedor using (id_proveedor)
    join licitaciones.moneda using (id_moneda)
	where estado<>'n' and  ord_pago is null
	group by oc.nro_orden,id_plantilla_pagos,id_moneda,oc.valor_dolar,oc.id_licitacion,proveedor.razon_social,simbolo
	order by oc.nro_orden DESC
) as ordenes
join
(
	select sum(precio_unitario*cantidad)  as total,id_plantilla_pagos,oc.id_moneda
	from compras.orden_de_compra as oc
	join compras.fila using(nro_orden)
        join compras.plantilla_pagos using (id_plantilla_pagos)
	where estado<>'n' and mostrar=0
	group by id_plantilla_pagos,id_moneda
	order by id_plantilla_pagos DESC
) as total_pagar using (id_plantilla_pagos)

join
(
        select sum(op.monto) as total, id_plantilla_pagos
        from compras.pago_plantilla
        join compras.forma_de_pago using (id_forma)
        join compras.ordenes_pagos op using (id_forma)
        where  ( (not op.\"númeroch\" is null) or (not op.\"iddébito\" is null) or (not id_ingreso_egreso is null))
        group by id_plantilla_pagos
        order by id_plantilla_pagos
) as total_pagado using (id_plantilla_pagos)

join
     (
       select sum(fila.cantidad) as cantidad, oc.nro_orden ,proveedor.razon_social,oc.id_licitacion
              from compras.orden_de_compra oc
              join licitaciones.licitacion using(id_licitacion)
              join licitaciones.estado using(id_estado)
              join general.proveedor using (id_proveedor)
              join compras.fila using(nro_orden)
              where  (oc.estado='g' or oc.estado='d')
                       and estado.nombre<>'Entregada'
                       and (estado.nombre='En curso' or estado.nombre='Presuntamente ganada' or estado.nombre='Preadjudicada' or estado.nombre='Orden de compra')
                       and   proveedor.razon_social not  ilike '%stock%'
                       and  fila.es_agregado=0

               group by nro_orden,proveedor.razon_social,oc.id_licitacion

               --traigo las ordenes de compra tipo stock
               union
               select sum(fila.cantidad) as cantidad, oc.nro_orden,proveedor.razon_social ,oc.id_licitacion
                     from compras.orden_de_compra oc
                     join general.proveedor using (id_proveedor)
                     join compras.fila using(nro_orden)
                     where  (oc.estado='g' or oc.estado='d')
                               and   proveedor.razon_social not  ilike '%stock%'
                               and  fila.es_agregado=0
                               and flag_stock=1

               group by nro_orden,proveedor.razon_social, oc.nro_orden,proveedor.razon_social ,oc.id_licitacion
               union
               select sum(fila.cantidad) as cantidad, oc.nro_orden,proveedor.razon_social ,oc.id_licitacion
                     from compras.orden_de_compra oc
                     join general.proveedor using (id_proveedor)
                     join compras.fila using(nro_orden)
                     where  (oc.estado='g' or oc.estado='d')
                               and   proveedor.razon_social not  ilike '%stock%'
                               and  fila.es_agregado=0
                               and  internacional=1

               group by nro_orden,proveedor.razon_social, oc.nro_orden,proveedor.razon_social ,oc.id_licitacion


            ) as filas using (nro_orden)
             left join
	    (
              --obtengo cuandos productos se recibieron de la oc
		      select sum(case when ent_rec is null then 0 else recibido_entregado.cantidad end ) as cantidad_rec,
                     --(sum(case when ent_rec is null then 0 else recibido_entregado.cantidad end *fila.precio_unitario * case when oc.id_moneda=2 then oc.valor_dolar else 1 end)) as total_recibido,
                       (sum(case when ent_rec is null then 0 else recibido_entregado.cantidad end *fila.precio_unitario)) as total_recibido,
                     oc.nro_orden
	       from compras.orden_de_compra oc
	       join compras.fila using(nro_orden)
	       left join compras.recibido_entregado using(id_fila)
               where (ent_rec=1 or ent_rec is null)
	       group by oc.nro_orden order by nro_orden
	     ) as recibidos using (nro_orden)
where recibidos.cantidad_rec<filas.cantidad
order by nro_orden DESC
) as principal
where monto_oc>total_recibido
";


if ($resultado) {
   $array=array();
   $datos=array();
   $monto=0;
   $res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);

   for($i=0;$i<$res->recordcount();$i++){

       $datos[]=array("nro_orden"=>$res->fields["nro_orden"],
                      "monto"=>$res->fields["total"],
                      "moneda"=>$res->fields["simbolo"],
                      "id_moneda"=>$res->fields["id_moneda"],
                      "id_licitacion"=>$res->fields["id_licitacion"],
                      "razon_social"=>$res->fields["razon_social"]);
       if ($res->fields["id_moneda"]==1)
                $monto_pesos+=$res->fields["total"];
                else
                $monto_dolares+=$res->fields["total"];

       $res->movenext();
       }

   }
 $array["sql"]=$sql;
 $array["monto_pesos"]=$monto_pesos;
 $array["monto_dolar"]=$monto_dolares;
 $array["datos"]=$datos;


 return $array;
} //de la funcin de adelentos



function sql_deuda_comercial($internacional=0,$resultado=0){
global $db;
if ($internacional==1)
{

     $sql="
    select (total_recibido) as monto,id_moneda,nro_orden,razon_social,id_licitacion,simbolo
     from (

            select
                    COALESCE(recibidos.total_recibido,0) as  total_recibido,
                    sum(COALESCE(pagos.total,0)) as total_pagos ,
                    COALESCE(cantidad_pagos.cantidad,0) as cantidad_pagos,
                    oc.id_moneda,razon_social,oc.id_licitacion,
                    oc.nro_orden,cantidad_filas.total as cantidad_filas,montos_filas.total_filas,
                    moneda.simbolo
                    from
                    compras.orden_de_compra oc
                    join general.proveedor using (id_proveedor)
                    join moneda using (id_moneda)
                    join
                    (
                        select  sum(fila.cantidad) as total ,
                                oc.nro_orden
		                        from compras.orden_de_compra oc
		                        join compras.fila using (nro_orden)
		                        where internacional=1
		                        group by oc.nro_orden order by oc.nro_orden
                    ) as cantidad_filas on(oc.nro_orden=cantidad_filas.nro_orden and oc.internacional=1)

                    join
                    (
                    select  oc.nro_orden,sum(fila.precio_unitario*fila.cantidad) as total_filas_sin_gastos,
                            sum((fila.precio_unitario*fila.cantidad) + fila.iva + fila.ib + derechos) as total_filas,monto_flete ,
                            honorarios_gastos, oc.id_moneda
                            from compras.orden_de_compra oc
                            join compras.datos_oc_internacional using (nro_orden)
                            join compras.fila using(nro_orden)

                            where fila.es_agregado=0     and internacional=1
                            group by oc.nro_orden,oc.id_moneda,monto_flete , honorarios_gastos
                            order by nro_orden
                    ) as montos_filas  on (montos_filas.nro_orden=oc.nro_orden)
                    left join
                    (
                     select   sum(
                              case when ent_rec is null then 0 else recibido_entregado.cantidad end ) as cantidad_recibido,
                             (sum(case when ent_rec is null then 0 else recibido_entregado.cantidad end *fila.precio_unitario)) as total_recibido,
                             oc.nro_orden
		                 from compras.orden_de_compra oc
	                     join compras.fila using(nro_orden)
	                     join compras.recibido_entregado using(id_fila)
	                     where recibido_entregado.ent_rec=1   and fila.es_agregado=0 and internacional=1
	                     group by oc.nro_orden order by nro_orden
	                 ) as recibidos on (oc.nro_orden=recibidos.nro_orden)
                     left  join
                     (
                     --obtengo cuanto pague de la oc
	                 select sum(op.monto) as total, op.id_pago,pago_orden.nro_orden
	                        from
                            compras.orden_de_compra oc
                            join  compras.pago_orden using (nro_orden)
                            join  compras.ordenes_pagos op using (id_pago)
	                        where  internacional=1
                                   and ( (not op.\"númeroch\" is null) or (not op.\"iddébito\" is null) or (not id_ingreso_egreso is null))
	                        group by op.id_pago,pago_orden.nro_orden order by pago_orden.nro_orden
                      ) as  pagos on (pagos.nro_orden=oc.nro_orden)
                      left join
                      (
                      --obtengo cuandos pagos multiples esta la oc
                      select count(pago_orden.id_pago) as cantidad, pago_orden.id_pago
	                         from
		                     compras.orden_de_compra oc
                             join compras.pago_orden on (oc.nro_orden=pago_orden.nro_orden)
		                     join compras.ordenes_pagos op using(id_pago)
		                     where internacional=1
                                   and ( (not op.\"númeroch\" is null) or (not op.\"iddébito\" is null) or (not id_ingreso_egreso is null))
		                     group by pago_orden.id_pago
	                   ) as cantidad_pagos using(id_pago)
           group by oc.id_moneda,razon_social,oc.id_licitacion,
                    oc.nro_orden,cantidad_filas,montos_filas.total_filas,total_recibido,cantidad_pagos.cantidad,moneda.simbolo
         ) as principal

         ";
 $where ="  total_recibido > total_pagos and total_recibido <=total_filas ";
}
else{
    $sql="
         select sum(total_recibido -total_pagos) as monto,  id_moneda ,nro_orden,razon_social,id_licitacion,simbolo
                from (
                        select recibidos.total_recibido ,
                               COALESCE(sum(pagos.total/cantidad_pagos.cantidad),0) as total_pagos,
                               oc.nro_orden,oc.id_moneda,oc.id_licitacion,
                               razon_social,simbolo
                               from
                               compras.orden_de_compra oc
                               join moneda using(id_moneda)
                               join general.proveedor using(id_proveedor)
                               join
                               (
                               select sum(fila.cantidad) as cantidad,sum(fila.precio_unitario*fila.cantidad) as total , oc.nro_orden,oc.id_moneda
                                                                              from compras.orden_de_compra oc
                                       join compras.fila using(nro_orden)
                                       where fila.es_agregado=0
                                       and internacional=0
                                       group by nro_orden,id_moneda
                               ) as filas using(nro_orden)
                               left join
	                           (
	                           select sum(
                                          case when ent_rec is null then 0 else recibido_entregado.cantidad end ) as cantidad,
                                          (sum(case when ent_rec is null then 0 else recibido_entregado.cantidad end *fila.precio_unitario)) as total_recibido,
                                          oc.nro_orden
	                                      from compras.orden_de_compra oc
	                                      join compras.fila using(nro_orden)
	                                      join compras.recibido_entregado using(id_fila)
	                                      where recibido_entregado.ent_rec=1 and internacional=0
                                          and fila.es_agregado=0
	                                      group by oc.nro_orden order by nro_orden
	                           ) as recibidos
                               using (nro_orden)
                               --obtengo cuanto pague de la oc
                               left  join
                               (
                               select sum(op.monto) as total, op.id_pago,pago_orden.nro_orden
	                                      from
                                          compras.pago_orden join
	                                      compras.ordenes_pagos op using (id_pago)
	                                      where ( (not op.\"númeroch\" is null) or (not op.\"iddébito\" is null) or (not id_ingreso_egreso is null))
	                                      group by op.id_pago,nro_orden order by nro_orden
                               ) as  pagos using (nro_orden)
                               left  join
                               (
                               --obtengo cuandos pagos multiples esta la oc
                               select count(pago_orden.id_pago) as cantidad, pago_orden.id_pago
	                                   from
		                               compras.orden_de_compra oc
		                               join compras.pago_orden on (oc.nro_orden=pago_orden.nro_orden)
		                               join compras.ordenes_pagos op using(id_pago)
		                               where ( (not op.\"númeroch\" is null) or (not op.\"iddébito\" is null) or (not id_ingreso_egreso is null))
		                               group by pago_orden.id_pago
	                           ) as cantidad_pagos using(id_pago)


                       WHERE   recibidos.cantidad<= filas.cantidad and   recibidos.cantidad > 0 and oc.estado<>'n'  and oc.estado<>'g'
                       group by oc.nro_orden,oc.id_licitacion,oc.id_moneda,razon_social,recibidos.total_recibido,simbolo
        ) as principal";
   $where=" total_recibido > total_pagos
            group by  id_moneda ,nro_orden,razon_social,id_licitacion,simbolo";

}



$array=$datos=array();
$monto_pesos=$monto_dolar=0;

if ($resultado) {

   $res=$db->execute($sql." where ".$where) or die($db->errormsg()." <br> ".$sql);

   for($i=0;$i<$res->recordcount();$i++){

        if ($res->fields["id_moneda"]==1)
                             $monto_pesos+=$res->fields["monto"];

        if ($res->fields["id_moneda"]==2)
                             $monto_dolar+=$res->fields["monto"];

        $datos[]=array("nro_orden"=>$res->fields["nro_orden"],
                       "monto"=>$res->fields["monto"],
                       "id_moneda"=>$res->fields["id_moneda"],
                       "moneda"=>$res->fields["simbolo"],
                       "id_licitacion"=>$res->fields["id_licitacion"],
                       "razon_social"=>$res->fields["razon_social"],
                       );

       $res->movenext();
       }   //del for
   }

   $array["monto_pesos"]=$monto_pesos;
   $array["monto_dolar"]=$monto_dolar;
   $array["datos"]=$datos;
   $array["sql"]=$sql;
   $array["where"]=$where;

return $array;

} // de la function sql de deuda comercial



function sql_cuentas_a_cobrar($resultado=0,$id_moneda=-1){

global $db;



$sql=" 
select p.id_cobranza,id_licitacion,nro_factura,id_moneda,simbolo,total,pagos.monto_ingresos,
        (total - COALESCE ( CASE WHEN  pagos.monto_ingresos isnull then pagos_atadas.monto else pagos.monto_ingresos end, 0) ) as monto
       --  ,pagos_atadas.monto as monto_pago_atadas
 from (
         select cobranzas.id_licitacion,cobranzas.id_cobranza,cobranzas.nro_factura,
                cobranzas.id_moneda,moneda.simbolo,sum(cobranzas.monto) as total
                from licitaciones.cobranzas
                left join facturacion.facturas using (id_factura)
                join licitaciones.moneda on cobranzas.id_moneda=moneda.id_moneda
                where cobranzas.estado='PENDIENTE'
                      and ((facturas.estado<>'A' and facturas.estado<>'a')or facturas.estado isnull)
                      and licitacion_entregada=1 and renglones_entregados=1
                group by cobranzas.id_licitacion,cobranzas.id_cobranza,cobranzas.nro_factura,
                         cobranzas.id_moneda,moneda.simbolo
          union
          select cobranzas.id_licitacion,cobranzas.id_cobranza,cobranzas.nro_factura,
	         cobranzas.id_moneda,moneda.simbolo,sum(cobranzas.monto) as total
	         from licitaciones.cobranzas
	         join licitaciones.moneda using (id_moneda)
	         left join facturacion.facturas using (id_factura)
	         where cobranzas.estado='PENDIENTE'
                       and ((facturas.estado<>'A' and facturas.estado<>'a')or facturas.estado isnull)
                       and cobranzas.id_licitacion isnull
                group by cobranzas.id_licitacion,cobranzas.id_cobranza,
                         cobranzas.nro_factura,cobranzas.id_moneda,moneda.simbolo

) as p
left join
(
  select c.id_cobranza,sum(di.monto_ingreso) as monto_ingresos
          from licitaciones.cobranzas c
          join licitaciones.datos_ingresos using (id_datos_ingreso)
          join licitaciones.pagos_ingreso using (id_datos_ingreso)
          join licitaciones.detalle_ingresos di using (id_detalle_ingreso)
  where c.estado='PENDIENTE'
  group by c.id_cobranza
)as pagos on (pagos.id_cobranza = p.id_cobranza)

left join
(
 select sum(pa.monto * case when pa.valor_dolar=0 then 1 else pa.valor_dolar end ) as monto, vfa.id_cobranza 
       from 
       licitaciones_datos_adicionales.venta_fac_atadas vfa
       join licitaciones_datos_adicionales.pagos_atadas pa on vfa.id_vta_atada=pa.id_vta_atada
       group by vfa.id_cobranza

) as pagos_atadas on pagos_atadas.id_cobranza= p.id_cobranza
";

if ($id_moneda && $id_moneda!=-1)
          $where.=" id_moneda=$id_moneda";

 $datos=array();
 if ($resultado){
         if ($where)
         $res=$db->execute($sql." where ".$where) or  die($db->errormsg()." <br> ".$sql);
         else
         $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);



        for($i=0;$i<$res->recordcount();$i++){   
            $datos[]=array("id_cobranza"  => $res->fields["id_cobranza"],
                           "monto"        => $res->fields["monto"],
                           "id_moneda"    => $res->fields["id_moneda"],
                           "moneda"       => $res->fields["simbolo"],
                           "id_licitacion"=> $res->fields["id_licitacion"],
                           "nro_factura"  => $res->fields["factura"],
                          );
             if ($res->fields['id_moneda']==1)
                            $cuentas_a_cobrar_pesos+=$res->fields['monto'];
                            else
                            $cuentas_a_cobrar_dolares+=$res->fields['monto'];
            $res->MoveNext();
        }// delfor


 }//del if

   $array["sql"] = $sql ;
   $array["where"] = $where;
   $array["monto_pesos"] = $cuentas_a_cobrar_pesos;
   $array["monto_dolar"] = $cuentas_a_cobrar_dolares;
   $array["datos"] = $datos;

 return $array;

}// de la function sql_cuentas_a_cobrar

function sql_bancos($fecha_hasta){
global $db;

$sql="select idbanco,nombrebanco,
            sum(COALESCE(total_deposito,0) + COALESCE(total_tarjeta,0) - COALESCE(total_cheque,0) - COALESCE(total_debito,0)) as saldo
            from
            (
            SELECT sum(ImporteDep) AS total_deposito,idbanco
            FROM bancos.depósitos INNER JOIN bancos.tipo_banco using(idbanco)
            WHERE FechaCrédito IS NOT NULL AND FechaCrédito BETWEEN '1996-01-01' AND '$fecha_hasta' and tipo_banco.activo=1
            group by idbanco
            ) as dep
            FULL OUTER JOIN
            (
            SELECT sum(ImporteCrédTar) AS total_tarjeta,idbanco
            FROM bancos.tarjetas INNER JOIN bancos.tipo_banco using(idbanco)
            WHERE FechaCrédTar IS NOT NULL AND FechaCrédTar BETWEEN '1996-01-01' AND '$fecha_hasta' and tipo_banco.activo=1
            group by idbanco
            ) as tar using(idbanco)
            FULL OUTER JOIN
            (
            SELECT sum(ImporteCh) AS total_cheque ,idbanco
            FROM bancos.cheques INNER JOIN bancos.tipo_banco using(idbanco)
            WHERE FechaDébCh IS NOT NULL AND FechaDébCh BETWEEN '1996-01-01' AND '$fecha_hasta' and tipo_banco.activo=1
            group by idbanco
            )as cheq using(idbanco)
            FULL OUTER JOIN
            (
            SELECT sum(ImporteDéb)AS total_debito,idbanco
            FROM bancos.débitos INNER JOIN bancos.tipo_banco using (idbanco)
            WHERE FechaDébito IS NOT NULL AND FechaDébito BETWEEN '1996-01-01' AND '$fecha_hasta' and tipo_banco.activo=1
            group by idbanco
            )as deb using(idbanco)
            FULL OUTER JOIN
            bancos.tipo_banco using(idbanco)
            where tipo_banco.activo=1  and tipo_banco.idbanco<>10 and  tipo_banco.idbanco<>8 and tipo_banco.idbanco<>7
            group by idbanco,nombrebanco
            order by nombrebanco";
 $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
 $datos=array();
 for($i=0;$i<$res->recordcount();$i++){
     $datos[]=array("nombre"=>$res->fields["nombrebanco"],
                    "idbanco"=>$res->fields["idbanco"],
                    "saldo"=>$res->fields["saldo"]);

     $saldo_acumulado+=$res->fields["saldo"];
     $res->movenext();
 }

 //***************Transferencias *********************//
 $sql="select sum(monto) as saldo from transferencias where id_estado_transferencias=2";
 $result=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
 $saldo=$result->fields["saldo"];
 ($result->fields["saldo"])?$saldo=$result->fields["saldo"]:$saldo=0;

 $saldo_acumulado+=$saldo;

 $datos[]=array("nombre"=>"Transferencias","saldo"=>$saldo);

  //prueba
  
 $array["sql"] = $sql ;
 $array["where"] = $where;
 $array["monto_pesos"] = $saldo_acumulado;
 $array["monto_dolar"] = 0;
 $array["datos"] = $datos;
 return $array;
}

function sql_stock(){
global $db;

$stock_dolares = $stock_pendiente_pesos =  $stock_pendiente_pesos = $stock_pendiente_dolares = 0;
$datos=array();

$sql="select sum(precio_stock*(cant_disp + cant_reservada +cant_a_confirmar)) as total , id_deposito,depositos.nombre
           from general.producto_especifico
           left join stock.en_stock using(id_prod_esp)
           join (
		        select id_deposito,nombre from general.depositos where nombre='San Luis'
		                or nombre='Buenos Aires'
		                or nombre='Serv. Tec. Bs. As.'
		                or nombre='Produccion'
		                or nombre='Produccion-San Luis'
                        )	 as depositos using (id_deposito)
	  group by id_deposito ,depositos.nombre ";

 $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);

 for ($i=0;$i<$res->recordcount();$i++)    {
    $stock_dolares+=$res->fields["total"];
    $datos[]=array("nombre"=>$res->fields["nombre"],"moneda"=>"u\$s","total"=>$res->fields["total"]);
    $res->movenext();
 }//del for

  //rma
 $sql="select sum(cantidad*precio_stock)as total,depositos.nombre 
                from stock.info_rma
                join stock.en_stock using (id_en_stock)
                left join general.producto_especifico using(id_prod_esp)
                left join stock.estado_rma using (id_estado_rma) 
                join general.depositos using (id_deposito)
                where (estado_rma.nombre_corto <> 'B' and estado_rma.nombre_corto <> 'E')
                group by depositos.nombre
                
     ";

  $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
  if ($res->recordcount()){
  for ($i = 0;$i<$res->recordcount();$i++){	
      $datos[]=array("nombre"=>$res->fields["nombre"],"moneda"=>"u\$s","total"=>$res->fields["total"]);	
      $stock_dolares+=$res->fields["total"];
      $res->movenext();
  }
  }
  
  //scrap
  $sql="select sum(pe.precio_stock*info_rma.cantidad )  as total
            from stock.info_rma 
            join stock.estado_rma using (id_estado_rma)
            join stock.en_stock using (id_en_stock)
            join general.producto_especifico pe using (id_prod_esp)
            where id_estado_rma=12";
            
  $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
  if ($res->fields["total"]){
        $datos[]=array("nombre"=>"Scrap","moneda"=>"u\$s","total"=>$res->fields["total"]);
        $stock_dolares+=$res->fields["total"];
  }


 //Notas de Credito
 $nc_pesos=$nc_dol=0;
 $sql="SELECT sum (monto) as total_nota_credito,id_moneda
  	          from general.nota_credito
              WHERE (estado=0 or estado=1)                                    
              group by id_moneda ";
 $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);
 for($i=0;$i<$res->recordcount();$i++) {
        if ($res->fields['id_moneda']==1)
                 $nc_pesos=$res->fields['total_nota_credito'];

                 else
                 $nc_dol=$res->fields['total_nota_credito'];
        $res->MoveNext();
 }
 $stock_pesos+=$nc_pesos;
 $stock_dolares+=$nc_dol;

 $datos[]=array("nombre"=>"Notas Créditos Pendientes","moneda"=>"\$","total"=>$nc_pesos);
 $datos[]=array("nombre"=>"Notas Créditos Pendientes","moneda"=>"u\$s","total"=>$nc_dol);


 //$notas_credito=($nc_pesos / $valor_dolar) + $nc_dol; //dolares
 $sql="select sum(costo) as total from muestras.muestra
              join licitaciones.entidad using(id_entidad)
              where muestra.estado=1";
 $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);

 $muestras_dolares=$res->fields["total"];
 $muestras_pesos=0;

 $stock_pesos+=$muestras_pesos;
 $stock_dolares+=$muestras_dolares;


 $datos[]=array("nombre"=>"Muestras Dólares","moneda"=>"u\$s","total"=>$muestras_dolares);

 $array["sql"] = $sql ;
 $array["where"] = $where;
 $array["monto_pesos"] = $stock_pesos;
 $array["monto_dolar"] = $stock_dolares;
 $array["datos"] = $datos;

 return $array;
}


function sql_deuda_financiera($resultado){
global $db;
  
 $sql="
        select facturas.nro_factura,facturas.id_licitacion,moneda.simbolo,moneda.id_moneda,
             ((facturas.monto*monto_prestamo)/sumatoria_facturas.monto) as proporcional_factura,
              monto_prestamo,
              id_venta_factura, factoring.nombre as nombre_factoring,facturas.nombre_entidad


       from bancos.facturas_venta fv
       join licitaciones.moneda on (fv.moneda=moneda.id_moneda)
       join licitaciones.factoring using (id_factoring)

       left join bancos.facturas_venta_lista fvl using (id_venta_factura)
       left join
           (
            select sum(precio * cant_prod) as monto, facturas.id_factura,facturas.nro_factura,facturas.id_moneda,cobranzas.id_licitacion,entidad.nombre as nombre_entidad
                   from bancos.facturas_venta_lista
                   join facturacion.facturas using (id_factura)
                   join licitaciones.entidad using (id_entidad)
                   join facturacion.items_factura using (id_factura)
                   left join licitaciones.cobranzas using(id_factura)
                   group by facturas.id_factura,facturas.nro_factura,facturas.id_moneda,cobranzas.id_licitacion,nombre_entidad
            ) as facturas using (id_factura)
       left  join (
            select sum(precio * cant_prod) as monto, id_venta_factura
                   from bancos.facturas_venta_lista
                   join facturacion.facturas using (id_factura)
                   join facturacion.items_factura using (id_factura)
                   group by id_venta_factura
                   order by id_venta_factura
            ) as sumatoria_facturas using (id_venta_factura)
 ";
 $where= " fv.estado_venta=1 ";

 $datos=array();
 if ($resultado){
         if ($where)
         $res=$db->execute($sql." where ".$where) or  die($db->errormsg()." <br> ".$sql);
         else
         $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);


         $deuda_financiera_pesos=$deudad_financiera_dolares=0;
        for($i=0;$i<$res->recordcount();$i++){
            $monto=0;
            if ($res->fields["proporcional_factura"])
                     $monto=$res->fields["proporcional_factura"];
                     else
                      $monto=$res->fields["monto_prestamo"];

            $datos[]=array("monto"        => $monto,
                           "id_moneda"    => $res->fields["id_moneda"],
                           "moneda"       =>  $res->fields["simbolo"],
                           "id_licitacion"=> $res->fields["id_licitacion"],
                           "nro_factura"  => $res->fields["nro_factura"],
						   "descripcion"  => $res->fields["nombre_factoring"] ." -- " . $res->fields["nombre_entidad"],
                          );


             if ($res->fields['id_moneda']==1)
                            $deuda_financiera_pesos+=$monto;
                            else
                            $deudad_financiera_dolares+=$monto;


            $res->MoveNext();
        }// delfor


 }//del if

   $array["sql"] = $sql ;
   $array["where"] = $where;
   $array["monto_pesos"] = $deuda_financiera_pesos;
   $array["monto_dolar"] = $deudad_financiera_dolares;
   $array["datos"] = $datos;

 return $array;


}  // de la funcion de deuda financiera

function sql_cheques_pendientes($resultado){
 global $db;
 
$fecha=date("Y-m-d ",mktime());  
$sql="SELECT idbanco,nombrebanco,\"númeroch\" as numero_cheque,ImporteCh as monto,proveedor.razon_social
             FROM bancos.cheques
             join bancos.tipo_banco using(idbanco)
			 left join general.proveedor on cheques.idprov=proveedor.id_proveedor
             WHERE FechaDébCh IS NULL and fechaemich <='$fecha 23:59:59' and
                   tipo_banco.activo=1
                   and  tipo_banco.idbanco<>10  and tipo_banco.idbanco<>7 and tipo_banco.idbanco<>8";
if ($resultado) {
         if ($where)
         $res=$db->execute($sql." where ".$where) or  die($db->errormsg()." <br> ".$sql);
         else
         $res=$db->execute($sql) or  die($db->errormsg()." <br> ".$sql);

        for ($i=0;$i<$res->recordcount();$i++){
            $datos[]=array("monto"        => $res->fields["monto"],
                           "id_moneda"    => 1,
                           "moneda"       => "\$",
						   "descripcion"  => $res->fields["nombrebanco"] ." -- " . $res->fields["numero_cheque"]." -- ".$res->fields["razon_social"],
                           "idbanco"      => $res->fields["idbanco"],
                           "nombrebanco"  => $res->fields["nombrebanco"]
                          );

            $cheques_pesos+=$res->fields["monto"];

            $array_bancos[$res->fields["nombrebanco"]]["monto"]+=$res->fields["monto"];
            $array_bancos[$res->fields["nombrebanco"]]["idbanco"]=$res->fields["idbanco"];
            
			$res->movenext();
			}
  foreach($array_bancos as $key => $value){
    $arreglo_cheques[]=array("nombre"=>$key,"total"=>$value["monto"],"idbanco"=>$value["idbanco"]);
    }
}
   $array["sql"] = $sql ;
   $array["where"] = $where;
   $array["monto_pesos"] = $cheques_pesos;
   $array["monto_dolar"] = 0;
   $array["datos"] = $datos;
   $array["montos_por_banco"] = $arreglo_cheques;
   return $array;
}


function obtener_neto(){
 global $db;	
 $fecha_hasta=date("Y-m-d G:00:00");


  $sql="select valor from general.dolar_general";
  $res=$db->execute($sql) or die($db->errormsg()."<br>".$sql);   
  $valor_dolar=$res->fields['valor'];	
	
  $sql="select monto from saldo_libre_disponibilidad order by fecha DESC limit 1";
  $res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
  ($res->fields["monto"])?$saldo_libre_disponibilidad=$res->fields["monto"]:$saldo_libre_disponibilidad=0;	
  
  $sql="select monto from suss order by fecha DESC limit 1";
  $res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
  ($res->fields["monto"])?$suss=$res->fields["monto"]:$suss=0;	
  

  /*******************************************************************************************
								Cuentas a cobrar
  *********************************************************************************************/
  
  $cuentas_a_cobrar=sql_cuentas_a_cobrar(1);
  $cuentas_a_cobrar_dolares = $cuentas_a_cobrar["monto_dolar"];
  $cuentas_a_cobrar_pesos   = $cuentas_a_cobrar["monto_pesos"];
  if (!$cuentas_a_cobrar_dolares) $cuentas_a_cobrar_dolares=0;
  if (!$cuentas_a_cobrar_pesos) $cuentas_a_cobrar_pesos=0;

  
  $cuentas_a_cobrar = $cuentas_a_cobrar_pesos + ($cuentas_a_cobrar_dolares * $valor_dolar);
  
  /*******************************************************************************************
                                  BANCOS
  *********************************************************************************************/

  $datos_bancos=sql_bancos($fecha_hasta);
  $arreglo_bancos=$datos_bancos["datos"];
  $bancos_pesos=$datos_bancos["monto_pesos"]; 
  $bancos_dolares=$datos_bancos["monto_dolar"];
  if (!$bancos_pesos) $bancos_pesos=0;
  if (!$banco_dolares) $banco_dolares=0;
  
  $bancos = $bancos_pesos + ($bancos_dolares * $valor_dolar);

  
  
 /******************************************************************************
                       STOCK
 stock total + stock de produccion + notas de credito pendientes
 /******************************************************************************/
 
  $datos_stock=sql_stock();
  $stock_pesos=$datos_stock["monto_pesos"];
  $stock_dolares=$datos_stock["monto_dolar"];
  
  $stock = $stock_pesos + ($stock_dolares * $valor_dolar);

 /***********************************************************************
                                BIENES DE USO
 ***********************************************************************/
 $sql="select sum(precio_unitario*cantidad)  as total
        from stock.inventario
        join stock.estado_inventario ei using(id_estado)
      ";
 $res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
 $total_bienes_de_uso_dolar=0;
 ($res->fields["total"])?$bienes_de_uso_pesos=$res->fields["total"]:$bienes_de_uso_pesos=0;
 $bienes_de_uso_dolares=0;
 
 $bienes_de_uso = $bienes_de_uso_pesos + ($bienes_de_uso_dolares * $valor_dolar);

 /**********************************************************************
                         CAJA
 **********************************************************************/
 $mes = substr($fecha_hasta,5,2);
 $dia = substr($fecha_hasta,8,2);
 $anio = substr($fecha_hasta,0,4);
 $nrodiasemana = date('w', mktime(0,0,0,$mes,$dia,$anio));

 if ($nrodiasemana==0 || feriado(fecha($fecha))) //si es domingo o feriado
 	 $fecha_caja=fecha_db(dia_habil(fecha($fecha)));
     else
     $fecha_caja=$fecha;

 /**********************************************************************
                        CAJA  DE SEGURIDAD
 **********************************************************************/

  $sql=" select sum (monto) as total , id_moneda
         from item_caja_seguridad
         join caja_seguridad using(id_caja_seguridad)
         WHERE caja_seguridad.id_caja_seguridad='1'
               and item_caja_seguridad.estado='existente'
         group by  id_moneda";
   $result=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);

   for ($i=0;$i<$result->recordcount();$i++){
       if ($result->fields["id_moneda"]==1)
               $caja_de_seguridad_pesos=$result->fields["total"];
       if ($result->fields["id_moneda"]==2)
               $caja_de_seguridad_dolar=$result->fields["total"];

    $result->movenext();
    }
   if (!$caja_de_seguridad_pesos) $caja_de_seguridad_pesos=0;
   if (!$caja_de_seguridad_dolar) $caja_de_seguridad_dolar=0;

   $caja_pesos_sl=total_caja(1,1,$fecha_hasta);
   $caja_pesos_bs=total_caja(2,1,$fecha_hasta);
   $caja_pesos=$caja_pesos_sl + $caja_pesos_bs  + $caja_de_seguridad_pesos;
   $caja_dolar_sl=total_caja(1,2,$fecha_hasta);
   $caja_dolar_bs=total_caja(2,2,$fecha_hasta);
   $caja_dolares=$caja_dolar_sl + $caja_dolar_bs + $caja_de_seguridad_dolar;

   $caja = $caja_pesos + ($caja_dolares * $valor_dolar); 

   /**********************************************************************
                          Depositos pendientes
   ***********************************************************************/
  //depositos pendientes
	  
   $sql="SELECT sum(ImporteDep) as total
         FROM bancos.depósitos
         JOIN bancos.tipo_banco using(idbanco)
         WHERE bancos.depósitos.FechaCrédito IS NULL AND tipo_banco.activo=1
         and tipo_banco.idbanco<>10 and  tipo_banco.idbanco<>7 and tipo_banco.idbanco<>8
         ";	  
   $res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
   $depositos_pendientes_pesos = $res->fields["total"];
   if (!$depositos_pendientes_pesos) $depositos_pendientes_pesos=0;  
   $depositos_pendientes_dolares=0;

   $depositos_pendientes = $depositos_pendientes_pesos + ($depositos_pendientes_dolares * $valor_dolar); 

  /********************************************************************
                  Cheques Diferidos Pendientes
  *********************************************************************/
   $total_cheques_diferidos=0;
   $sql="SELECT sum(monto) as total
	      FROM bancos.cheques_diferidos
          join bancos.bancos_cheques_dif using(id_banco)
	      WHERE cheques_diferidos.IdDepósito IS NULL
	      and cheques_diferidos.id_ingreso_egreso IS NULL and activo=1";	  

	$res=$db->execute($sql) or die($db->errormsg()." <br> ".$sql);
	$cheques_diferidos_pendientes_pesos = $res->fields["total"] ;
    if (!$cheques_diferidos_pendientes_pesos) $cheques_diferidos_pendientes_pesos=0;	
	$cheques_diferidos_pendientes_dolares=0;
	
	$cheques_diferidos = $cheques_diferidos_pendientes_pesos + ($cheques_diferidos_pendientes_dolares  * $valor_dolar);
	/*******************************************************************************
	                        Adelantos
	*******************************************************************************/
	 $adelantos=sql_adelantos(1); 
	 $adelantos_pesos   = $adelantos["monto_pesos"];
	 $adelantos_dolares = $adelantos["monto_dolar"];
	  
	 if (!$adelantos_pesos) $adelantos_pesos=0;
	 if (!$adelantos_dolares) $adelantos_dolares=0;
     
	 $adelantos = $adelantos_pesos + ($adelantos_dolares*$valor_dolar);
 
	/*****************************************************************************
	                                        DEBE
	******************************************************************************/

	/*******************************************************************************
	                        Cheques
	*******************************************************************************/

	$datos=sql_cheques_pendientes(1);
	$arreglo_cheques = $datos["montos_por_banco"];
	$arreglo_cheques_datos  = $datos["datos"] ;
	$cheques_pesos  =  $datos["monto_pesos"] ;
	$cheques_dolares = $datos["monto_dolar"];
	
	if (!$cheques_pesos) $cheques_pesos=0;
	if (!$cheques_dolares) $cheques_dolares=0;


	$cheques = $cheques_pesos + ($cheques_dolares * $valor_dolar);

	/*******************************************************************************
	                        Deuda Comercial
	*******************************************************************************/
	
	//Ordenes de Compra que se recibieron los productos y no se pago nada
	//Si se cambia la consulta aca, cambiarla en detalle_deuda_comercial
	//ahora tiene en cuenta las ordenes internacionales
	
	$deuda_comercial= sql_deuda_comercial(0,1);
	$deuda_comercial_pesos=$deuda_comercial["monto_pesos"];
	$deuda_comercial_dolares=$deuda_comercial["monto_dolar"];
	
	$deuda_comercial_internacional= sql_deuda_comercial(1,1);
	$deuda_comercial_pesos+=$deuda_comercial_internacional["monto_pesos"];
	$deuda_comercial_dolares+=$deuda_comercial_internacional["monto_dolar"];
	
	if (!$deuda_comercial_dolares) $deuda_comercial_dolares=0;
	if (!$deuda_comercial_pesos) $deuda_comercial_pesos=0;
	
	$deuda_comercial = $deuda_comercial_pesos + ($deuda_comercial_dolares * $valor_dolar);

   /************************************************************************************************************/
   /******************************************* Deuda Financiera  **********************************************/
   /************************************************************************************************************/
   $deuda_financiera= sql_deuda_financiera(1);
   $deuda_financiera_pesos=$deuda_financiera["monto_pesos"];
   $deuda_financiera_dolares=$deuda_financiera["monto_dolar"];

   if (!$deuda_financiera_dolares) $deuda_financiera_dolares=0;
   if (!$deuda_financiera_pesos) $deuda_financiera_pesos=0;

   $deuda_financiera = $deuda_financiera_pesos + ($deuda_financiera_dolares * $valor_dolar);   
   
   $activo = $cuentas_a_cobrar + $saldo_libre_disponibilidad + $suss +$stock + $bienes_de_uso
             +$adelantos + $depositos_pendientes + $cheques_diferidos + $caja + $bancos;
             
   $pasivo = $cheques + $deuda_comercial + $deuda_financiera;

   $neto = $activo - $pasivo;  

   return ($neto);

} //de la function

?>