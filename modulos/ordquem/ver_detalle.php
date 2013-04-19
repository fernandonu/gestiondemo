<?php
/*AUTOR: MAD
               1 julio 2004
$Author: nazabal $
$Revision: 1.9 $
$Date: 2005/03/28 18:21:02 $
*/
/*
Muestra el detalle de un reporte del programa de quemado, aqui se muestran los errores del quemado
*/
require_once("../../config.php");

echo $html_header;

$id_reporte = $_GET["id_reporte"];
$num_rep = $_GET["num_rep"];

$sql="select file_name from reporte_detalle where id_reporte = $id_reporte and num_rep = $num_rep";
$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");

$nombre = $result->fields["file_name"];

chdir("./reportes");

if (strlen($id_reporte) < 3) {
	$id_reporte_tmp = sprintf("%03d",$id_reporte);
}
else { $id_reporte_tmp = $id_reporte; }
$path_zip = substr($id_reporte_tmp,0,1)."/".substr($id_reporte_tmp,1,1)."/".substr($id_reporte_tmp,2,1);
$id_reporte_tmp = $path_zip."/".$id_reporte_tmp;
$zip_path_name = enable_path("$id_reporte_tmp.zip");

if (SERVER_OS == "linux") {
	$err = `/usr/bin/unzip "$zip_path_name" "$nombre.txt"`;
} elseif (SERVER_OS == "windows"){
	$paso = ROOT_DIR."\\lib\\zip";
	$err = shell_exec("$paso\\unzip.exe \"$zip_path_name\" \"$nombre.txt\"");
} else {
	die("Error en descompresión.");
}

if (file_exists("$nombre.txt")) {
	$file = fopen("$nombre.txt","r");
	$length = filesize("$nombre.txt");
	if ($length > 0) {
		$detalle = fread($file,$length);
	}
	else {
		$detalle = "EL REPORTE ESTA VACIO!!!";
	}
	fclose($file);
	
	unlink("$nombre.txt");
}
else {
	$detalle = "NO EXISTE EL ARCHIVO DEL REPORTE!!!";
}
?>
<BR>
<CENTER><H2>Detalle del reporte de quemado</H2></CENTER>
<DIV style="position:relative; width:100%;height:80%; overflow:auto;">
<?echo str_replace("\n","<br>",$detalle);?>
</DIV>
<BR>
<INPUT type="button" value="Cerrar" onclick="window.close();">
