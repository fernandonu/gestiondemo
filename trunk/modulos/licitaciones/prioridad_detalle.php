<?php
include("../../config.php");
/*
$Author: cesar $
$Revision: 1.13 $
$Date: 2004/08/07 14:26:52 $
*/



echo $html_header;
include("../ayuda/ayudas.php");
?>


<center>
<form name="form1" action="prioridad_detalle.php" method="POST">
<div align="right">
 <img src='<?php echo "$html_root/imagenes/ayuda.gif" ?>' border="0" alt="ayuda" onClick="abrir_ventana('<?php echo "$html_root/modulos/ayuda/licitaciones/ayuda_prioridad.htm" ?>', 'CAMBIAR PRIORIDAD')" >
</div>
<div align="center">
</div>

<?

$query="select * from licitaciones.prioridades order by(id_prioridad)";
$resultados = $db->Execute("$query") or die($db->ErrorMsg());
$filas_encontradas=$resultados->RecordCount();

$limite=$_POST["orden"];

if(($_POST["Bajar"]=="Bajar")&&($_POST['orden']!="")) {
	for($j=1;$j<$limite;$j++) {$resultados->MoveNext();}
	
    $valor_auxiliar=$resultados->fields['titulo'];	
    $resultados->MoveNext();
    $valor_auxiliar1=$resultados->fields['titulo'];
    if(($_POST['orden']+1) <= $filas_encontradas) {
    $valor_actualizar=$_POST['orden']+1;
       
 	/*echo $valor_auxiliar; //mouse
 	echo $valor_actualizar; //7
 	echo $valor_auxiliar1; //arquitectura
 	echo $_POST['orden'];  //6*/
 	$actualizar="UPDATE prioridades SET titulo='$valor_auxiliar' WHERE  id_prioridad=$valor_actualizar";
 	$resultado_actualizacion=$db->Execute($actualizar) or die($db->ErrorMsg());
 	$valor_actualizar1=$_POST['orden'];
 	$actualizar="UPDATE prioridades SET titulo='$valor_auxiliar1' WHERE  id_prioridad=$valor_actualizar1";
 	$resultado_actualizacion=$db->Execute($actualizar) or die($db->ErrorMsg());
    }
	
}
if(($_POST["Subir"]=="Subir")&&($_POST['orden']!="")) {
	for($j=1;$j<$limite;$j++) {$resultados->MoveNext();}
	
    $valor_auxiliar=$resultados->fields['titulo'];	
   // $resultados->MoveNext();
   $resultados->Move($resultados->AbsolutePosition()-1);
    $valor_auxiliar1=$resultados->fields['titulo'];
	$valor_actualizar=$_POST['orden']-1;
 	/*echo $valor_auxiliar; //memoria cache
 	echo $valor_actualizar; //3
 	echo $valor_auxiliar1; //arquitectura
 	echo $_POST['orden'];  //4*/
 	$actualizar="UPDATE prioridades SET titulo='$valor_auxiliar' WHERE  id_prioridad=$valor_actualizar";
 	$resultado_actualizacion=$db->Execute($actualizar) or die($db->ErrorMsg());
 	$valor_actualizar1=$_POST['orden'];
 	$actualizar="UPDATE prioridades SET titulo='$valor_auxiliar1' WHERE  id_prioridad=$valor_actualizar1";
 	$resultado_actualizacion=$db->Execute($actualizar) or die($db->ErrorMsg());
 	
	
}

