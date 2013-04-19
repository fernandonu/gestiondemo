<?
/*
Autor: GACZ
Creado: martes 19/04/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.3 $
$Date: 2005/04/22 19:34:16 $
*/

require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");

//Esta pagina abre todas las ordenes de compras hechas para un producto de prespuesto

$id_prod=$parametros['id_prod_pres'];

if ($id_prod=="")
	die("Falta el parametro requerido");
	
$q ="select id_producto_presupuesto,nro_orden ";
$q.="from oc_pp ";
$q.="join orden_de_compra using(nro_orden) ";
$q.="where estado!='n'	and id_producto_presupuesto=$id_prod";
$res=sql($q) or fin_pagina();

$links=array();
$i=$res->recordcount();
$win=new JsWindow();
$win->maximized=true;
echo "<html>\n";
echo "<script>\n";
//itero hasta el penultimo
for ($j=0; $j < $i-1; $j++)
{
	$win->url=encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$res->fields['nro_orden']));
	echo $win->open()."\n";
	$res->movenext();
}

//abro la ultima orden en la misma ventana
echo "window.resizeTo(screen.width,screen.height);\n";
echo "window.location.href='".encode_link("../ord_compra/ord_compra.php",array("nro_orden"=>$res->fields['nro_orden']))."';\n";
echo "</script>\n";
echo "</html>\n";
?>