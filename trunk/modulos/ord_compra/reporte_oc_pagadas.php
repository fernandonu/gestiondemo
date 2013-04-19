<?PHP
require_once("../../config.php");

if($_POST['no_tiene_factura']=='Marcar Sin Factura'){
	$nro_orden=$_POST['radio_nro_orden'];
	
	if ($nro_orden!=''){
		$link=encode_link("comentario_marca_factura.php",array('nro_orden' => $nro_orden));
    	header("Location:$link") or die("No se encontró la página destino");
	}
	else{
		$mensaje="Debe Seleccionar Una Orden de Compra/Pago";
	}
}

variables_form_busqueda("seguimiento_produccion_bsas");
echo $html_header; 

$orden = array(
       "default" => "1",
       "default_up" => "0",
       "1" => "nro_orden",
       "2" => "ord_pago", 
       "3" => "razon_social",
       "4" => "total_sum",
       "5" => "estado",
      ); 
      
$filtro = array(        
        "nro_orden" => "Número de Orden", 
        "razon_social" => "Proveedor",           
    );     
    
$sql_tmp="SELECT distinct(o.nro_orden),o.desc_prod,total_orden,total_sum,p.razon_social,o.estado,f.id_fact_asociada, ord_pago, simbolo  
			FROM compras.orden_de_compra o 
			left join general.proveedor p using(id_proveedor) 
			left join
				(select sum(cantidad*(case when estado='n' then 0 else precio_unitario end)) as total_sum,sum(cantidad*precio_unitario) as total_orden,nro_orden 
					from compras.fila 
					join compras.orden_de_compra using (nro_orden) 
					group by nro_orden
				) costo using(nro_orden) 
			left join licitaciones.moneda on(moneda.id_moneda=o.id_moneda) 
			left join compras.factura_asociadas f using (nro_orden)";

$where_tmp=" (estado='d' OR estado='g' OR estado='e') AND id_fact_asociada is null AND razon_social not like '%Stock%' AND no_tiene_factura!=1";

$contar="buscar";
if($_POST['keyword'] || $keyword) $contar="buscar";

if ($parametros['mensaje']!='') $mensaje=$parametros['mensaje'];
     
?>
<form name="reporte_oc_pagadas" action='reporte_oc_pagadas.php' method='post'>

<table align="center">
 <tr align="center">
 	<td align="center" colspan="6">		
 		<font size="3" color="Red"><b><?=$mensaje?></b></font>
 	</td>
 </tr>
</table>
	
<table align="center" >
 <tr>
  <td>
<?
 list($sql,$total_lic,$link_pagina,$up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,$contar); 
 //echo "<br>".$sql."<br>";  
 $resul_consulta=sql($sql,"No se pudo realizar la consulta del form busqueda") or fin_pagina();
 ?>
  </td>
  <td>
   <input type=submit name=form_busqueda value='Buscar'>&nbsp;
  </td>
  
  <td>
  <?
   $link=encode_link("reporte_oc_pagadas_excel.php",array());
  ?>
   <a target=_blank title='Bajar datos en un excel' href='<?=$link?>'>
    <img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' >
   </a>
  </td>
  
 </tr>
</table> 

<br>
<table width='100%' align="center" cellspacing="2" cellpadding="2" class="bordes" bgcolor="<?=$bgcolor2?>">
	<tr>
	 <td align="center">
		<input type="submit" name="no_tiene_factura" value="Marcar Sin Factura">&nbsp;&nbsp;
		<input type="button" name="cerrar" value="Cerrar" onclick="window.close();">
	</td>
   </tr>
</table>
<table width='100%' align="center" cellspacing="2" cellpadding="2" class="bordes">
 <tr id=ma>
  <td align="left" colspan="4">
   <b>Total:</b> <?=$total_lic?> <b>Ordene/s.</b>   
  </td>
  <td align="right" colspan="2">
   <?=$link_pagina;?>
  </td>
 </tr>
 
 <tr id=mo>
  <td width="5%" ><b>&nbsp;</b></td>
  <td width="10%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>OC</a></b></td>
  <td width="7%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Orden de Pago</a></b></td>
  <td width="40%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Proveedor</a></b></td>
  <td width="20%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Monto</a></b></td>
  <td width="18%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Estado</a></b></td>
 </tr>
  
    <? 
    while (!$resul_consulta->EOF){
    	
    	   $nro_orden=$resul_consulta->fields['nro_orden'];?>
	
    <tr bgcolor="<?=$color_fila?>" id=ma>
    	   <td align="center"> <input type="radio" name="radio_nro_orden" value="<?=$resul_consulta->fields['nro_orden']?>"> </td>
    	   <td align="center"><?=$resul_consulta->fields['nro_orden']?></td>
    	   <td align="center"><?=$resul_consulta->fields['ord_pago']?></td>
		   <td align="left"><?=$resul_consulta->fields['razon_social']?></td>
		   <td align="left"><font size="2"><b><?echo $resul_consulta->fields['simbolo'] .'  ' . number_format($resul_consulta->fields['total_sum'],2,',','.')?></b></font></td>
		   <? switch ($resul_consulta->fields['estado']){
		   	case 'd' : $estado_mostrar='Pagadas Parcialmente';
		   		break;
		   	case 'g' : $estado_mostrar='Pagadas Totalmente';
		   		break;	
		   	case 'e' : $estado_mostrar='Enviadas';
		   		break;	   		
		   }
		   ?>
		   <td align="left"><?=$estado_mostrar?></td>
    </tr> 
    
  	<?$resul_consulta->MoveNext();
    }
  	?>
</table> 
<br>
</form>
<?=fin_pagina();?>