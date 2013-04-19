<?
require_once ("../../config.php");
include ("funciones.php");

// Gantt example 30
// $Id: grafica_ensamblador.php,v 1.2 2004/09/09 19:02:17 mari Exp $
include ("../../lib/imagenes_stat/jpgraph.php");
include ("../../lib/imagenes_stat/jpgraph_gantt.php");

/***************************************************************************** 
 * ordenar_arr_fecha ordena un arreglo bidimensional 
 * @return arreglo ordenado segun el campo $campo de tipo fecha
 * @param bi_array arreglo bidimensional
 * @param $campo nombre de la segunda dimension por la cual se ordena
 * @desc ordena un arreglo bidimensional POR EL CAMPO $campo 
     de la segunda dimenson del arreglo (adaptacion de qsort_second_dimension)
 ****************************************************************************/

function ordenar_arr_fecha($bi_array,$campo) {
$i=0;
 $tam=sizeof($bi_array);
 
 while($i<$tam)
 {$j=$i+1;
  $i_item=$bi_array[$i][$campo];
  while($j<$tam)
   {$j_item=$bi_array[$j][$campo];
    if(compara_fechas($i_item,$j_item) ==1 ) {  //si i_item > j_item
       $temp=$bi_array[$i];
       $bi_array[$i]=$bi_array[$j];
       $bi_array[$j]=$temp;
       $j=$tam;
       $i--;
    }
    else  
     $j++;	
   }
   $i++;
 }	
 return $bi_array;	  
}


$fecha_actual=date("Y-m-d" ,mktime()); //fecha actual
$id_ensamblador=$parametros['id_ensamblador'];


$sql="select id_subir,subido_lic_oc.id_licitacion,entrega_estimada.nro,id_entrega_estimada,
      fecha_subido,vence_oc,nro_orden,ini_compra,fin_compra,ini_cdr,fin_cdr,
      ini_entrega,fin_entrega,avance_compra,avance_cdr,
      id_distrito,modificado,ensamblador.nombre,entidad.nombre as nom_entidad
      from  licitaciones.licitacion
	  join licitaciones.entidad using (id_entidad)
      left join licitaciones.subido_lic_oc using (id_licitacion)
      left join licitaciones.entrega_estimada using (id_entrega_estimada)
      left join ordenes.ensamblador using (id_ensamblador) 
      where borrada='f' and finalizada=0 and id_ensamblador=$id_ensamblador
      order by id_licitacion,nro";
      

$res_datos=sql($sql) or fin_pagina();
$cant_filas=$res_datos->RecordCount(); 


//  JpGraphError::Raise("No hay datos cargados para el ensamblador $nombre_ensamblador ");

$data=array();
$data1=array();
$i=0;

