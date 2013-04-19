<?php

/*
Autor: quique

MODIFICADA POR
$Author: 
$Revision: 
$Date: 2006/01/05 19:45:24 $
*/

require_once("../../config.php");

echo $html_header;

$sql="SELECT licitacion.id_licitacion,entrega_estimada.nro,entrega_estimada.id_entrega_estimada,entrega_estimada.comprado,
subido_lic_oc.id_subir,subido_lic_oc.vence_oc as vence,
entrega_estimada.fecha_estimada,
falta_factura,falta_remito, usuarios.iniciales as lider_iniciales
FROM (licitaciones.licitacion LEFT JOIN licitaciones.entidad USING (id_entidad))
LEFT JOIN licitaciones.entrega_estimada USING (id_licitacion)
LEFT JOIN licitaciones.subido_lic_oc using (id_entrega_estimada)
LEFT JOIN (select count(id_renglones_oc) as falta_remito,id_subir
			  from licitaciones.renglones_oc
				where id_renglones_oc not in (
				select id_renglones_oc from facturacion.remitos 
				join facturacion.items_remito using (id_remito)
				where estado <> 'a' and id_renglones_oc is not null
				order by id_renglones_oc) 
				group by id_subir) as f_rem using (id_subir)
LEFT JOIN sistema.usuarios on usuarios.id_usuario=licitacion.lider 
LEFT JOIN (select count(id_renglones_oc) as falta_factura,id_subir
						from licitaciones.renglones_oc
						where id_renglones_oc not in (
						select id_renglones_oc from facturacion.facturas 
						join facturacion.items_factura using (id_factura)
						where estado <> 'a' and id_renglones_oc is not null
						order by id_renglones_oc) 
						group by id_subir) as f_fact using (id_subir)

where licitaciones.entrega_estimada.finalizada=0 AND borrada='f'
order by (usuarios.iniciales)
";
?>
 <table align="center">
 <tr>
 <td><font color="Blue">
 <b>Reportes Seguimiento de Produccion</b></font>
 </td>
 </tr>
 </table>
 <table cellspacing=2 cellpadding=5 border=0  width=100% align=center>
 <tr>
 <td width='8%' align=right id=mo><a id=mo>Lider</a>
 <td width='8%' align=right id=mo><a id=mo>Seguimientos</a>
 <td width='8%' align=right id=mo><a id=mo>Vencidos</a>
 <td width='8%' align=right id=mo><a id=mo>Facturado</a>
 <td width='8%' align=right id=mo><a id=mo>Remitido</a>
 <td width='8%' align=right id=mo><a id=mo>Entregas Vencidas</a>
 </tr>
 <?	
$result = sql($sql) or die;
$iniciales=0;
$i=0;
$d=0;
$seg=0;
$fac=0;
$rem=0;
while(!$result->EOF)
{
  
  $fecha_hoy1 = date("d/m/Y",mktime());
  $fecha_vencimiento=$result->fields['fecha_estimada'];
  $ven_oc=$result->fields['vence'];
  if($fecha_vencimiento!="")
  {
  if ((compara_fechas(fecha_db($fecha_hoy1),$fecha_vencimiento)>= 0))//la fecha actual es mayor a la vencida
		       {
		        $i++;
		       }
		    
  }
  else 
  $i++;
  
  if($ven_oc!="")
  {
   if ((compara_fechas(fecha_db($fecha_hoy1),$ven_oc)>= 0))//la fecha actual es mayor a la vencida
		       {
		        $d++;
		       }
		    
  }
  else 
  $d++;		      
  $seg++;
  if ($result->fields["falta_factura"] ==null || $result->fields["falta_factura"] =="")
  $fac=$fac+1;
  if ($result->fields["falta_remito"] ==null || $result->fields["falta_remito"] =="")
  $rem=$rem+1;
  $iniciales=$result->fields['lider_iniciales'];
  $result->movenext();
  if($iniciales!=$result->fields['lider_iniciales'])
  {
	?>
	<tr <?=$atrib_tr?> >
	<td align="center"><b> <?=$iniciales?></b></td>
	<td align="center"><b> <?=$seg?></b></td>
	<td align="center"><b> <?=$d?></b></td>
	<td align="center"><b> <?=$fac?></b></td>
	<td align="center"><b> <?=$rem?></b></td>
	<td align="center"><b> <?=$i?></b></td>
	<?
	$i=0;
    $d=0;
    $seg=0;
    $fac=0;
    $rem=0;
  }	
}
?>
</table>
<TABLE align="center">
<tr>
<td>
         <input type=button name='cerrar_ventana' value='Cerrar Ventana'onclick="window.close();">
    </td>
</tr>
</TABLE>
<?
fin_pagina();
?>