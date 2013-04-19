<?php
/*AUTOR: Gabriel
$Author: gabriel $
$Revision: 1.4 $
$Date: 2006/01/17 21:27:13 $
*/
	require("../../config.php");
	cargar_calendario();
	variables_form_busqueda("referencias", array());
	//////////////////////////////////////////////////////////////////////////////
	if ($cmd == ""){
		$cmd="actuales";
		$_ses_referencias["cmd"]=$cmd;
  	phpss_svars_set("_ses_referencias", $_ses_referencias);
	}
	$datos_barra = array(
		array("descripcion"=> "Actuales", "cmd"=> "actuales"),
		array("descripcion"=> "Historial", "cmd"=> "historial")
	);
	
	$orden = array(
		"default_up"=>"0",
		"default" => "6",
		"1"=>"clientes_referencia.id_licitacion",
		"2" => "clientes_referencia.entidad",
		"3" => "clientes_referencia.distrito",
		"4" => "clientes_referencia.direccion",
		"5" => "clientes_referencia.nro_licitacion",
		"6" => "clientes_referencia.fecha_apertura",
		"7" => "clientes_referencia.monto",
		"8" => "clientes_referencia.fecha_entrega",
		"9" => "clientes_referencia.contacto",
	);
	
	$filtro = array(
		"clientes_referencia.id_licitacion"=>"Id. licitación",
		"clientes_referencia.entidad"=>"Cliente/Entidad",
		"clientes_referencia.distrito"=>"Localidad (Distrito)",
		"clientes_referencia.direccion"=>"Dirección",
		"clientes_referencia.nro_licitacion"=>"Nro. de licitación",
		"clientes_referencia.fecha_apertura"=>"Fecha de apertura",
		"clientes_referencia.monto"=>"Monto",
		"clientes_referencia.fecha_entrega"=>"Fecha de entrega",
		"clientes_referencia.contacto"=>"Contacto",
		"clientes_referencia.detalle"=>"Detalle"
	);     
	$sql_tmp="select * from licitaciones.clientes_referencia";
	if ($cmd=="actuales")	$where_tmp.=" (tipo_lista='a')";
	else $where_tmp.=" (tipo_lista!='a')";

	if ($_POST["sel_periodo"]){
		$fecha_desde=$_POST["fecha_desde"];
		$fecha_hasta=$_POST["fecha_hasta"];
		if (($fecha_desde)&&($fecha_hasta)){
			$where_tmp=" and ((fecha_apertura>='".Fecha_db($fecha_desde)."')and(fecha_apertura<='".Fecha_db($fecha_hasta)."'))";
		}elseif ($fecha_desde){
			$where_tmp=" and (fecha_apertura>='".Fecha_db($fecha_desde)."')";
		}elseif ($fecha_hasta){
			$where_tmp=" and (fecha_apertura<='".Fecha_db($fecha_hasta)."')";
		}
	}
	
	if (($_POST["pasaje"])&&($_POST["ids"])){
		$ids=explode(",", substr($_POST["ids"],2));
		for ($i=0; $i<count($ids); $i++){
			$consulta="update licitaciones.clientes_referencia set tipo_lista='h' where id_cliente_referencia=".$ids[$i];
			sql($consulta, "c68") or fin_pagina();
		}
	}
	
	if($_POST['keyword'] || $keyword) $contar="buscar";
	//////////////////////////////////////////////////////////////////////////////
	echo($html_header);
