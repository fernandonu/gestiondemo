<?
/*
Author: ferni
*/

require_once ("../../config.php");
require_once("funciones.php");

extract($_POST,EXTR_SKIP);
if ($parametros) extract($parametros,EXTR_OVERWRITE);

if ($_POST['a_pendientes']=="A Pendientes"){	
	$id_llamadas_tel=$_POST['id_llamadas_tel'];
	$db->StartTrans();
	//actualizo la tabal muletos
	$sql="update encuestas.llamadas_tel set estado='p' where id_llamadas_tel=$id_llamadas_tel";
	sql($sql) or fin_pagina();
	$db->CompleteTrans();
    //redirecciono
	$accion="Se Cambio el Estado de la Llamada a Pendiente";
    $link=encode_link('llamadas_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}
if ($_POST['en_curso']=="En Curso"){	
	$id_llamadas_tel=$_POST['id_llamadas_tel'];
	$db->StartTrans();
	//actualizo la tabal muletos
	$sql="update encuestas.llamadas_tel set estado='e' where id_llamadas_tel=$id_llamadas_tel";
	sql($sql) or fin_pagina();
	$db->CompleteTrans();
    //redirecciono
	$accion="Se Cambio el Estado de la Llamada a En Curso";
    $link=encode_link('llamadas_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}
if ($_POST['a_historial']=="A Historial"){	
	$id_llamadas_tel=$_POST['id_llamadas_tel'];
	$db->StartTrans();
	//actualizo la tabal muletos
	$sql="update encuestas.llamadas_tel set estado='h' where id_llamadas_tel=$id_llamadas_tel";
	sql($sql) or fin_pagina();
	$db->CompleteTrans();
    //redirecciono
	$accion="Se Cambio el Estado de la Llamada a Historial";
    $link=encode_link('llamadas_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}

if ($_POST['guardar']=="Guardar Nueva Llamada"){   
    $db->StartTrans();
    $nombre=$_POST['nombre'];
    $apellido=$_POST['apellido'];
	$tel1=$_POST['tel1'];
	$tel2=$_POST['tel2'];
	$direccion=$_POST['direccion'];
	$mail=$_POST['mail'];
	$dni=$_POST['dni'];
	$localidad=$_POST['localidad'];
	$provincia=$_POST['provincia'];
	$cp=$_POST['cp'];
	$observaciones=$_POST['observaciones'];
	$estado='p';

    $q="select nextval('llamadas_tel_id_llamadas_tel_seq') as id_llamadas_tel";
    $id_llamadas_tel=sql($q) or fin_pagina();
    $id_llamadas_tel=$id_llamadas_tel->fields['id_llamadas_tel'];
       
    $query="insert into encuestas.llamadas_tel
             (id_llamadas_tel,apellido,nombre,tel1,tel2,direccion,mail,dni,localidad,provincia,cp,estado,observaciones)
             values
             ($id_llamadas_tel,'$nombre','$apellido','$tel1','$tel2','$direccion','$mail','$dni','$localidad','$provincia','$cp','$estado','$observaciones')";

    sql($query, "Error al insertar el llamada") or fin_pagina();
    
    $accion="Los datos de la Llamada se guardaron con Exito";
    $db->CompleteTrans();
    $link=encode_link('llamadas_listado.php',array("accion"=>$accion));
    header("Location:$link") or die("No se encontró la página destino");
}//de if ($_POST['guardar']=="Guardar nuevo Muleto")

if ($_POST['guardar_editar']=="Guardar"){
	$id_llamadas_tel=$_POST['id_llamadas_tel'];
	$db->StartTrans();
    $nombre=$_POST['nombre'];
    $apellido=$_POST['apellido'];
	$tel1=$_POST['tel1'];
	$tel2=$_POST['tel2'];
	$direccion=$_POST['direccion'];
	$mail=$_POST['mail'];
	$dni=$_POST['dni'];
	$localidad=$_POST['localidad'];
	$provincia=$_POST['provincia'];
	$cp=$_POST['cp'];
	$observaciones=$_POST['observaciones'];
	   
   $query="update encuestas.llamadas_tel set 
             nombre='$nombre', apellido='$apellido', tel1='$tel1', tel2='$tel2' , direccion='$direccion',
             mail='$mail', dni='$dni', localidad='$localidad', provincia='$provincia' , cp='$cp', observaciones='$observaciones'
             where id_llamadas_tel=$id_llamadas_tel";

   sql($query, "Error al actualizar el muleto") or fin_pagina();
    
   $db->CompleteTrans();
    
   $accion="Los datos de la Llamada se Actualizaron con Exito";
   $link=encode_link('llamadas_listado.php',array("accion"=>$accion));
   header("Location:$link") or die("No se encontró la página destino");	
}

if ($_POST['eliminar_llamada']=="Eliminar"){
   $db->StartTrans();
      
    $sql="delete from encuestas.llamadas_tel where id_llamadas_tel = $id_llamadas_tel";
    sql($sql, "Error no se puede eliminar log muletos") or fin_pagina();
      
   $db->CompleteTrans();
    
   $accion="La Llamada se Elimino con Exito";
   $link=encode_link('llamada_listado.php',array("accion"=>$accion));
   header("Location:$link") or die("No se encontró la página destino");	
}

if ($id_llamadas_tel) {
$sql="select * from encuestas.llamadas_tel where id_llamadas_tel=$id_llamadas_tel";
$res_q_muletos=sql($sql, "Error al traer los datos del caso") or fin_pagina();

$nombre=$res_q_muletos->fields['nombre'];
$apellido=$res_q_muletos->fields['apellido'];
$tel1=$res_q_muletos->fields['tel1'];
$tel2=$res_q_muletos->fields['tel2'];
$direccion=$res_q_muletos->fields['direccion'];
$mail=$res_q_muletos->fields['mail'];
$dni=$res_q_muletos->fields['dni'];
$localidad=$res_q_muletos->fields['localidad'];
$provincia=$res_q_muletos->fields['provincia'];
$cp=$res_q_muletos->fields['cp'];
$observaciones=$res_q_muletos->fields['observaciones'];
}
echo $html_header;
?>
<script>
//controlan que ingresen todos los datos necesarios par el muleto
function control_nuevos()
{
 if(document.all.nombre.value==""){
  alert('Debe ingresar un Nombre');
  return false;
 }
 if(document.all.apellido.value==""){
  alert('Debe ingresar un Apellido');
  return false;
 }
 if(document.all.tel1.value==""){
  alert('Debe ingresar algún Telefono');
  return false;
 }
 if(document.all.direccion.value==""){
  alert('Debe ingresar un Domicilio');
  return false;
 } 
 return true;
}//de function control_nuevos()

function editar_campos()
{
	document.all.nombre.readOnly=false;
	document.all.apellido.readOnly=false;
	document.all.tel1.readOnly=false;
	document.all.tel2.readOnly=false;
	document.all.direccion.readOnly=false;
	document.all.mail.readOnly=false;
	document.all.dni.readOnly=false;
	document.all.cp.readOnly=false;
	document.all.localidad.readOnly=false;
	document.all.provincia.readOnly=false;
	document.all.observaciones.readOnly=false;
	
	document.all.cancelar_editar.disabled=false;
	document.all.guardar_editar.disabled=false;
	document.all.editar.disabled=true;
 	return true;
}//de function control_nuevos()

</script>

<form name='form1' action='llamadas_admin.php' method='POST'>
<br>
<input type="hidden" name="id_llamadas_tel" value="<?=$id_llamadas_tel?>">
<table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?=$bgcolor_out?>' class="bordes">
 <tr id="mo">
    <td>
    <?
    if (!$id_llamadas_tel) {
    ?>
     <font size=+1><b> Nueva Llamada</b></font>
    <? }
        else {
    ?>
      <font size=+1><b>LLamada</b></font>
    <? } ?>
    </td>
 </tr>
 <tr><td><? tabla_datos_muletos(); ?></td></tr>
 
<!--Cambios de Estado-->
<tr><td><table width=100% align="center" class="bordes">  
 <?if ($estado=="pendientes"){?>
    <tr id="mo">
   		<td align=center colspan="2">
   			<b>Cambios de Estado</b>
   		</td>
   	</tr>   
    <tr>
    	<td width="25%" align="center" colspan="2"><br>
    	<input type="submit" value="En Curso" title="Pasa Llamada A En curso" name="en_curso" style="width=170" onclick="return confirm ('Esta Seguro que Desea pasar a En Curso la llamada?')">
    	</td>
    </tr>
 
 <?}?> 
 <?if ($estado=="en_curso"){?>
    <tr id="mo">
   		<td align=center colspan="2">
   			<b>Cambios de Estado</b>
   		</td>
   	</tr>   
    <tr>
    	<td width="25%" align="center" colspan="2"><br>
    	<input type="submit" value="A Pendientes" title="Pasa Llamada A Pendientes" name="a_pendientes" style="width=170" onclick="return confirm ('Esta Seguro que Desea pasar a Pendientes la llamada?')">
    	<input type="submit" value="A Historial" title="Pasa Llamada A Historial" name="a_historial" style="width=170" onclick="return confirm ('Esta Seguro que Desea pasar a historial la llamada?')">
    	</td>
    </tr>
 
 <?}?> 
 <?if ($estado=="historial"){?>
    <tr id="mo">
   		<td align=center colspan="2">
   			<b>Cambios de Estado</b>
   		</td>
   	</tr>   
    <tr>
    	<td width="25%" align="center" colspan="2"><br>
    	<input type="submit" value="En Curso" title="Pasa Llamada A En curso" name="en_curso" style="width=170" onclick="return confirm ('Esta Seguro que Desea pasar a En Curso la llamada?')">
    	</td>
    </tr>
 
 <?}?> 
</table></td></tr>
<!--Fin de Cambios de Estado-->

 <tr><td><table width=100% align="center" class="bordes">
  <tr align="center">
   <td>
     <input type=button name="volver" value="Volver" onclick="document.location='llamadas_listado.php'"title="Volver al Listado de Llamadas" style="width=150px">     
   </td>
  </tr>
 </table></td></tr> 
</table> 
</form>
<?=fin_pagina();// aca termino ?>
