<?
/*
Autor: MAC

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2004/04/26 18:00:01 $
*/


//Separa las ordenes de compra de un pago multiple a formas 
//de pago separadas (cheque a 30 dias).

$db->StartTrans();

$cant_ordenes=sizeof($nro_orden);

//borramos las entrada de pago_orden viejas si es que hay, 
//para todas las ordenes
for($i=0;$i<$cant_ordenes;$i++)
{$query="delete from pago_orden where nro_orden=$nro_orden[$i]";
 $a=$db->Execute($query) or die($db->ErrorMsg()."borrado de pago_orden de orden $i (al separar orden de PM)");
}

//seleccionamos las ordenes de pagos vieja si es que hay y las borramos
$query="select id_pago from ordenes_pagos join pago_orden using(id_pago) join orden_de_compra using (nro_orden) where nro_orden=".$nro_orden[0];
$res_ord_pago=$db->Execute($query) or die($db->ErrorMsg()."seleccion de ordenes_pago(al separar orden de PM)");
while(!$res_ord_pago->EOF)
{$query="delete from ordenes_pagos where id_pago=".$res_ord_pago->fields['id_pago'];
 $db->Execute($query) or die($db->ErrorMsg()."borrado de ordenes_pago de primer orden(al separar orden de PM)");
 $res_ord_pago->MoveNext(); 
}

for($i=0;$i<$cant_ordenes;$i++)
{ 
 //ponemos la forma de pago cheque a 30 días (id_plantilla_pagos=4)
 $query="update orden_de_compra set id_plantilla_pagos=4 where nro_orden=".$nro_orden[$i];
 $db->Execute($query) or die($db->ErrorMsg()."actualizar de la orden con la plantilla de pago (al separar orden de PM)");	
 
 //obtenemos el monto a pagar de la orden
 $cuotas=array();
 $cuotas[0]=monto_a_pagar($nro_orden[$i]);
 
 //traemos el valor del dolar de la orden
 $query="select valor_dolar from orden_de_compra where nro_orden=".$nro_orden[$i];
 $valor=$db->Execute($query) or die($db->ErrorMsg()."Seleccion del valor del dolar (al separar orden de PM)");	
 $dolar=array();
 $dolar[0]=$valor->fields['valor_dolar'];
 //insertamos el pago por el total, para el cheque a 30 dias, para la forma
 //de pago de la orden.
 if($select_moneda==$moneda->fields['id_moneda'])
     insertar_ordenes_pagos($nro_orden[$i],$cuotas,$dolar);  
 else  
     insertar_ordenes_pagos($nro_orden[$i],$cuotas);
}	

//seleccionamos las notas de credito que se relacionan con las ordenes de compras
$query="select id_nota_credito from (select * from n_credito_orden where nro_orden=".$nro_orden[0].") as oc join nota_credito using(id_nota_credito) join moneda using(id_moneda)";  
$notas_credito=$db->Execute($query) or die ($db->ErrorMsg()."<br>Error al traer las notas de credito relacionadas con la orden de compra $nro_orden");

while(!$notas_credito->EOF)
{//Volvemos las notas de creditos que estan relacionadas con las ordenes de compra a
 //estado pendientes
 $query="update nota_credito set estado=0 where id_nota_credito=".$notas_credito->fields['id_nota_credito'];	
 $db->Execute($query) or die ($db->ErrorMsg()."<br>Error al actualizar estado de nota de credito a pendientes.Nota de credito".$notas_credito->fields['id_nota_credito']);
 
 //borramos todas las entrada que relacionan ordenes de compra con cada nota de credio
 $query="delete from n_credito_orden where id_nota_credito=".$notas_credito->fields['id_nota_credito'];
 $db->Execute($query) or die ($db->ErrorMsg()."<br>Error al borrar la entrada de  nota de credito y orden de compra. Nota de credito".$notas_credito->fields['id_nota_credito']);
 
 $notas_credito->MoveNext();
}

$db->CompleteTrans();
?>