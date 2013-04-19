<?
/*Autor: Gabriel
MODIFICADO POR:
$Author: fernando $
$Revision: 1.8 $
$Date: 2007/03/01 18:52:26 $
*/

require("../../config.php");
cargar_calendario();



variables_form_busqueda("gtias_contrato", array());
//////////////////////////////////////////////////////////////////////////////
if ($cmd == ""){
		$cmd="pendientes";
		$_ses_gtias_contrato["cmd"]=$cmd;
    	phpss_svars_set("_ses_gtias_contrato", $_ses_gtias_contrato);
	}
	$datos_barra = array(
		array("descripcion"=> "Pendientes", "cmd"=> "pendientes"),
		array("descripcion"=> "Historial", "cmd"=> "historial")
	);
	$orden = array(
		"default_up"=>"0",
		"default" => "1",
		"1" => "licitacion.id_licitacion",
		"2" => "usuarios.apellido, usuarios.nombre",
		"3" => "licitacion.fecha_apertura",
		"4" => "entidad.nombre",
		"5" => "distrito.nombre",
		"6" => "licitacion.nro_lic_codificado"
	);

$filtro = array(        
  	"licitacion.id_licitacion" => "Nro. de licitación",
  	"entidad.nombre" => "Entidad",
  	"distrito.nombre" => "Distrito",
  	"licitacion.nro_lic_codificado" => "Nro. de licitación",
  	"usuarios.apellido||', '||usuarios.nombre" => "Líder",
  	"licitacion.fecha_apertura" => "Fecha de apertura"
	);     
	$sql_tmp="select licitacion.id_licitacion as id_lic, licitacion.fecha_apertura, entidad.nombre as nombre_entidad, 
 			  distrito.nombre as nombre_distrito, licitacion.nro_lic_codificado, usuarios.apellido||', '||usuarios.nombre as nombre_lider,
			  lic_gtia_contrato_vencimiento.*,
 			  case when (fecha_registro < CURRENT_DATE) then 1 
				when (fecha_registro >= CURRENT_DATE) then 0 
				else null end as plazo_vencido,
				lic_gtia_contrato_vencimiento.fecha_vencimiento_garantia,
				lic_gtia_contrato_vencimiento.tipo_garantia
		from licitaciones.licitacion 
			left join sistema.usuarios on(lider=id_usuario)
			left join licitaciones.entidad using(id_entidad)
			left join licitaciones.distrito using(id_distrito)
			left join licitaciones_datos_adicionales.garantia_contrato using(id_garantia_contrato)
			left join licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento using(id_licitacion)
			
			
			";
	if ($cmd=="pendientes") {
		$where_tmp.="((lic_gtia_contrato_vencimiento.estado_garantia='pr')or(lic_gtia_contrato_vencimiento.estado_garantia='np'))";
	}else{
		$where_tmp.="((lic_gtia_contrato_vencimiento.estado_garantia='r')or(lic_gtia_contrato_vencimiento.id_lic_gtia_contrato_vencimiento is null))";
	}

	

	if($_POST['keyword'] || $keyword) $contar="buscar";
	//////////////////////////////////////////////////////////////////////////////
	if ($_POST["guardar"]){
		if ($_POST["h_regs"]!=""){
           	if ($cmd=="pendientes"){
                $estado="r";
	   	    }else{
                $estado="pr";
            }
	$ids=explode(",", $_POST["h_regs"]);
	for ($i=0; $i<count($ids); $i++){
				$sql_up="update licitaciones_datos_adicionales.lic_gtia_contrato_vencimiento
					set estado_garantia='$estado'	where id_licitacion=".$ids[$i];
				sql($sql_up, "c67") or fin_pagina();
	}
    if ($cmd=="pendientes"){ 
			    $contenido ="Se recuperaron las garantías de contrato de las siguientes licitaciones:\n".$_POST["h_regs"];
			    $contenido.="\n\nCambios efectuados por: ".$_ses_user["name"]." (".date("d/m/Y H:m").")";
			    $asunto="Aviso de cambios en Garantías de Contratos";
			    enviar_mail("adrian@coradir.com.ar,arrom@coradir.com.ar,irungaray@coradir.com.ar", $asunto, $contenido, "", "", "", 0);
            }
		} //del if post
	}
	//////////////////////////////////////////////////////////////////////////////
	echo($html_header);
