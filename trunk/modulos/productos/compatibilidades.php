<?php
include("../../config.php");

echo $html_header;
?>

<!--
<html>
  <head>
-->
	<!-- <link rel=stylesheet type='text/css' href='/marco/lib/estilos.css'>-->

	<?php //echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<style type="text/css">
<!--
a {
	cursor: hand;text-decoration:none;
	color: #006699;
}
-->

</style>
<?php
include("../ayuda/ayudas.php");
?>

  </head>
  <body bgcolor="#E0E0E0" onload='document.focus();'>
<?php
if(isset($_POST["mothers"]))
{
  $mother=$_POST["mothers"];
 //primero borramos todas las compatibilidades con esa mother
 $query="delete from compatibilidades where motherboard=".$mother;
  $db->Execute($query) or die($db->ErrorMsg().$query);

 //luego insertamos las nuevas
 $as=$_POST["compatiblesValues"];
 if($as=="")
  $tam=0;
 else
 {$array=explode(",",$as);
  $tam=count($array);
 }
  for($i=0;$i<$tam;$i++)
   {
    $compo=$array[$i];
    $query="insert into compatibilidades(motherboard,componente) values(".$mother.",".$compo.")";
    $db->Execute($query) or die($db->ErrorMsg().$query);
   }

}


?>
<!--

INFORMACION DEL ARCHIVO:
compatibilidades.php
HECHO POR:
Marco Canderle y Pablo Rojo


-->
<SCRIPT LANGUAGE="JavaScript">
<?php

//trae las placas madre junto con sus compatibilidades y las deja en la variable
//con nombre "placa" concatenada con el id del producto

$sql="select * from general.productos join general.tipos_prod using (id_tipo_prod) where codigo='placa madre' order by desc_gral";
$resultados_mother=$db->execute($sql) or die($sql);

$query_prods="select * from general.productos join general.tipos_prod using(id_tipo_prod) where codigo='micro' order by desc_gral";
$resultados_prod=$db->execute($query_prods) or die($query_prods);

while (!$resultados_mother->EOF)
 {$sql="select desc_gral,id_producto from (compatibilidades join productos on productos.id_producto=componente) where motherboard=".$resultados_mother->fields["id_producto"] ." order by desc_gral";
  $resultado_comp=$db->execute($sql) or die($sql);
?>
var placa_<?php echo $resultados_mother->fields["id_producto"]; ?>=new Array(<?php echo $resultado_comp->RecordCount(); ?>);
<?php
$i=0;
while (!$resultado_comp->EOF)
 {?>
 placa_<?php echo $resultados_mother->fields["id_producto"]; ?>[<?php echo $i; ?>]=new Array(2);
 placa_<?php echo $resultados_mother->fields["id_producto"]; ?>[<?php echo $i; ?>][0]=<?php echo $resultado_comp->fields['id_producto']; ?>;
 placa_<?php echo $resultados_mother->fields["id_producto"]; ?>[<?php echo $i; ?>][1]="<?php echo $resultado_comp->fields['desc_gral']; ?>";
<?php
$i++;
$resultado_comp->MoveNext();
 }
$resultados_mother->MoveNext();
}

?>

function moveOver() {
   var boxLength;// = document.form1.compatibles.length;
   var prodLength = document.form1.productos.length;
   var selectedText;  // = document.choiceForm.available.options[selectedItem].text;
   var selectedValue; // = document.form1.productos.options[selectedItem].value;
   var i;
   var isNew = true;
   //aderezos
   arrText = new Array();
   arrValue = new Array();
  var count = 0;
   for (i = 0; i < prodLength; i++) {
     if (document.form1.productos.options[i].selected) {
       arrValue[count] = document.form1.productos.options[i].value;
       arrText[count] = document.form1.productos.options[i].text;
       count++;
      }
     //count++;
   }

   //fin de aderezos
   for(j = 0; j < count; j++){
   isNew = true;
   	boxLength = document.form1.compatibles.length;
   	selectedText=arrText[j];
   	selectedValue=arrValue[j];
   if (boxLength != 0) {
      for (i = 0; i < boxLength; i++) {
       thisitem = document.form1.compatibles.options[i].text;
       if (thisitem == selectedText) {
         isNew = false;
      }
     }
   }
   if (isNew) {
   	 newoption = new Option(selectedText, selectedValue, false, false);
     document.form1.compatibles.options[boxLength] = newoption;
     //document.form1.compatibles.options[boxLength].selected=true;

   }
   document.form1.productos.selectedIndex=-1;
   }
}

function removeMe() {
   var boxLength = document.form1.compatibles.length;
   arrSelected = new Array();
   var count = 0;
   for (i = 0; i < boxLength; i++) {
     if (document.form1.compatibles.options[i].selected) {
       arrSelected[count] = document.form1.compatibles.options[i].value;
     }
     count++;
   }
   var x;
   for (i = 0; i < boxLength; i++) {
     for (x = 0; x < arrSelected.length; x++) {
       if (document.form1.compatibles.options[i].value == arrSelected[x]) {
         document.form1.compatibles.options[i] = null;
       }
     }
     boxLength = document.form1.compatibles.length;
   }
}

