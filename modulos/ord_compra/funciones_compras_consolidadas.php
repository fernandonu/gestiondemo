<?php
/*
Autor: Fernando
Creado: martes 4/1/2005

MODIFICADA POR
$Author: fernando $
$Revision: 1.15 $
$Date: 2006/05/18 18:26:08 $
*/



 
function genera_arreglo($result){

  for($i=0;$i<$result->recordcount();$i++){
      $arreglo[]=array("id_licitacion"=>$result->fields["id_licitacion"],
                       "cantidad_producto"=>$result->fields["cantidad_producto"],
                       "fecha_entrega"=>$result->fields["fecha_entrega"],
                       "id_subir"=>$result->fields["id_subir"],
                       "monto_unitario"=>$result->fields["monto_unitario"],
                       "id_entrega_estimada"=>$result->fields["id_entrega_estimada"],
                       "activo"=>1
                       );
      $result->movenext();
  }
 return $arreglo;
}

 




//funcion de buscar ordenes simples sin condicion de orden de compra
function busqueda_datos_simples($id_producto){

  $sql=" select orden_de_compra.nro_orden,fila.cantidad,id_licitacion,fila.precio_unitario,simbolo
         from orden_de_compra
         join fila using(nro_orden)
         join moneda using(id_moneda)
         where id_producto=$id_producto
               and orden_de_compra.estado<>'n'
               and not id_licitacion is null
         ";

  $res=sql($sql) or fin_pagina();
  $cantidad_ordenes=$res->recordcount();
  $datos=array();
  for($i=0;$i<$cantidad_ordenes;$i++){
     $datos[]=array("id_licitacion"=>$res->fields["id_licitacion"],
                    "nro_orden"=>$res->fields["nro_orden"],
                    "cantidad"=>$res->fields["cantidad"],
                    "monto_unitario"=>$res->fields["precio_unitario"],
                    "moneda"=>$res->fields["simbolo"],
                    "activo"=>1);
      $res->movenext();
  }//del for

 return $datos;
 }// de la funcion busqueda datos

//funcion que busca los datos en compras consolidadas de las compras menores

 

/*****************************************************************************
 * busqueda_cantidades_simples
 * @return void
  * @param $id_producto es el id del productoq ue deseo obtener si esta comprado
  * @desc busca las ordenes de los productos junto con las cantidades
 ****************************************************************************/

function busqueda_cantidades_simples($id_producto,$id_estado=0)
{

 $estado_renglon=3;
 
   if ($id_estado)
         $condicion=" and l.id_estado=7";
 $sql=" select sum(r.cantidad*p.cantidad) as cantidad,l.id_licitacion,precio_presupuesto.monto_unitario
        from licitaciones.entrega_estimada es
        join licitaciones.licitacion l on (l.id_licitacion=es.id_licitacion)
        join licitaciones.licitacion_presupuesto_new lp using(id_entrega_estimada)
        join licitaciones.renglon_presupuesto_new r using(id_licitacion_prop)
        join licitaciones.producto_presupuesto_new p using (id_renglon_prop)
        left join
           (
            select monto_unitario,id_producto_presupuesto
                   from licitaciones.producto_proveedor_new
                   where activo=1
            ) as precio_presupuesto using(id_producto_presupuesto)
        where  p.id_producto=$id_producto $condicion
        and  es.finalizada=0 and es.flag_compras_consolidadas=1 and l.es_presupuesto=0 and l.borrada='f'
        group by l.id_licitacion,monto_unitario";

  $res=sql($sql) or fin_pagina();
  $datos=array();
  $cantidad=$res->recordcount();
  for($i=0;$i<$cantidad;$i++){
      $datos[]=array("id_licitacion"=>$res->fields["id_licitacion"],
                     "cantidad"=>$res->fields["cantidad"],
                     "monto_unitario"=>$res->fields["monto_unitario"],
                     "moneda"=>"U\$S",
                     "activo"=>1
                     );
      $res->movenext();
  }//del for
//ya tengo todos los datos de las licitaciones
return $datos;
} /// de la funcion



function genera_descripcion_oc($id_licitacion,$id_producto){
 global $monto_comprado;
$sql="select fila.cantidad, oc.nro_orden,fila.id_producto,
              (fila.precio_unitario * case when oc.id_moneda=1 then 1 else oc.valor_dolar end) as precio_unitario,
             oc.id_moneda,oc.valor_dolar  
      from licitaciones.licitacion
      join compras.orden_de_compra oc using (id_licitacion)
      join compras.fila using(nro_orden)
      where id_licitacion=$id_licitacion and id_producto=$id_producto
      and es_agregado=0 and oc.estado<>'n'";
 
 $res=sql($sql) or fin_pagina();
 $cantidad=0;
 $datos=array();
 for($i=0;$i<$res->recordcount();$i++){
     $datos[]=array("nro_orden"=>$res->fields["nro_orden"],
                    "cantidad"=>$res->fields["cantidad"],
                    "precio_unitario"=>$res->fields["precio_unitario"],
                    "valor_dolar"=>$res->fields["valor_dolar"],
                    );
     $cantidad+=$res->fields["cantidad"];
     
     $monto_comprado+=$res->fields["cantidad"] * $res->fields["precio_unitario"];
     $res->movenext();  
 }
  
  $datos["cantidad"]=$cantidad;
  
return $datos;   
}// de la funcion genera_descripcion_oc

