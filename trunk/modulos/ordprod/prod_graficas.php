<?
require_once ("../../config.php");
include_once ("funciones.php");

// Gantt example 30
// $Id: prod_graficas.php,v 1.22 2005/09/15 15:45:43 mari Exp $
include_once ("../../lib/imagenes_stat/jpgraph.php");
include_once ("../../lib/imagenes_stat/jpgraph_gantt.php");

$fecha_actual=date("Y-m-d" ,mktime()); //fecha actual
if ($parametros['id_subir']) {
    $id_subir=$parametros['id_subir'];
    $cond= " and id_subir = $id_subir";
    $limit="";
    $offset="";
}
else {
   $cond="";
   $offset=$parametros['offset'];
   $limit = $parametros['limit'];
   $sql_ini="select min (fecha_subido) as fech_ini 
      from  licitaciones.licitacion
	  join licitaciones.entidad using (id_entidad)
      left join licitaciones.subido_lic_oc using (id_licitacion)
      left join licitaciones.entrega_estimada using (id_entrega_estimada) 
      where borrada='f' and finalizada=0";
   $res_ini=sql($sql_ini)or fin_pagina();
   $ini=substr($res_ini->fields['fech_ini'],0,10);
   
   $sql_fin= "select max(vence_oc) as fech_fin
           from  licitaciones.licitacion
	       join licitaciones.entidad using (id_entidad)
           left join licitaciones.subido_lic_oc using (id_licitacion)
           left join licitaciones.entrega_estimada using (id_entrega_estimada) 
           where borrada='f' and finalizada=0";
   $res_fin=sql($sql_fin) or fin_pagina();
   $fin=substr($res_fin->fields['fech_fin'],0,10);
}


$sql="select id_subir,subido_lic_oc.id_licitacion,entrega_estimada.nro,id_entrega_estimada,
      fecha_subido,vence_oc,nro_orden,ini_compra,fin_compra,ini_cdr,fin_cdr,
      ini_entrega,fin_entrega,avance_compra,avance_cdr,comentario_gantt,
      id_distrito,modificado,entidad.nombre as nom_entidad
      from  licitaciones.licitacion
	  join licitaciones.entidad using (id_entidad)
      left join licitaciones.subido_lic_oc using (id_licitacion)
      left join licitaciones.entrega_estimada using (id_entrega_estimada) 
      where borrada='f' and finalizada=0 ";
$sql.=$cond; 
$sql.="order by vence_oc";
if ($limit !="")
$sql.=" limit $limit offset $offset";
      

    $res_datos=sql($sql) or fin_pagina();
    $cant_filas=$res_datos->RecordCount(); 


$data=array();
   $i=0;
   $pos=0;  //indica la posicion en el diagrama 
