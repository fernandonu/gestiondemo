<?php

define('FPDF_FONTPATH','font/');
require('../../lib/fpdf.php');

class remito_interno extends FPDF
{
	var $base1;
	var $base2;
	var $base3;
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

    function asignar_base3($x) {
	    $this->base3=$x;
      }

    function recuperar_base3(){
	    return $this->base3;
      }
    
    function asignar_base4($x) {
	    $this->base4=$x;
      }

    function recuperar_base4(){
	    return $this->base4;
      }    

    function Header() {
        global $nro_remito, $id_licitacion; 
	
	    $this->cant=0;
		$this->flag=1;
		$this->rect(5,5,200,290);
		/*
		$this->Line(90,5,90,40);				
		$this->SetFont('Arial','B',32);
		$this->setxy(95,9);
		$this->Cell(20,20,"R");
		*/
		$this->asignar_base1(40);
		$this->line(5,$this->recuperar_base1(),205,$this->recuperar_base1());
		$this->Image('../ordprod/logo_coradir_prod.png',115,10,85);
		$this->line(110,5,110,32);
		
		$this->line(5,32,205,32);
		$this->SetFont('Arial','B',24);
    	$this->setxy(15,10);
		$this->Cell(80,20,"Remito i.");
		/* Celda con fondo gris que muestra a que esta asociado el remito*/
		$this->SetFont('Arial','B',18);
    	$this->setxy(5,31);
    	$this->SetFillColor(234,234,234);
		if ($id_licitacion) 
   			$this->cell(200,9,'Licitación ID :  ',1,1,'L',1);
		else 
  			$this->cell(200,9,' ',1,1,'L',1);
		/* Pasa los datos cuando el remito ocupa mas de una pagina */    
		if($this->PageNo()!=1){
			 $this->setxy(5,45);
			 $this->SetFontSize(11);
			 $this->cell(0,0,"Remito i.".$nro_remito." - Continuación",0,0,'C');
			 $this->nro_remito_interno($nro_remito);
			 if ($id_licitacion) 
	 			$this->pasa_id_licitacion($id_licitacion);
			} 
		$this->Ln(20);
		}	

	function nro_remito_interno($nro) {
		$this->SetFont('Arial','B',24);
   		$this->setxy(50,10);
		$this->cell(80,20,$nro);
	}

	function pasa_id_licitacion($id) {
		$this->SetFont('Arial','B',18);
		$this->setxy(50,31);
		$this->cell(80,9,$id);
	}

	function fecha($fecha) {
		$this->SetFont('Arial','',14);
		$this->setxy(170,$this->recuperar_base1()+2);
		$this->cell(26,6,$fecha,1,0,R);
	}
	
	function pasa_entrega($string) {
		$this->SetFont('Arial','',14);
		$this->setxy(5,70);
	//Control del tamaño del string de lugar_entrega para que
    //no se pase de largo en el pdf
    $add="";
	$largo=strlen($string);
	$cant_nl=str_count_letra("\n",$string);
	$res=ceil($largo/79);
	while($cant_nl+$res>6)
	{$add=" ...";
	 $string=substr($string,0,$largo-3);
	 $largo=strlen($string);
	 $cant_nl=str_count_letra("\n",$string);
	 $res=ceil($largo/79);
	} 
	$string.=$add; 
	$this->MultiCell(200,5,$string);
   	}

	function producto($descripcion,$cantidad) {
		// 
		//Control para que haga un salto de pagina, si el producto generado
		//no entra en la pagina actual.

		$largo=strlen($descripcion);
		$cant_nl=str_count_letra("\n",$descripcion);
		$res=ceil($largo/55);
		$nro_pixeles_posibles=($res+$cant_nl)*6;
		if(($this->recuperar_base4()+$nro_pixeles_posibles)>290){
			$this->SetAutoPageBreak(0);
			$this->AddPage();
			$this->asignar_base4(55);
			$this->flag=0;
//esto esta agregado para que cuando se pase a la otra 
//pagina los productos muestre el titulo de descripcion y cantidad			
		$this->SetFont('Arial','B',14);
   		$this->setxy(5,$this->recuperar_base1()+9);
		$this->cell(160,6,'Descripción ','RLTB',0,'L');
		$this->setxy(165,$this->recuperar_base1()+9);
		$this->cell(40,6,'Cantidad ','RLTB',0,'L');	
	 	}
 	
       	$this->setxy(5,$this->recuperar_base4());
		$this->SetFont('Arial','B',9);
		$y_inicial=$this->GetY();
	
		$this->MultiCell(160,6,$descripcion,'TRLB','L');
    	$y_posterior=$this->GetY();
    
		$this->setxy(165,$this->recuperar_base4());
		$this->cell(40,6,$cantidad,'TRBL',0,'C');
		
		$aux=$this->recuperar_base4()+ ($y_posterior-$y_inicial);
		$this->asignar_base4($aux);
		$this->cont++;
	}

