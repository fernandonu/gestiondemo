<?
/*
Autor: Norberto
Creado: viernes 21/01/05

MODIFICADA POR
$Author: nazabal $
$Revision: 1.3 $
$Date: 2005/03/09 20:34:53 $
*/

require_once("../../config.php");

if ($_POST['hexportar_reng'] == "") fin_pagina();

$renglones = $_POST['hexportar_reng'];
$entrega_estimada_producto = $_POST['entrega_estimada_producto'];
if (!FechaOk($entrega_estimada_producto)) { 
	Error("Falta la fecha de entrega estimada de los productos");
	fin_pagina();
}
else { $entrega_estimada_producto = fecha_db($entrega_estimada_producto); }

function excel_header_xml() {
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
  <Style ss:ID='Default' ss:Name='Normal'>
   <Alignment ss:Vertical='Bottom'/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID='s21'>
   <Alignment ss:Horizontal='Left' ss:Vertical='Bottom'/>
  </Style>
  <Style ss:ID='s22'>
   <Alignment ss:Vertical='Bottom' ss:WrapText='1'/>
  </Style>
  <Style ss:ID='s23'>
   <Alignment ss:Horizontal='Center' ss:Vertical='Center' ss:Rotate='90'
    ss:WrapText='1'/>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font ss:FontName='Tahoma' x:Family='Swiss' ss:Size='8' ss:Bold='1'/>
   <Interior ss:Color='#FFFF00' ss:Pattern='Solid'/>
  </Style>
  <Style ss:ID='s24'>
   <Alignment ss:Horizontal='Center' ss:Vertical='Center' ss:WrapText='1'/>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font ss:FontName='Tahoma' x:Family='Swiss' ss:Size='8' ss:Bold='1'/>
   <Interior ss:Color='#FFFF00' ss:Pattern='Solid'/>
  </Style>
  <Style ss:ID='s25'>
   <Alignment ss:Vertical='Bottom'/>
   <Borders/>
   <Font ss:Color='#FFFFFF'/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID='s26'>
   <Alignment ss:Horizontal='Left' ss:Vertical='Bottom'/>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font ss:FontName='Tahoma' x:Family='Swiss' ss:Size='8' ss:Bold='1'/>
   <Interior/>
  </Style>
  <Style ss:ID='s27'>
   <Alignment ss:Horizontal='Center' ss:Vertical='Bottom'/>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font ss:FontName='Tahoma' x:Family='Swiss' ss:Size='8' ss:Bold='1'/>
   <Interior/>
  </Style>
  <Style ss:ID='s28'>
   <Alignment ss:Horizontal='Center' ss:Vertical='Bottom'/>
   <Borders/>
   <Font ss:FontName='Tahoma' x:Family='Swiss' ss:Size='8' ss:Bold='1'/>
   <Interior/>
  </Style>
  <Style ss:ID='s29'>
   <Borders/>
  </Style>
  <Style ss:ID='s30'>
   <Alignment ss:Horizontal='Center' ss:Vertical='Bottom'/>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font x:Family='Swiss' ss:Bold='1'/>
   <Interior ss:Color='#FFFF00' ss:Pattern='Solid'/>
  </Style>
  <Style ss:ID='s31'>
   <Alignment ss:Horizontal='Left' ss:Vertical='Bottom'/>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font x:Family='Swiss'/>
  </Style>
  <Style ss:ID='s32'>
   <Alignment ss:Horizontal='Center' ss:Vertical='Bottom'/>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font x:Family='Swiss' ss:Bold='1'/>
   <Interior ss:Color='#FFFF00' ss:Pattern='Solid'/>
   <NumberFormat ss:Format='Short Date'/>
  </Style>
  <Style ss:ID='s33'>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font x:Family='Swiss'/>
  </Style>
  <Style ss:ID='s34'>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
  </Style>
  <Style ss:ID='s35'>
   <Alignment ss:Horizontal='Center' ss:Vertical='Bottom'/>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font ss:Bold='1'/>
   <Interior ss:Color='#FFFF00' ss:Pattern='Solid'/>
   <NumberFormat ss:Format='Short Date'/>
  </Style>
  <Style ss:ID='s36'>
   <Alignment ss:Horizontal='Center' ss:Vertical='Bottom'/>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='2'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='2'/>
   </Borders>
   <Font ss:Bold='1'/>
   <Interior ss:Color='#FFFF00' ss:Pattern='Solid'/>
  </Style>
  <Style ss:ID='s37'>
   <Alignment ss:Horizontal='Center' ss:Vertical='Bottom'/>
   <Font ss:Bold='1'/>
   <Interior/>
  </Style>
  <Style ss:ID='s38'>
   <Alignment ss:Horizontal='Left' ss:Vertical='Bottom'/>
   <Interior/>
  </Style>
  <Style ss:ID='s39'>
   <Interior/>
  </Style>
  <Style ss:ID='s40'>
   <Alignment ss:Vertical='Bottom' ss:WrapText='1'/>
   <Interior/>
  </Style>
 </Styles>
  <Worksheet ss:Name='Hoja1'>
  <Table ss:ExpandedColumnCount='12' x:FullColumns='1'
   x:FullRows='1' ss:DefaultColumnWidth='60'>
   <Column ss:AutoFitWidth='0' ss:Width='30.75'/>
   <Column ss:StyleID='s21' ss:AutoFitWidth='0' ss:Width='213'/>
   <Column ss:Index='4' ss:AutoFitWidth='0' ss:Width='166.5'/>
   <Column ss:AutoFitWidth='0' ss:Width='25.5'/>
   <Column ss:AutoFitWidth='0' ss:Width='66'/>
   <Column ss:AutoFitWidth='0' ss:Width='60.75'/>
   <Column ss:AutoFitWidth='0' ss:Width='65.25'/>
   <Row ss:AutoFitHeight='0' ss:Height='58.5' ss:StyleID='s22'>
    <Cell ss:StyleID='s23'><Data ss:Type='String'>Cantidad</Data></Cell>
    <Cell ss:StyleID='s24'><Data ss:Type='String'>Producto</Data></Cell>
    <Cell ss:StyleID='s24'><Data ss:Type='String'>fecha de entrega</Data></Cell>
    <Cell ss:StyleID='s24'><Data ss:Type='String'>Comentario</Data></Cell>
    <Cell ss:StyleID='s23'><Data ss:Type='String'>moneda</Data></Cell>
    <Cell ss:StyleID='s24'><Data ss:Type='String'>Precio a llenar por el Proveedor</Data></Cell>
    <Cell ss:StyleID='s24'><Data ss:Type='String'>Forma de Pago</Data></Cell>
    <Cell ss:StyleID='s24'><Data ss:Type='String'>Material en Stock?</Data></Cell>
    <Cell ss:StyleID='s25'><Data ss:Type='String'>U\$S</Data></Cell>
    <Cell ss:StyleID='s25'><Data ss:Type='String'>AR\$</Data></Cell>
    <Cell ss:StyleID='s25'><Data ss:Type='String'>SI</Data></Cell>
    <Cell ss:StyleID='s25'><Data ss:Type='String'>NO</Data></Cell>
   </Row>";
}

