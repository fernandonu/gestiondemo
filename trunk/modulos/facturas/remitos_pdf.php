<?php
//modulo facturas
//copia del modulo remitos
require_once("../../config.php");
define("OUTPUT","./pdfs"); //directorio donde se crearan los pdfs
require(LIB_DIR.'/fpdf.php'); //clase principal


class remito extends FPDF
{
 var $desp_producto;
 var $xoffset;
 var $yoffset;

function remito()
{$this->fpdf();
 //PARA HOJA A4
 $this->desp_producto=120;
 $this->xoffset=0;//0.8cm //8mm
 $this->yoffset=-31;//-3.1cm //-31mm //hacia arriba
}

function nombre($string)  {
    $this->SetFont('Arial','B',8);
    $this->setxy(33+$this->xoffset,86+$this->yoffset);
    $this->cell(0,0,$string);
}

function direccion($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(33+$this->xoffset,91+$this->yoffset);
    $this->cell(0,0,$string);
}

function cuit($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(33+$this->xoffset,96+$this->yoffset);
    $this->cell(0,0,$string);
}

function iva($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(33+$this->xoffset,101+$this->yoffset);
    $this->cell(0,0,$string);
}

function iib($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(90+$this->xoffset,96+$this->yoffset);
    $this->cell(0,0,$string);
}

function otros($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(90+$this->xoffset,101+$this->yoffset);
    $this->cell(0,0,$string);
}

function fecha($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(152+$this->xoffset,90+$this->yoffset);
    $this->cell(53,0,$string,null,null,'R'); //5.3cm de ancho, alineacion derecha
}

function pedido($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(152+$this->xoffset,95+$this->yoffset);
    $this->cell(53,0,$string,null,null,'R'); //5.3cm de ancho, alineacion derecha
}

function venta($string) {
    $this->SetFont('Arial','B',8);
    $this->setxy(152+$this->xoffset,100+$this->yoffset);
    $this->cell(53,0,$string,null,null,'R'); //5.3cm de ancho, alineacion derecha
}

function cantidad($string)
{$this->SetFont('Arial','B',8);
 $this->setxy(10+$this->xoffset,$this->desp_producto+$this->yoffset);
 $this->cell(16,0,$string,null,null,'R'); //1.6cm de ancho, alineacion derecha
}

function descripcion($string)
{$this->SetFont('Arial','B',8);
 $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
 $this->cell(0,0,$string);
}

function unitario($string)
{$this->SetFont('Arial','B',8);
 $this->setxy(152+$this->xoffset,$this->desp_producto+$this->yoffset);
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha
}

function parcial($string)
{$this->SetFont('Arial','B',8);
 $this->setxy(180+$this->xoffset,$this->desp_producto+$this->yoffset);
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha
}

function agregar_producto($cantidad,$descripcion,$unitario,$parcial,$imp=0)
{$this->cantidad($cantidad);
 $this->descripcion($descripcion);
 if ($imp==1)
 {$this->unitario($unitario);
  $this->parcial($parcial);
 }
 $this->desp_producto+=5;
}

function total($string)
{$this->SetFont('Arial','B',8);
 $this->setxy(180+$this->xoffset,270+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(25,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha

}

}


$pdf=new remito();
$pdf->Open();
$pdf->AddPage('P');
/*
$pdf->nombre("Diego Ingaramo");
$pdf->direccion("Barrio 139 Viv. Mza 222 Casa 6");
$pdf->cuit("30707779930");
$pdf->iva("Responsable Inscripto");
$pdf->iib("No se lo que es");
$pdf->otros("Algun agregado");
$pdf->fecha("Martes 22 de julio del 2003");
$pdf->pedido("23");
$pdf->venta("contado");
*/
$pdf->nombre($_POST['c_nbre']);
$pdf->direccion($_POST['c_dir']);
$pdf->cuit($_POST['c_cuit']);
$pdf->iva($_POST['c_iva']);
$pdf->iib($_POST['c_iib']);
$pdf->otros($_POST['otros']);

//Formamos la fecha correspondiente en castellano
$dia=date('D');
switch($dia)
{case "Sun":$dia="Domingo";break;
 case "Mon":$dia="Lunes";break;
 case "Tue":$dia="Martes";break;
 case "Wed":$dia="Miercoles";break;
 case "Thu":$dia="Jueves";break;
 case "Fri":$dia="Viernes";break;
 case "Sat":$dia="Sabado";break;
}
$dia.=" ".date('j')." de ";
$mes=date('M');
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

$dia.=" del ".date('Y');
$pdf->fecha($dia);
$pdf->pedido($_POST['pedido']);
$pdf->venta($_POST['venta']);
$items=get_items($id_remito);// devuelve todos los items en un arreglo
$i=0;
while ($i<$items['cantidad'])
{$pdf->agregar_producto($items[$i]['cant_prod'],$items[$i]['descripcion'],$items[$i]['precio'],$items[$i]['subtotal'],$_POST['con_precios']);
 $i++;
}

$pdf->total($_POST['total']);
$pdf->output(OUTPUT."/Remito".$id_remito.".pdf");
/*
$pdf->agregar_producto("4","No debe imprimir precio unitario","$400","$1600",0);
$pdf->agregar_producto("2","Debe imprimir precio unitario","$400","$800",1);
$pdf->agregar_producto("7","No debe imprimir precio unitario","$400","$800",0);
$pdf->agregar_producto("2","Debe imprimir precio unitario","$400","$800",1);
$pdf->total("900");
$pdf->output("Orden de Produccion.pdf");//*/

?>
