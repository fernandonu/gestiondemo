<?php
include("../../config.php");
/*
$Author: fernando $
$Revision: 1.43 $
$Date: 2004/10/16 17:15:25 $
*/
$id_licitacion=$parametros['licitacion'];

$link1=encode_link("renglon_alternativa.php", array("licitacion" => $id_licitacion,"volver"=>$parametros["volver"]));

$sql="select * from (licitacion join entidad on licitacion.id_entidad = entidad.id_entidad and id_licitacion = $id_licitacion)";
$resultado_licitacion=$db->execute($sql) or die ($sql);

$id_moneda=$resultado_licitacion->fields['id_moneda'];
$query_moneda="select * from moneda where id_moneda=$id_moneda";
$resultado_moneda=$db->execute($query_moneda)or die ($query_moneda);
$simbolo=$resultado_moneda->fields['simbolo'];

//echo "variable control: ".$_POST['control'];
$control=$_POST['control'];
if(($_POST['Guardar_cambios']=="Guardar Cambios")&&($control==1)&&($_POST['modificar']!='ok')){
 $as=$_POST["compatiblesValues"];
 if($as=="")
  $tam=0;
 else
 {$array=explode(",",$as);
  $tam=count($array);
 }
  $db->StartTrans();
  if($tam>0){
   $nombre=$_POST['titulo'];
   $query="INSERT INTO oferta_licitacion(id_licitacion,nombre)
   values($id_licitacion,'$nombre')";
   $db->Execute($query) or die($db->ErrorMsg().$query);
 }

  for($i=0;$i<$tam;$i++)
   {
    $campo=$array[$i];
    $query2="SELECT max(id_oferta) as maximo from oferta_licitacion";
    $maximo=$db->Execute($query2) or die($db->ErrorMsg().$query2);
    $id=$maximo->fields['maximo'];
    $query1="INSERT INTO elementos_oferta values($id,$campo)";
    $db->Execute($query1) or die($db->ErrorMsg().$query1);
   }
  $db->CompleteTrans();
  header("Location:$link1");
}

//************************************************************************************
//Si el usuario selecciona una oferta y desea hacer un cambio
//************************************************************************************
if(($_POST['Guardar_cambios']=="Guardar Cambios")&&($_POST['modificar']=='ok')){
$as=$_POST["compatiblesValues"];
 if($as=="")
  $tam=0;
 else
 {$array=explode(",",$as);
  $tam=count($array);
 }


//echo "id_oferta: ".$_POST['eliminar']."<br>";
$id_oferta_anterior=$_POST['eliminar'];

$nombre=$_POST['titulo'];
$query_update="UPDATE oferta_licitacion set nombre='$nombre' where id_oferta=$id_oferta_anterior";
$db->Execute($query_update) or die($db->ErrorMsg().$query_update);

$query_delete="delete from elementos_oferta where id_oferta=$id_oferta_anterior";
$db->Execute($query_delete) or die($db->ErrorMsg().$query_delete);

for($i=0;$i<$tam;$i++){
  $renglon_insertado=$array[$i];
  $insertar_elemento="INSERT into elementos_oferta (id_oferta,id_renglon) VALUES ($id_oferta_anterior,$renglon_insertado)";
  $db->Execute($insertar_elemento) or die($db->ErrorMsg().$insertar_elemento);
}
}   //fin del if  para actualizar datos.




?>

<html>
  <head>
     <?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
	<style type="text/css">
	</style>
 </head>
  <body bgcolor="#E0E0E0">


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

function habilitar_botones() {
      document.all.boton[1].disabled=false;
     // document.all.boton[2].disabled=false;
}

function controles() {
if (document.all.titulo.value=='') {
  alert("Debe ingresar el nombre de la oferta");
  return 0;
  }
  else return 1
}

function val_text()
{     //adaptar a los select de esta página.
 var a=new Array();
 var largo=document.form1.select_alternativas.length;
 var i=0;

    for(i;i<largo;i++)
 	   {a[i]=document.form1.select_alternativas.options[i].value;
    	}
 document.form1.compatiblesValues.value=a;
}

</script>

<?php

