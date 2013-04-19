<?php
/* quique
$Author: enrique $
$Revision: 1.4 $
$Date: 2006/05/23 15:39:26 $
*/

include("../../config.php");

//$tipo_p=$_POST["tipo_producto"] or $tipo_p=$parametros["tipo"] or $tipo_p="";

echo $html_header;
$accion=$parametros['accion'];
variables_form_busqueda("productos_compuestos");
?>
<br>
<form name="form1" method="POST" action="productos_compuestos.php">
<?
if ($up=="") $up = "1";

$seleccion = Array ( "codigo_barra" => "id_producto_compuesto in (select id_producto_compuesto
                                        from general.codigos_barra join 
                                        general.productos_compuestos using (id_producto_compuesto)
                                        where codigo_barra ILIKE '%$keyword%')"
);

$ignorar = Array ( 0 => "codigo_barra");

$orden = array(
		"default" => "1",
		"1" => "productos_compuestos.nro_serie",
		"2" => "orden_de_produccion.id_licitacion",
		"3" => "ordenes.maquina.nro_orden",
		"4" => "licitaciones.entidad.nombre",
		"5" => "log_productos_compuestos.fecha",
		"6" => "id_producto_compuesto"
		);
$filtro = array(
		"productos_compuestos.nro_serie" => "Numero Serie",
		"orden_de_produccion.id_licitacion" => "Id Licitacion",
		"ordenes.maquina.nro_orden" => "Orden Produccion",
		"licitaciones.entidad.nombre" => "Nombre Entidad",
		"log_productos_compuestos.fecha" => "Fecha",
		"productos_compuestos.id_producto_compuesto" => "Id Producto Compuesto",
		"codigo_barra" => "Codigo de Barra",
		);//
$query="select productos_compuestos.nro_serie,id_producto_compuesto,ordenes.maquina.nro_orden,orden_de_produccion.id_licitacion,id_entidad,licitaciones.entidad.nombre,log_productos_compuestos.fecha from general.productos_compuestos left join general.log_productos_compuestos using(id_producto_compuesto)
	left join ordenes.maquina using(nro_serie)left join ordenes.orden_de_produccion using(nro_orden)left join licitaciones.entidad using(id_entidad)";
$where="tipo_log='de creacion'"; 

/*$query1="select id_producto_compuesto,log_productos_compuestos.fecha from general.productos_compuestos left join general.log_productos_compuestos using(id_producto_compuesto)
	where nro_serie=''";
$result1 = sql($query1,"error en busqueda de serie null") or die("$sql<br>Error en form busqueda");*/
?>

<table width="100%">
<tr>
<td align="right">
 <? $link11=encode_link('buscador_prod_comp.php',array("total"=>$total_lic));
    $onclick1="ventana=window.open(\"$link11\",\"\",\"\")";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type=button name=exportar value=Exportar onclick='$onclick1'>";	
    ?>    
</td>
<td align="right" width="70%">
<TABLE align="center" cellspacing="0" class="bordes">
<TR>
<TD>
<?
list($sql,$total,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar","",$ignorar,$seleccion);
$result = sql($sql,"error en busqueda") or die("$sql<br>Error en form busqueda");
echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";
?>
</td>
</TR>
</TABLE>
</td>
<td>
<input type="button" name="nuevo" value="Nuevo Grupo" style="width:180" onclick="document.location='codigos_barra_prod_comp.php'">
<input type="button" name="atar" value="Atar N° Serie" style="width:180" onclick="document.location='atar_numero_serie.php'">
</td>
</table>
</CENTER>
<?
echo "<center><b><font size='2' color='red'>$accion</font></b></center>";
?>
<BR>

<TABLE class="bordes" align="center" width="98%" cellspacing="1">

<tr><td colspan=9 align=left id=ma>
    <table width=100%>
      <tr id=ma>
       <TD colspan="6" align="left" >Cantidad de productos: <?=$total?></TD>
       <td width=70% align=right><?=$link_pagina?></td>
      </tr>
    </table>
  </td></tr>
  
<TR id="mo">
<TD width="8%"><a id=mo href='<?=encode_link("productos_compuestos.php",Array('sort'=>1,'up'=>$up,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'>N° de Serie</A></TD>
<TD width="8%"><a id=mo href='<?=encode_link("productos_compuestos.php",Array('sort'=>2,'up'=>$up,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'>LIcitacion</A></TD>
<TD width="8%"><a id=mo href='<?=encode_link("productos_compuestos.php",Array('sort'=>3,'up'=>$up,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'>Orden de Compra</A></TD>
<TD width="8%"><a id=mo href='<?=encode_link("productos_compuestos.php",Array('sort'=>4,'up'=>$up,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'>Cliente</A></TD>
<TD width="8%"><a id=mo href='<?=encode_link("productos_compuestos.php",Array('sort'=>5,'up'=>$up,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'>Fecha de Creacion</A></TD>
<TD width="15%"><a id=mo href='<?=encode_link("productos_compuestos.php",Array('sort'=>6,'up'=>$up,'page'=>$page,'keyword'=>$keyword,'filter'=>$filter))?>'>Id Producto Compuesto</A></TD>
</TR>
    <? 
    while(!$result->EOF){
	$link = encode_link("codigos_barra_prod_comp.php",array("id_prod"=>$result->fields["id_producto_compuesto"],"pagina"=>1,"serie"=>$result->fields["nro_serie"]));    
	tr_tag($link); 
	?>
	<TD><?=$result->fields["nro_serie"];?></TD>
	<TD ><?=$result->fields["id_licitacion"];?></TD>
	<TD ><?=$result->fields["nro_orden"];?></TD>
	<TD ><?=$result->fields["nombre"];?></TD>
	<TD ><?=Fecha($result->fields["fecha"]);?></TD>
	<TD ><?=$result->fields["id_producto_compuesto"];?></TD>
	</TR>
	<?
	$result->MoveNext();
	}
	/* while(!$result1->EOF){
	$link = encode_link("codigos_barra_prod_comp.php",array("id_prod"=>$result->fields["id_producto_compuesto"],"pagina"=>1));    
	tr_tag($link); 
	?>
	<TD><?=$result1->fields["nro_serie"];?></TD>
	<TD ><?=$result1->fields["id_licitacion"];?></TD>
	<TD ><?=$result1->fields["nro_orden"];?></TD>
	<TD ><?=$result1->fields["nombre"];?></TD>
	<TD ><?=Fecha($result1->fields["fecha"]);?></TD>
	<TD ><?=$result1->fields["id_producto_compuesto"];?></TD>
	<?
	$result1->MoveNext();
	}*/?>
	
</TABLE>
</FORM>
<?
fin_pagina();
?>
</BODY>