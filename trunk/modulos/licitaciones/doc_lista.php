<?PHP
/*
$Creador:MAD $
	27 de Agosto 2004

$Author: broggi $
$Revision: 1.5 $
$Date: 2004/10/30 14:56:44 $
*/

require_once("../../config.php");

$hoy = date("Y-m-d",mktime());

$permiso_borrar = "";
$permiso_subir	= "";
$max_file_size = 5242880;

if(!permisos_check("inicio","borrar_documentacion") ) $permiso_borrar = "Disabled";

//parte de bajar archivo
if ($parametros["download"]) {
	$sql = "select * from documentacion where id_file = ".$parametros["FileID"];
	$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
	

	if ($parametros["comp"]) {
		$FileName = $result->fields["name_comp"];
		$FileNameFull = UPLOADS_DIR."/Licitaciones/documentacion/$FileName";
		$FileType="application/zip";
		$FileSize = $result->fields["size_comp"];
		FileDownload(1,$FileName,$FileNameFull,$FileType,$FileSize);
	} else {
		$FileName = $result->fields["name"];
		$FileNameFull = UPLOADS_DIR."/Licitaciones/documentacion/$FileName";
		$FileType = $result->fields["tipo"];
		$FileSize = $result->fields["size"];
		FileDownload(0,$FileName,$FileNameFull,$FileType,$FileSize);
	}	
}

//Parte de subir Archivo
if ($_POST["subir"]=="Subir") {
	if ($_POST["Comentario"] == '') {
		$msg="<font color='red'>Para subir un Documento debe especificar un Comentario</font>";
	}else {
		$db->StartTrans();
		$size=$_FILES["file"]["size"];
		$type=$_FILES["file"]["type"];
		$name=$_FILES["file"]["name"];
		$temp=$_FILES["file"]["tmp_name"];
		$path = UPLOADS_DIR."/Licitaciones/documentacion";
		$extensiones = array("doc","pdf","zip");
		function algo($uno,$dos){};
		$func = "algo";
	//	echo enable_path($path);
		$ret = FileUpload($temp,$size,$name,$type,$max_file_size,$path,$func,$extensiones,"","",1,0);
		switch ($ret["error"]) {
			case 0: {
				$FileDateUp = date("Y-m-d H:i:s", mktime());
				$sql = "select id_usuario from usuarios where login = '".$_ses_user["login"]."'";  
				$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
				$user = '';
				$user = $result->fields["id_usuario"];
				if ($user != '') {
					$sql = "insert into documentacion (comentario,name,id_usuario,fecha,name_comp,tipo,size_comp,size) values ";
					$sql .="('".$_POST["Comentario"]."','$name',$user,'$FileDateUp','".$ret["filenamecomp"]."','$type',".$ret["filesizecomp"].",$size)";
					if (sql($sql) or fin_pagina())
						$msg="<font color='green'>Ha Subido un nuevo Documento al Sistema.</font>";
					else
						$msg="<font color='red'>Error: ingresando a la base de datos el archivo subido.</font>";
				} else
					$msg="<font color='red'>Error: buscando el usuario responsable.</font>";
			} break;
			case 1: {
				$msg="<font color='red'>Error: No especificó el archivo o el mismo supera el tamaño máximo soportado.</font>";
			} break;
			case 2: {
				$msg="<font color='red'>Error: Falla inesperada subiendo el archivo.</font>";
			} break;
			case 3: {
				$msg="<font color='red'>Error: El sistema no acepta archivos vacios.</font>";
			} break;
			case 5: {
				$msg="<font color='red'>Error: El sistema no acepta archivos que superen los ".($max_file_size/1024)."Kb de información.</font>";
			} break;
			case 6: {
				$msg="<font color='red'>Error: El archivo ya existe en el Sistema y no esta permitido sobreescribir el mismo.</font>";
			} break;
			case 8: {
				$msg="<font color='red'>Error: Fallo el proceso de compresión, pero el archivo fue guardado sin comprimir.</font>";
			} break;
		}
		$db->CompleteTrans();	
	}	
}

//parte de eliminar archivos
if ($_POST["borrar"]=="Borrar")
{
	$i=0;
	$err=1; $elim = 0;
	while($i<$_POST["Cantidad"] && $err) {
		if ($_POST["select_$i"]!=""){
			$db->StartTrans();
			$sql = "Select * from documentacion where id_file = ".$_POST["select_$i"];
			$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
				
			$sql = "delete from documentacion where id_file = ".$_POST["select_$i"];			
			if (!$db->Execute($sql)){ 
				$msg="<font color='red'>Error el archivo ".$result->fields["name"]." no pudo eliminarse de la base de datos.</font>";
				$err=0;
			} else {
				if ($result->fields["name_comp"] != '')
					$FileName = ROOT_DIR."/uploads/Licitaciones/documentacion/".$result->fields["name_comp"];
				else
					$FileName = ROOT_DIR."/uploads/Licitaciones/documentacion/".$result->fields["name"];
				if (!unlink(enable_path($FileName))) {
					$msg="<font color='red'>Error el archivo ".$result->fields["name"]." no pudo eliminarse fisicamente.</font>";
					$err=0;
					$db->RollBackTrans();
				} else $elim++;
			}
			$db->CompleteTrans();
		}
		$i++;
	}
	if ($err) {
		$msg="<font color='green'>Se eliminaron $elim Archivos.</font>";			
	}
}


