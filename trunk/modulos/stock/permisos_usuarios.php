<?php
  /*
Autor: Quique
Fecha: 14/10/04

MODIFICADA POR
$Author: enrique $
$Revision: 1.2 $
$Date: 2006/03/28 16:01:25 $
*/
include("../../config.php");

echo $html_header;

$pagina=$parametros['pagina'] or $_POST['pagina'];
$control=$parametros['control'] or $_POST['control'];
$id_mail=$parametros['id_mail'] or $_POST['id_mail'];

if($_POST['Guardar_cambios']=="Guardar Cambios"){
$id_mail=$_POST['id_mail'];	
$sql="DELETE FROM mail_usuarios where id_mail_botones=$id_mail";
$result_verifica = sql($sql) or fin_pagina();
 $as=$_POST["datos"];
 $array=explode(",",$as);
 $tam=count($array);
 $db->StartTrans();
 for($i=0;$i<$tam;$i++)
   {
    $campo=$array[$i];
    echo"campo $campo <br>";
    if ($campo != ''){	
    	$sql="INSERT INTO mail_usuarios (id_mail_botones,id_usuario) values($id_mail,'$campo')";
	    $db->Execute($sql) or die($db->ErrorMsg().$sql);
    }///del if 
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
 	   {a[i]=document.form1.select_alternativas.options[i].value;
    	}
 document.form1.datos.value=a;
}

</script>



<form name="form1" method="post" action="<?=$link1?>">
<br>
<?
if($control==1)
{
$sel_bot="select * from mail_botones where pagina='$pagina'";
$botones=sql($sel_bot,"no se pudo recuperar los botones") or fin_pagina();
?>
<table  align="center" width="70%" class="bordessininferior">  
    
<tr> 
    	<td align="center" colspan="2"> 
    	<input type="hidden" value="<?=$id_indi?>" name="id_indi" title="id_indi">
    		<strong> <font size="3" color="Blue">
    		Seleccione el Mail a Configurar: &nbsp;	
    		</strong> </font>
    		<br>
    		<br>    		
    	</td> 
</tr>
</table>
<table width="70%" align="center" class='bordessinsuperior'>
<tr id="mo">
<td width="25%"><b>Palabra clave</b></td>
<td width="45%" title="Descripción breve de por que es enviado el mail"><b>Descripcion</b></td>
</tr>
<? 
 for($i=0;!$botones->EOF;$i++)
 {
  $string=$botones->fields['nombre'];
  $string1=$botones->fields['comentario'];
  $id_mail=$botones->fields['id_mail_botones'];	
  $ref = encode_link("permisos_usuarios.php",array("pagina"=>"","control"=>0,"id_mail"=>"$id_mail"));	
  $onclick="onClick=\"location.href='$ref'\";";
  ?>
  <tr <?=atrib_tr();?>>	
  <td <?=$onclick?>><?=$string?></td>
  <td <?=$onclick?>><?=$string1?></td>
  </tr>
  <? 
  $botones->MoveNext();
 }
?>
</table>
<div align="center">
    <input type="button" name="Cerrar" value="Cerrar" style="width:19%" style="cursor:hand" title="Presione aqui para cerrar la Ventana" onclick="window.close()">
</div>

<?
}
else 
{
?>
<table  border="0" cellspacing="0" cellpadding="0" width="100%">  
<input type="hidden" name="id_mail" value="<?=$id_mail?>">    
<tr> 
    	<td align="center" colspan="3"> 
    	<input type="hidden" value="<?=$id_indi?>" name="id_indi" title="id_indi">
    		<strong> <font size="3" color="Blue">
    		Asignar Mail: &nbsp;	
    		</strong> </font>
    		<br>
    		<br>    		
    	</td> 
</tr>

<tr> 
    	<td align="center" width="45%"> 
    		<strong> <font size="2" color="Black">
    		Usuarios 
    		</strong> </font>
    	</td> 
    	<td align="center" width="10%"> 
    	&nbsp;
    	</td> 
    	<td align="center" width="45%"> 
    		<strong> <font size="2" color="Black">
    		Enviar Mail
    		</strong> </font>
    	</td> 	
</tr>

<tr>
     <td width="45%">
      <?
       $sql="select usuarios.nombre ||' '|| usuarios.apellido as nbre,id_usuario from sistema.usuarios order by nbre";
	   $result_mail=$db->Execute($sql) or die($db->ErrorMsg()."$sql");
       ?>
       
       <select name="renglones" multiple size="15" style="width:85%">
        <?
       $result_mail->MoveFirst();
       for($i=0;!$result_mail->EOF;$i++)
       {
        $string=$result_mail->fields['nbre'];
        $ii=$result_mail->fields['id_usuario'];
        if ($result_mail->fields['nbre']!="") echo "<option value='$ii'>$string</option>";
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
       $sql="select usuarios.nombre ||' '|| usuarios.apellido as nbre,id_usuario from sistema.usuarios join mail_usuarios using(id_usuario) where $id_mail=id_mail_botones order by nbre";
       $result=$db->execute($sql) or die($sql);
      ?>
      <select name="select_alternativas" multiple size="15" style="width:85%">
      <?
        $result->MoveFirst();  
      	for($i=0;!$result->EOF;$i++)
          {
			$string=$result->fields['nbre'];
			$iii=$result->fields['id_usuario'];
        	echo "<option value='$iii'>$string</option>";
            $result->MoveNext();
          }
        ?>
        </select>
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
<?
}
?>
</form>
