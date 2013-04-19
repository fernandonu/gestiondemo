<?
/*
Autor: GACZ
Creado: lunes 20/12/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2004/12/22 22:44:20 $
*/

require_once("../../config.php");

//estas constantes definen los id en la DB de los distintos problemas
define("PROB_FECHA_E",1);
define("PROB_COMPRAR",2);
define("PROB_RECIBIR",3);

$fecha_db=date("Y-m-j");
//$q ="Select count(*) from usuarios_reg where id_usuario=".$_ses_user['id'];
//$r1=sql($q) or fin_pagina();

//si el usuario esta registrado al programa de ayuda
if (1) //($r1->fields['count']!=0)
{
	$q ="select distinct lp.*,ee.*,rp.*,sl.nro_orden as numero from ";
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
	
	$i=0;//variable para contar la cantidad de problemas encontrados
	//HACER CONSULTAS PARA IDENTIFICAR LOS PROBLEMAS
	while (!$presup->EOF)
	{
		$id_entrega=$presup->fields['id_entrega_estimada'];
		
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
		
		//esta consulta debe traer a los sumo 3 problemas
		$q ="select * from problemas_op ";
		$q.="join tipo_prob using(id_tipo_prob) ";
		$q.="where id_usuario={$_ses_user['id']} AND id_entrega_estimada=$id_entrega ";
		$q.="order by id_tipo_prob";
		$problemas=sql($q) or fin_pagina();
	
		while (!$problemas->EOF)
		{
			$atimestamp=getdate(strtotime($problemas->fields['postergar_hasta'])); 
			$timestamp_postergar=mktime($atimestamp['hours'],$atimestamp['minutes'],0,$atimestamp['mon'],$atimestamp['mday']+$increment,$atimestamp['year']);
			$timestamp=strtotime(date("Y-m-j H:i:00"));
			
			//debe entrar a lo sumo una vez en cada uno excepto el default
			switch ($problemas->fields['id_tipo_prob'])
			{
				case 1://PROBLEMA FECHA ENTREGA ESTIMADA VENCIDA
 							 $check_fecha=($problemas->fields['descartar'] || $timestamp < $timestamp_postergar )?0:1;//Para que el aviso salga una sola vez o no salga
 							 break;
				case 2://PROBLEMA FALTA COMPRAR MATERIAL				
 							 $check_comprar=($problemas->fields['descartar'] || $timestamp < $timestamp_postergar )?0:1;//Para que el aviso salga una sola vez o no salga
 							 break; 							 
				case 3://PROBLEMA FALTA RECIBIR MATERIAL				
 							 $check_recibir=($problemas->fields['descartar'] || $timestamp < $timestamp_postergar )?0:1;//Para que el aviso salga una sola vez o no salga
 							 break; 							 
				default:
								$check_fecha=1;//Para que el aviso salga una sola vez o no salga
								$check_comprar=1;//Para que el aviso salga una sola vez o no salga
								$check_recibir=1;//Para que el aviso salga una sola vez o no salga
			}
			$problemas->movenext();
		}
		//si aun no hay problemas guardados para este usuario
		//se chequean todos
		if ($problemas->recordcount()==0)
		{
			$check_fecha=1;//Para que el aviso salga una sola vez o no salga
			$check_comprar=1;//Para que el aviso salga una sola vez o no salga
			$check_recibir=1;//Para que el aviso salga una sola vez o no salga
		}
		//si esta vencida la fecha estimada de entrega
		if ($check_fecha && $presup->fields['fecha_estimada'] && strtotime($presup->fields['fecha_estimada']) < strtotime('today'))
		{
			//INDICAR PROBLEMA FECHA ENTREGA ESTIMADA VENCIDA
			//echo "FECHA ENTREGA ESTIMADA VENCIDA - LICITACION ".$presup->fields['id_licitacion']." - SEG Nº ".$presup->fields['nro']." - presupuesto ".$presup->fields['titulo']."<br>";
			$aproblemas[$i]['id_lic']=$presup->fields['id_licitacion'];
			$aproblemas[$i]['id_seguimiento']=$presup->fields['id_seguimiento'];
			$aproblemas[$i]['nro_seg']=$presup->fields['nro'];
			$aproblemas[$i]['numero']=$presup->fields['numero'];
			$aproblemas[$i]['presupuesto_titulo']=$presup->fields['titulo'];
			$aproblemas[$i]['vence_oc']=$presup->fields['vence_oc'];
			$aproblemas[$i]['problema']['id']=PROB_FECHA_E;
			$aproblemas[$i++]['problema']['descripcion']=$aproblemas_all[PROB_FECHA_E];
		}
		while (($check_comprar || $check_recibir) && !$presup->EOF && $id_entrega==$presup->fields['id_entrega_estimada'])
		{		
			if ($check_comprar)
			{
				//CONSULTA para saber que productos falta comprar en un determinado renglon_presupuesto
				//FUNCIONA PERFECTAMENTE
				$q ="select * from ";
				$q.="renglon_presupuesto_new ";
				$q.="join producto_presupuesto_new using(id_renglon_prop)";
				$q.="join productos p using(id_producto)";
				$q.="left join oc_pp using(id_producto_presupuesto)";
				$q.="where id_renglon_prop=".$presup->fields['id_renglon_prop']." ";
				$q.="AND NOT (p.tipo='conexos' OR p.tipo='garantia') ";
				$productos=sql($q) or fin_pagina();
				
				while (!$productos->EOF)
				{
					//si el producto no tiene orden de compra
					if ($productos->fields['nro_orden']=="")
					{
						//INDICAR PROBLEMA FALTA COMPRAR MATERIAL
						//echo "FALTA COMPRAR MATERIAL - LICITACION ".$presup->fields['id_licitacion']." - SEG Nº ".$presup->fields['nro']." - producto ".$productos->fields['desc_orig']." <br>";				
						$aproblemas[$i]['id_lic']=$presup->fields['id_licitacion'];
						$aproblemas[$i]['id_seguimiento']=$presup->fields['id_seguimiento'];
						$aproblemas[$i]['nro_seg']=$presup->fields['nro'];
						$aproblemas[$i]['numero']=$presup->fields['numero'];
						$aproblemas[$i]['presupuesto_titulo']=$presup->fields['titulo'];
						$aproblemas[$i]['vence_oc']=$presup->fields['vence_oc'];
						$aproblemas[$i]['problema']['id']=PROB_COMPRAR;
						$aproblemas[$i++]['problema']['descripcion']=$aproblemas_all[PROB_COMPRAR];
						
						$check_comprar=0;
						break;
					}
					$productos->movenext();
				}
			}
			if ($check_recibir)
			{
				//CONSULTA para saber que productos falta recibir en un determinado renglon_presupuesto
				//FUNCIONA PERFECTAMENTE				
				$q ="select f.id_fila,sum(r.cantidad) as recibidos,sum(f.cantidad) as comprados from ";
				$q.="renglon_presupuesto_new ";
				$q.="join producto_presupuesto_new using(id_renglon_prop)";
				$q.="join oc_pp using(id_producto_presupuesto)";
				$q.="join productos p using(id_producto)";
				$q.="join fila f using(id_fila)";
				$q.="left join recibidos r using(id_fila)";
				$q.="where id_renglon_prop=".$presup->fields['id_renglon_prop']." ";
				//productos que no se debe chequear si se recibieron
				$q.="AND NOT (p.tipo='conexos' OR p.tipo='monitor' OR p.tipo='garantia' OR p.tipo='software' OR f.es_agregado=1) ";
				$q.="group by f.id_fila ";
				$q.="having sum(r.cantidad) is null OR sum(r.cantidad) < sum(f.cantidad) ";
				$no_recibidos=sql($q) or fin_pagina();
				
				if ($no_recibidos->recordcount()>0)
				{
					//INDICAR PROBLEMA FALTA RECIBIR MATERIAL
					//echo "FALTA RECIBIR MATERIAL - LICITACION ".$presup->fields['id_licitacion']." - SEG Nº ".$presup->fields['nro']." - recordcount ".$no_recibidos->recordcount()." recib: ".$no_recibidos->fields['recibidos'] ." comp: ".$no_recibidos->fields['comprados'] ."<br>";			
						$aproblemas[$i]['id_lic']=$presup->fields['id_licitacion'];
						$aproblemas[$i]['id_seguimiento']=$presup->fields['id_seguimiento'];
						$aproblemas[$i]['nro_seg']=$presup->fields['nro'];
						$aproblemas[$i]['numero']=$presup->fields['numero'];
						$aproblemas[$i]['presupuesto_titulo']=$presup->fields['titulo'];
						$aproblemas[$i]['vence_oc']=$presup->fields['vence_oc'];
						$aproblemas[$i]['problema']['id']=PROB_RECIBIR;
						$aproblemas[$i++]['problema']['descripcion']=$aproblemas_all[PROB_RECIBIR];
						
						$check_recibir=0;
				}
			}
			$presup->movenext();
		}
	}
}
?>
<html>
<head>
<title>Problemas en seguimiento de produccion</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script src="../../lib/fns.js"></script>
<?=$html_header ?>
<form name="form1" method="post" action="">
<table width="95%"  border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="15%"><strong>Postergar Todos</strong></td>
    <td width="12%" align="center">
    <input type="text" style="width:15px;border-right:none;text-align:right" title="Dias : Horas : Minutos" name="dias" maxlength="1"><input type="text" tabindex="-1" style="width:12px;border-right:none;border-left:none;text-align:center;" value="d" title="Dias : Horas : Minutos" readonly ><input type="text" style="width:20px;border-left:none;border-right:none;text-align:right" name="horas" title="Dias : Horas : Minutos" maxlength="2"><input type="text" tabindex="-1" style="width:12px;border-right:none;border-left:none;text-align:center;" value="h" title="Dias : Horas : Minutos" readonly ><input type="text" style="width:20px;border-left:none;border-right:none;text-align:right" title="Dias : Horas : Minutos" name="minutos" maxlength="2"><input type="text" tabindex="-1" style="width:14px;border-left:none;text-align:center;" value="m" title="Dias : Horas : Minutos" readonly >
    </td>
    <td width="73%"><input type="button" name="bpostergar" value="Postergar">
