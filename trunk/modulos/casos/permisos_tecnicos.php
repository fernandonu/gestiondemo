<?
/*
$Author: cesar $
*/
require_once ("../../config.php");

echo $html_header;
?>
<html>
<head>
<link rel=stylesheet type='text/css' href='<?=$html_root;?>/lib/estilos.css'>
<style type="text/css">
<!--
.style1 {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
}
-->
</style>
<?
if ($_POST['boton']=="Guardar")
{$sql="delete from permisos_tecnicos";
 $db->execute($sql) or die($db->errormsg()." - ".$sql);
 $as=$_POST["permisos1"];
 if($as=="")
  $tam=0;
 else 
 {$array=explode(",",$as);
  $tam=count($array);
 } 
  for($i=0;$i<$tam;$i++)
   {
    $id=$array[$i];
    $query="insert into permisos_tecnicos(id_usuario,estado) values($id,0)";
    $db->Execute($query) or die($db->ErrorMsg().$query);
   }
   
  $as=$_POST["permisos_totales1"];
 if($as=="")
  $tam=0;
 else 
 {$array=explode(",",$as);
  $tam=count($array);
 } 
  for($i=0;$i<$tam;$i++)
   {
    $id=$array[$i];
    $query="insert into permisos_tecnicos(id_usuario,estado) values($id,1)";
    $db->Execute($query) or die($db->ErrorMsg().$query);
   }
 
 
}
?>
<script>

function val_text()
{var a=new Array();
    var largo=document.form1.permisos.length;
    var i=0;
    for(i;i<largo;i++)
    {a[i]=document.form1.permisos.options[i].value;
    }
	document.form1.permisos1.value=a;
}

function val_text2()
{var a=new Array();
    var largo=document.form1.permisos_totales.length;
    var i=0;
    for(i;i<largo;i++)
    {a[i]=document.form1.permisos_totales.options[i].value;
    }
	document.form1.permisos_totales1.value=a;
}

function moveOver_parcial() {
   var boxLength;// = document.form1.compatibles.length;
   var permisos_length = document.form1.personal.length;
   var selectedText;  // = document.choiceForm.available.options[selectedItem].text;
   var selectedValue; // = document.form1.productos.options[selectedItem].value;
   var i;
   var isNew = true;
   //aderezos
   arrText = new Array();
   arrValue = new Array();
  var count = 0;

  for (i = 0; i < permisos_length; i++) {
     if (document.form1.personal.options[i].selected) {
       arrValue[count] = document.form1.personal.options[i].value;
       arrText[count] = document.form1.personal.options[i].text;
       count++;
      }//fin if
   }//fin for
  
  for(j = 0; j < count; j++){
    isNew = true;
   	boxLength = document.form1.permisos.length;
   	selectedText=arrText[j];
   	selectedValue=arrValue[j];
   if (boxLength != 0) {
      for (i = 0;i < boxLength; i++) {
       thisitem = document.form1.permisos.options[i].text;
       if (thisitem == selectedText) 
         isNew = false;
     }//fin for
   }//fin if
   if (isNew) {
   	 newoption = new Option(selectedText, selectedValue, false, false);
     document.form1.permisos.options[boxLength] = newoption;    
   }//fin if
   document.form1.personal.selectedIndex=-1;
   }//fin for

   //elimino de permisos_total repetidos
   if (document.all.permisos_totales.options.length>0)
   {
   var x;
   count=0;
   boxLength = document.form1.permisos_totales.options.length;
   while(count < document.all.permisos_totales.options.length)
   {for (x = 0; ((x < document.all.permisos.options.length) && (count<document.all.permisos_totales.options.length)); x++) {
   // alert(document.form1.permisos.options[x].value); 
   // alert(document.all.permisos_totales.options[count].value);
    if (document.form1.permisos.options[x].value == document.all.permisos_totales.options[count].value) 
       {
         document.all.permisos_totales.options[count] = null;
       }
     }//fin for
     count++;
   }//fin while  
  }//fin if
}//fin funcion move Over_parcial




function removeMe_parcial() {
   var boxLength = document.form1.permisos.length;
   arrSelected = new Array();
   var count=0;
   for (i = 0; i < boxLength; i++) {
     if (document.form1.permisos.options[i].selected) {
       arrSelected[count] = document.form1.permisos.options[i].value;
       count++;
     }
   }
   var x;
   
   for (i = 0; i < boxLength; i++) {
     for (x = 0; x < arrSelected.length; x++) {
     if (document.form1.permisos.options[i].value == arrSelected[x]) {
         document.form1.permisos.options[i] = null;
       }
     }
     boxLength = document.form1.permisos.length;
   }
}

function removeMe_total() {
   var boxLength = document.form1.permisos_totales.length;
   arrSelected = new Array();
   var count=0;
   for (i = 0; i < boxLength; i++) {
     if (document.form1.permisos_totales.options[i].selected) {
       arrSelected[count] = document.form1.permisos_totales.options[i].value;
       count++;
     }
   }
   var x;
   
   for (i = 0; i < boxLength; i++) {
     for (x = 0; x < arrSelected.length; x++) {
     if (document.form1.permisos_totales.options[i].value == arrSelected[x]) {
         document.form1.permisos_totales.options[i] = null;
       }
     }
     boxLength = document.form1.permisos_totales.length;
   }
}

