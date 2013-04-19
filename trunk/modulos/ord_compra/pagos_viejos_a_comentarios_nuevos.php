<?
/*Autor: MAC*/

require_once("../../config.php");
$db->StartTrans();

//traemos todos los pagos de la tabla unidos a las ordenes de compra correspondientes
$query="select pagos.*,estado,comentario_pagos from compras.orden_de_compra join compras.pagos using(nro_orden)";
$pagos_ordenes=$db->Execute($query) or die ($db->ErrorMsg().$query);
$i=0;
while(!$pagos_ordenes->EOF)
{
 if($pagos_ordenes->fields['comentario_pagos']=="" && $pagos_ordenes->fields['estado']=='g')	
 {$i++;
  echo "<br>actualizando orden: ".$pagos_ordenes->fields['nro_orden'];
  $query="update orden_de_compra set comentario_pagos='Forma de Pago: ".$pagos_ordenes->fields['nro_cheque'].$pagos_ordenes->fields['tipo_pago']." - Monto: ".$pagos_ordenes->fields['monto']." - Fecha del pago: ".$pagos_ordenes->fields['fecha']." - Observaciones: ".$pagos_ordenes->fields['comentario']."' where nro_orden=".$pagos_ordenes->fields['nro_orden'];
  $db->Execute($query) or die ($db->ErrorMsg().$query);
 }	  	
$pagos_ordenes->MoveNext();	
}	
$db->CompleteTrans();
echo "<br><br><br><b>Cantidad de Ordenes de compra actualizadas: $i";

?>