<?
/*
Autor: GACZ
Creado: Lunes 08/11/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2004/11/09 19:25:13 $
*/

//require_once("../../config.php");

//estas variables deben estar seteadas cuando se invoca la pagina
$id_subir;
$parametros['ID']=$id_lic=$ID;
$id_entrega_estimada=$id_ent;
global $_ses_user;


	$q ="select distinct r.id_renglon,r.tipo,roc.cantidad,r.cantidad as cantidadr,r.titulo,r.codigo_renglon ";
	$q.="from ";
	$q.="renglon r ";
	$q.="join renglones_oc roc using(id_renglon) ";//renglones en estado orden de compra
	$q.="where id_licitacion=$id_lic and id_subir=$id_subir ";
	$q.="order by tipo";
	$reng=sql($q) or fin_pagina(); 
	
while (!$reng->EOF)
{
	$tipo=$reng->fields['tipo'];
	$_POST['id_lic_prop']=-1;//para que cree un presupuesto nuevo
	$_POST['titulo']="Renglones tipo $tipo";
	//$_POST['comentarios'];
	$nro_renglon=0;
	do 
	{
		$_POST['hidrenglon_'.$nro_renglon]=$reng->fields['id_renglon'];
		$_POST['cant_renglon_'.$nro_renglon]=$reng->fields['cantidad'];
		$q ="select producto.id_producto,producto.precio_licitacion,productos.desc_gral,producto.cantidad ";
		$q.="from renglon ";
		$q.="join producto using(id_renglon) ";
		$q.="join productos using(id_producto) ";
		$q.="where id_renglon=".$reng->fields['id_renglon'];
		$productos=sql($q) or fin_pagina();
		$nro_prod=0;
		while (!$productos->EOF)
		{
			$_POST["hidproducto_".$nro_renglon."_".$nro_prod]=$productos->fields['id_producto'];
			$_POST["hdescorig_".$nro_renglon."_".$nro_prod]=$productos->fields['desc_gral'];
			$_POST["cantidad_prod_".$nro_renglon."_".$nro_prod]=$productos->fields['cantidad'];
			$nro_prod++;
			$productos->MoveNext();
		}
		$nro_renglon++;
		$reng->MoveNext();
	}
	while (!$reng->EOF && $reng->fields['tipo']==$tipo);
	
	//inserto un nuevo presupuesto
	include("detalle_presupuesto_proc.php");
	$_POST=array();//para que no queden residuos de los renglones anteriores
}


?>