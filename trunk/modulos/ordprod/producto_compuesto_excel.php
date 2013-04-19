<?php
/*
Autor: Quique
Creado: 01/06/2004

$Author: 
$Revision: 
$Date: 2006/05/30 16:01:59 $
*/

require_once("../../config.php");
$void=$parametros['void'];
$void1=$parametros['void1'];
$serie=$parametros['serie'];
$serie1=$parametros['serie1'];
$cuantos=$parametros['cuantos'] or $cuantos=$_POST['cuantos'];
$tipo=$parametros['tipo'] or $tipo=$_POST['tipo'];

if($tipo!=3)
{
 function excel_header_xml($crear) {
echo "
<?xml version='1.0'?>
<?mso-application progid='Excel.Sheet'?>
<Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet'
 xmlns:o='urn:schemas-microsoft-com:office:office'
 xmlns:x='urn:schemas-microsoft-com:office:excel'
 xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet'
 xmlns:html='http://www.w3.org/TR/REC-html40'>
 <ExcelWorkbook xmlns='urn:schemas-microsoft-com:office:excel'>
  <WindowTopX>0</WindowTopX>
  <WindowTopY>0</WindowTopY>
  <ProtectStructure>True</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
 <Style ss:ID='s22'>
   <Interior ss:Color='#339966' ss:Pattern='Solid'/>
  </Style>
  <Style ss:ID='s23'>
   <Font ss:Color='#0000FF'/>
  </Style>
  <Style ss:ID='s24'>
   <Interior ss:Color='#99CCFF' ss:Pattern='Solid'/>
  </Style>
  </Styles>
  <Worksheet ss:Name='Hoja1'>
  <Table ss:ExpandedColumnCount='12' x:FullColumns='1'
   x:FullRows='1' ss:DefaultColumnWidth='60'>
  <Column ss:AutoFitWidth='0' ss:Width='50.25'/>
   <Column ss:AutoFitWidth='0' ss:Width='102'/>
   <Column ss:AutoFitWidth='0' ss:Width='63.75'/>
   <Row>
    <Cell ss:StyleID='s22'><Data ss:Type='String'>PC</Data></Cell>
    <Cell ss:StyleID='s22'><Data ss:Type='String'>Nro Serie</Data></Cell>";
$tt=0;
while($crear>=$tt)
{
echo"<Cell ss:StyleID='s22'><Data ss:Type='String'>Codigo Barra</Data></Cell>";
$tt++;
}
echo"</Row>";
}

function excel_footer_xml() {
echo "</Table>
  <WorksheetOptions xmlns='urn:schemas-microsoft-com:office:excel'>
   <PageSetup>
    <Layout x:Orientation='Landscape'/>
    <Header x:Margin='0'/>
    <Footer x:Margin='0'/>
    <PageMargins x:Bottom='0.984251969' x:Left='0.78740157499999996'
     x:Right='0.78740157499999996' x:Top='0.984251969'/>
   </PageSetup>
   <Print>
    <ValidPrinterInfo/>
    <PaperSizeIndex>9</PaperSizeIndex>
    <HorizontalResolution>600</HorizontalResolution>
    <VerticalResolution>600</VerticalResolution>
   </Print>
   <Selected/>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>";
echo "</Worksheet> </Workbook>";
}

function excel_row($arreglo,$i,$crear,$arreglo_cant,$arreglo_tit,$arreglo_num,$pintar) {
 $id=$arreglo[$i];	
 $num=$arreglo_num[$i];	
 $cant=$arreglo_cant[$i];	
 $suma=$i+1;
 echo"<Row>";
 echo "<Cell><Data ss:Type='String'>PC".$suma."</Data></Cell>
   <Cell><Data ss:Type='String'>".$num."</Data></Cell>";
  $cont=0;
  while($cont<=$cant)
  {
  $cod=$arreglo[$id][$cont];	
  $tit=$arreglo_tit[$id][$cont];	
  echo"<Cell ss:StyleID='s23'><Data ss:Type='Number'>".$cod."</Data><Comment ss:Author= 'STecnicos '><ss:Data
       xmlns= 'http://www.w3.org/TR/REC-html40 '><Font
        html:Face= 'Tahoma ' html:Size= '8 ' html:Color= '#000000 '>$tit</Font></ss:Data></Comment></Cell>";
  $cont++;
  }
  if($cont<=$crear)
  {
  	while($cont<=$crear)
  	{ 
  	 echo"<Cell><Data ss:Type='String'></Data></Cell>";
  	 $cont++;	
  	}
  }
echo"</Row>";
}

excel_header("codigos.xls");

if($tipo==2)
{

$sql1="select codigo_barra from general.codigos_barra order by codigo_barra";

$result_maquinas = sql($sql1) or fin_pagina();

while(!$result_maquinas->EOF)
{
 $nro=$result_maquinas->fields['codigo_barra'];	
 if($nro=="$void")
 {
 $con="'$nro'";
 $result_maquinas->MoveNext();
 while((!$result_maquinas->EOF)&&($nro!="$void1"))
 {
  $nro=$result_maquinas->fields['codigo_barra'];
  $con .=" ,'$nro'";
  $result_maquinas->MoveNext();
 }
 $con .=" ,'$nro'";
 }
 else 
 $result_maquinas->MoveNext();
}	
	
$sql = "select id_producto_compuesto,codigos_barra.codigo_barra,descripcion,nro_serie
from general.codigos_barra 
join general.productos_compuestos using (id_producto_compuesto)
left join general.producto_especifico using (id_prod_esp)
where id_producto_compuesto 
in(select id_producto_compuesto from general.codigos_barra 
where codigo_barra in($con)) order by id_producto_compuesto";
}

if($tipo==1)
{
$sql1="select nro_serie,oid from ordenes.maquina where oid>=$serie and oid<=$serie1 order by oid";

$result_maquinas = sql($sql1) or fin_pagina();

while(!$result_maquinas->EOF)
{
 $nro=$result_maquinas->fields['nro_serie'];	
 $con="'$nro'";
 $result_maquinas->MoveNext();
 while(!$result_maquinas->EOF)
 {
  $nro=$result_maquinas->fields['nro_serie'];
  $con .=" ,'$nro'";
  $result_maquinas->MoveNext();
 }
}
$sql="select id_producto_compuesto,codigos_barra.codigo_barra,descripcion,nro_serie
from general.codigos_barra 
join general.productos_compuestos using (id_producto_compuesto)
left join general.producto_especifico using (id_prod_esp)
where id_producto_compuesto 
in(select id_producto_compuesto from general.productos_compuestos 
where nro_serie in ($con)
)
order by id_producto_compuesto";
/*echo $sql;
die();*/
}
if($tipo==5)
{
 $primera=0;
 $cuant=1;	
 while($cuantos>=$cuant)
{	
 $nro=$_POST['series_'.$cuant];
 if($nro!="")
 {	
  if($primera==1)
  {
  	$con .=" ,'$nro'";
  	$cuant++;
  }
  else 
  {
   $con="'$nro'";
   $cuant++;	
   $primera=1;
  }
 }
 else 
 { 	
  $cuant++;	
 } 
}	
 $sql="select id_producto_compuesto,codigos_barra.codigo_barra,descripcion,nro_serie
 from general.codigos_barra 
 join general.productos_compuestos using (id_producto_compuesto)
 left join general.producto_especifico using (id_prod_esp) where id_producto_compuesto 
in(select id_producto_compuesto from general.productos_compuestos 
where nro_serie in ($con)
)
order by id_producto_compuesto";
}
$result_provincia = sql($sql) or fin_pagina();
 
 $ii=0;
 $p=-1;
 $id_prod=0;
 $crear=1;
 while (!$result_provincia->EOF)
 { 
  $id_prod1=$result_provincia->fields['id_producto_compuesto'];
  if($id_prod1!=$id_prod)
  {
  	if($crear<=$ii)
  	{
  		$crear=$ii;
  	}
  	$p++;
  	$arreglo[$p]=$id_prod1;
  	$arreglo_num[$p]=$result_provincia->fields['nro_serie'];
  	$id_prod=$id_prod1;
  	$ii=0;
  	$arreglo[$id_prod1][$ii]=$result_provincia->fields['codigo_barra'];
    $arreglo_tit[$id_prod1][$ii]=$result_provincia->fields['descripcion'];
  	$arreglo_cant[$p]=$ii;
  
  }
  else 
  {
  $ii++;
  $arreglo[$id_prod1][$ii]=$result_provincia->fields['codigo_barra'];
  $arreglo_tit[$id_prod1][$ii]=$result_provincia->fields['descripcion'];
  $arreglo_cant[$p]=$ii;  
  if($crear<=$ii)
  	{
  		$crear=$ii;
  	}
  }
  $result_provincia->MoveNext();
 } 
excel_header_xml($crear);
$datos_rangos = array();
$i=0;
//$pintar=0;
 while ($i<=$p)
 { 
  excel_row($arreglo,$i,$crear,$arreglo_cant,$arreglo_tit,$arreglo_num,$pintar);
  /*if($pintar==1)
  $pintar=0;
  else 
  $pintar++;*/
 $i++;
 }
 $datos_rangos[($p)]['end'] = ($crear);
echo excel_footer_xml();
exit;
}
else
{ 
echo $html_header;
?>

<script language="JavaScript" type="text/javascript">
function control()
{
 var cant=0;	
 for (i=1; i<=parseInt(document.all.cuantos.value); i++) {
	   sent="document.all.series_"+i;
	   check=eval(sent);
	   if (check.value!="") cant++;
      }
    if (cant==0){
    alert ("Falta Ingresar los Números de Serie");
    return false;
     }
 return true;
}
function cargarSeries(){
	var arregloaux = new Array();
	var arreglo = new Array();
	var tamArreglo;
	arregloaux=window.clipboardData.getData("Text");
	arreglo=arregloaux.split("\n");
	tamArreglo=arreglo.length;
	var i=eval ("document.all.rango.value");
	var j=0;
	var error=0;
	var errorCont=-1;
	while (j<=tamArreglo-1){
		var res = eval("document.all.series_"+i);
		
		if (typeof (res)=="undefined"){
			error=1;
			errorCont++;			
		}
		else{
			res.value=arreglo[j];
		}
		i++;
		j++;
	}
	if (error==1){
		alert ("La Cantidad de Datos del Portapapeles es MAYOR a los Cuadros de Textos Disponibles en la Pagina.\nLo Sobrepasa en "+errorCont+" Fila/s.");
	} 
}
</script>
<form action="producto_compuesto_excel.php" method="POST">
<table class="bordes" align="center" width="60%">
<input type="hidden" name="tipo" value="5">
 <tr id="mo"><td align="center">
 <table border="1" width="100%" align="center" bordercolor="Black">
 <tr>
 <td align="center">
 <b><font color="White" size="2">Ingrese los Números de serie</font></b>
 </td>
 </tr>
 </table>
 </td></tr>
 <tr>
 <td>
 <table width="100%" align="center" class="bordes">
  <tr align="center">
  	  <td align="center" colspan="2">
  	  	<strong>
  	  	<font color="Red">
  	  	Presionar el Boton despues de Copiar los Datos del Excel
  	  	</font>
  	  	</strong>  	  
  	  </td>
  </tr>
  <tr align="center">
  	<td align="center" colspan="2">
  		<b>Ingrese Numero de Inicio:&nbsp;</b>
  		<input type="text" value="1" name="rango" title="Ingrese el Numero Desde" size="4">
  	</td>
  </tr>
  
  <tr align="center">
  	  <td align="center" colspan="2">
  	  	<input type="button" name="cargar_series" value="Cargar Series del Portapapeles" onclick="cargarSeries();">
  	    <br>
  	  </td>
  </tr>
 </table>
 </td>
 </tr>
 <?
 $conta=1;
 while($conta<=$cuantos)
 {
 ?>
 <tr>
 <td align="center">
 <b>N° <?=$conta?>&nbsp;&nbsp;&nbsp;</b><input type="text" name="series_<?=$conta?>">
 </td>
 </tr>
 <?
 $conta++;
 }
 ?>
 <tr><td colspan="3" align="center">
 <input type="hidden" name="cuantos" value="<?=$cuantos?>">
 <input type="submit" name="aceptar" value="Aceptar" onclick="return control();"> 
 <input type=button name=cerrar value=Cerrar onclick="window.close()">
</td></tr>
</table>   
</form>
<?
fin_pagina();
}?>