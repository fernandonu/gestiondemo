<?php
  /*
$Author: ferni $
$Revision: 1.2 $
$Date: 2005/09/06 21:55:28 $
*/
include("../../config.php");

echo $html_header;
$id_reoprtes_incidentes=$parametros["id_reportes_incidentes"];

if($_POST['boton']=="Guardar")
{
$id_tabla=$_POST['id'];
$query="update reportes_incidentes set titulo='".$_POST['titulo']."',descripcion='".$_POST['descripcion']."' where id_reportes_incidentes=$id_tabla";
if (sql($query) or fin_pagina()){
 	echo "<br> <b> <center> <font size=2> EL INCIDENTE SE ACTUALIZO CON EXITO </font> </center> </b>";
	}
}//del guardar

//print_r($_POST);

if($_POST['alta']=="Agregar")
{
$id_prove_param=$_POST['id_proveedor'];
$fecha_param = date("Y-m-d");
$usuario_param = $_ses_user["name"];
$titulo_param=$_POST['titulo'];
$descripcion_param=$_POST['descripcion'];
/*
echo $id_prove_param." ";
echo $fecha_param." " ;
echo $titulo_param. " ";
echo $descripcion_param. " ";*/

$query="insert into reportes_incidentes (id_proveedor,fecha,usuario,titulo,descripcion) values($id_prove_param,'$fecha_param','".$_ses_user['name']."','".$titulo_param."','".$descripcion_param."')";
//echo $query;
if (sql($query) or fin_pagina()){
 	echo "<br> <b> <center> <font size=2> EL INCIDENTE SE AGREGO CON EXITO </font> </center> </b>";
	}
}//del alta		


if ($parametros["ver"]=="soloLectura"){
//aca viene la consulta de ver
$sql="select * from reportes_incidentes where id_reportes_incidentes=$id_reoprtes_incidentes";
$result=$db->execute($sql) or die($db->errormsg());
$fecha_inci=fecha($result->fields["fecha"]);
$usuario_inci=$result->fields["usuario"];
$titulo_inci=$result->fields["titulo"];
$descripcion_inci=$result->fields["descripcion"];
$soloLectura="readonly";
$visibleBoton="style='display:none'";
}

if ($parametros["alta"]=="alta"){
$fecha_inci=date("d-m-Y");
$usuario_inci=$_ses_user["name"];
$titulo_inci="";
$descripcion_inci="";
$soloLectura="";
$visibleBoton="";
}
?>

<script>
function control_datos()
{
 
 if(document.all.titulo.value==""){
 	alert('Debe ingresar un título para el Incidente');
  	return false;
 }		
 
 if(document.all.descripcion.value==""){
 	alert('Debe ingresar una descripción para el Incidente');
 	return false;
 }
 return true;
}
</script>


<form name="form1" action="<?=$link?>" method="POST" enctype='multipart/form-data'>

<!--utilizo el hidden para el id_proveedor en el alta-->
<input type="hidden" name="id_proveedor" value="<?=$parametros["id_proveedor"]?>">
<!--utilizo el hidden para el id_reoprtes_incidentes en el modificar-->
<input type="hidden" name="id" value="<?=$id_reoprtes_incidentes?>">
<br>
<br>
<?
//recupera el nombre del proveedor para mostrar en el titulo
$id_prov=$parametros["id_proveedor"];
$sql666="select razon_social from proveedor where id_proveedor=$id_prov";
$result666=$db->execute($sql666) or die($db->errormsg());
?>
<table width="85%"  border="1" align="center">
<tr>
   <td id=mo colspan="2">Reporte de Indidente del Proveedor <?=$result666->fields["razon_social"]?></td>
</tr> 
<tr>
 	<td>
 		<b>Fecha</b> 
 	</td>
 	<td>
  		<input readonly type="text" name="fecha" value="<?=$fecha_inci?>" style="width=30%">
 	</td>
</tr>

<tr>
 	<td>
 		<b>Usuario</b> 
 	</td>
 	<td>
  		<input readonly type="text" name="usuario" value="<?=$usuario_inci?>" style="width=30%">
 	</td>
</tr>

<tr>
 	<td>
 		<b>Título</b> 
 	</td>
 	<td>
  		<input <?=$soloLectura?> type="text" name="titulo" value="<?=$titulo_inci?>" style="width=97%">
 	</td>
</tr>
 
<tr>
 <td>
  <b>Descripción</b>
 </td>
 <td>
  <textarea <?=$soloLectura?> name="descripcion" cols="119" rows="8"><?=$descripcion_inci?></textarea>
 </td>
</tr>

</table>
<br>
<?
if ($parametros["ver"]=="soloLectura"){
	$link=encode_link("clasif_prove.php",array());
?>
<div align="center">
	<input type=submit name='boton' value='Guardar' style='display:none'>
	<input type=button name='Volver' value='Volver' onclick="document.location='<?=$link?>'" title="Volver">&nbsp;
</div>

<?}
if ($parametros["alta"]=="alta"){
?>
<div align="center">
	<input type=submit name='alta' value='Agregar' <?=$visibleBoton?> onclick="return control_datos()">
    <input type=button name='Cerrar' value='Cerrar' onclick="window.close()" title="Cerrar">
</div>
<?}?>

</form>
