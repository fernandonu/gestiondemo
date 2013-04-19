<?PHP
/*AUTOR: MAD
               1 julio 2004
$Author: marcelo $
$Revision: 1.13 $
$Date: 2004/12/21 19:32:17 $
*/

/*Este Script sirve para colectar los datos no sincronizados y enviarlos hacia el ensamblador
en forma automática*/

if (php_sapi_name() != "cli") exit;
unset($HTTP_PROXY);

$db_host = "localhost";
$db_user = "projekt";
$db_password = "propcp";
$db_name = "gestion";
$db_type = "postgres7";

require_once("../../lib/adodb/adodb.inc.php");
require_once("../../lib/adodb/adodb-pager.inc.php");
$db = &ADONewConnection($db_type) or die("Error al conectar a la base de
datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);
	 
$id = $_SERVER["argv"][1]; //el ensamblador coradir en el devel

 
$fecha = date("Y-m-d H:i:s",mktime());

echo "Sincronización automática [$fecha]\n";

if (!is_numeric($id)) die("Error, el parametro identificatorio del ensamblador no fue seteado...\n");

	//obtener el paso hacia el ensamblador
	$sql_path = "Select id_ensamblador,http,ensamblador.nombre from
ordenes.ensamblador_quemado join ordenes.ensamblador using(id_ensamblador) where id_entrada =
$id";
	
	$result_path = $db->Execute($sql_path) or die("error en: $sql_path");


	if($result_path->fields["http"] == 'No definido') {
	echo "Este ensamblador no tiene configurado el servidor de
quemado...\n";
	exit();
	}
	$id_ensamblador = $result_path->fields["id_ensamblador"];
	$ensamblador = $result_path->fields["nombre"];
	
	$url = $result_path->fields["http"].'/sincro_reply.php';


echo "Sincronización de datos con el ensamblador $ensamblador...\n";
	
	
	//obtener los datos no sincronizados aun 
	$sql="select nro_orden,fecha_orden,fecha_ini_quemado,fecha_fin_quemado,maq_quemadas,cantidad,ensamblador.nombre,config_quemado.id_config, config_quemado.duracion, config_quemado.data,orden_quemado.estado";
	$sql= $sql." from ordenes.orden_quemado join ordenes.orden_de_produccion using (nro_orden) join ordenes.ensamblador using (id_ensamblador) join ordenes.config_quemado using (id_config)";
	$sql= $sql." where sinc = 0 and id_ensamblador = $id_ensamblador";

	$result = $db->Execute($sql) or die("error en: $sql");
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
		echo "No hay datos a sincronizar hacia el servidor de quemado...\n";
	} else {
		echo "Se recolectaron $i ordenes a sincronizar hacia el servidor de quemado...\n";
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
   	echo "Transferencia hacia el servidor de quemado completa...\n";
   }
