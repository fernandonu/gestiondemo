<?
/*
Autor: GACZ
Creado: miercoles 07/07/04

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.2 $
$Date: 2004/07/23 18:14:18 $
*/

require_once("../../config.php");

extract($_POST,EXTR_SKIP);
if ($parametros)
 extract($parametros,EXTR_OVERWRITE);
 
function get_comment($text,$row,$col,$visible=false)
{
	if ($text=="")
	 return $text;
	$visible=($visible)?"visible":"hidden";
	$buffer="
	<div style=3D'mso-element:comment'>
	<div>
	<!--[if gte mso 9]>
		<xml>
		 <v:shape id=3D'cdr_id_{ID}' type=3D'#_x0000_t202' style=3D'position:absolute;
		  margin-left:141.75pt; margin-top:-147.75pt; width:96pt; height:96pt; 
		  z-index:2; visibility:{VISIBILITY}' fillcolor=3D'infoBackground [80]' 
		  o:insetmode=3D'auto'>
		  <v:fill color2=3D'infoBackground [80]'/>
		  <v:shadow on=3D't' color=3D'black' obscured=3D't'/>
		  <v:path o:connecttype=3D'none'/>
		  <v:textbox style=3D'mso-direction-alt:auto'/>
		  <x:ClientData ObjectType=3D'Note'>
		    <x:MoveWithCells/>
		    <x:SizeWithCells/>
		    <x:AutoFill>False</x:AutoFill>
		    <x:Row>{ROW}</x:Row>
		    <x:Column>{COL}</x:Column>
		    <x:Author></x:Author>
		  </x:ClientData>
		 </v:shape>
		</xml>
	<![endif]-->
		<div v:shape=3D'cdr_id_{ID}' style=3D'padding:.75pt 0pt 0pt .75pt;text-align:left' class=3Dshape>
 		{TEXT}
		</div>
	</div>
	</div>";
	$buffer=str_replace("{ROW}",$row,$buffer);
	$buffer=str_replace("{COL}",$col,$buffer);
	$buffer=str_replace("{TEXT}",$text,$buffer);
	$buffer=str_replace("{VISIBILITY}",$visible,$buffer);
	$buffer=str_replace("{ID}",date("s").$col.$row,$buffer);//genera el id automatico
	return $buffer;
}

?>
MIME-Version: 1.0
X-Document-Type: Worksheet
Content-Type: multipart/related; boundary="----=_NextPart_01C46E64.BD02D180"

Este documento es una página Web de un solo archivo, también conocido como archivo de almacenamiento Web. Si está viendo este mensaje, su explorador o editor no admite archivos de almacenamiento Web. Descargue un explorador que admita este tipo de archivos, como Microsoft Internet Explorer.

------=_NextPart_01C46E64.BD02D180
Content-Location: file:///C:/Res_lic_xls.htm
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset="iso-8859-1"

<html xmlns:v=3D"urn:schemas-microsoft-com:vml"
xmlns:o=3D"urn:schemas-microsoft-com:office:office"
xmlns:x=3D"urn:schemas-microsoft-com:office:excel"
xmlns=3D"http://www.w3.org/TR/REC-html40">
<head>
<style type=3D"text/css">
<!--
<? require('../../lib/estilos.css') ?>
-->
</style>
<title>Resultados de Licitaciones</title>
<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Diso-8859-1">
</head>
<body bgcolor=3D#E0E0E0 text=3D"#000000" topmargin=3D"0" >
<?
$id_licitacion=$keyword;
$sql="select entidad.nombre as nombre_ent,licitacion.id_licitacion,distrito.nombre  from (licitacion join entidad on entidad.id_entidad=licitacion.id_entidad) join distrito on distrito.id_distrito=entidad.id_distrito  where id_licitacion=$id_licitacion";
$resultado=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
$entidad=$resultado->fields['nombre_ent'];
$distrito=$resultado->fields['nombre'];

$sql="select distinct competidores.nombre,competidores.id_competidor from renglon join oferta on renglon.id_licitacion=$keyword and renglon.id_renglon=oferta.id_renglon join competidores on oferta.id_competidor=competidores.id_competidor";
$resultado=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);

?>
<br>
 <table cellspacing=3D0 border=3D1 bordercolor=3D#000000>
  <tr>    
  <td id=3Dmo colspan=3D<?= $resultado->rowcount()+2 ?>  align=3Dleft bordercolor=3D#000000>
      &nbsp;Resultados Licitacion N&ordm; &nbsp;<?=$id_licitacion ?>
      &nbsp; - &nbsp; Entidad: &nbsp;<?=$entidad ?>
      &nbsp;&nbsp; - &nbsp; Disrito: &nbsp;<?=$distrito ?>
      </td>
    </tr>
   <tr id=3Dma>
   <td width=3D"2%">Renglon</td>
   <td width=3D"2%">Cantidad</td>
<? 
 $i=0;
 while (!$resultado->EOF)
 {
?>
 <td align=3Dcenter width=3D10%> 
<?= $resultado->fields['nombre'] ?>
 </td>
<?
 $i++;
 $resultado->MoveNext();
 }
?>
</tr>
<?
$i=0;
$sql="select * from renglon where id_licitacion=$keyword order by codigo_renglon asc";
$resultado_ren=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
$fila=2;
 while (!$resultado_ren->EOF)
 {
?>
 <tr class=3Dtd >
 <td align=3Dcenter >
 <b><?= $resultado_ren->fields['codigo_renglon']; ?></b>
 </td>
 <td align=3Dcenter>
 <b> <?= $resultado_ren->fields['cantidad']; ?></b>
 </td>
<?
$comments[]=get_comment($resultado_ren->fields['titulo'],$fila,0);
$resultado->Move(0);
$columna=2;
while (!$resultado->EOF)
{
 $sql="select oferta.*,moneda.simbolo,moneda.id_moneda from (oferta join moneda on moneda.id_moneda=oferta.id_moneda) where id_renglon=".$resultado_ren->fields['id_renglon']." and id_competidor=".$resultado->fields['id_competidor'];
 $resultado_aux=$db->Execute($sql)  or die ($db->ErrorMsg()."<br>".$sql);
?>
 <td <?=str_replace("style=","style=3D",excel_style((($resultado_aux->fields['simbolo']!="")?$resultado_aux->fields['simbolo']:'$'))) ?>  >
 <b>
 <?
 if ($valor_dolar=='0')
 	$valor_dolar=1;
  echo (($resultado_aux->fields['monto_unitario']!=0)?number_format($resultado_aux->fields['monto_unitario'],2,",","."):0);
?>  
 </td>
<?
$comments[]=get_comment($resultado_aux->fields['observaciones'],$fila,$columna);
$columna++;
$resultado->MoveNext();
}
?>
 </tr>
<?
 $resultado_ren->MoveNext();
 $fila++;
 }
?>
</table>
<!-- para evitar que se extiendan los colores del tr -->
<table><tr><td>&nbsp</td></tr></table>
<? 
echo "<div style=3D'mso-element:comment-list'><![if !supportAnnotations]>
	  <hr class=3Dmsocomhide align=3Dleft size=3D1 width=3D33%>
	  <![endif]>";
foreach ($comments as $value)
	echo $value."\n";
echo "</div>";
?>
</body>
</html>
------=_NextPart_01C46E64.BD02D180--