//busco de la base de datos los renglones correspondientes con el id de licitacion que
//viene como parametro de la página realizar oferta
/*
$id_licitacion=$parametros['licitacion'];

$link1=encode_link("renglon_alternativa.php", array("licitacion" => $id_licitacion));

$sql="select * from (licitacion join entidad on licitacion.id_entidad = entidad.id_entidad and id_licitacion = $id_licitacion)";
 $resultado_licitacion=$db->execute($sql) or die ($sql);

  */
?>

<form name="form1" method="post" action="<?=$link1?>">

<input type="hidden" name="compatiblesValues" value="">
<?php

/*if($_POST['Guardar_cambios']=="Guardar Cambios"){
 $as=$_POST["compatiblesValues"];
 if($as=="")
  $tam=0;
 else
 {$array=explode(",",$as);
  $tam=count($array);
 }
  $db->StartTrans();

   $nombre=$_POST['titulo'];
   $query="INSERT INTO oferta_licitacion(id_licitacion,nombre)
   values($id_licitacion,'$nombre')";
   $db->Execute($query) or die($db->ErrorMsg().$query);

  for($i=0;$i<$tam;$i++)
   {
    $campo=$array[$i];
    $query2="SELECT max(id_oferta) as maximo from oferta_licitacion";
    $maximo=$db->Execute($query2) or die($db->ErrorMsg().$query2);
    $id=$maximo->fields['maximo'];
    $query1="INSERT INTO elementos_oferta values($id,$campo)";
    $db->Execute($query1) or die($db->ErrorMsg().$query1);
   }
  $db->CompleteTrans();

} */

?>

 <center>
  <table align="center" width="100%">
<tr id="mo">
<td colspan="7" align="center">
<font color="#E0E0E0">
<?
if($_ses_global_lic_o_pres=="pres")
                             $asunto_title="del Presupuesto";
else 
                             $asunto_title="de la Licitación";
?>
<b>Datos <?=$asunto_title?></td>
</tr>
 <tr >
   <td width="20%">
   <b>
   <?
if($_ses_global_lic_o_pres=="pres")
                             $asunto_title="Presupuesto";
else 
                             $asunto_title="Licitación";
?>
   <?=$asunto_title?>:
   <font color="#FF0000">
   <? echo $id_licitacion;?>
   </td>
   <td width="15%">
   <b>
   Entidad
   </td>
   <td>
   <font color="#FF0000">
   <b>
    <? echo $resultado_licitacion->fields['nombre'];  ?>
   </td>
 </tr>
</table>
       <br>

<?


     $sql="select * from oferta_licitacion where id_licitacion = $id_licitacion";
     $resultados_oferta=$db->execute($sql) or die($sql);
     $filas_encontradas = $resultados_oferta->RecordCount();

     $nro_oferta=$resultados_oferta->fields['id_oferta'];

/*

SELECT * from (licitaciones.elementos_oferta JOIN licitaciones.renglon on licitaciones.elementos_oferta.id_renglon=licitaciones.renglon.id_renglon) as p
JOIN licitaciones.oferta_licitacion on licitaciones.oferta_licitacion.id_oferta=p.id_oferta;

*/
//     $query="SELECT * from (elementos_oferta JOIN renglon join oferta on elementos_oferta.id_renglon=renglon.id_renglon)";


?>
<table  align="center" border="0" width="100%" bordercolor="#580000">
<tr id="mo">
<td colspan="7" align="center">
 <font color="#E0E0E0">
  <b>Ofertas realizadas
  </td>
</tr>
<tr  id="mo">
           <td  width="5%">
           </td>
      <!-- <td align="center">
           <font color="#E0E0E0" width="5%" >
           <b> Renglon
           </td> -->
           <td width="15%" align="center">
           <font color="#E0E0E0" >
           <b> Nombre Oferta
           </td>
           <td>
           <font width="70%" color="#E0E0E0">
           <b> Renglones que la componen
           </td>
     <!--  <td>
           <font color="#E0E0E0">
           <b>P. Total Renglon
           </td> -->
           <td>
           <font width="15%" color="#E0E0E0">
           <b>P. Total Oferta
           </td>
</tr>