//======================== recibir datos desde el servidor de quemado =========
   
   $desde = 0;
   $leng = 2;
   
   $aux = substr($result,$desde,$leng);
   
   
   if ($aux != 'ok'){
   	echo 'Error en el server de quemado: Es posible que la dirección no este bien
definida.\n';
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
   
//toma una letra y un string como parametros y devuelve
//el numero de ocurrencias de es letra en ese string
function str_count_letra($letra,$string) {
 $largo=strlen($string);
 $counter=0;
 for($i=0;$i<$largo;$i++)
 {
  if($string[$i]==$letra)
   $counter++;
 }
 return $counter;

}



//====================== parte de guardar datos en la base =================   
//funcion para guardar los reportes
function guardar_reporte($id_reporte,$num_rep,$datos){

	$name = "$id_reporte-$num_rep";
	$path_name = "./reportes/$name.txt";

	if (strlen($id_reporte) < 3) {
		$id_reporte_tmp = sprintf("%03d",$id_reporte);
	}
	else { $id_reporte_tmp = $id_reporte; }
	$path_zip = substr($id_reporte_tmp,0,1)."/".substr($id_reporte_tmp,1,1)."/".substr($id_reporte_tmp,2,1);
	$id_reporte_tmp = $path_zip."/".$id_reporte_tmp;
	$zip_path_name = "./reportes/$id_reporte_tmp.zip";

	if (!$file=fopen($path_name,"w")) return  array(0,"fallo al abrir el archivo");
	if (!fwrite($file,$datos)) return array(0,"error al escribir en archivo");
	fflush($file);
	fclose($file);

	$err = `/usr/bin/zip -j -9 -q -m -u "$zip_path_name" "$path_name"`;

	return array(1,"Ok.");

}//fin de funcion 
   

//si la sincronizacion hacia el server de quemado fue ok marco como sincronizado
if ($ret1 != '') {

	foreach ($ret1 as $aux) {
		$sql_update = "update ordenes.orden_quemado set sinc = 1 where nro_orden = ".$aux;
		$db->Execute($sql_update) or die("error en: $sql_update");
	}
}


if($ret2 == '') {
	Echo "No hay reportes para traer desde el servidor de quemado...\n";
} else {

	echo "Recolectando ".count($ret2)." reportes en el servidor de quemado....\n";
//RECIBIR REPORTES
	$i=0;
	$j=0;
	$k=0;
	foreach ($ret2 as $aux_reportes) {

		
		//control de concordancia
		$sql_concord = "select * from ordenes.reportes where nro_serie = '".$aux_reportes["nro_serie"]."' and fecha = '".$aux_reportes["fecha"]."'";
		$res_concord = $db->Execute($sql_concord) or die("error en: $sql_concord");  
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
			$sql_insert = "insert into ordenes.reportes (id_reporte,nro_serie,mac,resultado,disco,cpu,placabase,fecha)
			values (".$id_reporte.",'".$aux_reportes["nro_serie"]."','".$aux_reportes["mac"]."',".$aux_reportes
			["resultado"].",'".$aux_reportes["disco"]."','".$aux_reportes["cpu"]."','".$aux_reportes["placabase"]."','".$aux_reportes["fecha"]."')";

			if(!$db->Execute($sql_insert)) {
 				$err=1;
				echo $db->ErrorMessage()."\n$sql_insert\n";
				exit();
			}
		} else {//por concordancia
			//reportes a sincronizar
			$sql_update = "update ordenes.reportes set resultado = ".$aux_reportes["resultado"]." where id_reporte = $id_reporte";

			if(!$db->Execute($sql_update)) {
 				$err=1;
				echo "$sql_update\n";
			}
			
		} //fin concordancia
 		
		//detalle de reportes  a sincronizar

		
		if(!$repite){
			$cont = 0;
			while ($cont < $aux_reportes["cant_rep"]) {
				$result_guardar=guardar_reporte($id_reporte,$ret3[$j]["num_rep"],$ret3[$j]["detalle"]);
				if ($result_guardar[0]) {
					$sql_insert = "insert into ordenes.reporte_detalle (id_reporte,num_rep,file_name)
					values (".$id_reporte.",".$ret3[$j]["num_rep"].",'$id_reporte-".$ret3[$j]["num_rep"]."')";
				} else {
					$err=1;
					echo $result_guardar[1]."\n";
				}

				if(!$db->Execute($sql_insert)) {
 	 				$err=1;
					echo "$sql_insert\n";
				}
 				$j++;
 				$cont++;
			}	
		} else { //por concordancia
			$cont = 0;
			while ($cont < $aux_reportes["cant_rep"]) {
				
				//comprobacion de concordancia a nivel de detalle
				$sql_concord_detalle = "select * from ordenes.reporte_detalle where id_reporte = $id_reporte and num_rep = ".$ret3[$j]["num_rep"];
				$res_concord_detalle = $db->Execute($sql_concord_detalle) or die("error en: $sql_concord_detalle");  

				if($res_concord_detalle->RecordCount() == 0) { //es nuevo reporte			
					$result_guardar=guardar_reporte($id_reporte,$ret3[$j]["num_rep"],$ret3[$j]["detalle"]);
					if ($result_guardar[0]) {
						$sql_insert = "insert into ordenes.reporte_detalle (id_reporte,num_rep,file_name)
						values (".$id_reporte.",".$ret3[$j]["num_rep"].",'$id_reporte-".$ret3[$j]["num_rep"]."')";
					} else {
						$err=1;
						echo $result_guardar[1]."\n";
					}

					if(!$db->Execute($sql_insert)) {
 	 					$err=1;
						echo "$sql_insert\n";
					}
				}
 				$j++;
 				$cont++;
			}				
		}
 		

		//a que orden asocia
 		if(!$repite){
			$sql_insert = "insert into ordenes.reporteorden (id_reporte,id_orden)
			values (".$id_reporte.",".$ret4[$k]["id_orden"].")";
 			if(!$db->Execute($sql_insert)) {
				echo "$sql_insert\n";
 		 		$err=1;
 			}
 		}
 		
 		//orden tocada

 		$sql_update="update ordenes.orden_quemado set fecha_ini_quemado = '".$ret5[$k]["fecha_ini_quemado"]."', fecha_fin_quemado = '".$ret5[$k]["fecha_fin_quemado"]."', maq_quemadas = ".$ret5[$k]["maq_quemadas"]." where nro_orden = ".$ret5[$k]["nro_orden"];
		if(!$db->Execute($sql_update)) {
			echo "$sql_update\n";
       		$err=1;
 		}

 		$db->CompleteTrans();

 			
 		if ($err == 0) //codigos de retorno
 			$ret_rep[$i] = $aux_reportes["id_reporte"];
 		 		
 		$i++;
 		$k++;

	}	//fel foreach

	echo "Reportes guardados...\n";
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
   	echo "Sincronizacion terminada con exito...\n";
   }
}	
?>