while (!$res_datos->EOF) {
if ($res_datos->fields['comentario_gantt']) 
     $comentario=$res_datos->fields['comentario_gantt'];   
    else $comentario="";	
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
$entidad=$res_datos->fields['nom_entidad'];

if ($modificado == 0 ) {   //recupero valores por defecto

 //fin de la entrega
   	if ($fin_entrega == "" || $fin_entrega==NULL )   //por defecto es la fecha de vencimiento del seguimiento 
         $fin_entrega=$fin_seg;  
   
  //inicio de compras	
   	if ($ini_compra=="" || $ini_compra==NULL) {
      //selecciona la fecha de inicio de la primer orden de compra del seguimiento
      $sql_c="select min(fecha) as ini_compra from compras.orden_de_compra where id_licitacion=$id_licitacion and id_subir=$id_subir and estado <> 'n'";
      $res_c=sql($sql_c) or fin_pagina();
     
      if ($res_c->fields['ini_compra']!=NULL || $res_c->fields['ini_compra']!="") {
          $ini_compra=substr($res_c->fields['ini_compra'],0,10);
      }
      else {  //selecciona la fecha de inicio de la primer orden de compra de la licitacion
       $sql_c="select min(fecha) as ini_compra from compras.orden_de_compra where id_licitacion=$id_licitacion and estado <> 'n'";
       $res_c=sql($sql_c) or fin_pagina();
      
       if ($res_c->fields['ini_compra']!=NULL || $res_c->fields['ini_compra']!="" ) {
           $ini_compra=substr($res_c->fields['ini_compra'],0,10);
       }
          else 
             $ini_compra=$ini_seg;  //si no tiene orden de compra busca la fecha de subido del archivo de autorizacion orden de compra 
          }
   	}
  
   if (compara_fechas($ini_compra,$ini_seg) < 0) {// si la fecha ini_compras es menor a la vencida
       $ini_compra=$ini_seg;
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
//FF_FONT1 

if (!isset($parametros["id_subir"]) && $comentario!="") $pos++;
                //seguimiento
   	   $data[$i]=array();
   	   $data[$i][0]=$pos;
   	   $data[$i][1]="lic ".$id_licitacion." SEG N°".$nro;
   	   $data[$i][2]=$ini_seg;
       $data[$i][3]=$fin_seg;
       $data[$i][4]="";
       $data[$i][5]="";
       $data[$i][6]="";
       $data[$i][7]=FF_FONT1;
       $data[$i][8]=FS_BOLD;
       $data[$i][9]=8;
       $data[$i][10]=$modificado;
       $data[$i][11]=$comentario;
        
       //realizacion OC
       $pos++;
       $i++;
       $data[$i]=array();
       $data[$i][0]=$pos;
       $data[$i][1]="";
       $data[$i][2]=$ini_compra;
       $data[$i][3]=$fin_compra;
       $data[$i][4]="green";
       $data[$i][5]="green";
       $data[$i][6]=$avance_compra;
      
       
        //Armado CDR
       $i++;
       $data[$i]=array();
       $data[$i][0]=$pos;
       $data[$i][1]="";
       $data[$i][2]=$ini_cdr;
       $data[$i][3]=$fin_cdr;
       $data[$i][4]="blue";
       $data[$i][5]="blue";
       $data[$i][6]=$avance_cdr;
      
       
       //entrega
       $i++;
       $data[$i]=array();
       $data[$i][0]=$pos;
       $data[$i][1]=substr($entidad,0,32);
       $data[$i][2]=$ini_entrega;
       $data[$i][3]=$fin_entrega;
       $data[$i][4]="red";  //color barra de progreso
       $data[$i][5]="red";   //color barra
       $data[$i][6]="";   
 
       //linea divisoria
      
       if (!isset($parametros['id_subir'])) {
       $pos++;
      
       $i++;
       $data[$i]=array();
       $data[$i][0]=$pos;
       $data[$i][1]="";
       $data[$i][2]=$ini;
       $data[$i][3]=$fin;
       $data[$i][4]="black";  //color barra de progreso
       $data[$i][5]="black";   //color barra
       $data[$i][6]="2";  
      
       }
       
       $i++;
       $pos++;
    $res_datos->MoveNext();
   }
 
    
// Standard calls to create a new graph
$graph = new GanttGraph(0,0,"auto");
$graph->SetShadow();
$graph->SetBox();

// Titles for chart
$graph->title->Set("ENTREGA DE LICITACIONES");

$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);

// For illustration we enable all headers. 
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);

// For the week we choose to show the start date of the week
// the default is to show week number (according to ISO 8601)
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

// Change the scale font 
$graph->scale->week->SetFont(FF_FONT0);
$graph->scale->year->SetFont(FF_ARIAL,FS_BOLD,12);

//espacio entre linea
$graph->SetLabelVMarginFactor(0);

// Setup some data for the gantt bars
$progreso=0;
for($i=0; $i<count($data); ++$i) {
	$bar = new GanttBar($data[$i][0],$data[$i][1],$data[$i][2],$data[$i][3]);
	
	if (count($data[$i])>7) {
		if ($data[$i][10]==1)  {
		   $bar->SetPattern(BAND_RDIAG,"yellow"); //datos modificados
		   $bar->SetFillColor("black");
		}
		else {
			 $bar->SetPattern(BAND_RDIAG,"blue");  //datos sin modificar
		     $bar->SetFillColor("black");	 
		}
		
        $bar->SetFillColor("black");
		$bar->title->SetFont($data[$i][7],$data[$i][8],$data[$i][9]);
		$progreso=0;  //dibuja barra de progreso
	}	
	else { 
		$progreso=1;
		$bar->SetPattern(BAND_RDIAG,$data[$i][5]);
        $bar->SetFillColor("white");
	}
	
	// To indicate progress each bar can have a smaller bar within
	// For illustrative purpose just set the progress to 50% for each bar
	
	
	if ($progreso==1) {
		$bar->progress->SetPattern(BAND_RDIAG,$data[$i][4]); //barra de progreso
		$bar->SetFillColor("white");
	   	  if ($data[$i][6] !="" && $data[$i][6]!=0)
		       $bar->progress->Set($data[$i][6]/100);
	    
	}
	
    if ($data[$i][6]==2) {
    	   $bar->SetHeight(3);  //achica grosor de la linea
           $bar->SetFillColor("black");
    }
    	  
    else {
    	 $bar->SetHeight(9);
    	 
    }
    
	// ... and add the bar to the gantt chart
	$graph->Add($bar);
	
	
// agrega comentario
if (!isset($parametros['id_subir']) && count($data[$i])>7 && $data[$i][11]!="") {
$milestone = new MileStone($data[$i][0]-1,"",$data[$i][2],$data[$i][11]);
$milestone->title->SetColor("black");
$milestone->title->SetFont(FF_FONT1,FS_BOLD);
$graph->Add($milestone);
}
}

 
// Create a vertical line to emphasize the milestone
$vl = new GanttVLine($fecha_actual,"HOY","darkred");
$vl->SetDayOffset(0.5);	// Center the line in the day
$graph->Add($vl);

// Output the graph
$graph->Stroke();

?>
