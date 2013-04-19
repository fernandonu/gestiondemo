<?php
/*AUTOR: MAD
               14 diciembre 2004
$Author: marcelo $
$Revision: 1.11 $
$Date: 2004/12/21 18:28:18 $
*/
if (php_sapi_name() != "cli") exit;


$db_host = "localhost";
$db_user = "projekt";
$db_password = "propcp";
$db_name = "gestion";
$db_type = "postgres7";
require_once("../../lib/adodb/adodb.inc.php");
require_once("../../lib/adodb/adodb-pager.inc.php");
$db = &ADONewConnection($db_type) or die("Error al conectar a la base de datos");
$db->Connect($db_host, $db_user, $db_password, $db_name);


$sql="select * from ordenes.reporte_detalle order by id_reporte,num_rep";
$result = $db->execute($sql) or die("Error en la consulta principal");

for ($i=0;$i<=9;$i++) {
	if (!is_dir("reportes/$i")) mkdir("reportes/$i");
	for ($j=0;$j<=9;$j++) {
		if (!is_dir("reportes/$i/$j")) mkdir("reportes/$i/$j");
		for ($k=0;$k<=9;$k++) {
			if (!is_dir("reportes/$i/$j/$k")) mkdir("reportes/$i/$j/$k");
		}
	}
	
}
for($i=0;$i<$result->recordCount();$i++){
	$id_reporte = $result->fields["id_reporte"];
	$num_rep = $result->fields["num_rep"];
	$datos = $result->fields["detalle"] or $datos = "Por alguna razón el reporte quedo vacio.";
	$name = "$id_reporte-$num_rep";
	$path_name = "./reportes/$name.txt";
	if (strlen($id_reporte) < 3) {
		$id_reporte_tmp = sprintf("%03d",$id_reporte);
	}
	else { $id_reporte_tmp = $id_reporte; }
	$path_zip = substr($id_reporte_tmp,0,1)."/".substr($id_reporte_tmp,1,1)."/".substr($id_reporte_tmp,2,1);
	$id_reporte_tmp = $path_zip."/".$id_reporte_tmp;
	$zip_path_name = "./reportes/$id_reporte_tmp.zip";

	if (!$file=fopen($path_name,"w")) die("fallo al abrir el archivo '$path_name'");
	if (!fwrite($file,$datos)) die("error al escribir en archivo '$path_name'");
	fflush($file);
	fclose($file);

	$err = `/usr/bin/zip -j -9 -q -m -u "$zip_path_name" "$path_name"`;
	
	$sql_update = "update ordenes.reporte_detalle set file_name='$name' where id_reporte = $id_reporte and num_rep = $num_rep";
	$db->execute($sql_update) or die("error de actualización");
	
	$result->MoveNext();
}

?>
