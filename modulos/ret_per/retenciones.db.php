<?
/*
Autor: GACZ
Creado: jueves 05/05/05

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.4 $
$Date: 2005/07/08 21:48:28 $
*/

require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");

//funcion que agrega comillas a los campos de texto, 
//pone null a los campos vacios,
//y pone el año y mes que corresponda
function prepare_data_ret(&$data)
{
	global $mes,$anio,$mes_cambiar,$anio_cambiar,$cerrada_fdestino;
	
	for($i=count($data['id_factura']); $i ; $i--)	
	{
		//si se debe cambiar el año y esta checkeado y no esta cerrada la fecha destino
		if ($_POST['bcambiar_mes'] && isset($data['chk_'][$i-1]) && !$cerrada_fdestino)
		{
			$data['mes'][$i-1]=$mes_cambiar;
			$data['anio'][$i-1]=$anio_cambiar;
		}
		else 
		//dejo el año y el mes seleccionado en el filtro
		{
			$data['mes'][$i-1]=$mes;
			$data['anio'][$i-1]=$anio;
		}
		$data['nro_certificado'][$i-1]=$data['nro_certificado'][$i-1]!=""?"'".$data['nro_certificado'][$i-1]."'":"null";
		$data['fecha'][$i-1]=$data['fecha'][$i-1]?"'".Fecha_db($data['fecha'][$i-1])."'":'null';
		if ($data['id_factura'][$i-1]=="")	$data['id_factura'][$i-1]='null';
		//es nuevo???
		if ($data['hnuevo_'][$i-1])
		{
			if ($data['iva_monto'][$i-1]=="")
				$data['iva_monto'][$i-1]='null';
			else
				$data['iva_monto'][$i-1]=ereg_replace(",",".",$data['iva_monto'][$i-1]);

			if ($data['ib_monto'][$i-1]=="")
				$data['ib_monto'][$i-1]='null';
			else
				$data['ib_monto'][$i-1]=ereg_replace(",",".",$data['ib_monto'][$i-1]);
				
			if ($data['ganancia_monto'][$i-1]=="")
				$data['ganancia_monto'][$i-1]='null';
			else
				$data['ganancia_monto'][$i-1]=ereg_replace(",",".",$data['ganancia_monto'][$i-1]);
			
			//esta en la BD ??? (Si no esta)
			if ($data['id_retencion'][$i-1]=="")
				//inserto la fecha de nuevo(Timestamp)
				$data['fecha_nuevo'][$i-1]="'".date("Y-m-j H:i")."'";
		}
		else
		{
			unset($data['iva_monto'][$i-1]);
			unset($data['ib_monto'][$i-1]);
			unset($data['ganancia_monto'][$i-1]);
		}
		
		if ($data['id_ingreso_egreso'][$i-1]=="")	$data['id_ingreso_egreso'][$i-1]='null';
		if ($data['id_entidad'][$i-1]=="")	$data['id_entidad'][$i-1]='null';
		if ($data['id_distrito'][$i-1]=="")	$data['id_distrito'][$i-1]='null';
	}
	unset($data['chk_']);
	unset($data['hnuevo_']);
}

if ($_POST['bcerrar'])
{
	$q="insert into cierre_retenciones (fecha_cierre,mes,anio,usuario) values ('".Fecha_db($fecha_hoy)."',$mes,$anio,'{$_ses_user['login']}')";
	sql($q) or fin_pagina();
}
$q ="Select fecha_cierre from cierre_retenciones where mes=$mes and anio=$anio";
$r=sql($q);
$cerrada['retenciones']=($r->fields['fecha_cierre']!="");

//si se va a cambiar el mes controlo que no este cerrado el mes de destino
if ($_POST['bcambiar_mes'])
{
	$q ="Select fecha_cierre from cierre_retenciones where mes=$mes_cambiar and anio=$anio_cambiar";
	$r=sql($q);
	$cerrada_fdestino=($r->fields['fecha_cierre']!="");
}

