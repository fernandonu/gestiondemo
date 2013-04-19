<?
/*
Autor: GACZ
Creado: jueves 05/05/05

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.4 $
$Date: 2005/07/08 21:46:36 $
*/

require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");

//funcion que agrega comillas a los campos de texto, 
//pone null a los campos vacios,
//y pone el año y mes que corresponda
function prepare_data_per(&$data)
{
	global $mes,$anio,$mes_cambiar,$anio_cambiar,$cerrada_fdestino;
	for($i=count($data['id_factura']); $i ; $i--)	
	{
		//si se debe cambiar el año y esta checkeado y no esta cerrada la fecha de destino
		if ($_POST['bcambiar_mes'] && isset($data['chk_'][$i-1]) && !$cerrada_fdestino)
		{
			$data['mes_percepcion'][$i-1]=$mes_cambiar;
			$data['anio_percepcion'][$i-1]=$anio_cambiar;
		}
		else 
		{
			$data['mes_percepcion'][$i-1]=$mes;
			$data['anio_percepcion'][$i-1]=$anio;
		}
		//if ($data['id_distrito'][$i-1]=="" || $data['id_distrito'][$i-1]==-1)	$data['id_distrito'][$i-1]='null';
	}
	unset($data['chk_']);
}

if ($_POST['bcerrar'])
{
	$q="insert into cierre_percepciones (fecha_cierre,mes,anio,usuario) values ('".Fecha_db($fecha_hoy)."',$mes,$anio,'{$_ses_user['login']}')";
	sql($q) or fin_pagina();
}

$q ="Select fecha_cierre from cierre_percepciones where mes=$mes and anio=$anio ";
$r=sql($q);
$cerrada['percepciones']=($r->fields['fecha_cierre']!="");

//si se va a cambiar el mes controlo que no este cerrado el mes de destino
if ($_POST['bcambiar_mes'])
{
	$q ="Select fecha_cierre from cierre_percepciones where mes=$mes_cambiar and anio=$anio_cambiar";
	$r=sql($q);
	$cerrada_fdestino=($r->fields['fecha_cierre']!="");
}

if ($_POST['bguardar'] || $_POST['bcambiar_mes'])
{
	//recupero los items del POST	(por referencia)
	$data=PostvartoArray("chk_,hid_factura_",true);
	
	//pongo los nombres de las columnas de la tabla
	$newnames=array("hid_factura_"=>"id_factura");
									
	ArrayChangeKeyName($data,$newnames);
	
	//agrego comillas a los textos y null a los vacios
	prepare_data_per($data);

	$newdata=ArrayRowsAsCols($data,true);
 	
	//guardo SOLO retenciones que tienen un item en caja
	if (replace("fact_prov",$newdata,array("id_factura"))===0)
		$msg=($_POST['bcambiar_mes'] && $cerrada_fdestino)?"Las percepciones no se han cambiado de mes porque la fecha seleccionada estaba cerrada"
		:"Todo se actualizo correctamente";
	else 
		$msg="<font color=red >Ha ocurrido un error</font>";
}
//else	$msg="NO SE HIZO NADA";
if ($cmd=="percepciones" || $cmd="todas")
{

//*Recupero las Percepciones
$q ="select ";
$q.="f.id_factura,f.tipo_fact,f.nro_factura,f.percepcion_iva as iva_monto,perib.monto_ib as ib_monto,f.monto,f.monto_dolar,";
$q.="f.fecha_emision as fecha,f.anio_percepcion,f.mes_percepcion,";
$q.="d.id_distrito,d.nombre as distrito,";
$q.="p.id_proveedor,p.razon_social,p.cuit,";
$q.="m.id_moneda,m.simbolo,";
//$q.="per.id_percepcion,case when per.nro_certificado is not null then per.nro_certificado else f.nro_factura end as nro_certificado ";
$q.="f.nro_factura as nro_certificado ";
$q.="from ";
$q.="fact_prov f ";
$q.="join moneda m on m.id_moneda=f.moneda ";
$q.="join proveedor p using(id_proveedor) ";
$q.="left join percepciones_ib perib using(id_factura) "; //traigo todos los distritos en los que haya percepciones
$q.="left join distrito d using(id_distrito) ";
$q.="where ";
$qpart="f.anio_percepcion=$anio AND f.mes_percepcion=$mes";
	
	//si es el mes actual, incluyo las retenciones sin mes
	if ($mes==date('m') && $anio==date('Y'))
		$q.="(f.anio_percepcion is null AND f.mes_percepcion is null OR $qpart)";
	else
		$q.=$qpart;
 $q.=" AND (perib.id_distrito is not null OR percepcion_iva is not null AND percepcion_iva<>0)";		
 $q.=" order by fecha,id_factura asc";
}
?>