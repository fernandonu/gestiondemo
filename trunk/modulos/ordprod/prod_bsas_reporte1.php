<?php
require_once("../../config.php");
echo $html_header;

/* ATENCION !!!!!
 Esta pagina hace una consulta por los ultimos dias de diciembre de 2005 por que en esta 
 fecha empieza a existir los log de produccion bs as
 */
?>
<br>
<table border=1 width="80%" align="center" cellpadding="3" cellspacing='0' bgcolor=<?=$bgcolor3?>>
<tr id="mo">
 <td colspan="2" width="100%">
 	Cantidad de Maquinas Producidas y entregadas por Coradir Bs As
 </td>
</tr>

<tr id="ma">
 <td width="60%"><font size="2"> 26/12/2005 al 31/12/2005 </font></td>
 <?
 $sql="select sum (cantidad)as cantidad 
		from ordenes.orden_de_produccion 
		left join ordenes.log_op_bsas
		using (nro_orden)    
		where (nuevo_estado=-1) and ((fecha >= '2005-12-26 00:00:00') and (fecha <= '2005-12-31 23:59:59')) and id_ensamblador=4 and estado!='AN'";
 $result= sql ($sql,"No se puede ejecutar la consulta") or fin_pagina();
 ?>
 <td width="40%"><font size="2"><?=$result->fields['cantidad']?></font></td>
</tr>

<?
$anio_inicio=2006;
$mes_inicio=1;

while(($mes_inicio<=date(m))&&(substr($anio_inicio,2)<=date(y))){
	$dia_inicio=1;
	$dia_fin = date("t",mktime(0,0,0,$mes_inicio,$dia_inicio,$anio_inicio));//recupero la cantidad de dias que tiene el mes
	
	$fecha_inicio_db="$anio_inicio-$mes_inicio-$dia_inicio 00:00:00";
	$fecha_fin_db="$anio_inicio-$mes_inicio-$dia_fin 23:59:59";
	
	$sql="select sum (cantidad)as cantidad 
		from ordenes.orden_de_produccion 
		left join ordenes.log_op_bsas
		using (nro_orden)    
		where (nuevo_estado=-1) and ((fecha >= '$fecha_inicio_db') and (fecha <= '$fecha_fin_db')) and id_ensamblador=4 and estado!='AN'";
 	$result= sql ($sql,"No se puede ejecutar la consulta") or fin_pagina();
	
 	switch ($mes_inicio){
 		case 1 : $mes_mostrar="Enero"; break;
 		case 2 : $mes_mostrar="Febrero"; break;
 		case 3 : $mes_mostrar="Marzo"; break;
 		case 4 : $mes_mostrar="Abril"; break;
 		case 5 : $mes_mostrar="Mayo"; break;
 		case 6 : $mes_mostrar="Junio"; break;
 		case 7 : $mes_mostrar="Julio"; break;
 		case 8 : $mes_mostrar="Agosto"; break;
 		case 9 : $mes_mostrar="Septiembre"; break;
 		case 10 : $mes_mostrar="Octubre"; break;
 		case 11 : $mes_mostrar="Noviembre"; break;
 		case 12 : $mes_mostrar="Diciembre"; break;
 	}
 	?>
 	
 	<tr id="ma">
	 <td width="60%"><font size="2"> <?echo $mes_mostrar. " " . $anio_inicio?> </font></td>
	 <td width="40%"><font size="2"><?=$result->fields['cantidad']?></font></td>
	</tr>
	 <?
 
 	if ($mes_inicio==12){
 		$mes_inicio=1;
 		$anio_inicio++;
 	}
 	else $mes_inicio++;
}
?>
</table>
<?fin_pagina();?>