function excel_footer_xml($datos) {
echo "
   <Row>
    <Cell ss:StyleID='s37'/>
    <Cell ss:StyleID='s38'/>
    <Cell ss:StyleID='s37'/>
    <Cell ss:StyleID='s39'/>
    <Cell ss:StyleID='s39'/>
   </Row>
   <Row ss:AutoFitHeight='0' ss:Height='38.25'>
    <Cell ss:StyleID='s37'/>
    <Cell ss:StyleID='s38'/>
    <Cell ss:StyleID='s37'/>
    <Cell ss:StyleID='s40'><Data ss:Type='String'>Especificar si son Bulk o Box, adem√°s de la marca y modelo cotizado</Data></Cell>
    <Cell ss:StyleID='s40'/>
   </Row>
   <Row ss:AutoFitHeight='0' ss:Height='25.5'>
    <Cell ss:StyleID='s37'/>
    <Cell ss:StyleID='s38'/>
    <Cell ss:StyleID='s37'/>
    <Cell ss:StyleID='s40'><Data ss:Type='String'>En caso de discos rigidos no se aceptan marca hitachi</Data></Cell>
    <Cell ss:StyleID='s40'/>
   </Row>
  </Table>
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
	$col1 = $col2 = array();
	foreach ($datos as $rango) {
		$col1[] = "R".$rango['start']."C5:R".$rango['end']."C5";
		$col2[] = "R".$rango['start']."C8:R".$rango['end']."C8";
	}
echo "
  <DataValidation xmlns='urn:schemas-microsoft-com:office:excel'>
   <Range>".(join(",",$col1))."</Range>
   <Type>List</Type>
   <Value>R1C9:R1C10</Value>
  </DataValidation>
  <DataValidation xmlns='urn:schemas-microsoft-com:office:excel'>
   <Range>".(join(",",$col2))."</Range>
   <Type>List</Type>
   <Value>R1C11:R1C12</Value>
  </DataValidation>";
echo "
 </Worksheet>
</Workbook>";
}
function excel_row_header($id) {
echo "
   <Row ss:AutoFitHeight='0' ss:Height='13.5'>
    <Cell ss:StyleID='s26'><Data ss:Type='String'>ID $id</Data></Cell>
    <Cell ss:StyleID='s26'/>
    <Cell ss:StyleID='s27'/>
    <Cell ss:StyleID='s28'/>
    <Cell ss:StyleID='s28'/>
    <Cell ss:StyleID='s29'/>
    <Cell ss:StyleID='s29'/>
   </Row>";
}
function excel_row($cantidad,$descripcion,$fecha) {
echo "
   <Row ss:AutoFitHeight='0' ss:Height='13.5'>
    <Cell ss:StyleID='s36'><Data ss:Type='Number'>".$cantidad."</Data></Cell>
    <Cell ss:StyleID='s31'><Data ss:Type='String'>".$descripcion."</Data></Cell>
    <Cell ss:StyleID='s32'><Data ss:Type='DateTime'>".$fecha."T00:00:00.000</Data></Cell>
    <Cell ss:StyleID='s34'/>
    <Cell ss:StyleID='s33'><Data ss:Type='String'>U\$S</Data></Cell>
    <Cell ss:StyleID='s34'/>
    <Cell ss:StyleID='s34'/>
    <Cell ss:StyleID='s34'><Data ss:Type='String'>SI</Data></Cell>
   </Row>";
}
function excel_fix_chars($texto) {
	$texto = str_replace("·","&aacute;",$texto);
	$texto = str_replace("È","&eacute;",$texto);
	$texto = str_replace("Ì","&iacute;",$texto);
	$texto = str_replace("Û","&oacute;",$texto);
	$texto = str_replace("˙","&uacute;",$texto);
	$texto = str_replace("Ò","&ntilde;",$texto);
	$texto = str_replace("¡","&Aacute;",$texto);
	$texto = str_replace("…","&Eacute;",$texto);
	$texto = str_replace("Õ","&Iacute;",$texto);
	$texto = str_replace("”","&Oacute;",$texto);
	$texto = str_replace("⁄","&Uacute;",$texto);
	$texto = str_replace("—","&Ntilde;",$texto);
	return $texto;
}
excel_header("Presupuesto.xls");

