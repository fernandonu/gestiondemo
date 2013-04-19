<?
/*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2003/07/28 19:22:19 $
*/

//session_cache_limiter("public");

$path_pre="../";
$include_path = $path_pre."lib/lib.inc.php";
include_once $include_path;

$cmd=$_GET[cmd];

/*
$ID=884;
$FileID=736;
$cmd="presentadas";
*/
if ((!$ID) or (!$FileID)) {
	Header("Location: licitaciones.php");
}
$sql = "SELECT licitaciones.archivos.*,licitaciones.licitaciones.Fecha ";
$sql .= "FROM licitaciones.archivos ";
$sql .= "INNER JOIN licitaciones.licitaciones ";
$sql .= "ON licitaciones.archivos.IDLicitación=licitaciones.licitaciones.IDLicitación ";
$sql .= "WHERE licitaciones.archivos.IDArchivo=$FileID";
$result = db_query($sql) or db_die($sql);
if (db_query_rows($result) <= 0) {
	Mostrar_Error("No se encontró el archivo");
}
else {
	$row = db_fetch_row($result);
	if ($Comp) {
		$FileName=$row[3];
		$FileType="application/zip";
		$FileSize=$row[9];
	}
	else {
		$FileName=$row[2];
		$FileType=$row[6];
		$FileSize=$row[5];
	}
	$fecha = substr($row[11],0,4);
	$sql = "SELECT licitaciones.entidades.Descripción,licitaciones.distrito.Distrito ";
	$sql .= "FROM (licitaciones.licitaciones ";
	$sql .= "INNER JOIN licitaciones.entidades ";
	$sql .= "ON licitaciones.licitaciones.IDEntidad=licitaciones.entidades.IDEntidad) ";
	$sql .= "INNER JOIN licitaciones.distrito ";
	$sql .= "ON licitaciones.entidades.IDDistrito=licitaciones.distrito.IDDistrito ";
	$sql .= "WHERE licitaciones.licitaciones.IDLicitación=$ID";
	$result = db_query($sql) or db_die($sql);
	$row = db_fetch_row($result);
	$distrito = $row[1];
	$entidad = $row[0];
	$FilePath="$dateien/Licitaciones/$distrito/$entidad/$fecha/$ID";

	$FileNameFull="$FilePath/$FileName";
//	echo "F: $FileNameFull <br>sql: $sql"; exit;
	if (($Comp) or (substr($FileName,strrpos($FileName,".")) == ".zip")) {
		if (file_exists($FileNameFull)) {
			Mostrar_Header($FileName,$FileType,$FileSize);
			readfile($FileNameFull);
		}
		else {
			Mostrar_Error("Se produjo un error al intentar abrir el archivo comprimido");
		}
	}
	else {
		$FileNameFull = substr($FileNameFull,0,strrpos($FileNameFull,"."));
		$fp = popen("/usr/bin/unzip -p \"$FileNameFull\"","r");
//		if (!is_object($fp)) {
//			Mostrar_Error("Se produjo un error al intentar descomprimir el archivo");
//		}
//		else {
			Mostrar_Header($FileName,$FileType,$FileSize);
			fpassthru($fp);
			pclose($fp);
//		}
	}
}
function Mostrar_Error($msg) {
	global $bgcolor3, $ID, $cmd;
	echo "<html><body bgcolor=$bgcolor3>";
	echo "<form action=licitaciones.php method=post>\n";
	echo "<input type=hidden name=mode value=view>\n";
	echo "<input type=hidden name=cmd value='$cmd'>\n";
	echo "<input type=hidden name=cmd1 value=detalle>\n";
	echo "<input type=hidden name=ID value=$ID>\n";
	echo "<table bgcolor=$bgcolor3 align=center width=80%>\n";
	echo "<tr><td align=center>\n";
	echo "<font size=4 color=#FF0000><b>$msg</b></font>\n";
	echo "</td></tr>\n";
	echo "<tr><td align=center>\n";
	echo "<input type=submit name=down_error value='Volver a la licitación'>\n";
	echo "</td></tr>\n";
	echo "</table></form>\n";
	echo "</body></html>";
}
function Mostrar_Header($FileName,$FileType,$FileSize) {
	Header("Cache-Control: post-check=0,pre-check=0");
	Header("Content-Type: $FileType");
	Header("Content-Transfer-Encoding: binary"); 
	Header("Content-Connection: close"); 
	Header("Content-Disposition: attachment; filename=\"$FileName\"");
	Header("Content-Description: $FileName");
	Header("Content-Length: $FileSize");
}
?>