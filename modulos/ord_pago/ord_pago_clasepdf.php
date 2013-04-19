<?php

define('FPDF_FONTPATH','font/');
require('../../lib/fpdf.php');

class orden_compra extends FPDF
{
	var $base1;
	var $base2;
	var $cant;
	var $flag;

function asignar_base1($x) {
	 $this->base1=$x;
}

function recuperar_base1(){
	return $this->base1;
}

function asignar_base2($x) {
	 $this->base2=$x;
}

function recuperar_base2(){
	return $this->base2;
}

function Header() 
{global $nro_orden,$id_licitacion;
 	
	$this->cant=0;
	$this->flag=1;
	$this->rect(5,5,200,290);
	$this->asignar_base1(40);
	$this->line(5,$this->recuperar_base1(),205,$this->recuperar_base1());
	$this->Image('../ordprod/logo_coradir_prod.png',115,10,85);
	$this->line(110,5,110,32);
	$this->line(5,32,205,32);
	$this->SetFont('Arial','B',24);
    $this->setxy(15,10);
	$this->Cell(80,10,"Orden de Pago");
	$this->setxy(15,13);
	$this->cell(80,20,"Nro : ");
	/*Para licitacion*/
	$this->SetFont('Arial','B',18);
    $this->setxy(5,31);
    $this->SetFillColor(234,234,234);
//    $this->cell(200,9,'Licitación ID :  ',1,1,'L',1);
//	$this->Cell(80,10,"Licitación ID : ",1,1,'L',1);
//	$this->setxy(130,24);
//	$this->cell(30,8,"Internet  Service  Provider");
	if($this->PageNo()!=1)
	{
	 $this->setxy(5,45);
	 $this->SetFontSize(11);
	 $this->cell(0,0,"Orden de Pago Nº ".$nro_orden." - Continuación",0,0,'C');
	 $this->nro_orden_compra($nro_orden);
	 $this->pasa_id_licitacion($id_licitacion);
	} 
	$this->Ln(20);
	//$this->SetAutoPageBreak(1);
}	

function nro_orden_compra($nro) {
	
	$this->SetFont('Arial','B',24);
    $this->setxy(40,13);
	$this->cell(80,20,$nro);

//	$this->setxy(60,$this->recuperar_base1()+2);
//	$this->cell(25,6,$nro);
	
}

function pasa_id_licitacion($id) {
	
	$this->SetFont('Arial','B',18);
	$this->setxy(50,31);
	$this->cell(80,9,$id);
}


function fecha($fecha) {
	
	$this->SetFont('Arial','',14);
	$this->setxy(175,$this->recuperar_base1()+2);
	$this->cell(26,6,$fecha,1,0,R);
}

function proveedor($nombre) {
	
	$this->SetFont('Arial','',14);
	$this->setxy(35,$this->recuperar_base1()+10);
	$this->cell(100,6,$nombre,1,0,L);
}

function vendedor($nombre) {
	
	$this->SetFont('Arial','',14);
	$this->setxy(35,$this->recuperar_base1()+18);
	$this->cell(85,6,$nombre,1,0,L);
	
	
}

function forma_pago($string) {
	
	$this->SetFont('Arial','',14);
	$this->setxy(50,$this->recuperar_base1()+50);
	$this->cell(85,6,$string,1,0,L);
	
}

function entrega($fecha) {
	
	$this->SetFont('Arial','',14);
	$this->setxy(175,$this->recuperar_base1()+50);
	$this->cell(26,6,$fecha,1,0,L);
}

function lugar_entrega($string) {
	
	$this->SetFont('Arial','',14);
	$this->setxy(5,$this->recuperar_base1()+68);
	
	//Control del tamaño del string de lugar_entrega para que
    //no se pase de largo en el pdf
    $add="";
	$largo=strlen($string);
	$cant_nl=str_count_letra("\n",$string);
	$res=ceil($largo/79);
	while($cant_nl+$res>6)
	{$add="...";
	 $string=substr($string,0,$largo-3);
	 $largo=strlen($string);
	 $cant_nl=str_count_letra("\n",$string);
	 $res=ceil($largo/79);
	} 
	$string.=$add; 
	$this->MultiCell(195,5,$string);
	
}

function cliente($string) {
	$this->SetFont('Arial','',14);
	$this->setxy(24,$this->recuperar_base1()+99);
    
    //Control del tamaño del string de cliente para que
    //no se pase de largo en el pdf
    $add="";
	$largo=strlen($string);
	$cant_nl=str_count_letra("\n",$string);
	$res=ceil($largo/80);
	while($cant_nl+$res>6)
	{$add="...";
	 $string=substr($string,0,$largo-3);
	 $largo=strlen($string);
	 $cant_nl=str_count_letra("\n",$string);
	 $res=ceil($largo/80);
	} 
	$string.=$add; 
	$this->MultiCell(195,5,$string);
}
	

function producto($descripcion,$cantidad,$unitario,$moneda,$first) {
	
	//Control para que haga un salto de pagina, si el producto generado
	//no entra en la pagina actual.
	$largo=strlen($descripcion);
	$cant_nl=str_count_letra("\n",$descripcion);
	$res=ceil($largo/55);
	$nro_pixeles_posibles=($res+$cant_nl)*6;
	if(($this->recuperar_base2()+$nro_pixeles_posibles)>290)
	{$this->SetAutoPageBreak(0);
		$this->AddPage();
		$this->asignar_base2(50);
		$this->flag=0;
	 //$this->SetAutoPageBreak(1);	
	}
	$this->setxy(5,$this->recuperar_base2());
	$this->SetFont('Arial','B',9);
	$y_inicial=$this->GetY();
	
	$this->MultiCell(125,6,$descripcion,'TRLB','L');
    $y_posterior=$this->GetY();
    
	$this->setxy(130,$this->recuperar_base2());
	$this->cell(20,6,$cantidad,'TRBL',0,'C');
	$this->setxy(150,$this->recuperar_base2());
	$this->cell(30,6,$moneda,'TBL',0,'L');
	$this->setxy(150,$this->recuperar_base2());
	$unitario_formateado = number_format($unitario, 2, ',', '.');
	$this->cell(30,6,$unitario_formateado,'TRB',0,'R');
	$this->setxy(180,$this->recuperar_base2());
	$total=$cantidad*$unitario;
	$this->cell(25,6,$moneda,'TBL',0,'L');
	$this->setxy(180,$this->recuperar_base2());
	$total_formateado = number_format($total, 2, ',', '.');
	$this->cell(25,6,$total_formateado,'TRB',0,'R');
	
	$aux=$this->recuperar_base2()+ ($y_posterior-$y_inicial);
	$this->asignar_base2($aux);
	$this->cont++;
				
}

function precios($subtotal,$iva,$total){
	
  $this->setxy(110,$this->recuperar_base2()+6);
  $this->cell(50,6,'Subtotal',1);
  $this->setxy(160,$this->recuperar_base2()+6);
  $this->cell(30,6,$subtotal,1,0,R);
  $this->setxy(110,$this->recuperar_base2()+12);
  $this->cell(50,6,'IVA:',1);
  $this->setxy(160,$this->recuperar_base2()+12);
  $this->cell(30,6,$iva,1,0,R);
  $this->setxy(110,$this->recuperar_base2()+6);
  $this->cell(50,6,'TOTAL',1);
  $this->setxy(160,$this->recuperar_base2()+6);
  $this->cell(30,6,$total,1,0,R);
}

function nombre($nombre) {
	
	$this->setxy(140,$this->recuperar_base2()+70);
	$this->cell(60,6,$nombre,1);
	

}
function _final($preciototal,$moneda,$firma1,$firma2,$firma3) {
	if(($this->recuperar_base2()+20)>287)
	{$this->SetAutoPageBreak(0);
		$this->AddPage();
		$this->asignar_base2(50);
		$this->flag=0;
	}
	$this->setxy(135,$this->recuperar_base2()+5);
	$this->cell(35,6,'Total:',1);
	$this->setxy(170,$this->recuperar_base2()+5);
	$this->cell(35,6,$moneda,'TBL',0,'L');
	$this->setxy(170,$this->recuperar_base2()+5);
	$preciototal_formateado= number_format($preciototal, 2, ',', '.');
	$this->cell(35,6,$preciototal_formateado,'TRB',0,'R');
	$this->setxy(150,$this->recuperar_base2()+15);
	if(($this->recuperar_base2()+35)>287)
	{$this->SetAutoPageBreak(0);
		$this->AddPage();
		$this->asignar_base2(50);
		$this->flag=0;
	}
	$this->SetFont('Arial','B',10);
	$this->cell(50,6,'Los precios son I.V.A. incluido.');
	$this->setxy(6,$this->recuperar_base2()+23);
	if(($this->recuperar_base2()+35)>287)
	{$this->SetAutoPageBreak(0);
		$this->AddPage();
		$this->asignar_base2(50);
		$this->flag=0;
	}
	$this->SetFont('Arial','B',14);
	$this->cell(50,6,'Sin otro particular, saluda atentamente:');
	$this->setxy(100,$this->recuperar_base2()+28);
	if(($this->recuperar_base2()+35)>287)
	{$this->SetAutoPageBreak(0);
		$this->AddPage();
		$this->asignar_base2(50);
		$this->flag=0;
	}
	$this->SetFont('Arial','B',12);
	$this->cell(50,6,$firma1,0,0,'C');
	$this->setxy(100,$this->recuperar_base2()+33);
	if(($this->recuperar_base2()+35)>287)
	{$this->SetAutoPageBreak(0);
		$this->AddPage();
		$this->asignar_base2(50);
		$this->flag=0;
	}
	$this->cell(50,6,$firma2,0,0,'C');
	$this->setxy(100,$this->recuperar_base2()+38);
	if(($this->recuperar_base2()+35)>287)
	{$this->SetAutoPageBreak(0);
		$this->AddPage();
		$this->asignar_base2(50);
		$this->flag=0;
	}
	$this->cell(50,6,$firma3,0,0,'C');
}
	

function dibujar_planilla() {
		
	$this->Open();
	$this->AliasNbPages();
	$this->AddPage();
	$this->SetFont('Arial','B',10);
	$this->SetAutoPageBreak(0);
	//Inicializo las bases:
	$this->asignar_base1(40);
	$this->asignar_base2(151);
	$this->AliasNbPages("total_pag"); 
	$this->line(5,$this->recuperar_base1(),205,$this->recuperar_base1());
	$this->SetFont('Arial','B',14);

	$this->setxy(150,$this->recuperar_base1()+2);
	$this->cell(30,6,'Fecha');
	$this->setxy(5,$this->recuperar_base1()+10);
	$this->cell(30,6,'Proveedor ');
	$this->setxy(5,$this->recuperar_base1()+18);
	$this->cell(30,6,'Vendedor ');
	$this->setxy(7,$this->recuperar_base1()+28);
	$this->SetFont('Arial','',12);
	
	$this->SetFont('Arial','B',14);
	$this->setxy(5,$this->recuperar_base1()+50);
	$this->cell(35,6,'Forma de Pago ');
	$this->setxy(150,$this->recuperar_base1()+50);
	$this->cell(35,6,'Entrega');
	$this->setxy(5,$this->recuperar_base1()+60);
	$this->cell(35,6,'Lugar y Forma de Entrega');
	$this->Rect(5,$this->recuperar_base1()+68,200,30);
	$this->setxy(5,$this->recuperar_base1()+99);
	
	$this->SetFont('Arial','B',11);
	$this->setxy(5,$this->recuperar_base1()+105);
	$this->cell(125,6,'Descripción','RLTB',0,'C');
	$this->setxy(130,$this->recuperar_base1()+105);
	$this->cell(20,6,'Cantidad','RLTB',0,'C');
	$this->setxy(150,$this->recuperar_base1()+105);
	$this->cell(30,6,'Unitario','RLTB',0,'C');
	$this->setxy(180,$this->recuperar_base1()+105);
	$this->cell(25,6,'Subtotal','RLTB',0,'C');
	
} //de la funcion dibujar_planilla

function guardar_servidor($string) {
//funcion nueva que me permite guadar en un directorio ./PDF
//si la orden de produccion existe cre un archivo.old
//si no existe la guarda normalmente
/*if ($mail){
  mkdirs("./PDF");
if (file_exists("$string")) {
	                       copy("$string","$string.old");
	                       $this->output("./PDF/$string");
                           }
                           else {
                           	     $this->output("./PDF/$string");
                                 }

}
else   
  //no se guarda el archivo en el servidor  */
 //pide confirmacion para guardar
 $this->output("$string",true,false);                                                   
}//fin de funcion
                                    
function Footer()
{global $nro_orden;
 //ir a 1.2 cm del final de la hoja
    $this->SetY(-12);
    //letra italica
    $this->SetFont('Arial','I',8);
    //imprime nro de pagina y total de paginas 
    $this->Cell(0,10,'Orden de Pago Nº '.$nro_orden.' - Página '.$this->PageNo().'/total_pag',0,0,'C');
	
}   
                               
}//fin de la clase
?>