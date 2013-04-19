<?
/*
Autor: GACZ
Creado: lunes 20/12/04

MODIFICADA POR
$Author: marco_canderle $
$Revision: 1.3 $
$Date: 2006/01/04 09:02:15 $
*/

require_once("../../config.php");
//estas constantes definen los id_tipo_prob en la DB de los distintos problemas
define("PROB_FECHA_E",1);
define("PROB_COMPRAR",2);
define("PROB_RECIBIR",3);

/*
	Este archivo se usa de dos maneras
	1º cuando silent=1 revisa si hay problemas y sale (llamando a un archivo) cuando encuentra el primero
	2º cuando silent=0 (default) genera un archivo XML con los problemas encontrados
			2.1	cuando force_check=0 (default) muestra solo los problemas activos (no postergados ni descartados)
			2.2	cuando force_check=1 muestra todos los problemas (postergados y descartados y nuevos que se han encontrado)
*/
$fecha_db=date("Y-m-j");
$q ="Select count(*) from usuarios_reg where id_usuario=".$_ses_user['id'];
$r1=sql($q) or fin_pagina();

//Si silent esta en 1, se corta la ejecucion en el primer problema encontrado
if (!isset($silent))
	$silent=$parametros['silent'] or $silent=$_POST['silent'] or  $silent=0; //variable utilizada para que no muestre XML

if (!$force_check)
$force_check=$_GET['force_check'] or $force_check=0;//esta variable indica si debe avisar los problemas (aunque esten postergados o descartados o nada)