/*****************************************************************************
 * busqueda_cantidades_productos
 *  @return $datos
 * @param $id_producto es el id del productoq ue deseo obtener si esta comprado
 * @param $datos son los datos de la consulta sobre los cuales busco coincidencias 
                 en pedido de material y en las orden de compra
 * @desc busca las cantidades de el producto en ordenes de compra y pedido de material
        para un producto y una licitacion dada
 ****************************************************************************/
function busca_cantidades_productos($id_producto,$datos){

 if (sizeof($datos)>0)
         {
         $condicion_pm="  (";
         $condicion=" (";
          for($i=0;$i<sizeof($datos);$i++){
            if ($i==sizeof($datos)-1){
               $condicion.=" oc.id_licitacion=".$datos[$i]["id_licitacion"];
               $condicion_pm.="mm.id_licitacion=".$datos[$i]["id_licitacion"];
            }
               else{
               $condicion.=" oc.id_licitacion=".$datos[$i]["id_licitacion"]." or ";
               $condicion_pm.=" mm.id_licitacion=".$datos[$i]["id_licitacion"]." or ";
               }
          }//del for
         $condicion.=")";
         $condicion_pm.=" )";
         }
 
$sql="select sum(fila.cantidad) as cantidad,
      sum(fila.precio_unitario * fila.cantidad * case when oc.id_moneda=1 then 1 else oc.valor_dolar end) as monto_comprado ,
      fila.id_producto
      from 
      licitaciones.licitacion
      join compras.orden_de_compra oc using (id_licitacion)
      join compras.fila using(nro_orden)
      where $condicion and id_producto=$id_producto
      and es_agregado=0 and oc.estado<>'n'
      group by fila.id_producto
      ";
 
 $res=sql($sql) or fin_pagina();
 $datos=array();
 $datos["cantidad_oc"]=$res->fields["cantidad"];
 $datos["monto_comprado"]=$res->fields["monto_comprado"];


 $sql="select id_tipo_prod from general.productos where id_producto=$id_producto ";
 $res=sql($sql) or fin_pagina();
 $id_tipo_prod=$res->fields["id_tipo_prod"];
 
 
 $sql="select sum(dm.cantidad) as cantidad
       from mov_material.movimiento_material mm
       join mov_material.detalle_movimiento dm using(id_movimiento_material)
       join general.producto_especifico pe using(id_prod_esp)
       where mm.estado<>3 and $condicion_pm and pe.id_tipo_prod=$id_tipo_prod
       and es_pedido_material=1

       ";
       
 $res=sql($sql) or fin_pagina();
 $datos["cantidad_pm"]=$res->fields["cantidad"];      
 
return $datos;   
}// de la funcion genera_descripcion_oc




function genera_descripcion_pm($id_licitacion,$id_tipo_prod)
{
  
 $sql="select dm.cantidad,pe.descripcion,mm.id_movimiento_material 
       from mov_material.movimiento_material mm
       join mov_material.detalle_movimiento dm using(id_movimiento_material)
       join general.producto_especifico pe using(id_prod_esp)
       where mm.estado<>3 and id_licitacion=$id_licitacion and pe.id_tipo_prod=$id_tipo_prod
       and es_pedido_material=1";
  
 $res=sql($sql) or fin_pagina();
 $cantidad=0;
 $datos=array();

 for($i=0;$i<$res->recordcount();$i++){
     
     $datos[]=array("id_movimiento_material"=>$res->fields["id_movimiento_material"],
                    "cantidad"=>$res->fields["cantidad"]);
                    
     $cantidad+=$res->fields["cantidad"];
 $res->movenext();    
 }
 
 $datos["cantidad"]=$cantidad; 
return $datos;    
} // de la funcion genera_descripcion_pm




function genera_html($datos,$indice,$oc=1){
    
 for($i=0;$i<sizeof($datos)-1;$i++){
           if ($oc==1){
 ?>    
                      <tr>
                      <?
                      $link=encode_link("ord_compra.php",array("nro_orden"=>$datos[$i]["nro_orden"]));  
                       ?>
                       <td align=center width=33%>
                         <a href="<?=$link?>" target="_blank">
                            <?=$datos[$i]["nro_orden"]?>
                         </a>  
                       </td>
                       <td width=33% align=center>(<?=$datos[$i]["cantidad"]?>) </td>
                       <td align=center width=33%><?=formato_money($datos[$i]["precio_unitario"])?></td>
                      </tr>
 <?    
           } //del then
           else                    
                {
 ?>    
                 <tr bgcolor="#FFFFC0" title="Pedidos de Material Asociados">
                  <? $link=encode_link("../mov_material/detalle_movimiento.php",array("es_pedido_material"=>1,"id"=>$datos[$i]["id_movimiento_material"]));?>
                  <td align=center width=33%>
                      <a href="<?=$link?>" target="_blank">
                      <?=$datos[$i]["id_movimiento_material"]?>
                      </a>
                  </td>
                  <td align=center width=33%>(<?=$datos[$i]["cantidad"]?>)</td>
                  <td width=33%>&nbsp;</td>
                 </tr>
                 <?    
                 } // del else
    //hidden para calcular con el java script             
    ?>
    <input type="hidden" name="monto_unitario_h_<?=$indice?>" value="<?=$datos[$i]["precio_unitario"]?>">
    <input type="hidden" name="cantidad_h_<?=$indice?>" value="<?=$datos[$i]["cantidad"]?>">    
    <?                 
    }//del for
}
?>