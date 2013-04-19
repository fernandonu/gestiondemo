<?php
//Clase que me crea un documento PDF que representa una ORDEN DE PRODUCCION DE CORADIR
require_once("../../config.php");
require(LIB_DIR.'/fpdf.php');

class orden_produccion extends FPDF
{
  var $base1;
  var $base2;
  var $base3;
  var $base_descripcion;
  var $base_nro_serie;
  var $desp_der_nroserie;
  var $desp_kitatx;


function asignar_base1($x) {
	 $this->base1=$x;
}

function asignar_base2($x) {
	 $this->base2=$x;
}

function asignar_base3($x) {
	 $this->base3=$x;
}

function recuperar_base1(){
	return $this->base1;
}

function recuperar_base2(){
	return $this->base2;
}

function recuperar_base3(){
	return $this->base3;
}


function Header()
 
{   $this->desp_kitatx=183.5;//177.5
	$this->base_nro_serie=134;
    $this->desp_der_nroserie=150;
	$this->base_descripcion=129;
	$this->SetFont('Arial','B',22);
    $this->line(110,5,110,32);
	$this->setxy(15,8);
    $this->Cell(80,10,"Orden de Producción");
	$this->setxy(15,14);
	$this->cell(80,20,"Nro :  ");
	$this->Image('logo_coradir_prod.png',115,10,90);
	//$this->setxy(130,24);
	//$this->cell(30,8,"Internet  Service  Provider");
	$this->Ln(20);
	$this->SetAutoPageBreak(0,0);
}
function nro_produccion($string)  { 
	$this->SetFont('Arial','B',22);
	$this->setxy(40,14);
	$this->cell(80,20,$string); 
}

function pasa_id_lic($string) {
	$this->SetFont('Arial','B',18);
	$this->setxy(50,$this->recuperar_base1());
	$this->cell(30,8,$string);      
}


function fecha_inicio($fecha) {
	$this->SetFont('Arial','B',13);
	$this->setxy(40,$this->recuperar_base1()+16);
	$this->cell(30,8,$fecha);      
}
	
function fecha_entrega($fecha)	{
	$this->SetFont('Arial','B',15);
	$this->setxy(140,$this->recuperar_base1()+16); 
    $this->cell(30,8,$fecha);    
}

function ensamblador($nombre) {
	$this->SetFont('Arial','B',12);
	$this->setxy(40,$this->recuperar_base1()+25);            
	$this->cell(30,8,$nombre);
}
    
function cliente($nombre) {
	$longitud=strlen($nombre);  
	if($longitud < 78) {
		$this->SetFont('Arial','B',13);
		$this->setxy(45,$this->recuperar_base1()+34);
		$this->cell(150,8,$nombre);
	}
	else {  //si hay 2 renglones
		if ($longitud < 146) { 
			$this->SetFont('Arial','B',11);
			$this->setxy(45,$this->recuperar_base1()+33); 	
			$primera_parte=substr($nombre,0,75);
			$this->cell(170,8,$primera_parte);
			$segunda_parte=substr($nombre,75,75);
			$this->setxy(45,$this->recuperar_base1()+39); 	
			//$this->cell(170,8,$longitud);
			$this->cell(170,8,$segunda_parte);
		}
		else {
			$longitud=strlen($nombre);
			$this->SetFont('Arial','B',9);
			$primera_parte=substr($nombre,0,90);
			$this->setxy(45,$this->recuperar_base1()+33); 	
			$this->cell(130,8,$primera_parte);
			$this->setxy(45,$this->recuperar_base1()+36); 	
			$segunda_parte=substr($nombre,90,90);
			$this->cell(130,8,$segunda_parte);
			if($longitud > 189) {
				$tercera_parte=substr($nombre,180,130);
				$this->setxy(45,$this->recuperar_base1()+39); 	
				$this->cell(130,8,$tercera_parte);
			}
		}
	}
}

function lugar_entrega($nombre) {
	
/*	$this->SetFont('Arial','B',13);
	$this->setxy(45,90);					
	$this->cell(30,8,$string);
   esta igual que cliente*/ 	
	$longitud=strlen($nombre);  
	if($longitud < 78) {
		$this->SetFont('Arial','B',13);
		$this->setxy(45,$this->recuperar_base1()+48);
		$this->cell(130,8,$nombre);
	}
	else {  //si hay 2 renglones
		if ($longitud < 146) { 
			$this->SetFont('Arial','B',11);
			$this->setxy(45,$this->recuperar_base1()+48); 	
			$primera_parte=substr($nombre,0,75);
			$this->cell(170,8,$primera_parte);
			$segunda_parte=substr($nombre,75,75);
			$this->setxy(45,$this->recuperar_base1()+52); 	
			//$this->cell(170,8,$longitud);
			$this->cell(170,8,$segunda_parte);
		}
		else {
			$longitud=strlen($nombre);
			$this->SetFont('Arial','B',9);
			$primera_parte=substr($nombre,0,90);
			$this->setxy(45,$this->recuperar_base1()+48); 	
			$this->cell(130,8,$primera_parte);
			$this->setxy(45,$this->recuperar_base1()+51); 	
			$segunda_parte=substr($nombre,90,90);
			$this->cell(130,8,$segunda_parte);
			if($longitud > 189) {
				$tercera_parte=substr($nombre,180,130);
				$this->setxy(45,$this->recuperar_base1()+54); 	
				$this->cell(130,8,$tercera_parte);
			}
		}
	}
}

function cantidad($cant) {
	$this->SetFont('Arial','B',13);
	$this->setxy(33,$this->recuperar_base2()+10);			
	$this->cell(30,8,$cant);
}
function renglon($cant) {
	$this->SetFont('Arial','B',13);
	$this->setxy(133,$this->recuperar_base2()+10);			
	$this->cell(30,8,$cant);
}

function producto($string) {
	$this->SetFont('Arial','B',10);
    $this->setxy(30,$this->recuperar_base2());
    $this->cell(30,8,$string);
    
}

function modelo($modelo) {
   $this->SetFont('Arial','B',10);
   $this->setxy(82,$this->recuperar_base2());
   $this->cell(30,8,'Modelo: ');
   $this->setxy(100,$this->recuperar_base2());
   $this->cell(30,8,$modelo);
   
}
function sistema_operativo($string) {
/*	
	$this->SetFont('Arial','B',8);
	$this->setxy(5,$this->base_descripcion);
	$this->cell(30,8,'Sistema Operativo ');
	$this->setxy(50,$this->base_descripcion);					
	$this->cell(30,8,$string);
	$this->base_descripcion+=4;
*/
$this->setxy(5,$this->recuperar_base2()+23);
$this->SetFont('Arial','B',10);
$this->cell(200,8,$string);

}
function items($cant,$prod,$string) {
	
	$this->SetFont('Arial','B',8);
	$this->setxy(5,$this->base_descripcion);
	$this->cell(30,8,$cant."  ".$prod);
	if (strlen($string)>50) {
		$str1=substr($string,0,50);
		$str2=substr($string,50);
		$this->setxy(50,$this->base_descripcion);					
		$this->cell(30,8,$str1);
		$this->base_descripcion+=4;
		$this->setxy(50,$this->base_descripcion);					
		$this->cell(30,8,$str2);
		$this->base_descripcion+=4;
	}
	else {
		$this->setxy(50,$this->base_descripcion);					
		$this->cell(30,8,$string);
		$this->base_descripcion+=4;
	}
}

function adicionales($string) {
	
	$this->SetFont('Arial','B',8);
	$this->setxy(5,$this->base_descripcion);
	$this->cell(30,8,'Adicionales');
	$lineas=ceil(strlen($string)/60);
	$str[1]=substr($string,0,60);
	$str2=substr($string,60);
	$this->setxy(50,$this->base_descripcion);					
	$this->cell(30,8,$str[1]);
	for ($i=2;$i<=$lineas;$i++) {
		$str[$i]=substr($str2,0,60);
		$str2=substr($str2,60);
		$this->base_descripcion+=4;
		$this->setxy(50,$this->base_descripcion);					
		$this->cell(30,8,$str[$i]);
		if ($i==5) break;
	}
	if ($i==5) {
		$this->SetFont('Arial','B',12);
		$this->setxy(150,$this->base_descripcion-4);
		$this->cell(30,8,"Para mas información");
		$this->setxy(150,$this->base_descripcion);
		$this->cell(30,8,"ver orden de producción");
		$this->SetFont('Arial','B',8);
	}
	/*if (strlen($string)>50) {
		$str1=substr($string,0,50);
		$str2=substr($string,50);
		$this->setxy(50,$this->base_descripcion);					
		$this->cell(30,8,$str1);
		$this->base_descripcion+=4;
		$this->setxy(50,$this->base_descripcion);					
		$this->cell(30,8,$str2);
		$this->base_descripcion+=4;
	}
	else {
		$this->setxy(50,$this->base_descripcion);					
		$this->cell(30,8,$string);
		$this->base_descripcion+=4;
	}*/
}
// Accesorios para el kit atx

function accesorio($tipo,$string) {
	$this->setxy(50,$this->desp_kitatx);
    $this->cell(30,8,$tipo);
    $this->setxy(86,$this->desp_kitatx);						
    $this->cell(30,8,$string);
    $this->desp_kitatx+=4;
}

function numero_de_serie($string) {
	
   $this->SetFont('Arial','B',10);	
   $this->setxy($this->desp_der_nroserie,$this->base_nro_serie);					
   $this->cell(30,8,$string);
   $this->base_nro_serie+=4;
} 

/*
este metodo lo sacamos, por las dudas no lo borramos

function adicionales_kit_atx($string) {
	$this->setxy(50,$this->desp_kitatx);
    $this->cell(30,8,'Adicionales');
    $this->setxy(86,$this->desp_kitatx);						
    $this->cell(30,8,$string);
    $this->desp_kitatx+=4;
    
}*/	

function etiqueta($marca) {
	$this->SetFont('Arial','B',8);
	$this->setxy(26,$this->recuperar_base3()+19);
	$this->cell(30,8,$marca);
}




function monitor($monitor) {
	
	$this->SetFont('Arial','B',14);
	$this->setxy(5,$this->recuperar_base3()-19);
	$this->cell(50,8,'Monitor: '.$monitor);
}


function garantia($garantia) {
    $this->SetFont('Arial','B',14);
	$this->setxy(5,$this->recuperar_base3()-9);
	$this->cell(50,8,'Garantía: '.$garantia);
}






function dibujar_planilla() {
	
$this->Open();
$this->AliasNbPages();
$this->AddPage();
$this->asignar_base1(32);
$this->asignar_base2(93);
$this->asignar_base3(230);
//$this->rect(5,5,200,290);
$this->rect(5,5,200,285);
$this->line(5,$this->recuperar_base1(),205,$this->recuperar_base1());
//orden de produccion
$this->SetFont('Arial','B',18);
$this->setxy(5,$this->recuperar_base1());
$this->SetFillColor(234,234,234);
$this->cell(200,8,'Licitación ID :  ',1,1,'L',1);
$this->line(5,$this->recuperar_base1()+8,205,$this->recuperar_base1()+8);
//orden de produccion
//fechas
$this->SetFont('Arial','B',10);
$this->setxy(5,$this->recuperar_base1()+8);
$this->cell(30,8,'Fechas');
$this->line(5,$this->recuperar_base1()+14,205,$this->recuperar_base1()+14);
//fechas
//Inicio Entrega
//73
$this->line(5,$this->recuperar_base1()+25,205,$this->recuperar_base1()+25);
$this->SetFont('Arial','B',13);
$this->setxy(5,$this->recuperar_base1()+16);
$this->cell(30,8,'Inicio');
   //linea vertical
   $this->line(100,$this->recuperar_base1()+14,100,$this->recuperar_base1()+25);
   $this->SetFont('Arial','B',13);
   $this->setxy(106,$this->recuperar_base1()+16); 
   $this->cell(30,8,'Entrega');
 
   //linea vertical
//Inicio Entrega
//ensamblador
$this->SetFont('Arial','B',11);
$this->setxy(5,$this->recuperar_base1()+25);
$this->SetFillColor(234,234,234);
$this->cell(200,8,'Ensamblador: ',1,1,'L',1);
$this->line(5,$this->recuperar_base1()+33,205,$this->recuperar_base1()+33);
//ensamblador
//vacio
//$this->line(5,$this->recuperar_base1()+36,205,$this->recuperar_base1()+36);
//vacio
//ciente final
$this->SetFont('Arial','B',13);
$this->setxy(5,$this->recuperar_base1()+34);
$this->cell(30,8,'Cliente Final :');
$this->line(5,$this->recuperar_base1()+47,205,$this->recuperar_base1()+47);
//cliente final
//Lugar de entrega
$this->SetFont('Arial','B',13);
$this->setxy(5,$this->recuperar_base1()+48);
$this->cell(30,8,'Lugar de Entrega: ');
$this->line(5,$this->recuperar_base1()+61,205,$this->recuperar_base1()+61);	

//********************************Aca termina la primera base****************
//********************************Aca empieza la segunda base****************
//lugar de entrega
//producto
//computadora
//modelo
$this->SetFont('Arial','B',10);
$this->setxy(5,$this->recuperar_base2());
$this->SetFillColor(234,234,234);
$this->cell(200,8,'PRODUCTO: ',1,1,'L',1);
   //linea vertical
   //$this->line(80,102,80,109);
   $this->SetFont('Arial','B',10);
   //linea vertical
//computadora
//producto
//modelo   
//cantidad
$this->line(5,$this->recuperar_base2()+8,205,$this->recuperar_base2()+8);
//$this->line(5,116,205,116);
$this->setxy(5,$this->recuperar_base2()+10);
$this->SetFont('Arial','B',13);
$this->cell(30,8,'CANTIDAD : ');
$this->SetFont('Arial','B',10);
//cantidad
//renglon
$this->line(100,$this->recuperar_base2()+8,100,$this->recuperar_base2()+19);
$this->setxy(105,$this->recuperar_base2()+10);
$this->SetFont('Arial','B',13);
$this->cell(30,8,'RENGLON : ');
$this->SetFont('Arial','B',10);
$this->line(5,$this->recuperar_base2()+30,205,$this->recuperar_base2()+30);
//renglon
//sistema operativo
$this->line(5,$this->recuperar_base2()+19,205,$this->recuperar_base2()+19);
$this->setxy(5,$this->recuperar_base2()+18);
$this->SetFont('Arial','B',12);
$this->cell(30,8,'Sistema operativo instalado : ');
//sistema operativo
//descripicion del producto
$this->line(5,$this->recuperar_base2()+35,205,$this->recuperar_base2()+35);
$this->SetFont('Arial','B',10);
$this->setxy(5,$this->recuperar_base2()+29);
$this->cell(30,8,'Descripicion del Producto ');
   //linea vertical  cuadrado alrededor de los numeros de serie
   $this->line(135,$this->recuperar_base2()+30,135,$this->recuperar_base2()+67);
   $this->line(135,$this->recuperar_base2()+67,205,$this->recuperar_base2()+67);
   //linea vertical  cuadrado alrededor de los numeros de serie
   
$this->setxy(137,$this->recuperar_base2()+29);
$this->cell(30,8,'Nº de Serie de los Equipos');   
//descripicion del producto
//linea divisoria entre marca y modelo
$this->line(50,$this->recuperar_base2()+35,50,$this->recuperar_base2()+86);
//linea divisoria entre marca y modelo
//Empiezo con la descripicion del producto
//rectangulo del kit atx
$this->Rect(5,$this->recuperar_base2()+92,200,25);
//prolongacion de la linea vertical que divide 
//descripcion del producto ( la primera )
$this->line(50,$this->recuperar_base2()+86,50,$this->recuperar_base2()+117);
//lineas horizontales (grilla).
$this->line(50,$this->desp_kitatx+ 5.5,205,$this->desp_kitatx+ 5.5);	
$this->line(50,$this->desp_kitatx+ 9.5,205,$this->desp_kitatx+ 9.5);	
$this->line(50,$this->desp_kitatx+13.5,205,$this->desp_kitatx+ 13.5);	
$this->line(50,$this->desp_kitatx+ 17.5,205,$this->desp_kitatx+ 17.5);	
$this->line(50,$this->desp_kitatx+ 21.5,205,$this->desp_kitatx+ 21.5);	

//prolongacion de la linea vertical que divide N serie de los 
//equipos (la primera linea de la grilla).

$this->setxy(5,$this->recuperar_base2()+93);
$this->cell(30,8,'KIT ATX');
//$this->line(149,203,149,207);
//ultimo rectangulo
////********************aca termina la segunda base*******************************/
//*********************aca empieza la tercera base****************************/
$this->SetFont('Arial','B',14);

//$this->Rect(5,$this->recuperar_base3(),200,75);

//linea que divide monitor de garantia
$this->Line(5,$this->recuperar_base3()-10,205,$this->recuperar_base3()-10);

$this->setxy(5,$this->recuperar_base3());
$this->SetFillColor(234,234,234);
$this->cell(200,8,'Se deberá colocar a cada computadora armada',1,1,'L',1);


///$this->line($this->recuperar_base3(),100,$this->recuperar_base3(),179);
//linea que divide entre bolsa y cosas que debe llevar
//esta linea tiene que cambiar de posicion para nueva version
$this->line(97,$this->recuperar_base3()+ 8,97,$this->recuperar_base3()+ 60);
}

function accesorios_adicionales($sistema_operativo,$modem)
{
	
$this->SetFont('Arial','B',8);

$this->setxy(5,$this->recuperar_base3()+9);
$this->cell(100,8,'Etiqueta Logo: ');	

$this->setxy(5,$this->recuperar_base3()+13);
$this->cell(100,8,'Faja de garantía VOID');	

$this->setxy(5,$this->recuperar_base3()+17);
$this->cell(100,8,'Etiqueta de Nº de serie del CPU en la parte inferior');

$this->setxy(5,$this->recuperar_base3()+21);
$this->cell(100,8,'Product Key Sistema Operativo');

$this->setxy(5,$this->recuperar_base3()+25);
$this->cell(100,8,'Etiqueta Posterior "Eq.2"');	

$this->setxy(100,$this->recuperar_base3()+9);
$this->cell(100,8,'Bolsa de Accesorios: ');	

$this->setxy(110,$this->recuperar_base3()+13); //27
$this->cell(30,8,'Mouse ');	

$this->setxy(110,$this->recuperar_base3()+17); //27
$this->cell(30,8,'Pad');	

$this->setxy(110,$this->recuperar_base3()+21);
$this->cell(100,8,'Manual de Mother');	

$this->setxy(110,$this->recuperar_base3()+25);
$this->cell(40,8,'Drivers de Mother y adicionales');	

$this->setxy(110,$this->recuperar_base3()+29);
$this->cell(100,8,'Hoja de Garantía CDR Computers.');	

$this->setxy(110,$this->recuperar_base3()+33);
$this->cell(120,8,'Cable 220V (ficha tipo legrand 220v)');

//controles para sistema operativo y modem
$base_modem_sis=0;
if($sistema_operativo!="")
 {$base_modem_sis=4;
  $this->setxy(110,$this->recuperar_base3()+37);
  $this->cell(100,8,"Licencia:".$sistema_operativo);
 }

if($modem!=0)
{$base_modem_sis+=4;
 $this->setxy(110,$this->recuperar_base3()+41);
 $this->cell(100,8,'Cable de Telefonía p/Modem');
}

$this->setxy(110,$this->recuperar_base3()+$base_modem_sis+37);
$this->cell(100,8,'Manuales y Adicionales');

$this->setxy(100,$this->recuperar_base3()+$base_modem_sis+46);
$this->cell(200,8,'Caja de embalaje CDR con cinta de embalaje CDR');	

$this->setxy(100,$this->recuperar_base3()+$base_modem_sis+50);
$this->cell(80,8,'Etiqueta de caracteristicas y Nº de Serie del Equipo, exterior.');	
}

function guardar_servidor($string) {
//funcion nueva que me permite guadar en un directorio ./PDF
//si la orden de produccion existe cre un archivo.old
//si no existe la guarda normalmente
$path=enable_path("pdf/$string");
if (file_exists($path)) {
	                       copy($path,$path.".old");
	                       $this->output($path);
                           }
                           else {
                           	     $this->output($path);
                                 }

                                    }//fin de funcion
                                    

}

?>