<?
/*
Modificada por
$Author: mari $
$Revision: 1.2
$Date: 2006/07/13 19:10:12 $
*/

require_once("../../config.php");
echo $html_header;
cargar_calendario();
?>
<form name='form1' method="POST" action="informe_rma.php">
<div align="center">
    <font color="blue" size="+1"> Informe por estado </font>
</div>
<br>
<?
//Monto por estado  en la fecha actual
$sql="select monto, lugar from stock.estado_rma
	  left join (
	  select sum(precio_stock*cantidad) as monto,id_estado_rma 
	  from stock.en_stock join general.producto_especifico using(id_prod_esp) 
	  join stock.info_rma using (id_en_stock) 
	  WHERE en_stock.id_deposito=9 and cantidad > 0 
	  group by id_estado_rma) as sub
	  using (id_estado_rma)
	  where nombre_corto <> 'B' order by orden";

$res=sql($sql,"$sql") or fin_pagina();
?>
<table class=bordes align="center" width="50%">
  <tr id=mo>
    <td>Estado</td>
    <td>Monto</td>
  </tr>

<?

while (!$res->EOF) {?>
  <tr bgcolor='#B7C7D0'> 
      <td align="left"><?=$res->fields['lugar']?></td>
      <td align="left"><?="U\$S ".formato_money($res->fields['monto'])?></td>
  </tr>
<?
$res->movenext();
}
?>
</table>

<?
$fecha_desde=$_POST['fecha_desde'] or $fecha_desde=date("d/m/Y",mktime());
$fecha_hasta=$_POST['fecha_hasta'] or $fecha_hasta=date("d/m/Y",mktime());

$fecha_d=fecha_db($fecha_desde)." 00:00:00";
$fecha_h=fecha_db($fecha_hasta)." 23:59:59";

//ingresos
$sql_f=" select sum(precio_stock*cantidad) as total from
          stock.en_stock 
		  join general.producto_especifico using(id_prod_esp) 
	      join stock.info_rma using (id_en_stock)
		  join (select id_info_rma,fecha,tipo_log from stock.log_info_rma
		       where tipo_log ilike '%Creacion%'
               and fecha between '$fecha_d' and '$fecha_h') as log_rma using (id_info_rma)
          where en_stock.id_deposito=9 and cantidad > 0";

$res_fecha=sql($sql_f,"$sql_f") or fin_pagina();

?>

<br>
<table align="center" width="75%">
   <tr bgcolor="white" > 
     <td align="center"><b>Desde: </b><input size='10' type="text" name="fecha_desde" value='<?=$fecha_desde?>'> <?=link_calendario("fecha_desde");?>
         &nbsp;
         <b>Hasta: </b><input size='10' type="text" name='fecha_hasta' value='<?=$fecha_hasta?>'> <?=link_calendario("fecha_hasta");?>
         &nbsp;
         <input type='submit' name="datos" value='Ver datos'></td>
   </tr>
</table>

<br>

<table class=bordes align="center" width="50%">
  <tr id=mo>
     <td colspan="2">Ingresos a RMA</td>
  </tr>
   <tr id="ma_sf">
     <td style="cursor:hand" title="Ver detalles" onclick="ventana_det=window.open('<?=encode_link('detalle_info_rma.php',array("fecha_d"=>$fecha_d,"fecha_h"=>$fecha_h,"ingresos"=>1))?>','','');"><u>Ingresos</u></td>
     <td align="center" width="25%"><? echo "U\$S ".formato_money($res_fecha->fields['total'])?></td>
   </tr>
</table>
<br>
<?//egresos
$sql_eg="select total,lugar from 
         (select id_estado_rma,lugar,orden from stock.estado_rma where lugar ilike 'Baja%') as r1
          left join (select sum(precio_stock*cantidad) as total,tipo_log,info_rma.id_estado_rma 
           from stock.en_stock join general.producto_especifico using(id_prod_esp)
           join stock.info_rma using (id_en_stock) join stock.estado_rma using (id_estado_rma) join stock.log_info_rma using (id_info_rma) 
           where en_stock.id_deposito=9 and cantidad > 0 and tipo_log ilike 'Baja%' 
           and fecha between '$fecha_d' and '$fecha_h'  group by tipo_log,info_rma.id_estado_rma,orden order by orden ) as r
on r1.id_estado_rma=r.id_estado_rma order by orden";
$res_eg=sql($sql_eg,"$sql_eg") or fin_pagina();
?>

<table class=bordes align="center" width="50%">
<tr id=mo>
 <td colspan="2"> Egresos a RMA </td>
</tr>
<? while (!$res_eg->EOF) {?> 
   <tr id="ma_sf">
     <td align="left">
        <?=$res_eg->fields['lugar']?>
      </td>
      <td width="25%">
        <?="U\$S ".formato_money($res_eg->fields['total']);?>
      </td>
   </tr>
<?$res_eg->Movenext();
}?>
</table>
<br>
<table align="center">   
   <tr>
     <td align="center" ><input type="button" name="cerrar" value='Cerrar' onclick="window.close();"></td>
   </tr>
   
</table>