while (!$res_datos->EOF) {
   	
$id_distrito=$res_datos->fields['id_distrito'];
$id_licitacion=$res_datos->fields['id_licitacion'];
$id_entrega_estimada=$res_datos->fields['id_entrega_estimada'];
$id_subir=$res_datos->fields['id_subir'];
$nro=$res_datos->fields['nro']; 
$ini_seg=substr($res_datos->fields['fecha_subido'],0,10);
$fin_seg=substr($res_datos->fields['vence_oc'],0,10);
$ini_compra=substr($res_datos->fields['ini_compra'],0,10);
$fin_compra=substr($res_datos->fields['fin_compra'],0,10);
$ini_cdr=substr($res_datos->fields['ini_cdr'],0,10);
$fin_cdr=substr($res_datos->fields['fin_cdr'],0,10);
$ini_entrega=substr($res_datos->fields['ini_entrega'],0,10);
$fin_entrega=substr($res_datos->fields['fin_entrega'],0,10);
$avance_compra=$res_datos->fields['avance_compra'];
$avance_cdr=$res_datos->fields['avance_cdr'];
$modificado=$res_datos->fields['modificado'];
$nombre_ensamblador=$res_datos->fields['nombre'];
$entidad=$res_datos->fields['nom_entidad'];


if ($modiifcado == 0 ) {   //recupero valores por defecto

 //fin de la entrega
   	if ($fin_entrega == "" || $fin_entrega==NULL )   //por defecto es la fecha de vencimiento del seguimiento 
         $fin_entrega=$fin_seg;  

   	 //inicio de compras	
   	if ($ini_compra=="") {
      //selecciona la fecha de inicio de la primer orden de compra del seguimiento
      $sql_c="select min(fecha) as ini_compra from compras.orden_de_compra where id_licitacion=$id_licitacion and id_subir=$id_subir and estado <> 'n'";
      $res_c=sql($sql_c) or fin_pagina();
      if ($res_c->fields['ini_compra']!=NULL)
          $ini_compra=substr($res_c->fields['ini_compra'],0,10);
      else {  //selecciona la fecha de inicio de la primer orden de compra de la licitacion
       $sql_c="select min(fecha) as ini_compra from compras.orden_de_compra where id_licitacion=$id_licitacion and estado <> 'n'";
       $res_c=sql($sql_c) or fin_pagina();
        if ($res_c->fields['ini_compra']!=NULL)
                 $ini_compra=substr($res_c->fields['ini_compra'],0,10);
                 else 
                    $ini_compra=$ini_seg;  //si no tiene orden de compra busca la fecha de subido del archivo de autorizacion orden de compra 
   	  }
   	}

        
     //ini_entrega 
     if ($ini_entrega == "" || $ini_entrega == NULL) {
       //un dia de entrega para id_distrito=12 =>Prov de Bs aires 
       //2 dias de entregas en oro distrito
      if ($id_distrito==12) 
         $ini_entrega=Fecha_db(dias_habiles_anteriores(Fecha($fin_entrega),1)); //resta 1 dia
      else  
         $ini_entrega=Fecha_db(dias_habiles_anteriores(Fecha($fin_entrega),2)); //resta 2 dias
      }
      
      //fin  armado cdr
      if ($fin_cdr=="" || $fin_cdr == NULL) 
            $fin_cdr=$ini_entrega;
      
            
       //ini armado_cdr
     if ($ini_cdr == "" || $ini_cdr == NULL) {
      	$sql_cdr="select sum (renglones_oc.cantidad) as cantidad from licitaciones.renglones_oc 
                  join licitaciones.renglon using (id_renglon) where id_subir=$id_subir and 
                  (tipo='Computadora Enterprise' or tipo='Computadora Matrix')";
        $res_cdr=sql($sql_cdr) or fin_pagina();
        if ($res_cdr->fields['cantidad'] !=NULL && $res_cdr->fields['cantidad']!="") {
        	$cantidad=$res_cdr->fields['cantidad'];
            $sql_cant="select cant_dias from licitaciones.dias_armado_cdr where $cantidad between lim_inf and lim_sup";
            $res_cant=sql($sql_cant) or fin_pagina();
            $dias=$res_cant->fields['cant_dias']; 
            $ini_cdr=fecha_db(dias_habiles_anteriores(fecha($fin_cdr),$dias));
        }
        else {
        $ini_cdr=$ini_entrega;
        }
      }
      
//fin compra
      if ($fin_compra== "" || $fin_compra== NULL) 
                $fin_compra=$ini_cdr;
}	
        //Armado CDR
       
       $data[$i]=array();
       $data[$i][1]="lic ".$id_licitacion." SEG N°".$nro;
       $data[$i][2]=$ini_cdr;
       $data[$i][3]=$fin_cdr;
       $data[$i][4]=$avance_cdr;
      
       $i++;
      
    $res_datos->MoveNext();
   }
 
   
   if (count($data)>1) $data=ordenar_arr_fecha($data,2);
  
// Standard calls to create a new graph
$graph = new GanttGraph(0,0,"auto");
$graph->SetShadow();
$graph->SetBox();

// Titles for chart
$graph->title->Set("ENSAMBLADOR:$nombre_ensamblador");

$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);

// For illustration we enable all headers. 
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);


// For the week we choose to show the start date of the week
// the default is to show week number (according to ISO 8601)
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

// Change the scale font 
$graph->scale->week->SetFont(FF_FONT0);
$graph->scale->year->SetFont(FF_ARIAL,FS_BOLD,12);


// Setup some data for the gantt bars
$progreso=0;
for($i=0; $i<count($data); $i++) {
	
	$bar = new GanttBar($i,$data[$i][1],$data[$i][2],$data[$i][3]);
	
	$bar->SetPattern(BAND_RDIAG,'blue');
    $bar->SetFillColor("white");
	
	if ($data[$i][4] !="linea") {
	 if ($data[$i][4] !="" && $data[$i][4]!=0 ) {
		 $bar->progress->Set($data[$i][4]/100);
		 $bar->progress->SetPattern(BAND_RDIAG,'blue'); //barra de progreso
		 $bar->SetFillColor("white");
	   	 }
	   	 
	   	 
	}
	else {
	   $bar->SetHeight(3);
	   $bar->SetPattern(BAND_RDIAG,'black');
    	 $bar->SetFillColor("black"); 
	}
	   	 
	 
     // ... and add the bar to the gantt chart
	$graph->Add($bar);
}

 
// Create a vertical line to emphasize the milestone
$vl = new GanttVLine($fecha_actual,"HOY","darkred");
$vl->SetDayOffset(0.5);	// Center the line in the day
$graph->Add($vl);

// Output the graph
$graph->Stroke();

// EOF

?>
