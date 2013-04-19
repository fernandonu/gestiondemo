<?PHP
/*
Autor Broggi

$Author: ferni $
$Revision: 1.6 $
$Date: 2006/03/09 22:06:37 $
*/



require_once("../../config.php");
//require_once("../ord_compra/fns.php");

/* convierte una fecha al timestamp tipo unix y como resultado da el numero de segundo 
recibe como parametros tipos fecha-hora con formato unix timestamp*/
function resta_fecha($t1,$t2)
{
$tot_segundos = (strtotime($t1)-strtotime($t2));    /* total de segundos */

return $tot_segundos;
//$dias=(int)($tot_segundos/86400);    /* obtiene el numero de dias */
//$tot_segundos=$tot_segundos- ($dias*86400);   /* lo resto para obtener el saldo */

//$hora=(int)($tot_segundos/3600);   /* obtiene el numero de hora */
//$tot_segundos=$tot_segundos- ($hora*3600);

//$minuto=(int)($tot_segundos/60);     /* numero de minutos */
//$tot_segundos=$tot_segundos- ($minuto*60);

//$segundo = $tot_segundos;

/* el resultado se concatena en la variable $tiempo */
//$tiempo=$dias." Dias ".$hora." Horas ".$minuto." Min.";
//return $tiempo;
//return array ("dias"=>$dias, "horas"=>$hora, "min"=>$minuto);
}

variables_form_busqueda("seguimiento_produccion_bsas");
echo $html_header; 
   
$orden = array(
       "default" => "1",
       //"default_up" => "1",
       "1" => "ordenes.orden_de_produccion.id_licitacion",
       "2" => "ordenes.orden_de_produccion.nro_orden", 
       "3" => "licitaciones.entidad.nombre",
       "4" => "estado_bsas",
      ); 
      
$filtro = array(        
        "licitaciones.entidad.nombre" => "Entidad",                
        "ordenes.orden_de_produccion.id_licitacion" => "ID de licitación",        
		"ordenes.orden_de_produccion.nro_orden" => "Número de orden",		
    );     
    
$sql_tmp="select id_licitacion, orden_de_produccion.nro_orden,entidad.nombre,estado_bsas, cantidad
			from ordenes.orden_de_produccion 
			left join licitaciones.entidad using(id_entidad)
			left join licitaciones.licitacion using(id_licitacion)";

$where_tmp=" estado!='AN' and nro_orden>=1072";

$contar="buscar";
if($_POST['keyword'] || $keyword) $contar="buscar";
     
?>
<form name="produccion_bs_as_reporte" action='prod_bsas_reporte2.php' method='post'>
	
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
 </tr>
