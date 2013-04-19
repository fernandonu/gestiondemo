<?php
/* MAD
$Author: cestila $
$Revision: 1.17 $
$Date: 2006/02/15 22:03:12 $
*/

include("../../config.php");

$tipo_p=$_POST["tipo_producto"] or $tipo_p=$parametros["tipo"] or $tipo_p="";

echo $html_header;

variables_form_busqueda("productos_codigob");



$orden = array(
		"default" => "1",
		"1" => "u.codigo_barra",
		//"2" => "log.nro_orden",
		"5" => "u.descripcion",
		"6" => "u.precio"
	);

$filtro = array(
		"u.codigo_barra" => "Código Barra",
		//"log.nro_orden" => "OC",
		"u.precio" => "Precio",
		"u.descripcion" => "Descripción"
	);

/*$query="select *
from(
	select codigos_barra.codigo_barra,log_codigos_barra.nro_orden, log_codigos_barra.id_info_rma,puesto_servicio_tecnico,
		codigos_barra.id_producto, codigos_barra.id_prod_esp, log_codigos_barra.tipo, tipos_prod.id_tipo_prod as tipo_prod,
		(case when (codigos_barra.id_producto is not null) then productos.desc_gral
			else producto_especifico.descripcion
		end) as descripcion,
		(case when (codigos_barra.id_producto is not null) then productos.precio_licitacion
			else producto_especifico.precio_stock
		end) as precio
	from general.codigos_barra
		left join general.producto_especifico using(id_prod_esp)
		left join general.productos on(productos.id_producto=codigos_barra.id_producto)
		left join general.log_codigos_barra on(log_codigos_barra.codigo_barra=codigos_barra.codigo_barra and (log_codigos_barra.tipo ilike '%Ingresado%'))
		join general.tipos_prod on (tipos_prod.id_tipo_prod=productos.id_tipo_prod or tipos_prod.id_tipo_prod=producto_especifico.id_tipo_prod)
	)as t ";*/
$query="select u.*, tipos_prod.id_tipo_prod as tipo_prod
	from
		(
			select codigos_barra.codigo_barra, codigos_barra.id_producto as id_prod, codigos_barra.puesto_servicio_tecnico,
				productos.desc_gral as descripcion, productos.precio_licitacion as precio, productos.id_tipo_prod
			from general.productos join general.codigos_barra using (id_producto)
				union
			select codigos_barra.codigo_barra, codigos_barra.id_prod_esp as id_prod, codigos_barra.puesto_servicio_tecnico,
				producto_especifico.descripcion as descripcion, producto_especifico.precio_stock as precio, producto_especifico.id_tipo_prod
			from general.producto_especifico join general.codigos_barra using (id_prod_esp)
		)as u
		join general.tipos_prod using (id_tipo_prod) ";
if ($tipo_p != "") $where=" id_tipo_prod =$tipo_p";

?>
<br>
<form name="form1" method="POST" action="productos_codigob.php">
<script>
//ventana de codigos de barra
var vent_cb=new Object();
vent_cb.closed=true;
</script>

<table width="100%">
<tr>
<td align="right" width="70%">
<TABLE align="center" cellspacing="0" class="bordes">
<TR>
<td><STRONG>Tipo de Producto</STRONG>
<select name="tipo_producto" onKeypress= "buscar_op(this);" onblur="borrar_buffer()" onclick="borrar_buffer()" style="width:305px">
 <option value="" <?php if ($tipo_p=='') echo "selected" ?>>Todos