<?php //construccion dinamica de las ofertas realizadas hasta el momento para la licitacion
/*switch ($_POST['boton'])
{
case 'Eliminar oferta':*/
	 //Eliminar de la base de datos una oferta

//se elimina de la tabla oferta_licitacion y elementos_oferta
if($_POST['boton']=="Eliminar oferta") {
     $db->StartTrans();
      $id=$_POST['eliminar'];

      $borrar1="delete from elementos_oferta where id_oferta=$id";
      $db->Execute($borrar1) or die($db->ErrorMsg().$borrar1);

      $borrar="delete from oferta_licitacion where id_oferta=$id";
      $db->Execute($borrar) or die($db->ErrorMsg().$borrar);

     $db->CompleteTrans();

//    break;

} //fin de eliminar

   $query="SELECT * from ((licitaciones.elementos_oferta JOIN licitaciones.renglon on licitaciones.elementos_oferta.id_renglon=licitaciones.renglon.id_renglon) as p
   JOIN licitaciones.oferta_licitacion on licitaciones.oferta_licitacion.id_oferta=p.id_oferta and oferta_licitacion.id_licitacion=$id_licitacion)
   order by oferta_licitacion.id_oferta";
   $resultados_oferta=$db->execute($query) or die($query);
   $filas_encontradas = $resultados_oferta->RecordCount();

$monto=0;
$oferta_ant="";
for($i=0;$i<$filas_encontradas;$i++) {
    $cantidad=$resultados_oferta->fields['cantidad'];
    $renglon=$resultados_oferta->fields['codigo_renglon'];
    $nombre=$resultados_oferta->fields['nombre'];
    $titulo_renglon.=" "."<font color='#E0E0E0'><b>".$renglon."</font></b>"." - ".$resultados_oferta->fields['titulo']." "."<br>";
    $total=$resultados_oferta->fields['total'];
    $control=$oferta_ant;
    $valor_radio=$resultados_oferta->fields['id_oferta'];
//    echo "<tr  bgcolor='$bgcolor1'>";
    $j=$i;
    $resultados_oferta->MoveNext();
    $senia=$resultados_oferta->fields['id_oferta'];
     if($valor_radio!=$senia) {
        echo "<tr  bgcolor='$bgcolor1'>";
 //control para dejar seleccionado un radio o no en caso de un reload.
        if($_POST['eliminar']==$valor_radio)
                echo "<td align='center'><input type='radio' name='eliminar' value='$valor_radio' onclick='habilitar_botones();' checked></td>";
        else
                echo "<td align='center'><input type='radio' name='eliminar' value='$valor_radio' onclick='habilitar_botones();'></td>";

        echo "<td><b>$nombre</b></td>";
     	echo "<td><b>$titulo_renglon</b></td>";
        $titulo_renglon=" ";
    }
    //if para calcular el monto de la oferta
    if($valor_radio==$senia) {
     $monto+=$total*$cantidad;

     } //fin de if
     else {
         $monto+=$total*$cantidad;
         echo "<td bgcolor='$bgcolor2'><center><b><font color='#FF0000'>$simbolo $monto</b></center></td>";
         $monto=0;
         }
   $oferta_ant=$valor_radio;
   echo "</tr>";   //tener cuidado con este 'tr'
} //final del for

echo " </table>";
?>

  <br>
    <hr>
    <table width='70%' border='0' cellspacing="0" cellpadding="0">
    <tr align="center">
       <td><input type="submit" name="boton" value="Nueva oferta" style="cursor:hand" title="Presione aqui para realizar una nueva oferta"></td>
       <td><input type="submit" name="boton" value="Eliminar oferta" style="cursor:hand" title="Presione aqui para eliminar la oferta seleccionada" disabled></td>
       <td><input type="submit" name="Mostrar" value="Mostrar Oferta" style="cursor:hand" title="Presione aqui para ver los datos de la oferta seleccionada"></td></html>
    </tr>
    </table>
     <br>
    <table width='60%' border='0' cellspacing="0" cellpadding="0">
    <tr>
       <td><b>Nombre de la oferta:</b></td><td><input type=text name='titulo' value=''></td>
    </tr>
    </table>

    <hr>
<?

