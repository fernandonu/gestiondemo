<?
/*
Autor: quique
Creado: miercoles 04/08/04

MODIFICADA POR
$Author: mari $
$Revision: 1.4 $
$Date: 2006/12/29 12:23:07 $
*/
$filecount=$_POST['select_filecount'] or $filecount=1;
$max_file_count=$parametros['max_file_count'] or $max_file_count=10;

require_once("../../config.php");

//$nombre_producto=$parametros["nombre_producto"] or $nombre_producto=$_POST["nombre_producto"];
$id_muleto=$parametros["id_muleto"] or $id_muleto=$_POST["id_muleto"];
if ($_POST['baceptar'])
{
    $acceso="Todos";
    $comentario="Archivo de Muleto";
    $fecha=date("Y-m-d H:i:s");
    $files_total=$_POST['select_filecount'];
    $id_p_e=$_POST['id_muleto'];
    $sacar="archivo[1]";
    $filename=$_FILES["archivo"]["name"][0];
    $tamanio=$_FILES['archivo']["size"][0];
    $error_vector=array();
    for ($i=0; $i < $files_total ; $i++ )
    {
    	$comenta="coment_".$i;
    	$comentario=$_POST["$comenta"];
    	$filename=$_FILES["archivo"]["name"][$i];
    	$tamanio=$_FILES['archivo']["size"][$i];
	    /*if (!$filename)
           $error_msg="Debe seleccionar un archivo";
	    elseif ($_FILES["archivo"]["error"][$i])
           $error_msg="El archivo '$filename' es muy grande ";
        if (!$comentario)
           $error_msg="Debe Ingresar un Comentario"; */  
    	
	    if (!$error_msg) 
	    {
	    	if (subir_archivo($_FILES["archivo"]["tmp_name"][$i],"./Fotos/$id_p_e/$filename",$error_msg)===true)
	    	{
	         $sql="select nextval('foto_muleto_id_foto_muleto_seq') as idfile ";
	         $res=sql($sql) or $db->errormsg()."<br>";
	         $idfile=$res->fields['idfile'];
	         $q="INSERT INTO casos.foto_muleto
	              (id_foto_muleto,id_muleto,nombre_archivo,comentario_foto,tamano,subido_usuario,subido_fecha,tipo) Values
	              ($idfile,$id_p_e,'$filename','$comentario','$tamanio','".$_ses_user['login']."','$fecha','$acceso');";
	         //$q.="insert into archivos_ordprod (id_archivo,nro_orden) values ($idfile,$nro_orden);";

	         if (!sql($q))
	           $error_msg="No se pudo insertar el archivo ".$db->errormsg()."<br>$q ";	
	         else 
	           $ok_msg="El archivo '$filename' se subio con éxito";
	         }
	         
	    	
	    }
	     $error_vector[]=$error_msg;
	     $error_msg="";
	     $ok_vector[]=$ok_msg;
	     $ok_msg="";
    }
}


//-------------------------------------------------------------------

//Si se producen errores se deben dejar en una variable $error_vector
//El informe de procesamiento se debe dejar en una variable $ok_vector
//Las variables se imprimen automaticamente

//-------------------------------------------------------------------
echo $html_header; ?>
<script src="../../lib/funciones.js"></script>
<script>
function control()
{
	var t=0;
	while(document.all.total.value>t)
	{
	var co=eval("document.all.archivo.value");	
	var com=eval("document.all.coment_"+t+".value");	
	
	if(co=="")
	{
        alert ("falta ingresar los archivos");
		return false;
	}
	if(com=="")
	{
        alert ("falta ingresar los comentarios");
		return false;
	}
	t++;	
	}
	return true;
	
}
</script>

<form name='form_archivos' action='guardar_foto_muleto.php' method=POST enctype='multipart/form-data'>
<input type="hidden" name="MAX_FILE_SIZE" value="0"><!-- NO MAX -->
<!-- NO MAX -->
<center><b>
<? 
  if (is_array($error_vector))
  {
	foreach ($error_vector  as $valor )
  	  echo "<font color=red>$valor</font><br>";
  }
  if (is_array($ok_vector))
  {
	foreach ($ok_vector  as $valor )
  	  echo "<font color=green>$valor</font><br>";
  }
?>
</b><br></center>
<table border=1 cellspacing=0 cellpadding=5 bgcolor=#D5D5D5 align=center>
<tr>
  <input type="hidden" name="id_muleto" value="<?=$id_muleto?>">
  <td style="border:#E0E0E0;" colspan=2 align=center id=mo><font size=3><b>Foto del Muleto</b></td>
</tr><tr>
<td align=right colspan=2><b>Cantidad de archivos:</b>
<select name='select_filecount' onchange='document.forms[0].submit()'>
<? for ($i=1; $i<=$max_file_count ; $i++)
   {
?>
	<option <?= ($filecount==$i)?"selected":""; ?> ><?=$i ?></option>
<? } ?>	
</select>
</td>
</tr>
<? 
 $i=1;
 $j=0;
 while ($filecount--)
 {
?>
<tr>

  <td align=right>Foto <?= $i ?>: </td>
  <td><b>Archivo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b><input type=file id="archivo" name='archivo[<?=$j?>]' size=30><br>
  <b>Comentarios</b><input type="text" name="coment_<?=$j?>" value="" size=30>
  </td>
</tr>
<?
$i++;
$j++;
 }
?>
<tr>
<td align=center colspan=2>
<input type="hidden" name="total" value="<?=$j?>">
<input type=submit name='baceptar' value='Aceptar' onclick="return control(); document.all.div_aceptar.style.display = 'block';<?=$onclick["aceptar"]?>">&nbsp;&nbsp;&nbsp;
<input type="button" name="Cerrar" value="Cerrar" onclick="window.close();">
</table>
<br>
<br>
<div align="center" id=div_aceptar style="display:none" >
Subiendo Archivos.....<br>
Espere por favor...<br><br>
<!--<img src="../../imagenes/progreso.gif" border=0>-->
</div>
</form><br>
<?=fin_pagina(); ?>