<?
/*
Autor: GACZ
Creado: viernes 11/02/05

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.2 $
$Date: 2005/02/15 16:09:44 $
*/

require_once("../../config.php");

extract($_POST);
variables_form_busqueda("inventario");
if ($cmd=="historial")
	$id_estado=2;
else
{
	$id_estado=1;//por defecto actuales
	$cmd="actuales";
}
	
$q ="select *,precio_unitario*cantidad as total,ei.* ";
$q.="from inventario join estado_inventario ei using(id_estado) ";

$orden = array(
							"default" => "1",
							"default_up" => 0,
							"1" => "item_nro",
							"2" => "descripcion",
							"3" => "ubicacion",
							"4" => "cantidad",
							"5" => "precio_unitario",
							"6" => "total");

$filtro = array(
								"item_nro"        => "Nº",
								"descripcion"     => "Descripción",
								"ubicacion"       => "Ubicación");

//para que no imprima html
ob_start();
list($q,$total,$lnk,$up2,$suma) = form_busqueda($q,$orden,$filtro,$lnk,"id_estado=$id_estado","buscar",array("moneda"=>"id_moneda","campo"=>"total","mask"=>array()));
$ob_data=ob_get_contents();//guarda en ob_data lo que deberia ir al browser
ob_clean();

$r=sql($q) or fin_pagina();
$q_count=eregi_replace("(.*)(LIMIT.*)","\\1",$q);
$data=array();

//nbre_clave_columna en $data[0..n][] => Nbre columna(texto visible)
$data['titulos']=$data['encabezados']=array(
																						"item_nro" 				=> "Nº",
																						"descripcion"     => "Descripción",
																						"ubicacion"       => "Ubicación",
																						"cantidad"        => "Cantidad",
																						"precio_unitario" => "Precio U",
																						"total"						=> "Total");
//datos de la busqueda realizada
$data['busqueda']=array("keyword"=>$keyword,
												"filter"=>array("value"=>$filter,"text"=>$filtro[$filter]),
												"query"=>$q,
												"page_recordcount"=>$r->recordcount(),
												"total_recordcount"=>$total,
												"link_pagina"=>$lnk,
												"sort"=>array("sort_value"=>$sort,"col_db"=>$orden[$sort],"col_text"=>$data["encabezados"][$orden[$sort]]));

$data['total']=$suma;
while ($data[]=$r->fetchrow()){ };
unset($r);
?>