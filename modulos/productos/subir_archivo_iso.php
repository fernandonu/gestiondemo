<?
require_once("../../config.php");


$id_prov=$parametros['id_prov'];

if ($_POST['guardar']=="Guardar")
{
$id_prov=$_POST['id_prov'];

$msg=" ";
$size=$_FILES["file"]["size"];
//echo $size . "  ";
$type=$_FILES["file"]["type"];		
//echo $type . "  ";
$name=nombre_archivo($_FILES["file"]["name"]);
//echo $name . "  ";
$temp=$_FILES["file"]["tmp_name"];
//echo $temp . "  ";
$path = UPLOADS_DIR."/proveedores/archivos_iso";
//echo $path . "  ";
$extensiones = array("doc","pdf","zip");
//echo $extensiones . "  ";

$func = " ";		
$ret = FileUpload($temp,$size,$name,$type,$max_file_size,$path,$func,$extensiones,"","",1,0);		

		
		switch ($ret["error"]) {
			case 0: {$FileDateUp = date("Y-m-d H:i:s", mktime());
				      $user=$_ses_user['name'];								     
                      $sql = "insert into general.prov_archivos_subidos_iso (usuario,fecha,nombre_archivo_comp,nombre_archivo,id_proveedor,filetype,filesize,filesize_comp) values ";
					  $sql .="('$user','$FileDateUp','".$ret["filenamecomp"]."','$name',$id_prov,'$type',$size,".$ret["filesizecomp"].")";					 
					  $resultado_sql=sql($sql) or fin_pagina();
				     echo "<font size='2' color='red'><strong>El Archivo '$name' se subio Correctamente.</strong></font><br>";
				} 
			 break;
			case 1: {
				$msg.="<font color='red'>Error: Supera el tamaño máximo soportado.</font><br>";
			} break;
			case 2: {
				$msg.="<font color='red'>Error: Falla inesperada subiendo el archivo $name.</font><br>";
			} break;
			case 3: {
				$msg.="<font color='red'>Error: El sistema no acepta archivos vacios.</font><br>";
			} break;
			case 5: {
				$msg.="<font color='red'>Error: El sistema no acepta archivos que superen los ".($max_file_size/1024)." Kb de información </font><br>";
			} break;
			case 6: {
				$msg.="<font color='red'>Error: El archivo $name ya existe en el Sistema y no esta permitido sobreescribir el mismo.</font><br>";
			} break;
			case 8: {
				$msg.="<font color='red'>Error: Fallo el proceso de compresión, pero el archivo $name fue guardado sin comprimir.</font><br>";
			} break;
		}
}		
      
echo $html_header;	
?>

<form name='form1' action="subir_archivo_iso.php" method="POST" enctype="multipart/form-data">

<table width="100%" align="center" id=ma  border="1" cellspacing="1">    
<br>
<br>
    <tr >
     <td align="center" colspan="2">
      <font color="Black"><b>Nombre Archivo:&nbsp;</b></font>
     </td>
    </tr>
    
    <tr>
     <td align="center" colspan="2">
      <INPUT type="file" name="file" size="50">
     </td>
    </tr>  
    
</table>

<input name="id_prov" type="hidden" value="<?=$id_prov?>">

<table align="center">
 <tr>
  <td><input name="guardar" type="submit" value="Guardar"></td>
  <td><input name="volver" type="button" value="Volver" onclick="location.href='<?= encode_link("carga_prov.php",array()) ?>';window.opener.location.reload(); window.close();"></td>
 </tr>
</table>
</form>