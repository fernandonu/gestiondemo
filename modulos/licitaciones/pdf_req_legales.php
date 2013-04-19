<?
/*
Autor: GACZ

MODIFICADA POR
$Author: mari $
$Revision: 1.16 $
$Date: 2007/01/05 20:00:11 $
*/

//require_once("config_local.php");
$ID=$parametros['id_lic'];

		$sql = "SELECT licitacion.fecha_apertura,entidad.nombre as nombre_entidad,";
		$sql .= "distrito.nombre as nombre_distrito ";
		$sql .= "FROM (licitacion ";
		$sql .= "INNER JOIN entidad ";
		$sql .= "ON licitacion.id_entidad=entidad.id_entidad) ";
		$sql .= "INNER JOIN distrito ";
		$sql .= "ON entidad.id_distrito=distrito.id_distrito ";
		$sql .= "WHERE licitacion.id_licitacion=$ID";
		$result = $db->Execute($sql) or die($db->ErrorMsg());
		$distrito = $result->fields["nombre_distrito"];
		$entidad = $result->fields["nombre_entidad"];
//$OUTPUT.="/pdfs/"; //directorio donde se crearan los pdfs
$fecha = substr($result->fields["fecha_apertura"],0,4);
//$OUTPUT=UPLOADS_DIR."/Licitaciones/$distrito/$entidad/$fecha/$ID/";
$OUTPUT=UPLOADS_DIR."/Licitaciones/$ID/";
require(LIB_DIR.'/fpdf.php'); //clase principal

class prot_legal extends FPDF
{

 var $xoffset;
 var $yoffset; 
 var $h2;//mm alto de las celdas

 var $w1;//mm ancho del rectangulo en cliente
 
 var $chk_w; //mm ancho del checkbox, el alto es el de la fila
 var $pos_item;//posicion del item a imprimir

function prot_legal()
{
 $this->FPDF();
 //PARA HOJA A4
 $this->SetFont('Arial','B',8);
 $this->xoffset=15;
 $this->yoffset=20;
 $this->h2=5;//mm alto de las celdas

 
 $this->w1=30;//mm ancho del rectangulo en cliente
 $this->chk_w=5; //mm ancho del checkbox, el alto es el de la fila
 $this->pos_item=$this->yoffset+$this->h2*11;

}
function cliente($nbre_cl)
{
	$this->setxy($this->xoffset,$this->yoffset);
	$this->cell(45,$this->h2,"Cliente","LTRB"); 
	$this->cell(0,$this->h2,$nbre_cl,"LTRB"); 

}
function proced($str)
{
	$this->SetFont('Arial','B',8);
	$this->setxy($this->xoffset,$this->yoffset+($this->h2*2));
	$this->cell(45,$this->h2,"Procedimiento","LTRB"); 
	$this->cell(0,$this->h2,$str,"LTRB"); 
}
function fecha($str_fecha)
{
	$this->SetFont('Arial','B',8);
	$this->setxy($this->xoffset,$this->yoffset+$this->h2*4);
	$this->cell(45,$this->h2,"Fecha Apertura","LTRB"); 
	$this->cell(0,$this->h2,$str_fecha,"LTRB"); 
}
function hora ($str_hora)
{
	$this->SetFont('Arial','B',8);
	$this->setxy($this->xoffset,$this->yoffset+$this->h2*6);
	$this->cell(45,$this->h2,"Hora","LTRB"); 
	$this->cell(0,$this->h2,$str_hora,"LTRB"); 
}

function lugar($lugar)
{
	$this->SetFont('Arial','B',8);
	$this->setxy($this->xoffset,$this->yoffset+$this->h2*8);
	$this->cell(45,$this->h2,"Lugar","LTRB"); 
	$this->cell(0,$this->h2,$lugar,"LTRB"); 
}
function encabezados($enc_left, $enc_right)
{
	$this->SetFont('Arial','B',10);
	$this->setxy($this->xoffset,$this->yoffset+$this->h2*10);
//gris	$this->SetFillColor(200);
//verde
	$this->SetFillColor(0x00,0x99,0x00);

	$this->cell(75,$this->h2,$enc_left,"LTRB",null,'C',1);//con relleno 

	$this->cell($this->chk_w,$this->h2,"","LTRB"); 
	
	$this->cell(0,$this->h2,$enc_right,"LTRB",null,'C',1);//con relleno
	$this->SetFillColor(0);
}
function item($req_legal, $coment, $checked=0)
{
	$this->SetFont('Arial','B',8);
	$this->setxy($this->xoffset,$this->pos_item);
	$this->cell(75,$this->h2,$req_legal,"LTRB");

	//el cuadro de checked
	$this->cell($this->chk_w,$this->h2,null,"LTRB");

if ($checked)
{	//lo vuelvo donde estaba antes de pintar el checked
	$this->setxy($this->xoffset+75,$this->pos_item);
	$this->setxy($this->GetX()+1.5,$this->GetY()+1.5);	
	$this->cell(2,2,null,null,null,null,1);//con relleno
	$this->setxy($this->GetX()+1.5,$this->GetY()-1.5);	
}
	
	$this->cell(0,$this->h2,$coment,"LTRB"); 
	$this->pos_item+=$this->h2;
}

}

