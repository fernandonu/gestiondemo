<?PHP
/*AUTOR: MAD
               1 julio 2004
$Author: nazabal $
$Revision: 1.29 $
$Date: 2004/12/23 18:28:35 $
*/

/*Este Script sirve para colectar los datos no sincronizados y enviarlos hacia el ensamblador
*/
unset($HTTP_PROXY);
require_once("../../config.php");

$functions = "onload='document.focus();' onunload='window.opener.form1.submit();'";
echo str_replace("onload='document.focus();'",$functions,$html_header);
	
if(!isset($_GET["id"])){
	echo "Error: Parametro incorrecto en la entrada...<br>";
	exit();
}

	//obtener el paso hacia el ensamblador
	$sql_path = "Select id_ensamblador,http,ensamblador.nombre from ensamblador_quemado join ensamblador using(id_ensamblador) where id_entrada = ".$_GET["id"];
	$result_path = $db->Execute($sql_path) or die($db->ErrorMsg()."<br>$sql_path");


	if($result_path->fields["http"] == 'No definido') {
	echo "Este ensamblador no tiene configurado el servidor de quemado<br>";
	exit();
	}
	$id_ensamblador = $result_path->fields["id_ensamblador"];
	$ensamblador = $result_path->fields["nombre"];
	
	$url = $result_path->fields["http"].'/sincro_reply.php';


echo "<TABLE border='1'>
	<tr id='mo'>
	<TD>
	Sincronización de datos con el ensamblador $ensamblador...
	</TD>
	</tr>
	<tr>
	<TD>";

	
	
	//obtener los datos no sincronizados aun 
	$sql="select nro_orden,fecha_orden,fecha_ini_quemado,fecha_fin_quemado,maq_quemadas,cantidad,ensamblador.nombre,config_quemado.id_config, config_quemado.duracion, config_quemado.data,orden_quemado.estado";
	$sql= $sql." from ordenes.orden_quemado join ordenes.orden_de_produccion using (nro_orden) join ordenes.ensamblador using (id_ensamblador) join ordenes.config_quemado using (id_config)";
	$sql= $sql." where sinc = 0 and id_ensamblador = $id_ensamblador";

	$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
	$ensamblador = $result->Fields("nombre");
	$i=0;
	while (!$result->EOF) {
		$send[$i]["nro_orden"] = $result->Fields("nro_orden");
		$send[$i]["fecha_orden"] = $result->Fields("fecha_orden");
		$send[$i]["fecha_ini_quemado"] = $result->Fields("fecha_ini_quemado");
		$send[$i]["fecha_fin_quemado"] = $result->Fields("fecha_fin_quemado");
		$send[$i]["maq_quemadas"] = $result->Fields("maq_quemadas");
		$send[$i]["cantidad"] = $result->Fields("cantidad");
		$send[$i]["id_config"] = $result->Fields("id_config");
		$send[$i]["duracion"] = $result->Fields("duracion");
		$send[$i]["data"] = $result->Fields("data");
		$send[$i]["estado"] = $result->Fields("estado");
		
		$i++;
		$result->MoveNext();
	}	
   
	if($i==0) {
		echo "<font color='green'><b>-></b></font> No hay datos a sincronizar hacia el servidor de quemado... <br>";
	} else {
		echo "<font color='green'><b>-></b></font> Se recolectaron $i ordenes a sincronizar hacia el servidor de quemado... <br>";
	} 

   $params = "datos=".base64_encode(serialize($send));

	$j=0;
	while($j < $i) {
		$params.="&config_files_$j=".base64_encode($file_conf[$j]);
	$j++;
}
   $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

   $ch = curl_init();
   $e = 1;
   $e = $e && curl_setopt($ch, CURLOPT_PROXY,'');
   $e = $e && curl_setopt($ch, CURLOPT_POST,1);
   $e = $e && curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
   $e = $e && curl_setopt($ch, CURLOPT_URL,$url);
   $e = $e && curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
   $e = $e && curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
   $e = $e && curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
   $e = $e && curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  // this line makes it work under https

   $result=curl_exec ($ch);
   curl_close ($ch);

   if($i>0) {
   	echo "<font color='green'><b>-></b></font> Transferencia hacia el servidor de quemado completa...<br>";
   }
