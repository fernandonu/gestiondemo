<?
require_once("../../config.php");
require(LIB_DIR.'/fpdf.php');

class resultado extends FPDF
{
  //variables
  var $basea1;
  var $basea2;
  var $basea3;
  var $basea4;
  
  //funciones    
  function asignar_basea1($x) {
	$this->basea1=$x;
  }
  
  function asignar_basea2($x) {
	 $this->basea2=$x;
}

 function asignar_basea3($x) {
	 $this->basea3=$x;
}

function asignar_basea4($x) {
	 $this->basea4=$x;
}

function recuperar_basea1(){
	return $this->basea1;
}

function recuperar_basea2(){
	return $this->basea2;
}

function recuperar_basea3(){
	return $this->basea3;
}

function recuperar_basea4(){
	return $this->basea4;
}

function resultado()
{$this->FPDF('P','cm','Legal');
 $this->SetMargins(0,0);
 $this->SetFont('Arial','B',8);
 $this->asignar_basea1(3);
 $this->asignar_basea2($this->recuperar_basea1()+1.5); 
 $this->asignar_basea3($this->recuperar_basea2()+1);  
 $this->asignar_basea4($this->recuperar_basea3()+3);
}

function id_lic($lic)
{
 $this->setxy(8.2,$this->recuperar_basea1());
 $this->SetFont('Arial','B',10);
 $this->SetLineWidth('0.055');
 $this->cell(4.5,0.5,"Lic. ID: ".$lic,1); 
 
}

function entidad($entidad)
{
 $this->setxy(2,$this->recuperar_basea1()+1);
 $this->Write(0,"Entidad: ".$entidad);
}

function oferente()
{
 $this->setxy(2,$this->recuperar_basea2());
 $this->SetFont('Arial','',16);
 $this->cell(17,0.6,"Oferente:".$lic,1);
 $this->setxy(15,$this->recuperar_basea2());
 $this->SetFont('Arial','',12);
 $this->cell(17,0.6,"ISO 9001:    SI   NO");
}

function tipos()
{$this->setxy(2,$this->recuperar_basea3());
 $this->SetLineWidth('0.055');
 $this->cell(6.8,2,"",1);
 $this->SetFont('Arial','B',12);
 $this->setxy(2,$this->recuperar_basea3()+0.3);
 $this->cell(0,0,"Tipos");
 $this->SetLineWidth('0.03');
 $this->Line(3.5,$this->recuperar_basea3(),3.5,$this->recuperar_basea3()+2);
 $this->SetLineWidth('0.055');
 $this->Line(4.6,$this->recuperar_basea3(),4.6,$this->recuperar_basea3()+2);
 $this->SetLineWidth('0.03');
 $this->Line(3.5,$this->recuperar_basea3()+0.5,8.8,$this->recuperar_basea3()+0.5);
 $this->Line(3.5,$this->recuperar_basea3()+1,8.8,$this->recuperar_basea3()+1);
 $this->Line(3.5,$this->recuperar_basea3()+1.5,8.8,$this->recuperar_basea3()+1.5);
 $this->SetFont('Arial','',11);
 $this->setxy(3.8,$this->recuperar_basea3()+0.3);
 $this->cell(0,0,"1");
 $this->setxy(4.6,$this->recuperar_basea3()+0.3);
 $this->cell(0,0,"PC de Escritorio");
 $this->setxy(3.8,$this->recuperar_basea3()+0.8);
 $this->cell(0,0,"2");
 $this->setxy(4.6,$this->recuperar_basea3()+0.8);
 $this->cell(0,0,"Servidor");
 $this->setxy(3.8,$this->recuperar_basea3()+1.3);
 $this->cell(0,0,"3");
 $this->setxy(4.6,$this->recuperar_basea3()+1.3);
 $this->cell(0,0,"Impresora");
 $this->setxy(3.8,$this->recuperar_basea3()+1.8);
 $this->cell(0,0,"4");
 $this->setxy(4.6,$this->recuperar_basea3()+1.8);
 $this->cell(0,0,"Otro");
 $this->SetFont('Arial','B',10);
 $this->setxy(15,$this->recuperar_basea3()+0.4);
 $this->cell(0,0,"Moneda");
 $this->SetLineWidth('0.055');
 $this->setxy(15,$this->recuperar_basea3()+0.6);
 $this->cell(0.5,0.5,"",1);
 $this->setxy(15,$this->recuperar_basea3()+1.1);
 $this->cell(0.5,0.5,"",1);
 $this->setxy(15.5,$this->recuperar_basea3()+0.9);
 $this->SetFont('Arial','',10);
 $this->cell(0,0," $");
 $this->setxy(15.5,$this->recuperar_basea3()+1.4);
 $this->cell(0,0," U\$S");
 
}

function tabla_desc()
{$this->SetLineWidth('0.055');
 $this->setxy(2,$this->recuperar_basea4());
 $this->cell(17,23.4,"",1);
 $this->Line(2,$this->recuperar_basea4()+0.9,19,$this->recuperar_basea4()+0.9);
 $i=1;
 $desp=0.9;
 $this->SetLineWidth('0.03');
 while ($i<25)
 {$this->Line(2,$this->recuperar_basea4()+0.9+($i*$desp),19,$this->recuperar_basea4()+0.9+($i*$desp));
  $i++;
 }
 $this->Line(3.4,$this->recuperar_basea4(),3.4,$this->recuperar_basea4()+23.4);
 $this->Line(4.6,$this->recuperar_basea4(),4.6,$this->recuperar_basea4()+23.4);
 $this->Line(13.1,$this->recuperar_basea4(),13.1,$this->recuperar_basea4()+23.4);
 $this->Line(15.1,$this->recuperar_basea4(),15.1,$this->recuperar_basea4()+23.4);
 $this->SetLineWidth('0.055');
 $this->Line(17.1,$this->recuperar_basea4(),17.1,$this->recuperar_basea4()+23.4);
 $this->SetFont('Arial','',9);
 $this->setxy(2,$this->recuperar_basea4()+0.5);
 $this->cell(0,0,"Renglón");
 $this->setxy(3.5,$this->recuperar_basea4()+0.5);
 $this->cell(0,0,"Cant.");
 $this->setxy(7.8,$this->recuperar_basea4()+0.5);
 $this->cell(0,0,"Descripción");
 $this->setxy(13.5,$this->recuperar_basea4()+0.5);
 $this->cell(0,0,"Marca");
 $this->setxy(15.5,$this->recuperar_basea4()+0.5);
 $this->cell(0,0,"Precio");
 
 $this->setxy(17.5,$this->recuperar_basea4()+0.5);
 $this->cell(0,0,"Tipo");
  
}

 } //fin clase extends
 
function generar_pdf()
{global $parametros;
 $sol=new resultado();
 $sol->Open();
 $sol->AddPage();
 $sol->id_lic($parametros['ID']);
 $sol->entidad($parametros['entidad']);
 $sol->Oferente();
 $sol->tipos();
 $sol->tabla_desc();
 $sol->Output();
 //$sol->Output("C:\Documents and Settings\Banco Ciudad\bancociudad\modulos\clientes\pdf\formulario_".$_POST['id_titular'].".pdf");
 
}
generar_pdf();
?>