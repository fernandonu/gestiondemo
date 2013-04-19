<? 
require_once("../../config.php");
require_once("../ord_compra/fns.php");

$id_prov=$_POST['id_proveedor'] or $id_prov=$parametros['id_proveedor'];
//para recuperar el nombre del proveedor
$query_aux="select razon_social from proveedor where id_proveedor=$id_prov ";
$qa=sql($query_aux) or fin_pagina();
$nbre_prov=$qa->fields['razon_social'];
$nbre_arch_prov=nombre_archivo($nbre_prov);

//esta funcion se usa para crear un excel
excel_header("cuentas de proveedores-".$nbre_arch_prov.".xls");
?>

<script src="<?=$html_root."/lib/funciones.js"?>" ></script>

<html>
<body>

<? 
//con el id del prov recupero los datos desde fact_prov
//nro_de orden cuando tiene una fact_ de proveedor
$c2="select id_factura, nro_factura, monto, fecha_emision, nro_orden
    from general.fact_prov
    left join compras.factura_asociadas using (id_factura)
    left join compras.orden_de_compra using (nro_orden)
    where id_proveedor = $id_prov";

$id_fact_rc=$rc->fields['id_factura'];
$nro_orden_rc=$rc->fields['nro_orden'];
//
$c="select sum(cantidad*precio_unitario) as monto_ordenes, id_factura,
     fact_prov.nro_factura, monto, fecha_emision, nro_orden, moneda
     from general.fact_prov
     left join compras.factura_asociadas using (id_factura)
     left join compras.fila using (nro_orden)
     where fact_prov.id_proveedor = $id_prov    
     group by  id_factura, fact_prov.nro_factura,
     monto, fecha_emision, nro_orden, moneda";
$rc=sql($c) or fin_pagina();
$filas_rc=$rc->RecordCount();

//selecciona notas de credito.ESTADOS: pendientes(estado=0)  reservadas (estado=1) utilizadas (estado=2)
$query_nc="select nota_credito.id_nota_credito,nota_credito.estado,res.fecha, nota_credito.id_moneda, 
moneda.nombre, moneda.simbolo, monto from  general.nota_credito 
join licitaciones.moneda using (id_moneda) 
join general.proveedor using (id_proveedor) left join  
(select * from general.log_nota_credito where tipo='creación') as res 
using (id_nota_credito) where id_proveedor=$id_prov ";
$nc=sql($query_nc) or fin_pagina();
$filas_nc=$nc->RecordCount();

$query_oc="select * from 
(select sum(cantidad*precio_unitario) as monto,orden.nro_orden,orden.id_moneda,orden.estado,orden.simbolo,orden.fecha_entrega,orden.valor_dolar 
from compras.fila join 
(select nro_orden,id_moneda,simbolo,estado,fecha_entrega,valor_dolar from compras.orden_de_compra join licitaciones.moneda using (id_moneda)
 join general.proveedor using (id_proveedor) where id_proveedor=$id_prov and estado <> 'n' ) as orden using (nro_orden)
group by orden.nro_orden,orden.id_moneda,orden.estado,orden.fecha_entrega,orden.valor_dolar,orden.simbolo order by orden.estado)  as res1
left join 
(select sum (monto) as falta_pago,nro_orden  from compras.orden_de_compra 
join compras.pago_orden using (nro_orden)
join compras.ordenes_pagos using (id_pago)
where id_proveedor=$id_prov and estado ='d'
and idbanco isnull and iddébito isnull and id_ingreso_egreso isnull
group by nro_orden )as res2 
using (nro_orden)";
$oc=sql($query_oc) or fin_pagina();
$filas_oc=$oc->RecordCount();
$nro_orden_oc=$oc->fields['nro_orden'];

// hago esta consulta porque si no hay facturas cargadas para ese proveedor
// no puedo traer las ordenes de compra ni la forma de pago
// tengo que solo mostrar los carteles que no hay info asociada 

if ($oc->fields['id_moneda'] == 2) $moneda='Dólares';
else $moneda="";

if ($moneda) $simbolo="u\$s";
   else $simbolo="$";

