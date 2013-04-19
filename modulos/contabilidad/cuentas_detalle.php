<?php
/*
AUTOR: Gabriel
MODIFICADO POR:
$Author: gabriel $
$Revision: 1.4 $
$Date: 2005/10/27 22:08:57 $
*/
	require("../../config.php");
	require("../personal/gutils.php");
		
	$nro_cuenta=$parametros["nro_cuenta"] or $nro_cuenta=$_POST["h_nro_cuenta"];
	
	if ($parametros["modo"]=="nuevo"){
		$consulta="select max(numero_cuenta)+1 as nextval from general.tipo_cuenta";
		$rta_consulta=sql($consulta, "c20") or fin_pagina();
		$nro_cuenta=$rta_consulta->fields["nextval"];
		$nro_contador=$concepto=$plan="";
		$id_distrito=-1;
		$distrito=" ";
	}else{
		$concepto=$parametros["concepto"] or $concepto=$_POST["t_concepto"];
		$plan=$parametros["plan"] or $plan=$_POST["t_plan"];
		$id_distrito=$parametros["id_distrito"] or $id_distrito=$_POST["sel_pcia"];
		$distrito=$parametros["distrito"] or $distrito=$_POST["h_distrito"];
		$nro_contador=$parametros["nro_contador"] or $nro_contador=$_POST["t_nro_contador"];
		$nro_cuenta=$parametros["nro_cuenta"] or $nro_cuenta=$_POST["h_nro_cuenta"];
	}
	
	if ($_POST["guardar"]){
		$consulta="select * from general.tipo_cuenta where numero_cuenta=".$nro_cuenta;
		$rta_consulta=sql($consulta, "c32") or fin_pagina();
		if ($rta_consulta->recordCount()==0){//insert
			$consulta="insert into general.tipo_cuenta(numero_cuenta, numero_contador, concepto, plan, id_distrito)values ";
			$consulta.="(".$nro_cuenta.", ".(($nro_contador)?"'".$nro_contador."'":"null").", '".$concepto."', '".$plan."', ".(($id_distrito!=-1)?$id_distrito:"null").")";
			sql($consulta, "c36")or fin_pagina();
		}else{//update
			$consulta="update general.tipo_cuenta set ";
			$consulta.="numero_contador=".(($nro_contador)?"'".$nro_contador."'":"null").", ";
			$consulta.="concepto='".$concepto."', ";
			$consulta.="plan='".$plan."'";
			if ($id_distrito!=-1)	$consulta.=", id_distrito=".$id_distrito;
			$consulta.=" where numero_cuenta=".$nro_cuenta;
			sql($consulta, "c44")or fin_pagina();
		}
	}
	
	echo($html_header);
?>
<form name="form_cuentas_detalle" method="POST" action="cuentas_detalle.php">
	<input type="hidden" name="h_nro_cuenta" value="<?=(($nro_cuenta)?$nro_cuenta:$_POST["h_nro_cuenta"])?>">
	<input type="hidden" name="h_distrito" value="<?=(($distrito)?$distrito:$_POST["h_distrito"])?>">
	<input type="hidden" name="h_modo" value="<?=(($modo)?$modo:$_POST["h_modo"])?>">
	<table width="90%" align="center" bgcolor="<?=$bgcolor3?>">
		<tr>
			<td align="center">
				<table width="100%">
					<tr>
						<td id="mo" colspan="2"><h2>Datos de la cuenta nro.: <?=(($nro_contador)?$nro_contador:"?")?></h2></td>
					</tr>
					<tr>
						<td id="mo"><b>Nro. Contador:</b> </td>
						<td><input type="text" name="t_nro_contador" value="<?=(($_POST["t_nro_contador"])?$_POST["t_nro_contador"]:$nro_contador)?>" style="width:'100%'"></td>
					</tr>
					<tr>
						<td id="mo"><b>Concepto:</b> </td>
						<td><input type="text" name="t_concepto" value="<?=(($_POST["t_concepto"])?$_POST["t_concepto"]:$concepto)?>" style="width:'100%'"></td>
					</tr>
					<tr>
						<td id="mo"><b>Plan:</b> </td>
						<td><input type="text" name="t_plan" value="<?=(($_POST["t_plan"])?$_POST["t_plan"]:$plan)?>" style="width:'100%'"></td>
					</tr>
					<tr>
						<td id="mo"><b>Distrito (solo si corresponde):</b> </td>
						<td>
							<?
								$consulta="select id_distrito, nombre from licitaciones.distrito";
								$rta_consulta=sql($consulta, "c40") or fin_pagina();
								$nombre_pcias=array();
								$id_pcias=array();
								$i=0;
								while ($pcias[$i]=$rta_consulta->fetchRow()){
									$nombre_pcias[$i]=$pcias[$i]["nombre"];
									$id_pcias[$i]=$pcias[$i]["id_distrito"];
									$i++;
								}
								$nombre_pcias[$i]=" ";
								$id_pcias[$i]=-1;
								g_draw_value_select("sel_pcia", (($distrito)?$distrito:" "), $id_pcias, $nombre_pcias, 1, "onchange='document.all.h_distrito.value=this.options[this.selectedIndex].text;'");
							?>
						</td>
					</tr>
					<tr>
						<td colspan="2"><hr></td>
					</tr>
					<tr>
						<td colspan="2" align="center">
						<?if(permisos_check("inicio", "boton_cuentas_nueva_guardar")){?>
							<input type="submit" name="guardar" value="Guardar cambios">
						<?}?>
							<input type="button" name="volver" value="Volver" onclick="document.location='cuentas_lista.php';">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
<?
	fin_pagina(false);