<?
/*
Autor: MAC
Fecha: 20/04/05

MODIFICADA POR
$Author:  $
$Revision:  $
$Date:  $

*/


/********************************************************************************************************
*********************************************************************************************************
 ATENCION: Este archivo es de solo lectura. NO DEBE USARSE PARA MODIFICAR NINGUN DATO. 
 SOLO SE MUESTRAN DATOS DE LAS OC, PERO SI SE QUIERE MODIFICAR ALGO, SE USA EL YA CONOCIDO ord_compra.php
*********************************************************************************************************
*********************************************************************************************************/

require_once("../../config.php");
require_once("fns.php");

$nro_orden=$parametros["nro_orden"];

//traemos los datos propios de la OC
$query="select oc.id_licitacion,oc.es_presupuesto,oc.nrocaso,oc.flag_stock,oc.flag_honorario,oc.orden_prod,
        op.id_licitacion as id_lic_op,oc.estado,oc.fecha_entrega,razon_social,contactos.nombre as contacto,moneda.nombre as moneda,moneda.simbolo as simbolo,valor_dolar,
        plantilla_pagos.descripcion as forma_pago,oc.cliente,oc.lugar_entrega,oc.notas as comentarios,oc.notas_internas
        from orden_de_compra as oc join proveedor using(id_proveedor)
        join moneda using(id_moneda)
        join plantilla_pagos using(id_plantilla_pagos)
        left join orden_de_produccion as op on oc.orden_prod=op.nro_orden
        left join contactos using(id_contacto)
        where oc.nro_orden=$nro_orden";
$datos_oc=sql($query,"<br>Error al traer datos de la OC Nº $nro_orden<br>") or fin_pagina();

$id_licitacion=$datos_oc->fields["id_licitacion"];
$es_presupuesto=$datos_oc->fields["es_presupuesto"];
$nrocaso=$datos_oc->fields["nrocaso"];
$flag_stock=$datos_oc->fields["flag_stock"];
$flag_honorario=$datos_oc->fields["flag_honorario"];
$orden_prod=$datos_oc->fields["orden_prod"];
$id_lic_op=$datos_oc->fields["id_lic_op"];
$simbolo=$datos_oc->fields["simbolo"];

//determinamos el estado de la OC
switch ($datos_oc->fields["estado"])
{
  case 'p': $estado="Pendiente";break;
  case 'r': $estado="Rechazada";break;
  case 'u': $estado="Para Autorizar";break;
  case 'a': $estado="Autorizada";break;
  case 'e': $estado="Enviada";break;
  case 'd': $estado="Parcialmente Pagada";break;
  case 'g': $estado="Totalmente Pagada";break;
  default: die("Error: No se pudo determinar el estado de la Orden de Compra");break;
}//de switch ($datos_oc->fields["estado"])

$fecha_entrega=$datos_oc->fields["fecha_entrega"];
$proveedor=$datos_oc->fields["razon_social"];
$contacto=$datos_oc->fields["contacto"];
$moneda=$datos_oc->fields["moneda"];
$valor_dolar=$datos_oc->fields["valor_dolar"];
$forma_pago=$datos_oc->fields["forma_pago"];
$cliente=$datos_oc->fields["cliente"];
$lugar_entrega=$datos_oc->fields["lugar_entrega"];
$comentarios=$datos_oc->fields["comentarios"];
$internas=$datos_oc->fields["notas_internas"];

//decidimos a que esta asociada la OC
if($id_licitacion && !$es_presupuesto)
 $asociada_a="Asociada a Licitación Nº $id_licitacion";
elseif($id_licitacion && $es_presupuesto) 
 $asociada_a="Asociada a Presupuesto Nº $id_licitacion";
elseif($nrocaso) 
 $asociada_a="Asociada a Caso de Servicio Técnico Nº $nrocaso";
elseif($flag_stock) 
 $asociada_a="Asociada a Stock de Coradir"; 
elseif($flag_honorario) 
 $asociada_a="Asociada a Honorarios de Servicio Técnico";  
elseif($orden_prod) 
 $asociada_a="Asociada a RMA de OP Nº $orden_prod, con Licitación Nº $id_lic_op"; 

echo $html_header;
?>
<br>
<table align="center" width="90%" class="bordes">
 <tr id="mo">
  <td style="font-size:14px"  colspan="2">
   Orden de Compra Nº <?=$nro_orden?>
  </td>
 </tr> 
 <tr class="tabla_datos"> 
  <td style="font-size:14px">
   <?=$asociada_a?>
  </td>
  <td style="font-size:14px">
   Estado: <?=$estado?>
  </td>
 </tr> 
 <tr>
  <td colspan="2" style="font-size:14px">
   <?//archivo que muestra los datos propios de la OC (proveedor, cliente, forma de pago, etc.)
    include("oc_lite/oc_lite_datos_oc.php");
   ?>
  </td>
  </tr>
  <tr >
  <td style="font-size:14px" colspan="2"> 
   <?//archivo que muestra los productos de la OC (proveedor, cliente, forma de pago, etc.)
    include("oc_lite/oc_lite_productos_oc.php");
   ?>
  </td>
 </tr>
 
</table>
<table align="right">
 <tr>
  <td>
   <?$link_volver=encode_link("ord_compra_lite.php",array())?>
   <input type="button" name="volve" value="Volver" onclick="location.href='<?=$link_volver?>';"> 
  </td>
  <td width="50%">
   &nbsp;
  </td>
 </tr>
</table>
<br>

<?
fin_pagina();
?>