//======================== recibir datos desde el servidor de quemado =========
   
   $desde = 0;
   $leng = 2;
   
   $aux = substr($result,$desde,$leng);
   
   
   if ($aux != 'ok'){
   	echo 'Error en el server de quemado: Es posible que la dirección no este bien definida.<br>';
   	exit();
   }
   
	$desde = $leng + 4;
   $aux = '';
   while (($desde < strlen($result)) && ($result[$desde]!='<')){
   	$aux.=$result[$desde];
   	$desde++;
   }
   $leng = $aux;
   $desde += 4;
   
   $aux = substr($result,$desde,$leng);
   $ret1 = unserialize(gzuncompress(base64_decode($aux)));//ordenes que fueron tocadas
   

   $desde += ($leng + 4);
   $aux = '';
   while (($desde < strlen($result)) && ($result[$desde]!='<')){
   	$aux.=$result[$desde];
   	$desde++;
   }
   $leng = $aux;
   $desde += 4;
   
   $aux = substr($result,$desde,$leng);
   $ret2 = unserialize(gzuncompress(base64_decode($aux)));//reportes nuevos no sincronizados
   

   
   $desde += ($leng + 4);
   $aux = '';
   while (($desde < strlen($result)) && ($result[$desde]!='<')){
   	$aux.=$result[$desde];
   	$desde++;
   }
   $leng = $aux;
   $desde += 4;
   
   $aux = substr($result,$desde,$leng);
   $ret3 = unserialize(gzuncompress(base64_decode($aux)));//detalle de reportes no sincronizados
   

   
   
   $desde += ($leng + 4);
   $aux = '';
   while (($desde < strlen($result)) && ($result[$desde]!='<')){
   	$aux.=$result[$desde];
   	$desde++;
   }
   $leng = $aux;
   $desde += 4;
   
   $aux = substr($result,$desde,$leng);
   $ret4 = unserialize(gzuncompress(base64_decode($aux)));//Asociar reportes a la orden
   

   
   $desde += ($leng + 4);
   $aux = '';
   while (($desde < strlen($result)) && ($result[$desde]!='<')){
   	$aux.=$result[$desde];
   	$desde++;
   }
   $leng = $aux;
   $desde += 4;
   
   $aux = substr($result,$desde,$leng);
   $ret5 = unserialize(gzuncompress(base64_decode($aux)));//ordenes con fechas de quemado actualizadas
   
//   phpinfo();
   //print_r($ret1);
   //echo '<br><br><br>';
   //print_r($ret2);
   //echo '<br><br><br>';
//     print_r($ret3);
 //  echo '<br><br><br>';
 //  print_r($ret4);
 //  echo '<br><br><br>';
 //  print_r($ret5);
 //  echo '<br><br><br>';
  // die();
//====================== parte de guardar datos en la base =================   
//funcion para guardar los reportes
function guardar_reporte($id_reporte,$num_rep,$datos){
	$name = "$id_reporte-$num_rep";
	$path_name = enable_path("./reportes/$name.txt");
	
	if (strlen($id_reporte) < 3) {
		$id_reporte_tmp = sprintf("%03d",$id_reporte);
	}
	else { $id_reporte_tmp = $id_reporte; }
	$path_zip = substr($id_reporte_tmp,0,1)."/".substr($id_reporte_tmp,1,1)."/".substr($id_reporte_tmp,2,1);
	$id_reporte_tmp = $path_zip."/".$id_reporte_tmp;
	$zip_path_name = enable_path("./reportes/$id_reporte_tmp.zip");


	if (!$file=fopen($path_name,"w")) return  array(0,"fallo al abrir el archivo");
	if (fwrite($file,$datos) === FALSE) return array(0,"error al escribir en archivo");
	fflush($file);
	fclose($file);

	if (SERVER_OS == "linux") {
		$err = `/usr/bin/zip -j -9 -q -m -u "$zip_path_name" "$path_name"`;
	} elseif (SERVER_OS == "windows"){
		$paso = ROOT_DIR."\\lib\\zip";
		$err = shell_exec("$paso\\zip.exe -j -9 -q -m -u \"$zip_path_name\" \"$path_name\"");
	} else {
		return array(0,"Error en compresión.");
	}
	return array(1,"Ok.");

}//fin de funcion 
   

//si la sincronizacion hacia el server de quemado fue ok marco como sincronizado
if ($ret1 != '') {

	foreach ($ret1 as $aux) {
		$sql_update = "update orden_quemado set sinc = 1 where nro_orden = ".$aux;
		$db->Execute($sql_update) or die($db->ErrorMsg()."<br>".$sql_update);
	}
}


