<?php
  /*
$Author: ferni $
$Revision: 1.3 $
$Date: 2005/09/10 15:21:46 $
*/
include("../../config.php");

echo $html_header;

if($_POST['boton']=="Guardar")
{
$indi_a=$_POST['a'];
$indi_b=$_POST['b'];
$indi_c=$_POST['c'];
$indi_d=$_POST['d'];
$indi_e=$_POST['e'];
$indi_f=$_POST['f'];
$indi_g=$_POST['g'];
$indi_h=$_POST['h'];

$query="update calidad.const_eval_prov set ka=$indi_a, kb=$indi_b, kc=$indi_c, kd=$indi_d, ke=$indi_e, kf=$indi_f, kg=$indi_g, kh=$indi_h where id_const_eval_prov=1";
if (sql($query) or fin_pagina()){
 	echo "<br> <b> <center> <font size=2> LAS CONSTANTES SE ACTUALIZARON CON EXITO </font> </center> </b>";
	}
}//del guardar

?>

<script>
function control_datos()
{
	var suma=0;
	suma=
	parseFloat(document.all.a.value)+
	parseFloat(document.all.b.value)+
	parseFloat(document.all.c.value)+
	parseFloat(document.all.d.value)+
	parseFloat(document.all.e.value)+
	parseFloat(document.all.f.value)+
	parseFloat(document.all.g.value)+
	parseFloat(document.all.h.value)

if (suma==100){
	return true;
}//del if
else{
	alert("La Suma de las Constantes es: "+suma);
	return false;
}//del else

}//de la funcion

</script>


<form name="form1" action="<?=$link?>" method="POST" enctype='multipart/form-data'>
<?
//recupera el nombre del proveedor para mostrar en el titulo
$sql="select * from calidad.const_eval_prov";
$result=$db->execute($sql) or die($db->errormsg());
$indi_a=$result->fields["ka"];
$indi_b=$result->fields["kb"];
$indi_c=$result->fields["kc"];
$indi_d=$result->fields["kd"];
$indi_e=$result->fields["ke"];
$indi_f=$result->fields["kf"];
$indi_g=$result->fields["kg"];
$indi_h=$result->fields["kh"];
?>
<table width="80%"  border="1" align="center">
<br>
<br>
<tr>
   <td id=mo colspan="4">Contantes de Indicadores</td>
</tr> 

<tr>
 	<td width="40%">
 		<b>Letra A: Plazo de Pago</b>
  	</td>
 	<td width="10%"> 
  		<input type="text" name="a" value="<?=$indi_a?>" style="width=90%">
 	</td>

 	<td width="40%">
 		<b>Letra B: Limite de Credito</b>
  	</td>
 	<td>
  		<input type="text" name="b" value="<?=$indi_b?>" style="width=90%">
 	</td>
</tr>

<tr>
 	<td>
 		<b>Letra C: Cumplimiento de Entrega en Tiempo</b> 
 	</td>
 	<td>
  		<input type="text" name="c" value="<?=$indi_c?>" style="width=90%">
 	</td>

 	<td>
 		<b>Letra D: Esta Certificado ISO?</b> 
 	</td>
 	<td>
  		<input type="text" name="d" value="<?=$indi_d?>" style="width=90%">
 	</td>
</tr>

<tr>
 	<td>
 		<b>Letra E: Incidentes de Inconformidad</b> 
 	</td>
 	<td>
  		<input type="text" name="e" value="<?=$indi_e?>" style="width=90%">
 	</td>

 	<td>
 		<b>Letra F: Valoración Subjetiva</b> 
 	</td>
 	<td>
  		<input type="text" name="f" value="<?=$indi_f?>" style="width=90%">
 	</td>
</tr>

<tr>
 	<td>
 		<b>Letra G: Cantidad de Ordenes de Compra Pagadas</b> 
 	</td>
 	<td>
  		<input type="text" name="g" value="<?=$indi_g?>" style="width=90%">
 	</td>

 	<td>
 		<b>Letra H: Monto Total Comprado</b> 
 	</td>
 	<td>
  		<input type="text" name="h" value="<?=$indi_h?>" style="width=90%">
 	</td>
</tr>

<tr align="left">
 <td colspan="4" align="left">
 <font color="Red">
 <strong>
 <b>* La Suma de las Constantes debera ser 100 <br>
 	** A mayor constante, MAS Importancia se le Asignara al Indicador <br></b>
 </strong>
 </font>
 </td>
</tr>

</table>
<br>
<?
	$link=encode_link("clasif_prove.php",array());
?>
<div align="center">
	<input type=submit name='boton' value='Guardar' onclick="return control_datos()">&nbsp;
	<input type=button name='Volver' value='Volver' onclick="document.location='<?=$link?>'" title="Volver">
</div>

</form>