//si el usuario esta registrado al programa de ayuda
if ($r1->fields['count']!=0)
{
	$q ="select distinct lp.*,ee.*,rp.*,sl.nro_orden as numero,sl.id_subir,sl.vence_oc from ";
	$q.="licitacion_presupuesto_new lp ";
	$q.="join entrega_estimada ee using(id_entrega_estimada)  ";
	$q.="join subido_lic_oc sl using(id_entrega_estimada)  ";
	$q.="join renglon_presupuesto_new rp using(id_licitacion_prop)  ";
	$q.="join renglon r using(id_renglon)  ";
	$q.="where ";
	$q.="(ee.finalizada is null OR ee.finalizada!=1) AND "; //seguimientos no finalizados
	$q.="r.tipo ilike '%computadora%' AND "; //renglones tipo computadora
	$q.="(";
	//cambiar el current_date por algo que me de la cantidad de dias HABILES
	//cantidad del renglon presupuestado (puede diferir del renglon original)
	$q.="vence_oc <= '".next_habil($fecha_db,8)."' AND rp.cantidad > 300 OR ";//8 dias amarillo	7 dias naranja	6 dias y menor en rojo
	$q.="vence_oc <= '".next_habil($fecha_db,7)."' AND rp.cantidad > 100 AND rp.cantidad <= 300 OR ";//7 dias amarillo	6 dias naranja	5 dias y menor en rojo
	$q.="vence_oc <= '".next_habil($fecha_db,6)."' AND rp.cantidad > 25 AND rp.cantidad <= 100 OR ";//6 dias amarillo	5 dias naranja	4 dias y menor en rojo
	$q.="vence_oc <= '".next_habil($fecha_db,5)."' AND rp.cantidad > 5 AND rp.cantidad <= 25 OR ";//5 dias amarillo	4 dias naranja	3 dias y menor en rojo
	$q.="vence_oc <= '".next_habil($fecha_db,4)."' AND rp.cantidad <= 5 ";//4 dias amarillo	3 dias naranja	2 dias y menor en rojo
	$q.=") ";
	$q.="order by ee.id_licitacion,ee.id_entrega_estimada,ee.nro";

	$presup=sql($q) or fin_pagina();
	$aproblemas=array();//variable que se usa para guardar los problemas encontrados
	//esta consulta debe traer a los sumo 3 problemas
	$q ="select * from ";
	$q.="tipo_prob ";
	$q.="order by id_tipo_prob";
	$res_tmp=sql($q) or fin_pagina();
	while (!$res_tmp->EOF)
	{
		$aproblemas_all[$res_tmp->fields['id_tipo_prob']]=$res_tmp->fields['descripcion'];
		$res_tmp->movenext();
	}

	$i=0;//variable para contar la cantidad de problemas encontrados
	//HACER CONSULTAS PARA IDENTIFICAR LOS PROBLEMAS
	while (!$presup->EOF)
	{
		$id_entrega=$presup->fields['id_entrega_estimada'];

		//esta consulta debe traer a los sumo 3 problemas
		$q ="select * from problemas_op ";
		$q.="join tipo_prob using(id_tipo_prob) ";
		$q.="where id_usuario={$_ses_user['id']} AND id_entrega_estimada=$id_entrega ";
		$q.="order by id_tipo_prob";
		$problemas=sql($q) or fin_pagina();

		$check[PROB_FECHA_E]=$check[PROB_COMPRAR]=$check[PROB_RECIBIR]=1;//Para que el aviso salga una sola vez o no salga
		$id_prob[PROB_FECHA_E]=$id_prob[PROB_COMPRAR]=$id_prob[PROB_RECIBIR]=0;

		while (!$problemas->EOF)
		{
			//si no tiene postergar le doy un postergar ya vencido
			$atimestamp=getdate(strtotime($problemas->fields['postergar_hasta']?$problemas->fields['postergar_hasta']:'today'));
			$timestamp_postergar=mktime($atimestamp['hours'],$atimestamp['minutes'],0,$atimestamp['mon'],$atimestamp['mday']+$increment,$atimestamp['year']);
			$timestamp=strtotime(date("Y-m-j H:i:00"));

			$check[$problemas->fields['id_tipo_prob']]=($problemas->fields['descartar'] || $timestamp < $timestamp_postergar )?0:1;//Para que el aviso salga una sola vez o no salga
			$id_prob[$problemas->fields['id_tipo_prob']]=$problemas->fields['id_prob'];
			$descartar[$problemas->fields['id_tipo_prob']]=$problemas->fields['descartar']?1:0;
			$review[$problemas->fields['id_tipo_prob']]=$problemas->fields['last_review'];
			$dhm[$problemas->fields['id_tipo_prob']]=$problemas->fields['dias'].",".$problemas->fields['horas'].",".$problemas->fields['minutos'];

			$problemas->movenext();
		}
		if ($force_check)
		 $check[PROB_FECHA_E]=$check[PROB_COMPRAR]=$check[PROB_RECIBIR]=1;

		//si esta vencida la fecha estimada de entrega
		if ($check[PROB_FECHA_E] && $presup->fields['fecha_estimada'] && strtotime($presup->fields['fecha_estimada']) < strtotime('today'))
		{
			if ($silent)
			{
				//lanzo el archivo para que se muestren los problemas
				echo "<script>window.showModalDialog('op_problemas.php',null,'help:no;dialogHeight:600px;dialogWidth:800px'); </script>";
				return;//termino la ejecucion de este archivo;
			}

			//INDICAR PROBLEMA FECHA ENTREGA ESTIMADA VENCIDA
			//echo "FECHA ENTREGA ESTIMADA VENCIDA - LICITACION ".$presup->fields['id_licitacion']." - SEG Nº ".$presup->fields['nro']." - presupuesto ".$presup->fields['titulo']."<br>";
			$aproblemas[$i]['id_lic']=$presup->fields['id_licitacion'];
			$aproblemas[$i]['id_entrega_estimada']=$presup->fields['id_entrega_estimada'];
			$aproblemas[$i]['id_subir']=$presup->fields['id_subir'];
			$aproblemas[$i]['id_prob']=$id_prob[PROB_FECHA_E];
			$aproblemas[$i]['descartar']=$descartar[PROB_FECHA_E];
			$aproblemas[$i]['last_review']=$review[PROB_FECHA_E];
			$aproblemas[$i]['dhm']=$dhm[PROB_FECHA_E];
			$aproblemas[$i]['nro_seg']=$presup->fields['nro'];
			$aproblemas[$i]['numero']=$presup->fields['numero'];
			$aproblemas[$i]['presupuesto_titulo']=$presup->fields['titulo'];
			$aproblemas[$i]['vence_oc']=$presup->fields['vence_oc'];
			$aproblemas[$i]['cantidad']=$presup->fields['cantidad'];//cantidad de computadoras en el renglon
			$aproblemas[$i]['problema']['id_tipo']=PROB_FECHA_E;
			$aproblemas[$i++]['problema']['descripcion']=$aproblemas_all[PROB_FECHA_E];
		}
		while (!$presup->EOF && $id_entrega==$presup->fields['id_entrega_estimada'])
		{
			if ($check[PROB_COMPRAR])
			{
				//CONSULTA para saber que productos falta comprar en un determinado renglon_presupuesto
				//FUNCIONA PERFECTAMENTE
				$q ="select * from ";
				$q.="renglon_presupuesto_new ";
				$q.="join producto_presupuesto_new using(id_renglon_prop)";
				$q.="join productos p using(id_producto)";
				$q.="left join oc_pp using(id_producto_presupuesto)";
				$q.="left join general.tipos_prod tp using(id_tipo_prod)";
				$q.="where id_renglon_prop=".$presup->fields['id_renglon_prop']." ";
				$q.="AND NOT (tp.codigo='conexos' OR tp.codigo='garantia') ";
				$productos=sql($q) or fin_pagina();

				while (!$productos->EOF)
				{
					//si el producto no tiene orden de compra
					if ($productos->fields['nro_orden']=="")
					{
						if ($silent)
						{
							//lanzo el archivo para que se muestren los problemas
							echo "<script>window.showModalDialog('op_problemas.php',null,'help:no;dialogHeight:600px;dialogWidth:800px'); </script>";
							return;//termino la ejecucion de este archivo;
						}
						//INDICAR PROBLEMA FALTA COMPRAR MATERIAL
						//echo "FALTA COMPRAR MATERIAL - LICITACION ".$presup->fields['id_licitacion']." - SEG Nº ".$presup->fields['nro']." - producto ".$productos->fields['desc_orig']." <br>";
						$aproblemas[$i]['id_lic']=$presup->fields['id_licitacion'];
						$aproblemas[$i]['id_entrega_estimada']=$presup->fields['id_entrega_estimada'];
						$aproblemas[$i]['id_subir']=$presup->fields['id_subir'];
						$aproblemas[$i]['id_prob']=$id_prob[PROB_COMPRAR];
						$aproblemas[$i]['descartar']=$descartar[PROB_COMPRAR];
						$aproblemas[$i]['last_review']=$review[PROB_COMPRAR];
						$aproblemas[$i]['dhm']=$dhm[PROB_COMPRAR];
						$aproblemas[$i]['nro_seg']=$presup->fields['nro'];
						$aproblemas[$i]['numero']=$presup->fields['numero'];
						$aproblemas[$i]['presupuesto_titulo']=$presup->fields['titulo'];
						$aproblemas[$i]['vence_oc']=$presup->fields['vence_oc'];
						$aproblemas[$i]['cantidad']=$presup->fields['cantidad'];//cantidad de computadoras en el renglon
						$aproblemas[$i]['problema']['id_tipo']=PROB_COMPRAR;
						$aproblemas[$i++]['problema']['descripcion']=$aproblemas_all[PROB_COMPRAR];

						$check[PROB_COMPRAR]=0;
						break;
					}
					$productos->movenext();
				}
			}
			if ($check[PROB_RECIBIR])
			{
				//CONSULTA para saber que productos falta recibir en un determinado renglon_presupuesto
				//FUNCIONA PERFECTAMENTE
				$q ="select f.id_fila,sum(r.cantidad) as recibidos,sum(f.cantidad) as comprados from ";
				$q.="renglon_presupuesto_new ";
				$q.="join producto_presupuesto_new using(id_renglon_prop)";
				$q.="join oc_pp using(id_producto_presupuesto)";
				$q.="join productos p using(id_producto)";
				$q.="join general.tipos_prod tp using(id_tipo_prod)";
				$q.="join fila f using(id_fila)";
				$q.="left join recibido_entregado r using(id_fila)";
				$q.="where id_renglon_prop=".$presup->fields['id_renglon_prop']." ";
				//productos que no se debe chequear si se recibieron
				$q.="AND NOT (tp.codigo='conexos' OR tp.codigo='monitor' OR tp.codigo='garantia' OR tp.codigo='software' OR f.es_agregado=1) ";
				$q.="group by f.id_fila ";
				$q.="having sum(r.cantidad) is null OR sum(r.cantidad) < sum(f.cantidad) ";
				$no_recibidos=sql($q) or fin_pagina();

				if ($no_recibidos->recordcount()>0)
				{
						if ($silent)
						{
							//lanzo el archivo para que se muestren los problemas
							echo "<script>window.showModalDialog('op_problemas.php',null,'help:no;dialogHeight:600px;dialogWidth:800px'); </script>";
							return;//termino la ejecucion de este archivo;
						}
					//INDICAR PROBLEMA FALTA RECIBIR MATERIAL
					//echo "FALTA RECIBIR MATERIAL - LICITACION ".$presup->fields['id_licitacion']." - SEG Nº ".$presup->fields['nro']." - recordcount ".$no_recibidos->recordcount()." recib: ".$no_recibidos->fields['recibidos'] ." comp: ".$no_recibidos->fields['comprados'] ."<br>";
						$aproblemas[$i]['id_lic']=$presup->fields['id_licitacion'];
						$aproblemas[$i]['id_entrega_estimada']=$presup->fields['id_entrega_estimada'];
						$aproblemas[$i]['id_subir']=$presup->fields['id_subir'];
						$aproblemas[$i]['id_prob']=$id_prob[PROB_RECIBIR];
						$aproblemas[$i]['descartar']=$descartar[PROB_RECIBIR];
						$aproblemas[$i]['last_review']=$review[PROB_RECIBIR];
						$aproblemas[$i]['dhm']=$dhm[PROB_RECIBIR];
						$aproblemas[$i]['nro_seg']=$presup->fields['nro'];
						$aproblemas[$i]['numero']=$presup->fields['numero'];
						$aproblemas[$i]['presupuesto_titulo']=$presup->fields['titulo'];
						$aproblemas[$i]['vence_oc']=$presup->fields['vence_oc'];
						$aproblemas[$i]['cantidad']=$presup->fields['cantidad'];//cantidad de computadoras en el renglon
						$aproblemas[$i]['problema']['id_tipo']=PROB_RECIBIR;
						$aproblemas[$i++]['problema']['descripcion']=$aproblemas_all[PROB_RECIBIR];

						$check[PROB_RECIBIR]=0;
				}
			}
			$presup->movenext();
		}
	}
}

