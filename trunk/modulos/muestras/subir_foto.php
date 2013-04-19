<?
/*
AUTOR: Fernando 
FECHA: 29/05/2006

$Author: fernando $
$Revision: 1.3 $
$Date: 2006/06/20 21:23:01 $
*/

require_once("../../config.php");


$id_muestra=$parametros["id_muestra"] or $id_muestra=$_POST["id_muestra"];
$max_file_count=10;
$filecount=$_POST['select_filecount'] or $filecount=1;
if ($_POST['aceptar']){
                  $acceso="Todos";
                  $comentario="Archivo de Muestras";
                  $fecha=date("Y-m-d H:i:s");
                  $usuario=$_ses_user["name"];
                  $files_total=$_POST['select_filecount'];
                  
                   $db->starttrans();
                   
                  
                  for ($i=0; $i < $files_total ; $i++ ) {
                       
    	               $filename=$_FILES["archivo"]["name"][$i];
    	               $tamanio =$_FILES['archivo']["size"][$i];
    	                if (subir_archivo($_FILES["archivo"]["tmp_name"][$i],"./Fotos/$id_muestra/$filename",$error_msg)===true)
    	                    {
                            $sql="INSERT INTO  foto_muestra
                                    (id_muestra,nombre_archivo,tamano,usuario,fecha,tipo) 
                                    Values
                                  ($id_muestra,'$filename','$tamanio','$usuario','$fecha','$acceso');";
                             sql($sql) or fin_pagina();  
                             }
	           	                       

                     }// del for
                   if ( $db->completetrans() ) $msg=" Se subieron las fotos correctamente";    
             }


//-------------------------------------------------------------------

//Si se producen errores se deben dejar en una variable $error_vector
//El informe de procesamiento se debe dejar en una variable $ok_vector
//Las variables se imprimen automaticamente

//-------------------------------------------------------------------
echo $html_header; ?>
<script>
function control()
{
	var t=0;
	while(document.all.total.value>t)
	{
	var co=eval("document.all.archivo.value");	
	
	if(co=="")
	{
        alert ("falta ingresar los archivos");
		return false;
	}
	t++;	
	}
	return true;
	
}
</script>
<?
if ($msg) Aviso($msg);
?>
<form name='form_archivos' action='subir_foto.php' method=POST enctype='multipart/form-data'>
<input type='hidden' name='MAX_FILE_SIZE' value='<?=$max_file_size?>'>
<table  width=50% align=center class=bordes>
 <tr>
  <input type="hidden" name="id_muestra" value="<?=$id_muestra?>">
   <td colspan=2 align=center id=mo>Fotos de la Muestra</td>
 </tr>
 <tr>
 <td align=right colspan=2><b>Cantidad de archivos:</b>
 <select name='select_filecount' onchange='document.forms[0].submit()'>
     <? for ($i=1; $i<=$max_file_count ; $i++)
           {
     ?>
  	      <option <?= ($filecount==$i)?"selected":""; ?> ><?=$i ?></option>
     <?    } ?>	
 </select>
</td>
</tr>
<tr>
  <td colspan=2>
   <table width=100% align=center class=bordes>
   <? 
   $i=1;
   $j=0;
   while ($filecount--)
   {
   ?>
  <tr <?=atrib_tr()?>>
      <td align=right>Foto <?= $i ?>: </td>
      <td><b>Archivo</b></td>
      <td align=center><input type=file id="archivo" name='archivo[<?=$j?>]' size=30></td>
  </tr>
<?
  $i++;
  $j++;
  }
?>
  </table>
 </td>
</tr>  
<tr>
<td align=center colspan=2>
<input type="hidden" name="total" value="<?=$j?>">
<input type=submit   name='aceptar' value='Aceptar' onclick="return control(); ">
 &nbsp;&nbsp;&nbsp;
<input type="button" name="Cerrar" value="Cerrar" onclick="window.opener.document.form1.submit();window.close();">
</table>
</form>
<br>
<?=fin_pagina(); ?>