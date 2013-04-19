<?php
  /*
$Author: $
$Revision: $
$Date: $
*/
include("../../config.php");
?>


<?php

$link=" ";

$id_cliente=$parametros['id_cliente'];
$pagina=$parametros['pagina'];

if($_POST['Agregar']=="Agregar")  {

//Insertamos un cliente en la base de datos
$nombre=$_POST['nombre'];
$direccion=$_POST['direccion'];
$cod_post=$_POST['cod_postal'];
$localidad=$_POST['localidad'];
$provincia=$_POST['provincia'];
$cuit=$_POST['cuit'];
$contacto=$_POST['contacto'];
$telefono=$_POST['telefono'];
$mail=$_POST['mail'];
$observaciones=$_POST['observaciones'];

$query="INSERT INTO general.cliente (nombre,direccion,cod_pos,localidad,provincia,cuit,contacto,telefono,mail)
		values ('$nombre','$direccion','$cod_post','$localidad','$provincia',$cuit,
		        '$contacto',$telefono,'$mail')";
$resultado = $db->Execute($query) or die($db->ErrorMsg().$query);
}



if($_POST['Eliminar']=="Eliminar Cliente")  {

$query="DELETE FROM general.cliente WHERE id_cliente=$id_cliente";
$resultado = $db->Execute($query) or die($db->ErrorMsg().$query);
}


?>

<html>
<head>
<title>Clientes</title>
<?php echo "<link rel=stylesheet type='text/css' href='$html_root/lib/estilos.css'>"; ?>
<style type="text/css">
<!--
a {
	cursor: hand;text-decoration:none;
	color: #006699;
}
-->

</style>
<style type="text/css">
.boton{
        font-size:10px;
        font-family:Verdana,Helvetica;
        font-weight:bold;
        color:white;
        background:#638cb9;
        border:0px;
        width:160px;
        height:19px;
       }
</style>


</head>
<body bgcolor="#E0E0E0">
<center>
<br>
<!-- BUSQUEDA DE CLIENTES-->




<form name="form1" method="post" action="nuevo_cliente.php">
<center>

<?php

echo "<table border='0' width='80%'>";
echo "<tr  bgcolor='$bgcolor1'> ";
echo "<td colspan='6' ><font color=$bgcolor3><div align='center'><b>Cliente</b></div></font></TD>";
echo "</tr>";

//echo "<input type='text' name ='prueba' value='".$_POST["select_razon_social"]."'>";

?>
<?php

//querys necesarios para setear los textfiels en cada 'reload' de la página.

if($pagina=="listado_clientes"){

$query="SELECT * from general.cliente WHERE id_cliente = $id_cliente";
$resultado = $db->Execute($query) or die($db->ErrorMsg().$query);
$filas_encontradas=$resultado->RecordCount();

}
?>
	<tr  bgcolor="#CCCCCC">
		<td>Nombre: </td>
		<? if($pagina=="listado_clientes")
			echo "<td colspan='2'><input type='text' name='nombre' value='"
   	    	.$resultado->fields['nombre']."' size='25'></td>";
            else
       		echo "<td colspan='2'><input type='text' name='nombre' value='' size='25'>";
        ?>
		<td>Contacto: </td>
		<?if($pagina=="listado_clientes")
			echo "<td colspan='2'><input type='text' name='contacto' value='"
			.$resultado->fields['contacto']."' size='25'></td>";
			else
			echo "<td colspan='2'><input type='text' name='contacto' value='' size='25'></td>";
		?>
	</tr>
	<tr  bgcolor="#CCCCCC">
		<td>Direcci&ocute;n:</td>
		<?if($pagina=="listado_clientes")
			echo "<td colspan='2'><input type='text' name='direccion' value='"
			.$resultado->fields['direccion']."'
			' size='25'></td>";
		else
		echo "<td colspan='2'><input type='text' name='direccion' value='' size='25'></td>";
	   ?>
		<td>Localidad:</td>
		<?if($pagina=="listado_clientes")
			echo "<td colspan='2'><input type='text' name='localidad' value='"
			.$resultado->fields['localidad']."'	 size='25'></td>";
		else
			echo "<td colspan='2'><input type='text' name='localidad' value='' size='25'></td>";
		?>
	</tr>

	<tr  bgcolor="#CCCCCC">
		<td>Codigo Postal:</td>
		<?
        if($pagina=="listado_clientes")
			echo "<td colspan='2'><input type='text' name='localidad' value='"
			.$resultado->fields['cod_pos']."' size='25'></td>";
		else
			echo "<td colspan='2'><input type='text' name='cod_postal' value='' size='25'></td>"
		?>
		<td>CUIT:</td>
		<?
		if($pagina=="listado_clientes")
			echo "<td colspan='2'><input type='text' name='cuit' value='"
			.$resultado->fields['cuit']."' size='25'></td>";
		else
			echo "<td colspan='2'><input type='text' name='cuit' value='' size='25'></td>"
		?>
	</tr>

	<tr  bgcolor="#CCCCCC">
		<td>Telefono:</td>
		<? //name=telefono

		if($pagina=="listado_clientes")
			echo "<td colspan='2'><input type='text' name='telefono' value='"
			.$resultado->fields['telefono']."' size='25'></td>";
		else
			echo "<td colspan='2'><input type='text' name='telefono' value='' size='25'></td>"
		?>
		<td>email:</td>
		<?//name=email
		if($pagina=="listado_clientes")
			echo "<td colspan='2'><input type='text' name='mail' value='"
			.$resultado->fields['mail']."' size='25'></td>";
		else
			echo "<td colspan='2'><input type='text' name='mail' value='' size='25'></td>"
		?>
	</tr>
</table>
<br>

<table border='0' width="80%">

	<tr  bgcolor= <? echo $bgcolor1 ?>>
		<td><div align='center'><font color=<? echo $bgcolor3 ?>><b> Comentarios:</b></font><div></td>
	</tr>

	<tr>
		<td><textarea name="observaciones" rows="3" cols="100"><?//para que sea dinamico?> </textarea></td>
	</tr>

</table>
<?php
	echo "<input type='submit' name='Agregar' value='Agregar' class='boton' style='cursor:hand' >&nbsp;";
	echo "<input type='submit' name='Cambiar' value='Cambiar' class='boton' style='cursor:hand' >&nbsp;";
    echo "<input type='submit' name='Eliminar' value='Eliminar Cliente' class='boton' style='cursor:hand'>";
?>

</center>

</form>
</body>
</html>