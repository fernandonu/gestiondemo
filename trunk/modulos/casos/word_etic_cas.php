<?
/*
Autor: Gabriel
MODIFICADA POR
$Author: ferni $
$Revision: 1.1 $
$Date: 2006/12/05 17:40:30 $
*/
require_once("../../config.php");

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

///////////////////////////////////////////////////////////////
$gag_color=$bgcolor3;
$buffer="
<html><head></head><body>
<table width='95%' border=1 cellpadding=3 cellspacing=0 bordercolor='black'>
<tr bgcolor='$gag_color'>
	<td align='center'><b><font size=7>D E S T I N O</font></b></td>
</tr>";


		$cas=$parametros["cas"];
		$dir=$parametros["dir"];
		$ciu=$parametros["ciu"];
		$prov=$parametros["prov"];
		$query="select nombre from distrito where id_distrito=$prov";
      	$resultado_provincia = sql($query,'consulta de provincia') or fin_pagina();
      	$prov=$resultado_provincia->fields['nombre'];      	
		$cp=$parametros["cp"];
		$contacto=$parametros["contacto"];
		$tel=$parametros["tel"];
		$formato=$parametros["formato"];				

		$buffer.="
<tr>
	<td align='center'><b><font size=5>$cas</font></b></td>				
</tr>
<tr>
	<td>
		<table border=\"1\" cellspacing=\"0\" width=\"100%\" align=\"center\">			
			
			
			<tr>
				<td width=20% bgcolor=$gag_color><b><font size=5>Domicilio</font></b></td>
				<td><b><font size=5>$dir</b></td>
			</tr>
			<tr>
				<td bgcolor=$gag_color><b><font size=5>Ciudad</font></b></td>
				<td><font size=5>$ciu</font></td>
			</tr>
			<tr>
				<td bgcolor=$gag_color><b><font size=5>Provincia</font></b></td>
				<td><b><font size=5>$prov</font></b></td>
			</tr>
			<tr>
				<td bgcolor=$gag_color><b><font size=5>C.P.</font></b></td>
				<td><font size=5>$cp</font></td>
			</tr>			
			<tr>
				<td bgcolor=$gag_color><b><font size=5>Contacto</font></b></td>
				<td><font size=5>$contacto</font></td>
			</tr>
			<tr>
				<td bgcolor=$gag_color><b><font size=5>Teléfono</font></b></td>
				<td><font size=5>$tel</font></td>
			</tr>	
			<tr>
				<td bgcolor=$gag_color><b><font size=5>$formato</font></b></td>
				<td>&nbsp</td>
			</tr>	
			<tr>
				<td bgcolor=$gag_color colspan=2 align=center><b><font size=5>A DOMICILIO</font></b></td>				
			</tr>	
			
		</table>
		<br>
		</td></tr>";
	
	$buffer.="
			</table>
		</tr>
	</td>
</tr>

</table>
</body></html>";
//$buffer=nl2br($buffer);
enviar("Etiqueta_CAS.doc");
?>