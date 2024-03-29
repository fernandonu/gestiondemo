<?php
require_once ("../../config.php");
include ("../../lib/imagenes_stat/jpgraph.php");
include ("../../lib/imagenes_stat/jpgraph_bar.php");

$anio = $parametros['anio'];
$id_indi=$parametros['id_indi'];
$tama�o=$parametros['tama�o'];


$sql="SELECT indicadores.id_desc_indicador, indicadores.mes, indicadores.anio, indicadores.valor, desc_indicador.descripcion ";
$sql.="FROM calidad.desc_indicador INNER JOIN calidad.indicadores ON desc_indicador.id_desc_indicador = indicadores.id_desc_indicador ";
$sql.="WHERE ( ((indicadores.id_desc_indicador)=$id_indi) AND ((indicadores.anio)=$anio))";
$sql.="Order By indicadores.mes ASC";
$sat_cliente= sql($sql) or fin_pagina();

$titulo=$sat_cliente->fields["descripcion"];

$datax=array("Ene","Feb","Mar","Abr","Mayo","Jun","Jul","Ago","Sep","Oct","Nov","Dic");
$datay=array("","","","","","","","","","","","");

$i=0;
while (!$sat_cliente->EOF){

$datay[($sat_cliente->fields['mes']-1)]=$sat_cliente->fields['valor'];	
$sat_cliente->MoveNext();
$i++;

}

switch ($tama�o) {
    case "small":
                $graph = new Graph(300,210,"auto");
                $graph->img->SetMargin(30,10,15,60);
                $tama�o_font=10;
                $tama�o_eje=8;
                break;
    case "large":
                $graph = new Graph(600,510,"auto");
                $graph->img->SetMargin(30,10,15,50);
                $tama�o_font=15;
                $tama�o_eje=10;
                break;

}

$graph->SetScale("textlin");
$graph->SetMarginColor("lightblue");
$graph->SetColor($bgcolor_out);


//Seteo el titulo para el grafico
$graph->title->Set($titulo);
$graph->title->SetFont(FF_COURIER,FS_BOLD,$tama�o_font);
$graph->title->SetColor($bgcolor1);

// Seteo fuente para los ejes x e y
$graph->xaxis->SetFont(FF_COURIER,FS_NORMAL,$tama�o_eje);
$graph->yaxis->SetFont(FF_COURIER,FS_NORMAL,$tama�o_eje);

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