echo $html_header;

?>
<br><CENTER>
<form name="form1" method="POST" action="doc_lista.php" enctype="multipart/form-data">
<?
$orden = array(
		"default" => "1",
		"1" => "documentacion.id_file",
		"2" => "documentacion.name",
		"3" => "documentacion.fecha",
		"4" => "usuarios.nombre"
	);

$filtro = array(
		"documentacion.id_file" => "Número del Documento",
		"documentacion.name" => "Nombre del Documento",
		"documentacion.fecha" => "Fecha de subido al sistema",
		"usuarios.nombre" => "Responsable"
	);

$query="select documentacion.*,usuarios.nombre,usuarios.apellido from documentacion join usuarios using (id_usuario)";
$where = "";

list($sql,$total_files,$link_pagina,$up) = form_busqueda($query,$orden,$filtro,$link_tmp,$where,"buscar"); 

$result = $db->Execute($sql) or die($db->ErrorMsg()."<br>Error en form busqueda");

?>
&nbsp;&nbsp;<input type=submit name=form_busqueda value='Buscar'>
</center>
<BR>
<?echo "<center><b>".$msg."</b></center>";?>

<table class="bordes" width="95%" align="center">
 <tr id=mo bgcolor=<?=$bgcolor3?>>
    <td align="left" colspan="2">
     <b>Documentos:</b> <?=$total_files?>.
    </td>
	<td align="right" colspan="3">
	 <?=$link_pagina?>
	</td>
  </tr>
<tr id=mo>
 <td width='10%'><b><INPUT type="submit" name="borrar" value="Borrar" title="Eliminar Seleccioneados" disabled onclick="return eliminar();"></b></td>
 <td width='10%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"1","up"=>$up))?>'>Nº.</a></b></td>
 <td width='40%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"2","up"=>$up))?>'>Nombre.</a></b></td>
 <td width='20%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"3","up"=>$up))?>'>Fecha y Hora.</A></b></td>
 <td width='20%'><b><a id=mo href='<?=encode_link($_SERVER["PHP_SELF"],array("sort"=>"4","up"=>$up))?>'>Responsable.</A></b></td>
</tr>

<?  $i=0;
	while(!$result->EOF){ 
	$link = encode_link($_SERVER["PHP_SELF"],array("FileID"=>$result->fields["id_file"],"download"=>1,"comp"=>0));
	?>

<TR id="ma" title="<?=$result->fields["comentario"]?>">
	<TD>
    <input type="checkbox" name="select_<? echo $i; ?>" value="<? echo $result->fields['id_file']; ?>" <?=$permiso_borrar?> onclick="habilitar_borrar(this);" title="Seleccione para eliminar">
	</TD>
	<TD>
	<?=$result->fields["id_file"]?>
	</TD>
	<TD>
	<a title='<?=$result->fields["name_comp"]?> [<?=number_format($result->fields["size_comp"]/1024)?> Kb]' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$result->fields["id_file"],"download"=>1,"comp"=>1))?>'>
	<img align=middle src=<?=$html_root?>/imagenes/zip.gif border=0></A>
	<a title = 'Abrir archivo' href='<?=encode_link($_SERVER["PHP_SELF"],array("FileID"=>$result->fields["id_file"],"download"=>1,"comp"=>0))?>'>
	<? echo $result->fields["name"]." (".number_format(($result->fields["size"]/1024),"2",".","")."Kb)"?>
	</A>
	</TD>
	<TD>
	<?=$result->fields["fecha"]?>
	</TD>
	<TD>
	<? echo $result->fields["nombre"]." ".$result->fields["apellido"];?>
	</TD>
</TR>
<? $result->MoveNext(); $i++;}?>
	<INPUT type="hidden" name="Cantidad" value="<?=$i?>">
</TABLE>

<TABLE class="bordes" width="95%" align="center">
	<TR id="mo">
		<td align="center" colspan="2">
		Subir Nuevo Documento
		</TD>
	</TR>
	<TR id="ma">
		<TD width="60%">
		<FONT color="Black">Comentario</FONT>
		</TD>
		<TD width="40%">
		<FONT color="Black">Archivo</FONT>
		</TD>
	</TR>
	<TR id="ma">
		<TD rowspan="2">
		<TEXTAREA name="Comentario" rows="6" cols="60"></TEXTAREA>
		</TD>
		<TD>
		<INPUT type="file" name="file">
		<INPUT type="submit" name="subir" value="Subir" <?=$permiso_subir?>>
		</TD>
	</TR>
	<TR id="ma">
		<TD>
		<B>Tamaño Maximo a subir <?=$max_file_size/1024?> Kb.</B>
		</TD>
	</TR>
</TABLE>
</FORM>
<SCRIPT>
var contador=0;
function habilitar_borrar(valor)
{
 if (valor.checked)
             contador++;
             else
             contador--;
 if (contador>=1){
         window.document.all.borrar.disabled=0;
 		}
        else{
         window.document.all.borrar.disabled=1;
        }
}//fin function
function eliminar() {
	return window.confirm("Esta seguro que quiere eliminar "+contador+" archivos almacenados en el sistema.");
}
</SCRIPT>