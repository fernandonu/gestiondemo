<?
/*
Autor: Marco

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2004/09/24 21:22:50 $

ARCHIVO QUE EJECUTA UNA ACTUALIZACION EN ENTRADAS DE LA TABLA PROVEEDOR_CANTIDAD
PARA CADA FILA DE MOV MATERIAL QUE TENGAN COMO PROVEEDOR A UN STOCK Y QUE NO TENGAN
INSERTADA LA ENTRADA CORRESPONDIENTE EN LA MENCIONADA TABLA, PARA CADA FILA.
ESTE ARCHIVO ES PARA USARSE UNA SOLA VEZ. 

NO LO INTENTEN EJECUTAR BAJO NINGUN PUNTO DE VISTA!!!

*/



require_once("../../config.php");
$db->StartTrans();
//seleccionamos todas las ordenes de compra con proveedor stock, 
$query="
select movimiento_material.estado,movimiento_material.fecha_creacion,
origen,destino,id_movimiento_material,titulo,prod_recibidos,prod_enviados
from (mov_material.movimiento_material 
 join (select id_deposito,nombre as origen from general.depositos) as orig on orig.id_deposito=movimiento_material.deposito_origen) 
 join (select id_deposito,nombre as destino from general.depositos) as dest on dest.id_deposito=movimiento_material.deposito_destino 
 left join (select id_movimiento_material,sum(enviados) as prod_enviados,sum(recibidos) as prod_recibidos 
 from (select id_movimiento_material,id_detalle_movimiento,sum(cantidad) as enviados from mov_material.detalle_movimiento group by id_movimiento_material,id_detalle_movimiento) as env 
 left join (select id_detalle_movimiento,sum(cantidad) as recibidos from mov_material.recibidos_mov 
 group by id_detalle_movimiento) as rec using (id_detalle_movimiento)
 group by id_movimiento_material )as recib_env using (id_movimiento_material) 

WHERE estado=2 ORDER BY fecha_creacion
";
$oc=sql($query) or fin_pagina();
$contador=0;
//recorremos cada oc traida
while(!$oc->EOF)
{echo "<br><br>----------<br>Movimiento Material Nº ".$oc->fields['id_movimiento_material']."<bR>----------Falta ".$oc->fields['prod_recibidos']."-------------------<br>";
 //si la cantidad que falta recibir es diferente de -1 entonces hay que
 //actualizar la tabla proveedor_cantidad, sino, significa que 
 //ya se recibieron todos los productos para esa OC, por lo que no es necesario
 //agregar nada en proveedor_cantidad
 if($oc->fields['prod_recibidos']!=-1)
 {
  //traemos todas las filas de la OC para agregar una entrada en proveedor_cantidad
  //para las filas que no tengan aun una entrada  
  $query="select id_detalle_movimiento,detalle_movimiento.cantidad,id_movimiento_material,id_producto,detalle_movimiento.id_proveedor, recibidos_mov.cantidad as cant_recib,id_prov_cantidad
         from detalle_movimiento left join recibidos_mov using(id_detalle_movimiento) left join prov_cantidad using(id_detalle_movimiento) where id_movimiento_material=".$oc->fields['id_movimiento_material'];
  $filas_oc=sql($query) or fin_pagina();
   while(!$filas_oc->EOF)
   {echo "<br>Fila Nº ".$filas_oc->fields['id_detalle_movimiento']." -- cantidad recibida ".$filas_oc->fields['cant_recib']." cantidad en la fila ".$filas_oc->fields['cantidad']."<br>";
    //si no tiene entrada en la tabla proveedor_cantidad, la ingresamos
    if($filas_oc->fields['id_prov_cantidad']=="")
    {if($filas_oc->fields['cant_recib']!="" && ($filas_oc->fields['cantidad']-$filas_oc->fields['cant_recib'])>0)
     {//si la cantidad recibida existe y es mayor que 0, entonces en proveedor_cantidad
      //insertamos esa cantidad en la entrada
      $cant_insert=$filas_oc->fields['cantidad']-$filas_oc->fields['cant_recib'];
      
     }//de if($filas_oc->fields['cant_recib']!="" && $filas_oc->fields['cant_recib'])
     else
     {//sino, insertamos la cantidad de la fila en proveedor_cantidad
     $cant_insert=$filas_oc->fields['cantidad'];
     } 
     
     $proveedor=$filas_oc->fields['id_proveedor'];

     $insert="insert into prov_cantidad(id_detalle_movimiento,id_proveedor,cant_seleccionada)
              values(".$filas_oc->fields['id_detalle_movimiento'].",$proveedor,$cant_insert)";	
     sql($insert) or fin_pagina(); 
     echo "<br>INSERTAMOS $insert<br>";$contador++;
    }//de if($filas_oc->fields['id_producto_cantidad']=="")
    $filas_oc->MoveNext();
   }//de while(!$filas->EOF)

 }//de if($oc->fields['falta']!=-1)

 $oc->MoveNext();
}//de while(!$oc->EOF)

$db->CompleteTrans();
echo "<br><br>Cantidad de Movimientos de Material afectadas: $contador";
echo "<br>-------------------------------------------------------------------------------------------------<br>";
?>