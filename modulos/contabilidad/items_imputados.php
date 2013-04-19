<?php
/*
AUTOR: Gabriel (28/10/2005)
MODIFICADO POR:
$Author: gabriel $
$Revision: 1.4 $
$Date: 2005/11/09 20:25:23 $
*/
	require("../../config.php");
	require_once("../personal/gutils.php");
	
	$datos_fijos=array(
		"first_time"=>"1",
		"sels"=>""
	);
	variables_form_busqueda("var_items", $datos_fijos);
	
	$datos_barra = array(
		array("descripcion"=> "Pendientes", "cmd"=> "pendientes"),
		array("descripcion"=> "Controladas", "cmd"=> "controladas"),
		array("descripcion"=> "Todas", "cmd"=> "todas")
	);

	if ($cmd == ""){
		$cmd="pendientes";
		$_ses_var_items["cmd"]=$cmd;
  	phpss_svars_set("_ses_var_items", $_ses_var_items);
	}
	///////////////////////// DATOS DE SELECTS //////////////////////////
	if (!$sels){
		$sels=array("provincias"=>"",	"estado"=>"",	"tipo_cuenta"=>"",	"tipo_imput"=>"");
		$rta_consulta=sql("select id_distrito as key, nombre as label from licitaciones.distrito order by nombre", "c25") or fin_pagina();
		$i=0;
		while ($sels["provincias"][$i++]=$rta_consulta->fetchRow());
		$sels["provincias"][$i-1]["key"]=-1; $sels["provincias"][$i-1]["label"]=" ";

		$rta_consulta=sql("select id_estado_imputacion as key, nombre as label from contabilidad.estado_imputacion where activo=1 order by nombre", "c27") or fin_pagina();
		$i=0;
		while ($sels["estado"][$i++]=$rta_consulta->fetchRow());
		$sels["estado"][$i-1]["key"]=-1; $sels["estado"][$i-1]["label"]=" ";

		$rta_consulta=sql("select numero_cuenta as key, concepto||' ['||plan||']' as label from general.tipo_cuenta order by concepto, plan", "c30") or fin_pagina();
		$i=0;
		while ($sels["tipo_cuenta"][$i++]=$rta_consulta->fetchRow());
		$sels["tipo_cuenta"][$i-1]["key"]=-1; $sels["tipo_cuenta"][$i-1]["label"]=" ";
		
		$rta_consulta=sql("select id_tipo_imputacion as key, descripcion as label, numero_cuenta from contabilidad.tipo_imputacion where activo=1 order by descripcion", "c57") or fin_pagina();
		$i=0;
		while ($sels["tipo_imput"][$i++]=$rta_consulta->fetchRow());
		$sels["tipo_imput"][$i-1]["key"]=-1; $sels["tipo_imput"][$i-1]["label"]=" ";
		
		/*$rta_consulta=sql("select login as key, apellido||', '||nombre||' ('||login||')' as label from sistema.usuarios order by apellido, nombre, login", "c67") or fin_pagina();
		$i=0;
		while ($sels["usuarios"][$i++]=$rta_consulta->fetchRow());
		$sels["usuarios"][$i-1]["key"]=-1; $sels["usuarios"][$i-1]["label"]=" ";*/
		
		$_ses_var_items["sels"]=comprimir_variable($sels);
		phpss_svars_set("_ses_var_items", $_ses_var_items);
	}else $sels=descomprimir_variable($sels);
	
	if ($_POST["first_time"]){
		$first_time="0";
		$_ses_var_items["first_time"]=$first_time;
  	phpss_svars_set("_ses_var_items", $_ses_var_items);
	}
	
	/////////////////////////////////////////////////////////////////////
	
	//echo("<br>POST:");print_r($_POST);echo("<br>");
	if($parametros['accion']!=""){ Aviso($parametros['accion']);}
	$orden=array(
		"default" => "1",
    "default_up" => "0",
		"1"=>"nombre_cuenta",
		"2"=>"detalle_imputacion.monto",
		"3"=>"tipo_imputacion.descripcion",
		"4"=>"estado_imputacion.nombre",
		"5"=>"detalle_imputacion.usuario",
		"6"=>"detalle_imputacion.fecha",
		"7"=>"tipo_cuenta.numero_contador"
	);
	$filtro=array(
		"tipo_cuenta.numero_contador"=>"Nro. Contador",
		"tipo_cuenta.concepto||' ['||tipo_cuenta.plan||']'"=>"Cuenta",
		"detalle_imputacion.monto"=>"Monto",
		"tipo_imputacion.descripcion"=>"Tipo",
		"detalle_imputacion.usuario"=>"Usuario",
		"detalle_imputacion.fecha"=>"Fecha"
	);
	/////////////////////////////////// CONSULTA ///////////////////////////////////////
	$sql_tmp="select tipo_cuenta.numero_contador, detalle_imputacion.numero_cuenta, detalle_imputacion.usuario, detalle_imputacion.fecha, 
			detalle_imputacion.monto, tipo_imputacion.descripcion as tipo_imput, distrito.nombre as provincia, 
			estado_imputacion.nombre as estado_imput, tipo_cuenta.concepto||' ['||tipo_cuenta.plan||']' as nombre_cuenta
		from contabilidad.imputacion
			left join contabilidad.detalle_imputacion using(id_imputacion)
			left join bancos.tipo_banco using(idbanco)
			left join licitaciones.distrito using(id_distrito)
			left join contabilidad.estado_imputacion using(id_estado_imputacion)
			left join contabilidad.log_imputacion using(id_imputacion)
			left join general.tipo_cuenta using (numero_cuenta)
			left join contabilidad.tipo_imputacion using(id_tipo_imputacion)
			left join bancos.cheques using(númeroch)
			left join bancos.débitos using(iddébito)
			left join caja.ingreso_egreso using(id_ingreso_egreso)";
	
	if ($cmd=="pendientes")	$where_tmp="((estado_imputacion.id_estado_imputacion=4)or(estado_imputacion.id_estado_imputacion=1))";
	elseif ($cmd=="controladas")	$where_tmp="((estado_imputacion.id_estado_imputacion!=4)and(estado_imputacion.id_estado_imputacion!=1))";
	else $where_tmp="(true)";
	if ($_POST["ch_buscar_cuentas"]){
		;//nada: la condición la agrega el form búsqueda
	}else $keyword="";
	
	if (($_POST["ch_buscar_fecha"])||($first_time)){
		$where_tmp.="and(imputacion.fecha ilike '".$_POST["sel_agno"]."-".$_POST["sel_mes"]."-%')";
	}else	$_POST["sel_mes"]=$_POST["sel_agno"]="";
	
	if ($_POST["ch_t_banco"]){
		$where_tmp.="and((tipo_banco.nombrebanco ilike '%".$_POST["t_banco"]."%')and(tipo_banco.activo=1))";
	}else $_POST["t_banco"]="";
	
	if ($_POST["ch_t_cheque"]){
		if (($_POST["t_cheque_desde"])&&($_POST["t_cheque_hasta"])){
			$where_tmp.="and((imputacion.númeroch>=".$_POST["t_cheque_desde"].")and(imputacion.númeroch<=".$_POST["t_cheque_hasta"]."))";
		}elseif ($_POST["t_cheque_desde"]) $where_tmp.="and(imputacion.númeroch>=".$_POST["t_cheque_desde"].")";
		elseif ($_POST["t_cheque_hasta"]) $where_tmp.="and(imputacion.númeroch<=".$_POST["t_cheque_hasta"].")";
	}else $_POST["t_cheque_desde"]=$_POST["t_cheque_hasta"]="";
	
	if ($_POST["ch_sel_estado"]){
		$where_tmp.="and(imputacion.id_estado_imputacion=".$_POST["sel_estado"].")";
	}else $_POST["sel_estado"]="";
	
	if ($_POST["ch_sel_tipo_cuenta"]){
		$where_tmp.="and(detalle_imputacion.numero_cuenta=".$_POST["sel_tipo_cuenta"].")";
	}else $_POST["sel_tipo_cuenta"]="";
	
	if ($_POST["ch_sel_provincias"]){
		$where_tmp.="and(detalle_imputacion.id_distrito=".$_POST["sel_provincias"].")";
	}else $_POST["sel_provincias"]="";
	
	if ($_POST["ch_t_fecha"]){
		if (($_POST["t_fecha_desde"])&&($_POST["t_fecha_hasta"])){
			$where_tmp.="and((detalle_imputacion.fecha>='".Fecha_db($_POST["t_fecha_desde"])."')and(detalle_imputacion.fecha<='".Fecha_db($_POST["t_fecha_hasta"])."'))";
		}elseif ($_POST["t_fecha_desde"]) $where_tmp.="and(detalle_imputacion.fecha>='".Fecha_db($_POST["t_fecha_desde"])."')";
		elseif ($_POST["t_fecha_hasta"]) $where_tmp.="and(detalle_imputacion.fecha<='".Fecha_db($_POST["t_fecha_hasta"])."')";
	}else $_POST["t_fecha_desde"]=$_POST["t_fecha_hasta"]="";
	
	if ($_POST["ch_sel_tipo_imputacion"]){
		$where_tmp.="and((detalle_imputacion.id_tipo_imputacion=".$_POST["sel_tipo_imputacion"].")and(tipo_imputacion.activo=1))";
	}else $_POST["sel_tipo_imputacion"]="";
	
	if ($_POST["ch_t_usuario"]){
		$where_tmp.="and(detalle_imputacion.usuario ilike '%".$_POST["t_usuario"]."%')";
	}else $_POST["t_usuario"]="";
	
	if ($_POST["ch_t_monto"]){
		if(($_POST["t_monto_desde"]!="")&&($_POST["t_monto_hasta"]!="")){
			$where_tmp.="and((detalle_imputacion.monto>=".$_POST["t_monto_desde"].")and(detalle_imputacion.monto<=".$_POST["t_monto_hasta"]."))";
		}elseif ($_POST["t_monto_desde"]!="") $where_tmp.="and(detalle_imputacion.monto>=".$_POST["t_monto_desde"].")";
		elseif ($_POST["t_monto_hasta"]!="") $where_tmp.="and(detalle_imputacion.monto<=".$_POST["t_monto_hasta"].")";
	}else $_POST["t_monto_desde"]=$_POST["t_monto_hasta"]="";
	
	if ($_POST["ch_t_importech"]){
		if(($_POST["t_importech_desde"]!="")&&($_POST["t_importech_hasta"]!="")){
			$where_tmp.="and((cheques.importech>=".$_POST["t_importech_desde"].")and(cheques.importech<=".$_POST["t_importech_hasta"]."))";
		}elseif ($_POST["t_importech_desde"]!="") $where_tmp.="and(cheques.importech>=".$_POST["t_importech_desde"].")";
		elseif ($_POST["t_importech_hasta"]!="") $where_tmp.="and(cheques.importech<=".$_POST["t_importech_hasta"].")";
	}else $_POST["t_importech_desde"]=$_POST["t_importech_hasta"]="";
	
	if ($_POST["ch_t_fechadebito"]){
		if(($_POST["t_fechadebito_desde"])&&($_POST["t_fechadebito_hasta"])){
			$where_tmp.="and((débitos.fechadébito>='".Fecha_db($_POST["t_fechadebito_desde"])."')and(débitos.fechadébito<='".Fecha_db($_POST["t_fechadebito_hasta"])."'))";
		}elseif ($_POST["t_fechadebito_desde"]) $where_tmp.="and(débitos.fechadébito>='".Fecha_db($_POST["t_fechadebito_desde"])."')";
		elseif ($_POST["t_fechadebito_hasta"]) $where_tmp.="and(débitos.fechadébito<='".Fecha_db($_POST["t_fechadebito_hasta"])."')";
	}else $_POST["t_fechadebito_desde"]=$_POST["t_fechadebito_hasta"]="";
	
	if ($_POST["ch_t_importedeb"]){
		if(($_POST["t_importedeb_desde"]!="")&&($_POST["t_importedeb_hasta"]!="")){
			$where_tmp.="and((débitos.importedéb>=".$_POST["t_importedeb_desde"].")and(débitos.importedéb<=".$_POST["t_importedeb_hasta"]."))";
		}elseif ($_POST["t_importedeb_desde"]!="") $where_tmp.="and(débitos.importedéb>=".$_POST["t_importedeb_desde"].")";
		elseif ($_POST["t_importedeb_hasta"]!="") $where_tmp.="and(débitos.importedéb<=".$_POST["t_importedeb_hasta"].")";
	}else $_POST["t_importedeb_desde"]=$_POST["t_importedeb_hasta"]="";
	
	if ($_POST["ch_t_monto2"]){
		if(($_POST["t_monto2_desde"]!="")&&($_POST["t_monto2_hasta"]!="")){
			$where_tmp.="and((ingreso_egreso.monto>=".$_POST["t_monto2_desde"].")and(ingreso_egreso.monto<=".$_POST["t_monto2_hasta"]."))";
		}elseif ($_POST["t_monto2_desde"]!="") $where_tmp.="and(ingreso_egreso.monto>=".$_POST["t_monto2_desde"].")";
		elseif ($_POST["t_monto2_hasta"]!="") $where_tmp.="and(ingreso_egreso.monto<=".$_POST["t_monto2_hasta"].")";
	}else $_POST["t_monto2_desde"]=$_POST["t_monto2_hasta"]="";
	if ($_POST["ch_t_fecha_creacion"]){
		if(($_POST["t_fecha_creacion_desde"])&&($_POST["t_fecha_creacion_hasta"])){
			$where_tmp.="and((ingreso_egreso.fecha_creacion>='".Fecha_db($_POST["t_fecha_creacion_desde"])."')and(ingreso_egreso.fecha_creacion<='".Fecha_db($_POST["t_fecha_creacion_hasta"])."'))";
		}elseif ($_POST["t_fecha_creacion_desde"]) $where_tmp.="and(ingreso_egreso.fecha_creacion>='".Fecha_db($_POST["t_fecha_creacion_desde"])."')";
		elseif ($_POST["t_fecha_creacion_hasta"]) $where_tmp.="and(ingreso_egreso.fecha_creacion<='".Fecha_db($_POST["t_fecha_creacion_hasta"])."')";
	}else $_POST["t_fecha_creacion_desde"]=$_POST["t_fecha_creacion_hasta"]="";
	if ($_POST["ch_t_item"]){
		$where_tmp.="and(ingreso_egreso.item ilike '%".$_POST["t_item"]."%')";
	}else $_POST["t_item"]="";
	$where_tmp.="group by tipo_cuenta.numero_contador, detalle_imputacion.numero_cuenta, detalle_imputacion.usuario, detalle_imputacion.fecha, 
		detalle_imputacion.monto, tipo_imputacion.descripcion, distrito.nombre, estado_imputacion.nombre, 
		tipo_cuenta.concepto, tipo_cuenta.plan";
	////////////////////////////////////////////////////////////////////////////////////
	echo($html_header);	
	cargar_calendario();