//de acuerdo a la mother seleccionada, trae los componentes compatibles con ella.
function compat_mother()
{   var info;
    var x;
    var seguir=1;
    //vaciamos el select de compatibles
    document.form1.compatibles.length=0;
    //guardamos en info la informacion de la placa seleccionaa
    switch(document.all.mothers.options[document.form1.mothers.selectedIndex].value)
    {<?PHP
     $id="";
     $resultados_mother->Move(0);
     while(!$resultados_mother->EOF)
     {?>
      case '<?echo $resultados_mother->fields["id_producto"]?>': info=placa_<?echo $resultados_mother->fields["id_producto"];?>;break;
     <?
      $resultados_mother->MoveNext();
     }
     ?>
    default:seguir=0;
    }
    if(seguir)
    {
     largo=info.length;
     for(x=0;x<largo;x++)
     {
      document.all.compatibles.length++;
      document.all.compatibles.options[document.all.compatibles.length-1].text=info[x][1];
      document.all.compatibles.options[document.all.compatibles.length-1].value=info[x][0];
     }
    }

}

function val_text()
{var a=new Array();
    var largo=document.form1.compatibles.length;
    var i=0;
    for(i;i<largo;i++)
    {a[i]=document.form1.compatibles.options[i].value;
    }
	document.form1.compatiblesValues.value=a;
}

//funcion que habilita los objetos del foemulario despues que se selecciona una placa madre
function control(obj){
//javascript:document.form1.productos.disabled = !(this.value !='-1');
document.form1.productos.disabled=!(obj.value !='-1');
document.form1.pasar_derecha.disabled=!(obj.value !='-1');
document.form1.pasar_izquierda.disabled=!(obj.value !='-1');
document.form1.compatibles.disabled=!(obj.value !='-1');
document.form1.Guardar_cambios.disabled=!(obj.value !='-1');
}


</script>
<br>
<form name="form1" method="post" action="compatibilidades.php">

 <input type="hidden" name="compatiblesValues" value="">
 <input type="hidden" name="compatiblesTexts" value="">
 <div align="right">
        <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/productos/ayuda_compat.htm" ?>', 'COMPATIBILIDADES')" >
    </div>
 <center>
  <table  border="0" cellspacing="0" cellpadding="0" width="100%">
    <tr>
       <td align="left" colspan="3"><b>Placa Madre&nbsp;&nbsp;</b></td>
    </tr>
    <tr>
     <td align="center" colspan="3">
          <SELECT name="mothers" onchange="compat_mother();control(this);" onKeypress= "compat_mother(); control(this);buscar_op(this);compat_mother(); control(this);" onblur="borrar_buffer()" onclick= "borrar_buffer()" >
       <option value="-1">Seleccione placa madre</option>
       <?
          $resultados_mother->Move(0);
          while(!$resultados_mother->EOF)
          {$sel="";
           if($resultados_mother->fields["id_producto"]==$_POST["mothers"])
            $sel="selected";
          	 ?>
          <option value="<? echo $resultados_mother->fields["id_producto"]."\" ".$sel?>><? echo $resultados_mother->fields["desc_gral"]?></option>
          <?
           $resultados_mother->MoveNext();
          }
         ?>

       </select>
       <br>
       <hr>
        <br>
     </td>
    </tr>
    <tr>
     <td width="45%">
      <center>
      <b>Productos</b>
	      <select name="productos" multiple size=15 style="width:85%" <?php if (!isset($_POST["mothers"])) echo 'disabled'?>>
         <? $resultados_prod->Move(0);
          while(!$resultados_prod->EOF)
          {?>

		   <option value=<? echo $resultados_prod->fields["id_producto"]?>><? echo $resultados_prod->fields["desc_gral"]?></option>
          <?
           $resultados_prod->MoveNext();
          }
         ?>
      </select>
     </center>
     </td>
	  <td width="10%" align="center" valign="middle">
      <input type="button" name="pasar_derecha" value=">>" size="10" onclick="moveOver();" <?php if (!isset($_POST["mothers"])) echo 'disabled'?>>
    <br>
     <br>
	   <input type="button" name="pasar_izquierda" value="<<" size="10" onclick="removeMe();" <?php if (!isset($_POST["mothers"])) echo 'disabled'?>>
       </td>
       <td width="45%" align="center">
      <b>Productos Compatibles</b>
    	   <select name="compatibles" value="compatibles" size="15" multiple  style="width:85%" <?php if (!isset($_POST["mothers"])) echo 'disabled'?>>
        <?/*
          if($_POST["mothers"]!="-1")
          {$query="select id_producto,desc_gral from (productos join compatibilidades on productos.id_producto=compatibilidades.id_producto and productos.id_producto=".$_POST["mothers"];
           $resultados_comp_sel=$db->Execute($query) or die($db->ErrorMsg());
           $resultados_comp_sel->Move(0);
           while(!$resultados_comp_sel->EOF)
           {?>
           <option value=<? echo $resultados_comp_sel->fields["id_producto"]?>><? echo $resultados_comp_sel->fields["desc_gral"]?></option>
           <?
            $resultados_comp_sel->MoveNext();
           }
          } */
         ?>
      </select>
     </td>
    </tr>
   </table>
    <center>
    <hr>
    <input type="button" name="Guardar_cambios" value="Guardar Cambios" title="Guarda los cambios realizados a la tabla de productos compatibles " onclick="if(document.form1.mothers.value=='-1')
                                                                                                                                                               {alert('Debe seleccionar una placa madre');
                                                                                                                                                               }
                                                                                                                                                               else
                                                                                                                                                               {val_text();
                                                                                                                                                                 window.document.form1.submit();
                                                                                                                                                               }
                                                                                                                                                               " <?php if (!isset($_POST["mothers"])) echo 'disabled' ?> >
    </CENTER>
<script >
compat_mother();
</script>
</form>
</body>
</html>