if ($_POST['bguardar'] || $_POST['bcambiar_mes'])
{
	//recupero los items del POST	(por referencia)
	$data=PostvartoArray("chk_,fecha_,hnuevo_,hid_retencion_,hid_factura_,hid_entidad_,hid_distrito_,certificado_,hid_ingreso_egreso_,iva_,ib_,ganancia_",true);
	
	//pongo los nombres de las columnas de la tabla
	$newnames=array("hid_retencion_"=>"id_retencion",
									"fecha_"=>"fecha",
									"hid_factura_"=>"id_factura",
									"hid_entidad_"=>"id_entidad",
									"hid_distrito_"=>"id_distrito",
									"certificado_"=>"nro_certificado",
									"hid_ingreso_egreso_"=>"id_ingreso_egreso",
									"fecha_"=>"fecha",
									"iva_"=>"iva_monto",
									"ib_"=>"ib_monto",
									"ganancia_"=>"ganancia_monto");
									
									
//	echo "ANTES DE ArrayChangeKeyName: <br>";print_r($data);// die;
	ArrayChangeKeyName($data,$newnames);
//	echo "DESPUES DE ArrayChangeKeyName: <br>";print_r($data);// die;
	
	//agrego comillas a los textos y null a los vacios
	prepare_data_ret($data);

//	echo "<br><br>ANTES DE ArrayRowsAsCols: <br>";print_r($data); die;
	$newdata=ArrayRowsAsCols($data,true);
// 	echo "<br><br>DESPUES DE ArrayRowsAsCols: <br>";print_r($newdata); die;	

	//guardo SOLO retenciones que tienen un item en caja
	if (replace("retenciones_fact",$newdata,array("id_retencion"))===0)
		$msg=($_POST['bcambiar_mes'] && $cerrada_fdestino)?"Las retenciones no se han cambiado de mes pq la fecha seleccionada estaba cerrada"
		:"Todo se actualizo correctamente";
	else 
		$msg="<font color=red >Ha ocurrido un error</font>";
}
//else	$msg="NO SE HIZO NADA";
		
//Consulta por retenciones
if ($cmd=="retenciones" || $cmd=='todas')
{
	$q ="select ";
	$q.="case when r.fecha is not null then r.fecha else ie.fecha_creacion end as fecha,";
	$q.="case when tc.plan ilike 'retenciones ganancia%' then ie.monto else r.ganancia_monto end as ganancia_monto,";
	$q.="case when tc.plan like 'RIB%' then ie.monto else r.ib_monto end as ib_monto,";
	$q.="case when tc.plan ilike 'retenciones i.v.a%' then ie.monto else r.iva_monto end as iva_monto,";
	$q.="r.nro_certificado,r.id_retencion,r.fecha_nuevo,";
	//el nombre de la entidad, sino la descripcion del item
	$q.="e.id_entidad,case when e.nombre is not null then e.nombre else ie.item end as entidad,e.cuit,";
	$q.="d.id_distrito,d.nombre as distrito,";
	$q.="f.id_factura,f.nro_factura,upper(f.tipo_factura) as tipo_factura,";
	$q.="ie.id_ingreso_egreso ";//para recuperar las facturas asociadas
	$q.="from ingreso_egreso ie ";
	$q.="join caja on ie.id_caja=caja.id_caja AND caja.id_moneda=1 "; //recupero egresos solo en PESOS
	$q.="join tipo_cuenta tc using (numero_cuenta) ";
	$q.="join proveedor p using (id_proveedor) ";
	$q.="full join retenciones_fact r using (id_ingreso_egreso) ";
	$q.="left join entidad e on r.id_entidad=e.id_entidad ";
	$q.="left join distrito d on r.id_distrito=d.id_distrito ";
	$q.="left join detalle_egresos de using(id_ingreso_egreso) ";
	$q.="left join cobranzas cob using(id_cobranza) ";
	$q.="left join facturas f on r.id_factura=f.id_factura or cob.id_factura=f.id_factura ";
	$q.="where (r.fecha_nuevo is not null OR tc.concepto ilike 'impuestos' and ";
	$q.="(tc.plan ilike 'retenciones ganancia%' OR tc.plan like 'RIB%' OR tc.plan ilike 'retenciones i.v.a%'))";
	$q.=" AND ";
	
	$qpart="r.anio=$anio AND r.mes=$mes";
	
	//si es el mes actual, incluyo las retenciones sin mes
	if ($mes==date('m') && $anio==date('Y'))
		$q.="(r.anio is null AND r.mes is null OR $qpart)";
	else
		$q.=$qpart;
		
	$q.="order by fecha asc "; //ordeno todo
}		
?>