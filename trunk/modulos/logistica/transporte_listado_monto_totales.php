<?php
/* 
$Author: ferni $
$Revision: 1.2 $
$Date: 2006/01/25 17:05:54 $
*/

include("../../config.php");

echo $html_header;

variables_form_busqueda("transporte_listado_con_renglon");

$orden = array(
		"default" => "2",
		"1" => "sum_cant",
		"2" => "nombre_transporte",		
		"3" => "precio_sum",
		
	);

$filtro = array(
		"sum_cant" => "Cantidad de Envios",
		"nombre_transporte" => "Nombre Transporte",
		"precio_sum" => "Monto",
	);

$query="select count (id_envio_renglones) as sum_cant, nombre_transporte, sum (precio_sum) as precio_sum, id_transporte from 
(
select id_envio_renglones, nombre_transporte, id_transporte,
       sum (
	case when (id_moneda = 1) then ((precio * valor_dolar_lic)*cantidad)
				  else (precio*cantidad)	
	end) as precio_sum
        from licitaciones_datos_adicionales.envio_renglones 
          left join licitaciones_datos_adicionales.renglones_bultos using (id_envio_renglones) 
          left join licitaciones_datos_adicionales.datos_envio using (id_envio_renglones)         
          left join licitaciones_datos_adicionales.envio_origen using (id_envio_origen)         
          left join licitaciones_datos_adicionales.envio_destino using (id_envio_destino)         
          left join licitaciones.distrito using (id_distrito)         
          left join licitaciones_datos_adicionales.log_envio_renglones using (id_envio_renglones) 
          left join licitaciones.renglones_oc using (id_renglones_oc)
          left join licitaciones.subido_lic_oc using (id_licitacion) 
	  left join licitaciones.licitacion using (id_licitacion)
          left join licitaciones_datos_adicionales.transporte using (id_transporte)
          
WHERE  envio_cerrado=1 and tipo_log='creacion'

GROUP BY id_envio_renglones , nombre_transporte,id_transporte 
) as sub_con
group by sub_con.nombre_transporte,sub_con.id_transporte
 ";
		
?>
<br>
<form name="form1" method="POST" action="transporte_listado_con_renglon.php">
<center>
<table width="100%">
<tr>
<td align="center">
<?
$link = encode_link("transporte_editor_avanzado.php",array());

list($sql,$total,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar");

$result = sql($sql,"error en busqueda") or die("$sql<br>Error en form busqueda");

echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";


?>
</td>
</tr>
</table>
</CENTER>
<BR>

<TABLE class="bordes" align="center" width="70%" cellspacing="1">
<TR id="ma">
<TD align="left" >Cantidad de transporte: <?=$total?></TD>
<TD colspan="2" align="right"> <?=$link_pagina?></TD>
</TR>
<TR id="mo">
<TD width="20%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up,"tipo"=>$tipo_p))?>'>Cantidad de Envíos</A></TD>
<TD width="50%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up,"tipo"=>$tipo_p))?>'>Transporte</A></TD>
<TD width="30%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up,"tipo"=>$tipo_p))?>'>Monto</A></TD>
</TR>
<? while(!$result->EOF){
	?>
	<?$link = encode_link("transporte_detalle.php",array("code"=>$result->fields["id_transporte"]));?>
	<tr <?=atrib_tr();?> onclick="document.location='<?=$link?>'">
	<TD ><font size="2"><?=$result->fields["sum_cant"];?></font></TD>
	<TD ><font size="2"><b><?=$result->fields["nombre_transporte"];?></b></font></TD>
	<TD align="right"><font size="2"><?echo "$ ".number_format($result->fields["precio_sum"],'2',',','.');?></font></TD>
	</TR>
	<?
	$result->MoveNext();
	}?>
</TABLE>


</FORM>

<?
fin_pagina();
?>
</BODY>