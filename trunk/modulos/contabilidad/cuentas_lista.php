<?php
/*
AUTOR: Gabriel
MODIFICADO POR:
$Author: gabriel $
$Revision: 1.5 $
$Date: 2005/10/27 22:09:04 $
*/
	require("../../config.php");
	
	variables_form_busqueda("cuentas", array());
	
	$orden = array(
		"default_up"=>"0",
		"default" => "1",
		"1" => "numero_contador",
		"2"=>"concepto",
		"3"=>"plan",
		"4"=>"nombre"
	);
	$filtro = array(        
  	"numero_contador" => "Nro. de cuenta",
		"concepto" => "Concepto",
		"plan" => "Plan",
		"nombre" => "Ubicación",
	);     
	$sql_tmp="select numero_cuenta, concepto, plan, nombre, id_distrito, numero_contador
		from general.tipo_cuenta
			left join licitaciones.distrito using(id_distrito)";
	$where_tmp="group by numero_cuenta, concepto, plan, nombre, id_distrito, numero_contador";
	
	echo($html_header);
	if($parametros['accion']!=""){ Aviso($parametros['accion']);}
?>
<form name="form_cuentas_lista" method="POST" action="cuentas_lista.php">
	<table width="90%" border="0" cellpadding="1" cellspacing="1" align="center"> 
		<tr>
			<td align="center" bgcolor="<?=$bgcolor3?>">
				<?
					$itemspp=75;
					list($sql, $total_leg, $link_pagina, $up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar"); 
				?>
				<input type=submit name=buscar value='Buscar'>
			</td>
		<tr>
			<td align="center">
				<table width="100%" bgcolor="<?=$bgcolor3?>">
					<tr>
						<td colspan="3">
							Total de cuentas: <?=$total_leg?>
						</td>
						<td align="right">
							<?=$link_pagina?>
						</td>
					</tr>
					<tr id="mo">
						<td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Nro. Cuenta</a></td>
						<td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Concepto</a></td>
						<td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Plan</a></td>
						<td><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Provincia</a></td>
					</tr>
					<?
						$rta_consulta=sql($sql, "C65") or fin_pagina();
						while ($fila=$rta_consulta->fetchRow()){
							$link=encode_link("cuentas_detalle.php",array("modo"=>"modif", "nro_cuenta"=>$fila["numero_cuenta"], 
								"concepto"=>$fila["concepto"], "plan"=>$fila["plan"], "id_distrito"=>$fila["id_distrito"], 
								"distrito"=>$fila["nombre"], "nro_contador"=>$fila["numero_contador"], "pagina"=>"cuentas_detalle.php"));
							tr_tag($link);
					?>
						<td>&nbsp;<?=$fila["numero_contador"]?></td>
						<td><?=$fila["concepto"]?></td>
						<td><?=$fila["plan"]?></td>
						<td><?=$fila["nombre"]?></td>
					</tr>
							<?
						}
					?>
				</table>
			</td>
		</tr>
		<tr>
			<td align="center" bgcolor="<?=$bgcolor3?>">
			<?if(permisos_check("inicio", "boton_cuentas_nueva_guardar")){?>
				<input type="button" name="nueva" value="Nueva Cuenta" onclick="document.location.href='<?=encode_link("cuentas_detalle.php",array("modo"=>"nuevo", "pagina"=>"cuentas_detalle.php"))?>'">
			<?}?>
			</td>
		</tr>
	</table>
</form>
<?
	fin_pagina(false);