if($ret2 == '') {
	Echo "<font color='green'><b><-</b></font> No hay reportes para traer desde el servidor de quemado...<br>";
} else {

	echo "<font color='green'><b><-</b></font> Recolectando ".count($ret2)." reportes en el servidor de quemado....<br>";
//RECIBIR REPORTES
	$i=0;
	$j=0;
	$k=0;
	foreach ($ret2 as $aux_reportes) {

		
		//control de concordancia
		$sql_concord = "select * from reportes where nro_serie = '".$aux_reportes["nro_serie"]."' and fecha = '".$aux_reportes["fecha"]."'";
		$res_concord = $db->Execute($sql_concord) or die($sql_concord);  
		$repite = 0;
		
		if ($res_concord->RecordCount() > 0) {
			$repite = 1;
			$id_reporte = $res_concord->fields["id_reporte"];
		}

		$db->StartTrans();
		//calcular siguiente serial 
		if(!$repite){	
			$sql_next ="Select nextval('ordenes.reportes_id_reporte_seq') as id_reporte";
			if(!($resultado = $db->Execute($sql_next))) $err=1;  
			$id_reporte = $resultado->fields['id_reporte'];
		
			//reportes a sincronizar
			$sql_insert = "insert into reportes (id_reporte,nro_serie,mac,resultado,disco,cpu,placabase,fecha)
			values (".$id_reporte.",'".$aux_reportes["nro_serie"]."','".$aux_reportes["mac"]."',".$aux_reportes
			["resultado"].",'".$aux_reportes["disco"]."','".$aux_reportes["cpu"]."','".$aux_reportes["placabase"]."','".$aux_reportes["fecha"]."')";

			if(!$db->Execute($sql_insert)) {
 				$err=1;
				echo $sql_insert;
			}
		} else {//por concordancia
			//reportes a sincronizar
			$sql_update = "update reportes set resultado = ".$aux_reportes["resultado"]." where id_reporte = $id_reporte";

			if(!$db->Execute($sql_update)) {
 				$err=1;
				echo $sql_insert;
			}
			
		} //fin concordancia
 		
		//detalle de reportes  a sincronizar

		
		if(!$repite){
			$cont = 0;
			while ($cont < $aux_reportes["cant_rep"]) {
				$result_guardar=guardar_reporte($id_reporte,$ret3[$j]["num_rep"],$ret3[$j]["detalle"]);
				if ($result_guardar[0]) {
					$sql_insert = "insert into reporte_detalle (id_reporte,num_rep,file_name)
					values (".$id_reporte.",".$ret3[$j]["num_rep"].",'$id_reporte-".$ret3[$j]["num_rep"]."')";
					if(!$db->Execute($sql_insert)) {
						$err=1;
						echo $sql_insert;
					}
				} else {
					$err=1;
					print_r($result_guardar);
				}

 				$j++;
 				$cont++;
			}	
		} else { //por concordancia
			$cont = 0;
			while ($cont < $aux_reportes["cant_rep"]) {
				
				//comprobacion de concordancia a nivel de detalle
				$sql_concord_detalle = "select * from reporte_detalle where id_reporte = $id_reporte and num_rep = ".$ret3[$j]["num_rep"];
				$res_concord_detalle = $db->Execute($sql_concord_detalle) or die($sql_concord_detalle);  

				if($res_concord_detalle->RecordCount() == 0) { //es nuevo reporte			
					$result_guardar=guardar_reporte($id_reporte,$ret3[$j]["num_rep"],$ret3[$j]["detalle"]);
					if ($result_guardar[0]) {
						$sql_insert = "insert into reporte_detalle (id_reporte,num_rep,file_name)
						values (".$id_reporte.",".$ret3[$j]["num_rep"].",'$id_reporte-".$ret3[$j]["num_rep"]."')";
					} else {
						$err=1;
						echo $result_guardar[1];
					}

					if(!$db->Execute($sql_insert)) {
 	 					$err=1;
						echo $sql_insert;
					}
				}
 				$j++;
 				$cont++;
			}				
		}
 		

		//a que orden asocia
 		if(!$repite){
			$sql_insert = "insert into reporteorden (id_reporte,id_orden)
			values (".$id_reporte.",".$ret4[$k]["id_orden"].")";
 			if(!$db->Execute($sql_insert)) {
				echo $sql_insert;
 		 		$err=1;
 			}
 		}
 		
 		//orden tocada

 		$sql_update="update orden_quemado set fecha_ini_quemado = '".$ret5[$k]["fecha_ini_quemado"]."', fecha_fin_quemado = '".$ret5[$k]["fecha_fin_quemado"]."', maq_quemadas = ".$ret5[$k]["maq_quemadas"]." where nro_orden = ".$ret5[$k]["nro_orden"];
		if(!$db->Execute($sql_update)) {
			echo $sql_update;
       		$err=1;
 		}

 		$db->CompleteTrans();

 			
 		if ($err == 0) //codigos de retorno
 			$ret_rep[$i] = $aux_reportes["id_reporte"];
 		 		
 		$i++;
 		$k++;

	}	//fel foreach

	echo "<font color='green'><b><-</b></font> Reportes guardados...<br>";
//retornar los id de reportes sinc para marcarlos

   $params = "ret=".base64_encode(serialize($ret_rep));

   $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

   $ch = curl_init();
   $e = 1;
   $e = $e && curl_setopt($ch, CURLOPT_PROXY,'');
   $e = $e && curl_setopt($ch, CURLOPT_POST,1);
   $e = $e && curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
   $e = $e && curl_setopt($ch, CURLOPT_URL,$url);
   $e = $e && curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
   $e = $e && curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
   $e = $e && curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
   $e = $e && curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  // this line makes it work under https

   $result=curl_exec ($ch);

   curl_close ($ch);
   
   if ($result == 'ok') {
   	echo "<font color='green'><b><-</b></font> Sincronizacion terminada con exito...";
   }
}	
?>
</TD>
</TR>
<TR id="ma">
	<TD align="center">
	<INPUT type="button" name="cerrar" value="Cerrar" onclick="window.close();">
</TD>
</TR>
</table>
</HTML>


