<?
/*
Autor: Gabriel
MODIFICADA POR
$Author: gabriel $
$Revision: 1.4 $
$Date: 2006/01/18 18:25:04 $
*/
require_once("../../config.php");
$nro_orden=$_POST['nro_orden'] or $nro_orden=$parametros['nro_orden'];

function enviar($nombre_archivo){
	global $buffer, $html_root;
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: must-revalidate");
	header("Content-Transfer-Encoding: binary");
	Header('Content-Type: application/dummy');
	Header('Content-Length: '.strlen($buffer));
	Header('Content-disposition: attachment; filename='.$nombre_archivo);

	echo <<<TAG1
From: <Guardado a mano>
Subject: 
Date: Thu, 22 Dec 2005 16:02:42 -0300
MIME-Version: 1.0
Content-Type: text/html;
	boundary="bound123"

--bound123
Content-Type: text/html;
	charset="iso-8859-1"

TAG1;
error_reporting(8);
	echo $buffer;

echo <<<END
--bound123--
END;

}

//consulta con la recolección de datos que irán en el "word"
$rta_consulta_general=sql($parametros["consulta"], "c56") or fin_pagina();

///////////////////////////////////////////////////////////////
$gag_color=$bgcolor3;
$buffer="
<html><head></head><body>
<table width='100%' border=1 cellpadding=3 cellspacing=0 bordercolor='black'>
<tr bgcolor='$gag_color'>
	<td align='center'><b><font size=7>LISTADO DE CLIENTES DE REFERENCIA</font></b></td>
</tr>";

	while ($fila=$rta_consulta_general->fetchRow()){
		$id_cliente_referencia=$fila["id_cliente_referencia"];
		$nro_licitacion=$fila["nro_licitacion"];
		$id_licitacion=$fila["id_licitacion"];
		$entidad=$fila["entidad"];
		$direccion=$fila["direccion"];
		$distrito=$fila["distrito"];
		$monto=$fila["monto"];
		$contacto=$fila["contacto"];
		$tel_contacto=$fila["tel_contacto"];
		$detalle=$fila["detalle"];
		$fecha_apertura=$fila["fecha_apertura"];
		$fecha_entrega=$fila["fecha_entrega"];
		$moneda=$fila["moneda"];

		$buffer.="
<tr>
	<td>
		<table border=\"1\" cellspacing=\"0\" width=\"100%\" align=\"center\">
			<tr>
				<td width=15% bgcolor=$gag_color>Entidad:</td>
				<td><b>$entidad</b></td>
			</tr>
			<tr>
				<td bgcolor=$gag_color>Distrito:</td>
				<td>$distrito</td>
			</tr>
			<tr>
				<td bgcolor=$gag_color>Dirección</td>
				<td>$direccion</td>
			</tr>
			<tr>
				<td bgcolor=$gag_color>Nro. de licitación:</td>
				<td>$nro_licitacion</td>
			</tr>
			<tr>
				<td bgcolor=$gag_color>Fecha de apertura:</td>
				<td align=\"left\">".Fecha($fecha_apertura)."</td>
			</tr>
			<tr>
				<td bgcolor=$gag_color>Contacto:</td>
				<td>$contacto</td>
			</tr>
			<tr>
				<td bgcolor=$gag_color>Teléfono:</td>
				<td>$tel_contacto</td>
			</tr>
			<tr>
				<td colspan=2>
					<table width='100%' border=0 cellspacing=0 cellpadding=0>
						<tr>
							<td width=15% bgcolor=$gag_color>Monto:</td>
							<td width=35% >$moneda ".formato_money($monto)."</td>
							<td width=15% bgcolor=$gag_color>Fecha de entrega:</td>
							<td width=35% >".Fecha($fecha_entrega)."</td>
						</tr>
					</table>
				</td>
			<tr>
				<td bgcolor=$gag_color>Detalle</td>
				<td>$detalle</td>
			</tr>
		</table>
		<br>
		</td></tr>";
	}
	$buffer.="
			</table>
		</tr>
	</td>
</tr>
</table>
</body></html>";
//$buffer=nl2br($buffer);
enviar("listado_clientes_de_referencia.doc");
?>