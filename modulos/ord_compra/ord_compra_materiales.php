<?
/*
Autor: nazabal

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.9 $
$Date: 2006/01/04 15:12:59 $
*/

require_once("../../config.php");

echo $html_header;

variables_form_busqueda("ord_compra");

//SECCION DE BUSQUEDA

$query="
SELECT
	licitaciones.unir_texto(TEXTCAT(o.nro_orden,TEXT(' '))) AS num_orden,
	p.razon_social,
	f.descripcion_prod,
	m.simbolo,
	f.precio_unitario,
	o.estado,
	SUM(
		CASE WHEN p.razon_social ILIKE 'Stock%'
			THEN entregados
			ELSE recibidos
		END) AS recibidos,
	SUM(f.cantidad) as pedidos
FROM compras.fila f
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
	LEFT JOIN licitaciones.moneda m USING(id_moneda)
";

if ($cmd!='todas')
{	if ($cmd=="p")
	{$where="(estado='p' OR estado='r')";
	 $contar="select count (*) from orden_de_compra where estado='p' or estado='r'";
	}
	elseif ($cmd=='d')
	{ $where="(estado='d' OR estado='g' OR estado='t')";
	  $contar="select count (*) from orden_de_compra where estado='g' or estado='t' or estado='d'";
	}
	elseif($cmd=='e')
	{$where="(estado='d' OR estado='e')";
	 $contar="select count (*) from orden_de_compra where estado='d' or estado='e'";
	}
	else
	{$where="estado='$cmd'";
	 $contar="select count (*) from orden_de_compra where estado='$cmd'";
	}
}
else { $contar="select count (*) from orden_de_compra"; }

$where .= " GROUP BY f.descripcion_prod,simbolo,f.precio_unitario,
			o.estado,p.razon_social";

if (!(strpos($cmd,"Fecha")==false))
    	$keyword=Fecha_db($keyword);

if ($cmd!='p')
           $order=0;
           else
           $order=1;

$orden= array
(
		"default" => "4",
        "default_up"=>"$order",
		"1" => "num_orden",
		"2" => "o.estado",
		"3" => "pedidos",
		"4" => "recibidos",
		"5" => "f.descripcion_prod",
		"6" => "p.razon_social",
		"7" => "m.simbolo",
        "8" => "f.precio_unitario"
);
$filtro_array= array
(
		"o.nro_orden"=>"Nº de Orden",
		"o.nro_factura"=>"Nº de Factura",
		"o.id_licitacion"=>"ID Licitacion",
		"o.lugar_entrega"=>"Lugar de entrega",
		"o.cliente"=>"Cliente",
		"o.notas"=>"Comentarios",
		"p.razon_social"=> "Proveedor",
		"f.descripcion_prod"=>"Productos"
);
/*Para que pueda buscar en los productos
if ($cmd=='fila.descripcion_prod' || $cmd=='all')
{
	 $query.=" left join fila using(nro_orden)";
}
*/

if($_POST['keyword'] || $keyword)// en la variable de sesion para keyword hay datos)
     $contar="buscar";
$itemspp=50;
ob_start();
list($query,$total,$link_pagina,$up) = form_busqueda($query,$orden,$filtro_array,$link_tmp,$where,$contar);
ob_clean();
//print_r($_ses_ord_compra);

$result = sql($query) or fin_pagina();
?>
<table align=center cellpadding=5 cellspacing=0 border=1>
  <tr bordercolor='#000000'>
	<td id=mo colspan=8 align=center>
	  <table id=mo border=0 width=100% >
	    <tr>
		  <td align=left width=70% >Materiales: <?=$total?></td>
		  <td align=right width=30% ><?=$link_pagina?>&nbsp;</td>
		</tr>
	  </table>
	</td>
  </tr>
  <tr bordercolor='#000000' id=ma>
	<td align=center><a id=ma href='<?=encode_link("ord_compra_materiales.php",Array('sort'=>1,'up'=>$up))?>'>Orden</a></td>
	<td align=center><a id=ma href='<?=encode_link("ord_compra_materiales.php",Array('sort'=>2,'up'=>$up))?>'>Pagado</a></td>
	<td align=center><a id=ma href='<?=encode_link("ord_compra_materiales.php",Array('sort'=>3,'up'=>$up))?>'>Recibidos</a></td>
	<td align=center><a id=ma href='<?=encode_link("ord_compra_materiales.php",Array('sort'=>4,'up'=>$up))?>'>Pedidos</a></td>
	<td align=center><a id=ma href='<?=encode_link("ord_compra_materiales.php",Array('sort'=>5,'up'=>$up))?>'>Producto</a></td>
	<td align=center><a id=ma href='<?=encode_link("ord_compra_materiales.php",Array('sort'=>6,'up'=>$up))?>'>Proveedor</a></td>
	<td align=center><a id=ma href='<?=encode_link("ord_compra_materiales.php",Array('sort'=>7,'up'=>$up))?>'>Moneda</a></td>
	<td align=center><a id=ma href='<?=encode_link("ord_compra_materiales.php",Array('sort'=>8,'up'=>$up))?>'>Precio</a></td>
  </tr>
<?
   while ($fila = $result->fetchrow()) {
		$bg_anulada="";
   		switch($fila['estado']){
   			case "g":
   				$pagado="Sí";
   				break;
   			case "d":
   				$pagado="Sí";
   				break;
			case "n":
				$bg_anulada="bgcolor='#FF8080' title='Anulada'";
   			default:
   				$pagado="No";
   		}
        echo "<tr bordercolor='#000000'>\n";
        echo "<td align=center $bg_anulada>".ereg_replace(" ","<br>",$fila['num_orden'])."</td>";
        echo "<td align=center>".$pagado."</td>";
        echo "<td align=center>".intval($fila['recibidos'])."</td>\n";
        echo "<td align=center>".intval($fila['pedidos'])."</td>\n";
		echo "<td align=left>".$fila['descripcion_prod']."</td>\n";
        echo "<td align=left>".$fila['razon_social']."</td>\n";
        echo "<td align=right>".$fila['simbolo']."</td>\n";
        echo "<td align=right>".formato_money($fila['precio_unitario'])."</td>\n";
        echo "</tr>\n";
   }
?>
</table>
<?
fin_pagina();
?>