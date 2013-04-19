<?php
//modulo facturas

require_once("../../config.php");
define("OUTPUT","./pdfs"); //directorio donde se crearan los pdfs
require(LIB_DIR.'/fpdf.php'); //clase principal

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

//hata aca hay que sacar
class factura_a extends FPDF
{
 var $desp_producto;
 var $desp_producto_aux;
 var $xoffset;
 var $yoffset;

function factura_a()
{$this->fpdf();
 //PARA HOJA A4
 $this->desp_producto=120;
 $this->xoffset=-7;//desplazado a izq
 $this->yoffset=-40;//desplazado hacia arriba
}

function nombre($string)  {
    $this->SetFont('Arial','B',8);
    $this->setxy(34+$this->xoffset,86+$this->yoffset);
    $string=substr($string,0,75);
    $this->cell(0,0,$string);
}

function direccion($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(34+$this->xoffset,91+$this->yoffset);
    $string=substr($string,0,75);
    $this->cell(0,0,$string);
}

function cuit($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(34+$this->xoffset,96+$this->yoffset);
    $this->cell(0,0,$string);
}

function iva($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(34+$this->xoffset,101+$this->yoffset);
    $this->cell(0,0,$string);
}

function iib($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(95+$this->xoffset,96+$this->yoffset);
    $this->cell(0,0,$string);
}

function otros($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(95+$this->xoffset,101+$this->yoffset);
    $string=substr($string,0,75);
    $this->cell(0,0,$string);
}
function fecha($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(150+$this->xoffset,86+$this->yoffset);
    $this->cell(53,0,$string,null,null,'L'); //5.3cm de ancho, alineacion derecha
}
function pedido($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(150+$this->xoffset,91+$this->yoffset);
    $this->cell(53,0,$string,null,null,'L'); //5.3cm de ancho, alineacion derecha
}

function remito($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(150+$this->xoffset,96+$this->yoffset);
    $this->cell(53,0,$string,null,null,'L'); //5.3cm de ancho, alineacion derecha
}


function venta($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(150+$this->xoffset,101+$this->yoffset);
    $this->cell(53,0,$string,null,null,'L'); //5.3cm de ancho, alineacion derecha
}


function cantidad($string)
{$this->SetFont('Arial','B',8);
 $this->setxy(12+$this->xoffset,$this->desp_producto+$this->yoffset);
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
		   $this->setxy(34+$this->xoffset,$this->desp_producto+$this->yoffset);
		   $this->cell(0,0,$array_string[$i]);
		   if ($i!=$cantidad-1)$this->desp_producto+=5;
		} //del for

} //de la descripcion

