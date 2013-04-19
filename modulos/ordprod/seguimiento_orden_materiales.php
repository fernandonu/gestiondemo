<?php

/*
Autor: nazabal

MODIFICADA POR
$Author: gabriel $
$Revision: 1.12 $
$Date: 2006/01/26 17:45:15 $
*/

require_once("../../config.php");

echo $html_header;

//variables_form_busqueda("ord_compra");

//SECCION DE BUSQUEDA
$id_entrega_estimada = $parametros["id_entrega_estimada"] or $id_entrega_estimada=$_POST['id_entrega_estimada'];
$id_sub = $parametros["id_sub"] or $id_sub=$_POST['id_sub'];
if($_POST['buscar']=='Buscar')
{
	$id_entrega_estimada=$_POST['ordenes'];
	$selec_sub="select id_subir from licitaciones.subido_lic_oc where id_entrega_estimada=$id_entrega_estimada";
	$subir = sql($selec_sub) or fin_pagina();
	$id_sub = $subir->fields["id_subir"];

}

$id_licitacion = $parametros["id_licitacion"] or $id_licitacion=$_POST['id_licitacion'];
if ($id_licitacion == "") {
	Error("Falta el ID de la licitación");
	fin_pagina();
}


// Datos de la parte izquierda
$up_proveedores = $parametros["up_proveedores"];
$sort_proveedores = $parametros["sort_proveedores"];
if ($up_proveedores == "") {
    $up_proveedores = "1";
}
$up_proveedores_old = $up_proveedores;
if ($up_proveedores == "1") {
	$direccion_proveedores = "ASC";
	$up_proveedores = "0";
}
else {
	$direccion_proveedores = "DESC";
	$up_proveedores = "1";
}

$orden_proveedores = array(
		"default" => "5",
		"default_up"=>"$up_proveedores",
		"1" => "num_orden",
		"2" => "o.estado",
		"3" => "recibidos",
		"4" => "pedidos",
		"5" => "f.descripcion_prod",
		"6" => "p.razon_social",
		"7" => "m.simbolo",
		"8" => "f.precio_unitario"
);
$ordenar_proveedores = $orden_proveedores[$sort_proveedores] or $ordenar_proveedores = $orden_proveedores[$orden_proveedores["default"]];

$query_proveedores="
SELECT
		licitaciones.unir_texto(TEXTCAT(o.nro_orden,TEXT(' '))) AS num_orden,
		p.razon_social,
		f.descripcion_prod,
		m.simbolo,
		f.precio_unitario,
		o.estado,";
if($id_entrega_estimada!=0)
$query_proveedores.="s.id_entrega_estimada, ";
$query_proveedores.="
        SUM(
			CASE WHEN p.razon_social ILIKE 'Stock%'
				 THEN entregados
				 ELSE recibidos
			END) AS recibidos,
		SUM(f.cantidad) AS pedidos
	FROM
		compras.fila f
		LEFT JOIN compras.orden_de_compra o USING(nro_orden)
        LEFT JOIN (
             SELECT id_fila,cantidad AS recibidos
             FROM compras.recibido_entregado
             WHERE recibido_entregado.ent_rec = 1
             ) r1 USING(id_fila)
        LEFT JOIN (
             SELECT id_fila,cantidad AS entregados
             FROM compras.recibido_entregado
             WHERE recibido_entregado.ent_rec = 0
             ) r2 USING(id_fila)
		LEFT JOIN general.proveedor p USING(id_proveedor)
		LEFT JOIN licitaciones.moneda m USING(id_moneda)";
if($id_entrega_estimada!=0)
$query_proveedores.="JOIN licitaciones.subido_lic_oc s USING(id_subir) ";
$query_proveedores.="
        WHERE ".(($parametros["ocultar_anuladas"])?"o.estado <> 'n' and ":"")."
		o.id_licitacion=$id_licitacion";


if($id_entrega_estimada!=0)
$query_proveedores.=" and s.id_entrega_estimada=$id_entrega_estimada";


$query_proveedores.="	GROUP BY
		f.descripcion_prod,
		simbolo,
		f.precio_unitario,
		o.estado,
        p.razon_social";
