<?
/*
Autor: MAC
Creado: 07/10/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2004/10/07 20:51:49 $
*/
require_once("../../config.php");

$db->StartTrans();
//traeamos todos los datos necesarios
$query="select * from stock.stock join stock.descuento using(id_deposito,id_producto,id_proveedor)
        join stock.info_rma using(id_info_rma)        
        join stock.control_stock using(id_control_stock) 
        where control_stock.fecha_modif<'2004-09-23' and stock.id_deposito=9
        and (id_ubicacion<>4 or id_ubicacion isnull) and info_rma.cantidad>0
        ";
$a_borrar=sql($query) or fin_pagina();

$contador=0;
while(!$a_borrar->EOF)
{
 //borramos las entradas correspondientes de log_stock
 $query="delete from log_stock where id_control_stock=".$a_borrar->fields['id_control_stock'];
 sql($query) or fin_pagina();
 
 //borramos las entradas correspondientes de descuento 
 $query="delete from descuento where id_control_stock=".$a_borrar->fields['id_control_stock'];
 sql($query) or fin_pagina();
 
 $query="delete from control_stock where id_control_stock=".$a_borrar->fields['id_control_stock'];
 sql($query) or fin_pagina();

 $query="delete from log_ubicacion where id_info_rma=".$a_borrar->fields['id_info_rma'];
 sql($query) or fin_pagina();
 
 $query="delete from archivos_subidos where id_info_rma=".$a_borrar->fields['id_info_rma'];
 sql($query) or fin_pagina();
 
  $query="delete from comentarios_rma where id_info_rma=".$a_borrar->fields['id_info_rma'];
 sql($query) or fin_pagina();
 
 $query="delete from info_rma where id_info_rma=".$a_borrar->fields['id_info_rma'];
 sql($query) or fin_pagina();
 
 $query="update stock set cant_disp=0 where id_deposito=".$a_borrar->fields['id_deposito']." and id_producto=".$a_borrar->fields['id_producto']." and id_proveedor=".$a_borrar->fields['id_proveedor'];
 sql($query) or fin_pagina();
 
 $contador++;
 $a_borrar->MoveNext();
} 

echo "Cantidad de iteraciones: $contador";
$db->CompleteTrans();

?>