<?php
//modulo facturas tipo b
//copia del modulo remitos

require_once("../../config.php");
define("OUTPUT","./pdfs"); //directorio donde se crearan los pdfs
require(LIB_DIR.'/fpdf.php'); //clase principal

/*
require('../../lib/fpdf.php');
include('./config1.php');
global $db;
$db->SetFetchMode(ADODB_FETCH_ASSOC);
*/

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);
	
class factura_b extends FPDF
{
 var $desp_producto;
 var $desp_producto_aux;
 var $xoffset;
 var $yoffset;


function factura_b(){
$this->fpdf();
 //PARA HOJA A4
 //$this->desp_producto=120;
// $this->desp_producto=105;
 $this->desp_producto=111;
 $this->xoffset=-2;//(-izq +der)
 $this->yoffset=-37;//desplazado hacia arriba
}

function nombre($string)  {
    $this->SetFont('Arial','B',8);
    $this->setxy(26+$this->xoffset,77+$this->yoffset);
    $string=substr($string,0,75);
    $this->cell(0,0,$string);
}

function direccion($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(26+$this->xoffset,82+$this->yoffset);
    $string=substr($string,0,75);
    $this->cell(0,0,$string);
}

function cuit($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(26+$this->xoffset,87+$this->yoffset);
    $this->cell(0,0,$string);
}



function iva($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(26+$this->xoffset,92+$this->yoffset);
    $this->cell(0,0,$string);
}

function iib($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(92+$this->xoffset,87+$this->yoffset);
    $this->cell(0,0,$string);
}

function otros($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(92+$this->xoffset,92+$this->yoffset);
    $string=substr($string,0,75);
    $this->cell(0,0,$string);
}

function fecha($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(150+$this->xoffset,77+$this->yoffset);
    $this->cell(53,0,$string,null,null,'L'); //5.3cm de ancho, alineacion derecha
}

function pedido($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(150+$this->xoffset,82+$this->yoffset);
    $this->cell(53,0,$string,null,null,'L'); //5.3cm de ancho, alineacion derecha
}
function remito($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(150+$this->xoffset,87+$this->yoffset);
    $this->cell(53,0,$string,null,null,'L'); //5.3cm de ancho, alineacion derecha
}


function venta($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(150+$this->xoffset,92+$this->yoffset);
    $this->cell(53,0,$string,null,null,'L'); //5.3cm de ancho, alineacion derecha
}

function cantidad($string)
{$this->SetFont('Arial','B',8);
 $this->setxy(3+$this->xoffset,$this->desp_producto+$this->yoffset);
 $this->cell(16,0,$string,null,null,'R'); //1.6cm de ancho, alineacion derecha
}


function descripcion($string)
{
	$this->desp_producto_aux=$this->desp_producto;
	$this->SetFont('Arial','B',8);
	$array_string=strtoArray($string,75);
	$cantidad=count($array_string);
		for($i=0;$i<$cantidad;$i++)
		{
		   $this->setxy(28+$this->xoffset,$this->desp_producto+$this->yoffset);
		   $this->cell(0,0,$array_string[$i]);
		   if ($i!=$cantidad-1)$this->desp_producto+=5;
		} //del for
} //de la descripcion


function unitario($string,$moneda)
{
 $this->SetFont('Arial','B',8);
 $this->setxy(152+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
 $this->cell(2,0,$moneda,null,null,'L'); //2.5cm de ancho, alineacion derecha
 $this->setxy(152+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha

}

function parcial($string,$moneda)
{
 $this->SetFont('Arial','B',8);
 $this->setxy(180+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
 $this->cell(2,0,$moneda,null,null,'L'); //2.5cm de ancho, alineacion derecha
 $this->setxy(180+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha
}

function agregar_producto($cantidad,$descripcion,$unitario,$parcial,$moneda)
{$this->cantidad($cantidad);
 $this->descripcion($descripcion);
 $this->unitario($unitario,$moneda);
 $this->parcial($parcial,$moneda);
 $this->desp_producto+=5;
}

function total($string ,$moneda)
{$this->SetFont('Arial','B',8);
 //$this->setxy(175+$this->xoffset,255+$this->yoffset);//+3.2cm //+3mm en y
 $this->setxy(180+$this->xoffset,272+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(2,0,$moneda,null,null,'L'); //2.5cm de ancho, alineacion derecha
 $this->setxy(180+$this->xoffset,272+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha

}

function guardar_servidor($string) {
$this->output("$string",true,false);                                                   
}//fin de funcion
    

}//fin del pdf de factura


//$id_factura=10;

//$sql="select * from facturas join where id_factura=$id_factura";
$sql="select * from (facturas join moneda on facturas.id_moneda = moneda.id_moneda  and id_factura=$id_factura)";
$resultado_factura=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
if ($resultado_factura->RecordCount()>=1) {
      $nombre=$resultado_factura->fields['cliente'];
      $direccion=$resultado_factura->fields['direccion'];
      $cuit=$resultado_factura->fields['cuit'];
      $iva=$resultado_factura->fields['iva_tipo'];
      $iib=$resultado_factura->fields['iib'];
      $otros=$resultado_factura->fields['otros'];
      $iib=$resultado_factura->fields['iib'];
      $fecha=$resultado_factura->fields['fecha_factura'];
      $pedido=$resultado_factura->fields['pedido'];
      $venta=$resultado_factura->fields['venta'];
      $remito=$resultado_factura->fields['nro_remito'];
      $iva_tasa=$resultado_factura->fields['iva_tasa'];
      $moneda=$resultado_factura->fields['simbolo'];
      $venta=$resultado_factura->fields['venta'];

//Formamos la fecha correspondiente en castellano
switch($dia)
{case "Sun":$dia="Domingo";break;
 case "Mon":$dia="Lunes";break;
 case "Tue":$dia="Martes";break;
 case "Wed":$dia="Miercoles";break;
 case "Thu":$dia="Jueves";break;
 case "Fri":$dia="Viernes";break;
 case "Sat":$dia="Sabado";break;
}
$dia.=" ".date('j',strtotime($fecha))." de ";
$mes=date('M',strtotime($fecha));
switch($mes)
{case "Jan":$dia.="Enero";break;
 case "Feb":$dia.="Febrero";break;
 case "Mar":$dia.="Marzo";break;
 case "Apr":$dia.="Abril";break;
 case "May":$dia.="Mayo";break;
 case "Jun":$dia.="Junio";break;
 case "Jul":$dia.="Julio";break;
 case "Aug":$dia.="Agosto";break;
 case "Sep":$dia.="Septiembre";break;
 case "Oct":$dia.="Octubre";break;
 case "Nov":$dia.="Noviembre";break;
 case "Dec":$dia.="Diciembre";break;
}
$dia.=" del ".date('Y',strtotime($fecha));




$pdf=new factura_b();
$pdf->Open();
$pdf->AddPage('P');
$pdf->nombre($nombre);
$pdf->direccion($direccion);
$pdf->cuit($cuit);
$pdf->iva($iva);
$pdf->iib($iib);
$pdf->otros($otros);

$pdf->fecha($dia);
$pdf->pedido($pedido);
$pdf->venta($venta);
$pdf->remito($remito);

//busco lo productos
$subtotal=0;
//if ($seg==1) {
$sql="select cant_prod,descripcion,items_factura.precio,renglon.codigo_renglon
	from (facturacion.items_factura left join licitaciones.renglones_oc using (id_renglones_oc))
  	left join licitaciones.renglon using (id_renglon)
	where items_factura.id_factura=$id_factura
	order by codigo_renglon";

//}
//else {
//$sql="select * from (items_factura join productos on items_factura.id_producto = productos.id_producto and items_factura.id_factura=$id_factura)";
//}
$resultado_productos = $db->execute($sql) or die($db->ErrorMsg());
$cantidad_productos=$resultado_productos->RecordCount();

$total=0;
$parcial=0;
for ($i=0;$i<$cantidad_productos;$i++){
//for ($i=0;$i<30;$i++){
$cantidad=$resultado_productos->fields['cant_prod'];
$descripcion=$resultado_productos->fields['descripcion'];
$precio=$resultado_productos->fields['precio'];
$parcial=$cantidad*$precio;
$total+=$parcial;
$precio=number_format($precio,2,".","");
$parcial=number_format($parcial,2,".","");
$pdf->agregar_producto($cantidad,$descripcion,$precio,$parcial,$moneda);
$resultado_productos->MoveNext();
}
$total=number_format($total,2,".","");
$pdf->total($total,$moneda);


//$pdf->output(OUTPUT."/Factura".$id_factura.".pdf");
$pdf->guardar_servidor("Factura".$id_factura.".pdf");
//$pdf->output("c:factura_b.pdf");
}//del if de cantidad de faturaas
?>