$sql = "
SELECT 
  licitaciones.renglon_presupuesto_new.id_renglon_prop,
  licitaciones.producto_presupuesto_new.desc_orig,
  (licitaciones.renglon_presupuesto_new.cantidad * licitaciones.producto_presupuesto_new.cantidad) AS cantidad,
  licitaciones.producto_presupuesto_new.adicional,
  licitaciones.producto_presupuesto_new.desc_adic,
  licitaciones.entrega_estimada.fecha_estimada,
  licitaciones.entrega_estimada.id_licitacion,
  licitaciones.renglon.codigo_renglon
FROM
  licitaciones.producto_presupuesto_new
  INNER JOIN licitaciones.renglon_presupuesto_new ON (licitaciones.producto_presupuesto_new.id_renglon_prop = licitaciones.renglon_presupuesto_new.id_renglon_prop)
  INNER JOIN licitaciones.licitacion_presupuesto_new ON (licitaciones.renglon_presupuesto_new.id_licitacion_prop = licitaciones.licitacion_presupuesto_new.id_licitacion_prop)
  INNER JOIN licitaciones.entrega_estimada ON (licitaciones.licitacion_presupuesto_new.id_entrega_estimada = licitaciones.entrega_estimada.id_entrega_estimada)
  INNER JOIN licitaciones.renglon ON (licitaciones.renglon_presupuesto_new.id_renglon = licitaciones.renglon.id_renglon)
WHERE
  (licitaciones.renglon_presupuesto_new.id_renglon_prop IN ($renglones))
ORDER BY
  licitaciones.renglon_presupuesto_new.id_renglon_prop,
  licitaciones.producto_presupuesto_new.desc_orig
";
$result = sql($sql) or fin_pagina();

$estilo = "style=\"background-color:yellow;font-weight:bold;text-align:center;vertical-align:middle;height:50px\"";

excel_header_xml();

$last_id = "";
$datos_rangos = array();
$cont_filas = 3;
$cont_rangos = 0;
while (!$result->EOF) {
	$id = $result->fields["id_licitacion"]."/".$result->fields["codigo_renglon"];
	if ($id != $last_id) {
		$last_id = $id;
		if ($datos_rangos[($cont_rangos - 1)]['start'] != "") {
			$datos_rangos[($cont_rangos - 1)]['end'] = ($cont_filas - 2);
		}
		//$datos_rangos[$cont_rangos]['end'] = $cont_filas;
		$datos_rangos[$cont_rangos]['start'] = $cont_filas;
		$cont_filas++;
		$cont_rangos++;
		excel_row_header($id);
	}
	$cantidad = $result->fields["cantidad"];
	$descripcion = excel_fix_chars($result->fields["desc_orig"].(($result->fields["desc_adic"])?" (".$result->fields["desc_adic"].")":""));
	$fecha = $entrega_estimada_producto;
	excel_row($cantidad,$descripcion,$fecha);
	$cont_filas++;
	$result->MoveNext();
}
$datos_rangos[($cont_rangos - 1)]['end'] = ($cont_filas - 2);
//print_r($datos_rangos);
echo excel_footer_xml($datos_rangos);
exit;
?>