	function dibujar_planilla() {
		$this->Open();
		$this->AliasNbPages();
		$this->AddPage();
		$this->SetFont('Arial','B',10);
		$this->SetAutoPageBreak(0);
		//Inicializo las bases:
		$this->asignar_base1(40);
		$this->asignar_base2(220);
		$this->asignar_base4(120);
		$this->AliasNbPages("total_pag"); 
		$this->line(5,$this->recuperar_base1(),205,$this->recuperar_base1());
		$this->SetFont('Arial','B',14);
		$this->setxy(130,$this->recuperar_base1()+2);
		$this->cell(30,6,'Fecha');
		$this->setxy(5,$this->recuperar_base1()+10);
		$this->SetFont('Arial','B',14);
    	$this->setxy(15,$this->recuperar_base1()+20);
		$this->cell(35,6,'Entrega ');
		$this->Rect(5,$this->recuperar_base1()+28,200,30);
 	 	$this->SetFont('Arial','B',14);
   		$this->SetFillColor(234,234,234);
		$this->setxy(5,$this->recuperar_base1()+65);
		$this->cell(200,9,'Productos ',1,1,'L',1);
    	$this->setxy(5,$this->recuperar_base1()+74);
		$this->cell(160,6,'Descripción ','RLTB',0,'L');
		$this->setxy(165,$this->recuperar_base1()+74);
		$this->cell(40,6,'Cantidad ','RLTB',0,'L');
		//$this->line(5,$this->recuperar_base2(),205,$this->recuperar_base2());
	} //de la funcion dibujar_planilla

	function _final() {
		$this->asignar_base3(210);
		$this->SetFont('Arial','',12);
		$this->setxy(7,$this->recuperar_base3()+25);	
		$this->cell(100,5,'Casa Matriz Ruta 3 km. 1.4 (Sur)');
	    $this->setxy(7,$this->recuperar_base3()+30);
	    $this->cell(100,5,'D (5700) BQJ San Luis ');
 	    $this->setxy(7,$this->recuperar_base3()+35);
	    $this->cell(100,5,'Fax (02652) 435940 / 431134');	
	
	    $this->SetFont('Arial','',12);
	    $this->setxy(7,$this->recuperar_base3()+42);	
	    $this->cell(100,5,'Suc. Buenos Aires Patagones 2538 ');
	    $this->setxy(7,$this->recuperar_base3()+47);
	    $this->cell(100,5,'CPA: C1282ACD Bs. As. ');
 	    $this->setxy(7,$this->recuperar_base3()+52);
	    $this->cell(100,5,'Tel/Fax (011) 5354-0300 y rotativas');
    
	    $this->setxy(7,$this->recuperar_base3()+60);
	    $this->cell(100,5,'C.U.I.T: 30-67338016-2');
	    $this->setxy(7,$this->recuperar_base3()+65);
	    $this->cell(100,5,'I.B. C.M.: N° 919-680869-2');
	    
	    
	    
	    
		$this->setxy(135,$this->recuperar_base3()+28);
		$this->SetFont('Arial','B',14);
		$this->cell(60,6,'Recibí conforme',1,0,'C');
		$this->setxy(135,$this->recuperar_base3()+33);
		$this->cell(60,25,' ',1);
    }
	
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
                                    
	function Footer(){
		global $nro_remito;
		//ir a 1.2 cm del final de la hoja
    	$this->SetY(-12);
    	//letra italica
    	$this->SetFont('Arial','I',8);
    	//imprime nro de pagina y total de paginas 
    	$this->Cell(0,10,'Remito i.'.$nro_remito.' - Página '.$this->PageNo().'/total_pag',0,0,'C');
	}   
                               
}//fin de la clase
?>