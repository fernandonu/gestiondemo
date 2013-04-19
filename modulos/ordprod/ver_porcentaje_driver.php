<?php
  /*
$Author: ferni $
$Revision: 1.1 $
$Date: 2006/11/21 19:53:57 $
*/
include("../../config.php");

echo $html_header;

//inicio de calculo de porcentaje de maquinas con driver
$sql_1="select * from
	(select count(distinct(maquina.nro_orden)) as total from ordenes.orden_de_produccion 
	rigth join ordenes.maquina Using(nro_orden)) as total
union(
select count(distinct(maquina.nro_orden)) as total from ordenes.orden_de_produccion 
join ordenes.maquina Using(nro_orden)
join maquinas.drivers using (nro_serie)
WHERE (sync is null or sync<>2))";
$res_1=sql($sql_1) or fin_pagina();
$res_1->Movefirst();
$numerador=$res_1->fields['total'];
$res_1->MoveNext();
$denominador=$res_1->fields['total'];
$total_1=(($numerador/$denominador)*100);
//FIN de calculo de porcentaje de maquinas con driver
?>

<form name="form1" method="post" action="ver_porcentaje_driver.php">
<br>
<table  border="0" cellspacing="0" cellpadding="0" width="100%">  
<tr> 
    	<td align="center" width="45%"> 
    		<strong> <font size="2" color="Black">
    		Actualmente hay un <font size="2" color="Red"><?=number_format($total_1,2,',','.')?></font> % de Maquinas con Driver.
    		</strong> </font>
    	</td>     	
</tr>
</table>
<br>
<br>  

<div align="center">
    <input type="button" name="Cerrar" value="Cerrar" style="width:19%" style="cursor:hand" title="Presione aqui para cerrar la Ventana" onclick="window.close()">
</div>

</form>