//calcula el haber para el proveedor (lo que se le debe)
$suma_p=0;
$suma_d=0;
while (!$oc->EOF) { 
  if ($oc->fields['estado'] !='g' and $oc->fields['estado'] != 'd'){
       if ($oc->fields['id_moneda']==1) 
       $suma_p+=$oc->fields['monto'];
       else
       $suma_d+=$oc->fields['monto'];
  }
  elseif ($oc->fields['estado'] == 'd') {
  	   if ($oc->fields['id_moneda']==1) 
  	   $suma_p+=($oc->fields['monto']-$oc->fields['falta_pago']);
  	   else
  	   $suma_d+=($oc->fields['monto']-$oc->fields['falta_pago']);
  }
  $oc->MoveNext();
}

//calcula el haber del proveedor (lo que el proveedor debe)
$debe_p=0;
$debe_d=0;
while (!$nc->EOF) { 
 if ($nc->fields['estado'] !=2) {  //la nota no esta utilizada
   if ($nc->fields['id_moneda']==1) $debe_p+=$nc->fields['monto'];
   else $debe_d+=$nc->fields['monto'];
  }
  $nc->MoveNext();
}
$oc->MoveFirst();
//esto se agrega para saber si las ordenes estan asociadas a un solo pago
//para que el monto sea la suma de todas estas
?>

<table width="80%" id="mo" align="center" border="1" bordercolor="#000000" cellspacing="0" cellpadding="0">
  <tr>
    <td <?=excel_style("texto")?> align="center" colspan="8">
      <b>CUENTAS DEL PROVEEDOR <?=strtoupper($nbre_prov)?></b>
    </td>
  </tr>
  <tr>
    <td <?=excel_style("texto")?> align="center" colspan="2">HABER (del proveedor)</td>
    <td <?=excel_style("texto")?> align="center" colspan="3"><?='$ '.formato_money($suma_p)?></td>
    <td <?=excel_style("texto")?> align="center" colspan="3"><?='U$S '.formato_money($suma_d)?></td>
  </tr>
  <tr>
    <td <?=excel_style("texto")?> align="center" colspan="2">DEBE (del proveedor)</td>
    <td <?=excel_style("texto")?> align="center" colspan="3"><?= '$ '.formato_money($debe_p)?></td>
    <td <?=excel_style("texto")?> align="center" colspan="3"><?= 'U$S '.formato_money($debe_d)?></td>
  </tr>
  <tr>
    <td <?=excel_style("texto")?> align="center" colspan="2">SALDO <FONT color="red"><b>(*)</b></font></td>
    <td <?=excel_style("texto")?> align="center" colspan="3"><?='$ '.formato_money($suma_p - $debe_p)?></td>
    <td <?=excel_style("texto")?> align="center" colspan="3"><?='U$S '.formato_money($suma_d - $debe_d)?></td>
 </tr>
 <tr>
   <td <?=excel_style("texto")?> align="right" colspan="8">
   <FONT color="red"><b>(*)</b></font> 
   Saldo positivo indica el monto que se debe al proveedor </td>
 </tr>
 <tr><td colspan="8">&nbsp;</td></tr>
 <tr>
   <td colspan="8" align="center" width="100%">
     <table width="100%" border="1" bordercolor="#000000" cellspacing="0" cellpadding="0">
