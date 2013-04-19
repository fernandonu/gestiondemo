<?
require_once("../../config.php");
echo $html_header;

$id_nota_credito=$parametros['id_nota_credito'] or $id_nota_credito=$_POST['id_nota_credito'];
$cant_arch = $_POST["files_cant"] or $cant_arch = 1;
$max_file_size = 5242880;

if($_POST['Guardar_archivo']) //subo archivos
{$db->StartTrans();
 $i=0;
 //print_r($_FILES);
 //print_r($_POST);
 while ($i<$cant_arch) //subo archivos
 {
  //subo archivos comprimidos
  
  $FileNametmp = $_FILES["archivo"]["tmp_name"][$i];
  $upload_dir = UPLOADS_DIR."/notas_credito";
  $FileType=$_FILES["archivo"]["type"][$i];
  $FileSize = $_FILES["archivo"]["size"][$i];
  $name=$_FILES["archivo"]["name"][$i];
  //echo "Temp:$FileNametmp<br>";
  //echo "Dir:$upload_dir<br>";
  //echo "Tipo:$FileType<br>";
  //echo "Tamano:$FileSize<br>";
  //echo "Nombre:$name<br>";
  $ret=FileUpload($FileNametmp,$FileSize,$name,$FileType,$max_file_size,$upload_dir,$func,$extensiones,"","",1,0);
  
  //actualizo base de datos
  if ($ret["error"]==0)
  {
   $sql = "insert into arch_notas_credito(id_nota_credito,id_usuario,nbre_arch,tam_arch,tipo,nombre_comp,tamano_comp,fecha_carga) values($id_nota_credito,".$_ses_user["id"].",'$name',$FileSize,'$FileType','".$ret['filenamecomp']."',".$ret['filesizecomp'].",'".date("Y-m-d H:i:s")."')";
   $db->Execute($sql) or die($db->ErrorMsg()."<br>$sql");
  }
  $i++;  
 }
$db->CompleteTrans(); 
 if ($ret["error"]==0)
 {
  ?>
  <script languaje='javascript'>
  alert('El Archivo se subió con éxito');
  window.opener.location.reload();
  window.close();
  </script>
 <?
 }//de if ($ret["error"]==0)
}

?>
<form name="form1" action="subir_arch_creditos.php" method="POST" enctype="multipart/form-data">
<input type="hidden" name="id_nota_credito" value="<?=$id_nota_credito;?>">
<table width='80%' border="1" cellspacing=0 cellpadding=3 >
    <tr> 
	 <td colspan="2" align="center" id=mo>Subir archivo para Nota de Credito Nro <?=$id_nota_credito;?>
    </tr>
    <tr> <td colspan="2" align="center">Tamaño m&aacute;ximo de archivo es: <? echo ($max_file_size/1024/1024); ?> MB </td>
    </tr>
    <tr>
	<td colspan='2' align=right> <strong>Cantidad de archivos:</strong>&nbsp;&nbsp;
	<select name="files_cant" onChange="document.form1.submit()">
     <? for ($i=1; $i<=20; $i++) {
		echo "<option value='$i'";
		if ($i == $cant_arch) echo " selected";
		echo ">$i\n";
	}?>
	</select>
	</td>
	</tr>
  <tr> <td colspan="2">
 <? for ($i=0;$i<$cant_arch;$i++){ 
 echo "<div align='center'>";
	echo "<table witdh='80%' border='1' cellspacing=0 cellpadding=3 bgcolor=$bgcolor_out>\n"; 
	echo "<tr>\n";
    echo "<td colspan='2' style='border:$bgcolor1;'> <strong>Archivo Nº &nbsp; "; echo ($i+1);echo "</strong> </td>\n";
	echo "<tr>\n";
	echo "<tr>\n";
	echo "<td> <strong>Nombre del archivo y localización:</strong></td>\n";
	echo "<td align='center'>  <input size='30' type=file name=archivo[$i] ></td>\n";
	echo "</tr>\n";
	echo "</table>\n</div>";
	} ?>
	</td>
 </tr>	
 <tr><td colspan="2">
	 <center>
	 <table>
	  <tr><td colspan="2" align='center'> <input name="Guardar_archivo" type="submit" value="Guardar" >&nbsp;&nbsp;
	  <input name="boton" type="button" value="Cerrar" onclick="window.close();"></td>
	
	</tr> </td>
    </table></center>
  </tr>
</table>
</form>
<?=$html_footer;?>