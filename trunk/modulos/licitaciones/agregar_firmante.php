<? 
require_once("../../config.php");
$guardo=0;

//recupero el id y nombre de sucursales 
$q_suc="select id_sucursal,nombre from licitaciones.sucursales";
$res_suc=$db->Execute($q_suc) or die($db->ErrorMsg()."<br>".$q_suc);

switch($_POST['boton'])
{case 'Cancelar':header("location: licitaciones_view.php");break;
 case 'Guardar':$sql="delete from firmantes_lic";
                      $db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
                     /* $as=$_POST["firmantes_hidden"];
                      if($as=="")
                       $tam=0;
                      else 
                      {$array=explode(",",$as);
                       $tam=count($array);
                      }*/ 
                      for($i=0;(($i<10)&&($_POST['nombre_'.$i]!=""));$i++)
                       {$activo=(($i==0)?1:0);
                        $id=$array[$i];
                        $query="insert into firmantes_lic(nombre,dni,activo,id_sucursal) values('".$_POST['nombre_'.$i]."','".$_POST['dni_'.$i]."',".$activo.",'".$_POST['sucursal_'.$i]."')";
                        $db->Execute($query) or die($db->ErrorMsg().$query);
                       }
                       $guardo=1;
 default:{
echo $html_header;
?>
<form name="form" action="agregar_firmante.php" method="POST">
<?
if ($guardo)
{
?>
<center><font color="Red" size="5" face="times new roman">Los Datos se guardaron con éxito</font></center><br><br>
<?
}
?>
<font color="Blue" size="5" face="times new roman">Firmantes</font><br><br>
<script language="javascript">
/*
function val_text()
{var a=new Array();
    var largo=document.form.nombre.length;
    alert(largo);
    alert(document.form.nombre[2].value);
    var i=0;
    for(i;i<largo;i++)
    {a[i]=document.form.nombre[i].value;
    }
	document.form.firmantes_hidden.value=a;
	
	return false;
}

/*function move() {
   var boxLength;// = document.form1.compatibles.length;
   var permisos_length = document.form.personal.options.length;
   var selectedText;  // = document.choiceForm.available.options[selectedItem].text;
   var selectedValue; // = document.form1.productos.options[selectedItem].value;
   var i;
   var isNew = true;
   //aderezos
   arrText = new Array();
   arrValue = new Array();
   var count = 0;
  for (i = 0; i < permisos_length; i++) {
     if (document.form.personal.options[i].selected) {
       arrValue[count] = document.form.personal.options[i].value;
       arrText[count] = document.form.personal.options[i].text;
       count++;
      }//fin if
   }//fin for
  for(j = 0; j < count; j++){
    isNew = true;
   	boxLength = document.form.firmantes.length;
   	selectedText=arrText[j];
   	selectedValue=arrValue[j];
   if (boxLength != 0) {
      for (i = 0;i < boxLength; i++) {
       thisitem = document.form.firmantes.options[i].text;
       if (thisitem == selectedText) 
         isNew = false;
     }//fin for
   }//fin if
   if (isNew) {
   	 newoption = new Option(selectedText, selectedValue, false, false);
     document.form.firmantes.options[boxLength] = newoption;    
   }//fin if
   document.form.personal.selectedIndex=-1;
   }//fin for
}

function remove() {
   var boxLength = document.form.firmantes.length;
   arrSelected = new Array();
   var count=0;
   for (i = 0; i < boxLength; i++) {
     if (document.form.firmantes.options[i].selected) {
       arrSelected[count] = document.form.firmantes.options[i].value;
       count++;
     }
   }
   var x;
   for (i = 0; i < boxLength; i++) {
     for (x = 0; x < arrSelected.length; x++) {
     if (document.form.firmantes.options[i].value == arrSelected[x]) {
         document.form.firmantes.options[i] = null;
       }
     }
     boxLength = document.form.firmantes.length;
   }
}*/
</script>
<table border="0" align="center">
<tr><td align="center"><b><font size="3">Nombre</font></b></td>
<td align="center"><b><font size="3">DNI</font></b></td>
<td align="center"><b><font size="3" title="sucursal en donde trabaja">SUCURSAL</font></b></td>
</tr>
<?
$cant=0;
$sql="select nombre,dni,id_sucursal from firmantes_lic order by activo desc";
$resultado_firmantes=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
?>
<?
   while ($cant<10)
    {
    ?>
    <tr><td><input type="text" name="nombre_<?=$cant;?>" value="<?=$resultado_firmantes->fields['nombre'];?>" size="50"></td>
    <td><input type="text" name="dni_<?=$cant;?>" value="<?=$resultado_firmantes->fields['dni'];?>" size="30"></td>
     <td>
    <select name="sucursal_<?=$cant;?>" >
     <? $res_suc->MoveFirst();
     while (!$res_suc->EOF) { ?>
    <option value="<?=$res_suc->fields['id_sucursal']?>" <? if ($resultado_firmantes->fields['id_sucursal']==$res_suc->fields['id_sucursal']) echo 'selected' ?> > <?=$res_suc->fields['nombre']?></option>
    <? $res_suc->MoveNext();
    }?>
    </select>
     </td>
    </tr>
    <?
    if (!$resultado_firmantes->EOF)
     $resultado_firmantes->MoveNext();
    $cant++;
    }
?>
</table><br>
<center>
<input type="submit" name="boton" value="Guardar" style="cursor:hand" onclick="//return val_text()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="boton" value="Cancelar" style="cursor:hand">
</center>
</form>
</body>
</html>
<?
 }
}
?>