?>
<form name="form_items_imputados" method="POST" action="items_imputados.php">
	<input type="hidden" name="first_time" value="<?=$first_time?>">
	<table width="90%" border="0" cellpadding="1" cellspacing="1" align="center" bgcolor="<?=$bgcolor3?>"> 
		<tr>
			<td colspan="2">
				<?=generar_barra_nav($datos_barra);?>
			<td>
		</tr>
		<tr>
			<td align="center" bgcolor="<?=$bgcolor3?>" colspan="2">
				<input type="checkbox" name="ch_buscar_cuentas" <?=((($_POST["ch_buscar_cuentas"])||($first_time))?"checked":"")?>>
				<?
					$itemspp=75;
					list($sql, $total_items, $link_pagina, $up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar"); 
				?>
				<input type="checkbox" name="ch_buscar_fecha" <?=((($_POST["ch_buscar_fecha"])||($first_time==1))?"checked":"")?>>
				<?
					g_draw_select("sel_mes", (($_POST["sel_mes"])?$_POST["sel_mes"]:date("m")));
					g_draw_range_select("sel_agno", (($_POST["sel_agno"])?$_POST["sel_agno"]:date("Y")), 2000, date("Y"));
				?>
					<input type="checkbox" name="sel_periodo" value="avanzado" <?=(($_POST["sel_periodo"])?" checked ":"")?> onclick="document.all.tabla_busqueda_avanzada.style.display=(this.checked)?'inline':'none';">&nbsp;Avanzado
					<input type=submit name=buscar value='Buscar'>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table width="100%" id="tabla_busqueda_avanzada" style="display:none">
					<tr>
						<td id="mo" colspan="2"><h3>B&uacute;squeda avanzada</h3></td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_banco" <?=(($_POST["ch_t_banco"])?"checked":"")?>>
							Banco:
						</td>
						<td><input type="text" name="t_banco" value="<?=$_POST["t_banco"]?>"></td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_cheque" <?=(($_POST["ch_t_cheque"])?"checked":"")?>>
							N&uacute;mero de cheque:
						</td>
						<td>
							entre <input type="text" name="t_cheque_desde" value="<?=$_POST["t_cheque_desde"]?>"> y <input type="text" name="t_cheque_hasta" value="<?=$_POST["t_cheque_hasta"]?>">
						</td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_sel_estado" <?=(($_POST["ch_sel_estado"])?"checked":"")?>>
							Estado imputaci&oacute;n:
						</td>
						<td>
							 <?g_draw_mix_select("sel_estado", (($_POST["sel_estado"])?$_POST["sel_estado"]:" "), $sels["estado"]);?>
						</td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_sel_tipo_cuenta" <?=(($_POST["ch_sel_tipo_cuenta"])?"checked":"")?>>
							Tipo de cuenta:
						</td>
						<td>
							 <?g_draw_mix_select("sel_tipo_cuenta", (($_POST["sel_tipo_cuenta"])?$_POST["sel_tipo_cuenta"]:" "), $sels["tipo_cuenta"]);?>
						</td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_sel_provincias" <?=(($_POST["ch_sel_provincias"])?"checked":"")?>>
							Distrito:
						</td>
						<td><?g_draw_mix_select("sel_provincias", (($_POST["sel_provincias"])?$_POST["sel_provincias"]:" "), $sels["provincias"]);?></td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_fecha" <?=(($_POST["ch_t_fecha"])?"checked":"")?>>
							Fecha de imputaci&oacute;n:
						</td>
						<td>
							entre&nbsp;
							<input type="text" name="t_fecha_desde" value="<?=$_POST["t_fecha_desde"]?>" >&nbsp;<?=link_calendario("t_fecha_desde")?>
						 	&nbsp;y &nbsp;
							<input type="text" name="t_fecha_hasta" value="<?=$_POST["t_fecha_hasta"]?>" >&nbsp;<?=link_calendario("t_fecha_hasta")?>
						</td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_sel_tipo_imputacion" <?=(($_POST["ch_sel_tipo_imputacion"])?"checked":"")?>>
							Tipo de inputaci&oacute;n:
						</td>
						<td><?g_draw_mix_select("sel_tipo_imputacion", (($_POST["sel_tipo_imputacion"])?$_POST["sel_tipo_imputacion"]:" "), $sels["tipo_imput"]);?></td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_usuario" <?=(($_POST["ch_t_usuario"])?"checked":"")?>>
							Usuario (realiz&oacute; la operaci&oacute;n):
						</td>
						<td><input type="text" name="t_usuario" value="<?=$_POST["t_usuario"]?>"></td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_monto" <?=(($_POST["ch_t_monto"])?"checked":"")?>>
							Monto:
						</td>
						<td>
							 mayor o igual que <input type="text" name="t_monto_desde" value="<?=$_POST["t_monto_desde"]?>" 
							 onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Monto de la imputación (desde)');"
							> y menor o igual que <input type="text" name="t_monto_hasta" value="<?=$_POST["t_monto_hasta"]?>"
				 			onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Monto de la imputación (hasta)');">
						</td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_importech" <?=(($_POST["ch_t_importech"])?"checked":"")?>>
							Importe del cheque:
						</td>
						<td>
							entre 
							<input type="text" name="t_importech_desde" value="<?=$_POST["t_importech_desde"]?>" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Importe del cheque (desde)');">
							&nbsp;y&nbsp;
							<input type="text" name="t_importech_hasta" value="<?=$_POST["t_importech_hasta"]?>" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Importe del cheque (hasta)');">
						</td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_fechadebito" <?=(($_POST["ch_t_fechadebito"])?"checked":"")?>>
							Fecha del d&eacute;bito:
						</td>
						<td>
							entre&nbsp;
							<input type="text" name="t_fechadebito_desde" value="<?=$_POST["t_fechadebito_desde"]?>" ><?=link_calendario("t_fechadebito_desde");?>
							&nbsp;y el d&iacute;a&nbsp;
							<input type="text" name="t_fechadebito_hasta" value="<?=$_POST["t_fechadebito_hasta"]?>" ><?=link_calendario("t_fechadebito_hasta");?>
						</td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_importedeb" <?=(($_POST["ch_t_importedeb"])?"checked":"")?>>
							Importe del d&eacute;bito:
						</td>
						<td>
							entre&nbsp;
							<input type="text" name="t_importedeb_desde" value="<?=$_POST["t_importedeb_desde"]?>" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Importe del débito (desde)');">
							&nbsp;y&nbsp;
							<input type="text" name="t_importedeb_hasta" value="<?=$_POST["t_importedeb_hasta"]?>" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Importe del débito (hasta)');">
						</td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_monto2" <?=(($_POST["ch_t_monto2"])?"checked":"")?>>
							Monto del ingreso/egreso: 
						</td>
						<td>
							entre&nbsp;
							<input type="text" name="t_monto2_desde" value="<?=$_POST["t_monto2_desde"]?>" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Monto del ingreso/egreso (desde)');">
							&nbsp;y&nbsp;
							<input type="text" name="t_monto2_hasta" value="<?=$_POST["t_monto2_hasta"]?>" onchange="this.value=this.value.replace(',','.'); return control_numero(this, 'Monto del ingreso/egreso (hasta)');">
						</td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_fecha_creacion" <?=(($_POST["ch_t_fecha_creacion"])?"checked":"")?>>
							Fecha del ingreso/egreso:
						</td>
						<td>
							entre el d&iacute;a&nbsp;
							<input type="text" name="t_fecha_creacion_desde" value="<?=$_POST["t_fecha_creacion_desde"]?>" ><?=link_calendario("t_fecha_creacion_desde")?>
							&nbsp;y el d&iacute;a&nbsp;
							<input type="text" name="t_fecha_creacion_hasta" value="<?=$_POST["t_fecha_creacion_hasta"]?>" ><?=link_calendario("t_fecha_creacion_hasta")?>
						</td>
					</tr>
					<tr>
						<td id="mo_sf">
							<input type="checkbox" name="ch_t_item" <?=(($_POST["ch_t_item"])?"checked":"")?>>
							Item: 
						</td>
						<td><input type="text" name="t_item" value="<?=$_POST["t_item"]?>"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<script>
		if (document.all.sel_periodo.checked) document.all.tabla_busqueda_avanzada.style.display='inline';
	</script>
	<table width="90%" align="center" bgcolor="<?=$bgcolor3?>">
		<tr id="ma">
			<td colspan="5" align="left">Registros listados: <?=$total_items?></td>
			<td colspan="2" align="right"><?=$link_pagina?></td>
		</tr>
		<tr id="mo">
			<td nowrap><a id=mo href='<?=encode_link("items_imputados.php",array("sort"=>"7","up"=>$up))?>'>Nro. Contador</a></td>
			<td nowrap><a id=mo href='<?=encode_link("items_imputados.php",array("sort"=>"1","up"=>$up))?>'>Cuenta</a></td>
			<td nowrap><a id=mo href='<?=encode_link("items_imputados.php",array("sort"=>"2","up"=>$up))?>'>Monto</a></td>
			<td><a id=mo href='<?=encode_link("items_imputados.php",array("sort"=>"3","up"=>$up))?>'>Tipo</a></td>
			<td><a id=mo href='<?=encode_link("items_imputados.php",array("sort"=>"4","up"=>$up))?>'>Estado</a></td>
			<td><a id=mo href='<?=encode_link("items_imputados.php",array("sort"=>"5","up"=>$up))?>'>Usuario</a></td>
			<td nowrap><a id=mo href='<?=encode_link("items_imputados.php",array("sort"=>"6","up"=>$up))?>'>Fecha</a></td>
		</tr>
	<?
		$rta_consulta=sql($sql, "c300") or fin_pagina();
		$i=0;
		while ($resultados[$i]=$rta_consulta->fetchRow()){
		?>
		<tr <?=atrib_tr()?>>
			<td><?=$resultados[$i]["numero_contador"]?></td>
			<td><?=$resultados[$i]["nombre_cuenta"]?></td>
			<td align="right">$ <?=formato_money($resultados[$i]["monto"])?></td>
			<td align="right"><?=$resultados[$i]["tipo_imput"]?></td>
			<td <?=((stristr($resultados[$i]["estado_imput"],"Por Controlar"))?"style='background-color:#ff2222'":"")?>><?=$resultados[$i]["estado_imput"]?></td>
			<td><?=$resultados[$i]["usuario"]?></td>
			<td><?=Fecha($resultados[$i]["fecha"])?></td>
		</tr>
		<?
			$i++;
		}
	?>
	</table>
</form>
<?
	fin_pagina();
?>