if ($_POST["Mover"]){
  $pos=$_POST["posicion"]; //nueva pos 
   //posicionarse en la prioridad a cambiar
  $resultados->MoveFirst(); 
  $resultados->Move($resultados->AbsolutePosition()+($limite-1));
  //recupera valores a cambiar
  $valor_actual=$resultados->fields['titulo'];
  $limite=$resultados->fields['id_prioridad'];
 
 if ($pos < $limite){
   for ($i=$limite; $i>$pos; $i--){
  	$id_actual=$resultados->fields['id_prioridad'];
    $resultados->Move($resultados->AbsolutePosition()-1);
    $valor_cambiar=$resultados->fields['titulo'];
	$id_cambiar=$resultados->fields['id_prioridad'];
	$actualizar="UPDATE prioridades SET titulo='$valor_cambiar' WHERE  id_prioridad=$id_actual";
 	$resultado_actualizacion=$db->Execute($actualizar) or die($db->ErrorMsg());
   }
 	$actualizar="UPDATE prioridades SET titulo='$valor_actual' WHERE  id_prioridad=$pos";
 	$resultado_actualizacion=$db->Execute($actualizar) or die($db->ErrorMsg());
 }
 
 else {
  for ($i=$limite; $i<$pos; $i++){
 	$id_actual=$resultados->fields['id_prioridad'];
    $resultados->Move($resultados->AbsolutePosition()+1);
    $valor_cambiar=$resultados->fields['titulo'];
	$id_cambiar=$resultados->fields['id_prioridad'];
	$actualizar="UPDATE prioridades SET titulo='$valor_cambiar' WHERE  id_prioridad=$id_actual";
 	$resultado_actualizacion=$db->Execute($actualizar) or die($db->ErrorMsg());
    }
 	$actualizar="UPDATE prioridades SET titulo='$valor_actual' WHERE  id_prioridad=$pos";
 	$resultado_actualizacion=$db->Execute($actualizar) or die($db->ErrorMsg());
  }
 
}

$query="select * from licitaciones.prioridades order by(id_prioridad)";
$resultados = $db->Execute("$query") or die($db->ErrorMsg());
$filas_encontradas=$resultados->RecordCount();

?>
<table width="80%" bordes=0>
      <tr bgcolor="#c0c6c9">
      <td align="left"><font color="#006699" face="Georgia, Times New Roman, Times, serif"><b>Cantidad
            de Productos a establecer prioridad: <? echo $filas_encontradas; ?> </b></font>
      </td>
    </tr>
  </table>

<table class="bordes" width="80%" >
<tr title="Vea comentarios de los proveedores" bgcolor="<?php echo "#006699";?>">
<td align='center' width="10%"><font color="<?php echo $bgcolor2; ?>"><b>Cambiar Orden</b></font></td>
<td align='center' width="10%"><font color="<?php echo $bgcolor2; ?>"><b>Orden</b></font></td>
<td align='center' width="30%"><font color="<?php echo $bgcolor2; ?>"><b>Tipo de producto</b></font></td>
<?
//Generacion dinamica de la tabla principal de la pagina.
for ($i=1;$i<=$filas_encontradas;$i++) {
	$string=$resultados->fields['titulo'];
	//echo "<tr bgcolor='#CCCCCC'>";
	$aux=$i;   //variable auxiliar para llevar un control de la fila seleccionada
	if($_POST['Subir']=="Subir") $aux++; //Si el usuario presiono subir la fila seleccionada
										 //por defecto al recargar la pagina sera
										 //la siguiente a la actual
	if($_POST['Bajar']=="Bajar") $aux--; //idem del anterior pero al presionar bajar.
	if($_POST['orden']==$aux) {
		echo "<tr bgcolor=$bgcolor_over>";
	    echo "<td align='center'><INPUT type='radio' name='orden' value='$i' checked </td>";	 
	}
	else {
	  echo "<tr bgcolor=$bgcolor_out>";
	  echo "<td align='center'><INPUT type='radio' name='orden' value='$i'></td>";
	}
	echo "<td align='center'><font color='#0066CC'><b> $i </b></font></td>";	
	echo "<td><font color='#0066CC'><b>$string</b></font></td>";
	echo "</tr>";
	$resultados->MoveNext();
}

?>
</table>
<hr>

<input type="submit" name="Subir" value="Subir">
<input type="submit" name="Bajar" value="Bajar">
<input type="submit" name="Mover" value="Mover a .." > 
<select name="posicion">
<?php for ($i=1;$i<= $filas_encontradas;$i++){?>
 <option><? echo  $i;?> </option> <?}?>
</select>

 

<?
// <input type="button" name="Volver" value="Volver" onclick="history.go(-1)">
?>
</form>
</html>