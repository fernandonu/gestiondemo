<?
require_once("../../config.php");

echo $html_header;
$cantidad;

$id_entrega_estimada=$parametros['id_entrega_estimada'] or $id_entrega_estimada=$_POST['id_entrega_estimada'];
$licitacion=$parametros['licitacion'] or $licitacion=$_POST['licitacion'];
$numero=$parametros['numero'] or $numero=$_POST['numero'];
$volver=$parametros['volver'] or $volver=$_POST['volver'];
$oc=$parametros["oc"] or $oc=$_POST["oc"];
$cliente=$parametros['cliente'] or $cliente=$_POST['cliente'];
$vencimiento=$parametros['vencimiento'] or $vencimiento=$_POST['vencimiento'];


function subir_archivos($FVARS,$cant,$id_ent_est){
global $db, $max_file_size,$error,$cantidad,$_ses_user;
$cantidad=$cant;
for ($i=0;$i<$cant;$i++){
  
 $size=0;
 if ($_FILES["archivo"]["name"][$i]){
    $size=$FVARS["archivo"]["size"][$i];
    $type=$FVARS["archivo"]["type"][$i];
    $name=$FVARS["archivo"]["name"][$i];
	$temp=$FVARS["archivo"]["tmp_name"][$i];
   
   if ($size >  $max_file_size)
           Error ("El archivo $name es muy grande");
   if (!$error){
       mkdirs(UPLOADS_DIR."/archivos_seg");
      if (is_file(UPLOADS_DIR."/archivos_seg/".$id_ent_est.'-'.$name))
       {  Error("El Archivo Nº $name ya existe.");
       } 
      if (!$error )
          if (!copy($temp,UPLOADS_DIR."/archivos_seg/".$id_ent_est.'-'.$name))
            {Error("No se pudo Subir el archivo $name");
            }
          if (!$error){ 
             $fecEmision=fecha_db(date("d/m/Y",mktime()));
             $query_insert="insert into arch_seguimiento (nbre_arch,tam_arch,id_entrega_estimada,subidopor,fecha_carga) VALUES ('$name',$size,$id_ent_est,'".$_ses_user['name']."','$fecEmision')";
             sql($query_insert) or die;
            
			 $cantidad--;
             aviso ("El archivo $name  se subio correctamente");
          }
    }
} 
else {
    Error ("Debe seleccionar un archivo");	
    
}
$error=0;
} //fin for

} //fin subir archivos

if ($_POST['Guardar_archivo']=='Guardar'){
  $id_ent_est=$_POST['id_entrega_estimada'];
  $cant_arch=$_POST['files_cant'];
  subir_archivos($_FILES,$cant_arch,$id_ent_est);
}

?>

<form action="seg_subir_arch.php" method="post" enctype="multipart/form-data" name="form1">

<input name='cant_arch' type='hidden' value='<? if ($_POST['files_cant']) echo $_POST['files_cant']; else echo '1' ?>'>
<input name='id_entrega_estimada' type='hidden' value='<?=$id_entrega_estimada?>'>
<input name='numero' type='hidden' value='<?=$numero?>'>
<input name='licitacion' type='hidden' value='<?=$licitacion?>'>
<input name='volver' type='hidden' value='<?=$volver?>'> 
<input name='oc' type='hidden' value='<?=$oc?>'> 
<input name='cliente' type='hidden' value='<?=$cliente?>'> 
<input name='vencimiento' type='hidden' value='<?=$vencimiento?>'> 

<?
$cant_arch = $_POST["files_cant"] or $cant_arch = 1;
if ($cantidad > 0) $cant_arch=$cantidad;
?>

<div align="center"><br> <br>
  <table width='80%' border="1" cellspacing=0 cellpadding=3 >
    <tr> 
	 <td colspan="2" align="center" id=mo>Subir archivo para el seguimiento N° <?=$numero?> de la Licitacion N°<?=$licitacion?>
    </tr>
    <tr> <td colspan="2" align="center">Tamaño m&aacute;ximo de archivo es: <? echo sprintf("%01.2lf",get_cfg_var("upload_max_filesize")/1024/1000); ?> MB </td>
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

 <? 
  $link1=encode_link($volver,array("id_entrega_estimada"=>$id_entrega_estimada,"licitacion"=>$licitacion,"numero"=>$numero,"pagina_volver"=>'entregas.php',"oc"=>$oc,"cliente"=>$cliente,"vencimiento"=>$vencimiento));?>
 <tr><td colspan="2">
	 <div align="center">
	 <table>
	  <tr><td colspan="2" align='center'> <input name="Guardar_archivo" type="submit" value="Guardar" >&nbsp;&nbsp;
	  <input name="volver" type="button" value="Volver" Onclick="location.href='<? echo $link1?>'"></td>
	
	</tr> </td>
    </table></div>
  </tr>
</table>
</form>
</body>
</html>
	