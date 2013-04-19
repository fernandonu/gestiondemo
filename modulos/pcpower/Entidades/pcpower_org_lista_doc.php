<? 
/*
Autor: GACZ
Creado: viernes 11/06/04

MODIFICADA POR
$Author: broggi $
$Revision: 1.1 $
$Date: 2004/10/13 21:16:01 $
*/
require_once("../../../config.php");
$q ="select ";
$q.="e.nombre as e_nombre,e.direccion,e.localidad,e.codigo_postal,";
$q.="o.nombre as o_nombre,o.id_org,d.nombre as provincia ";
$q.="from ";
$q.="pcpower_organismos o join ";
$q.="pcpower_org_entidades oe using(id_org) join ";
$q.="pcpower_entidad e using(id_entidad) join ";
$q.="pcpower_distrito d using(id_distrito) ";
//$q.="group by id_org,id_entidad,e.nombre,e.direccion,e.localidad,e.codigo_postal,o.nombre";
//$q.="where id_org=1 ";
$q.="order by o.nombre,e.nombre";

$org=sql($q) or fin_pagina();

excel_header("Organismos y Entidades.doc");
?>
MIME-Version: 1.0
Content-Type: multipart/related; boundary="----=_NextPart_01C44FBE.20EE5F90"

Este documento es una página Web de un solo archivo, también conocido como archivo de almacenamiento Web. 
Si está viendo este mensaje, su explorador o editor no admite archivos de almacenamiento Web. Descargue un explorador que admita este tipo de archivos, como Microsoft Internet Explorer.

------=_NextPart_01C44FBE.20EE5F90
Content-Location: file:///C:/Organismos.htm
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset=iso-8859-1

<html xmlns:v=3D"urn:schemas-microsoft-com:vml" xmlns:o=3D"urn:schemas-microsoft-com:office:office" xmlns:w=3D"urn:schemas-microsoft-com:office:word" xmlns=3D"http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv=3DContent-Type content=3D"text/html; charset=3Diso-8859-1">
<meta name=3DProgId content=3DWord.Document>
<meta name=3DGenerator content=3D"Microsoft Word 11">
<meta name=3DOriginator content=3D"Microsoft Word 11">
<link rel=3DFile-List href=3D"Organismos_archivos/filelist.xml">
<title>Organismo: Administraci&oacute;n Federal de Ingresos P&uacute;blicos</title>
<!--[if gte mso 9]>
<xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:HyphenationZone>21</w:HyphenationZone>
  <w:ValidateAgainstSchemas/>
  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
 </w:WordDocument>
</xml>
<![endif]-->
<!--[if gte mso 9]>
<xml>
 <w:LatentStyles DefLockedState=3D"false" LatentStyleCount=3D"156">
 </w:LatentStyles>
</xml>
<![endif]-->
<style>
<!--
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:8.0pt;
	font-family:"Times New Roman";
	mso-fareast-font-family:"Times New Roman";}
p.MsoHeader, li.MsoHeader, div.MsoHeader
	{margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	tab-stops:center 212.6pt right 425.2pt;
	font-size:20.0pt;
	font-family:"Times New Roman";
	mso-fareast-font-family:"Times New Roman";}
p.MsoFooter, li.MsoFooter, div.MsoFooter
	{margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	tab-stops:center 212.6pt right 425.2pt;
	font-size:8.0pt;
	font-family:"Times New Roman";
	mso-fareast-font-family:"Times New Roman";}
p
	{mso-margin-top-alt:auto;
	margin-right:0cm;
	mso-margin-bottom-alt:auto;
	margin-left:0cm;
	mso-pagination:widow-orphan;
	font-size:8.0pt;
	font-family:"Times New Roman";
	mso-fareast-font-family:"Times New Roman";}
 /* Page Definitions */
 @page
	{mso-footnote-separator:url("header.htm") fs;
	mso-footnote-continuation-separator:url("header.htm") fcs;
	mso-endnote-separator:url("header.htm") es;
	mso-endnote-continuation-separator:url("header.htm") ecs;}