function moveOver_total() {
   var boxLength;// = document.form1.compatibles.length;
   var permisos_length = document.form1.personal.options.length;
   var selectedText;  // = document.choiceForm.available.options[selectedItem].text;
   var selectedValue; // = document.form1.productos.options[selectedItem].value;
   var i;
   var isNew = true;
   //aderezos
   arrText = new Array();
   arrValue = new Array();
  var count = 0;

  for (i = 0; i < permisos_length; i++) {
     if (document.form1.personal.options[i].selected) {
       arrValue[count] = document.form1.personal.options[i].value;
       arrText[count] = document.form1.personal.options[i].text;
       count++;
      }//fin if
   }//fin for
  
  for(j = 0; j < count; j++){
    isNew = true;
   	boxLength = document.form1.permisos_totales.length;
   	selectedText=arrText[j];
   	selectedValue=arrValue[j];
   if (boxLength != 0) {
      for (i = 0;i < boxLength; i++) {
       thisitem = document.form1.permisos_totales.options[i].text;
       if (thisitem == selectedText) 
         isNew = false;
     }//fin for
   }//fin if
   if (isNew) {
   	 newoption = new Option(selectedText, selectedValue, false, false);
     document.form1.permisos_totales.options[boxLength] = newoption;    
   }//fin if
   document.form1.personal.selectedIndex=-1;
   }//fin for
   
    //elimino de permisos_parciales repetidos
   if (document.all.permisos.options.length>0)
   {
   var x;
   count=0;
   boxLength = document.form1.permisos.options.length;
   while(count < document.all.permisos.options.length)
   {for (x = 0; ((x < document.all.permisos_totales.options.length) && (count<document.all.permisos.options.length)); x++)
    {
    if (document.form1.permisos_totales.options[x].value == document.all.permisos.options[count].value) 
       {
         document.all.permisos.options[count] = null;
       }//fin if
     }//fin for
     count++;
   }//fin while  
  }//fin if
 }
</script>
</head>
<body background=<?echo "$html_root/imagenes/$fondo"?>>
<form name="form1" action="permisos_tecnicos.php" method="POST">
<br>
<center>
<span class="style1"><font size="4">Permisos</font></span><br>
<b><cite><font size="3">T&eacute;cnicos Simples</font></cite></b>
</center>
<br><br>
<input type="hidden" name="permisos1">
<input type="hidden" name="permisos_totales1">
<table align="center" width="100%">
<tr>
<td width="35%"><b>&nbsp;Personal</b></td>
<td width="30%">
<b>Permisos Parciales</b> 
<input type="button" name="boton" value="X" title="Haga click aqui para eliminar usuarios de permisos parciales" onclick="removeMe_parcial();">
</td>
<td width="35%"><b>Permisos Totales </b>
<input type="button" name="boton" value="X" title="Haga click aqui para eliminar usuarios de permisos totales" onclick="removeMe_total();">
</td>
</tr>
<tr>
<td>
<table width="50%">
<tr>
<td width="20%">
<select multiple size="10" name="personal">
<?
$sql="select id_usuario,apellido, nombre,estado from usuarios left join permisos_tecnicos using(id_usuario) order by apellido";
$resultado_permisos=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
while (!$resultado_permisos->EOF)
{
?>
<OPTION value="<?=$resultado_permisos->fields['id_usuario']; ?>"><?=$resultado_permisos->fields['apellido']." ".$resultado_permisos->fields['nombre'];?></OPTION>
<?
$resultado_permisos->MoveNext();
}
?>
</select>
</td>
<td>
<input type="button" name="boton" value="Permiso Parcial" onclick="moveOver_parcial();" style="cursor:hand;width=100;"><br>
<input type="button" name="boton" value="&nbsp;Permiso Total&nbsp;&nbsp;" onclick="moveOver_total();" style="cursor:hand;width=100;">
</td>
</tr>
</table>
</td>
<td>
<select multiple size="10" name="permisos">
<?
$sql="select id_usuario,apellido, nombre,estado from usuarios join permisos_tecnicos using(id_usuario) where estado=0 order by apellido";
$resultado_permisos=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
while (!$resultado_permisos->EOF)
{
?>
<OPTION value="<?=$resultado_permisos->fields['id_usuario']; ?>"><?=$resultado_permisos->fields['apellido']." ".$resultado_permisos->fields['nombre'];?></OPTION>
<?
$resultado_permisos->MoveNext();
}
?>
</select>
</td>
<td>
<select multiple size="10" name="permisos_totales">
<?
$sql="select id_usuario,apellido, nombre,estado from usuarios join permisos_tecnicos using(id_usuario) where estado=1 order by apellido";
$resultado_permisos=$db->Execute($sql) or die($db->ErrorMsg()."<br>".$sql);
while (!$resultado_permisos->EOF)
{
?>
<OPTION value="<?=$resultado_permisos->fields['id_usuario']; ?>"><?=$resultado_permisos->fields['apellido']." ".$resultado_permisos->fields['nombre'];?></OPTION>
<?
$resultado_permisos->MoveNext();
}
?>
</select>
</td>
</tr>
</table>
<br>
<center>
<input type="submit" name="boton" value="Guardar" style="cursor:hand;" onclick="if((document.form1.permisos.options.length==0) && (document.form1.permisos_totales.options.length==0))
																				 {alert('No ha ingresado ningun proveedor');
																				  return false;
																				 }
                                                                       			else
                                                                       			{val_text();
                                                                       			 val_text2();
                                                                       			 return true;
                                                                       			}">
</center>
</form>
</body>
</html>