function unitario($string,$moneda)
{
 $this->SetFont('Arial','B',8);
 $this->setxy(155+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
 $this->cell(2,0,$moneda,null,null,'L'); //2.5cm de ancho, alineacion derecha
 $this->setxy(155+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
 $this->cell(19,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha

}

function parcial($string,$moneda)
{
 $this->SetFont('Arial','B',8);
 $this->setxy(185+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
 $this->cell(2,0,$moneda,null,null,'L'); //2.5cm de ancho, alineacion derecha

 $this->setxy(185+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
$this->cell(25,0,$string,null,null,'R'); //1.6cm de ancho, alineacion derecha
}

function agregar_producto($cantidad,$descripcion,$unitario,$parcial,$moneda)
{
  $this->cantidad($cantidad);
  $this->descripcion($descripcion);
  $this->unitario($unitario,$moneda);
  $this->parcial($parcial,$moneda);
  $this->desp_producto+=5;
}

function subtotal($string,$moneda)
{$this->SetFont('Arial','B',8);
 $this->setxy(185+$this->xoffset,284+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(2,0,$moneda,null,null,'L'); //2.5cm de ancho, alineacion derecha
 $this->setxy(185+$this->xoffset,284+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha
}
function iva_inscripto($string,$moneda)
{$this->SetFont('Arial','B',8);
 $this->setxy(185+$this->xoffset,290+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(2,0,$moneda,null,null,'L'); //2.5cm de ancho, alineacion derecha
 $this->setxy(185+$this->xoffset,290+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha
}
/*no va este tipo de iva
function iva_no_inscripto($string,$moneda)
{$this->SetFont('Arial','B',8);
 $this->setxy(177+$this->xoffset,282+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(2,0,$moneda,null,null,'L'); //2.5cm de ancho, alineacion derecha
 $this->setxy(177+$this->xoffset,282+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha
}*/
function total($string,$moneda)
{$this->SetFont('Arial','B',8);
 $this->setxy(185+$this->xoffset,296+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(2,0,$moneda,null,null,'L'); //2.5cm de ancho, alineacion derecha
 $this->setxy(185+$this->xoffset,296+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha
}


function guardar_servidor($string) {
$this->output("$string",true,false);                                                   
}//fin de funcion
                                    

}//fin del pdf de factura

//obtengo los datos de la base de datos
//$id_factura viene por post
//$id_factura=18;
//$sql="select * from facturas where id_factura=$id_factura";
$sql="select * from (facturas join moneda on facturas.id_moneda = moneda.id_moneda  and id_factura=$id_factura)";
$resultado_factura=$db->execute($sql) or die($db->ErrorMsg()."<br>".$sql);
if ($resultado_factura->RecordCount()>=1) {
      $nombre=$resultado_factura->fields['cliente'];
      $direccion=$resultado_factura->fields['direccion'];
      $cuit=$resultado_factura->fields['cuit'];
      $iva_tipo=$resultado_factura->fields['iva_tipo'];
      $iib=$resultado_factura->fields['iib'];
      $otros=$resultado_factura->fields['otros'];
      $iib=$resultado_factura->fields['iib'];
      $fecha=$resultado_factura->fields['fecha_factura'];
      $pedido=$resultado_factura->fields['pedido'];
      $remito=$resultado_factura->fields['nro_remito'];
      $venta=$resultado_factura->fields['venta'];
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





$pdf=new factura_a();
$pdf->Open();
$pdf->AddPage('P');
$pdf->nombre($nombre);
$pdf->direccion($direccion);
$pdf->cuit($cuit);
$pdf->iva($iva_tipo);
$pdf->iib($iib);
$pdf->otros($otros);
$pdf->remito($remito);
$pdf->fecha($dia);
$pdf->pedido($pedido);
$pdf->venta($venta);
//busco lo sproductos
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
$iva=0;
$subtotal=0;
$porcentaje=($iva_tasa/100)+1;
$parcial=0;
for ($i=0;$i<$cantidad_productos;$i++){
//for ($i=0;$i<30;$i++){
$cantidad=$resultado_productos->fields['cant_prod'];
$descripcion=$resultado_productos->fields['descripcion'];
$precio=$resultado_productos->fields['precio'];
$precio_sin_iva=$precio/$porcentaje;
$iva+=($precio-$precio_sin_iva)*$cantidad;
$parcial=$cantidad*$precio_sin_iva;
$subtotal+=$parcial;
$precio_sin_iva=number_format($precio_sin_iva,2,".","");
$parcial=number_format($parcial,2,".","");
$pdf->agregar_producto($cantidad,$descripcion,$precio_sin_iva,$parcial,$moneda);
$resultado_productos->MoveNext();
}

$subtotal=number_format($subtotal,2,".","");
$pdf->subtotal($subtotal,$moneda);
$total=$subtotal;
$iva=number_format($iva,2,".","");

$pdf->iva_inscripto($iva,$moneda);
//$pdf->iva_inscripto(58666,$moneda);
$total=$total+0;
$iva=$iva+0;
$total=$total+$iva;
$total=number_format($total,2,".","");
$pdf->total($total,$moneda);


//$pdf->output(OUTPUT."/Factura".$id_factura.".pdf");
$pdf->guardar_servidor("Factura".$id_factura.".pdf");
//$pdf->output("c:factura_noelia.pdf");
}//del if de cantidad de faturaas
?>