?>
<script>
function control_datos(datos){
	var ids=new Array();
	for (i=0, j=0; i<datos.length; i++){
		id=eval("document.all."+datos[i]);
		if ((id.value!='')&&(id.checked)){
			ids[j++]=id.value;
		}
	}
	document.all.h_regs.value=ids;
	return true;
}
</script>
<form name="form_adjuntos" method="POST" action="garantias_contrato.php">
	<input type="hidden" name="h_regs" value="<?=$_POST["h_regs"]?>">
	
	<table cellspacing=2 cellpadding=2 border=0 bgcolor=<? echo $bgcolor3 ?> width=95% align=center>
		<tr>
			<td>
				<?=generar_barra_nav($datos_barra)?>  
			</td>
		</tr>
		<tr>
			<td align=center>
				<? list($sql, $total_leg, $link_pagina, $up) = form_busqueda($sql_tmp,$orden,$filtro,$link_tmp,$where_tmp,"buscar"); ?>
				<input type=submit name=buscar value='Buscar'>
			</td>
		</tr>
	</table>
	<table align="center" width="90%" cellpadding="1" cellspacing="0" border="1">
		<tr>
			<td colspan="7">
				<table width=100%>
					<tr id=ma>
						<td width=30% align=left><b>Total:</b> <?=$total_leg?> ítems listados.</td>
						<td width=70% align=right><?=$link_pagina?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr id=mo>
 			<td width="10%" nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Id.</a></b></td>
 			<td width="10%" nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Líder</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Apertura</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Entidad</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"5","up"=>$up))?>'>Distrito</a></b></td>
 			<td nowrap><b><a href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"6","up"=>$up))?>'>Número</a></b></td>
 			<td>Gtía. recuperada</td>
		</tr>
		<?
			$result=sql($sql, "c116") or fin_pagina();
            $i=0;
            
            $fecha_mas_cinco = date("Y-m-d",mktime(0,0,0,date('m'),date('d')+5,date('Y')));
            $fecha_hoy = date("Y-m-d");
			while ($fila=$result->fetchRow()){
                if ($fila["tipo_garantia"]=="abierta" && ($fila["fecha_vencimiento_garantia"] <= $fecha_hoy)) $color="bgcolor='red'";								
                elseif ($fila["tipo_garantia"]=="abierta" && ($fila["fecha_vencimiento_garantia"] >= $fecha_hoy && $fila["fecha_vencimiento_garantia"] <= $fecha_mas_cinco)) $color="bgcolor='yellow'";			
				else $color="";
				
				$ref = encode_link("garantias_contrato_poliza.php", array("ID"=>$fila["id_lic"]));
		?>
		<tr <?=atrib_tr()?>>
		<a href="<?=$ref?>">
			<td <?=$color?>><?=$fila["id_lic"]?>&nbsp;</td>
			<td><?=$fila["nombre_lider"]?>&nbsp;</td>
			<td align="center"><?=Fecha($fila["fecha_apertura"])?>&nbsp;</td>
			<td align="left"><?=$fila["nombre_entidad"]?>&nbsp;</td>
			<td><?=$fila["nombre_distrito"]?>&nbsp;</td>
			<td><?=$fila["nro_lic_codificado"]?>&nbsp;</td>
		</a>
        <?
           //(($_POST["ch_".$i]==$fila["id_licitacion"])?$checked="checked ":$checked="");
           (($cmd=="pendientes" )?$disabled="":$disabled="disabled ");        
           if ($_ses_user["login"]=="adrian" || $_ses_user["login"]=="fernando")
                   $disabled="";
        ?>
			<td align="center"><input type="checkbox" name="ch_<?=$i?>" value="<?=$fila["id_licitacion"]?>" <?=$checked ?> <?=$disabled?>></td>
		</tr>
		<?
				$checks[$i]="\"ch_".$i."\"";
				$i++;
		}
		?>
 	<? if ($cmd=="pendientes") {?>
		<tr bgcolor="#b7c7d0">
			<td colspan="7" align="center">
				<input type="submit" id="guardar" name="guardar" value="Guardar cambios" onclick='if (typeof(document.all.ch_0)!=undefined) control_datos(new Array(<?=(($checks)?implode(", ", $checks):"")?>));'>
			</td>
		</tr>
	<? } else {?>
		<tr bgcolor="#b7c7d0">
			<td colspan="7" align="center">
				<input type="submit" id="guardar" name="guardar" value="Volver a Pendiente" onclick='if (typeof(document.all.ch_0)!=undefined) control_datos(new Array(<?=(($checks)?implode(", ", $checks):"")?>));'>
			</td>
		</tr>
	<? }?>    
	</table>
	<br>
	<table align="center" width="60%" cellpadding="1" cellspacing="0" border="1" bgcolor="White">
		<tr>
			<td width="10" height="10" bgcolor="Red">&nbsp;</td>
			<td>Garantía Vencida (Recuperar)</td>
		</tr>
		<tr>
			<td width="10" height="10" bgcolor="Yellow">&nbsp;</td>
			<td>Garantía Próxima a Vencer (5 Días)</td>
		</tr>
	</table>
</form>
<?
fin_pagina();
?>