if($id_entrega_estimada!=0)
$query_proveedores.=",s.id_entrega_estimada ";
$query_proveedores.="
	ORDER BY $ordenar_proveedores $direccion_proveedores
";

$result_proveedores = sql($query_proveedores) or fin_pagina();


// Datos de la parte derecha
$up_clientes = $parametros["up_clientes"];
$sort_clientes = $parametros["sort_clientes"];
if ($up_clientes == "") {
    $up_clientes = "1";
}
$up_clientes_old = $up_clientes;
if ($up_clientes == "1") {
	$direccion_clientes = "ASC";
	$up_clientes = "0";
}
else {
	$direccion_clientes = "DESC";
	$up_clientes = "1";
}

$orden_clientes = array(
	"default" => "1",
	"defaul_up" => "$up_clientes",
	"1" => "general.productos.desc_gral",
	"2" => "licitaciones.producto.precio_licitacion",
	"3" => "cantidad"
);
$ordenar_clientes = $orden_clientes[$sort_clientes] or $ordenar_clientes = $orden_clientes[$orden_clientes["default"]];

if($id_entrega_estimada==0)
{

	$query_clientes = "
	SELECT
		general.productos.desc_gral,
		licitaciones.producto.precio_licitacion,
		SUM(licitaciones.producto.cantidad * licitaciones.renglones_oc.cantidad) AS cantidad
	FROM
		licitaciones.producto
		LEFT JOIN licitaciones.renglones_oc ON (licitaciones.producto.id_renglon = licitaciones.renglones_oc.id_renglon)
		LEFT JOIN general.productos ON (licitaciones.producto.id_producto = general.productos.id_producto)
		LEFT JOIN licitaciones.renglon ON (licitaciones.renglones_oc.id_renglon = licitaciones.renglon.id_renglon)
	WHERE
		licitaciones.renglon.id_licitacion = $id_licitacion
	GROUP BY
		general.productos.desc_gral,
		licitaciones.producto.precio_licitacion
	ORDER BY $ordenar_clientes $direccion_clientes
	";
	$result_clientes = sql($query_clientes) or fin_pagina();
}

else
{

  $query_clientes ="SELECT
  general.productos.desc_gral,
  licitaciones.producto.precio_licitacion,
  SUM(licitaciones.producto.cantidad * licitaciones.renglones_oc.cantidad) AS cantidad
   FROM
  licitaciones.producto
  LEFT JOIN licitaciones.renglones_oc ON (licitaciones.producto.id_renglon = licitaciones.renglones_oc.id_renglon)
  LEFT JOIN general.productos ON (licitaciones.producto.id_producto = general.productos.id_producto)
   WHERE
  licitaciones.renglones_oc.id_subir = $id_sub
  GROUP BY
  general.productos.desc_gral,
  licitaciones.producto.precio_licitacion
  ORDER BY $ordenar_clientes $direccion_clientes";
  $result_clientes = sql($query_clientes) or fin_pagina();

}



$sql = "
SELECT
  licitaciones.entidad.nombre,
  licitaciones.subido_lic_oc.nro_orden
FROM
  licitaciones.licitacion
  LEFT JOIN licitaciones.entidad ON (licitaciones.licitacion.id_entidad = licitaciones.entidad.id_entidad)
  LEFT JOIN licitaciones.subido_lic_oc ON (licitaciones.licitacion.id_licitacion = licitaciones.subido_lic_oc.id_licitacion)
WHERE
  licitaciones.licitacion.id_licitacion = $id_licitacion
";
$result = sql($sql) or fin_pagina();
$entidad = $result->fields["nombre"];
$nro_orden = $result->fields["nro_orden"];

$sql = "
SELECT
  licitaciones.renglon.codigo_renglon
FROM
  licitaciones.renglon
WHERE
  licitaciones.renglon.id_licitacion = $id_licitacion
";
$result = sql($sql) or fin_pagina();
$renglones_array = array();
while(!$result->EOF){
	$renglones_array[] = $result->fields["codigo_renglon"];
	$result->MoveNext();
}
$renglones = join(", ",$renglones_array);

$sql = "
SELECT
  id_entrega_estimada,nro,nro_orden
FROM
  licitaciones.entrega_estimada
  left join licitaciones.subido_lic_oc using (id_entrega_estimada)
