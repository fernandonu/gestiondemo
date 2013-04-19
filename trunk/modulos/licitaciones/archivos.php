<?

$max_file_size = 5120000;	// 5mb
$path="/www/gestion/uploads";

$files_cant = $_POST["files_cant"] or $files_cant = 1;

// Procesar el formulario se se presiono el boton aceptar
if ($_POST["files_add"]) {
	ProcForm($_FILES);
}

function mkdirs($strPath, $mode = "0700") {
	if (SERVER_OS == "windows") {
		$strPath = ereg_replace("/","\\",$strPath);
	}
	if (is_dir($strPath)) return true;
	$pStrPath = dirname($strPath);
	if (!mkdirs($pStrPath, $mode)) return false;
	return mkdir($strPath);
}

function ProcForm($FVARS) {
	global $max_file_size,$path;
	for($i=0;$i<count($FVARS["archivo"]["tmp_name"]);$i++) {
		$size=$FVARS["archivo"]["size"][$i];
		$type=$FVARS["archivo"]["type"][$i];
		$name=$FVARS["archivo"]["name"][$i];
		$temp=$FVARS["archivo"]["tmp_name"][$i];
		$ret = FileUpload($temp,$size,$name,$type,$max_file_size,$path,1);
	}
}

function FileUpload($TempFile, $FileSize, $FileName, $FileType, $MaxSize, $Path, $OverwriteOk) {
	$ErrNo = 0;
	mkdirs($Path);

	echo "Nombre: $FileName<br>\n";
	echo "Tamaño: $FileSize<br>\n";
	echo "Tipo MIME: $FileType<br>\n";

	if($TempFile == 'none' || $TempFile == '') {
		$ErrorTxt = "No se especificó el nombre del archivo<br>";
		$ErrorTxt .= "o el archivo excede el máximo de tamaño de:<br>";
		$ErrorTxt .= ($MaxSize / 1024)." Kb.";
		$ErrNo = 1;
		FileUploadError($ErrNo, $ErrorTxt);
		return $ErrNo;
	}

	if(!is_uploaded_file($TempFile)) {
		$ErrorTxt = "File Upload Attack, Filename: \"$FileName\"";
		$ErrNo = 2;
		FileUploadError($ErrNo, $ErrorTxt);
		return $ErrNo;
	}

	if($FileSize == 0) {
		$ErrorTxt = 'El archivo que ha intentado subir, está vacio!';
		$ErrNo = 3;
		FileUploadError($ErrNo, $ErrorTxt);
		return $ErrNo;
	}

	if($FileSize > $MaxSize) {
		$ErrorTxt = 'El archivo que ha intentado subir excede el máximo de ' . ($MaxSize / 1024) . 'kb.';
		$ErrNo = 5;
		FileUploadError($ErrNo, $ErrorTxt);
		return $ErrNo;
	}

	if(file_exists($Path."/".$FileName) && !strlen($OverwriteOk)) {
		$ErrorTxt = 'El archivo que ha intentado subir ya existe. Por favor especifique un nombre distinto.';
		$ErrNo = 6;
		FileUploadError($ErrNo, $ErrorTxt);
		return $ErrNo;
	}

	$FileNameFull = $Path."/".$FileName;		// linux
//	$FileNameFull = $Path."\\".$FileName;		// windows

	move_uploaded_file ($TempFile, $FileNameFull);

	chmod ($FileNameFull, 0600);

	echo "Archivo subido correctamente!<br>\n";
	return $ErrNo;
}

function FileUploadError($ErrorNumber, $ErrorText) {
	echo "Error $ErrorNumber: $ErrorText";
}
?>

<form action=archivos.php method=POST enctype='multipart/form-data'>
<br>
<table border=1 cellspacing=0 cellpadding=5 align=center>
<tr>
	<td colspan=2 align=center><font size=3><b>Agregar archivos</b></font></td>
</tr>
<tr>
	<td align=right><b>Cantidad de archivos:</b>
	<select name=files_cant onchange='document.forms[0].submit()'>
<?
	for ($i=1; $i<=20; $i++) {
		echo "<option value='$i'";
		if ($i == $files_cant) echo " selected";
		echo ">$i\n";
	}
?>
	</td>
</tr>
<tr>
	<td align=center>
	<input type='hidden' name='MAX_FILE_SIZE' value='<? echo $max_file_size; ?>'>
<?
	for ($i=0; $i<$files_cant; $i++) {
		echo "<table border=1 width=100% cellpadding=2 cellspacing=0>";
		echo "<tr><td colspan=2><b>Archivo ";
		echo ($i + 1).": </b>";
		echo "</td></tr>";
		echo "<tr><td align=right>";
		echo "Nombre: ";
		echo "</td><td>";
		echo "<input type=file name='archivo[$i]' size=20>\n";
		echo "</td></tr>";
		echo "</table>";
	}
?>
</td>
</tr>
<tr>
	<td align=center colspan=2>
		<input type=submit name='files_add' value='Aceptar'>&nbsp;&nbsp;&nbsp;
		<input type=submit name='files_cancel' value='Cancelar'>
	</td>
</tr>
</table>
</form>
<br>
