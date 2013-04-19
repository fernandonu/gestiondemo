<?php
/*
MODIFICADA POR
$Author: fernando $
$Revision: 1.34 $
$Date: 2006/12/04 20:54:33 $
*/

require_once("../../config.php");
define("OUTPUT","./pdfs"); //directorio donde se crearan los pdfs
require(LIB_DIR.'/fpdf.php'); //clase principal

if ($parametros)
	extract($parametros,EXTR_OVERWRITE);

	switch ($formato)
	{
		case 'new':	//remitos Nº 5401 - 5600
//								$g_xoffset=-35;//mm (desp izq -) (desp der +)
//								$g_yoffset=-40;//mm (desp arriba -) (desp abajo +)
								$g_xoffset=-9;//mm (desp izq -) (desp der +)
								$g_yoffset=-40;//mm (desp arriba -) (desp abajo +)
								$g_prodoffset_y=120;//mm (desp arriba -) (desp abajo +)
								$g_prodoffset_x=15;//mm (desp izq -) (desp der +)
								$g_cant_width=19; //mm
								$g_desc_width=115; 
								$g_unitario_width=27; 
								$g_parcial_width=26; 
								break;
		case 'old':		//remitos Nº 5301 - 5400
								$g_xoffset=-2;//mm (desp izq -) (desp der +)
								$g_yoffset=-24;//mm (desp arriba -) (desp abajo +)
								$g_prodoffset_y=115;//mm (desp arriba -) (desp abajo +)
								$g_prodoffset_x=15;//mm (desp izq -) (desp der +)
								$g_cant_width=19; //mm
								$g_desc_width=115; 
								$g_unitario_width=27; 
								$g_parcial_width=26; 
								break;
		
	};
	