WHERE
  licitaciones.entrega_estimada.id_licitacion =$id_licitacion
";
$result_li = sql($sql) or fin_pagina();


?>
<script language='Javascript'>
	function alternar_color(obj,color) {
		color=color.toLowerCase();
		if (obj.style.backgroundColor == color)

			obj.style.backgroundColor = ""
		else
			obj.style.backgroundColor = color
	}
</script>
<form method="POST" action="seguimiento_orden_materiales.php">
<input type="hidden" name="id_licitacion" value="<?=$id_licitacion?>">
<table align="center">
<tr>
<td> Buscar por Seguimiento de Orden
	<select name="ordenes">
	<option value="0">Todas</option>
	<?
	while(!$result_li->EOF)
	{
	$nro=$result_li->fields["nro_orden"];
	$id_entrega_estima=$result_li->fields["id_entrega_estimada"];
	?>
	<option value="<?=$id_entrega_estima?>" <?if($id_entrega_estimada==$id_entrega_estima){?>selected<?}?>><?=$nro?></option>
	<?
	$result_li->MoveNext();
	}
	?>
	</select>
	<input type="submit" name="buscar" value="Buscar">
	<input type="button" value="ver con listado de PM" name="listado_pm" onclick="window.open('<?=encode_link("../ordprod/seguimiento_orden_materiales_pm.php",array("id_licitacion"=>$id_licitacion,"mostrar_pedidos"=>1))?>','','left=40,top=80,width=700,height=300,resizable=1,status=1,scrollbars=1');">
</td>
</tr>
</table>

<table border=1><tr><td valign="top">
<div id="imprimir">
<table align=center cellpadding=5 cellspacing=0 border=1 width='100%'>
  <tr bordercolor='#000000'>
	<td id=mo colspan=8 align=center>
		Proveedores
<!--		Materiales incluidos en ordenes de compra de Coradir a proveedores -->
	</td>
  </tr>
  <tr bordercolor='#000000' id=ma>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_proveedores'=>1,'up_proveedores'=>$up_proveedores,'up_clientes'=>$up_clientes_old,'sort_clientes'=>$sort_clientes,'id_licitacion'=>$id_licitacion))?>'>Orden</a></td>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_proveedores'=>2,'up_proveedores'=>$up_proveedores,'up_clientes'=>$up_clientes_old,'sort_clientes'=>$sort_clientes,'id_licitacion'=>$id_licitacion))?>'>Pagado</a></td>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_proveedores'=>3,'up_proveedores'=>$up_proveedores,'up_clientes'=>$up_clientes_old,'sort_clientes'=>$sort_clientes,'id_licitacion'=>$id_licitacion))?>'>Recibidos</a></td>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_proveedores'=>4,'up_proveedores'=>$up_proveedores,'up_clientes'=>$up_clientes_old,'sort_clientes'=>$sort_clientes,'id_licitacion'=>$id_licitacion))?>'>Pedidos</a></td>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_proveedores'=>5,'up_proveedores'=>$up_proveedores,'up_clientes'=>$up_clientes_old,'sort_clientes'=>$sort_clientes,'id_licitacion'=>$id_licitacion))?>'>Producto</a></td>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_proveedores'=>6,'up_proveedores'=>$up_proveedores,'up_clientes'=>$up_clientes_old,'sort_clientes'=>$sort_clientes,'id_licitacion'=>$id_licitacion))?>'>Proveedor</a></td>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_proveedores'=>7,'up_proveedores'=>$up_proveedores,'up_clientes'=>$up_clientes_old,'sort_clientes'=>$sort_clientes,'id_licitacion'=>$id_licitacion))?>'>Moneda</a></td>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_proveedores'=>8,'up_proveedores'=>$up_proveedores,'up_clientes'=>$up_clientes_old,'sort_clientes'=>$sort_clientes,'id_licitacion'=>$id_licitacion))?>'>Precio</a></td>
  </tr>
