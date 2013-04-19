<?
/*
Autor: GACZ
Creado: miercoles 06/06/05

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.5 $
$Date: 2005/07/08 21:49:08 $
*/

require_once("../../config.php");
require_once(LIB_DIR."/class.gacz.php");

function sortByDate($res_name1,$res_name2)
{
	global ${$res_name1},${$res_name2};
	//NOTA: LOS recursos deben estar previamente ordenados independientemente
	
	//asigno las variables globales a otros nombres de variables conocidas
	//se asignan por direccion para evitar la copia, pero nunca se modifican
	$res1=&${$res_name1};
	$res2=&${$res_name2};
	
	$indexes=array();
	
	//Uno los dos resultados en un arreglo de indices
	for ($i=0; !$res1->EOF; $i++)
	{
		$indexes[$i]=array("res_name"=>$res_name1,"posicion"=>$i);		
		$res1->movenext();
	}
	for ($j=$i,$k=0; !$res2->EOF; $j++,$k++)
	{
		$indexes[$j]=array("res_name"=>$res_name2,"posicion"=>$k);		
		$res2->movenext();
	}
	//si ambos tienen resultados, ordeno sino, no
	if ($res1->recordcount() && $res2->recordcount())
		quicksort($indexes,0,count($indexes)-1);
		
	return $indexes;
}

function quicksort (&$a, $lo, $hi)
{
	global $r1,$r2;//el algoritmo depende del nombre de estas dos variables para comparar
//  $lo is the lower index, $hi is the upper index
//  of the region of array a that is to be sorted
    $i=$lo; $j=$hi;
    //$x=$a[($lo+$hi)/2];
   $m=(int)(($lo+$hi)/2);
   ${$a[$m]['res_name']}->move($a[$m]['posicion']);
   //fecha pivot
   $x=${$a[$m]['res_name']}->fields['fecha'];

    //  partition
    do
    {    
	    ${$a[$i]['res_name']}->move($a[$i]['posicion']);
		$fecha1=${$a[$i]['res_name']}->fields['fecha'];
		//while ($a[$i]<$x)
		while (compara_fechas($fecha1,$x)==-1)
        {
       	 $i++; 
		 ${$a[$i]['res_name']}->move($a[$i]['posicion']);
		 $fecha1=${$a[$i]['res_name']}->fields['fecha'];
        }
	    ${$a[$j]['res_name']}->move($a[$j]['posicion']);
		$fecha1=${$a[$j]['res_name']}->fields['fecha'];
        //while ($a[$j]>$x)
        while (compara_fechas($fecha1,$x)==1)
        {
         $j--;
		 ${$a[$j]['res_name']}->move($a[$j]['posicion']);
		 $fecha1=${$a[$j]['res_name']}->fields['fecha'];
        }
        if ($i<=$j)
        {
            $h=$a[$i]; $a[$i]=$a[$j]; $a[$j]=$h;
            $i++; $j--;
        }
    } while ($i<=$j);

    //  recursion
    if ($lo<$j) quicksort($a, $lo, $j);
    if ($i<$hi) quicksort($a, $i, $hi);
}


require("retenciones.db.php");
$r1=sql($q) or fin_pagina();

require("percepciones.db.php");
$r2=sql($q) or fin_pagina();

//arreglo que contiene los indices y el nombre del recurso ordenados de menor a mayor
$indices=sortByDate("r1","r2");

$xls=new XMListGenerator();
$row=$xls->titulos->addRow();
// 0x0D0A //hexadecimal del enter $tmp="Factura ".chr(13).chr(10)."Tipo y Nº";
$row->addCol("Factura Tipo y Nº",array(width=>'18.5px'));
$row->addCol("Cuit",array(width=>'13.45px'));
$row->addCol("Fecha",array(width=>'10px'));
$row->addCol("Certificado",array(width=>'13px'));
$row->addCol("Entidad/Proveedor",array(width=>'45px'));
$row->addCol("IVA Retencion",array(width=>'14.50px'));
$row->addCol("IVA Percepcion",array(width=>'14.5px'));
$row->addCol("IB Retencion",array(width=>'14.5px'));
$row->addCol("IB Percepcion",array(width=>'14.5px'));
$row->addCol("Gan. Retencion",array(width=>'14.5px'));
$row->addCol("Gan. Percepcion",array(width=>'14.5px'));
$row->addCol("Provincia",array(width=>'20px'));