if ($silent) return;//termino la ejecucion de este archivo;
$i=0;
 $aproblemas2=array();
	foreach ($aproblemas as $arr )
	{
		$lnk = encode_link("ver_seguimiento_ordenes.php",array("cmd1"=>"detalle","id"=>$arr['id_lic'], "id_entrega_estimada"=>$arr['id_entrega_estimada'],"nro"=>$arr["nro_seg"],"id_subir"=>$arr["id_subir"],"nro_orden_cliente"=>$arr["numero"]));
		//le sumo uno para que incluya el dia actual
		$dif_dias=diferencia_dias_habiles(date2("S"),date2("S",$arr['vence_oc']))+1;
		if ($arr['cantidad'] <= 5)
			switch ($dif_dias)
			{
				case 4: $tr_color="yellow";break;
				case 3: $tr_color="orange";break;
				case 2: $tr_color="#FF8080";break;
				default: $tr_color="#FF8080";break;
			}
		elseif (5 < $arr['cantidad'] && $arr['cantidad'] <= 25)
			switch ($dif_dias)
			{
				case 5: $tr_color="yellow";break;
				case 4: $tr_color="orange";break;
				case 3: $tr_color="#FF8080";break;
				default: $tr_color="#FF8080";break;
			}
		elseif (25 < $arr['cantidad'] && $arr['cantidad'] <= 100)
			switch ($dif_dias)
			{
				case 6: $tr_color="yellow";break;
				case 5: $tr_color="orange";break;
				case 4: $tr_color="#FF8080";break;
				default: $tr_color="#FF8080";break;
			}
		elseif (100 < $arr['cantidad'] && $arr['cantidad'] <= 300)
			switch ($dif_dias)
			{
				case 7: $tr_color="yellow";break;
				case 6: $tr_color="orange";break;
				case 5: $tr_color="#FF8080";break;
				default: $tr_color="#FF8080";break;
			}
		else //mayores que 300
			switch ($dif_dias)
			{
				case 8: $tr_color="yellow";break;
				case 7: $tr_color="orange";break;
				case 6: $tr_color="#FF8080";break;
				default: $tr_color="#FF8080";break;
			}

		$aproblemas2[$i]=array();
		$aproblemas2[$i]['id_prob']=$arr['id_prob']?$arr['id_prob']:0;
		$aproblemas2[$i]['id_tipo_prob']=$arr['problema']['id_tipo'];
		$aproblemas2[$i]['id_entrega']=$arr['id_entrega_estimada'];
		$aproblemas2[$i]['id_lic']=$arr['id_lic'];
		$aproblemas2[$i]['descripcion']=$arr['problema']['descripcion'];
		$aproblemas2[$i]['vence_oc']=date2("S",$arr['vence_oc']);
		$aproblemas2[$i]['nro_seg']=$arr['nro_seg'];
		$aproblemas2[$i]['nro_orden_cliente']=$arr['numero'];
		$aproblemas2[$i]['link']=$lnk;
		list($dias,$horas,$minutos)=split(",",$arr['dhm']);
		$aproblemas2[$i]['postergar']=($dias?$dias:0).",".($horas?$horas:0).",".($minutos?$minutos:0);
		$aproblemas2[$i]['postergar_hasta']=null;
		$aproblemas2[$i]['descartar']=$arr['descartar'];
		$aproblemas2[$i]['trcolor']=$tr_color;
		$aproblemas2[$i++]['last_review']=$arr['last_review']?date2("SHM",$arr['last_review']):"sin revisar";
	}
	unset ($aproblemas);
	$aproblemas=$aproblemas2;
	unset($aproblemas2);
?>
