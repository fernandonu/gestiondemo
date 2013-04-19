<?
/*
Autor: GACZ
Creado: miercoles 06/06/05

MODIFICADA POR
$Author: gonzalo $
$Revision: 1.1 $
$Date: 2005/06/09 18:13:12 $
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


$cmd="retenciones";
require("retenciones.db.php");
$r1=sql($q) or fin_pagina();
$cmd="percepciones";
require("percepciones.db.php");
$r2=sql($q) or fin_pagina();

//arreglo que contiene los indices y el nombre del recurso ordenados de menor a mayor
$indices=sortByDate("r1","r2");

$xls=new XMListGenerator();
$row=$xls->titulos->addRow();
$row->addCol("Factura\n Tipo y Nº");
$row->addCol("Cuit");
$row->addCol("Fecha");
$row->addCol("Certificado");
$row->addCol("Entidad/Proveedor");
$row->addCol("IVA Retencion");
$row->addCol("IB Retencion");
$row->addCol("Ganancia Retencion");
$row->addCol("IVA Percepcion");
$row->addCol("IB Percepcion");
$row->addCol("Ganancia Percepcion");

foreach ($indices as $i => $arr)
{
	$res=${$arr["res_name"]};
	$res->move($arr["posicion"]);
	$row=$xls->lista->addRow();
	$row->addCol($res->fields['tipo_factura']." ".$res->fields['nro_factura']);
	$row->addCol($res->fields['cuit']);
	$row->addCol($res->fields['fecha'])->data[0]->datatype="date";
	$row->addCol($res->fields['nro_certificado']);
	$row->addCol($res->fields['entidad']);
	//es retencion??
	if ($arr["res_name"]=="r1")
	{
		$row->addCol($res->fields['iva_monto'])->data[0]->datatype="money";
		$row->addCol($res->fields['ib_monto'])->data[0]->datatype="money";
		$row->addCol($res->fields['ganancia_monto'])->data[0]->datatype="money";
		$row->addCol(0)->data[0]->datatype="money";
		$row->addCol(0)->data[0]->datatype="money";
		$row->addCol(0)->data[0]->datatype="money";
	}
	else //es percepcion
	{
		$row->addCol(0)->data[0]->datatype="money";
		$row->addCol(0)->data[0]->datatype="money";
		$row->addCol(0)->data[0]->datatype="money";
		$row->addCol($res->fields['iva_monto'])->data[0]->datatype="money";
		$row->addCol($res->fields['ib_monto'])->data[0]->datatype="money";
		$row->addCol($res->fields['ganancia_monto'])->data[0]->datatype="money";
	}
}
$xls->encabezado->recordcount=count($indices);
$out=new XMListReader($xls->saveXML());
$out->setScreenPercent(150);
$out->sendHTML();

?>