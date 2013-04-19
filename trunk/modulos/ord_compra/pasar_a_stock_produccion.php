<?
/*
Autor: MAC
Fecha: 21/09/05

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.2 $
$Date: 2005/09/22 01:03:13 $

*/
require_once("../../config.php");require_once("fns.php");
$db->StartTrans();
//traemos los datos de las OC que debemos considerar, y descartamos el resto
$query="select nro_orden,id_licitacion,comprados,cant_recibidos,cant_entregados,id_estado,orden_de_compra.estado,razon_social,orden_de_compra.id_proveedor
from compras.orden_de_compra 
  join general.proveedor using(id_proveedor)
  left join licitaciones.licitacion using(id_licitacion)
  join (select sum(cantidad) as comprados,nro_orden from compras.fila group by nro_orden )as cant_fila using(nro_orden)
  left join(select sum(recibidos.cantidad) as cant_recibidos,nro_orden from compras.fila join compras.recibidos using(id_fila) 
             where ent_rec=1
            group by nro_orden
           )as recibidos using(nro_orden)
  left join(select sum(recibidos.cantidad) as cant_entregados,nro_orden from compras.fila join compras.recibidos using(id_fila) 
             where ent_rec=0
            group by nro_orden
           )as entregados using(nro_orden)
 where id_licitacion is not null and id_estado!=1 and (orden_de_compra.estado='e' or orden_de_compra.estado='d' or orden_de_compra.estado='g')
order by nro_orden";
$oc=sql($query,"<br>Error al traer datos de OC<br>") or fin_pagina();

$afectan_a_stock_produccion=array();$i=0;
//por cada OC encotrada
while (!$oc->EOF)
{//si tiene algo entregado
 if($oc->fields["cant_entregados"]!="")
 {
  //si el proveedor es stock, agregamos la OC a la lista de las que si afectan a stock en produccion
  if(substr_count($oc->fields['razon_social'],"Stock")>0)
  {
   	$afectan_a_stock_produccion[$i]=array();
  	$afectan_a_stock_produccion[$i]["nro_orden"]=$oc->fields['nro_orden'];
  	$afectan_a_stock_produccion[$i]["recibidos"]=$oc->fields['cant_recibidos'];
  	$afectan_a_stock_produccion[$i]["entregados"]=$oc->fields['cant_entregados'];
  	$afectan_a_stock_produccion[$i]["id_licitacion"]=$oc->fields['id_licitacion'];
  	$afectan_a_stock_produccion[$i]["id_proveedor"]=$oc->fields['id_proveedor'];
  	$afectan_a_stock_produccion[$i]["razon_social"]=$oc->fields['razon_social'];
  	$i++;
  }
  //sino, si tiene algo recibido
  elseif($oc->fields["cant_recibidos"]!="")
  {
  	$afectan_a_stock_produccion[$i]=array();
  	$afectan_a_stock_produccion[$i]["nro_orden"]=$oc->fields['nro_orden'];
  	$afectan_a_stock_produccion[$i]["recibidos"]=$oc->fields['cant_recibidos'];
  	$afectan_a_stock_produccion[$i]["entregados"]=$oc->fields['cant_entregados'];
  	$afectan_a_stock_produccion[$i]["id_licitacion"]=$oc->fields['id_licitacion'];
  	$afectan_a_stock_produccion[$i]["id_proveedor"]=$oc->fields['id_proveedor'];
  	$afectan_a_stock_produccion[$i]["razon_social"]=$oc->fields['razon_social'];
  	$i++;
  }
 }//de if($oc->fields["cant_entregados"]!="")	 
	
 $oc->MoveNext();
}//de while(!$oc->EOF)

$tam=sizeof($afectan_a_stock_produccion);
for($j=0;$j<$tam;$j++)
{
  echo "<br>-------------------------------------------------------------------------------<br>";
  echo "<br>OC: ".$afectan_a_stock_produccion[$j]["nro_orden"]." con proveedor :".$afectan_a_stock_produccion[$j]["razon_social"];
  //traemos los datos de las filas de la OC para ver cuales agregamos a stock de produccion
  $query="select id_fila,fila.cantidad,rec,ent,fila.id_producto from fila 
          left join (select id_fila,cantidad as rec from recibidos where ent_rec=1)as recibidos using(id_fila) 
          left join (select id_fila,cantidad as ent from recibidos where ent_rec=0)as entregados using(id_fila)
          where nro_orden=".$afectan_a_stock_produccion[$j]["nro_orden"];
          
  $filas=sql($query,"<br>Error al traer datos de la fila para la OC Nº".$afectan_a_stock_produccion[$j]["nro_orden"]."<br>") or fin_pagina();
  //por cada fila 
  while (!$filas->EOF)
  {	
  	 if($filas->fields["ent"]=="")
  	  $no_dar_bola=1;
  	 else 
  	  $no_dar_bola=0;
  	
	  //echo $afectan_a_stock_produccion[$j]["nro_orden"].",".$afectan_a_stock_produccion[$j]["id_licitacion"].",".$filas->fields["id_producto"].",".$afectan_a_stock_produccion[$j]["id_proveedor"].",".$filas->fields["cantidad"];die;
	  //si hay mas recibidos o igual, que entregados, a produccion va la cantidad entregada
	  if((substr_count($afectan_a_stock_produccion[$j]["razon_social"],"Stock")>0) || $filas->fields["rec"]>=$filas->fields["ent"])
	   $cant_insertar=$filas->fields["ent"];
	  //sino (hay mas entregados que recibidos), va la cantidad recibida 
	  else if($filas->fields["rec"]<$filas->fields["ent"])
	   $cant_insertar=$filas->fields["rec"];
	  else 
	  { print_r($filas->fields);die;}
	  echo "<br>Insertando la fila ".$filas->fields["id_fila"]." con producto ".$filas->fields["id_producto"].". Cant Rec:".$filas->fields["rec"]." - Cant Ent:".$filas->fields["ent"]." - Cantidad a insertar: $cant_insertar ";
	   
	 //lo metemos en produccion
	 if($no_dar_bola==0)
      a_stock_produccion($afectan_a_stock_produccion[$j]["nro_orden"],$afectan_a_stock_produccion[$j]["id_licitacion"],$filas->fields["id_producto"],$afectan_a_stock_produccion[$j]["id_proveedor"],$cant_insertar);
     
     $filas->MoveNext();
  }//de while(!$filas->EOF)	
}//de for($j=0;$j<$tam;$j++)
echo "<br><br>-------------------------------------------------------------------------------<br>";
echo "Finalizado con $tam OC afectadas";

$db->CompleteTrans();
?>