<? 
/*
$Author: mari $
$Revision: 1.7 $
$Date: 2006/06/06 22:02:04 $
*/

require_once ("../../config.php");
require_once("funciones.php");
include_once ("../../lib/imagenes_stat/jpgraph.php");
include_once ("../../lib/imagenes_stat/jpgraph_bar.php");

//gasto del mes de enero son las OP que se emitieron en mes de Febrero

$mes_desde=$parametros['mes_desde'];
$mes_hasta=$parametros['mes_hasta'];
$anio_desde=$parametros['anio_desde'];
$anio_hasta=$parametros['anio_hasta'];
$atendido=$parametros['atendido'];
$j=$mes_desde; //indice para inicializar el arreglo $datax


//esto es para el caso que se eliga un solo mes 
$mes=$j;
$a=substr($anio_desde,0,4);

/******************* armo fecha para mostrar en el grafico *****************/
$mes_i=$mes_desde+1;
$mes_f=$mes_hasta+1;
   
$dia_hasta=ultimoDia($mes_f,$anio_hasta);
$fecha_ini=$anio_desde."-".$mes_i."-01";
$fecha_fin=$anio_hasta."-".$mes_f."-".$dia_hasta;
$datax=armar_datax($fecha_ini,$fecha_fin);

/******************************************************************************/

/****************armo fecha para la consulta *********************************/
//El select de los meses devuelve meses desde 0 a 11
// 0 enero, 1 febrero ... 11 Diciembre
//En la consulta si pide por ej Febrero tengo que buscar las OC de emitidas en marzo 
// o sea ordenes de compras emitidas en el mes siguiente
//por ejemplo si mes-desde ==0 es enero tengo que traer las OP emitidas en febrero entoces
//tengo que aumentar 2 veces el mes 
$mes_desde++;
$mes_desde++;

if ($mes_desde==13) { 
	 $mes_desde="01";
	 $anio_desde++;
}
else if ($mes_desde > 0 && $mes_desde <10) $mes_desde="0".$mes_desde;

$mes_hasta++;
$mes_hasta++;

if ($mes_hasta==13) {;
	$mes_hasta="01";
	$anio_hasta++;
}
else if ($mes_hasta > 0 && $mes_hasta <10) $mes_hasta="0".$mes_hasta;

$dia_hasta=ultimoDia($mes_hasta,$anio_hasta);
$fecha_desde=$anio_desde."-".$mes_desde."-01"." "."00:00:00";
$fecha_hasta=$anio_hasta."-".$mes_hasta."-".$dia_hasta." "."23:59:59";
/**************************************************************************************/

//oc emitidas en enero se muestran en Diciembre del anño anterior..
$sql="select (cantidad*precio_unitario) as total,id_moneda,fecha,estado 
      from compras.orden_de_compra join compras.fila using(nro_orden)";
if ($atendido != -1) {
$sql.=" join (select idate,fila from casos.casos_cdr ) as cas
        on fila.id_fila=cas.fila";
}
$sql.=" where (flag_honorario=1) and orden_de_compra.fecha between '$fecha_desde' and '$fecha_hasta' and estado <> 'n'";
if ($atendido != -1) {
  $sql.=" and idate=$atendido";
}
 $sql.=" order by orden_de_compra.fecha,id_moneda";

$res=sql($sql,"$sql") or fin_pagina();

$aux=array();
$sum=0;
$ind=0; //indice del arreglo datay
$fecha_anterior=substr($fecha_desde,0,7);

while (!$res->EOF) {
$fecha=substr($res->fields['fecha'],0,7);
  
  if($fecha == $fecha_anterior){
     //if ($res->fields['id_moneda']==1) 
  	 $sum+=$res->fields['total'];
  	// else $sum+=($res->fields['total']*$valor_dolar);
  }
  else {
  	
    $mes=substr($fecha_anterior,5,7)-1;
    $a=substr($fecha_anterior,0,4);
    if ($mes==0) {
    	  $a--;
    	  $mes=12;
    }
    $aux[mes_num_a_let($mes)."-".substr($a,2,4)]=number_format($sum,"2",".","");
    // if ($res->fields['id_moneda']==1) 
    $sum=$res->fields['total'];
     // else $sum=$res->fields['total']*$valor_dolar;
  }
  
  $fecha_anterior=substr($res->fields['fecha'],0,7);
  
$res->MoveNext();
}

$m=$mes+1;
 if ($m==13)  {
 	$m=1;
    $a++;
 } 
$aux[mes_num_a_let($m)."-".substr($a,2,4)]=$sum;

$cant_x=count($datax);
for($i=0;$i<$cant_x;$i++) {
   $ind=$datax[$i];
    if($aux[$ind]) 
       $datay[$i]=$aux[$ind];
    else  $datay[$i]=0.0;
}

// Setup the graph. 


$graph = new Graph(700,400,"auto");    
//$graph = new Graph(600,200,"auto");    
$graph->img->SetMargin(60,20,30,50);
$graph->SetScale("textlin");
$graph->SetMarginColor("lightblue");
$graph->SetShadow();
//
// Set up the title for the graph

$graph->title->Set("Montos en \$ de OC asociadas a Honorarios de Serv. Técnico");
$graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);
$graph->title->SetColor("darkred");

// Setup font for axis
$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,10);

// Show 0 label on Y-axis (default is not to show)
$graph->yscale->ticks->SupressZeroLabel(false);

// Setup X-axis labels
$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetLabelAngle(50);

// Create the bar pot
$bplot = new BarPlot($datay);

//setea ancho de las barras
if ($cant_x <8)
 $bplot->SetWidth(0.2); 
else 
 $bplot->SetWidth(0.6);

// Setup color for gradient fill style 
$bplot->SetFillGradient("navy","#EEEEEE",GRAD_LEFT_REFLECTION);
//deja espacio al tope del grafico
$graph->yaxis->scale->SetGrace(5); 

//muestra los valores
$bplot->value->Show(); 

// Set color for the frame of each bar
$bplot->SetColor("white");

$graph->Add($bplot);

// Finally send the graph to the browser
$graph->Stroke();

?>