class remito extends FPDF
{
 var $desp_producto;
 var $desp_producto_x;
 var $desp_producto_aux;
 var $xoffset;
 var $yoffset; 
 var $cant_width; 
 var $desc_width; 
 var $unitario_width; 
 var $parcial_width; 

function remito()
{
	global $g_xoffset,$g_yoffset,$g_prodoffset_y,$g_cant_width,$g_desc_width,$g_unitario_width,$g_parcial_width,$g_prodoffset_x;
	
	$this->fpdf();
 //PARA HOJA A4
 $this->xoffset=$g_xoffset;
 $this->yoffset=$g_yoffset;
 $this->desp_producto=$g_prodoffset_y;
 $this->desp_producto_x=$g_prodoffset_x;
 $this->cant_width=$g_cant_width; 
 $this->desc_width=$g_desc_width; 
 $this->unitario_width=$g_unitario_width; 
 $this->parcial_width=$g_parcial_width; 
}
 
function nombre($string)  {
	$this->SetFont('Arial','B',8);
	$this->setxy(33+$this->xoffset,86+$this->yoffset);
	$this->cell(0,0,$string); 
}

function direccion($string) {
	$this->SetFont('Arial','B',8);
	$this->setxy(33+$this->xoffset,91+$this->yoffset);
	$this->cell(0,0,'esta entrando'.$string);
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
	$this->SetFont('Arial','B',9);
	//$this->setxy(4+$this->desp_producto_x+$this->cant_width+$this->desc_width+$this->xoffset+$this->unitario_width,265+$this->yoffset);//+3.2cm //+3mm en y
	$array_string=strtoArray($string,68);
	$cantidad=count($array_string);
	for($i=0;$i<$cantidad;$i++) {
		$this->setxy($this->desp_producto_x+$this->cant_width+$this->xoffset,10+$this->desp_producto+$this->yoffset);
		$this->cell(22,0,$array_string[$i]);
		if ($i!=$cantidad-1)$this->desp_producto+=5;
	}
}

function fecha($string) {
	$this->SetFont('Arial','B',8);
	$this->setxy(148+$this->xoffset,90+$this->yoffset);
    $this->cell(50,0,$string,null,null,'R'); //5.3cm de ancho, alineacion derecha 
}

function pedido($string) {
	$this->SetFont('Arial','B',8);
	$this->setxy(148+$this->xoffset,95+$this->yoffset);
    $this->cell(50,0,$string,null,null,'R'); //5.3cm de ancho, alineacion derecha 
}

function venta($string) {
	$this->SetFont('Arial','B',8);
	$this->setxy(148+$this->xoffset,100+$this->yoffset);
    $this->cell(50,0,$string,null,null,'R'); //5.3cm de ancho, alineacion derecha
}

function cantidad($string)
{
 $this->SetFont('Arial','B',8);
 $this->setxy($this->desp_producto_x+$this->xoffset,$this->desp_producto+$this->yoffset);
 $this->cell($this->cant_width,0,$string,null,null,'R'); //1.6cm de ancho, alineacion derecha
}
function divide_string($string){

if ((strlen($string)>=0)&&(strlen($string)<=75)){
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string1=substr($string,0,75);
                                $this->cell(0,0,$string1);
                               }//del primer if del else

if ((strlen($string)>75)&&(strlen($string)<=150)){
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string1=substr($string,0,75);
                                $this->cell(0,0,$string1);
                                $this->desp_producto+=5;
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string2=substr($string,75,75);
                                $this->cell(0,0,$string2);
                                }//del degundo if del else
if ((strlen($string)>150)&&(strlen($string)<=225)){
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string1=substr($string,0,75);
                                $this->cell(0,0,$string1);
                                $this->desp_producto+=5;
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string2=substr($string,75,75);
                                $this->cell(0,0,$string2);
                                $this->desp_producto+=5;
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string3=substr($string,150,75);
                               $this->cell(0,0,$string3);
                                 }
if ((strlen($string)>225)){
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string1=substr($string,0,75);
                                $this->cell(0,0,$string1);
                                $this->desp_producto+=5;
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string2=substr($string,75,75);
                                $this->cell(0,0,$string2);
                                $this->desp_producto+=5;
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string3=substr($string,150,75);
                                $this->cell(0,0,$string3);
                                $this->desp_producto+=5;
                                $this->setxy(33+$this->xoffset,$this->desp_producto+$this->yoffset);
                                $string4=substr($string,225,75);
                                $this->cell(0,0,$string4);

                                 }

 } //de divide string

function descripcion($string)
{
	$this->desp_producto_aux=$this->desp_producto;
	$this->SetFont('Arial','B',8);
	$array_string=strtoArray($string,75);
	$cantidad=count($array_string);
		for($i=0;$i<$cantidad;$i++)
		{
		   $this->setxy($this->desp_producto_x+$this->cant_width+$this->xoffset,$this->desp_producto+$this->yoffset);
		   $this->cell($this->desc_width,0,$array_string[$i]);
		   if ($i!=$cantidad-1)$this->desp_producto+=5;
		} //del for

} //de la descripcion

function unitario($string,$moneda)
{$this->SetFont('Arial','B',8);
 $this->setxy($this->desp_producto_x+$this->cant_width+$this->desc_width+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
 $this->cell(4,0,$moneda,null,null,'R'); //0.5cm de ancho, alineacion derecha
 $this->setxy(4+$this->desp_producto_x+$this->cant_width+$this->desc_width+$this->xoffset,$this->desp_producto_aux+$this->yoffset);
 $this->cell(22,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha
}

function parcial($string,$moneda)
{$this->SetFont('Arial','B',8);
 $this->setxy($this->desp_producto_x+$this->cant_width+$this->desc_width+$this->xoffset+$this->unitario_width,$this->desp_producto_aux+$this->yoffset);
 $this->cell(4,0,$moneda,null,null,'R'); //0.5cm de ancho, alineacion derecha
 $this->setxy(4+$this->desp_producto_x+$this->cant_width+$this->desc_width+$this->xoffset+$this->unitario_width,$this->desp_producto_aux+$this->yoffset);
 $this->cell(22,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha
}

function agregar_producto($cantidad,$descripcion,$unitario,$parcial,$imp,$moneda)
{$this->cantidad($cantidad);
 $this->descripcion($descripcion);
 if ($imp==1)
 {$this->unitario(number_format($unitario,2,".",""),$moneda);
  $this->parcial($parcial,$moneda);
 }
 $this->desp_producto+=5;
}

function total($string,$moneda)
{$this->SetFont('Arial','B',8);
 $this->setxy($this->desp_producto_x+$this->cant_width+$this->desc_width+$this->xoffset+$this->unitario_width,265+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(4,0,$moneda,null,null,'R'); //2.5cm de ancho, alineacion derecha
 $this->setxy(4+$this->desp_producto_x+$this->cant_width+$this->desc_width+$this->xoffset+$this->unitario_width,265+$this->yoffset);//+3.2cm //+3mm en y
 $this->cell(22,0,$string,null,null,'R'); //2.5cm de ancho, alineacion derecha

}



function guardar_servidor($string) {
$this->output("$string",true,false);                                                   
}//fin de funcion
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



$sql="select * from (facturacion.remitos join licitaciones.moneda on facturacion.remitos.id_moneda = moneda.id_moneda 
 and id_remito=$id_remito)";
$res=sql($sql) or fin_pagina();

$pdf->nombre($res->fields['cliente']);
//$pdf->direccion($res->fields['direccion']);
$pdf->cuit($res->fields['cuit']);
$pdf->iva($res->fields['iva_tipo']);
$pdf->iib($res->fields['iib']);
//$pdf->otros($res->fields['otros']);

//Formamos la fecha correspondiente en castellano
$dia=date('D',strtotime( $res->fields['fecha_remito']));
switch($dia)
{case "Sun":$dia="Domingo";break;
 case "Mon":$dia="Lunes";break;
 case "Tue":$dia="Martes";break;
 case "Wed":$dia="Miercoles";break;
 case "Thu":$dia="Jueves";break;
 case "Fri":$dia="Viernes";break;
 case "Sat":$dia="Sabado";break;
}
$dia.=" ".date('j',strtotime( $res->fields['fecha_remito']))." de ";
$mes=date('M',strtotime( $res->fields['fecha_remito']));
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

//obtenemos la moneda que se esta usando
$query_moneda="select simbolo from remitos join moneda using(id_moneda) where id_remito=$id_remito";
$moneda=$db->Execute($query_moneda) or die ($db->ErrorMsg());

$dia.=" del ".date('Y',strtotime($res->fields['fecha_remito']));
$pdf->fecha($dia);

$pdf->pedido($res->fields['id_licitacion']?"ID Lic: ".$res->fields['id_licitacion']:$res->fields['pedido']);
$pdf->venta($res->fields['venta']);

//if ($seg==1) {
$sql="select cant_prod,descripcion,items_remito.precio,chk_precios
	from facturacion.remitos join facturacion.items_remito
    on facturacion.remitos.id_remito =facturacion.items_remito.id_remito
    and items_remito.id_remito=$id_remito";
//}
//else {
//$sql1="select * from 
//       facturacion.remitos join 
//	   (facturacion.items_remito join general.productos on 
//       items_remito.id_producto = productos.id_producto and items_remito.id_remito=$id_remito)
//       using (id_remito)";
//}

$resultado_productos = $db->execute($sql) or die($db->ErrorMsg());
$cantidad_productos=$resultado_productos->RecordCount();
//$resultado_productos1 = $db->execute($sql1) or die($db->ErrorMsg());
//$cantidad_productos1=$resultado_productos1->RecordCount();
$imprime=$resultado_productos->fields['chk_precios'];
//$imprime1=$resultado_productos1->fields['chk_precios'];

$i=0;
$total=0;
while ($i<$cantidad_productos)
{
 $subtotal=number_format($resultado_productos->fields['precio']*$resultado_productos->fields['cant_prod'],2,".","");
 $pdf->agregar_producto($resultado_productos->fields['cant_prod'],$resultado_productos->fields['descripcion'],$resultado_productos->fields['precio'],$subtotal,$imprime,$moneda->fields['simbolo']);
 $i++;
 $total+=$resultado_productos->fields['precio']*$resultado_productos->fields['cant_prod'];
 $resultado_productos->MoveNext();
}
$pdf->otros($res->fields['otros']);
//imprime el total solo si es con precios
if($imprime==1)
    $pdf->total(number_format($total,2,".",""),$moneda->fields['simbolo']);
/*$i=0;
$total=0;
while ($i<$cantidad_productos1)
{
 $subtotal=number_format($resultado_productos1->fields['precio']*$resultado_productos1->fields['cant_prod'],2,".","");
 $pdf->agregar_producto($resultado_productos1->fields['cant_prod'],$resultado_productos1->fields['descripcion'],$resultado_productos1->fields['precio'],$subtotal,$imprime1,$moneda->fields['simbolo']);
 $i++;
 $total+=$resultado_productos1->fields['precio']*$resultado_productos1->fields['cant_prod'];
 $resultado_productos1->MoveNext();
}

if($imprime1==1)
    $pdf->total(number_format($total,2,".",""),$moneda->fields['simbolo']);
*/
$pdf->guardar_servidor("Remito".$id_remito.".pdf");

?>