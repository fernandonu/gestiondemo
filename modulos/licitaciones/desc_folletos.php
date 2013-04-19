<?
/*
Author: Cesar

MODIFICADA POR
$Author: mari $
$Revision: 1.24 $
$Date: 2007/01/05 19:38:18 $
*/

include("../../config.php");


function GetExt2($Filename)
{
	$RetVal = explode ( '.', $Filename);
	return $RetVal[count($RetVal)-1];
}//de function GetExt2($Filename)

function ProcForm2($FVARS,$forcename)
{
	global $max_file_size,$extensiones,$ID,$bgcolor2,$db,$size,$type;
	global $html_root;
	$path=UPLOADS_DIR."/folletos";	// linux
	$files_arr = array();
	$size=$FVARS["archivo"]["size"][0];
	$type=$FVARS["archivo"]["type"][0];
	$name=$FVARS["archivo"]["name"][0];
	$temp=$FVARS["archivo"]["tmp_name"][0];
	$ret = FileUpload2($temp,$size,$name,$type,$max_file_size,$path,"",$extensiones,$forcename,1);
}//de function ProcForm2($FVARS,$forcename)

function FileUpload2($TempFile, $FileSize, $FileName, $FileType, $MaxSize, $Path, $ErrorFunction, $ExtsOk, $ForceFilename, $OverwriteOk)
{
	global $ID,$db,$FileSizeComp,$_ses_user;
	$ErrNo = 0;
	if (strlen($ForceFilename)) { $FileName = $ForceFilename; }
	$err=`mkdir -p '$Path'`;
	if (!function_exists($ErrorFunction)) {
		if (!function_exists('DoFileUploadDefErrorHandle')) {
			function DoFileUploadDefErrorHandle($ErrorNumber, $ErrorText) {
			}
		}
		$ErrorFunction = 'DoFileUploadDefErrorHandle';
	}
	if($TempFile == 'none' || $TempFile == '') {
		$ErrorTxt = "No se especificó el nombre del archivo<br>";
		$ErrorTxt .= "o el archivo excede el máximo de tamaño de:<br>";
		$ErrorTxt .= ($MaxSize / 1024)." Kb.";
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

	$FileNameFull = $Path."/".$FileName;		// linux
//	$FileNameFull = $Path."\\".$FileName;		// windows

	move_uploaded_file ($TempFile, $FileNameFull);
    $ext = strtolower(GetExt2($FileNameFull));
	if ($ext != "zip") {
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
	$FileSizeComp=filesize($FileNameFull);
	chmod ($FileNameFull, 0600);
	$FileNameComp = substr($FileNameFull,strrpos($FileNameFull,"/") + 1);
	$FileDateUp = date("Y-m-d H:i:s", mktime());
	return $ErrNo;
}//de function FileUpload2($TempFile, $FileSize, $FileName, $FileType, $MaxSize, $Path, $ErrorFunction, $ExtsOk, $ForceFilename, $OverwriteOk)


$id_producto=$parametros["id_producto"] or $id_producto=$_POST["id_producto"] or $id_producto="";
$desc_prod=$parametros["desc_prod"] or $desc_prod=$_POST["desc_prod"] or $desc_prod="";
$pagina=$parametros["pagina"] or $pagina=$_POST["pagina"];

if ($_POST["guardar"]=="Guardar Folleto")
{
	$ext=GetExt2($_FILES["archivo"]["name"][0]);
	$id_pr=$_POST["id_producto"];
	$desc_pr=$_POST["desc_prod"];

	$query="select nextval('licitaciones.folletos_id_folleto_seq') as id_folleto";
	$id_fol=sql($query,"<br>Error al traer el id del folleto<br>") or fin_pagina();
	$id_folleto=$id_fol->fields["id_folleto"];

	$nombre=nombre_archivo("folleto".$id_folleto."p".$id_pr." - ".$desc_pr.".".$ext);
	ProcForm2($_FILES,$nombre);

	$query="INSERT INTO licitaciones.folletos (id_folleto,id_producto,nombre_ar,tamaño,tipo,tamaño_comp)
			values ($id_folleto,$id_pr,'$nombre',$size,'$type',$FileSizeComp)";
	sql($query,"<br>Error al insertar el folleto en la BD") or fin_pagina();

	if($pagina!="")
	{
		$link_recargar=encode_link("$pagina",array("id_producto"=>$id_producto));
	?>
		<script>
			window.opener.document.location.href='<?=$link_recargar?>';
		</script>
	<?
	}//de if($pagina!="")

	echo "<center><b>El folleto se cargó con éxito</b></center>";

}//de if ($_POST["guardar"]=="Guardar Folleto")

echo $html_header;
?>

<SCRIPT language='JavaScript' src="funcion.js"></SCRIPT>
<script>
var wrecibir_prod=new Object();
wrecibir_prod.closed=1;

function elegir_producto()
{
	   var producto=eval("document.all.desc_prod");
	   var id_producto=eval("document.all.id_producto");
	   producto.value=wrecibir_prod.document.all.nombre_producto_elegido.value;
	   id_producto.value=wrecibir_prod.document.all.id_producto_seleccionado.value;

	   document.all.guardar.disabled=0;
	   wrecibir_prod.close();

}//de function elegir_producto_recibido(id_fila)
</script>

<form name="form1" method="post" action="desc_folletos.php" enctype='multipart/form-data'>
  <input type="hidden" name="pagina" value="<?=$pagina?>">
	<br>
    <table width="80%" class="bordes" align="center">
      <tr id="ma">
	    <td>
	    	Seleccione el producto para el cual se quiere agregar el folleto &nbsp;
	    	<?$link2=encode_link('../productos/listado_productos.php',array('pagina_viene'=>'desc_folletos.php','onclick_cargar'=>"window.opener.elegir_producto()",'cambiar'=>0));?>
	 		<input type="button" name="seleccionar_producto" value="Seleccionar Producto" onclick=" if(wrecibir_prod.closed)
	       																								wrecibir_prod=window.open('<?=$link2?>');
	          																						else
	          																							wrecibir_prod.focus();
	        																					  "
	        >
		 </td>
	  </tr>
	</table>
	<hr>
<input type="hidden" name="id_producto" value="<?=$id_producto?>">
<table border="0" align="center" id="mo" width="80%">
	<tr>
		<td align="left">Producto Seleccionado</td>
		<td align="center">
			<input type="text" name="desc_prod" value="<?=$desc_prod?>" size="100">
		</td>
	</tr>
</table>
<?
if ($aviso != "") echo "<table align='center' class='bordes' cellpadding=2><tr  bgcolor=$bgcolor3><td>$aviso</td></tr></table>";
?>
	<input type=hidden name=ID value='<?=$ID?>'>
	<input type=hidden name=det_addfile value='1'>
	<br>
	<table class='bordes' cellspacing=0 cellpadding=2 align=center>
	 <tr>
	  <td colspan=3 align=center id=mo>
	  	<font size=3><b>Agregar archivo de folleto</b>
	  </td>
	 </tr>
	 <tr bgcolor="<?=$bgcolor_out?>">
	  <td align=center>
	   <input type='hidden' name='MAX_FILE_SIZE' value='<?=$max_file_size?>'>
	    <b>Archivo</b>
	  </td>
	  <td>
	   <input type=file name='archivo[0]' size=50>
 	  </td>
 	 </tr>
</table>
<div align="center">
	<input type="submit" name="guardar" value="Guardar Folleto"  <?if ($id_producto=="") echo "disabled" ?>>
	<?
	if($pagina!="")
	{
		?>
		&nbsp;<input type="button" name="cerrar" value="Cerrar" onclick="window.close()">
		<?
	}//de if($pagina!="")
	?>
</div>
</form>
</body>
</html>