</table> 
<br>
<table width='100%' align="center" cellspacing="2" cellpadding="2" class="bordes">
 <tr id=ma>
  <td align="left" colspan="3">
   <b>Total:</b> <?=$total_lic?> <b>Orden/es.</b>   
  </td>
  <td align="right" colspan="6">
   <?=$link_pagina?>
  </td>
 </tr>
 
 <tr id=mo>
  <td width="5%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>ID</a></b></td>
  <td width="5%" ><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>OP</b></td>
  <td width="30%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Entidad</b></td>
  <td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Estado Actual</b></td>
  <td width="10%"><b>Cantidad</b></td>
  <td width="10%"><b>Producción (en dias)</b></td>
  <td width="10%"><b>Inspección. (en dias)</b></td>
  <td width="10%"><b>Embalaje (en dias)</b></td>
  <td width="10%"><b>Calidad (en dias)</b></td>
 </tr>
  
    <? 
    while (!$resul_consulta->EOF){
    	$nro_orden=$resul_consulta->fields['nro_orden'];
    ?>
    <tr <?=atrib_tr()?>>

           <td align="center"><?=$resul_consulta->fields['id_licitacion']?></td>
           <td align="center"><?=$resul_consulta->fields['nro_orden']?></td>
           <td align="left"><?=$resul_consulta->fields['nombre']?></td>
           <?
           if ($resul_consulta->fields['estado_bsas']=="")$estado_bsas="Pendiente";
           if ($resul_consulta->fields['estado_bsas']==1)$estado_bsas="En Producción";
           if ($resul_consulta->fields['estado_bsas']==5)$estado_bsas="En Inspección";
           if ($resul_consulta->fields['estado_bsas']==3)$estado_bsas="En Embalaje";
           if ($resul_consulta->fields['estado_bsas']==4)$estado_bsas="Calidad";
           if ($resul_consulta->fields['estado_bsas']==2)$estado_bsas="Historial";
           ?>
           <td align="center"><?=$estado_bsas?></td>
           <td align="center"><?=$resul_consulta->fields['cantidad']?></td>
           <?
           	$sql="select log_op_bsas.* 
					from ordenes.orden_de_produccion 
					left join ordenes.log_op_bsas
					using (nro_orden)    
					where nro_orden=$nro_orden and nuevo_estado=1
					order by id_log_op_bsas";
           	$result_1 = sql($sql,"No se Puede Ejecutar la Consulta") or fin_pagina();
           	
           	$sql="select log_op_bsas.* 
					from ordenes.orden_de_produccion 
					left join ordenes.log_op_bsas
					using (nro_orden)    
					where nro_orden=$nro_orden and nuevo_estado=5
					order by id_log_op_bsas";
           	$result_2 = sql($sql,"No se Puede Ejecutar la Consulta") or fin_pagina();
           	
           	$segundos_final_sum=0;
           	$segundos_final=0;
           	/*
           	$fecha=null;
           	$dias_final=0;
           	$horas_final=0;
           	$minutos_final=0;*/
           	           	
           	while ((!$result_1->EOF) and (!$result_2->EOF)){
           		
           		$segundos_final=resta_fecha($result_2->fields['fecha'],$result_1->fields['fecha']);
           		
				$segundos_final_sum=$segundos_final_sum+$segundos_final;
           		/*$dias_final=$dias_final+$fecha['dias'];
				$horas_final=$horas_final+$fecha['horas'];
				$minutos_final=$minutos_final+$fecha['min'];*/
			
           		$result_1->MoveNext();
           		$result_2->MoveNext();
           	}
           	/*
           	$dias_final=$dias_final+($horas_final/24);
           	$horas_final=$horas_final+($minutos_final/60);
           	$dias_final=$dias_final %365;
           	$horas_final=$horas_final %24;
           	$minutos_final= $minutos_final % 60;*/
           	
           ?>
           <td align="center"><?=number_format(($segundos_final_sum/86400),2,',','.')?></td>

           <?
           	$sql="select log_op_bsas.* 
					from ordenes.orden_de_produccion 
					left join ordenes.log_op_bsas
					using (nro_orden)    
					where nro_orden=$nro_orden and nuevo_estado=5
					order by id_log_op_bsas";
           	$result_1 = sql($sql,"No se Puede Ejecutar la Consulta") or fin_pagina();
           	
           	$sql="select log_op_bsas.* 
					from ordenes.orden_de_produccion 
					left join ordenes.log_op_bsas
					using (nro_orden)    
					where nro_orden=$nro_orden and nuevo_estado=3
					order by id_log_op_bsas";
           	$result_2 = sql($sql,"No se Puede Ejecutar la Consulta") or fin_pagina();
           	
           	$segundos_final_sum=0;
           	$segundos_final=0;
           	while ((!$result_1->EOF) and (!$result_2->EOF)){
           		
           		$segundos_final=resta_fecha($result_2->fields['fecha'],$result_1->fields['fecha']);
				$segundos_final_sum=$segundos_final_sum+$segundos_final;
           		
           		$result_1->MoveNext();
           		$result_2->MoveNext();
           	}?>
           <td align="center"><?=number_format(($segundos_final_sum/86400),2,',','.')?></td>           
           
           <?
           	$sql="select log_op_bsas.* 
					from ordenes.orden_de_produccion 
					left join ordenes.log_op_bsas
					using (nro_orden)    
					where nro_orden=$nro_orden and nuevo_estado=3
					order by id_log_op_bsas";
           	$result_1 = sql($sql,"No se Puede Ejecutar la Consulta") or fin_pagina();
           	
           	$sql="select log_op_bsas.* 
					from ordenes.orden_de_produccion 
					left join ordenes.log_op_bsas
					using (nro_orden)    
					where nro_orden=$nro_orden and nuevo_estado=4
					order by id_log_op_bsas";
           	$result_2 = sql($sql,"No se Puede Ejecutar la Consulta") or fin_pagina();
           	
           	$segundos_final_sum=0;
           	$segundos_final=0;
           	while ((!$result_1->EOF) and (!$result_2->EOF)){
           		
           		$segundos_final=resta_fecha($result_2->fields['fecha'],$result_1->fields['fecha']);
				$segundos_final_sum=$segundos_final_sum+$segundos_final;
           		
           		$result_1->MoveNext();
           		$result_2->MoveNext();
           	}?>
           <td align="center"><?=number_format(($segundos_final_sum/86400),2,',','.')?></td>           
          
           <?
           	$sql="select log_op_bsas.* 
					from ordenes.orden_de_produccion 
					left join ordenes.log_op_bsas
					using (nro_orden)    
					where nro_orden=$nro_orden and nuevo_estado=4
					order by id_log_op_bsas";
           	$result_1 = sql($sql,"No se Puede Ejecutar la Consulta") or fin_pagina();
           	
           	$sql="select log_op_bsas.* 
					from ordenes.orden_de_produccion 
					left join ordenes.log_op_bsas
					using (nro_orden)    
					where nro_orden=$nro_orden and nuevo_estado=-1
					order by id_log_op_bsas";
           	$result_2 = sql($sql,"No se Puede Ejecutar la Consulta") or fin_pagina();
           	
           	$segundos_final_sum=0;
           	$segundos_final=0;
           	while ((!$result_1->EOF) and (!$result_2->EOF)){
           		
           		$segundos_final=resta_fecha($result_2->fields['fecha'],$result_1->fields['fecha']);
				$segundos_final_sum=$segundos_final_sum+$segundos_final;
           		
           		$result_1->MoveNext();
           		$result_2->MoveNext();
           	}?>
           <td align="center"><?=number_format(($segundos_final_sum/86400),2,',','.')?></td>           
 
    </tr> 
    
  	<?$resul_consulta->MoveNext();
     }?>
</table> 
<br>
</form>
<?=fin_pagina();?>

