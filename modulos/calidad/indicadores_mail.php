<?php
/*
$Author: mari $
$Revision: 1.7 $
$Date: 2007/01/03 13:06:45 $
*/

include("../../config.php");

echo $html_header;

$id_indi=$parametros['id_indi'] or $_POST['id_indi'];
$sql="select descripcion 
      from calidad.desc_indicador
      where id_desc_indicador=$id_indi";

$result=$db->execute($sql) or die($db->errormsg());

if($_POST['Guardar_cambios']=="Guardar Cambios"){
	$desc_medicion=$_POST['desc_medicion'];
	$sql="update calidad.desc_indicador set desc_medicion='$desc_medicion' where id_desc_indicador=$id_indi";
	sql($sql,'No se puede actualizar la descripcion de la medicion del indicador');


$sql="DELETE FROM calidad.mail_indicador where id_desc_indicador=$id_indi";
$result_verifica = sql($sql) or fin_pagina();

 $as=$_POST["datos"];
 $array=explode(",",$as);
 $tam=count($array);
 $db->StartTrans();

 for($i=0;$i<$tam;$i++)
   {
    $campo=$array[$i];
    if ($campo != ''){	
    	$sql="INSERT INTO calidad.mail_indicador (id_desc_indicador,mail) values($id_indi,'$campo')";
	    $db->Execute($sql) or die($db->ErrorMsg().$sql);
    }//del if 
   }//del for
  $db->CompleteTrans();

  echo "<div align='center'> <strong> <font size='3' color='RED'>";
  echo "Los Registros se Guardaron Exitosamente";
  echo "</strong></font></div>";
}

?>



<SCRIPT LANGUAGE="JavaScript">

function moveOver() {

   var boxLength;// = document.form1.compatibles.length;
   var prodLength = document.form1.renglones.length;
   var selectedText;  // = document.choiceForm.available.options[selectedItem].text;
   var selectedValue; // = document.form1.productos.options[selectedItem].value;
   var i;
   var isNew = true;

   //aderezos

   arrText = new Array();
   arrValue = new Array();
   var count = 0;
   for (i = 0; i < prodLength; i++) {
     if (document.form1.renglones.options[i].selected) {
       arrValue[count] = document.form1.renglones.options[i].value;
       arrText[count] = document.form1.renglones.options[i].text;
       count++;
      }
     //count++;
   }

   //fin de aderezos
   for(j = 0; j < count; j++){
       boxLength = document.form1.select_alternativas.length;
       selectedText=arrText[j];
       selectedValue=arrValue[j];
   if (boxLength != 0) {
     for (i = 0; i < boxLength; i++) {
       thisitem = document.form1.select_alternativas.options[i].text;
       if (thisitem == selectedText) {
         isNew = false;
      }
     }
   }
   if (isNew) {
        newoption = new Option(selectedText, selectedValue, false, false);
     document.form1.select_alternativas.options[boxLength] = newoption;
     //document.form1.compatibles.options[boxLength].selected=true;
   }
   document.form1.select_alternativas.selectedIndex=-1;
   }
}

function removeMe() {
   var boxLength = document.form1.select_alternativas.length;
   arrSelected = new Array();
   var count = 0;
   for (i = 0; i < boxLength; i++) {
     if (document.form1.select_alternativas.options[i].selected) {
       arrSelected[count] = document.form1.select_alternativas.options[i].value;
     }
     count++;
   }
   var x;
   for (i = 0; i < boxLength; i++) {
     for (x = 0; x < arrSelected.length; x++) {
       if (document.form1.select_alternativas.options[i].value == arrSelected[x]) {
         document.form1.select_alternativas.options[i] = null;
       }
     }
    boxLength = document.form1.select_alternativas.length;
   }
}


function val_text()
{     //adaptar a los select de esta página.
 var a=new Array();
 var largo=document.form1.select_alternativas.length;
 var i=0;

    for(i;i<largo;i++)
 	   {a[i]=document.form1.select_alternativas.options[i].text;
    	}
 document.form1.datos.value=a;
}

</script>

<form name="form1" method="post" action="<?=$link1?>">
<br>
<table  border="0" cellspacing="0" cellpadding="0" width="100%">  
<tr> 
    	<td align="center" colspan="3"> 
    	<input type="hidden" value="<?=$id_indi?>" name="id_indi" title="id_indi">
   		<strong> <font size="3" color="Blue">
    		Indicador: &nbsp;
    		<?echo $result->fields['descripcion'];?>	
    		</strong> </font>
    		<br>
    		<br>    		
    	</td> 
</tr>
<tr> 
    	<td align="center" width="45%"> 
    		<strong> <font size="2" color="Black">
    		Mail de Todos 
    		</strong> </font>
    	</td> 
    	<td align="center" width="10%"> 
    	&nbsp;
    	</td> 
    	<td align="center" width="45%"> 
    		<strong> <font size="2" color="Black">
    		Mail a Enviar
    		</strong> </font>
    	</td> 	
</tr>
<tr>
     <td width="45%">
      <?
       $sql="select mail from sistema.usuarios order by mail";
	   $result_mail=$db->Execute($sql) or die($db->ErrorMsg()."$sql");
       ?>    

       <select name="renglones" multiple size="15" style="width:85%">
        <?
       $result_mail->MoveFirst();
       for($i=0;!$result_mail->EOF;$i++)
       {
        $string=$result_mail->fields['mail'];
        if ($result_mail->fields['mail']!="") echo "<option value='$i'>$string</option>";
        $result_mail->MoveNext();
       }
       ?>
      </select>
     </td>   

     <td width="10%" align="center" valign="middle">
      <input type="button" name="pasar_derecha" value=">>" size="10" style="cursor:hand" onclick="moveOver();">
      <br>
      <br>
      <input type="button" name="pasar_izquierda" value="<<" size="10" style="cursor:hand" onclick="removeMe();">
     </td>
     
     <td width="45%" align="center">
      <?
          $sql="select * from calidad.mail_indicador where id_desc_indicador=$id_indi order by mail";
          $result=$db->execute($sql) or die($sql);
      ?>
      <select name="select_alternativas" multiple size="15" style="width:85%">
      <?
        $result->MoveFirst();  
      	for($i=0;!$result->EOF;$i++)
          {
			$string=$result->fields['mail'];
        	echo "<option value='$i'>$string</option>";
            $result->MoveNext();
          }
        ?>
        </select>
     </td>
</tr>

<?

$sql="select desc_medicion from calidad.desc_indicador where id_desc_indicador=$id_indi";
$result=sql($sql,'NO se puede ejecutar');
$desc_medicion=$result->fields['desc_medicion']
?>

<tr align="center">
     <td colspan="4">
      <br>
       <strong>Descripción del Método de Medición.</strong><br>
  		<textarea name="desc_medicion" cols="80" rows="4"><?=$desc_medicion?></textarea>
  	 </td>
</tr>
	
</table>

<br>
<br>  

<div align="center">
    <input type='hidden' name='datos' value=''>
	<input type="submit" name="Guardar_cambios" value="Guardar Cambios" style="cursor:hand" title="Presione aqui para guardar los cambios realizados" onclick="val_text()">
    <input type="button" name="Cerrar" value="Cerrar" style="width:19%" style="cursor:hand" title="Presione aqui para cerrar la Ventana" onclick="window.close()">
</div>

</form>