@page Section1
	{size:595.3pt 841.9pt;
	margin:70.85pt 2.0cm 70.85pt 63.0pt;
	mso-header-margin:35.4pt;
	mso-footer-margin:35.4pt;
	mso-header:url("header.htm") h1;
	mso-footer:url("header.htm") f1;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>
<!--[if gte mso 10]>
<style>
 /* Style Definitions */
 table.MsoNormalTable
	{mso-style-name:"Tabla normal";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-parent:"";
	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
	mso-para-margin:0cm;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	font-family:"Times New Roman";
	mso-ansi-language:#0400;
	mso-fareast-language:#0400;
	mso-bidi-language:#0400;}
</style>
<![endif]-->
</head>
<body lang=3DES style=3D'tab-interval:35.4pt'>
<div class=3DSection1>
<div align=3Dcenter>
<table class=3DMsoNormalTable border=3D1 cellspacing=3D0 cellpadding=3D0 width=3D655 style=3D'width:491.4pt;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt; mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh: .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
<? while (!$org->EOF) 
   { 
   	$id_org=$org->fields['id_org'];
   	$i=1;
?>
<tr style=3D'mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=3D655 colspan=3D5 valign=3Dtop style=3D'width:491.4pt;border:none; border-top:solid black 2.25pt;padding:0cm 5.4pt 0cm 5.4pt'>
	  <p style=3D'font-size:12.0pt;'>
		  <i style=3D'mso-bidi-font-style:normal;'>Organismo</i>: <b style=3D'mso-bidi-font-weight:normal'><?=$org->fields['o_nombre'] ?></b>
	  </p>
  </td>
</tr>
 <tr style=3D'mso-yfti-irow:1'>
  <td width=3D333 valign=3Dtop style=3D'width:250.0pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=3DMsoNormal align=3Dcenter style=3D'text-align:center'><i>Cliente</i></p>
  </td>
  <td width=3D147 valign=3Dtop style=3D'width:110.0pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=3DMsoNormal align=3Dcenter style=3D'text-align:center'><i>Domicilio</i></p>
  </td>
  <td width=3D27 valign=3Dtop style=3D'width:20.0pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=3DMsoNormal align=3Dcenter style=3D'text-align:center'><i>C.P.</i></p>
  </td>
  <td width=3D156 valign=3Dtop style=3D'width:116.65pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=3DMsoNormal align=3Dcenter style=3D'text-align:center'><i>Localidad</i></p>
  </td>
  <td width=3D53 valign=3Dtop style=3D'width:40.0pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=3DMsoNormal align=3Dcenter style=3D'text-align:center'><i>Provincia</i></p>
  </td>
 </tr>
<?
   	do 
   	{
?>

<!-- LINEA A REEMPLAZAR LOS DATOS -- OJO CON LA PROPIEDAD lastrow:yes en el <TR> -->
 <tr style=3D'mso-yfti-irow:<?=++$i ?><? if ($org->recordcount()==$i-3) echo ';mso-yfti-lastrow:yes'?>'>
  <td width=3D115 valign=3Dtop style=3D'border:none;mso-border-top-alt: solid silver .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
	  <p class=3DMsoNormal><b><?=$org->fields['e_nombre'] ?></b></p>
  </td>
  <td width=3D142 valign=3Dtop style=3D'border:none;mso-border-top-alt: solid silver .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
	  <p class=3DMsoNormal><?=$org->fields['direccion'] ?></p>
  </td>
  <td width=3D48 valign=3Dtop style=3D'border:none;mso-border-top-alt:  solid silver .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
	  <p class=3DMsoNormal><?="&nbsp;".$org->fields['codigo_postal'] ?></p>
  </td>
  <td width=3D156 valign=3Dtop style=3D'border:none;mso-border-top-alt: solid silver .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
	  <p class=3DMsoNormal><?="&nbsp;".$org->fields['localidad'] ?></p>
  </td>
  <td width=3D194 valign=3Dtop style=3D'border:none;mso-border-top-alt: solid silver .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
	  <p class=3DMsoNormal><?="&nbsp;".$org->fields['provincia'] ?></p>
  </td>
 </tr>
 
<?
	$org->MoveNext();
   	}
 	while (!$org->EOF && $org->fields['id_org']==$id_org);
   }
 	
?> 
 <!-- FIN  REEMPLAZAR DATOS -->
</table>
</div>
</div>
</body>
</html>
------=_NextPart_01C44FBE.20EE5F90
Content-Location: file:///C:/header.htm
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset=iso-8859-1

<html xmlns:v=3D"urn:schemas-microsoft-com:vml" xmlns:o=3D"urn:schemas-microsoft-com:office:office" xmlns:w=3D"urn:schemas-microsoft-com:office:word" xmlns=3D"http://www.w3.org/TR/REC-html40">
<!-- ENCABEZADO Y PIE DE PAGINA -->
<head>
<meta http-equiv=3DContent-Type content=3D"text/html; charset=3Diso-8859-1">
<meta name=3DProgId content=3DWord.Document>
<meta name=3DGenerator content=3D"Microsoft Word 11">
<meta name=3DOriginator content=3D"Microsoft Word 11">
<link id=3DMain-File rel=3DMain-File href=3D"../Organismos.htm">
</head>
<body lang=3DES>
<div style=3D'mso-element:footnote-separator' id=3Dfs>
	<p class=3DMsoNormal><span style=3D'mso-special-character:footnote-separator'>
	<![if !supportFootnotes]>
	<hr align=3Dleft size=3D1 width=3D"33%">
	<![endif]></span></p>
</div>
<div style=3D'mso-element:footnote-continuation-separator' id=3Dfcs>
	<p class=3DMsoNormal><span style=3D'mso-special-character:footnote-continuation-separator'>
	<![if !supportFootnotes]>
	<hr align=3Dleft size=3D1>
	<![endif]></span></p>
</div>
<div style=3D'mso-element:endnote-separator' id=3Des>
	<p class=3DMsoNormal><span style=3D'mso-special-character:footnote-separator'>
	<![if !supportFootnotes]>
	<hr align=3Dleft size=3D1 width=3D"33%">
	<![endif]></span></p>
</div>
<div style=3D'mso-element:endnote-continuation-separator' id=3Decs>
	<p class=3DMsoNormal><span style=3D'mso-special-character:footnote-continuation-separator'>
	<![if !supportFootnotes]>
	<hr align=3Dleft size=3D1>
	<![endif]></span></p>
</div>
<!-- ENCABEZADO -->
<div style=3D'mso-element:header' id=3Dh1>
<p class=3DMsoHeader align=3Dcenter style=3D'text-align:center'>
	<b style=3D'mso-bidi-font-weight:normal'>
		<span>Clientes: Listado de Organismos Oficiales</span>
	</b> 
</p>
</div>
<!-- PIE DE PAGINA -->
<div style=3D'mso-element:footer' id=3Df1>
<p class=3DMsoFooter align=3Dright style=3D'text-align:right'>
<span class=3DMsoPageNumber>P&aacute;gina 
<span style=3D'mso-field-code:" PAGE "'><span style=3D'mso-no-proof:yes'></span></span>&nbsp;de&nbsp;<span style=3D'mso-field-code:" NUMPAGES "'><span style=3D'mso-no-proof:yes'></span></span></span></p>
</div>
</body>
</html>

------=_NextPart_01C44FBE.20EE5F90--
