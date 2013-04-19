<?
/*
Creado por: Quique

Modificada por
$Author: enrique $
$Revision: 
$Date: 2006/02/07 17:42:54 $
*/


require_once("../../config.php");
//funcion para mandar el arreglo por post
//print_r($parametros);
$id_entrega_estimada=$parametros['id_entrega_estimada'] or $id_entrega_estimada=$_POST["id_entrega_estimada"];
echo $html_header;
?>
<form action="configurar_entrega.php" method="POST">
<table align="center" border="1" bgcolor='<?=$bgcolor3?>'>
<tr>
 <td>
  <font color="Blue"><b>Configuración de Entregas</b></font>
 </td>
</tr>
</table>
<table align="center" bgcolor='<?=$bgcolor3?>' width="100%">
<?
$sql="select id_lugar_entrega,configuracion_entrega.cantidad,lugar_entrega.direccion,titulo,id_renglones_oc,configuracion_entrega.id_entrega_estimada,codigo_renglon from licitaciones.configuracion_entrega
left join renglones_oc using(id_renglones_oc)
join licitaciones.renglon using (id_renglon)
join licitaciones.lugar_entrega using (id_lugar_entrega)

where configuracion_entrega.id_entrega_estimada=$id_entrega_estimada";//join licitaciones.lugar_entrega using(id_lugar_entrega)
$res=sql($sql) or fin_pagina();

$cons="select direccion,contacto,telefono,banda_horaria,id_lugar_entrega from licitaciones.lugar_entrega where id_entrega_estimada=$id_entrega_estimada";
$consulta=sql($cons,"No se pudo recuperar las direcciones");
$agregado=$consulta->RecordCount();
?>

<tr>
<td colspan="2">
<table id="tabla1" name="tabla1" width="100%" align="center">
<tr id="mo">
 <td><b>Contacto</b></td>
 <td><b>Tel</b></td>
 <td><b>Direccion</b></td>
 <td><b>Banda Horaria</b></td>
</tr>
<?

$i=1;	
while(!$consulta->EOF)
{
$id=$consulta->fields['id_lugar_entrega'];	
$ar[$i]=$id;
?> 
<tr <?=$atrib_tr?>> 
 <td><?=$consulta->fields['contacto'];?></td>
 <td><?=$consulta->fields['telefono'];?></td>
 <td><?=$consulta->fields['direccion'];?></td>
 <td><?=$consulta->fields['banda_horaria'];?></td>
</tr>
<?
$i++;
$consulta->MoveNext();
}
?>
</table>

<tr>
<td colspan="2" align="center">
<table id="tabla" name="tabla" width="100%">

<tr id="mo">
 <td width="15%"><b>Reng</b></td>
 <td width="5%"><b>Cant</b></td>
 <td width="40%"><b>Titulo</b></td>
 <td width="40%"><b>Entrega</b></td>
</tr> 
<?
$i=1;
while(!$res->EOF)
{
?>
<tr <?=$atrib_tr?>>
 <td><?=$res->fields['codigo_renglon'];?></td>
 <td><?=$res->fields['cantidad'];?></td>
 <td><?=$res->fields['titulo'];?></td>
 <td><?=$res->fields['direccion'];?></td>
</tr>

<?
$i++;
$res->MoveNext();
}
?>
</table>
</td>
</tr>
<tr align="center">
<td colspan="2">
<input type='button' name='cerrar' value='Cerrar' onclick="window.close()"></td>
</tr>
</table>
</form>
