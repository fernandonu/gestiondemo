<?
/*
$Author: mari $
$Revision: 1.1 $
$Date: 2006/05/19 14:42:38 $

*/
//cant_a_ingresar esla cantidad que ingresa para pasar al proximo estado
//cant_por_tanda es la cantidad que hay en el estado actual
function buscar_ordenes($nro_orden,$estado,$orden,$cant_a_ingresar,$cant_por_tanda=-1) {

if ($cant_por_tanda== -1) { //estado proximo o sea al que va a pasar
	    $op="+";
	 	//$orden++;
	 	$cant=$cant_a_ingresar;  
}
   else { 
   	$op="-";
    $cant=$cant_por_tanda-$cant_a_ingresar; //es la cantidad que queda en el estado anterior
   }

 $sql="select prod_bsas_por_tanda.id_por_tanda,prod_bsas_por_tanda.nro_orden,
       prod_bsas_por_tanda.cantidad_por_tanda,prod_bsas_por_tanda.estado_bsas_por_tanda 
       from ordenes.prod_bsas_por_tanda 
       where prod_bsas_por_tanda.nro_orden=$nro_orden 
       and prod_bsas_por_tanda.estado_bsas_por_tanda = $estado";
 $res=sql($sql,"$sql ") or fin_pagina();
 
 if ($res->RecordCount()>0) { //update
   $sql_update="update ordenes.prod_bsas_por_tanda set cantidad_por_tanda=cantidad_por_tanda $op $cant_a_ingresar 
                where nro_orden=$nro_orden and estado_bsas_por_tanda = $estado";
   sql($sql_update,"$sql_update en anterior") or fin_pagina(); 
 }
 else { //insert
  $sql_insert="insert into ordenes.prod_bsas_por_tanda (nro_orden,cantidad_por_tanda,estado_bsas_por_tanda,orden_tanda) 
               values($nro_orden,$cant,$estado,$orden)";
  sql($sql_insert,"$sql_insert en estado anterior") or fin_pagina();
  
  $cant=$cant_por_tanda-$cant_a_ingresar;  //es la cantidad que queda en el estado anterior
 }
}


function actualiza_estado_bsas($estado_actual,$nro_orden,$cond,$estado_bsas) {
 //busco si quedo en cero el estado actual para cambiar el estado bsas de la tabla produccion
/*el subselect es para saber si no hay ordenes en el estado anterior */

if ($estado_actual==0) { //de estado pendienente a produccion
     $sql="select prod_bsas_por_tanda.cantidad_por_tanda
           from ordenes.prod_bsas_por_tanda 
           where prod_bsas_por_tanda.nro_orden=$nro_orden 
           and prod_bsas_por_tanda.estado_bsas_por_tanda=$estado_actual";
     $anterior=0;
  }
  else {
     $sql="select prod_bsas_por_tanda.cantidad_por_tanda,r.total_ant 
           from ordenes.prod_bsas_por_tanda 
           left join (select sum(prod_bsas_por_tanda.cantidad_por_tanda) as total_ant,prod_bsas_por_tanda.nro_orden 
                 from ordenes.prod_bsas_por_tanda  
                 where $cond
                 group by prod_bsas_por_tanda.nro_orden) as r
           using (nro_orden)
           where prod_bsas_por_tanda.nro_orden=$nro_orden and prod_bsas_por_tanda.estado_bsas_por_tanda=$estado_actual ";
  }
  $res=sql($sql," $sql en cero") or fin_pagina();

 if ($res->Recordcount() > 0) {
 	   if ($res->fields['total_ant']) $anterior=$res->fields['total_ant'];
 	   else $anterior=0;
 }
 if ($res->Recordcount() > 0 && $res->fields['cantidad_por_tanda']==0 && $anterior==0) {
  $sql_up="update ordenes.orden_de_produccion set estado_bsas=$estado_bsas
           where nro_orden=$nro_orden";
  sql($sql_up,"$sql_up update en actualiza estado ") or fin_pagina();
  $act=1;
  }
  else $act=0;

return $act;
}
?>