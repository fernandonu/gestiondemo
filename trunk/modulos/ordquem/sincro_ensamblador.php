<?PHP
/*AUTOR: MAD
               1 julio 2004
$Author: marcelo $
$Revision: 1.7 $
$Date: 2004/08/09 21:14:39 $
*/
/*
Este Script sirve para colectar los datos no sincronizados y enviarlos hacia el ensamblador
*/
require_once("../../config.php");

echo $html_header;
	
if(!isset($_GET["id"])){
	echo "Error: Parametro incorrecto en la entrada...";
	exit();
}

	//obtener el paso hacia el ensamblador
	$sql_path = "Select http from ensamblador where id_ensamblador = ".$_GET["id"];
	$result_path = $db->Execute($sql_path) or die($db->ErrorMsg()."<br>$sql_path");


	if($result_path->fields["http"] == '') {
	echo "<b>Este ensamblador no tiene configurado el servidor de quemado</b>";
	exit();
	}

	$path = $result_path->fields["http"].'/sincro_reply.php';
	//direccion de retorno
	$aux = $_SERVER['PHP_SELF'];
	$aux = str_replace('sincro_ensamblador','sincro_reply_reply',$aux);

	if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == 'on')) {
		$protocolo = 'https://';
	} else {
		$protocolo = 'http://';
	}	
	
	$gestion = $protocolo.$_SERVER['HTTP_HOST'].$aux;
	
	//obtener los datos no sincronizados aun 
	$sql="select nro_orden,fecha_orden,fecha_ini_quemado,fecha_fin_quemado,maq_quemadas,cantidad,ensamblador.nombre,config_quemado.id_config, config_quemado.duracion, config_quemado.data,orden_quemado.estado";
	$sql= $sql." from ordenes.orden_quemado join ordenes.orden_de_produccion using (nro_orden) join ordenes.ensamblador using (id_ensamblador) join ordenes.config_quemado using (id_config)";
	$sql= $sql." where sinc = 0 and orden_quemado.estado > 0 and id_ensamblador = ".$_GET["id"];

	$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
	$ensamblador = $result->Fields("nombre");
	$i=0;
	while (!$result->EOF) {
		$send[$i]["nro_orden"] = $result->Fields("nro_orden");
		$send[$i]["fecha_orden"] = $result->Fields("fecha_orden");
		$send[$i]["fecha_ini_quemado"] = $result->Fields("fecha_ini_quemado");
		$send[$i]["fecha_fin_quemado"] = $result->Fields("fecha_fin_quemado");
		$send[$i]["maq_quemadas"] = $result->Fields("maq_quemadas");
		$send[$i]["cantidad"] = $result->Fields("cantidad");
		$send[$i]["id_config"] = $result->Fields("id_config");
		$send[$i]["duracion"] = $result->Fields("duracion");
		$send[$i]["data"] = $result->Fields("data");
		$send[$i]["estado"] = $result->Fields("estado");
		
		$i++;
		$result->MoveNext();
	}	
?>
<FORM id="sincro" action="<?=$path?>" method="POST">

<TABLE width="90%" align="center" class="bordes">
	<TR id="mo">
		<TD>
		<h3>Ensamblador: <?=$ensamblador?></H3>
		</TD>
	</TR>
	<TR id="ma">
		<TD>
		<b>Se actualizarán <?=$result->RecordCount()?> Ordenes en la base de datos del ensamblador.</B>
		</TD>
	</TR>
	<TR id="mo">
		<TD>
		<INPUT type="button" name="cerrar" value="Cancelar" onclick="window.close()" tabindex="1">
		<INPUT type="submit" name="continuar" value="Continuar >>" style= "font : 'bold'" tabindex="0">		
		<INPUT type="hidden" name="datos" value="<?=str_replace('"','#',serialize($send))?>">
		<INPUT type="hidden" name="ret" value="<?=$gestion?>">
		<INPUT type="hidden" name="source" value="<?=$path?>">
		<? $j=0;
			while($j < $i) {?>
			<INPUT type="hidden" name="config_files_<?=$j?>" value="<?=base64_encode($file_conf[$j])?>">
		<? $j++;
		}?>
		</TD>
	</TR>
	<TR>
	<TD align="left">
	[Paso 1]
	</TD>
	</TR>
</TABLE>

</FORM>
