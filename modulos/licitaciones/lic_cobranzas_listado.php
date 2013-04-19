<?php
  /*
$Author: ferni $
$Revision: 1.8 $
$Date: 2005/10/04 16:04:41 $
*/
include("../../config.php");

variables_form_busqueda("lic_cobranzas_listado_busqueda");
$orden = array (
    "default" => "1",
    "1" => "nombre",
    "2" => "indicador1",
    "3" => "indicador2",
    "4" => "indicador3",
  );
  
$filtro = array (
    "nombre" => "Nombre de la Entidad",
  ); 

$query="select nombre, indicador1, indicador2, indicador3, id_entidad from (
			select sum (diasXplata.total/plata.total) as indicador1,sum (diasXplata2.total/plata2.total) as indicador2, e.nombre, e.id_entidad, (sum (diasXplata.total/plata.total)-sum (diasXplata2.total/plata2.total)) as indicador3
					from licitaciones.entidad e
                			join
                 			(
                   			select sum (
                            			   case 
                               			when (id_moneda = 1 ) then  float8( ( (date(fin_fecha) - date(fecha_factura)) * monto) ) 
	     	                                     			else  float8( ( (date(fin_fecha) - date(fecha_factura)) * (monto * 3) ) ) 
                               			end )   as total,cobranzas.id_entidad
      			       			from licitaciones.cobranzas 
      			        			where (cobranzas.fin_fecha Is not Null) and (cobranzas.fecha_factura is not Null) 
 		                 			group by cobranzas.id_entidad
                                			order by id_entidad
                   			) as diasXplata 
                  			using (id_entidad) 
                  			join
                  			(
								select sum (case when (id_moneda=1) 
                                                        			then monto 
										else monto*3 end
                                                    			) as total,cobranzas.id_entidad 
								from licitaciones.cobranzas 
								where (cobranzas.fin_fecha Is not Null) and (cobranzas.fecha_factura is not Null) 
								
								group by id_entidad
                   			) as plata on(e.id_entidad=plata.id_entidad)
							join 
							(
                  			select sum (
                            			   case 
                               			when (id_moneda = 1 ) then  float8( ( (date(fin_fecha) - date(fecha_presentacion)) * monto) ) 
	     	                            			         else  float8( ( (date(fin_fecha) - date(fecha_presentacion)) * (monto * 3) ) ) 
                               			end )   as total,cobranzas.id_entidad
      			       			from licitaciones.cobranzas 
      			        			where (cobranzas.fin_fecha Is not Null) and (cobranzas.fecha_presentacion is not Null) 
 		                 			group by cobranzas.id_entidad
                                			order by id_entidad
					) as diasxplata2 on(e.id_entidad=diasxplata2.id_entidad)

					join
                		(
						select sum (case when (id_moneda=1) 
                        	                             then monto 
														else monto*3 end
                                   ) as total,cobranzas.id_entidad 
						from licitaciones.cobranzas 
						where (cobranzas.fin_fecha Is not Null) and (cobranzas.fecha_presentacion is not Null) 
					
						group by id_entidad
                	) as plata2 on(e.id_entidad=plata2.id_entidad)
					group by e.id_entidad, e.nombre 
					) as posta "; 

echo $html_header;
?>

<form name="form1" action="lic_cobranzas_listado.php" method="POST" >
<center>
<?
list($query,$total_productos,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar");
$result=sql($query,"<br>Error<br>") or fin_pagina();
?>

<input type="submit" name="Buscar" value="Buscar">
</center>

<br>
<br>
<table width="80%"  align="center" class="bordes">

<tr class="bordes">
	<td id=mo colspan="4" class="bordes">
		<font size="2"><strong> Listado de Clientes y Tiempos Promedio de Cobro </strong></font>
	</td>
</tr>

<tr> 
<td colspan="4">
	<table width="100%">
     <tr  id=ma_sf>
      <td align=left>
       <b>Total: </b><?=$total_productos?> Clientes
       </td>
       <td align="right">
        <?=($link_pagina)?$link_pagina:"&nbsp;"?>
       </td>
      </tr>
    </table>  
</td>
</tr>

<tr class="bordes">
   	<td id=mo width="40%" class="bordes">
	<a id=mo href='<?=encode_link("lic_cobranzas_listado.php",array("sort"=>"1","up"=>$up))?>'>
     Nombre de la Entidad
    </a>
    </td> 
   
   <td id=mo width="20%" class="bordes">
   <a id=mo href='<?=encode_link("lic_cobranzas_listado.php",array("sort"=>"2","up"=>$up))?>'>
     Tiempo Promedio de Cobro (en dias) por Unidad Monetaria (Fecha Creacion a Fecha Cierre)
   </a>
   </td>
   
   <td id=mo width="20%" class="bordes">
	<a id=mo href='<?=encode_link("lic_cobranzas_listado.php",array("sort"=>"3","up"=>$up))?>'>
   Tiempo Promedio de Cobro (en dias) por Unidad Monetaria (Fecha Presentacion a Fecha Cierre)
   </a>
   </td>
   
   <td id=mo width="20%" class="bordes">
	<a id=mo href='<?=encode_link("lic_cobranzas_listado.php",array("sort"=>"4","up"=>$up))?>'>
   Fecha Creacion a Fecha de Presentación
   </a>
   </td>
   
</tr> 

<?
	$result->MoveFirst();
	while (!$result->EOF){

$link=encode_link("lic_cobranzas.php",array("filtro"=>"todas","keyword"=>$result->fields["nombre"],"cmd"=>"finalizada"));

?>
<tr <?=atrib_tr()?> onclick="window.open('<?=$link?>')">
  	
	<td class="bordes">
		<b><font color="#0000CC"><strong><?=$result->fields["nombre"]?></strong></font></b>
  	</td>
 	
  	<td align="center" class="bordes">
 		<b><?
  		echo number_format($result->fields["indicador1"],0,'.','');
 		?></b>
  	</td>
  	<td align="center" class="bordes">
 		<b><?
 	 	echo number_format($result->fields["indicador2"],0,'.','');
 		?></b>
  	</td>
  	<td align="center" class="bordes">
 		<b><?
 	 	echo number_format($result->fields["indicador3"],0,'.','');
 		?></b>
  	</td>
 
</tr>

<?
	$result->MoveNext();
	}
?>

</table>