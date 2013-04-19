<?
/*
MODIFICADA POR
$Author: fernando $
$Revision: 1.7 $
$Date: 2006/07/10 21:32:22 $
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
Content-Type: multipart/related;
	boundary="bound123"

--bound123
Content-Type: text/html;
	charset="iso-8859-1"

TAG1;
error_reporting(8);
	echo $buffer;
	echo <<<END

--bound123
Content-id: <logo_coradir>
Content-disposition: inline; filename=logo_coradir.jpg
Content-Type: image/jpeg
Content-Transfer-Encoding: base64


END;
/*$tempfile=ROOT_DIR."/imagenes/logo_coradir.jpg";
$handle = fopen($tempfile,'rb') or die("error en apertura de archivo");
$file_content = fread($handle,filesize($tempfile)) or die("error en lectura de archivo");
fclose($handle);
$encoded = chunk_split(base64_encode($file_content)) or die("error en codificación de archivo");
echo($encoded);*/

echo <<<END

--bound123--

END;

}

//consulta con la recolección de datos que irán en el "word"
$rta_consulta_general=sql("select op.nro_orden, op.id_licitacion, r.codigo_renglon, op.fecha_inicio, op.fecha_entrega, 
		ensamblador.nombre as ensamblador, entidad.nombre as cliente, op.lugar_entrega, op.desc_prod, op.cantidad, op.nserie_desde, 
		op.nserie_hasta, op.clave_root, op.comentario, op.titulo_etiqueta, so.descripcion as sisop, op.adicionales
	from ordenes.orden_de_produccion op
		left join licitaciones.entidad using (id_entidad)
		left join licitaciones.renglon r using (id_renglon)
		left join ordenes.ensamblador using (id_ensamblador)
		left join ordenes.sistema_operativo so using (id_sistema_operativo)
	where nro_orden=".$nro_orden, "c32") or fin_pagina();
$rta_consulta_productos=sql("select fop.descripcion as producto, tipos_prod.descripcion as tipo, fop.cantidad
	from ordenes.filas_ord_prod fop 
		left join general.producto_especifico using (id_prod_esp)
		left join general.tipos_prod using (id_tipo_prod)
	where nro_orden=".$nro_orden, "c37") or fin_pagina();
$rta_consulta_accesorios=sql("select id_accesorio,descripcion,esp1,tipo 
	from ordenes.accesorios 
	where nro_orden=".$nro_orden." order by tipo", "c40") or fin_pagina();

$fila_gral=$rta_consulta_general->fetchRow();
///////////////////////////////////////////////////////////////
$buffer="
<html><head>
<style>
body {font-family: Arial;font-size: 8pt;} 
</style>
</head><body style='font-family: Arial;font-size: 8pt;'>
<table width='100%' border=1 cellpadding=0 cellspacing=0 bordercolor='black'>
	<tr>
		<td><b>Orden de producción nro.:<font size=7> ".$fila_gral["nro_orden"]."</b></font></td>
		<td width='20%' align='center' nowrap><font size=6><b>CORADIR S.A.</b></font>";//<img src='cid:logo_coradir'>
if ($fila_gral["id_licitacion"]) {
	$sql="select usuarios.nombre, usuarios.apellido from usuarios left join licitacion on lider=id_usuario where id_licitacion=".$fila_gral["id_licitacion"];
	$lider=sql($sql,"Error en la consulta de lider") or fin_pagina();
}
$buffer.="</td>
	</tr>
	<tr bgcolor='$bgcolor3'>
		<td><b>Id. Licitación:<font size=5> ".$fila_gral["id_licitacion"]."</font></b></td>
		<td><b>Renglón:<font size=5> ".$fila_gral["codigo_renglon"]."</font></b></td>
	</tr>
</table>
<br>
<table width='100%' border=1 cellpadding=3 cellspacing=0 bordercolor='black'>
	<tr>
		<td><b>Fecha de inicio</b> ".Fecha($fila_gral["fecha_inicio"])."</td>
		<td><b>Fecha de entrega</b> ".Fecha($fila_gral["fecha_entrega"])."</td>
	</tr>
	<tr bgcolor='$bgcolor3'>";
	$buffer.="
		<td><b>Ensamblador:</b> ".$fila_gral["ensamblador"]."</td>
		<td><b>Lider:</b> ".$lider->fields["nombre"]." ".$lider->fields["apellido"]."</td>
	</tr>
	<tr>
		<td colspan=2><b>Cliente final:</b> ".nl2br($fila_gral["cliente"])."</td>
	</tr>
	<tr>
		<td colspan=2><b>Lugar de entrega:</b> ".nl2br($fila_gral["lugar_entrega"])."</td>
	</tr>
</table>
<br>
<table width='100%' border=1 cellpadding=1 cellspacing=0 bordercolor='black'>
	<tr bgcolor='$bgcolor3'>
		<td colspan=2><b>Producto:</b><font size='4'> ".nl2br($fila_gral["desc_prod"])."</font></td>
	</tr>
	<tr>
		<td colspan=2>
			<b>Etiqueta (título):</b> ".$fila_gral["titulo_etiqueta"]."<br>
			<b>Etiqueta (descripcion):</b> ".$fila_gral["descripcion"]."
	</tr>
	<tr>
		<td><b>Cantidad:</b><b><font size=7> ".$fila_gral["cantidad"]."</font></b></td>
		<td>Nros. de serie <b>".$fila_gral["nserie_desde"]." </b>al<b> ".$fila_gral["nserie_hasta"]."</b></td>
	</tr>
	<tr>
		<td colspan=2>
			<b>Sistema operativo instalado:</b> ".$fila_gral["sisop"]."<br>
			<b>Contraseña de root:</b> ".$fila_gral["clave_root"]."
		</td>
	</tr>
</table>
<br>
<table width='100%' border=1 cellpadding=0 cellspacing=0 bordercolor='black'>
	<tr bgcolor='$bgcolor2'>
		<td colspan=2 align='center'><b>Descripción del producto</b></td>
	</tr>
	<tr>
		<td colspan=2>
			<table border=0 width='100%'>";
	while ($fila=$rta_consulta_productos->fetchRow()){
		if ((stripos($fila["tipo"], "garant")===false)&&(stripos($fila["producto"], "garant")===false)){
			$prod_aux=split(" ",$fila['producto']);
			if($prod_aux[0]=='Monitor')
			{
			$ult=1;
			$canti_mon=$fila["cantidad"];
			$monit=$fila["producto"];
			//$buffer.="<tr><td width='10%'>".$fila["cantidad"]."</td><td><b>".$fila["producto"]."</b></td></tr>";
			}
			else {
				$buffer.="<tr><td width='10%'>".$fila["cantidad"]."</td><td>".$fila["producto"]."</td></tr>";
			}
		}else $gtia="Garantía: ".$fila["producto"];
	}
	if($ult==1)
	$buffer.="<tr><td width='10%'>".$canti_mon."</td><td><b>".$monit."</b></td></tr>";
	$buffer.="
			</table>
		</tr>
	</td>
	<tr>
		<td>
		<b>KB</b>&nbsp;".$rta_consulta_accesorios->fields["descripcion"]."&nbsp;&nbsp;&nbsp;";
		$rta_consulta_accesorios->moveNext();
		$buffer.="<b>Mouse</b>&nbsp;".$rta_consulta_accesorios->fields["descripcion"]."&nbsp;&nbsp;&nbsp;";
		$rta_consulta_accesorios->moveNext();
		$buffer.="<b>Parlantes</b>&nbsp;".$rta_consulta_accesorios->fields["descripcion"]."&nbsp;&nbsp;&nbsp;";
		$rta_consulta_accesorios->moveNext();
		if($rta_consulta_accesorios->fields["descripcion"]!="")
		{
		$buffer.="<b>Micrófono</b>&nbsp;".$rta_consulta_accesorios->fields["descripcion"]."&nbsp;&nbsp;&nbsp;";
		}
		$rta_consulta_accesorios->moveNext();
		if($rta_consulta_accesorios->fields["descripcion"]!="")
		{
		$buffer.="<b>FDD</b>&nbsp;".$rta_consulta_accesorios->fields["descripcion"]."&nbsp;";
		}
		$rta_consulta_accesorios->moveNext();
		if($rta_consulta_accesorios->fields["descripcion"]!="")
		{
		$buffer.="<b>Etiquetas Windows</b>&nbsp;".$rta_consulta_accesorios->fields["descripcion"]."&nbsp;";
		}		
			
	
		$buffer.="
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='2'><b>$gtia</b></td>
	</tr>
</table>
<table width='100%' border=1 cellpadding=0 cellspacing=0 bordercolor='black'>
	<tr bgcolor='$bgcolor2'>
		<td colspan=2 align='center'><b>Se deberá colocar a cada computadora armada</b></td>
	</tr>
	<tr>
		<td><small>
			Faja de garantía VOID,
			Product Key Sistema Operativo<br>
			Etiqueta Nº de serie,
			Caja de embalaje CDR con cinta de embalaje CDR<br>
			Etiqueta de características y Nº de Serie del equipo, exterior.
		</small></td>
		<td><small>
			Bolsa de accesorios:&nbsp;
				Mouse y Pad<br>
				Manuales, Drivers de Motherboard y adicionales,
				Hoja de Garantía CDR Computers<br>
				Cable de alimentación 220V,
				Manuales y Adicionales<br>
		</small></td>
	</tr>
</table>

<table width='100%' border=1 cellpadding=0 cellspacing=0 bordercolor='black'>
	<tr bgcolor='$bgcolor2'>
		<td colspan=2 align='center'><b>Adicionales</b></td>
	</tr>
	<tr>
		<td>
		   " . str_replace(chr(13),'<br>',$fila_gral["adicionales"]) . "
		</td>
	</tr>
</table>

</body></html>

";
//$buffer=nl2br($buffer);
enviar("orden_de_produccion_".$nro_orden.".doc");
?>