</td>
  </tr>
  <tr>
    <td><strong>Descartar Todos</strong></td>
    <td align="center"><input name="chk_all" type="checkbox" id="chk_all" value="checkbox"></td>
    <td><input type="button" name="bdescartar" value="Descartar"></td>
  </tr>
</table>
<br />
<table width="95%"  border="1" align="center">
  <th colspan="6">Problemas  de Producci&oacute;n</th>
  <tr align="center">
    <td width="9%" scope="col">ID Licitacion </td>
    <td width="10%" scope="col">Venc OC </td>
    <td width="10%" scope="col">N&ordm; Seg. </td>
    <td width="45%" scope="col">Descripci&oacute;n del Problema</td>
    <td width="19%" scope="col">Postergar</td>
    <td width="7%" scope="col">Descartar</td>
  </tr>
<? 
	$q="select * from opt_postergar order by minutos,horas,dias";
	$postergar=sql($q) or fin_pagina();
	foreach ($aproblemas as $arr ) 
	{
		$postergar->movefirst();
?>  
  <tr onclick="alternar_color(this,'#a6c2fc')" >
    <td><?=$arr['id_lic'] ?> </td>
    <td><?=date2("S",$arr['vence_oc']) ?></td>
    <td align="center"><?=$arr['numero']." <b>/</b> ".$arr['nro_seg'] ?> </td>
    <td><?=$arr['problema']['descripcion'] ?> </td>
    <td align="center">
    <input type="text" style="width:15px;border-right:none;text-align:right" title="Dias : Horas : Minutos" name="dias" maxlength="1"><input type="text" tabindex="-1" style="width:12px;border-right:none;border-left:none;text-align:center;" value="d" title="Dias : Horas : Minutos" readonly ><input type="text" style="width:20px;border-left:none;border-right:none;text-align:right" name="horas" title="Dias : Horas : Minutos" maxlength="2"><input type="text" tabindex="-1" style="width:12px;border-right:none;border-left:none;text-align:center;" value="h" title="Dias : Horas : Minutos" readonly ><input type="text" style="width:20px;border-left:none;border-right:none;text-align:right" title="Dias : Horas : Minutos" name="minutos" maxlength="2"><input type="text" tabindex="-1" style="width:14px;border-left:none;text-align:center;" value="m" title="Dias : Horas : Minutos" readonly >
    </td>
    <td align="center"><input name="chk_descartar" type="checkbox" value="1"></td>
  </tr>
<?
	}
?>  
</table>
<center>
<br />
<br />
  <input type="submit" name="Submit" value="Terminar">
  </center>
</form>
</body>
</html>
<?
fin_pagina();
?>