<?
require_once("../../config.php");

echo $html_header;
$cantidad;

function subir_archivos($FVARS,$cant,$id_fact){
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
           Error ("El archivo  $name es muy grande");
   if (!$error){
       mkdirs(UPLOADS_DIR."/facturacion");
      if (is_file(UPLOADS_DIR."/facturacion/".$id_fact.'-'.$name))
       {  Error("El Archivo Nº $name ya existe.");
       } 
      if (!$error )
          if (!copy($temp,UPLOADS_DIR."/facturacion/".$id_fact.'-'.$name))
            {Error("No se pudo Subir el archivo $name");
            }
          if (!$error){ 
             $fecEmision=fecha_db(date("d/m/Y",mktime()));
             $query_insert="insert into arch_prov (nbre_arch,tam_arch,id_factura,subidopor,fecha_carga) VALUES ('$name',$size,$id_fact,'".$_ses_user['name']."','$fecEmision')";
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
  $id_fact=$_POST['id_factura'];
  $cant_arch=$_POST['files_cant'];
  subir_archivos($_FILES,$cant_arch,$id_fact);
}

$fila=$parametros['fila'];
$link2=encode_link("fact_prov_arch.php",array("fila"=>$fila));
?>

<form action="<?=$link2?>" method="post" enctype="multipart/form-data" name="form1">
<input name='cant_arch' type='hidden' value='<? if ($_POST['files_cant']) echo $_POST['files_cant']; else echo '1' ?>'>
<input name='id_factura' type='hidden' value='<? if ($parametros['fact']) echo $parametros['fact']; else echo $_POST['id_factura'];?>'>
<input name='nro_factura' type='hidden' value='<? if ($parametros['nro']) echo $parametros['nro']; else echo $_POST['nro_factura'];?>'>
<input name='fecha_factura' type='hidden' value='<? if ($_POST['fecha_factura']) echo $_POST['fecha_factura']; else echo $parametros['fecha_factura'];?>'>
<input name='nro_orden' type='hidden' value='<? if ($parametros['nro_orden']) echo $parametros['nro_orden']; else echo $_POST['nro_orden'];?>'> 
<?


if ($parametros['fact']) $id_fact=$parametros['fact']; else $id_fact=$_POST['id_factura']; 
$cant_arch = $_POST["files_cant"] or $cant_arch = 1;
if ($cantidad > 0) $cant_arch=$cantidad;
?>

<div align="center"><br> <br>
  <table width='80%' border="1" cellspacing=0 cellpadding=3 bgcolor=<? echo $bgcolor2; ?>>
    <tr> 
	 <td colspan="2" align="center" id=mo>Subir archivo para la factura Nº <? if ($parametros['nro']) echo $parametros['nro']; else echo $_POST['nro_factura'] ?> <br></td> 
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
	echo "<table witdh='80%' border='1' cellspacing=0 cellpadding=3 bgcolor=$bgcolor2 >\n"; 
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
 if ($parametros["fact"]) $fact=$parametros["fact"]; else $fact=$_POST['id_factura'];
if ($parametros['nro_orden'])
  $link1=encode_link("fact_prov_subir.php",array("fact"=>$fact,"nro_orden"=>$parametros['nro_orden'],"fecha_factura"=>$parametros['fecha_factura'],"fila"=>$fila));
  elseif ($_POST['nro_orden']) $link1=encode_link("fact_prov_subir.php",array("fact"=>$fact,"nro_orden"=>$_POST['nro_orden'],"fecha_factura"=>$_POST['fecha_factura'],"fila"=>$fila));
     else $link1=encode_link("fact_prov_subir.php",array("fact"=>$fact));?>
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
	