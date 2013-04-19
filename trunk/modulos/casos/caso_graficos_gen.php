<?php
require_once ("../../config.php");
include ("../../lib/imagenes_stat/jpgraph.php");
include ("../../lib/imagenes_stat/jpgraph_bar.php");

function suma_fechas($fecha,$ndias){
      if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha))
      	list($dia,$mes,$año)=split("/", $fecha);
      if (preg_match("/[0-9]{1,2}-[0-9]{1,2}-([0-9][0-9]){1,2}/",$fecha))
        list($dia,$mes,$año)=split("-",$fecha);
      $nueva = mktime(0,0,0, $mes,$dia,$año) + $ndias * 24 * 60 * 60;
      $nuevafecha=date("d-m-Y",$nueva);
      return ($nuevafecha);  
}



$fecha_desde=$parametros['fecha_desde'];
$fecha_hasta=$parametros['fecha_hasta'];
$porcentaje=$parametros['porcentaje'];
$abierto=$parametros['abierto'];

if($abierto==1){
	$titulo='Grafico de Casos Abiertos';
}
else $titulo='Grafico de Casos Cerrados';

$i=0;
$datax=array();
$datay=array();

$fecha_desde_db=Fecha_db($fecha_desde);
$fecha_hasta_db=Fecha_db($fecha_hasta);
$fecha_aux_db=$fecha_desde_db;


while ($fecha_aux_db!=fecha_db(suma_fechas(Fecha($fecha_hasta_db),1))) {
	if($abierto==1){
		$sql="select count(idcaso) as valor from casos.casos_cdr 
			  where fechainicio='$fecha_aux_db'";
	}
	else{
		$sql="select count (distinct (idcaso)) as valor
				from casos.log_casos 
				where (descripcion = 'Finalizado') and (date(fecha)='$fecha_aux_db')";
	}
	$result = sql($sql) or fin_pagina();
	$datax[$i]=Fecha($fecha_aux_db);
	$datay[$i]=$result->fields['valor'];
	$i++;	
	$fecha_aux_db=fecha_db(suma_fechas(Fecha($fecha_aux_db),1));
}

$ancho=$Porcentaje*8;
switch ($porcentaje){
		case "75": $ancho=700;
		break;
		case "100": $ancho=950;
		break;
		case "150": $ancho=1400;
		break;
		case "200": $ancho=1900;
		break;
}

$graph = new Graph($ancho,510,"auto");
$graph->img->SetMargin(30,10,15,50);
$tamaño_font=15;
$tamaño_eje=10;

$graph->SetScale("textlin");
$graph->SetMarginColor("lightblue");
$graph->SetColor($bgcolor_out);


//Seteo el titulo para el grafico
$graph->title->Set($titulo);
$graph->title->SetFont(FF_COURIER,FS_BOLD,$tamaño_font);
$graph->title->SetColor($bgcolor1);

// Seteo fuente para los ejes x e y
$graph->xaxis->SetFont(FF_COURIER,FS_NORMAL,$tamaño_eje);
$graph->yaxis->SetFont(FF_COURIER,FS_NORMAL,$tamaño_eje);

// Show 0 label on Y-axis (default is not to show)
$graph->yscale->ticks->SupressZeroLabel(false);

// Setup X-axis labels
$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetLabelAngle(20);

// Se creo la barra de plot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.6);

// Seteo color
$bplot->SetFillGradient("navy","#EEEEEE",GRAD_LEFT_REFLECTION);

// Set color for the frame of each bar
$bplot->SetColor("white");
$graph->Add($bplot);

// Se envia el grafico al navegador
$graph->Stroke();

?>