//aca estaba el case
switch ($_POST['boton'])
{
//case 'Mostrar oferta':break;
default:?>
    <table  border="0" cellspacing="0" cellpadding="0" width="100%">
    <tr>
     <td width="45%">
      <center>
      <b>Renglones</b>
      <?

       $query_renglon="select * from renglon where id_licitacion=$id_licitacion";
	   $resultados=$db->Execute($query_renglon) or die($db->ErrorMsg()."$query_renglon");
	   $filas_encontradas=$resultados->RecordCount();
          ?>
       <select name="renglones" multiple size="15" style="width:85%">
        <?
       $resultados->MoveFirst();
       for($i=0;$i<$filas_encontradas;$i++)
       {
        $string=$resultados->fields['codigo_renglon'];
        $string.=" - ";
        $string.=$resultados->fields['titulo'];
           echo "<option value='".$resultados->fields['id_renglon']."'>$string</option>";
           $resultados->MoveNext();
       }
       ?>
     </select>
     </center>
     </td>
     <td width="10%" align="center" valign="middle">
      <input type="button" name="pasar_derecha" value=">>" size="10" style="cursor:hand" onclick="moveOver();">
      <br>
      <br>
      <input type="button" name="pasar_izquierda" value="<<" size="10" style="cursor:hand" onclick="removeMe();">
     </td>
     <td width="45%" align="center">
       <b>Renglones que componen la oferta</b>
     <?php
      //Si el usuario presiona el boton 'Mostrar oferta' se genera el select
      //con los datos cargados anteriormente en la licitacion para poder
      //realizar modificaciones

    if($_POST['Mostrar']=="Mostrar Oferta")
      {
          $id=$_POST['eliminar'];

          /*$query="select * from (licitaciones.elementos_oferta join licitaciones.renglon on
   		       licitaciones.elementos_oferta.id_renglon = licitaciones.renglon.id_renglon and
          	   licitaciones.elementos_oferta.id_oferta=$id)";*/
          $query="select * from ((licitaciones.elementos_oferta join licitaciones.renglon on  licitaciones.elementos_oferta.id_renglon = licitaciones.renglon.id_renglon and licitaciones.elementos_oferta.id_oferta=$id)  as p
          join licitaciones.oferta_licitacion on p.id_licitacion = licitaciones.oferta_licitacion.id_licitacion and
          licitaciones.oferta_licitacion.id_oferta=$id)";

          $resultados=$db->execute($query) or die($query);
  		  $filas_encontradas = $resultados->RecordCount();
          //cargo en el text el nombre de la oferta
          ?>
          <script>
          document.all.titulo.value='<?=$resultados->fields['nombre']?>'
          </script>
          <?
          echo "<input type='hidden' name='modificar' value='ok'>";
          echo "<select name='select_alternativas' value='' size='15' multiple style='width:85%'>";
          for($i=0;$i<$filas_encontradas;$i++)
          {
			$string=$resultados->fields['codigo_renglon'];
        	$string.=" - ";
	        $string.=$resultados->fields['titulo'];
    	    echo "<option value='".$resultados->fields['id_renglon']."'>$string</option>";
            $resultados->MoveNext();
          }
          echo "</select>";
         } //del if
      else {
           echo "<select name='select_alternativas' value='' size='15' multiple style='width:85%'>
           </select>";
         }
       ?>

     </td>
    </tr>
   </table>
  <center>
    <hr>
<?PHP
 
//$link=encode_link("realizar_oferta.php", array("licitacion" =>$parametros['licitacion']));
if (!$parametros["volver"]) $volver="realizar_oferta.php";
                       else $volver=$parametros["volver"];
$link=encode_link($volver, array("licitacion" =>$parametros['licitacion']));

?>
  	<?echo "<input type='hidden' name='control' value=''>"?>
    <input type="submit" name="Guardar_cambios" value="Guardar Cambios" style="cursor:hand" title="Presione aqui para guardar los cambios realizados" onclick="if(controles()) {val_text();document.all.control.value='1';} else document.all.control.value='0'">
    <input type="button" name="Volver" value="Volver" style="width:19%" style="cursor:hand" title="Presione aqui para volver a la pagina anterior" onclick="location.href='<?echo $link; ?>'">
<?
}?>

</form>
</body>
</html>