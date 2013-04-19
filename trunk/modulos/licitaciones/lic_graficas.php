<?
require_once ("../../config.php");
include ("../../lib/imagenes_stat/jpgraph.php");
include ("../../lib/imagenes_stat/jpgraph_bar.php");

$datay=$parametros["data"];
$datax=$parametros["leyenda"];
$titulo=$parametros["titulo"];
$tama�o=$parametros["tama�o"];

//seteo el tama�o
switch ($tama�o) {
    case "small":
                $graph = new Graph(300,210,"auto");
                $graph->img->SetMargin(30,10,15,60);
                $tama�o_font=10;
                $tama�o_eje=8;
                break;
    case "large":
                $graph = new Graph(500,410,"auto");
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