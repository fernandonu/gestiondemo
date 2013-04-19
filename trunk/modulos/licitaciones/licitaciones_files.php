<?
/*
$Author: nazabal $
$Revision: 1.2 $
$Date: 2003/07/28 19:22:19 $
*/

// check whether the lib has been included - authentication!
if (!defined("lib_included")) { die("Please use index.php!"); }

if ($dat_crypt) {
	include_once($path_pre."lib/scramble.inc.php");
}

$extensiones = array("doc","obd","xls","zip");
$maxsize=get_cfg_var(upload_max_filesize);

if ($HTTP_POST_VARS[files_cancel]) {
	$cmd1 = "detalle";
	include_once("./licitaciones_view.php");
}
elseif ($HTTP_POST_VARS[files_add]) {
	ProcForm($HTTP_POST_FILES);
	$cmd1 = "detalle";
	include_once("./licitaciones_view.php");
}
else {

	if (!$files_cant) $files_cant=1;

	echo "<form action=licitaciones.php?mode=files method=POST enctype='multipart/form-data'>\n";
	echo "<input type=hidden name=PHPSESSID value=$PHPSESSID>\n";
	echo "<input type=hidden name=ID value='$ID'>\n";
	echo "<input type=hidden name=cmd value='$cmd'>\n";
	echo "<br><table border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>\n";
	echo "<tr>\n";
	echo "<td>Cantidad de archivos:\n";
	echo "<td><select name=files_cant onchange='document.forms[0].submit()'>\n";
	for ($i=1; $i<=20; $i++) {
		echo "<option value='$i'";
		if ($i == $files_cant) echo " selected";
		echo ">$i\n";
	}
	echo "</td>\n";
	echo "</tr><tr>\n";
	echo "<td colspan=2 align=center>\n";
	for ($i=1; $i<=$files_cant; $i++) {
		echo "$i: <input type=file name='archivo[]' size=20><br>\n";
	}
	echo "</td>\n";
	echo "</tr><tr>\n";
	echo "<td align=center colspan=2>\n";
	echo "<input type=submit name='files_add' value='Aceptar'>&nbsp;&nbsp;&nbsp;\n";
	echo "<input type=submit name='files_cancel' value='Cancelar'>\n";
	echo "</table>\n";
	echo "</form>\n";
}
	
function ProcForm($FVARS) {
	global $maxsize,$dateien,$extensiones,$ID,$bgcolor2;
	echo "<table border=0 cellspacing=1 cellpadding=2 bgcolor=$bgcolor2 align=center>";
	echo "<tr><td colspan=2 align=center bgcolor=$bgcolor2 id=ma>Agregando archivos</td></tr>\n";
//	$sql = "select licitaciones.entidades.Descripción,licitaciones.tipo_entidad.TipoE,licitaciones.distrito.Distrito from licitaciones.licitaciones,licitaciones.entidades,licitaciones.tipo_entidad,licitaciones.distrito where licitaciones.licitaciones.IDLicitación = $ID and licitaciones.licitaciones.IDEntidad=licitaciones.entidades.IDEntidad and licitaciones.entidades.IDDistrito=licitaciones.distrito.IDDistrito and licitaciones.entidades.IDTipoE=licitaciones.tipo_entidad.IDTipoE group by licitaciones.licitaciones.IDLicitación";
	$sql = "SELECT licitaciones.entidades.Descripción,licitaciones.distrito.Distrito,licitaciones.licitaciones.Fecha ";
	$sql .= "FROM (licitaciones.licitaciones ";
	$sql .= "INNER JOIN licitaciones.entidades ";
	$sql .= "ON licitaciones.licitaciones.IDEntidad=licitaciones.entidades.IDEntidad) ";
	$sql .= "INNER JOIN licitaciones.distrito ";
	$sql .= "ON licitaciones.entidades.IDDistrito=licitaciones.distrito.IDDistrito ";
	$sql .= "WHERE licitaciones.licitaciones.IDLicitación=$ID";
	$result = db_query($sql) or db_die($sql);
	$row = db_fetch_row($result);
//	$distrito = $row[2];
	$distrito = $row[1];
//	$tipoentidad = $row[1];
	$entidad = $row[0];
//	$fecha = date("Y",mktime());
	$fecha = substr($row[2],0,4);
//	$path="$dateien/Licitaciones/$distrito/$tipoentidad/$entidad/$fecha/$ID";
	$path="$dateien/Licitaciones/$distrito/$entidad/$fecha/$ID";
	for($i=0;$i<count($FVARS[archivo][tmp_name]);$i++) {
		$size=$FVARS[archivo][size][$i];
		$type=$FVARS[archivo][type][$i];
		$name=$FVARS[archivo][name][$i];
		$temp=$FVARS[archivo][tmp_name][$i];
		FileUpload($temp,$size,$name,$type,$maxsize,$path,"",$extensiones,"",1);
	}
	echo "</table>\n";
}

