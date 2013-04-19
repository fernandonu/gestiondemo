<?
/*
Autor: Marco

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2004/09/24 18:11:45 $

ARCHIVO QUE EJECUTA UNA ACTUALIZACION EN ENTRADAS DE LA TABLA PROVEEDOR_CANTIDAD
PARA CADA FILA DE OC QUE TENGAN COMO PROVEEDOR A UN STOCK Y QUE NO TENGAN
INSERTADA LA ENTRADA CORRESPONDIENTE EN LA MENCIONADA TABLA, PARA CADA FILA.
ESTE ARCHIVO ES PARA USARSE UNA SOLA VEZ. 

NO LO INTENTEN EJECUTAR BAJO NINGUN PUNTO DE VISTA!!!

*/



require_once("../../config.php");
$db->StartTrans();
//seleccionamos todas las ordenes de compra con proveedor stock, 
$query="select * from 
(SELECT distinct(o.nro_orden),o.estado,o.desc_prod,o.fecha_entrega,o.id_licitacion,o.cliente,p.razon_social,id_proveedor,
 simbolo,total_orden, licitacion.es_presupuesto, o.nrocaso, o.flag_honorario, o.flag_stock, o.orden_prod 
 FROM compras.orden_de_compra o left join general.proveedor p using(id_proveedor) 
 left join (select sum(cantidad*precio_unitario) as total_orden,nro_orden from compras.fila group by nro_orden) costo using(nro_orden) 
 left join licitaciones.moneda on(moneda.id_moneda=o.id_moneda) left join licitaciones.licitacion using(id_licitacion)
 WHERE p.razon_social ILIKE '%stock%' ORDER BY o.fecha_entrega) as t1 
 left join (select nro_orden,case when sum(comprados)-sum(recibidos)=0 then -1 else sum(comprados)-sum(recibidos) end as falta
 from (select nro_orden,id_fila,sum(cantidad) as comprados from compras.fila group by id_fila,nro_orden) f 
 left join (select id_fila,sum(cantidad) as recibidos from compras.recibidos group by id_fila) r using(id_fila) group by nro_orden) t2 using(nro_orden)";
$oc=sql($query) or fin_pagina();
$contador=0;
//recorremos cada oc traida
while(!$oc->EOF)
{echo "<br><br>----------<br>Orden de Compra Nº ".$oc->fields['nro_orden']."<bR>----------Falta ".$oc->fields['falta']."-------------------<br>";
 //si la cantidad que falta recibir es diferente de -1 entonces hay que
 //actualizar la tabla proveedor_cantidad, sino, significa que 
 //ya se recibieron todos los productos para esa OC, por lo que no es necesario
 //agregar nada en proveedor_cantidad
 if($oc->fields['falta']!=-1)
 {
  //traemos todas las filas de la OC para agregar una entrada en proveedor_cantidad
  //para las filas que no tengan aun una entrada  
  $query="select id_fila,fila.cantidad,nro_orden,id_producto,prov_prod, recibidos.cantidad as cant_recib,id_producto_cantidad
         from fila left join recibidos using(id_fila) left join proveedor_cantidad using(id_fila) where nro_orden=".$oc->fields['nro_orden'];
  $filas_oc=sql($query) or fin_pagina();
   while(!$filas_oc->EOF)
   {echo "<br>Fila Nº ".$filas_oc->fields['id_fila']." -- cantidad recibida ".$filas_oc->fields['cant_recib']." cantidad en la fila ".$filas_oc->fields['cantidad']."<br>";
    //si no tiene entrada en la tabla proveedor_cantidad, la ingresamos
    if($filas_oc->fields['id_producto_cantidad']=="")
    {if($filas_oc->fields['cant_recib']!="" && ($filas_oc->fields['cantidad']-$filas_oc->fields['cant_recib'])>0)
     {//si la cantidad recibida existe y es mayor que 0, entonces en proveedor_cantidad
      //insertamos esa cantidad en la entrada
      $cant_insert=$filas_oc->fields['cantidad']-$filas_oc->fields['cant_recib'];
      
     }//de if($filas_oc->fields['cant_recib']!="" && $filas_oc->fields['cant_recib'])
     else
     {//sino, insertamos la cantidad de la fila en proveedor_cantidad
     $cant_insert=$filas_oc->fields['cantidad'];
     } 
     if($oc->fields['estado']=='p' ||$oc->fields['estado']=='r' || $oc->fields['estado']=='u')
      $proveedor=$filas_oc->fields['prov_prod'];
     else 
      $proveedor=$oc->fields['id_proveedor'];

     $insert="insert into proveedor_cantidad(id_fila,id_proveedor,cant_seleccionada)
              values(".$filas_oc->fields['id_fila'].",$proveedor,$cant_insert)";	
     sql($insert) or fin_pagina(); 
     echo "<br>INSERTAMOS $insert<br>";$contador++;
    }//de if($filas_oc->fields['id_producto_cantidad']=="")
    $filas_oc->MoveNext();
   }//de while(!$filas->EOF)

 }//de if($oc->fields['falta']!=-1)

 $oc->MoveNext();
}//de while(!$oc->EOF)

$db->CompleteTrans();
echo "<br><br>Cantidad de Ordenes de Compra afectadas: $contador";
echo "<br>-------------------------------------------------------------------------------------------------<br>";
?>