?>
	<script>
		function hacer_lista(ctos){
			document.all.ids.value='';
			for (i=0; i<ctos; i++){
				obj=eval("document.all.ch_"+i);
				tobj=eval("document.all.t_"+i);
				if ((typeof(obj)!=undefined)&&(obj.checked)) document.all.ids.value=document.all.ids.value+', '+tobj.value;
			}
			return true;
		}
	</script>
	<form name="form_referencias" method="POST" action="clientes_referencia.php">
	<input type="hidden" name="ids" value="<?=$_POST["ids"]?>">
	<table cellspacing=2 cellpadding=2 border=0 bgcolor=<? echo $bgcolor3 ?> width=95% align=center>
		<tr>
			<td>
				<? generar_barra_nav($datos_barra);?>  
			</td>
		</tr>
		<tr>
			<td align=center>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="90%" align="center">
				<? list($sql, $total_leg, $link_pagina, $up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar"); ?>
				<input type="checkbox" name="sel_periodo" value="avanzado" <?=(($_POST["sel_periodo"])?" checked ":"")?> onclick="document.all.tabla_busqueda_avanzada.style.display=(this.checked)?'inline':'none';">&nbsp;Avanzado
				<input type=submit name=buscar value='Buscar'>
				</td><td align="right">
				<?
				$link8=encode_link("word_clientes_referencia.php", array("consulta"=>$sql,"formato"=>'new'));	
       ?>
       <A target='_blank' href='<?=$link8?>'><IMG src='<?=$html_root?>/imagenes/word.gif' height='16' width='16' border='0'></a>
       			</td>
       		</tr>
      	</table>
			</td>
		</tr>
		<tr>
			<td>
				<table border=0 bgcolor="<?=$bgcolor3?>" width=70% align="center" id="tabla_busqueda_avanzada" style="display:none">
					<tr>
						<td>
							<b>Fecha de apertura entre el día:&nbsp;</b>
							<input type=text name='fecha_desde' size=11 value='<?=$fecha_desde?>'>&nbsp;
							<? echo link_calendario('fecha_desde');?>
						</td>
						<td>
							<b>y el día:&nbsp;</b>
							<input type=text name='fecha_hasta' size=11 value='<?=$fecha_hasta?>'>&nbsp;
							<? echo link_calendario('fecha_hasta');?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<script>
		if (document.all.sel_periodo.checked) document.all.tabla_busqueda_avanzada.style.display='inline';
	</script>
	<table align="center" width="95%" cellpadding="1" cellspacing="0" border="1">
		<tr>
			<td colspan="10">
				<table width=100%>
					<tr id=ma>
						<td width=30% align=left><b>Total:</b> <?=$total_leg?> referencias.</td>
						<td width=70% align=right><?=$link_pagina?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id=mo>
			<td>&nbsp;</td>
 			<td width="10%" nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Id. Licitación</a></b></td>
 			<td width="10%" nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Entidad</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Distrito</a></b></td>
 			<td nowrap width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Dirección</a></b></td>
			<td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Nro. de licitación</a></b></td>
 			<td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Apertura</a></b></td>
 			<td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"7","up"=>$up))?>'>Monto</a></b></td>
 			<td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"8","up"=>$up))?>'>Fecha de entrega</a></b></td>
 			<td width="10%"><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"9","up"=>$up))?>'>Contacto</a></b></td>
		</tr>
		<?
			$result=sql($sql) or fin_pagina();
			$i=0;
			while ($fila=$result->fetchRow()){
				$ref = encode_link("cliente_referencia_editar.php",array_merge(array("modo"=>"modif", "pagina"=>"cliente_referencia_editar.php"), $fila));
				
		?>
		<tr <?=atrib_tr()?> title="<?=$fila["detalle"]?>">
			<input type="hidden" name="t_<?=$i?>" value="<?=$fila["id_cliente_referencia"]?>">
			<td width="5%">
			<?
				if ($cmd=="actuales") echo("<input type='checkbox' name='ch_$i'></td>");
				else echo("&nbsp;</td>");
			?>
			<a href="<?=$ref?>">
			<td><?=$fila["id_licitacion"]?>&nbsp;</td>
			<td><?=$fila["entidad"]?>&nbsp;</td>
			<td><?=$fila["distrito"]?>&nbsp;</td>
			<td><?=$fila["direccion"]?>&nbsp;</td>
			<td><?=$fila["nro_licitacion"]?>&nbsp;</td>
			<td><?=Fecha($fila["fecha_apertura"])?>&nbsp;</td>
			<td><?echo $fila["moneda"]." ".formato_money($fila["monto"])?></td>
			<td><?=Fecha($fila["fecha_entrega"])?>&nbsp;</td>
			<td><?=$fila["contacto"]?>&nbsp;</td>
			</a>
		</tr>
		<?
			$i++;
			}
?>
		<tr>
			<td colspan="10" align="center" bgcolor="<?=$bgcolor3?>">
				<input type="button" name="nueva_referencia" value="Nuevo cliente de referencia" onclick="document.location.href='<?=encode_link("cliente_referencia_editar.php",array("modo"=>"nuevo", "pagina"=>"cliente_referencia_editar.php"))?>'">
			<?if($cmd=="actuales"){?>
				<input type="submit" name="pasaje" value="Pasar a historial los ítems seleccionados" onclick="return hacer_lista(<?=(($i)?$i:0)?>);">
				<?}?>
			</td>
		</tr>
	</table>
</form>
<?
fin_pagina();
?>