<?php
$sql="select descripcion, id_tipo_prod from tipos_prod order by descripcion";
$resultado_desc = $db->Execute($sql) or die($db->ErrorMsg());
while (!$resultado_desc->EOF){
?>
  <option value="<?php echo $resultado_desc->fields['id_tipo_prod']; ?>" <?php if ($tipo_p==$resultado_desc->fields['id_tipo_prod']) echo "selected" ?>><?php echo $resultado_desc->fields['descripcion']; ?>
<?php
  $resultado_desc->MoveNext();
}
?>
</select>
</TD>
<TR>
<TD>
<?
/*if ($_POST["keyword"] || $_POST["tipo_producto"]) $buscar_key="buscar";
else $buscar_key="select count(codigo_barra) from general.codigos_barra";*/
if ($_POST["keyword"] || $_POST["tipo_producto"]){
	if ($_POST["filter"]=="u.codigo_barra")	$buscar_key="select count(codigo_barra) as total from general.codigos_barra where codigo_barra ilike '%".$_POST["keyword"]."%'";
	/*elseif ($_POST["filter"]=="log.nro_orden")	$buscar_key="select count (id_log_codigos_barra) as total
		from general.log_codigos_barra log
		WHERE nro_orden ILIKE '%".$_POST["keyword"]."%' and log.tipo ilike '%Ingresado%'";*/
	elseif ($_POST["filter"]=="u.precio")	$buscar_key="select count(codigo_barra) as total from (
			select codigos_barra.codigo_barra, codigos_barra.id_producto as id_prod, codigos_barra.puesto_servicio_tecnico,
				productos.desc_gral as descripcion, productos.precio_licitacion as precio, productos.id_tipo_prod
			from general.productos join general.codigos_barra using (id_producto)
				union
			select codigos_barra.codigo_barra, codigos_barra.id_prod_esp as id_prod, codigos_barra.puesto_servicio_tecnico,
				producto_especifico.descripcion as descripcion, producto_especifico.precio_stock as precio, producto_especifico.id_tipo_prod
			from general.producto_especifico join general.codigos_barra using (id_prod_esp)
		)as u where precio ilike '%".$_POST["keyword"]."%'";
	elseif ($_POST["filter"]=="u.descripcion")	$buscar_key="select count(codigo_barra) as total from (
			select codigos_barra.codigo_barra, codigos_barra.id_producto as id_prod, codigos_barra.puesto_servicio_tecnico,
				productos.desc_gral as descripcion, productos.precio_licitacion as precio, productos.id_tipo_prod
			from general.productos join general.codigos_barra using (id_producto)
				union
			select codigos_barra.codigo_barra, codigos_barra.id_prod_esp as id_prod, codigos_barra.puesto_servicio_tecnico,
				producto_especifico.descripcion as descripcion, producto_especifico.precio_stock as precio, producto_especifico.id_tipo_prod
			from general.producto_especifico join general.codigos_barra using (id_prod_esp)
		)as u where descripcion ilike '%".$_POST["keyword"]."%'";
	elseif ($_POST["tipo_producto"]) $buscar_key="select count(codigo_barra) as total from (
			select codigos_barra.codigo_barra, codigos_barra.id_producto as id_prod, codigos_barra.puesto_servicio_tecnico,
				productos.desc_gral as descripcion, productos.precio_licitacion as precio, productos.id_tipo_prod
			from general.productos join general.codigos_barra using (id_producto)
				union
			select codigos_barra.codigo_barra, codigos_barra.id_prod_esp as id_prod, codigos_barra.puesto_servicio_tecnico,
				producto_especifico.descripcion as descripcion, producto_especifico.precio_stock as precio, producto_especifico.id_tipo_prod
			from general.producto_especifico join general.codigos_barra using (id_prod_esp)
		)as u where id_tipo_prod =".$_POST["tipo_producto"];
	else $buscar_key="buscar";
}else $buscar_key="select count(codigo_barra) as total from general.codigos_barra";

list($sql,$total,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,$buscar_key);

$result = sql($sql,"error en busqueda") or die("$sql<br>Error en form busqueda");

echo "&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>";


?>
</td>
</TR>
</TABLE>
</td>
<td>

<?
//controlamos permiso de ver el boton de hermanar

if(permisos_check("inicio","permiso_boton_hermanar_cb"))
{?>
<input type="button" name="hermanar" value="Hermanar Códigos de Barra" style="width:180" onclick="if(vent_cb.closed)vent_cb=window.open('hermanar_codigos_barra.php','','top=50, left=170, width=400px, height=500px, scrollbars=1, status=1,directories=0');else vent_cb.focus();">
<?
}
else
 echo "&nbsp;";
if(permisos_check("inicio","permiso_boton_agregar_codigo_barra"))
{
?>
<input type="button" name="nuevo" value="Nuevo Código de Barra" style="width:180" onclick="window.open('add_producto_codigo_barra.php','','top=50, left=170, width=800, height=200, scrollbars=0, status=1,directories=0');">
 <?}?>
</td>
</table>
</CENTER>
<BR>

<?=$parametros["msg"];?>
<TABLE class="bordes" align="center" width="98%" cellspacing="1">
<TR id="ma">
<TD colspan="2" align="left" >Cantidad de productos: <?=$total?></TD>
<TD colspan="2" align="right"> <?=$link_pagina?></TD>
</TR>
<TR id="mo">
<TD width="8%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up,"tipo"=>$tipo_p))?>'>Código</A></TD>
<!--<TD width="8%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up,"tipo"=>$tipo_p))?>'>OC</A></TD>-->
<TD width="37%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up,"tipo"=>$tipo_p))?>'>Descripción</A></TD>
<TD width="15%"><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up,"tipo"=>$tipo_p))?>'>Precio(U$S)</A></TD>
</TR>
<? while(!$result->EOF){
	$link = encode_link("productos_codigob_descripcion.php",array("code"=>$result->fields["codigo_barra"]));
    $puesto_rma=$result->fields["puesto_servicio_tecnico"];
    if ($puesto_rma) $color="#FF8080";
                else $color="#B7C7D0";

     ?>

	<tr <?=atrib_tr($color);?> onclick="document.location='<?=$link?>'">
	<TD bgcolor="#F0F8FF"><STRONG><?=$result->fields["codigo_barra"];?></STRONG></TD>
	<!--<TD align="center"><?if ($result->fields["nro_orden"]) echo $result->fields["nro_orden"]; else if ($result->fields["id_info_rma"]) echo "RMA"?></TD>-->
	<TD ><?=$result->fields["descripcion"];?></TD>
	<TD align="right"><?=formato_money($result->fields["precio"]);?></TD>
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