<? if ($filas_oc > 0 ) { ?>
       <tr>
        <td <?=excel_style("texto")?> align="center" colspan="8">
          <b>HISTORIAL DE ORDENES DE COMPRA PARA EL PROVEEDOR <?=strtoupper($nbre_prov)?></b> 
        </td>
       </tr>
       <tr>
         <td <?=excel_style("texto")?> align=center>
         <font size="2" family="helvetica, sans-serif" color="#000000"><b>FACTURA <br> PROVEEDOR</b>
         </font></td>
         <td <?=excel_style("texto")?> align=center>
         <font size="2" family="helvetica, sans-serif" color="#000000"><b>FECHA <br> ENTREGA</b>
         </font></td>
         <td <?=excel_style("texto")?> align=center>
         <font size="2" family="helvetica, sans-serif" color="#000000"><b>MONTO <br> FACTURA</b>
         </font></td>
         <td <?=excel_style("texto")?> align=center>
         <font size="2" family="helvetica, sans-serif" color="#000000"><b>VALOR <br> DOLAR</b>
         </font></td>
         <td <?=excel_style("texto")?> align=center>
         <font size="2" family="helvetica, sans-serif" color="#000000"><b>ORDEN</b>
         </font></td>
         <td <?=excel_style("texto")?> align=center>
           <table width="100%" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
             <tr>
               <td colspan="4" <?=excel_style("texto")?> align=center>
              <font size="2" family="helvetica, sans-serif" color="#000000"><b>RESUMEN DE PAGO</b>
               </font></td>
             </tr>
             <tr>
               <td width="25%" align=center>
               <font size="2" family="helvetica, sans-serif" color="#000000"><b>Pago</b>
               </font></td>
               <td width="25%" align=center>
               <font size="2" family="helvetica, sans-serif" color="#000000"><b>NRO.</b>
               </font></td>
               <td width="25%" align=center>
               <font size="2" family="helvetica, sans-serif" color="#000000"><b>Monto</b>
               </font></td>
               <td width="25%" align=center>
               <font size="2" family="helvetica, sans-serif" color="#000000"><b>Fecha</b>
               </font></td>
             </tr>
           </table>         
         </td>
         <td <?=excel_style("texto")?> align=center>
         <font size="2" family="helvetica, sans-serif" color="#000000"><b>MONTO</b>
         </font></td>
         <td <?=excel_style("texto")?> align=center>
         <font size="2" family="helvetica, sans-serif" color="#000000"><b>TOTAL <br> A PAGAR</b>
         </font></td>
       </tr>
<? //muestra las ordenes que estan asociadas con facturas del proveedor 
for ($j=0;$j<$filas_rc;$j++){
  if ($rc->fields['nro_orden']!=""){	
	if ($oc->fields['id_moneda'] == 2) $signos='Dólares';
       else $signos="";
	if ($signos) $simbolos="u\$s";
    else $simbolos="$"; 
    if ($j==0) $rowspan="rowspan=$filas_rc";
    else $rowspan=""; 
//esto se agrega para sacar el total a pagar cuando hay pagos multiples
    $total_a_pagar=0;
    $ordenes_atadas=PM_ordenes($rc->fields['nro_orden']);
    $cant_ordenes=sizeof($ordenes_atadas);
    $tam=sizeof($ordenes_atadas);
    for($i=0;$i<$tam;$i++)
       {$m_orden=monto_a_pagar($ordenes_atadas[$i]);
                      $total_a_pagar+=$m_orden;
                       }//del for 
?>
       <tr>
         <td><?=$rc->fields['id_factura'];?></td>
         <td ><?=fecha($rc->fields['fecha_emision']);?></td>
         <td ><?=$simbolos." ".formato_money($rc->fields['monto']);?></td>
         <td >&nbsp;</td>
<? $control_orden=$rc->fields['nro_orden'];
   $control="select nro_orden, id_proveedor 
             from compras.orden_de_compra 
             where orden_de_compra.nro_orden = $control_orden";
   $rescontrol=sql($control) or fin_pagina();
   if ($rescontrol->fields['id_proveedor']!= $id_prov) {
   	$marca=1;
?>  
         <td><?=$rc->fields['nro_orden'];?><FONT color="red"><b>(#)</b></font></td>
<? } 
   else { 
   	$marca=0;?>
         <td><?=$rc->fields['nro_orden'];?></td>
<? } ?>   
                
         <td><?=resumen_excel($rc->fields['nro_orden'],$simbolos);?></td>     
         <td ><?=$simbolos." ".formato_money($rc->fields['monto_ordenes']);?></td>
<? if ($tam>1) {?>         
         <td rowspan="<?$tam?>"><?=$simbolos." ".formato_money($total_a_pagar);?></td>
<? } else { ?>
         <td><?=$simbolos." ".formato_money($total_a_pagar);?></td> 
<? } }?>
       </tr>  
<? $rc->MoveNext();
    } 
$oc->MoveFirst();
$rc->MoveFirst();    
//muestra las ordenes que no tienen facturas asociadas para el proveedor
for ($j=0;$j<$filas_oc;$j++){
  if ($oc->fields['nro_orden']!=""){
	if ($oc->fields['id_moneda'] == 2) $signos='Dólares';
    else $signos="";
	if ($signos) $simbolos="u\$s";
    else $simbolos="$";
    while (!$rc->EOF && !$oc->EOF)
     { if ($oc->fields['nro_orden'] == $rc->fields['nro_orden'])
         { $oc->MoveNext();
           $rc->MoveFirst();
            }
       else  $rc->MoveNext(); 
          };
      if (!$oc->EOF) { 
//esto se agrega para sacar el total a pagar cuando hay pagos multiples
      $total_a_pagar=0;
      $ordenes_atadas=PM_ordenes($oc->fields['nro_orden']);
      $cant_ordenes=sizeof($ordenes_atadas);
      $tam=sizeof($ordenes_atadas);
      for($i=0;$i<$tam;$i++)
        {$m_orden=monto_a_pagar($ordenes_atadas[$i]);
                      $total_a_pagar+=$m_orden;
                       }//del for
?>
       <tr>
         <td>&nbsp;</td>
         <td>&nbsp;</td>
         <td>&nbsp;</td>
         <td>&nbsp;</td>
         <td><?=$oc->fields['nro_orden'];?></td>
         <td><?=resumen_excel($oc->fields['nro_orden'],$signos);?></td>
         <td><?=$simbolos." ".formato_money($oc->fields['monto']);?></td>
<? if ($tam>1) {?>         
         <td rowspan="<?$tam?>"><?=$simbolos." ".formato_money($total_a_pagar);?></td>
<? } else { ?>
         <td><?=$simbolos." ".formato_money($total_a_pagar);?></td> 
<? } ?>
       </tr> 
<? } } 
   $rc->MoveFirst();
   $oc->MoveNext();
} ?>           
     </table>
   </td>
 </tr>
<? } 
else {?>
  <tr>
    <td <?=excel_style("texto")?> align="center" colspan="8">
      <b>NO HAY ORDENES DE COMPRAS REGISTRADAS PARA EL PROVEEDOR <?=strtoupper($nbre_prov)?></b>
    </td>
  </tr>
<? } 
if ($marca) {
?>
 <tr><td colspan="8" align="right"><FONT color="red"><b>(#)</b></font>
  El <b>Proveedor de la Orden</b> es distinto del <b>Proveedor de la Factura</b>
 </td></tr>
<? } ?> 
 <tr><td colspan="8">&nbsp;</td></tr>
<? if ($filas_nc > 0 ) { ?>
 <tr>
   <td <?=excel_style("texto")?> align="center" colspan="8">
     <b>HISTORIAL NOTAS DE CREDITO PARA EL PROVEEDOR <?=strtoupper($nbre_prov)?></b>
   </td>                                  
 </tr>
 <tr>
  <td <?=excel_style("texto")?> align=center colspan="2"> <font size="2" family="helvetica, sans-serif" color="#000000"><b>ID</b></font></td>
  <td <?=excel_style("texto")?> align=center colspan="2"> <font size="2" family="helvetica, sans-serif" color="#000000"><b>ESTADO</b></font></td>
  <td <?=excel_style("texto")?> align=center colspan="2"> <font size="2" family="helvetica, sans-serif" color="#000000"><b>FECHA CREACION</b></font></td>
  <td <?=excel_style("texto")?> align=center colspan="2"> <font size="2" family="helvetica, sans-serif" color="#000000"><b>MONTO</b></font></td>
 </tr>
 <tr>
<?  $nc->MoveFirst();
    while (!$nc->EOF) {       
?>
  <td align='center' colspan="2"> <?=$nc->fields['id_nota_credito'] ?></td>
  <td align="center" colspan="2">
   <? switch ($nc->fields['estado']) {
    case 0: echo 'PENDIENTE';break; 
    case 1: echo 'RESERVADA';break;
    case 2: echo 'UTILIZADA';break;
    }
    ?>
  </td>
   <?
    if ($nc->fields['fecha'] != ""){ ?>    
  <td align="center" colspan="2"><?=fecha($nc->fields['fecha'])?> </td>
<? }
 else { ?>
  <td align="center" colspan="2">&nbsp;</td>
<? } ?>     
  <td align="center" colspan="2"><?=$nc->fields['simbolo']." ".formato_money($nc->fields['monto'])?></td>
 </tr>
<? $nc->MoveNext();
    } 
 }
else { ?>
  <tr>
   <td <?=excel_style("texto")?> align="center" colspan="8">
    <b>NO HAY NOTAS DE CREDITO REGISTRADAS PARA EL PROVEEDOR <?=strtoupper($nbre_prov)?></b></td>
  </tr>
<? } ?>
</table>
</body>
</html>