<?PHP
include("../../config.php");
include(LIB_DIR.'/imagenes_stat/jpgraph.php');
include(LIB_DIR.'/imagenes_stat/jpgraph_line.php');
include(LIB_DIR.'/imagenes_stat/jpgraph_radar.php');

$desde = $parametros["desde"];
$proveedor = $parametros["proveedor"];
$media = $parametros["media"];
$max=20;

/*
$sql = "Select calificado from calificacion_proveedor
           where id_proveedor = $proveedor and desde = $desde
           order by fecha desc limit  $max offset 0";*/

$sql = "Select calificado from calificacion_proveedor
           where id_proveedor = $proveedor 
           order by fecha desc limit  $max offset 0";

$result=sql($sql,"error consultando la base de datos");
/*
switch ($desde) {
	case 1: $titulo="'Para Autorizar'"; break;
	case 2: $titulo="'Recibo de Material'"; break;
	case 3: $titulo="'Pagar'"; break;
}*/
$titulo="'(Para Autorizar - Recibo de Material - Pagar)'";

switch ($media) {
	case 'A': $upp=10; $low=8; $color='#00CC00'; break;
	case 'B': $upp=8;  $low=6; $color='#99FF00'; break;
	case 'C': $upp=6;  $low=4; $color='#FFFF66'; break;
	case 'D': $upp=4;  $low=2; $color='#FF9900'; break;
	case 'E': $upp=2;  $low=0; $color='#ff0000'; break;
}

$i=0;
//establece limite superior
while($i<$max){
	$ymedia_u[$i] = $upp;
	$i++;
}


$i=0;
//establece limite inferior
while($i<$max){
	$ymedia_l[$i] = $low;
	$i++;
}

$result->MoveLast();
for ($i=0;$i<$result->RecordCount();$i++){
    if ($i==$result->recordcount()-1) {
                                     $ultimo_valor=$result->fields("calificado");
                                     $ultimo_indice=$i;
                                    }
	$ydata[]=$result->fields("calificado");
	$result->Move($result->CurrentRow()-1);
}

//calculo media
$media=0;
if (sizeof($ydata)>0) {
       for($i=0;$i<sizeof($ydata);$i++) $media+=$ydata[$i];
       $media=$media/sizeof($ydata);
       }
       else $media=0;

for($i=$ultimo_indice;$i<$max;$i++) $ydata[]=$ultimo_valor;

for($i=0;$i<$max;$i++)  $ymedia[]=$media;
//fin del calculo de la maedia
 //die(print_r($ymedia));
$graph = new Graph(700,250);

$graph->SetScale("textlin",0,10,0,$max-1);
$graph->SetTickDensity(TICKD_DENSE);
$graph->yscale->SetAutoTicks();
$graph->SetColor("gray");

if ($result->RecordCount()==0)
	$graph->title->Set('No hay calificaciones disponibles desde '.$titulo);
else
	$graph->title->Set("Ult. $max calif. emitidas ".$titulo);

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->legend->SetAbsPos(0,0,'left','top');
/*
$line_u = new LinePlot($ymedia_u);
$line_u->SetColor("$color");
$line_u->SetLegend("Limite S");
$graph->Add($line_u);

$line_l = new LinePlot($ymedia_l);
$line_l->SetColor("$color");
$line_l->SetLegend("Limite I");
$graph->Add($line_l);
  */
if ($result->RecordCount()>0) {
	$line = new LinePlot($ydata);
	$line->SetColor("#C0FFFF");
	$line->SetLegend("Calif.");
    $line->SetWeight(2);
	$graph->Add($line);
    //inserto la media
	$line2 = new LinePlot($ymedia);
	$line2->SetColor("red");
    $media=number_format($media,"2",".","");
	$line2->SetLegend("Media[$media]");
    //$line2->SetLegend();
    $line2->SetWeight(3);
	$graph->Add($line2);

}


// Output graph
$graph->Stroke();

?>