//para controlar que no se repitan los datos del iva y ganancia de las percepciones
//el iva y la ganancia se imprimen solo la primera vez
$idsfact=array();
foreach ($indices as $i => $arr)
{
	$res=${$arr["res_name"]};
	$res->move($arr["posicion"]);
	$row=$xls->lista->addRow();
	$row->addCol($res->fields['tipo_factura']." ".$res->fields['nro_factura']);
	$row->addCol($res->fields['cuit']);
	$row->addCol($res->fields['fecha'])->data[0]->datatype="date";
	$row->addCol($res->fields['nro_certificado']);
	//es retencion??
	if ($arr["res_name"]=="r1")
	{
		$row->addCol($res->fields['entidad']);
		$row->addCol($res->fields['iva_monto'])->data[0]->datatype="money";
		$row->addCol(0.00)->data[0]->datatype="money";
		$row->addCol($res->fields['ib_monto'])->data[0]->datatype="money";
		$row->addCol(0.00)->data[0]->datatype="money";
		$row->addCol($res->fields['ganancia_monto'])->data[0]->datatype="money";
		$row->addCol(0.00)->data[0]->datatype="money";
		$row->addCol($res->fields['distrito']);
	}
	else //es percepcion
	{
		//si no esta el idfactura, lo agrego
		if (!($idexist=in_array($res->fields['id_factura'],$idsfact)))
			$idsfact[]=$res->fields['id_factura'];
			
		$row->addCol($res->fields['razon_social']);
		$row->addCol(0.00)->data[0]->datatype="money";
		$row->addCol($idexist?0:$res->fields['iva_monto'])->data[0]->datatype="money";
		$row->addCol(0.00)->data[0]->datatype="money";
		$row->addCol($res->fields['ib_monto'])->data[0]->datatype="money";
		$row->addCol(0.00)->data[0]->datatype="money";
		$row->addCol($idexist?0:$res->fields['ganancia_monto'])->data[0]->datatype="money";
		$row->addCol($res->fields['distrito']);
	}
}
$xls->encabezado->recordcount=count($indices);
$out=new XMListReader($xls->saveXML());
$out->setScreenPercent(150);
if ($download)
	$out->sendExcel("Retenciones_Percepciones(".$meses[intval($mes)]." de $anio).xls");
else 
{
	echo '<form name="form1" action="'.$_SERVER['SCRIPT_NAME'].'" method="POST" ><br>';
	echo "<table width=\"90%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"1\">
    <tr> 
      <td width=\"100%\" align=\"center\" valign=\"middle\" > ";
	echo  "<font size=\"2\"><strong>".strtoupper($cmd).": ver datos correspondientes a</strong></font>&nbsp;&nbsp;&nbsp;Mes &nbsp; ";
	$select_mes->toBrowser();
	echo "&nbsp; A&ntilde;o &nbsp;";
	$select_anio->toBrowser();
	echo "&nbsp;&nbsp; <input type=\"submit\" name=\"Submit\" value=\"Actualizar\"> &nbsp;&nbsp;";
    if ($cmd=="todas")
    	echo " <a title=\"Bajar datos {$meses[intval($mes)]} de $anio en un excel\" href=\"".encode_link($_SERVER['SCRIPT_NAME'],array('download'=>1,'mes'=>$mes,'anio'=>$anio))."\" ><img src='../../imagenes/excel.gif' width=16 height=16 border=0 align='absmiddle' ></a>";
	echo " </td> </tr> </table>";
	echo ($msg?"<br><center><b>$msg</b></center>":""); //si hay mensaje, lo muestra
	echo "<br>";  
	$out->sendHTML();
	echo "</form><br>	";
}
?>