function FileUpload($TempFile, $FileSize, $FileName, $FileType, $MaxSize, $Path, $ErrorFunction, $ExtsOk, $ForceFilename, $OverwriteOk) {
	global $dat_crypt,$ID,$user_name,$user_firstname;
	$ErrNo = -1;
	if (strlen($ForceFilename)) { $FileName = $ForceFilename; }
	$err=`mkdir -p '$Path'`;

	if (!function_exists($ErrorFunction)) {
		if (!function_exists('DoFileUploadDefErrorHandle')) {
			function DoFileUploadDefErrorHandle($ErrorNumber, $ErrorText) {
				echo "<tr><td colspan=2 align=center><font color=red><b>Error $ErrorNumber: $ErrorText</b></font><br><br></td></tr>";
			}
		}
		$ErrorFunction = 'DoFileUploadDefErrorHandle';
	}

//	echo <<<HTML
//	<center>Subiendo el archivo $FileName:
//	<table>
	echo "<tr><td>Nombre:</td><td>$FileName</td></tr>\n";
	echo "<tr><td>Tamaño:</td><td>$FileSize</td></tr>\n";
	echo "<tr><td>Tipo MIME:</td><td>$FileType</td></tr>\n";
//	</table>
//	</center>
//HTML;

	if($TempFile == 'none' || $TempFile == '') {
		$ErrorTxt = "No se especificó el archivo.";
		$ErrNo = 1;
		$ErrorFunction($ErrNo, $ErrorTxt);
		return $ErrNo; 
	}

	if(!is_uploaded_file($TempFile)) {
		$ErrorTxt = "File Upload Attack, Filename: \"$FileName\"";
		$ErrNo = 2;
		$ErrorFunction($ErrNo, $ErrorTxt);
		return $ErrNo;
	}

	if($FileSize == 0) {
		$ErrorTxt = 'El archivo que ha intentado subir, está vacio!';
		$ErrNo = 3;
		$ErrorFunction($ErrNo, $ErrorTxt);
		return $ErrNo;
	}

/*
	$TheExt = GetExt($FileName);

	foreach ($ExtsOk as $CurNum => $CurText) {
		if ($TheExt == $CurText) { $FileExtOk = 1; }
	}

	if($FileExtOk != 1) {
		$ErrorTxt = 'You attempted to upload a file with a disallowed extention!';
		$ErrNo = 4;
		$ErrorFunction($ErrNo, $ErrorTxt);
		return $ErrNo;
	}
*/

	if($FileSize > $MaxSize) {
		$ErrorTxt = 'El archivo que ha intentado subir excede el máximo de ' . ($MaxSize / 1024) . 'kb.';
		$ErrNo = 5;
		$ErrorFunction($ErrNo, $ErrorTxt);
		return $ErrNo;
	}

	if(file_exists($Path."/".$FileName) && !strlen($OverwriteOk)) {
		$ErrorTxt = 'El archivo que ha intentado subir ya existe. Por favor especifique un nombre distinto.';
		$ErrNo = 6;
		$ErrorFunction($ErrNo, $ErrorTxt);
		return $ErrNo;
	}
	
	$FileNameFull = $Path."/".$FileName;

	move_uploaded_file ($TempFile, $FileNameFull);

	$dat_crypt = 0;  // Habilitar el renombrado de archivos
	
	$ext = strtolower(GetExt($FileNameFull));
	if ($ext != "zip") {
		if ($dat_crypt) {
			$FileNameOld = $FileNameFull;
			$FileNameFull = $Path."/".scramble();
			$err = `/bin/pkzip -add -nozip -silent -dir=none "$FileNameFull" "$FileNameOld"`;
			unlink($FileNameOld);
			if ($err) {
				$ErrorTxt = "No se pudo comprimir el archivo $FileName";
				$ErrNo = 7;
				$ErrorFunction($ErrNo, $ErrorTxt);
				return $ErrNo;
			}
		}
		else {
			$FileNameOld = $FileNameFull;
			$FileNameFull = substr($FileNameFull,0,strlen($FileNameFull) - strpos(strrev($FileNameFull),".") - 1).".zip";
//			$err = `/bin/pkzip -add -dir=none "$FileNameFull" "$FileNameOld"`;
			$err = `/usr/bin/zip -j -9 -q "$FileNameFull" "$FileNameOld"`;
			unlink($FileNameOld);
			if ($err) {
				$ErrorTxt = "No se pudo comprimir el archivo $FileName";
				$ErrNo = 8;
				$ErrorFunction($ErrNo, $ErrorTxt);
				return $ErrNo;
			}
		}
	}
	$FileSizeComp=filesize($FileNameFull);
	echo "<tr><td>Tamaño comprimido:</td><td>$FileSizeComp</td></tr>\n";

	chmod ($FileNameFull, 0600);
	
	$FileNameComp = substr($FileNameFull,strrpos($FileNameFull,"/") + 1);
	$FileDateUp = date("Y-m-d H:i:s", mktime());

	$sql = "select IDArchivo,IDLicitación,Nombre from licitaciones.archivos where IDLicitación = $ID and Nombre = '$FileName'";
	$result = db_query($sql) or db_die($sql);
	$cant_filas = db_query_rows($result);
//	echo "f: $cant_filas";
	if ($cant_filas == 0) {
		$sql = "insert into licitaciones.archivos (IDLicitación, Nombre, NombreComp, Tamaño, TamañoComp, Tipo, SubidoFecha, SubidoUsuario) values ($ID, '$FileName', '$FileNameComp', $FileSize, $FileSizeComp, '$FileType', '$FileDateUp', '$user_firstname $user_name')";
//		echo "sql: $sql";
		$result = db_query($sql) or db_die($sql);
	}
	else {
		$sql = "update licitaciones.archivos set Tamaño=$FileSize, TamañoComp=$FileSizeComp, SubidoFecha='$FileDateUp', SubidoUsuario='$user_firstname $user_name' where IDLicitación = $ID and Nombre = '$FileName'";
		$result = db_query($sql) or db_die($sql);
	}
	echo "<tr><td colspan=2 align=center><b>Archivo subido correctamente!</b><br><br></td></tr>\n";
	return $ErrNo;
}

function GetExt($Filename) {
	$RetVal = explode ( '.', $Filename);
	return $RetVal[count($RetVal)-1];
}

?>