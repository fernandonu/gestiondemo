<?
/*AUTOR: MAC
 FECHA: 09/03/05

$Author: marco_canderle $
$Revision: 1.20 $
$Date: 2005/03/10 21:14:15 $
*/
require_once("../../config.php");



function parse_linea($linea){
	$aux=split(" ",$linea);
//	print_r($aux);
//	echo "<br>";
	$i=0;
	$ret["name"]='';
	
	while($i<count($aux) && !is_numeric($aux[$i])){//descripcion
		$ret["name"].=$aux[$i]." ";
		$i++;
	}
//	echo $i;
	if(substr_count($ret["name"],"Network")>0){
		if ($i==count($aux)) return 0;
		while($i<count($aux) && is_numeric($aux[$i])){//numero de red
			$ret["name"].=$aux[$i]." ";
			$i++;
		}
		if ($i==count($aux)) return 0;
		while($i<count($aux) && !is_numeric($aux[$i])){//espacio
			$i++;
		}
	}

	if ($i==count($aux)) return 0;
	while($i<count($aux) && is_numeric($aux[$i])){//primer numero
		$i++;
	}
	if ($i==count($aux)) return 0;
	while($i<count($aux) && !is_numeric($aux[$i])){//espacio
		$i++;
	}
	if ($i==count($aux)) return 0;
	while($i<count($aux) && is_numeric($aux[$i])){//segundo numero
		$i++;
	}
	if ($i==count($aux)) return 0;
	while($i<count($aux) && !is_numeric($aux[$i])){//espacio
		$i++;
	}
	if ($i>=count($aux)) return 0;

	$ret["#errores"]="";
	while($i<count($aux) && is_numeric($aux[$i])){//tercer numero
		$ret["#errores"].=$aux[$i];
		$i++;
	}

	if ($i>=count($aux)) return 0;

	$ret["tipo"]='';
	while($i<count($aux)){
		$ret["tipo"].=$aux[$i]." ";
		$i++;
	}	
	return $ret;
}













$db->StartTrans();
echo "Comenzando ejecucion...<br>";
	
if(chdir("./reportes"))
	 echo "<br><br>OK<br><br>";
else  
	 echo "<br><br>NO OK<br><br>";
	 
//traemos todas las ordenes de quemado que hay en el sistema
$query="select nro_orden from ordenes.orden_quemado order by nro_orden";
$orde_quem=sql($query,"<br>Error al traer daros de orden quemado") or fin_pagina();
$cant_actualizaciones=0;
//por cada orden de quemado...
while(!$orde_quem->EOF)
{ $nro_orden_quem=$orde_quem->fields['nro_orden'];
  $reportes_error=0;
  echo "<br><br>-----------------------------------------------";
  echo "<br>Analizando Orden de Quemado $nro_orden_quem...<br>";
  
  //traemos todos los reportes de la orden de quemado que estamos recorriendo
  $query="select id_reporte from reporteorden where id_orden=$nro_orden_quem";
  $reportes_quem=sql($query,"<br>Error al traer reportes de la orden<br>") or fin_pagina();
  
  //por cada reporte de la orden de quemado
  while	(!$reportes_quem->EOF)
  {
  	$id=$reportes_quem->fields['id_reporte'];
	
	$sql="select reporte_detalle.file_name,nro_serie,resultado from reportes
              join reporte_detalle using(id_reporte) where id_reporte = $id";
	$result_detalle_rep = sql($sql,"Error buscando errores");

	if ($result_detalle_rep->RecordCount()==0) echo "<br>---------------------No hay datos para analizar.----------------------<br>";
   
	$nro_serie=$result_detalle_rep->fields["nro_serie"];


	if (strlen($id_reporte) < 3) {
		$id_reporte_tmp = sprintf("%03d",$id);
	}
	else { $id_reporte_tmp = $id; }
	$path_zip = substr($id_reporte_tmp,0,1)."/".substr($id_reporte_tmp,1,1)."/".substr($id_reporte_tmp,2,1);
	$id_reporte_tmp = $path_zip."/".$id_reporte_tmp;echo "Paso: $id_reporte_tmp";
	$zip_path_name = enable_path("$id_reporte_tmp.zip");

	if (SERVER_OS == "linux") {
		$err = `/usr/bin/unzip "$zip_path_name"`;
	} elseif (SERVER_OS == "windows"){
		$paso = ROOT_DIR."\\lib\\zip";
		$err = shell_exec("$paso\\unzip.exe \"$zip_path_name\"");
	} else {
		die("Error en descompresión.");
	}
    
	$hay_error_2d=0;
	$hay_otros_errores=0;

	for($i=0;$i<$result_detalle_rep->RecordCount();$i++)
	{
		$resultado_reporte=$result_detalle_rep->fields["resultado"];
		$nombre = $result_detalle_rep->fields["file_name"];
		$file = fopen("$nombre.txt","r");
		$length = filesize("$nombre.txt");
		if ($length > 0)
		{
			$report = fread($file,$length);

			//$report=$result_detalle_rep->fields['detalle'];
			$report=explode("DETAILED ERROR LOG:\n",$report);
			$report = $report[0];
			$report = split("\n",$report);
			$t=0;

			for($j=25;$j<33;$j++)
			{
				$result_linea=parse_linea($report[$j]);

				$result_total[$t]["name"]=$result_linea["name"];
				if (substr_count($result_total[$t]["name"],"2D Graphics"))
				 $hay_error_2d++;
				else
				 $hay_otros_errores+=$result_linea["#errores"];
				$t++;
			}//de for($j=25;$j<33;$j++)

		}//de if ($length > 0)
		fclose($file);
		unlink("$nombre.txt");

		$result_detalle_rep->MoveNext();
	}//de for($i=0;$i<$result_detalle_rep->RecordCount();$i++)
	   	
		//si hubo solo erroes 2d Graphics, ponemos el estado del reporte en 1 (terminado sin errores), solo si el reporte esta 
		//en estado 3 (con errores)
		echo "<br>##############################<br>";
		echo "Errores 2D: $hay_error_2d - Otros Errores: $hay_otros_errores<br>Resultado reporte $resultado_reporte";
		echo "<br>##############################<br>";
		if($hay_error_2d && !$hay_otros_errores)
		{if($resultado_reporte==3)
		 {$query="update reportes set resultado=1 where id_reporte=$id";
		  sql($query,"<br>Error al actualizar el resultado de reportes") or fin_pagina(); 
		  echo "<br>Actualizando el reporte $id de la orden $nro_orden_quem<br>";
		  $cant_actualizaciones++;
		  $reportes_error++;
		 }
		}
		
	$reportes_quem->MoveNext();
  }//de while	(!$reportes_quem->EOF)

  
  echo "Cantidad de errores de maquinas: $reportes_error<br>";
  echo "<br><br>-----------------------------------------------<br>";
  $orde_quem->MoveNext();
}//de while(!$orde_quem->EOF)

echo "<br>Actualización lista (en total: $cant_actualizaciones reportes actualizados), sin completar transaccion";
$db->CompleteTrans();
?>