<?
	$i=0;
	while ($fila = $result_proveedores->fetchrow()) {
		$bg_anulada="";
   		switch($fila['estado']){
   			case "g":
   				$pagado="Sí";
   				break;
   			case "d":
   				$pagado="Sí";
   				break;
			case "n":
				$bg_anulada="bgcolor='#FF8080' class='anulada' title='Anulada'";
   			default:
   				$pagado="No";
   		}
		if ($fila['estado'] == "n") echo "<div style='display: none;'>";
        echo "<tr bordercolor='#000000' onClick=\"alternar_color(this,'#00C823')\">\n";
        echo "<td align=center $bg_anulada>".ereg_replace(" ","<br>",$fila['num_orden'])."</td>";
        echo "<td align=center>".$pagado."</td>";
        echo "<td align=center>".intval($fila['recibidos'])."</td>\n";
        echo "<td align=center>".intval($fila['pedidos'])."</td>\n";
		echo "<td align=left>".$fila['descripcion_prod']."</td>\n";
        echo "<td align=left>".$fila['razon_social']."</td>\n";
        echo "<td align=right>".$fila['simbolo']."</td>\n";
        echo "<td align=right>".formato_money($fila['precio_unitario'])."</td>\n";
        echo "</tr>\n";
		if ($fila['estado'] == "n") echo "</div>";
   }
?>
</table>
</div>
<?
$header_imprimir = "<html><head><link rel=stylesheet type=text/css href=$html_root/lib/estilos.css><style type=text/css>@media print { .text_6 { display: none; } }</style></head><body>";

if ($parametros["ocultar_anuladas"]) {
	$link_ocultar = encode_link("seguimiento_orden_materiales.php",array("id_licitacion"=>$id_licitacion,"ocultar_anuladas"=>0,"id_sub"=>$id_sub,"id_entrega_estimada"=>$id_entrega_estimada));
	$str_ocultar = "Mostrar";
}
else {
	$link_ocultar = encode_link("seguimiento_orden_materiales.php",array("id_licitacion"=>$id_licitacion,"ocultar_anuladas"=>1,"id_sub"=>$id_sub,"id_entrega_estimada"=>$id_entrega_estimada));
	$str_ocultar = "Ocultar";
}
?>
<center>
	<input type="button" value="Imprimir" onclick="ImprimirDivs(new Array('imprimir'),'<? echo $header_imprimir; ?>');">
	<input type="button" value="<? echo $str_ocultar; ?> ordenes anuladas" onclick="document.location='<? echo $link_ocultar; ?>'">
</center>
</td><td valign="top">
<table align=center cellpadding=5 cellspacing=0 border=1>
  <tr bordercolor='#000000'>
	<td id=mo colspan=3 title='ID Licitaci&oacute;n: <?=$id_licitacion."\n"?>Entidad: <?=$entidad."\n"?>OC N°: <?=$nro_orden."\n"?>Renglones: <?=$renglones?>'>
		Clientes
	</td>
  </tr>
  <tr bordercolor='#000000' id=ma>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_clientes'=>1,'up_clientes'=>$up_clientes,'up_proveedores'=>$up_proveedores_old,'sort_proveedores'=>$sort_proveedores,'id_licitacion'=>$id_licitacion))?>'>Producto</a></td>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_clientes'=>2,'up_clientes'=>$up_clientes,'up_proveedores'=>$up_proveedores_old,'sort_proveedores'=>$sort_proveedores,'id_licitacion'=>$id_licitacion))?>'>Precio</a></td>
	<td align=center><a id=ma href='<?=encode_link("seguimiento_orden_materiales.php",Array('sort_clientes'=>3,'up_clientes'=>$up_clientes,'up_proveedores'=>$up_proveedores_old,'sort_proveedores'=>$sort_proveedores,'id_licitacion'=>$id_licitacion))?>'>Cantidad</a></td>
  </tr>
<?
   while ($fila = $result_clientes->fetchrow()) {
        echo "<tr bordercolor='#000000' onClick=\"alternar_color(this,'#00C823')\">\n";
        echo "<td align=left>".$fila["desc_gral"]."</td>";
        echo "<td align=right> U\$S ".formato_money($fila['precio_licitacion'])."</td>\n";
        echo "<td align=center>".intval($fila['cantidad'])."</td>\n";
        echo "</tr>\n";
   }
?>
</table>
</td></tr></table>
<br>
<center>
 <input type=button name=cerrar value=Cerrar onclick="window.close()">
</center>
<?
fin_pagina();
?>