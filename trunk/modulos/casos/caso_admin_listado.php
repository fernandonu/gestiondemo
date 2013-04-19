<?php
  /*
$Author: ferni $
$Revision: 1.4 $
$Date: 2005/10/25 14:47:34 $
*/
include("../../config.php");

variables_form_busqueda("lic_cobranzas_listado_busqueda");
$orden = array (
    "default" => "1",
    "1" => "nrocaso",
    "2" => "nombre",
    "3" => "nro_orden",
    "4" => "precio_unitario",
  );
  
$filtro = array (
    "nrocaso" => "Numero de Caso",
    "nombre" => "Proveedor C.A.S.",
    "nro_orden" => "Numero de Orden de Compra",
    "precio_unitario" => "Precio Unitario",
  ); 

$query="select casos_cdr.nrocaso, cas_ate.nombre, fila.nro_orden, fila.precio_unitario, casos_cdr.idcaso, cas_ate.idate
from casos.casos_cdr 
join compras.fila
on (fila.id_fila=casos_cdr.fila)
join casos.cas_ate 
using (idate)"; 

echo $html_header;
?>

<form name="form1" action="caso_admin_listado.php" method="POST" >
<center>
<?
list($query,$total_productos,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar");
$result=sql($query,"<br>Error<br>") or fin_pagina();
?>

<input type="submit" name="Buscar" value="Buscar">
</center>

<br>

<table width="80%"  align="center" class="bordes">

<tr class="bordes">
	<td id=mo colspan="4" class="bordes">
		<font size="2"><strong> Listado de Casos </strong></font>
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
   	<td id=mo width="25%" class="bordes">
	<a id=mo href='<?=encode_link("caso_admin_listado.php",array("sort"=>"1","up"=>$up))?>'>
    Nro. de Caso
    </a>
    </td> 
   
   <td id=mo width="45%" class="bordes">
   <a id=mo href='<?=encode_link("caso_admin_listado.php",array("sort"=>"2","up"=>$up))?>'>
   Proveedor C.A.S.
   </a>
   </td>
   
   <td id=mo width="15%" class="bordes">
   <a id=mo href='<?=encode_link("caso_admin_listado.php",array("sort"=>"3","up"=>$up))?>'>
   Nro. de Orden de Compra
   </a>
   </td>
   
   <td id=mo width="15%" class="bordes">
   <a id=mo href='<?=encode_link("caso_admin_listado.php",array("sort"=>"4","up"=>$up))?>'>
   Monto de la Fila de la Orden de Compra
   </a>
   </td>
   
</tr> 

<?
	$result->MoveFirst();
	while (!$result->EOF){
		
$link=encode_link("caso_estados.php",Array("id"=>$result->fields["idcaso"],"id_entidad"=>$result->fields['idate']));
?>

<tr <?=atrib_tr()?> onclick="window.open('<?=$link?>')">
  	
	<td class="bordes">
		<b><font size="2"> <strong><?=$result->fields["nrocaso"]?></strong></font></b>
  	</td>
 	
  	<td class="bordes">
 		<b><?
  		echo $result->fields["nombre"];
 		?></b>
  	</td>
  	<td align="center" class="bordes">
 		<b><?
 	 	echo number_format($result->fields["nro_orden"],0,'.','');
 		?></b>
  	</td>
  	<td align="center" class="bordes">
 		<b><?
 	 	echo number_format($result->fields["precio_unitario"],2,'.','');
 		?></b>
  	</td>
 
</tr>

<?
	$result->MoveNext();
	}
?>

</table>