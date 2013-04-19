<?php

require_once("../../config.php");
include_once("ord_compra_clasepdf.php"); 
//generacion de pdf
$pdf=new orden_compra();

if ($parametros['nro_orden']) $nro_orden=$parametros['nro_orden'];
//traemos la forma de pago
/*
$query="select l.user_login as usuario,l.fecha,forma_de_pago.descripcion,moneda.*,razon_social,contactos.nombre as contacto ".
"from orden_de_compra o join forma_de_pago on o.id_forma=forma_de_pago.id_forma ".
"left join log_ordenes l on o.nro_orden=l.nro_orden AND l.tipo_log='de creacion' ".
"left join moneda on moneda.id_moneda=o.id_moneda ".
"left join proveedor on proveedor.id_proveedor=o.id_proveedor ".
"left join contactos on contactos.id_contacto=o.id_contacto ".
"where o.nro_orden=$nro_orden";
*/
$query="select l.user_login as usuario,l.fecha,o.fecha_entrega,o.lugar_entrega,o.cliente,plantilla_pagos.descripcion,moneda.*,razon_social,contactos.nombre as contacto ".
"from orden_de_compra o join plantilla_pagos using(id_plantilla_pagos) ".
"left join log_ordenes l on o.nro_orden=l.nro_orden AND l.tipo_log='de creacion' ".
"left join moneda on moneda.id_moneda=o.id_moneda ".
"left join proveedor on proveedor.id_proveedor=o.id_proveedor ".
"left join contactos on contactos.id_contacto=o.id_contacto ".
"where o.nro_orden=$nro_orden";

$f_res=$db->Execute($query) or die($db->ErrorMsg());

$pdf->dibujar_planilla();
$pdf->nro_orden_compra($nro_orden);
$pdf->proveedor($f_res->fields['razon_social']);
$pdf->fecha(Fecha($f_res->fields['fecha']));
$pdf->vendedor($f_res->fields['contacto']);
$pdf->forma_pago($f_res->fields['descripcion']);

if ($_POST['fecha_entrega']) $pdf->entrega($_POST['fecha_entrega']);
else  $pdf->entrega(Fecha($f_res->fields['fecha_entrega']));

if ($_POST['entrega']) $pdf->lugar_entrega($_POST['entrega']);
else $pdf->lugar_entrega($f_res->fields['lugar_entrega']);
if ($_POST['cliente']) $pdf->cliente($_POST['cliente']);
else $pdf->cliente($f_res->fields['cliente']);

$simbolo=($f_res->fields['simbolo'])?$f_res->fields['simbolo']:'U$S';

//FALTA AVERIGUAR CUAL ES LA MONEDA CON LA QUE SE VA A MOSTRAR

//traemos los productos para agregar al pdf desde la tabla filas
$query="select descripcion_prod,desc_adic,cantidad,precio_unitario from fila where nro_orden=$nro_orden";
$datos_prod=$db->Execute($query) or die($db->ErrorMsg());
$first=1;
while(!$datos_prod->EOF)
{
$pdf->producto($datos_prod->fields['descripcion_prod']." ".$datos_prod->fields['desc_adic'],$datos_prod->fields['cantidad'],$datos_prod->fields['precio_unitario'],$simbolo,$first);
$datos_prod->MoveNext();
$first=0;
}

//seleccionamos la firma del usuario que creo la orden,
//para mostrar en el pdf.
$query="select firma1,firma2,firma3 from usuarios where login='".$f_res->fields['usuario']."'";
$firm=$db->Execute($query) or die($db->ErrorMsg()." firma");
$firma1=$firm->fields['firma1'];
$firma2=$firm->fields['firma2'];
$firma3=$firm->fields['firma3'];
$pdf->_final($_POST['total'],$simbolo,$firma1,$firma2,$firma3);
$pdf->Footer();
$pdf->guardar_servidor("orden_de_compra_$nro_orden.pdf");
//fin de generacion de pdf

if ($parametros['nro_orden']){
?>
<html>
<SCRIPT language="javascript">
window.location='./PDF/orden_de_compra_<?=$nro_orden?>.pdf';
</script>
</html
<? }
?>