$sql="select * from protocolo_leg join items_pl on protocolo_leg.id_prolegal=$id_prolegal and protocolo_leg.id_licitacion=$ID and protocolo_leg.id_prolegal=items_pl.id_prolegal";
$resultado1 = $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);

$legal=new prot_legal();
$legal->Open();
$legal->AddPage();

$legal->cliente($resultado1->fields['entidad']);
$legal->proced($resultado1->fields['procedimiento']);
$legal->fecha($resultado1->fields['fecha_aper']);
$legal->hora($resultado1->fields['hora']);
$legal->lugar($resultado1->fields['lugar']);
$legal->encabezados("Requisitos Legales","Comentarios");
$resultado1->Move(0);
$i=1;
while (!$resultado1->EOF)
{$legal->item($resultado1->fields['titulo'],$resultado1->fields['comentario'],($resultado1->fields['activo']=="f")?0:1);
 $resultado1->MoveNext();
}
/*$legal->item("SIPRO","habilidad para contratar",1);
$legal->item("Servicio tecnico","sin garantia");
$legal->item("Servicio tecnico","sin garantia",1);
$legal->item("Servicio tecnico","sin garantia");
$legal->item("Servicio tecnico","sin garantia",1);
*/
//$_sess_login="test";
$fullfilename=$OUTPUT."req_legales_$ID.pdf";
$fullzipfilename=$OUTPUT."req_legales_$ID.zip";

//crea los directorios necesarios
if (mkdirs($OUTPUT))
{
		$legal->output($fullfilename);
		$filesize=filesize($fullfilename);
		if (SERVER_OS == "linux"){
			$compresion=`/usr/bin/zip -j -9 -q "$fullzipfilename" "$fullfilename"`;
			unlink($fullfilename);
			$filesizecomp=filesize($fullzipfilename);
		}	
		else
		 $filesizecomp=filesize($fullfilename);
		
		//else  no se pudo comprimir
		$sql="select id_licitacion from archivos where id_licitacion=$ID and nombre='req_legales_$ID.pdf';";
		$resultado2 = $db->Execute($sql) or die($db->ErrorMsg());
		if ($resultado2->RecordCount()>0) //existe en la BD
		{$sql="update archivos set id_licitacion=$ID, nombre='req_legales_$ID.pdf', nombrecomp='req_legales_$ID.zip', tipo='application/pdf', subidofecha='Now', subidousuario='".$_ses_user['name']."', tamaño=$filesize ,tamañocomp=".$valores.=($filesizecomp)?$filesizecomp:0 ;
		 $sql.=" where id_licitacion=$ID and nombre='req_legales_$ID.pdf';";
		 $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
        }
        else
        {$campos="id_licitacion,nombre,nombrecomp,tipo,subidofecha,subidousuario,".
				  "tamaño,tamañocomp";
		 $valores="$ID,'req_legales_$ID.pdf','req_legales_$ID.zip','application/pdf',".
					"'Now','".$_ses_user['name']."',$filesize,";
		 $valores.=($filesizecomp)?$filesizecomp:0;
		 $insert="INSERT INTO ARCHIVOS ($campos) values ($valores)";
		 $db->Execute($insert) or die($db->ErrorMsg()."<br>$insert");
        }

}
else 
		die("NO SE PUDO CREAR LA RUTA AL ARCHIVO");

	
//echo "LISTO !!!".$parametros['id_lic'];;

?>